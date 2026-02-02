<?php
/**
 * Farm Model
 *
 * Verwaltet die Hauptlogik einer Farm.
 */
class Farm
{
    private Database $db;
    private int $farmId;
    private ?array $data = null;

    public function __construct(int $farmId)
    {
        $this->db = Database::getInstance();
        $this->farmId = $farmId;
        $this->loadFarmData();
    }

    /**
     * Lädt die Farm-Daten aus der Datenbank
     */
    private function loadFarmData(): void
    {
        $sql = "SELECT f.*, u.username
                FROM farms f
                JOIN users u ON f.user_id = u.id
                WHERE f.id = ?";
        $this->data = $this->db->fetchOne($sql, [$this->farmId]);
    }

    /**
     * Gibt die Farm-Daten zurück
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Gibt die Farm-ID zurück
     */
    public function getId(): int
    {
        return $this->farmId;
    }

    /**
     * Fügt Geld hinzu
     */
    public function addMoney(float $amount, string $reason = ''): float
    {
        $newMoney = $this->data['money'] + $amount;

        $this->db->update('farms', ['money' => $newMoney], 'id = :id', ['id' => $this->farmId]);

        $this->logEvent($amount > 0 ? 'sale' : 'purchase', $reason, 0, $amount);
        $this->updateRanking();
        $this->loadFarmData();

        return $newMoney;
    }

    /**
     * Zieht Geld ab
     */
    public function subtractMoney(float $amount, string $reason = ''): bool
    {
        if ($this->data['money'] < $amount) {
            return false;
        }

        $this->addMoney(-$amount, $reason);
        return true;
    }

    /**
     * Prüft ob genügend Geld vorhanden ist
     */
    public function hasMoney(float $amount): bool
    {
        return $this->data['money'] >= $amount;
    }

    /**
     * Fügt Punkte hinzu
     */
    public function addPoints(int $points, string $reason = ''): int
    {
        $newPoints = $this->data['points'] + $points;

        $this->db->update('farms', ['points' => $newPoints], 'id = :id', ['id' => $this->farmId]);

        $this->logEvent('points', $reason, $points, 0);
        $this->checkLevelUp();
        $this->updateRanking();
        $this->loadFarmData();

        return $newPoints;
    }

    /**
     * Prüft und führt Level-Up durch
     */
    private function checkLevelUp(): void
    {
        $this->loadFarmData();
        $currentLevel = $this->data['level'];
        $requiredPoints = $currentLevel * POINTS_PER_LEVEL_MULTIPLIER;

        if ($this->data['points'] >= $requiredPoints) {
            $newLevel = $currentLevel + 1;

            $this->db->update('farms', ['level' => $newLevel], 'id = :id', ['id' => $this->farmId]);

            // Level-Up Bonus
            $this->db->query(
                'UPDATE farms SET money = money + ?, points = points + ? WHERE id = ?',
                [1000 * $newLevel, POINTS_LEVEL_UP_BONUS, $this->farmId]
            );

            $this->logEvent('level_up', "Level {$newLevel} erreicht!", POINTS_LEVEL_UP_BONUS, 1000 * $newLevel);

            Logger::info('Farm leveled up', ['farm_id' => $this->farmId, 'new_level' => $newLevel]);
        }
    }

    /**
     * Aktualisiert das Ranking
     */
    private function updateRanking(): void
    {
        $this->loadFarmData();

        $this->db->query(
            'INSERT INTO rankings (farm_id, total_points, total_money)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE total_points = ?, total_money = ?',
            [
                $this->farmId,
                $this->data['points'],
                $this->data['money'],
                $this->data['points'],
                $this->data['money']
            ]
        );
    }

    /**
     * Gibt alle Felder der Farm zurück
     */
    public function getFields(): array
    {
        $sql = "SELECT f.*, c.name as crop_name, c.growth_time_hours, c.sell_price, c.image_url as crop_image
                FROM fields f
                LEFT JOIN crops c ON f.current_crop_id = c.id
                WHERE f.farm_id = ?
                ORDER BY f.id";
        return $this->db->fetchAll($sql, [$this->farmId]);
    }

    /**
     * Gibt alle Tiere der Farm zurück
     */
    public function getAnimals(): array
    {
        $sql = "SELECT fa.*, a.name, a.type, a.production_item, a.production_time_hours,
                       a.production_quantity, a.feed_cost, a.image_url
                FROM farm_animals fa
                JOIN animals a ON fa.animal_id = a.id
                WHERE fa.farm_id = ?";
        return $this->db->fetchAll($sql, [$this->farmId]);
    }

    /**
     * Gibt alle Fahrzeuge der Farm zurück
     */
    public function getVehicles(): array
    {
        $sql = "SELECT fv.*, v.name, v.type, v.efficiency_bonus, v.fuel_consumption, v.image_url
                FROM farm_vehicles fv
                JOIN vehicles v ON fv.vehicle_id = v.id
                WHERE fv.farm_id = ?";
        return $this->db->fetchAll($sql, [$this->farmId]);
    }

    /**
     * Gibt alle Gebäude der Farm zurück
     */
    public function getBuildings(): array
    {
        $sql = "SELECT fb.*, b.name, b.type, b.storage_capacity, b.production_bonus, b.image_url
                FROM farm_buildings fb
                JOIN buildings b ON fb.building_id = b.id
                WHERE fb.farm_id = ?";
        return $this->db->fetchAll($sql, [$this->farmId]);
    }

