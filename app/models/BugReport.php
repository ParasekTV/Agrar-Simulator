<?php
/**
 * BugReport Model
 *
 * Verwaltet Bug-Meldungen.
 */
class BugReport
{
    private Database $db;
    private string $webhookUrl = 'https://discord.com/api/webhooks/1469200486300385400/iJ0WEUVJ8poHTLoR5ejIs3R46ihnaQqtKbG0OVKwoNS9vrgxaYyxLKSv6GvvSfheZanv';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Erstellt eine neue Bug-Meldung
     */
    public function create(int $userId, int $farmId, string $title, string $description): array
    {
        // Validierung
        if (strlen($title) < 5 || strlen($title) > 200) {
            return ['success' => false, 'message' => 'Titel muss zwischen 5 und 200 Zeichen lang sein'];
        }

        if (strlen($description) < 20) {
            return ['success' => false, 'message' => 'Beschreibung muss mindestens 20 Zeichen lang sein'];
        }

        // Hole Benutzerdaten
        $user = $this->db->fetchOne(
            'SELECT u.username, f.farm_name FROM users u JOIN farms f ON u.id = f.user_id WHERE u.id = ?',
            [$userId]
        );

        // Speichere in Datenbank
        $bugId = $this->db->insert('bug_reports', [
            'user_id' => $userId,
            'farm_id' => $farmId,
            'title' => Validator::sanitizeString($title),
            'description' => Validator::sanitizeString($description),
            'status' => 'open'
        ]);

        // Sende an Discord und speichere Thread-ID
        $threadId = $this->sendToDiscord($bugId, $title, $description, $user['username'], $user['farm_name']);

        if ($threadId) {
            $this->db->update('bug_reports', [
                'discord_thread_id' => $threadId
            ], 'id = :id', ['id' => $bugId]);
        }

        Logger::info('Bug report created', [
            'user_id' => $userId,
            'bug_id' => $bugId,
            'discord_thread_id' => $threadId
        ]);

        return [
            'success' => true,
            'message' => 'Bug-Meldung erfolgreich gesendet!',
            'bug_id' => $bugId
        ];
    }

    /**
     * Sendet Bug-Meldung an Discord Webhook
     * @return string|null Die Thread-ID oder null bei Fehler
     */
    private function sendToDiscord(int $bugId, string $title, string $description, string $username, string $farmName): ?string
    {
        $embed = [
            'title' => "Bug #$bugId: $title",
            'description' => $description,
            'color' => 15158332, // Rot
            'fields' => [
                [
                    'name' => 'Gemeldet von',
                    'value' => "$username ($farmName)",
                    'inline' => true
                ],
                [
                    'name' => 'Status',
                    'value' => 'Offen',
                    'inline' => true
                ]
            ],
            'timestamp' => date('c'),
            'footer' => [
                'text' => 'LSBG Agrar Simulator Bug Report'
            ]
        ];

        $payload = [
            'embeds' => [$embed],
            'thread_name' => "Bug #$bugId: $title",
            'applied_tags' => [] // Forum tags müssen manuell konfiguriert werden
        ];

        $ch = curl_init($this->webhookUrl . '?wait=true');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $response = json_decode($result, true);
            // Bei Forum-Webhooks ist die channel_id die Thread-ID
            return $response['channel_id'] ?? null;
        }

        Logger::error('Discord webhook failed', [
            'http_code' => $httpCode,
            'response' => $result
        ]);

