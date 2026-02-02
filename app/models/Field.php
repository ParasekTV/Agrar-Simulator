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

        // Prüfe Forschungsanforderung
        if ($crop['required_research_id']) {
            $farm = new Farm($farmId);
            if (!$farm->hasResearch($crop['required_research_id'])) {
                return ['success' => false, 'message' => 'Forschung erforderlich'];
            }
        }

        // Berechne Kosten
        $totalCost = $crop['buy_price'] * $field['size_hectares'];

        // Prüfe und ziehe Geld ab
        $farm = new Farm($farmId);
        if (!$farm->subtractMoney($totalCost, "Aussaat: {$crop['name']}")) {
            return ['success' => false, 'message' => 'Nicht genügend Geld'];
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
        // Hole Feld mit Crop-Daten (inkl. pH-Werte)
        $field = $this->db->fetchOne(
            "SELECT f.*, c.name as crop_name, c.sell_price, c.yield_per_hectare,
                    c.optimal_ph_min, c.optimal_ph_max, c.ph_degradation
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

        // Berechne Ertrag (mit Bodenqualität, pH und Dünger)
        $qualityMultiplier = $field['soil_quality'] / 100;
        $phModifier = $this->calculatePhYieldModifier($field['soil_ph'] ?? 7.0, $field);
        $fertilizerMultiplier = $this->getFertilizerMultiplier($field);

        $baseYield = $field['yield_per_hectare'] * $field['size_hectares'];
        $actualYield = (int) ($baseYield * $qualityMultiplier * $phModifier * $fertilizerMultiplier);

        // Füge zum Inventar hinzu
        $this->addToInventory($farmId, 'crop', $field['current_crop_id'], $field['crop_name'], $actualYield);

        // Prüfe ob Bio-Dünger aktiv ist (verhindert Qualitätsverlust)
        $preventQualityLoss = $this->checkBioFertilizer($field);
        if ($preventQualityLoss) {
            $newSoilQuality = $field['soil_quality'];
        } else {
            $newSoilQuality = max(50, $field['soil_quality'] - 5);
        }

        // Reduziere pH-Wert basierend auf Pflanze
        $phDegradation = $field['ph_degradation'] ?? 0.2;
        $newPh = max(4.0, ($field['soil_ph'] ?? 7.0) - $phDegradation);

        // Leere Feld
        $this->db->update('fields', [
            'current_crop_id' => null,
            'planted_at' => null,
            'harvest_ready_at' => null,
            'status' => 'empty',
            'soil_quality' => $newSoilQuality,
            'soil_ph' => $newPh,
            'active_fertilizer_id' => null,
            'fertilizer_applied_at' => null,
            'fertilizer_expires_at' => null
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
            'yield' => $actualYield,
            'ph_modifier' => $phModifier,
            'fertilizer_modifier' => $fertilizerMultiplier
        ]);

        return [
            'success' => true,
            'message' => "{$actualYield} Einheiten {$field['crop_name']} geerntet!",
            'yield' => $actualYield,
            'value' => $value,
            'crop_name' => $field['crop_name'],
            'new_ph' => $newPh
        ];
    }

    /**
     * Berechnet pH-basierten Ertragsmodifikator
     */
    private function calculatePhYieldModifier(float $soilPh, array $crop): float
    {
        $optMin = $crop['optimal_ph_min'] ?? 6.0;
        $optMax = $crop['optimal_ph_max'] ?? 7.5;

        // Optimaler Bereich: 100% Ertrag
        if ($soilPh >= $optMin && $soilPh <= $optMax) {
            return 1.0;
        }

        // Akzeptabler Bereich (+-0.5): 90% Ertrag
        if ($soilPh >= ($optMin - 0.5) && $soilPh <= ($optMax + 0.5)) {
            return 0.9;
        }

        // Schlechter Bereich: 70% Ertrag
        return 0.7;
    }

    /**
     * Gibt Dünger-Ertragsmodifikator zurück
     */
    private function getFertilizerMultiplier(array $field): float
    {
        if (empty($field['active_fertilizer_id'])) {
            return 1.0;
        }

        // Prüfe ob Dünger noch aktiv ist (für Bio-Dünger)
        if (!empty($field['fertilizer_expires_at']) && strtotime($field['fertilizer_expires_at']) < time()) {
            return 1.0;
        }

        $fertilizer = $this->db->fetchOne(
            'SELECT yield_multiplier FROM fertilizer_types WHERE id = ?',
            [$field['active_fertilizer_id']]
        );

        return $fertilizer['yield_multiplier'] ?? 1.0;
    }

    /**
     * Prüft ob Bio-Dünger aktiv ist (verhindert Qualitätsverlust)
     */
    private function checkBioFertilizer(array $field): bool
    {
        if (empty($field['active_fertilizer_id'])) {
            return false;
        }

        $fertilizer = $this->db->fetchOne(
            'SELECT prevents_quality_loss FROM fertilizer_types WHERE id = ?',
            [$field['active_fertilizer_id']]
        );

        return (bool) ($fertilizer['prevents_quality_loss'] ?? false);
    }

    /**
     * Fügt Items zum Inventar hinzu
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
     * Gibt verfügbare Pflanzen zurück
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
            return ['success' => false, 'message' => 'Nicht genügend Geld'];
        }

        // Finde nächste Position
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
     * Gibt ein einzelnes Feld zurück
     */
    public function getField(int $fieldId, int $farmId): ?array
    {
        return $this->db->fetchOne(
            "SELECT f.*, c.name as crop_name, c.growth_time_hours, c.sell_price, c.image_url as crop_image,
                    c.optimal_ph_min, c.optimal_ph_max, c.category as crop_category,
                    ft.name as fertilizer_name, ft.yield_multiplier as fertilizer_yield_bonus
             FROM fields f
             LEFT JOIN crops c ON f.current_crop_id = c.id
             LEFT JOIN fertilizer_types ft ON f.active_fertilizer_id = ft.id
             WHERE f.id = ? AND f.farm_id = ?",
            [$fieldId, $farmId]
        );
    }

    /**
     * Aktualisiert Feldstatus (für Cron)
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
     * Verbessert die Bodenqualität (Basis-Düngung - Legacy)
     */
    public function fertilize(int $fieldId, int $farmId): array
    {
        // Nutze Basis-Dünger (ID 1)
        return $this->applyFertilizer($fieldId, 1, $farmId);
    }

    /**
     * Wendet erweiterten Dünger an
     */
    public function applyFertilizer(int $fieldId, int $fertilizerTypeId, int $farmId): array
    {
        $field = $this->db->fetchOne(
            'SELECT * FROM fields WHERE id = ? AND farm_id = ?',
            [$fieldId, $farmId]
        );

        if (!$field) {
            return ['success' => false, 'message' => 'Feld nicht gefunden'];
        }

        if ($field['status'] !== 'empty') {
            return ['success' => false, 'message' => 'Feld muss leer sein für Düngung'];
        }

        $fertilizer = $this->db->fetchOne('SELECT * FROM fertilizer_types WHERE id = ?', [$fertilizerTypeId]);

        if (!$fertilizer) {
            return ['success' => false, 'message' => 'Düngertyp nicht gefunden'];
        }

        // Prüfe Forschungsanforderung
        if ($fertilizer['required_research_id']) {
            $farm = new Farm($farmId);
            if (!$farm->hasResearch($fertilizer['required_research_id'])) {
                return ['success' => false, 'message' => 'Forschung erforderlich'];
            }
        }

        if ($field['soil_quality'] >= 100 && $fertilizer['yield_multiplier'] <= 1.0) {
            return ['success' => false, 'message' => 'Bodenqualität bereits maximal'];
        }

        // Berechne Kosten
        $totalCost = $fertilizer['cost_per_hectare'] * $field['size_hectares'];

        $farm = new Farm($farmId);
        if (!$farm->subtractMoney($totalCost, "Düngung: {$fertilizer['name']}")) {
            return ['success' => false, 'message' => 'Nicht genügend Geld'];
        }

        // Berechne neue Bodenqualität
        $newQuality = min(100, $field['soil_quality'] + $fertilizer['quality_boost']);

        // Setze Ablaufzeit für nicht-sofortige Dünger
        $expiresAt = null;
        if (!$fertilizer['instant_effect'] && $fertilizer['effect_duration_hours'] > 0) {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$fertilizer['effect_duration_hours']} hours"));
        }

        $this->db->update('fields', [
            'soil_quality' => $newQuality,
            'active_fertilizer_id' => $fertilizerTypeId,
            'fertilizer_applied_at' => date('Y-m-d H:i:s'),
            'fertilizer_expires_at' => $expiresAt
        ], 'id = :id', ['id' => $fieldId]);

        $yieldBonus = ($fertilizer['yield_multiplier'] - 1) * 100;
        $message = "Dünger '{$fertilizer['name']}' angewendet! Bodenqualität: {$newQuality}%";
        if ($yieldBonus > 0) {
            $message .= " (+{$yieldBonus}% Ertragsbonus)";
        }

        Logger::info('Fertilizer applied', [
            'farm_id' => $farmId,
            'field_id' => $fieldId,
            'fertilizer' => $fertilizer['name']
        ]);

        return [
            'success' => true,
            'message' => $message,
            'new_quality' => $newQuality,
            'yield_bonus' => $yieldBonus
        ];
    }

    /**
     * Wendet Kalk an (pH-Korrektur)
     */
    public function applyLime(int $fieldId, int $limeTypeId, int $farmId): array
    {
        $field = $this->db->fetchOne(
            'SELECT * FROM fields WHERE id = ? AND farm_id = ?',
            [$fieldId, $farmId]
        );

        if (!$field) {
            return ['success' => false, 'message' => 'Feld nicht gefunden'];
        }

        $lime = $this->db->fetchOne('SELECT * FROM lime_types WHERE id = ?', [$limeTypeId]);

        if (!$lime) {
            return ['success' => false, 'message' => 'Kalktyp nicht gefunden'];
        }

        // Prüfe Forschungsanforderung
        if ($lime['required_research_id']) {
            $farm = new Farm($farmId);
            if (!$farm->hasResearch($lime['required_research_id'])) {
                return ['success' => false, 'message' => 'Forschung erforderlich: Bodenkunde'];
            }
        }

        $currentPh = $field['soil_ph'] ?? 7.0;
        if ($currentPh >= 8.0) {
            return ['success' => false, 'message' => 'pH-Wert bereits maximal (8.0)'];
        }

        // Berechne Kosten
        $totalCost = $lime['cost_per_hectare'] * $field['size_hectares'];

        $farm = new Farm($farmId);
        if (!$farm->subtractMoney($totalCost, "Kalkung: {$lime['name']}")) {
            return ['success' => false, 'message' => 'Nicht genügend Geld'];
        }

        // Erhöhe pH (max 8.0)
        $newPh = min(8.0, $currentPh + $lime['ph_increase']);

        $this->db->update('fields', [
            'soil_ph' => $newPh,
            'last_limed_at' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $fieldId]);

        Logger::info('Lime applied', [
            'farm_id' => $farmId,
            'field_id' => $fieldId,
            'lime' => $lime['name'],
            'new_ph' => $newPh
        ]);

        return [
            'success' => true,
            'message' => "Boden gekalkt! pH-Wert auf " . number_format($newPh, 1) . " erhöht",
            'new_ph' => $newPh
        ];
    }

    /**
     * Gibt verfügbare Düngertypen für eine Farm zurück
     */
    public function getAvailableFertilizers(int $farmId): array
    {
        $sql = "SELECT ft.*
                FROM fertilizer_types ft
                LEFT JOIN farm_research fr ON ft.required_research_id = fr.research_id
                    AND fr.farm_id = ? AND fr.status = 'completed'
                WHERE ft.required_research_id IS NULL
                   OR fr.id IS NOT NULL
                ORDER BY ft.cost_per_hectare";
        return $this->db->fetchAll($sql, [$farmId]);
    }

    /**
     * Gibt verfügbare Kalktypen für eine Farm zurück
     */
    public function getAvailableLimeTypes(int $farmId): array
    {
        $sql = "SELECT lt.*
                FROM lime_types lt
                LEFT JOIN farm_research fr ON lt.required_research_id = fr.research_id
                    AND fr.farm_id = ? AND fr.status = 'completed'
                WHERE lt.required_research_id IS NULL
                   OR fr.id IS NOT NULL
                ORDER BY lt.cost_per_hectare";
        return $this->db->fetchAll($sql, [$farmId]);
    }
}
