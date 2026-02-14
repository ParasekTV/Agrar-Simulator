<?php
/**
 * Field Model Extension v2.0
 * Automatisch generiert - Neue Methoden für Grubbern, Pflügen, Wiesen, Gewächshäuser, Unkraut
 *
 * Diese Methoden in Field.php einfügen oder als Trait verwenden
 */

trait FieldExtension
{
    /**
     * Grubbern oder Pflügen durchführen
     */
    public function cultivate(int $fieldId, string $cultivationType, int $farmId): array
    {
        $field = $this->getField($fieldId);
        if (!$field || $field['farm_id'] !== $farmId) {
            return ['success' => false, 'message' => 'Feld nicht gefunden'];
        }

        if ($field['status'] !== 'empty') {
            return ['success' => false, 'message' => 'Feld muss leer sein zum Bearbeiten'];
        }

        // Hole Bodenbearbeitungs-Typ
        $cultivation = $this->db->fetchOne(
            'SELECT * FROM cultivation_types WHERE type = ?',
            [$cultivationType]
        );

        if (!$cultivation) {
            return ['success' => false, 'message' => 'Unbekannter Bearbeitungstyp'];
        }

        $cost = $cultivation['cost_per_hectare'] * $field['size_hectares'];

        // Prüfe Geld
        $farm = new Farm($farmId);
        if ($farm->getMoney() < $cost) {
            return ['success' => false, 'message' => 'Nicht genug Geld'];
        }

        // Ziehe Geld ab
        $farm->subtractMoney($cost, "Bodenbearbeitung: {$cultivation['name_de']}");

        // Aktualisiere Feld
        $newQuality = min(100, $field['soil_quality'] + $cultivation['soil_quality_boost']);
        $this->db->update('fields', [
            'cultivation_type' => $cultivationType,
            'last_cultivated_at' => date('Y-m-d H:i:s'),
            'needs_cultivation' => false,
            'soil_quality' => $newQuality
        ], 'id = :id', ['id' => $fieldId]);

        return [
            'success' => true,
            'message' => "{$cultivation['name_de']} abgeschlossen. Bodenqualität: {$newQuality}%"
        ];
    }

    /**
     * Herbizid anwenden
     */
    public function applyHerbicide(int $fieldId, int $herbicideId, int $farmId): array
    {
        $field = $this->getField($fieldId);
        if (!$field || $field['farm_id'] !== $farmId) {
            return ['success' => false, 'message' => 'Feld nicht gefunden'];
        }

        if ($field['weed_level'] <= 0) {
            return ['success' => false, 'message' => 'Kein Unkraut vorhanden'];
        }

        $herbicide = $this->db->fetchOne('SELECT * FROM herbicide_types WHERE id = ?', [$herbicideId]);
        if (!$herbicide) {
            return ['success' => false, 'message' => 'Herbizid nicht gefunden'];
        }

        $cost = $herbicide['cost_per_hectare'] * $field['size_hectares'];

        $farm = new Farm($farmId);
        if ($farm->getMoney() < $cost) {
            return ['success' => false, 'message' => 'Nicht genug Geld'];
        }

        $farm->subtractMoney($cost, "Herbizid: {$herbicide['name_de']}");

        // Reduziere Unkraut
        $reduction = ($field['weed_level'] * $herbicide['effectiveness']) / 100;
        $newWeedLevel = max(0, $field['weed_level'] - $reduction);

        $this->db->update('fields', [
            'weed_level' => $newWeedLevel,
            'weed_appeared_at' => $newWeedLevel > 0 ? $field['weed_appeared_at'] : null
        ], 'id = :id', ['id' => $fieldId]);

        return [
            'success' => true,
            'message' => "Unkraut um {$herbicide['effectiveness']}% reduziert"
        ];
    }