    /**
     * Gibt das Inventar der Farm zurück
     */
    public function getInventory(): array
    {
        $sql = "SELECT * FROM inventory WHERE farm_id = ? ORDER BY item_type, item_name";
        return $this->db->fetchAll($sql, [$this->farmId]);
    }

    /**
     * Gibt die abgeschlossenen Forschungen zurück
     */
    public function getCompletedResearch(): array
    {
        $sql = "SELECT fr.*, rt.name, rt.description, rt.category
                FROM farm_research fr
                JOIN research_tree rt ON fr.research_id = rt.id
                WHERE fr.farm_id = ? AND fr.status = 'completed'";
        return $this->db->fetchAll($sql, [$this->farmId]);
    }

    /**
     * Gibt die aktive Forschung zurück
     */
    public function getActiveResearch(): ?array
    {
        $sql = "SELECT fr.*, rt.name, rt.description, rt.category, rt.research_time_hours
                FROM farm_research fr
                JOIN research_tree rt ON fr.research_id = rt.id
                WHERE fr.farm_id = ? AND fr.status = 'in_progress'
                LIMIT 1";
        return $this->db->fetchOne($sql, [$this->farmId]);
    }

    /**
     * Prüft ob eine Forschung abgeschlossen ist
     */
    public function hasResearch(int $researchId): bool
    {
        return $this->db->exists(
            'farm_research',
            'farm_id = ? AND research_id = ? AND status = ?',
            [$this->farmId, $researchId, 'completed']
        );
    }

    /**
     * Gibt die letzten Events zurück
     */
    public function getRecentEvents(int $limit = 10): array
    {
        $sql = "SELECT * FROM game_events
                WHERE farm_id = ?
                ORDER BY created_at DESC
                LIMIT ?";
        return $this->db->fetchAll($sql, [$this->farmId, $limit]);
    }

    /**
     * Gibt Statistiken der Farm zurück
     */
    public function getStats(): array
    {
        $fieldsCount = $this->db->count('fields', 'farm_id = ?', [$this->farmId]);
        $fieldsGrowing = $this->db->count('fields', 'farm_id = ? AND status = ?', [$this->farmId, 'growing']);
        $fieldsReady = $this->db->count('fields', 'farm_id = ? AND status = ?', [$this->farmId, 'ready']);
        $animalsCount = $this->db->fetchColumn(
            'SELECT SUM(quantity) FROM farm_animals WHERE farm_id = ?',
            [$this->farmId]
        ) ?? 0;
        $vehiclesCount = $this->db->count('farm_vehicles', 'farm_id = ?', [$this->farmId]);
        $buildingsCount = $this->db->count('farm_buildings', 'farm_id = ?', [$this->farmId]);

        $rank = $this->db->fetchOne(
            'SELECT rank_position FROM rankings WHERE farm_id = ?',
            [$this->farmId]
        );

        return [
            'money' => $this->data['money'],
            'points' => $this->data['points'],
            'level' => $this->data['level'],
            'fields_total' => $fieldsCount,
            'fields_growing' => $fieldsGrowing,
            'fields_ready' => $fieldsReady,
            'animals' => $animalsCount,
            'vehicles' => $vehiclesCount,
            'buildings' => $buildingsCount,
            'rank' => $rank['rank_position'] ?? 0
        ];
    }

    /**
     * Loggt ein Event
     */
    public function logEvent(string $type, string $description, int $points = 0, float $moneyChange = 0): void
    {
        $this->db->insert('game_events', [
            'farm_id' => $this->farmId,
            'event_type' => $type,
            'description' => $description,
            'points_earned' => $points,
            'money_change' => $moneyChange
        ]);
    }

    /**
     * Berechnet die Gesamtlagerkapazität
     */
    public function getTotalStorageCapacity(): int
    {
        $sql = "SELECT SUM(b.storage_capacity)
                FROM farm_buildings fb
                JOIN buildings b ON fb.building_id = b.id
                WHERE fb.farm_id = ?";
        return (int) ($this->db->fetchColumn($sql, [$this->farmId]) ?? 1000);
    }

    /**
     * Berechnet den aktuellen Lagerbestand
     */
    public function getCurrentStorageUsed(): int
    {
        $sql = "SELECT SUM(quantity) FROM inventory WHERE farm_id = ?";
        return (int) ($this->db->fetchColumn($sql, [$this->farmId]) ?? 0);
    }

    /**
     * Gibt benötigte Punkte für nächstes Level zurück
     */
    public function getPointsForNextLevel(): int
    {
        return $this->data['level'] * POINTS_PER_LEVEL_MULTIPLIER;
    }

    /**
     * Findet eine Farm nach ID
     */
    public static function findById(int $farmId): ?self
    {
        $db = Database::getInstance();
        $exists = $db->exists('farms', 'id = ?', [$farmId]);

        if (!$exists) {
            return null;
        }

        return new self($farmId);
    }

    /**
     * Findet eine Farm nach User-ID
     */
    public static function findByUserId(int $userId): ?self
    {
        $db = Database::getInstance();
        $farm = $db->fetchOne('SELECT id FROM farms WHERE user_id = ?', [$userId]);

        if (!$farm) {
            return null;
        }

        return new self($farm['id']);
    }
}
