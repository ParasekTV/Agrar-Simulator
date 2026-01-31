<?php
/**
 * Ranking Model
 *
 * Verwaltet Ranglisten und Herausforderungen.
 */
class Ranking
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Gibt die globale Rangliste zurueck
     */
    public function getGlobalRanking(int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;

        $rankings = $this->db->fetchAll(
            "SELECT r.*, f.farm_name, f.level, u.username
             FROM rankings r
             JOIN farms f ON r.farm_id = f.id
             JOIN users u ON f.user_id = u.id
             ORDER BY r.total_points DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );

        // Fuege Rang hinzu
        foreach ($rankings as $index => &$rank) {
            $rank['position'] = $offset + $index + 1;
        }

        $total = (int) $this->db->fetchColumn('SELECT COUNT(*) FROM rankings');

        return [
            'rankings' => $rankings,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Gibt die Genossenschafts-Rangliste zurueck
     */
    public function getCooperativeRanking(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, COUNT(cm.id) as member_count
             FROM cooperatives c
             JOIN cooperative_members cm ON c.id = cm.cooperative_id
             GROUP BY c.id
             ORDER BY c.total_points DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Gibt den Rang einer Farm zurueck
     */
    public function getFarmRank(int $farmId): array
    {
        $farm = $this->db->fetchOne(
            "SELECT r.*, f.farm_name, f.level
             FROM rankings r
             JOIN farms f ON r.farm_id = f.id
             WHERE r.farm_id = ?",
            [$farmId]
        );

        if (!$farm) {
            return ['rank' => 0, 'points' => 0];
        }

        // Berechne Position
        $position = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) + 1 FROM rankings WHERE total_points > ?",
            [$farm['total_points']]
        );

        $farm['position'] = $position;

        return $farm;
    }

    /**
     * Aktualisiert alle Ranking-Positionen
     */
    public static function updatePositions(): void
    {
        $db = Database::getInstance();

        // Hole alle Rankings sortiert nach Punkten
        $rankings = $db->fetchAll(
            'SELECT id FROM rankings ORDER BY total_points DESC'
        );

        // Aktualisiere Positionen
        foreach ($rankings as $position => $ranking) {
            $db->update('rankings', [
                'rank_position' => $position + 1
            ], 'id = :id', ['id' => $ranking['id']]);
        }

        Logger::info('Rankings updated', ['count' => count($rankings)]);
    }

    /**
     * Gibt die Top-Farmen nach verschiedenen Kriterien zurueck
     */
    public function getTopBy(string $criteria, int $limit = 10): array
    {
        $validCriteria = ['total_points', 'total_money', 'total_sales_value'];

        if (!in_array($criteria, $validCriteria)) {
            $criteria = 'total_points';
        }

        return $this->db->fetchAll(
            "SELECT r.*, f.farm_name, f.level, u.username
             FROM rankings r
             JOIN farms f ON r.farm_id = f.id
             JOIN users u ON f.user_id = u.id
             ORDER BY r.{$criteria} DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Gibt aktive woechentliche Herausforderungen zurueck
     */
    public function getActiveChallenges(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM weekly_challenges
             WHERE active = TRUE
             AND start_date <= CURDATE()
             AND end_date >= CURDATE()
             ORDER BY end_date ASC"
        );
    }

    /**
     * Gibt den Fortschritt bei Herausforderungen zurueck
     */
    public function getChallengeProgress(int $farmId): array
    {
        $challenges = $this->getActiveChallenges();

        foreach ($challenges as &$challenge) {
            $progress = $this->db->fetchOne(
                'SELECT * FROM challenge_progress WHERE challenge_id = ? AND farm_id = ?',
                [$challenge['id'], $farmId]
            );

            $challenge['current_value'] = $progress['current_value'] ?? 0;
            $challenge['completed'] = $progress['completed'] ?? false;
            $challenge['percentage'] = min(100, ($challenge['current_value'] / $challenge['target_value']) * 100);
        }

        return $challenges;
    }

    /**
     * Aktualisiert den Fortschritt einer Herausforderung
     */
    public function updateChallengeProgress(int $farmId, string $challengeType, int $value): void
    {
        // Finde aktive Herausforderungen dieses Typs
        $challenges = $this->db->fetchAll(
            "SELECT * FROM weekly_challenges
             WHERE challenge_type = ?
             AND active = TRUE
             AND start_date <= CURDATE()
             AND end_date >= CURDATE()",
            [$challengeType]
        );

        foreach ($challenges as $challenge) {
            // Hole oder erstelle Fortschritt
            $progress = $this->db->fetchOne(
                'SELECT * FROM challenge_progress WHERE challenge_id = ? AND farm_id = ?',
                [$challenge['id'], $farmId]
            );

            if ($progress) {
                if ($progress['completed']) {
                    continue; // Bereits abgeschlossen
                }

                $newValue = $progress['current_value'] + $value;

                if ($newValue >= $challenge['target_value']) {
                    // Herausforderung abgeschlossen
                    $this->db->update('challenge_progress', [
                        'current_value' => $newValue,
                        'completed' => true,
                        'completed_at' => date('Y-m-d H:i:s')
                    ], 'id = :id', ['id' => $progress['id']]);

                    // Vergebe Belohnung
                    $farm = new Farm($farmId);
                    $farm->addPoints($challenge['reward_points'], "Herausforderung abgeschlossen: {$challenge['challenge_name']}");

                    Logger::info('Challenge completed', [
                        'farm_id' => $farmId,
                        'challenge' => $challenge['challenge_name']
                    ]);
                } else {
                    $this->db->update('challenge_progress', [
                        'current_value' => $newValue
                    ], 'id = :id', ['id' => $progress['id']]);
                }
            } else {
                // Erstelle neuen Fortschritt
                $this->db->insert('challenge_progress', [
                    'challenge_id' => $challenge['id'],
                    'farm_id' => $farmId,
                    'current_value' => $value,
                    'completed' => $value >= $challenge['target_value']
                ]);

                if ($value >= $challenge['target_value']) {
                    $farm = new Farm($farmId);
                    $farm->addPoints($challenge['reward_points'], "Herausforderung abgeschlossen: {$challenge['challenge_name']}");
                }
            }
        }
    }

    /**
     * Gibt die Bestenliste einer Herausforderung zurueck
     */
    public function getChallengeLeaderboard(int $challengeId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT cp.*, f.farm_name
             FROM challenge_progress cp
             JOIN farms f ON cp.farm_id = f.id
             WHERE cp.challenge_id = ?
             ORDER BY cp.current_value DESC
             LIMIT ?",
            [$challengeId, $limit]
        );
    }

    /**
     * Erstellt eine neue woechentliche Herausforderung
     */
    public function createChallenge(
        string $name,
        string $description,
        string $type,
        int $targetValue,
        int $rewardPoints
    ): int {
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+7 days'));

        return $this->db->insert('weekly_challenges', [
            'challenge_name' => $name,
            'description' => $description,
            'challenge_type' => $type,
            'target_value' => $targetValue,
            'reward_points' => $rewardPoints,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Beendet abgelaufene Herausforderungen
     */
    public static function expireChallenges(): int
    {
        $db = Database::getInstance();

        return $db->query(
            "UPDATE weekly_challenges
             SET active = FALSE
             WHERE active = TRUE AND end_date < CURDATE()"
        )->rowCount();
    }

    /**
     * Gibt Statistiken fuer das Dashboard zurueck
     */
    public function getDashboardStats(): array
    {
        $totalPlayers = (int) $this->db->fetchColumn('SELECT COUNT(*) FROM farms');
        $totalCoops = (int) $this->db->fetchColumn('SELECT COUNT(*) FROM cooperatives');
        $totalSales = (float) $this->db->fetchColumn('SELECT SUM(total_sales_value) FROM rankings');
        $activeToday = (int) $this->db->fetchColumn(
            'SELECT COUNT(*) FROM users WHERE DATE(last_login) = CURDATE()'
        );

        return [
            'total_players' => $totalPlayers,
            'total_cooperatives' => $totalCoops,
            'total_sales_value' => $totalSales,
            'active_today' => $activeToday
        ];
    }
}
