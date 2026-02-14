<?php
/**
 * Arena Model v2.0
 * FSL-inspirierter Genossenschafts-Wettkampf
 */

class Arena
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Genossenschaft herausfordern
     */
    public function challengeCooperative(int $challengerCoopId, int $defenderCoopId): array
    {
        if ($challengerCoopId === $defenderCoopId) {
            return ['success' => false, 'message' => 'Kann sich nicht selbst herausfordern'];
        }

        // Prüfe ob bereits ein aktives Match existiert
        $existing = $this->db->fetchOne(
            "SELECT * FROM arena_matches
             WHERE status NOT IN ('finished', 'cancelled')
               AND ((challenger_cooperative_id = ? AND defender_cooperative_id = ?)
                    OR (challenger_cooperative_id = ? AND defender_cooperative_id = ?))",
            [$challengerCoopId, $defenderCoopId, $defenderCoopId, $challengerCoopId]
        );

        if ($existing) {
            return ['success' => false, 'message' => 'Es gibt bereits ein aktives Match zwischen diesen Genossenschaften'];
        }

        $this->db->insert('arena_matches', [
            'challenger_cooperative_id' => $challengerCoopId,
            'defender_cooperative_id' => $defenderCoopId,
            'status' => 'pending'
        ]);

        $matchId = $this->db->lastInsertId();

        return ['success' => true, 'message' => 'Herausforderung gesendet!', 'match_id' => $matchId];
    }

    /**
     * Herausforderung annehmen
     */
    public function acceptChallenge(int $matchId, int $coopId): array
    {
        $match = $this->db->fetchOne(
            'SELECT * FROM arena_matches WHERE id = ? AND defender_cooperative_id = ? AND status = "pending"',
            [$matchId, $coopId]
        );

        if (!$match) {
            return ['success' => false, 'message' => 'Herausforderung nicht gefunden oder bereits bearbeitet'];
        }

        $this->db->update('arena_matches', [
            'status' => 'pick_ban',
            'accepted_at' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $matchId]);

        return ['success' => true, 'message' => 'Herausforderung angenommen! Pick & Ban Phase beginnt.'];
    }

    /**
     * Herausforderung ablehnen
     */
    public function declineChallenge(int $matchId, int $coopId): array
    {
        $match = $this->db->fetchOne(
            'SELECT * FROM arena_matches WHERE id = ? AND defender_cooperative_id = ? AND status = "pending"',
            [$matchId, $coopId]
        );

        if (!$match) {
            return ['success' => false, 'message' => 'Herausforderung nicht gefunden'];
        }

        $this->db->update('arena_matches', [
            'status' => 'cancelled'
        ], 'id = :id', ['id' => $matchId]);

        return ['success' => true, 'message' => 'Herausforderung abgelehnt'];
    }

    /**
     * Verfügbare Fahrzeuge für Arena
     */
    public function getAvailableVehicles(int $matchId): array
    {
        // Hole bereits gepickte/gebannte Fahrzeuge
        $used = $this->db->fetchAll(
            "SELECT vehicle_id FROM arena_picks_bans WHERE match_id = ?",
            [$matchId]
        );
        $usedIds = array_column($used, 'vehicle_id');
        $usedIds[] = 0; // Fallback

        return $this->db->fetchAll(
            "SELECT avp.*, v.name, v.power_hp, vb.name as brand_name
             FROM arena_vehicles_pool avp
             JOIN vehicles v ON avp.vehicle_id = v.id
             LEFT JOIN vehicle_brands vb ON v.brand_id = vb.id
             WHERE avp.is_available = TRUE AND avp.vehicle_id NOT IN (" . implode(',', $usedIds) . ")
             ORDER BY avp.category, avp.arena_power_rating DESC"
        );
    }

    /**
     * Fahrzeug picken
     */
    public function pickVehicle(int $matchId, int $coopId, int $vehicleId): array
    {
        $match = $this->db->fetchOne('SELECT * FROM arena_matches WHERE id = ?', [$matchId]);
        if (!$match || $match['status'] !== 'pick_ban') {
            return ['success' => false, 'message' => 'Nicht in Pick & Ban Phase'];
        }

        // Prüfe ob Fahrzeug bereits gepickt/gebannt
        $used = $this->db->fetchOne(
            'SELECT * FROM arena_picks_bans WHERE match_id = ? AND vehicle_id = ?',
            [$matchId, $vehicleId]
        );

        if ($used) {
            return ['success' => false, 'message' => 'Fahrzeug bereits ausgewählt oder gebannt'];
        }

        // Prüfe Pick-Limit (max 3 pro Team)
        $pickCount = $this->db->fetchColumn(
            'SELECT COUNT(*) FROM arena_picks_bans WHERE match_id = ? AND cooperative_id = ? AND action_type = "pick"',
            [$matchId, $coopId]
        );

        if ($pickCount >= 3) {
            return ['success' => false, 'message' => 'Maximale Anzahl Picks (3) erreicht'];
        }

        $order = $this->db->fetchColumn(
            'SELECT COALESCE(MAX(action_order), 0) + 1 FROM arena_picks_bans WHERE match_id = ?',
            [$matchId]
        );

        $this->db->insert('arena_picks_bans', [
            'match_id' => $matchId,
            'cooperative_id' => $coopId,
            'vehicle_id' => $vehicleId,
            'action_type' => 'pick',
            'action_order' => $order
        ]);

        return ['success' => true, 'message' => 'Fahrzeug gepickt'];
    }

    /**
     * Fahrzeug bannen
     */
    public function banVehicle(int $matchId, int $coopId, int $vehicleId): array
    {
        $match = $this->db->fetchOne('SELECT * FROM arena_matches WHERE id = ?', [$matchId]);
        if (!$match || $match['status'] !== 'pick_ban') {
            return ['success' => false, 'message' => 'Nicht in Pick & Ban Phase'];
        }

        $used = $this->db->fetchOne(
            'SELECT * FROM arena_picks_bans WHERE match_id = ? AND vehicle_id = ?',
            [$matchId, $vehicleId]
        );

        if ($used) {
            return ['success' => false, 'message' => 'Fahrzeug bereits verwendet'];
        }

        // Prüfe Ban-Limit (max 2 pro Team)
        $banCount = $this->db->fetchColumn(
            'SELECT COUNT(*) FROM arena_picks_bans WHERE match_id = ? AND cooperative_id = ? AND action_type = "ban"',
            [$matchId, $coopId]
        );

        if ($banCount >= 2) {
            return ['success' => false, 'message' => 'Maximale Anzahl Bans (2) erreicht'];
        }

        $order = $this->db->fetchColumn(
            'SELECT COALESCE(MAX(action_order), 0) + 1 FROM arena_picks_bans WHERE match_id = ?',
            [$matchId]
        );

        $this->db->insert('arena_picks_bans', [
            'match_id' => $matchId,
            'cooperative_id' => $coopId,
            'vehicle_id' => $vehicleId,
            'action_type' => 'ban',
            'action_order' => $order
        ]);

        return ['success' => true, 'message' => 'Fahrzeug gebannt'];
    }

    /**
     * Pick & Ban Status abrufen
     */
    public function getPickBanState(int $matchId): array
    {
        $actions = $this->db->fetchAll(
            "SELECT apb.*, v.name as vehicle_name, c.name as coop_name
             FROM arena_picks_bans apb
             JOIN vehicles v ON apb.vehicle_id = v.id
             JOIN cooperatives c ON apb.cooperative_id = c.id
             WHERE apb.match_id = ?
             ORDER BY apb.action_order",
            [$matchId]
        );

        $match = $this->db->fetchOne('SELECT * FROM arena_matches WHERE id = ?', [$matchId]);

        $challengerPicks = array_filter($actions, fn($a) => $a['cooperative_id'] == $match['challenger_cooperative_id'] && $a['action_type'] == 'pick');
        $defenderPicks = array_filter($actions, fn($a) => $a['cooperative_id'] == $match['defender_cooperative_id'] && $a['action_type'] == 'pick');
        $challengerBans = array_filter($actions, fn($a) => $a['cooperative_id'] == $match['challenger_cooperative_id'] && $a['action_type'] == 'ban');
        $defenderBans = array_filter($actions, fn($a) => $a['cooperative_id'] == $match['defender_cooperative_id'] && $a['action_type'] == 'ban');

        // Prüfe ob Pick & Ban abgeschlossen
        $isComplete = count($challengerPicks) >= 3 && count($defenderPicks) >= 3 && count($challengerBans) >= 2 && count($defenderBans) >= 2;

        return [
            'actions' => $actions,
            'challenger_picks' => array_values($challengerPicks),
            'defender_picks' => array_values($defenderPicks),
            'challenger_bans' => array_values($challengerBans),
            'defender_bans' => array_values($defenderBans),
            'is_complete' => $isComplete
        ];
    }

    /**
     * Rolle zuweisen
     */
    public function assignRole(int $matchId, int $farmId, string $role, int $coopId): array
    {
        $validRoles = ['harvest_specialist', 'bale_producer', 'transport'];
        if (!in_array($role, $validRoles)) {
            return ['success' => false, 'message' => 'Ungültige Rolle'];
        }

        // Prüfe ob Spieler bereits eine Rolle hat
        $existing = $this->db->fetchOne(
            'SELECT * FROM arena_participants WHERE match_id = ? AND farm_id = ?',
            [$matchId, $farmId]
        );

        if ($existing) {
            // Update Rolle
            $this->db->update('arena_participants', [
                'role' => $role
            ], 'id = :id', ['id' => $existing['id']]);
        } else {
            // Prüfe ob Rolle bereits vergeben
            $roleUsed = $this->db->fetchOne(
                'SELECT * FROM arena_participants WHERE match_id = ? AND cooperative_id = ? AND role = ?',
                [$matchId, $coopId, $role]
            );

            if ($roleUsed) {
                return ['success' => false, 'message' => 'Diese Rolle ist bereits vergeben'];
            }

            $this->db->insert('arena_participants', [
                'match_id' => $matchId,
                'farm_id' => $farmId,
                'cooperative_id' => $coopId,
                'role' => $role
            ]);
        }

        $roleNames = [
            'harvest_specialist' => 'Ernte-Spezialist',
            'bale_producer' => 'Ballen-Produzent',
            'transport' => 'Transport'
        ];

        return ['success' => true, 'message' => "Rolle '{$roleNames[$role]}' zugewiesen"];
    }

    /**
     * Teilnehmer bereit melden
     */
    public function setParticipantReady(int $matchId, int $farmId): array
    {
        $participant = $this->db->fetchOne(
            'SELECT * FROM arena_participants WHERE match_id = ? AND farm_id = ?',
            [$matchId, $farmId]
        );

        if (!$participant) {
            return ['success' => false, 'message' => 'Du bist kein Teilnehmer dieses Matches'];
        }

        $this->db->update('arena_participants', [
            'is_ready' => true
        ], 'id = :id', ['id' => $participant['id']]);

        // Prüfe ob alle bereit sind
        $this->checkMatchReady($matchId);

        return ['success' => true, 'message' => 'Bereit gemeldet'];
    }

    /**
     * Prüft ob Match starten kann
     */
    private function checkMatchReady(int $matchId): void
    {
        $match = $this->db->fetchOne('SELECT * FROM arena_matches WHERE id = ?', [$matchId]);

        // Prüfe ob 6 Teilnehmer (3 pro Team) bereit sind
        $readyCount = $this->db->fetchColumn(
            'SELECT COUNT(*) FROM arena_participants WHERE match_id = ? AND is_ready = TRUE',
            [$matchId]
        );

        if ($readyCount >= 6) {
            $this->db->update('arena_matches', [
                'status' => 'ready'
            ], 'id = :id', ['id' => $matchId]);
        }
    }

    /**
     * Match starten
     */
    public function startMatch(int $matchId): array
    {
        $match = $this->db->fetchOne('SELECT * FROM arena_matches WHERE id = ?', [$matchId]);
        if (!$match) {
            return ['success' => false, 'message' => 'Match nicht gefunden'];
        }

        if ($match['status'] !== 'ready') {
            return ['success' => false, 'message' => 'Match ist noch nicht bereit. Alle Teilnehmer müssen sich bereit melden.'];
        }

        $this->db->update('arena_matches', [
            'status' => 'in_progress',
            'started_at' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $matchId]);

        return ['success' => true, 'message' => 'Match gestartet!'];
    }

    /**
     * Aktion aufzeichnen
     */
    public function recordAction(int $matchId, int $farmId, string $actionType, int $amount): array
    {
        $participant = $this->db->fetchOne(
            'SELECT * FROM arena_participants WHERE match_id = ? AND farm_id = ?',
            [$matchId, $farmId]
        );

        if (!$participant) {
            return ['success' => false, 'message' => 'Teilnehmer nicht gefunden'];
        }

        $match = $this->db->fetchOne('SELECT * FROM arena_matches WHERE id = ?', [$matchId]);
        if ($match['status'] !== 'in_progress') {
            return ['success' => false, 'message' => 'Match läuft nicht'];
        }

        // Berechne Punkte basierend auf Aktion
        $basePoints = 0;
        $multiplier = 1.0;

        switch ($actionType) {
            case 'wheat_harvest':
                $basePoints = $amount; // 1 Punkt pro Einheit
                $this->db->update('arena_participants', [
                    'wheat_harvested' => $participant['wheat_harvested'] + $amount
                ], 'id = :id', ['id' => $participant['id']]);
                break;

            case 'bale_production':
                $basePoints = $amount * 10; // 10 Punkte pro Ballen
                $multiplier = $this->getScoreMultiplier($matchId, $participant['cooperative_id']);
                $this->db->update('arena_participants', [
                    'bales_produced' => $participant['bales_produced'] + $amount
                ], 'id = :id', ['id' => $participant['id']]);
                break;

            case 'transport_delivery':
                $basePoints = $amount * 15; // 15 Punkte pro Transport
                $multiplier = $this->getScoreMultiplier($matchId, $participant['cooperative_id']);
                $this->db->update('arena_participants', [
                    'transported_amount' => $participant['transported_amount'] + $amount
                ], 'id = :id', ['id' => $participant['id']]);
                break;

            default:
                return ['success' => false, 'message' => 'Unbekannte Aktion'];
        }

        $finalPoints = (int)($basePoints * $multiplier);

        $this->db->insert('arena_score_events', [
            'match_id' => $matchId,
            'participant_id' => $participant['id'],
            'event_type' => $actionType,
            'base_points' => $basePoints,
            'multiplier' => $multiplier,
            'final_points' => $finalPoints
        ]);

        // Aktualisiere Score
        $this->db->update('arena_participants', [
            'score_contribution' => $participant['score_contribution'] + $finalPoints
        ], 'id = :id', ['id' => $participant['id']]);

        return ['success' => true, 'points' => $finalPoints, 'multiplier' => $multiplier];
    }

    /**
     * Score-Multiplikator basierend auf Weizenernte
     */
    private function getScoreMultiplier(int $matchId, int $coopId): float
    {
        $totalWheat = $this->db->fetchColumn(
            'SELECT COALESCE(SUM(wheat_harvested), 0) FROM arena_participants WHERE match_id = ? AND cooperative_id = ?',
            [$matchId, $coopId]
        );

        // 1.0x Basis, +0.1x pro 100 Weizen, max 3.0x
        return min(3.0, 1.0 + ($totalWheat / 1000));
    }

    /**
     * Match beenden
     */
    public function endMatch(int $matchId): array
    {
        $match = $this->db->fetchOne('SELECT * FROM arena_matches WHERE id = ?', [$matchId]);
        if (!$match || $match['status'] !== 'in_progress') {
            return ['success' => false, 'message' => 'Match kann nicht beendet werden'];
        }

        // Berechne finale Scores
        $challengerScore = (int)$this->db->fetchColumn(
            'SELECT COALESCE(SUM(score_contribution), 0) FROM arena_participants WHERE match_id = ? AND cooperative_id = ?',
            [$matchId, $match['challenger_cooperative_id']]
        );

        $defenderScore = (int)$this->db->fetchColumn(
            'SELECT COALESCE(SUM(score_contribution), 0) FROM arena_participants WHERE match_id = ? AND cooperative_id = ?',
            [$matchId, $match['defender_cooperative_id']]
        );

        $winnerId = null;
        if ($challengerScore > $defenderScore) {
            $winnerId = $match['challenger_cooperative_id'];
        } elseif ($defenderScore > $challengerScore) {
            $winnerId = $match['defender_cooperative_id'];
        }

        $this->db->update('arena_matches', [
            'status' => 'finished',
            'finished_at' => date('Y-m-d H:i:s'),
            'challenger_score' => $challengerScore,
            'defender_score' => $defenderScore,
            'winner_cooperative_id' => $winnerId
        ], 'id = :id', ['id' => $matchId]);

        // Rankings aktualisieren
        $this->updateRankings($matchId);

        return [
            'success' => true,
            'challenger_score' => $challengerScore,
            'defender_score' => $defenderScore,
            'winner_id' => $winnerId
        ];
    }

    /**
     * Rankings aktualisieren
     */
    private function updateRankings(int $matchId): void
    {
        $match = $this->db->fetchOne('SELECT * FROM arena_matches WHERE id = ?', [$matchId]);

        foreach ([$match['challenger_cooperative_id'], $match['defender_cooperative_id']] as $coopId) {
            $isWinner = $match['winner_cooperative_id'] === $coopId;
            $isDraw = $match['winner_cooperative_id'] === null;

            $pointChange = $isDraw ? 5 : ($isWinner ? 25 : -15);

            // Upsert Ranking
            $existing = $this->db->fetchOne(
                'SELECT * FROM arena_rankings WHERE cooperative_id = ?',
                [$coopId]
            );

            if ($existing) {
                $this->db->update('arena_rankings', [
                    'total_matches' => $existing['total_matches'] + 1,
                    'wins' => $existing['wins'] + ($isWinner ? 1 : 0),
                    'losses' => $existing['losses'] + (!$isWinner && !$isDraw ? 1 : 0),
                    'draws' => $existing['draws'] + ($isDraw ? 1 : 0),
                    'ranking_points' => max(0, $existing['ranking_points'] + $pointChange),
                    'last_match_at' => date('Y-m-d H:i:s')
                ], 'cooperative_id = :id', ['id' => $coopId]);
            } else {
                $this->db->insert('arena_rankings', [
                    'cooperative_id' => $coopId,
                    'total_matches' => 1,
                    'wins' => $isWinner ? 1 : 0,
                    'losses' => !$isWinner && !$isDraw ? 1 : 0,
                    'draws' => $isDraw ? 1 : 0,
                    'ranking_points' => 1000 + $pointChange,
                    'last_match_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    /**
     * Arena-Rangliste abrufen
     */
    public function getArenaRankings(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT ar.*, c.name as cooperative_name
             FROM arena_rankings ar
             JOIN cooperatives c ON ar.cooperative_id = c.id
             ORDER BY ar.ranking_points DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Ausstehende Herausforderungen
     */
    public function getPendingChallenges(int $coopId): array
    {
        return $this->db->fetchAll(
            "SELECT am.*, c.name as challenger_name
             FROM arena_matches am
             JOIN cooperatives c ON am.challenger_cooperative_id = c.id
             WHERE am.defender_cooperative_id = ? AND am.status = 'pending'
             ORDER BY am.challenge_sent_at DESC",
            [$coopId]
        );
    }

    /**
     * Aktive Matches einer Genossenschaft
     */
    public function getActiveMatches(int $coopId): array
    {
        return $this->db->fetchAll(
            "SELECT am.*,
                    c1.name as challenger_name,
                    c2.name as defender_name
             FROM arena_matches am
             JOIN cooperatives c1 ON am.challenger_cooperative_id = c1.id
             JOIN cooperatives c2 ON am.defender_cooperative_id = c2.id
             WHERE (am.challenger_cooperative_id = ? OR am.defender_cooperative_id = ?)
               AND am.status IN ('pending', 'pick_ban', 'ready', 'in_progress')
             ORDER BY am.challenge_sent_at DESC",
            [$coopId, $coopId]
        );
    }

    /**
     * Match-Historie
     */
    public function getMatchHistory(int $coopId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT am.*,
                    c1.name as challenger_name,
                    c2.name as defender_name,
                    cw.name as winner_name
             FROM arena_matches am
             JOIN cooperatives c1 ON am.challenger_cooperative_id = c1.id
             JOIN cooperatives c2 ON am.defender_cooperative_id = c2.id
             LEFT JOIN cooperatives cw ON am.winner_cooperative_id = cw.id
             WHERE (am.challenger_cooperative_id = ? OR am.defender_cooperative_id = ?)
               AND am.status = 'finished'
             ORDER BY am.finished_at DESC
             LIMIT ?",
            [$coopId, $coopId, $limit]
        );
    }

    /**
     * Match-Details abrufen
     */
    public function getMatch(int $matchId): ?array
    {
        return $this->db->fetchOne(
            "SELECT am.*,
                    c1.name as challenger_name,
                    c2.name as defender_name
             FROM arena_matches am
             JOIN cooperatives c1 ON am.challenger_cooperative_id = c1.id
             JOIN cooperatives c2 ON am.defender_cooperative_id = c2.id
             WHERE am.id = ?",
            [$matchId]
        );
    }

    /**
     * Teilnehmer eines Matches
     */
    public function getParticipants(int $matchId): array
    {
        return $this->db->fetchAll(
            "SELECT ap.*, f.name as farm_name, u.username
             FROM arena_participants ap
             JOIN farms f ON ap.farm_id = f.id
             JOIN users u ON f.user_id = u.id
             WHERE ap.match_id = ?
             ORDER BY ap.cooperative_id, ap.role",
            [$matchId]
        );
    }
}
