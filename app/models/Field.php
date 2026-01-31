<?php
/**
 * Field Model
 *
 * Verwaltet Felder und den Anbau von Pflanzen.
 */
class Field
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Pflanzt eine Feldfrucht
     */
    public function plantCrop(int $fieldId, int $cropId, int $farmId): array
    {
        // Hole Feld
        $field = $this->db->fetchOne(
            'SELECT * FROM fields WHERE id = ? AND farm_id = ?',
            [$fieldId, $farmId]
        );

        if (!$field) {
            return ['success' => false, 'message' => 'Feld nicht gefunden'];
        }

        if ($field['status'] !== 'empty') {
            return ['success' => false, 'message' => 'Feld ist nicht leer'];
        }

        // Hole Crop
        $crop = $this->db->fetchOne('SELECT * FROM crops WHERE id = ?', [$cropId]);

        if (!$crop) {
            return ['success' => false, 'message' => 'Pflanze nicht gefunden'];
        }

        // Pruefe Forschungsanforderung
        if ($crop['required_research_id']) {
            $farm = new Farm($farmId);
            if (!$farm->hasResearch($crop['required_research_id'])) {
                return ['success' => false, 'message' => 'Forschung erforderlich'];
            }
        }

        // Berechne Kosten
        $totalCost = $crop['buy_price'] * $field['size_hectares'];

        // Pruefe und ziehe Geld ab
        $farm = new Farm($farmId);
        if (!$farm->subtractMoney($totalCost, "Aussaat: {$crop['name']}")) {
            return ['success' => false, 'message' => 'Nicht genuegend Geld'];
        }

        // Berechne Erntezeit
        $harvestTime = date('Y-m-d H:i:s', strtotime("+{$crop['growth_time_hours']} hours"));

        // Aktualisiere Feld
        $this->db->update('fields', [
            'current_crop_id' => $cropId,
            'planted_at' => date('Y-m-d H:i:s'),
            'harvest_ready_at' => $harvestTime,
            'status' => 'growing'
        ], 'id = :id', ['id' => $fieldId]);

        // Vergebe Punkte
        $farm->addPoints(POINTS_FIELD_WORK, "Feld bepflanzt: {$crop['name']}");

        Logger::info('Crop planted', [
            'farm_id' => $farmId,
            'field_id' => $fieldId,
            'crop' => $crop['name']
        ]);

        return [
            'success' => true,
            'message' => "{$crop['name']} erfolgreich gepflanzt!",
            'harvest_at' => $harvestTime
        ];
    }

    /**
     * Erntet ein Feld
     */
    public function harvest(int $fieldId, int $farmId): array
    {
        // Hole Feld mit Crop-Daten
        $field = $this->db->fetchOne(
            "SELECT f.*, c.name as crop_name, c.sell_price, c.yield_per_hectare
             FROM fields f
             JOIN crops c ON f.current_crop_id = c.id
             WHERE f.id = ? AND f.farm_id = ?",
            [$fieldId, $farmId]
        );

        if (!$field) {
            return ['success' => false, 'message' => 'Feld nicht gefunden'];
        }

        if ($field['status'] !== 'ready') {
            return ['success' => false, 'message' => 'Feld ist noch nicht bereit zur Ernte'];
        }

        // Berechne Ertrag (mit Bodenqualitaet)
        $qualityMultiplier = $field['soil_quality'] / 100;
        $baseYield = $field['yield_per_hectare'] * $field['size_hectares'];
        $actualYield = (int) ($baseYield * $qualityMultiplier);

        // Fuege zum Inventar hinzu
        $this->addToInventory($farmId, 'crop', $field['current_crop_id'], $field['crop_name'], $actualYield);

        // Reduziere Bodenqualitaet leicht
        $newSoilQuality = max(50, $field['soil_quality'] - 5);

        // Leere Feld
        $this->db->update('fields', [
            'current_crop_id' => null,
            'planted_at' => null,
            'harvest_ready_at' => null,
            'status' => 'empty',
            'soil_quality' => $newSoilQuality
        ], 'id = :id', ['id' => $fieldId]);

        // Vergebe Punkte
        $farm = new Farm($farmId);
        $farm->addPoints(POINTS_HARVEST, "Ernte: {$field['crop_name']}");

        // Berechne potentiellen Verkaufswert
        $value = $actualYield * $field['sell_price'];

        Logger::info('Field harvested', [
            'farm_id' => $farmId,
            'field_id' => $fieldId,
            'crop' => $field['crop_name'],
            'yield' => $actualYield
        ]);

        return [
            'success' => true,
            'message' => "{$actualYield} Einheiten {$field['crop_name']} geerntet!",
            'yield' => $actualYield,
            'value' => $value,
            'crop_name' => $field['crop_name']
        ];
    }

    /**
     * Fuegt Items zum Inventar hinzu
     */
    private function addToInventory(int $farmId, string $itemType, int $itemId, string $itemName, int $quantity): void
    {
        $existing = $this->db->fetchOne(
            'SELECT * FROM inventory WHERE farm_id = ? AND item_type = ? AND item_id = ?',
            [$farmId, $itemType, $itemId]
        );

        if ($existing) {
            $this->db->update('inventory', [
                'quantity' => $existing['quantity'] + $quantity
            ], 'id = :id', ['id' => $existing['id']]);
        } else {
            $this->db->insert('inventory', [
                'farm_id' => $farmId,
                'item_type' => $itemType,
                'item_id' => $itemId,
                'item_name' => $itemName,
                'quantity' => $quantity
            ]);
        }
    }

    /**
     * Gibt verfuegbare Pflanzen zurueck
     */
    public function getAvailableCrops(int $farmId): array
    {
        $sql = "SELECT c.*
                FROM crops c
                LEFT JOIN farm_research fr ON c.required_research_id = fr.research_id
                    AND fr.farm_id = ? AND fr.status = 'completed'
                WHERE c.required_research_id IS NULL
                   OR fr.id IS NOT NULL
                ORDER BY c.buy_price";
        return $this->db->fetchAll($sql, [$farmId]);
    }

    /**
     * Kauft ein neues Feld
     */
    public function buyField(int $farmId, float $sizeHectares): array
    {
        // Berechne Kosten (2000 pro Hektar)
        $pricePerHectare = 2000;
        $totalCost = $pricePerHectare * $sizeHectares;

        $farm = new Farm($farmId);

        if (!$farm->subtractMoney($totalCost, "Neues Feld ({$sizeHectares} ha)")) {
            return ['success' => false, 'message' => 'Nicht genuegend Geld'];
        }

        // Finde naechste Position
        $lastField = $this->db->fetchOne(
            'SELECT MAX(position_x) as max_x FROM fields WHERE farm_id = ?',
            [$farmId]
        );
        $posX = ($lastField['max_x'] ?? 0) + 100;

        // Erstelle Feld
        $fieldId = $this->db->insert('fields', [
            'farm_id' => $farmId,
            'size_hectares' => $sizeHectares,
            'position_x' => $posX,
            'position_y' => 0
        ]);

        $farm->addPoints(POINTS_BUILDING, 'Neues Feld gekauft');

        Logger::info('Field purchased', [
            'farm_id' => $farmId,
            'field_id' => $fieldId,
            'size' => $sizeHectares
        ]);

        return [
            'success' => true,
            'message' => "Neues Feld ({$sizeHectares} ha) gekauft!",
            'field_id' => $fieldId
        ];
    }

    /**
     * Gibt ein einzelnes Feld zurueck
     */
    public function getField(int $fieldId, int $farmId): ?array
    {
        return $this->db->fetchOne(
            "SELECT f.*, c.name as crop_name, c.growth_time_hours, c.sell_price, c.image_url as crop_image
             FROM fields f
             LEFT JOIN crops c ON f.current_crop_id = c.id
             WHERE f.id = ? AND f.farm_id = ?",
            [$fieldId, $farmId]
        );
    }

    /**
     * Aktualisiert Feldstatus (fuer Cron)
     */
    public static function updateReadyFields(): int
    {
        $db = Database::getInstance();

        $updated = $db->query(
            "UPDATE fields
             SET status = 'ready'
             WHERE harvest_ready_at <= NOW()
             AND status = 'growing'"
        )->rowCount();

        if ($updated > 0) {
            Logger::info('Fields updated to ready', ['count' => $updated]);
        }

        return $updated;
    }

    /**
     * Verbessert die Bodenqualitaet (Duengung)
     */
    public function fertilize(int $fieldId, int $farmId): array
    {
        $field = $this->db->fetchOne(
            'SELECT * FROM fields WHERE id = ? AND farm_id = ?',
            [$fieldId, $farmId]
        );

        if (!$field) {
            return ['success' => false, 'message' => 'Feld nicht gefunden'];
        }

        if ($field['soil_quality'] >= 100) {
            return ['success' => false, 'message' => 'Bodenqualitaet bereits maximal'];
        }

        // Kosten: 50 pro Punkt Verbesserung
        $improvement = min(20, 100 - $field['soil_quality']);
        $cost = $improvement * 50;

        $farm = new Farm($farmId);

        if (!$farm->subtractMoney($cost, 'Duengung')) {
            return ['success' => false, 'message' => 'Nicht genuegend Geld'];
        }

        $newQuality = $field['soil_quality'] + $improvement;

        $this->db->update('fields', ['soil_quality' => $newQuality], 'id = :id', ['id' => $fieldId]);

        return [
            'success' => true,
            'message' => "Bodenqualitaet auf {$newQuality}% verbessert",
            'new_quality' => $newQuality
        ];
    }
}