    /**
     * Wiese mähen
     */
    public function mowMeadow(int $fieldId, int $farmId): array
    {
        $field = $this->getField($fieldId);
        if (!$field || $field['farm_id'] !== $farmId) {
            return ['success' => false, 'message' => 'Wiese nicht gefunden'];
        }

        if ($field['field_type'] !== 'meadow') {
            return ['success' => false, 'message' => 'Dies ist keine Wiese'];
        }

        if ($field['status'] !== 'ready') {
            return ['success' => false, 'message' => 'Gras ist noch nicht bereit zum Mähen'];
        }

        // Berechne Ertrag (Gras)
        $baseYield = 1000 * $field['size_hectares'];
        $qualityMultiplier = $field['soil_quality'] / 100;
        $actualYield = (int)($baseYield * $qualityMultiplier);

        // Füge Gras zum Inventar hinzu
        $farm = new Farm($farmId);
        $farm->addToInventory('crop', 'Gras', $actualYield);

        // Setze Feld zurück und starte neues Wachstum
        $this->db->update('fields', [
            'status' => 'growing',
            'growth_stage' => 0,
            'planted_at' => date('Y-m-d H:i:s'),
            'harvest_ready_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ], 'id = :id', ['id' => $fieldId]);

        return [
            'success' => true,
            'message' => "{$actualYield}x Gras geerntet"
        ];
    }

    /**
     * Prüft Feld-Limits beim Kauf
     */
    public function canBuyField(int $farmId, string $fieldType, float $size): bool
    {
        // Hole Limit
        $limit = $this->db->fetchOne(
            'SELECT max_count FROM field_limits WHERE field_type = ? AND size_hectares = ?',
            [$fieldType, $size]
        );

        if (!$limit) {
            return false;
        }

        // Zähle aktuelle Felder
        $currentCount = $this->db->fetchColumn(
            'SELECT COUNT(*) FROM fields WHERE farm_id = ? AND field_type = ? AND size_hectares = ?',
            [$farmId, $fieldType, $size]
        );

        return $currentCount < $limit['max_count'];
    }

    /**
     * Kauft eine Wiese
     */
    public function buyMeadow(int $farmId, float $size): array
    {
        if (!$this->canBuyField($farmId, 'meadow', $size)) {
            return ['success' => false, 'message' => 'Maximale Anzahl Wiesen dieser Größe erreicht'];
        }

        $limit = $this->db->fetchOne(
            'SELECT price_per_hectare FROM field_limits WHERE field_type = ? AND size_hectares = ?',
            ['meadow', $size]
        );

        $cost = $limit['price_per_hectare'] * $size;

        $farm = new Farm($farmId);
        if ($farm->getMoney() < $cost) {
            return ['success' => false, 'message' => 'Nicht genug Geld'];
        }

        $farm->subtractMoney($cost, "Wiese kaufen ({$size} ha)");

        $this->db->insert('fields', [
            'farm_id' => $farmId,
            'field_type' => 'meadow',
            'size_hectares' => $size,
            'status' => 'growing',
            'soil_quality' => 100,
            'planted_at' => date('Y-m-d H:i:s'),
            'harvest_ready_at' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ]);

        return ['success' => true, 'message' => "Wiese ({$size} ha) gekauft!"];
    }

    /**
     * Kauft ein Gewächshaus
     */
    public function buyGreenhouse(int $farmId, float $size): array
    {
        if (!$this->canBuyField($farmId, 'greenhouse', $size)) {
            return ['success' => false, 'message' => 'Maximale Anzahl Gewächshäuser dieser Größe erreicht'];
        }

        $limit = $this->db->fetchOne(
            'SELECT price_per_hectare FROM field_limits WHERE field_type = ? AND size_hectares = ?',
            ['greenhouse', $size]
        );

        $cost = $limit['price_per_hectare'] * $size;

        $farm = new Farm($farmId);
        if ($farm->getMoney() < $cost) {
            return ['success' => false, 'message' => 'Nicht genug Geld'];
        }

        $farm->subtractMoney($cost, "Gewächshaus kaufen ({$size} ha)");

        $this->db->insert('fields', [
            'farm_id' => $farmId,
            'field_type' => 'greenhouse',
            'size_hectares' => $size,
            'status' => 'empty',
            'soil_quality' => 100
        ]);

        return ['success' => true, 'message' => "Gewächshaus ({$size} ha) gekauft!"];
    }

    /**
     * Aktualisiert Wachstumsstufen (Cron)
     */
    public static function updateGrowthStages(): int
    {
        $db = Database::getInstance();
        $now = time();

        $fields = $db->fetchAll(
            "SELECT f.*, c.growth_time_hours
             FROM fields f
             LEFT JOIN crops c ON f.current_crop_id = c.id
             WHERE f.status = 'growing'"
        );

        $updated = 0;
        foreach ($fields as $field) {
            $plantedAt = strtotime($field['planted_at']);
            $growthHours = $field['growth_time_hours'] ?? 24;
            $totalGrowthTime = $growthHours * 3600;
            $elapsed = $now - $plantedAt;
            $progress = min(1, $elapsed / $totalGrowthTime);

            $maxStages = $field['max_growth_stages'] ?? 4;
            $currentStage = (int)floor($progress * $maxStages);

            if ($currentStage !== (int)$field['growth_stage']) {
                $db->update('fields', ['growth_stage' => $currentStage], 'id = :id', ['id' => $field['id']]);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Zufälliges Unkraut-Wachstum (Cron)
     */
    public static function checkWeedGrowth(): int
    {
        $db = Database::getInstance();

        // 10% Chance pro wachsendem Feld pro Check (nur normale Felder, keine Gewächshäuser)
        $fields = $db->fetchAll(
            "SELECT * FROM fields
             WHERE status = 'growing'
               AND weed_level < 100
               AND field_type = 'field'"
        );

        $affected = 0;
        foreach ($fields as $field) {
            if (rand(1, 100) <= 10) {
                $newWeedLevel = min(100, $field['weed_level'] + rand(5, 15));
                $db->update('fields', [
                    'weed_level' => $newWeedLevel,
                    'weed_appeared_at' => $field['weed_appeared_at'] ?? date('Y-m-d H:i:s')
                ], 'id = :id', ['id' => $field['id']]);
                $affected++;
            }
        }

        return $affected;
    }

    /**
     * Gibt alle Wiesen einer Farm zurück
     */
    public function getMeadows(int $farmId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM fields WHERE farm_id = ? AND field_type = 'meadow' ORDER BY id",
            [$farmId]
        );
    }

    /**
     * Gibt alle Gewächshäuser einer Farm zurück
     */
    public function getGreenhouses(int $farmId): array
    {
        return $this->db->fetchAll(
            "SELECT f.*, c.name as crop_name
             FROM fields f
             LEFT JOIN crops c ON f.current_crop_id = c.id
             WHERE f.farm_id = ? AND f.field_type = 'greenhouse'
             ORDER BY f.id",
            [$farmId]
        );
    }

    /**
     * Gibt Gewächshaus-Pflanzen zurück
     */
    public function getGreenhouseCrops(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM crops WHERE is_greenhouse_only = TRUE ORDER BY name"
        );
    }

    /**
     * Gibt verfügbare Herbizide zurück
     */
    public function getAvailableHerbicides(int $farmId): array
    {
        $farm = new Farm($farmId);
        $researchIds = array_column($farm->getCompletedResearch(), 'research_id');
        $researchIds[] = 0; // Für NULL-Werte

        return $this->db->fetchAll(
            "SELECT * FROM herbicide_types
             WHERE required_research_id IS NULL
                OR required_research_id IN (" . implode(',', $researchIds) . ")
             ORDER BY cost_per_hectare"
        );
    }

    /**
     * Gibt Bodenbearbeitungs-Typen zurück
     */
    public function getCultivationTypes(): array
    {
        return $this->db->fetchAll("SELECT * FROM cultivation_types ORDER BY cost_per_hectare");
    }

    /**
     * Gibt Feld-Limits zurück
     */
    public function getFieldLimits(string $fieldType = null): array
    {
        $sql = "SELECT * FROM field_limits";
        $params = [];

        if ($fieldType) {
            $sql .= " WHERE field_type = ?";
            $params[] = $fieldType;
        }

        $sql .= " ORDER BY field_type, size_hectares";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Setzt needs_cultivation nach Ernte
     */
    public function setNeedsCultivation(int $fieldId): void
    {
        $this->db->update('fields', [
            'needs_cultivation' => true,
            'cultivation_type' => 'none'
        ], 'id = :id', ['id' => $fieldId]);
    }
}
