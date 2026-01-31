<?php
/**
 * Research Model
 *
 * Verwaltet den Forschungsbaum und Forschungsfortschritt.
 */
class Research
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Startet eine Forschung
     */
    public function start(int $researchId, int $farmId): array
    {
        // Hole Forschungs-Daten
        $research = $this->db->fetchOne('SELECT * FROM research_tree WHERE id = ?', [$researchId]);

        if (!$research) {
            return ['success' => false, 'message' => 'Forschung nicht gefunden'];
        }

        // Pruefe ob bereits erforscht
        $existing = $this->db->fetchOne(
            'SELECT * FROM farm_research WHERE farm_id = ? AND research_id = ?',
            [$farmId, $researchId]
        );

        if ($existing) {
            if ($existing['status'] === 'completed') {
                return ['success' => false, 'message' => 'Bereits erforscht'];
            }
            if ($existing['status'] === 'in_progress') {
                return ['success' => false, 'message' => 'Forschung laeuft bereits'];
            }
        }

        // Pruefe ob bereits eine Forschung laeuft
        $activeResearch = $this->db->fetchOne(
            'SELECT * FROM farm_research WHERE farm_id = ? AND status = ?',
            [$farmId, 'in_progress']
        );

        if ($activeResearch) {
            return ['success' => false, 'message' => 'Es laeuft bereits eine Forschung'];
        }

        // Pruefe Voraussetzung
        if ($research['prerequisite_id']) {
            $prerequisite = $this->db->fetchOne(
                'SELECT * FROM farm_research WHERE farm_id = ? AND research_id = ? AND status = ?',
                [$farmId, $research['prerequisite_id'], 'completed']
            );

            if (!$prerequisite) {
                return ['success' => false, 'message' => 'Voraussetzung nicht erfuellt'];
            }
        }

        // Pruefe Level-Anforderung
        $farm = new Farm($farmId);
        $farmData = $farm->getData();

        if ($farmData['level'] < $research['level_required']) {
            return ['success' => false, 'message' => "Level {$research['level_required']} erforderlich"];
        }

        // Pruefe und ziehe Kosten ab
        if ($research['cost'] > 0) {
            if (!$farm->subtractMoney($research['cost'], "Forschung: {$research['name']}")) {
                return ['success' => false, 'message' => 'Nicht genuegend Geld'];
            }
        }

        // Berechne Fertigstellungszeit
        $completedAt = date('Y-m-d H:i:s', strtotime("+{$research['research_time_hours']} hours"));

        // Starte Forschung
        $this->db->insert('farm_research', [
            'farm_id' => $farmId,
            'research_id' => $researchId
        ]);

        Logger::info('Research started', [
            'farm_id' => $farmId,
            'research' => $research['name']
        ]);

        return [
            'success' => true,
            'message' => "Forschung '{$research['name']}' gestartet!",
            'completed_at' => $completedAt
        ];
    }

    /**
     * Gibt den Forschungsbaum zurueck
     */
    public function getTree(int $farmId): array
    {
        // Hole alle Forschungen
        $allResearch = $this->db->fetchAll('SELECT * FROM research_tree ORDER BY category, level_required, id');

        // Hole Farm-Forschungen
        $farmResearch = $this->db->fetchAll(
            'SELECT * FROM farm_research WHERE farm_id = ?',
            [$farmId]
        );

        // Erstelle Lookup
        $farmResearchLookup = [];
        foreach ($farmResearch as $fr) {
            $farmResearchLookup[$fr['research_id']] = $fr;
        }

        // Fuege Status hinzu
        foreach ($allResearch as &$research) {
            $farmData = $farmResearchLookup[$research['id']] ?? null;

            if ($farmData) {
                $research['status'] = $farmData['status'];
                $research['started_at'] = $farmData['started_at'];
                $research['completed_at'] = $farmData['completed_at'];
            } else {
                $research['status'] = 'locked';
                $research['started_at'] = null;
                $research['completed_at'] = null;
            }

            // Pruefe ob freischaltbar
            $research['unlockable'] = $this->isUnlockable($research, $farmResearchLookup, $farmId);
        }

        // Gruppiere nach Kategorie
        $grouped = [];
        foreach ($allResearch as $research) {
            $grouped[$research['category']][] = $research;
        }

        return $grouped;
    }

    /**
     * Prueft ob eine Forschung freischaltbar ist
     */
    private function isUnlockable(array $research, array $farmResearchLookup, int $farmId): bool
    {
        // Bereits erforscht oder in Arbeit
        if (isset($farmResearchLookup[$research['id']])) {
            return false;
        }

        // Pruefe Voraussetzung
        if ($research['prerequisite_id']) {
            $prereq = $farmResearchLookup[$research['prerequisite_id']] ?? null;
            if (!$prereq || $prereq['status'] !== 'completed') {
                return false;
            }
        }

        // Pruefe Level
        $farm = new Farm($farmId);
        $farmData = $farm->getData();

        if ($farmData['level'] < $research['level_required']) {
            return false;
        }

        return true;
    }

    /**
     * Gibt aktive Forschung zurueck
     */
    public function getActive(int $farmId): ?array
    {
        return $this->db->fetchOne(
            "SELECT fr.*, rt.name, rt.description, rt.category, rt.research_time_hours, rt.points_reward
             FROM farm_research fr
             JOIN research_tree rt ON fr.research_id = rt.id
             WHERE fr.farm_id = ? AND fr.status = ?",
            [$farmId, 'in_progress']
        );
    }

    /**
     * Schliesst Forschung ab (fuer Cron)
     */
    public static function completeResearch(): int
    {
        $db = Database::getInstance();

        // Finde fertige Forschungen
        $completed = $db->fetchAll(
            "SELECT fr.*, rt.name, rt.points_reward
             FROM farm_research fr
             JOIN research_tree rt ON fr.research_id = rt.id
             WHERE fr.status = 'in_progress'
             AND TIMESTAMPADD(HOUR, rt.research_time_hours, fr.started_at) <= NOW()"
        );

        $count = 0;
        foreach ($completed as $research) {
            // Aktualisiere Status
            $db->update('farm_research', [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $research['id']]);

            // Vergebe Punkte
            $farm = new Farm($research['farm_id']);
            $farm->addPoints($research['points_reward'], "Forschung abgeschlossen: {$research['name']}");

            Logger::info('Research completed', [
                'farm_id' => $research['farm_id'],
                'research' => $research['name']
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * Bricht eine laufende Forschung ab
     */
    public function cancel(int $farmId): array
    {
        $active = $this->getActive($farmId);

        if (!$active) {
            return ['success' => false, 'message' => 'Keine aktive Forschung'];
        }

        // Erstattung: 50% der Kosten
        $research = $this->db->fetchOne('SELECT * FROM research_tree WHERE id = ?', [$active['research_id']]);
        $refund = $research['cost'] * 0.5;

        if ($refund > 0) {
            $farm = new Farm($farmId);
            $farm->addMoney($refund, "Forschung abgebrochen: {$research['name']}");
        }

        $this->db->update('farm_research', ['status' => 'cancelled'], 'id = :id', ['id' => $active['id']]);

        Logger::info('Research cancelled', [
            'farm_id' => $farmId,
            'research' => $research['name']
        ]);

        return [
            'success' => true,
            'message' => "Forschung abgebrochen. {$refund} EUR erstattet.",
            'refund' => $refund
        ];
    }

    /**
     * Gibt abgeschlossene Forschungen zurueck
     */
    public function getCompleted(int $farmId): array
    {
        return $this->db->fetchAll(
            "SELECT fr.*, rt.name, rt.description, rt.category
             FROM farm_research fr
             JOIN research_tree rt ON fr.research_id = rt.id
             WHERE fr.farm_id = ? AND fr.status = ?
             ORDER BY fr.completed_at DESC",
            [$farmId, 'completed']
        );
    }

    /**
     * Gibt verbleibende Zeit fuer aktive Forschung zurueck
     */
    public function getRemainingTime(int $farmId): ?array
    {
        $active = $this->getActive($farmId);

        if (!$active) {
            return null;
        }

        $startedAt = strtotime($active['started_at']);
        $completesAt = $startedAt + ($active['research_time_hours'] * 3600);
        $remaining = $completesAt - time();

        if ($remaining <= 0) {
            return ['remaining' => 0, 'completed' => true];
        }

        return [
            'remaining' => $remaining,
            'completed' => false,
            'hours' => floor($remaining / 3600),
            'minutes' => floor(($remaining % 3600) / 60),
            'completes_at' => date('Y-m-d H:i:s', $completesAt)
        ];
    }
}