        return null;
    }

    /**
     * Sendet Status-Update an Discord Thread
     */
    public function sendStatusUpdateToDiscord(int $bugId, string $status, string $reason, string $adminName): bool
    {
        $bug = $this->get($bugId);

        if (!$bug || empty($bug['discord_thread_id'])) {
            Logger::warning('Cannot send Discord update: No thread ID', ['bug_id' => $bugId]);
            return false;
        }

        $statusLabels = [
            'open' => 'Offen',
            'in_progress' => 'In Bearbeitung',
            'resolved' => 'Gelöst',
            'closed' => 'Geschlossen'
        ];

        $statusColors = [
            'open' => 15158332,      // Rot
            'in_progress' => 16761095, // Orange
            'resolved' => 5763719,    // Grün
            'closed' => 9807270       // Grau
        ];

        $statusEmojis = [
            'open' => '🔴',
            'in_progress' => '🟠',
            'resolved' => '✅',
            'closed' => '🔒'
        ];

        $embed = [
            'title' => $statusEmojis[$status] . ' Status-Update',
            'description' => "Der Status wurde auf **{$statusLabels[$status]}** geändert.",
            'color' => $statusColors[$status] ?? 9807270,
            'fields' => [
                [
                    'name' => 'Bearbeitet von',
                    'value' => $adminName,
                    'inline' => true
                ],
                [
                    'name' => 'Neuer Status',
                    'value' => $statusLabels[$status] ?? $status,
                    'inline' => true
                ]
            ],
            'timestamp' => date('c'),
            'footer' => [
                'text' => 'LSBG Agrar Simulator'
            ]
        ];

        // Grund hinzufügen wenn vorhanden
        if (!empty($reason)) {
            $embed['fields'][] = [
                'name' => 'Begründung',
                'value' => $reason,
                'inline' => false
            ];
        }

        $payload = ['embeds' => [$embed]];

        // Webhook-URL mit Thread-ID
        $webhookUrlWithThread = $this->webhookUrl . '?thread_id=' . $bug['discord_thread_id'];

        $ch = curl_init($webhookUrlWithThread);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            Logger::error('Discord status update failed', [
                'http_code' => $httpCode,
                'response' => $result,
                'bug_id' => $bugId
            ]);
            return false;
        }

        return true;
    }

    /**
     * Gibt alle Bug-Meldungen zurück
     */
    public function getAll(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $reports = $this->db->fetchAll(
            "SELECT br.*, u.username, f.farm_name
             FROM bug_reports br
             JOIN users u ON br.user_id = u.id
             JOIN farms f ON br.farm_id = f.id
             ORDER BY br.created_at DESC
             LIMIT $perPage OFFSET $offset"
        );

        $total = (int) $this->db->fetchColumn('SELECT COUNT(*) FROM bug_reports');

        return [
            'reports' => $reports,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Aktualisiert den Status einer Bug-Meldung
     */
    public function updateStatus(int $bugId, string $status, string $reason = '', string $adminName = ''): array
    {
        $allowedStatuses = ['open', 'in_progress', 'resolved', 'closed'];
        if (!in_array($status, $allowedStatuses)) {
            return ['success' => false, 'message' => 'Ungültiger Status'];
        }

        $updateData = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Grund speichern wenn vorhanden
        if (!empty($reason)) {
            $updateData['admin_reason'] = Validator::sanitizeString($reason);
        }

        $this->db->update('bug_reports', $updateData, 'id = :id', ['id' => $bugId]);

        // An Discord senden
        if (!empty($adminName)) {
            $this->sendStatusUpdateToDiscord($bugId, $status, $reason, $adminName);
        }

        return ['success' => true, 'message' => 'Status aktualisiert und an Discord gesendet'];
    }

    /**
     * Gibt eine einzelne Bug-Meldung zurück
     */
    public function get(int $bugId): ?array
    {
        return $this->db->fetchOne(
            'SELECT br.*, u.username, f.farm_name
             FROM bug_reports br
             JOIN users u ON br.user_id = u.id
             JOIN farms f ON br.farm_id = f.id
             WHERE br.id = ?',
            [$bugId]
        );
    }

    /**
     * Zählt offene Bug-Meldungen
     */
    public function countOpen(): int
    {
        return (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM bug_reports WHERE status = 'open'"
        );
    }
}
