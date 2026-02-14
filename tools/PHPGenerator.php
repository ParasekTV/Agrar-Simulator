<?php
/**
 * PHP Code Generator
 *
 * Generiert PHP-Models, Controller, Views und Crons
 */

class PHPGenerator
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Generiert Model-Erweiterungen
     */
    public function generateModelExtension(string $feature, array $def, array $parsedFeature): string
    {
        switch ($feature) {
            case 'fields':
                return $this->generateFieldModelExtension();
            case 'animals':
                return $this->generateAnimalModelExtension();
            case 'vehicles':
                return $this->generateVehicleModelExtension();
            case 'arena':
                return $this->generateArenaModel();
            default:
                return "<?php\n// Keine Erweiterung für $feature\n";
        }
    }

    /**
     * Generiert Controller-Erweiterungen
     */
    public function generateControllerExtension(string $feature, array $def, array $parsedFeature): string
    {
        switch ($feature) {
            case 'fields':
                return $this->generateFieldControllerExtension();
            case 'animals':
                return $this->generateAnimalControllerExtension();
            case 'vehicles':
                return $this->generateVehicleControllerExtension();
            case 'arena':
                return $this->generateArenaController();
            default:
                return "<?php\n// Keine Erweiterung für $feature\n";
        }
    }

    // ==================== FIELD EXTENSIONS ====================

    private function generateFieldModelExtension(): string
    {
        return <<<'PHP'
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

        // Setze Feld zurück
        $this->db->update('fields', [
            'status' => 'empty',
            'growth_stage' => 0,
            'planted_at' => null,
            'harvest_ready_at' => null
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
     * Aktualisiert Wachstumsstufen (Cron)
     */
    public static function updateGrowthStages(): int
    {
        $db = Database::getInstance();
        $now = time();

        $fields = $db->fetchAll(
            "SELECT f.*, c.growth_time_hours, c.growth_stages
             FROM fields f
             JOIN crops c ON f.current_crop_id = c.id
             WHERE f.status = 'growing'"
        );

        $updated = 0;
        foreach ($fields as $field) {
            $plantedAt = strtotime($field['planted_at']);
            $totalGrowthTime = $field['growth_time_hours'] * 3600;
            $elapsed = $now - $plantedAt;
            $progress = min(1, $elapsed / $totalGrowthTime);

            $maxStages = $field['growth_stages'] ?? 4;
            $currentStage = (int)floor($progress * $maxStages);

            if ($currentStage !== $field['growth_stage']) {
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

        // 10% Chance pro wachsendem Feld pro Check
        $fields = $db->fetchAll("SELECT * FROM fields WHERE status = 'growing' AND weed_level < 100");

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
            "SELECT * FROM fields WHERE farm_id = ? AND field_type = 'greenhouse' ORDER BY id",
            [$farmId]
        );
    }

    /**
     * Gibt verfügbare Herbizide zurück
     */
    public function getAvailableHerbicides(int $farmId): array
    {
        $farm = new Farm($farmId);
        $researchIds = array_column($farm->getCompletedResearch(), 'research_id');

        return $this->db->fetchAll(
            "SELECT * FROM herbicide_types
             WHERE required_research_id IS NULL
                OR required_research_id IN (" . implode(',', $researchIds ?: [0]) . ")
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
}
PHP;
    }

    private function generateFieldControllerExtension(): string
    {
        return <<<'PHP'
<?php
/**
 * FieldController Extension v2.0
 * Automatisch generiert - Neue Routen für Grubbern, Pflügen, Wiesen, Gewächshäuser, Unkraut
 *
 * Diese Methoden in FieldController.php einfügen
 */

trait FieldControllerExtension
{
    /**
     * Zeigt Wiesen
     */
    public function meadows(): void
    {
        $this->requireAuth();
        $fieldModel = new Field();

        $data = [
            'title' => 'Wiesen',
            'meadows' => $fieldModel->getMeadows($this->getFarmId()),
            'fieldLimits' => $fieldModel->getFieldLimits('meadow')
        ];

        $this->renderWithLayout('fields/meadows', $data);
    }

    /**
     * Zeigt Gewächshäuser
     */
    public function greenhouses(): void
    {
        $this->requireAuth();
        $fieldModel = new Field();

        $data = [
            'title' => 'Gewächshäuser',
            'greenhouses' => $fieldModel->getGreenhouses($this->getFarmId()),
            'greenhouseCrops' => $fieldModel->getGreenhouseCrops(),
            'fieldLimits' => $fieldModel->getFieldLimits('greenhouse')
        ];

        $this->renderWithLayout('fields/greenhouses', $data);
    }

    /**
     * Grubbern/Pflügen (POST)
     */
    public function cultivate(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/fields');
        }

        $data = $this->getPostData();
        $fieldId = (int)($data['field_id'] ?? 0);
        $cultivationType = $data['cultivation_type'] ?? '';

        $fieldModel = new Field();
        $result = $fieldModel->cultivate($fieldId, $cultivationType, $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/fields');
    }

    /**
     * Herbizid anwenden (POST)
     */
    public function applyHerbicide(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/fields');
        }

        $data = $this->getPostData();
        $fieldId = (int)($data['field_id'] ?? 0);
        $herbicideId = (int)($data['herbicide_id'] ?? 0);

        $fieldModel = new Field();
        $result = $fieldModel->applyHerbicide($fieldId, $herbicideId, $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/fields');
    }

    /**
     * Wiese mähen (POST)
     */
    public function mow(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/fields/meadows');
        }

        $data = $this->getPostData();
        $fieldId = (int)($data['field_id'] ?? 0);

        $fieldModel = new Field();
        $result = $fieldModel->mowMeadow($fieldId, $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/fields/meadows');
    }
}
PHP;
    }

    // ==================== ANIMAL EXTENSIONS ====================

    private function generateAnimalModelExtension(): string
    {
        return <<<'PHP'
<?php
/**
 * Animal Model Extension v2.0
 * Automatisch generiert - Krankheiten, Stroh, Wasser, Mist, Nachwuchs, Tod
 */

trait AnimalExtension
{
    /**
     * Füttert mit spezifischem Futter
     */
    public function feedWithType(int $farmAnimalId, int $feedTypeId, int $farmId): array
    {
        $farmAnimal = $this->db->fetchOne(
            'SELECT fa.*, a.type as animal_type, a.name
             FROM farm_animals fa
             JOIN animals a ON fa.animal_id = a.id
             WHERE fa.id = ? AND fa.farm_id = ?',
            [$farmAnimalId, $farmId]
        );

        if (!$farmAnimal) {
            return ['success' => false, 'message' => 'Tier nicht gefunden'];
        }

        // Prüfe ob Futter erlaubt ist
        $feedReq = $this->db->fetchOne(
            'SELECT * FROM animal_feed_requirements WHERE animal_type = ? AND feed_type_id = ?',
            [$farmAnimal['animal_type'], $feedTypeId]
        );

        if (!$feedReq) {
            return ['success' => false, 'message' => 'Dieses Futter ist nicht geeignet für diese Tierart'];
        }

        $feed = $this->db->fetchOne('SELECT * FROM animal_feed_types WHERE id = ?', [$feedTypeId]);

        // Prüfe Inventar oder kaufe
        $quantity = $farmAnimal['quantity'] * ($feedReq['quantity_per_animal'] ?? 1);
        // TODO: Prüfe Inventar nach feed->name_de

        // Aktualisiere Tier
        $healthBoost = min(100, $farmAnimal['health_status'] + 10);
        $happinessBoost = min(100, $farmAnimal['happiness'] + ($feed['happiness_bonus'] ?? 10));

        $this->db->update('farm_animals', [
            'health_status' => $healthBoost,
            'happiness' => $happinessBoost,
            'last_feeding' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $farmAnimalId]);

        return ['success' => true, 'message' => "Tiere mit {$feed['name_de']} gefüttert"];
    }

    /**
     * Medizin verabreichen
     */
    public function administerMedicine(int $farmAnimalId, int $medicineId, int $farmId): array
    {
        $farmAnimal = $this->db->fetchOne(
            'SELECT fa.*, a.name FROM farm_animals fa
             JOIN animals a ON fa.animal_id = a.id
             WHERE fa.id = ? AND fa.farm_id = ?',
            [$farmAnimalId, $farmId]
        );

        if (!$farmAnimal) {
            return ['success' => false, 'message' => 'Tier nicht gefunden'];
        }

        if (!$farmAnimal['is_sick']) {
            return ['success' => false, 'message' => 'Tier ist nicht krank'];
        }

        $medicine = $this->db->fetchOne('SELECT * FROM animal_medicines WHERE id = ?', [$medicineId]);
        if (!$medicine) {
            return ['success' => false, 'message' => 'Medikament nicht gefunden'];
        }

        $cost = $medicine['cost_per_animal'] * $farmAnimal['quantity'];

        $farm = new Farm($farmId);
        if ($farm->getMoney() < $cost) {
            return ['success' => false, 'message' => 'Nicht genug Geld'];
        }

        $farm->subtractMoney($cost, "Medizin: {$medicine['name_de']}");

        // Heilungschance
        $healed = rand(1, 100) <= $medicine['effectiveness'];

        if ($healed || $medicine['cure_all']) {
            $this->db->update('farm_animals', [
                'is_sick' => false,
                'sickness_type_id' => null,
                'sick_since' => null,
                'health_status' => min(100, $farmAnimal['health_status'] + 20)
            ], 'id = :id', ['id' => $farmAnimalId]);

            return ['success' => true, 'message' => 'Tiere wurden geheilt!'];
        }

        return ['success' => true, 'message' => 'Behandlung durchgeführt, aber Krankheit besteht weiter'];
    }

    /**
     * Stroh einstreuen
     */
    public function addStraw(int $farmAnimalId, int $farmId): array
    {
        $farmAnimal = $this->db->fetchOne(
            'SELECT * FROM farm_animals WHERE id = ? AND farm_id = ?',
            [$farmAnimalId, $farmId]
        );

        if (!$farmAnimal) {
            return ['success' => false, 'message' => 'Tier nicht gefunden'];
        }

        // TODO: Prüfe Stroh im Inventar
        $cost = 5 * $farmAnimal['quantity']; // 5T pro Tier

        $farm = new Farm($farmId);
        if ($farm->getMoney() < $cost) {
            return ['success' => false, 'message' => 'Nicht genug Geld für Stroh'];
        }

        $farm->subtractMoney($cost, 'Stroh einstreuen');

        $this->db->update('farm_animals', [
            'straw_level' => 100,
            'last_straw_change' => date('Y-m-d H:i:s'),
            'happiness' => min(100, $farmAnimal['happiness'] + 5)
        ], 'id = :id', ['id' => $farmAnimalId]);

        return ['success' => true, 'message' => 'Stroh eingestreut'];
    }

    /**
     * Tiere tränken
     */
    public function waterAnimals(int $farmAnimalId, int $farmId): array
    {
        $farmAnimal = $this->db->fetchOne(
            'SELECT * FROM farm_animals WHERE id = ? AND farm_id = ?',
            [$farmAnimalId, $farmId]
        );

        if (!$farmAnimal) {
            return ['success' => false, 'message' => 'Tier nicht gefunden'];
        }

        // TODO: Prüfe Wasser im Inventar

        $this->db->update('farm_animals', [
            'water_level' => 100,
            'last_watered' => date('Y-m-d H:i:s'),
            'health_status' => min(100, $farmAnimal['health_status'] + 5)
        ], 'id = :id', ['id' => $farmAnimalId]);

        return ['success' => true, 'message' => 'Tiere getränkt'];
    }

    /**
     * Ausmisten
     */
    public function muckOut(int $farmAnimalId, int $farmId): array
    {
        $farmAnimal = $this->db->fetchOne(
            'SELECT * FROM farm_animals WHERE id = ? AND farm_id = ?',
            [$farmAnimalId, $farmId]
        );

        if (!$farmAnimal) {
            return ['success' => false, 'message' => 'Tier nicht gefunden'];
        }

        // Produziere Mist
        $manureAmount = $farmAnimal['manure_level'] * $farmAnimal['quantity'];

        if ($manureAmount > 0) {
            $farm = new Farm($farmId);
            $farm->addToInventory('material', 'Mist', $manureAmount);
        }

        $this->db->update('farm_animals', [
            'manure_level' => 0,
            'last_mucked_out' => date('Y-m-d H:i:s'),
            'happiness' => min(100, $farmAnimal['happiness'] + 10)
        ], 'id = :id', ['id' => $farmAnimalId]);

        return ['success' => true, 'message' => "{$manureAmount}x Mist gewonnen"];
    }

    /**
     * Krankheits-Check (Cron)
     */
    public static function checkSickness(): int
    {
        $db = Database::getInstance();

        // 5% Chance pro ungepflegtem Tier
        $animals = $db->fetchAll(
            "SELECT fa.*, a.type as animal_type FROM farm_animals fa
             JOIN animals a ON fa.animal_id = a.id
             WHERE fa.is_sick = FALSE AND (
                 fa.water_level < 30 OR
                 fa.straw_level < 30 OR
                 fa.health_status < 50
             )"
        );

        $sickened = 0;
        foreach ($animals as $animal) {
            if (rand(1, 100) <= 5) {
                // Wähle zufällige Krankheit
                $sickness = $db->fetchOne(
                    "SELECT * FROM animal_sicknesses
                     WHERE JSON_CONTAINS(affects_animal_types, ?)",
                    ['"' . $animal['animal_type'] . '"']
                );

                if ($sickness) {
                    $db->update('farm_animals', [
                        'is_sick' => true,
                        'sickness_type_id' => $sickness['id'],
                        'sick_since' => date('Y-m-d H:i:s')
                    ], 'id = :id', ['id' => $animal['id']]);
                    $sickened++;
                }
            }
        }

        return $sickened;
    }

    /**
     * Nachwuchs-Check (Cron)
     */
    public static function checkReproduction(): int
    {
        $db = Database::getInstance();

        // Tiere die reproduzieren können
        $animals = $db->fetchAll(
            "SELECT fa.*, a.type as animal_type, a.cost FROM farm_animals fa
             JOIN animals a ON fa.animal_id = a.id
             WHERE fa.can_reproduce = TRUE
               AND fa.is_sick = FALSE
               AND fa.health_status >= 80
               AND fa.happiness >= 70
               AND fa.quantity >= 2
               AND (fa.last_reproduction IS NULL OR fa.last_reproduction < DATE_SUB(NOW(), INTERVAL 7 DAY))"
        );

        $births = 0;
        foreach ($animals as $animal) {
            // 10% Chance pro Check
            if (rand(1, 100) <= 10) {
                $offspring = rand(1, 3);

                $db->update('farm_animals', [
                    'quantity' => $animal['quantity'] + $offspring,
                    'last_reproduction' => date('Y-m-d H:i:s'),
                    'offspring_count' => $animal['offspring_count'] + $offspring
                ], 'id = :id', ['id' => $animal['id']]);

                // Log
                $db->insert('animal_births', [
                    'farm_id' => $animal['farm_id'],
                    'parent_animal_id' => $animal['id'],
                    'animal_type' => $animal['animal_type'],
                    'offspring_quantity' => $offspring
                ]);

                $births++;
            }
        }

        return $births;
    }

    /**
     * Todes-Check (Cron)
     */
    public static function checkDeaths(): int
    {
        $db = Database::getInstance();
        $deaths = 0;

        // Altersschwäche (14+ Tage, zufällig)
        $oldAnimals = $db->fetchAll(
            "SELECT fa.*, a.name as animal_name FROM farm_animals fa
             JOIN animals a ON fa.animal_id = a.id
             WHERE fa.age_days >= 14"
        );

        foreach ($oldAnimals as $animal) {
            // 5% Chance pro Tag nach 14 Tagen
            $daysOver = $animal['age_days'] - 14;
            $deathChance = min(50, 5 + ($daysOver * 2));

            if (rand(1, 100) <= $deathChance) {
                $deathCount = max(1, (int)($animal['quantity'] * 0.1)); // 10% sterben

                if ($deathCount >= $animal['quantity']) {
                    $db->delete('farm_animals', 'id = ?', [$animal['id']]);
                } else {
                    $db->update('farm_animals', [
                        'quantity' => $animal['quantity'] - $deathCount
                    ], 'id = :id', ['id' => $animal['id']]);
                }

                // Log
                $db->insert('animal_deaths', [
                    'farm_id' => $animal['farm_id'],
                    'animal_id' => $animal['animal_id'],
                    'animal_name' => $animal['animal_name'],
                    'quantity' => $deathCount,
                    'death_reason' => 'age',
                    'age_at_death' => $animal['age_days']
                ]);

                $deaths++;
            }
        }

        // Tod durch Krankheit (health = 0)
        $sickAnimals = $db->fetchAll(
            "SELECT fa.*, a.name as animal_name FROM farm_animals fa
             JOIN animals a ON fa.animal_id = a.id
             WHERE fa.health_status <= 0"
        );

        foreach ($sickAnimals as $animal) {
            $db->delete('farm_animals', 'id = ?', [$animal['id']]);

            $db->insert('animal_deaths', [
                'farm_id' => $animal['farm_id'],
                'animal_id' => $animal['animal_id'],
                'animal_name' => $animal['animal_name'],
                'quantity' => $animal['quantity'],
                'death_reason' => 'sickness',
                'age_at_death' => $animal['age_days']
            ]);

            $deaths++;
        }

        return $deaths;
    }

    /**
     * Tägliches Update (Cron)
     */
    public static function dailyUpdate(): void
    {
        $db = Database::getInstance();

        // Erhöhe Alter
        $db->query("UPDATE farm_animals SET age_days = age_days + 1");

        // Reduziere Wasser
        $db->query("UPDATE farm_animals SET water_level = GREATEST(0, water_level - 10)");

        // Reduziere Stroh
        $db->query("UPDATE farm_animals SET straw_level = GREATEST(0, straw_level - 5)");

        // Erhöhe Mist
        $db->query("UPDATE farm_animals SET manure_level = LEAST(100, manure_level + 10)");

        // Reduziere Gesundheit bei schlechter Pflege
        $db->query(
            "UPDATE farm_animals SET health_status = GREATEST(0, health_status - 5)
             WHERE water_level < 20 OR straw_level < 20"
        );
    }
}
PHP;
    }

    private function generateAnimalControllerExtension(): string
    {
        return <<<'PHP'
<?php
/**
 * AnimalController Extension v2.0
 */

trait AnimalControllerExtension
{
    public function feedSpecific(): void
    {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->redirect('/animals');
        }

        $data = $this->getPostData();
        $animalModel = new Animal();
        $result = $animalModel->feedWithType(
            (int)$data['farm_animal_id'],
            (int)$data['feed_type_id'],
            $this->getFarmId()
        );

        Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect('/animals');
    }

    public function medicine(): void
    {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->redirect('/animals');
        }

        $data = $this->getPostData();
        $animalModel = new Animal();
        $result = $animalModel->administerMedicine(
            (int)$data['farm_animal_id'],
            (int)$data['medicine_id'],
            $this->getFarmId()
        );

        Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect('/animals');
    }

    public function straw(): void
    {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->redirect('/animals');
        }

        $data = $this->getPostData();
        $animalModel = new Animal();
        $result = $animalModel->addStraw((int)$data['farm_animal_id'], $this->getFarmId());

        Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect('/animals');
    }

    public function water(): void
    {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->redirect('/animals');
        }

        $data = $this->getPostData();
        $animalModel = new Animal();
        $result = $animalModel->waterAnimals((int)$data['farm_animal_id'], $this->getFarmId());

        Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect('/animals');
    }

    public function muckOut(): void
    {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->redirect('/animals');
        }

        $data = $this->getPostData();
        $animalModel = new Animal();
        $result = $animalModel->muckOut((int)$data['farm_animal_id'], $this->getFarmId());

        Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect('/animals');
    }
}
PHP;
    }

    // ==================== VEHICLE EXTENSIONS ====================

    private function generateVehicleModelExtension(): string
    {
        return <<<'PHP'
<?php
/**
 * Vehicle Model Extension v2.0
 */

trait VehicleExtension
{
    /**
     * In Werkstatt schicken
     */
    public function sendToWorkshop(int $farmVehicleId, int $farmId): array
    {
        $vehicle = $this->db->fetchOne(
            'SELECT fv.*, v.price, v.name FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE fv.id = ? AND fv.farm_id = ?',
            [$farmVehicleId, $farmId]
        );

        if (!$vehicle) {
            return ['success' => false, 'message' => 'Fahrzeug nicht gefunden'];
        }

        if ($vehicle['is_in_workshop']) {
            return ['success' => false, 'message' => 'Fahrzeug ist bereits in der Werkstatt'];
        }

        // Berechne Kosten und Dauer
        $repairNeeded = 100 - $vehicle['condition_percent'];
        $repairCost = ($vehicle['price'] * 0.1) * ($repairNeeded / 100);
        $durationHours = max(1, (int)ceil($repairNeeded / 20)); // 1h pro 20% Schaden

        $farm = new Farm($farmId);
        if ($farm->getMoney() < $repairCost) {
            return ['success' => false, 'message' => 'Nicht genug Geld'];
        }

        $farm->subtractMoney($repairCost, "Werkstatt: {$vehicle['name']}");

        $finishedAt = date('Y-m-d H:i:s', strtotime("+{$durationHours} hours"));

        $this->db->update('farm_vehicles', [
            'is_in_workshop' => true,
            'workshop_started_at' => date('Y-m-d H:i:s'),
            'workshop_finished_at' => $finishedAt
        ], 'id = :id', ['id' => $farmVehicleId]);

        $this->db->insert('workshop_repairs', [
            'farm_vehicle_id' => $farmVehicleId,
            'farm_id' => $farmId,
            'repair_cost' => $repairCost,
            'duration_hours' => $durationHours,
            'condition_before' => $vehicle['condition_percent']
        ]);

        return [
            'success' => true,
            'message' => "Fahrzeug in Werkstatt. Fertig in {$durationHours} Stunden."
        ];
    }

    /**
     * Fertige Reparaturen abschließen (Cron)
     */
    public static function completeRepairs(): int
    {
        $db = Database::getInstance();

        $finished = $db->fetchAll(
            "SELECT * FROM farm_vehicles
             WHERE is_in_workshop = TRUE AND workshop_finished_at <= NOW()"
        );

        foreach ($finished as $vehicle) {
            $db->update('farm_vehicles', [
                'is_in_workshop' => false,
                'condition_percent' => 100,
                'last_maintenance' => date('Y-m-d H:i:s'),
                'workshop_started_at' => null,
                'workshop_finished_at' => null
            ], 'id = :id', ['id' => $vehicle['id']]);

            $db->update('workshop_repairs', [
                'status' => 'completed',
                'finished_at' => date('Y-m-d H:i:s')
            ], 'farm_vehicle_id = :id AND status = "in_progress"', ['id' => $vehicle['id']]);
        }

        return count($finished);
    }

    /**
     * Tägliche Betriebsstunden simulieren (Cron)
     */
    public static function simulateDailyUsage(): int
    {
        $db = Database::getInstance();

        $vehicles = $db->fetchAll(
            "SELECT * FROM farm_vehicles WHERE is_in_workshop = FALSE"
        );

        foreach ($vehicles as $vehicle) {
            $hours = rand(5, 10);
            $conditionLoss = $hours * 0.5; // 0.5% pro Stunde

            $db->update('farm_vehicles', [
                'operating_hours' => $vehicle['operating_hours'] + $hours,
                'daily_operating_hours' => $hours,
                'condition_percent' => max(10, $vehicle['condition_percent'] - $conditionLoss)
            ], 'id = :id', ['id' => $vehicle['id']]);
        }

        return count($vehicles);
    }

    /**
     * Diesel verbrauchen
     */
    public function consumeDiesel(int $farmVehicleId, int $farmId, float $liters, string $activity = ''): bool
    {
        // Prüfe Diesel im Inventar
        $farm = new Farm($farmId);
        $storage = new Storage();
        $diesel = $storage->getProductByName($farmId, 'Diesel');

        if (!$diesel || $diesel['quantity'] < $liters) {
            return false;
        }

        // Reduziere Diesel
        $storage->removeProduct($farmId, $diesel['id'], $liters);

        // Log
        $this->db->insert('diesel_consumption_log', [
            'farm_id' => $farmId,
            'farm_vehicle_id' => $farmVehicleId,
            'liters_consumed' => $liters,
            'activity_type' => $activity
        ]);

        return true;
    }

    /**
     * Prüft ob Fahrzeug verfügbar ist
     */
    public function isAvailable(int $farmVehicleId): bool
    {
        $vehicle = $this->db->fetchOne(
            'SELECT is_in_workshop FROM farm_vehicles WHERE id = ?',
            [$farmVehicleId]
        );

        return $vehicle && !$vehicle['is_in_workshop'];
    }

    /**
     * Effizienz-Bonus (angepasst für Werkstatt)
     */
    public function getTotalEfficiencyBonus(int $farmId): float
    {
        $sql = "SELECT SUM(
                    v.power_hp * (fv.condition_percent / 100) *
                    CASE WHEN fv.is_in_workshop THEN 0.5 ELSE 1 END
                ) as total_power
                FROM farm_vehicles fv
                JOIN vehicles v ON fv.vehicle_id = v.id
                WHERE fv.farm_id = ? AND fv.condition_percent > 30";

        $result = $this->db->fetchOne($sql, [$farmId]);
        $totalPower = (float)($result['total_power'] ?? 0);

        return $totalPower / 100;
    }
}
PHP;
    }

    private function generateVehicleControllerExtension(): string
    {
        return <<<'PHP'
<?php
/**
 * VehicleController Extension v2.0
 */

trait VehicleControllerExtension
{
    public function workshop(): void
    {
        $this->requireAuth();
        $vehicleModel = new Vehicle();

        $data = [
            'title' => 'Werkstatt',
            'inRepair' => $vehicleModel->getVehiclesInWorkshop($this->getFarmId()),
            'needRepair' => $vehicleModel->getVehiclesNeedingRepair($this->getFarmId())
        ];

        $this->renderWithLayout('vehicles/workshop', $data);
    }

    public function sendToWorkshop(): void
    {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->redirect('/vehicles');
        }

        $data = $this->getPostData();
        $vehicleModel = new Vehicle();
        $result = $vehicleModel->sendToWorkshop((int)$data['farm_vehicle_id'], $this->getFarmId());

        Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect('/vehicles');
    }
}
PHP;
    }

    // ==================== ARENA ====================

    private function generateArenaModel(): string
    {
        return <<<'PHP'
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
               AND (challenger_cooperative_id = ? OR defender_cooperative_id = ?)
               AND (challenger_cooperative_id = ? OR defender_cooperative_id = ?)",
            [$challengerCoopId, $challengerCoopId, $defenderCoopId, $defenderCoopId]
        );

        if ($existing) {
            return ['success' => false, 'message' => 'Es gibt bereits ein aktives Match'];
        }

        $this->db->insert('arena_matches', [
            'challenger_cooperative_id' => $challengerCoopId,
            'defender_cooperative_id' => $defenderCoopId,
            'status' => 'pending'
        ]);

        return ['success' => true, 'message' => 'Herausforderung gesendet!'];
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
            return ['success' => false, 'message' => 'Herausforderung nicht gefunden'];
        }

        $this->db->update('arena_matches', [
            'status' => 'pick_ban',
            'accepted_at' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $matchId]);

        return ['success' => true, 'message' => 'Herausforderung angenommen! Pick & Ban beginnt.'];
    }

    /**
     * Fahrzeug picken
     */
    public function pickVehicle(int $matchId, int $coopId, int $vehicleId): array
    {
        $match = $this->db->fetchOne('SELECT * FROM arena_matches WHERE id = ?', [$matchId]);
        if (!$match || $match['status'] !== 'pick_ban') {
            return ['success' => false, 'message' => 'Nicht in Pick-Phase'];
        }

        // Prüfe ob Fahrzeug bereits gepickt/gebannt
        $used = $this->db->fetchOne(
            'SELECT * FROM arena_picks_bans WHERE match_id = ? AND vehicle_id = ?',
            [$matchId, $vehicleId]
        );

        if ($used) {
            return ['success' => false, 'message' => 'Fahrzeug bereits verwendet'];
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
            return ['success' => false, 'message' => 'Nicht in Ban-Phase'];
        }

        $used = $this->db->fetchOne(
            'SELECT * FROM arena_picks_bans WHERE match_id = ? AND vehicle_id = ?',
            [$matchId, $vehicleId]
        );

        if ($used) {
            return ['success' => false, 'message' => 'Fahrzeug bereits verwendet'];
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
     * Rolle zuweisen
     */
    public function assignRole(int $matchId, int $farmId, string $role, int $coopId): array
    {
        $validRoles = ['harvest_specialist', 'bale_producer', 'transport'];
        if (!in_array($role, $validRoles)) {
            return ['success' => false, 'message' => 'Ungültige Rolle'];
        }

        // Prüfe ob Rolle bereits vergeben
        $existing = $this->db->fetchOne(
            'SELECT * FROM arena_participants WHERE match_id = ? AND cooperative_id = ? AND role = ?',
            [$matchId, $coopId, $role]
        );

        if ($existing) {
            return ['success' => false, 'message' => 'Rolle bereits vergeben'];
        }

        $this->db->insert('arena_participants', [
            'match_id' => $matchId,
            'farm_id' => $farmId,
            'cooperative_id' => $coopId,
            'role' => $role
        ]);

        return ['success' => true, 'message' => 'Rolle zugewiesen'];
    }

    /**
     * Match starten
     */
    public function startMatch(int $matchId): array
    {
        $match = $this->db->fetchOne('SELECT * FROM arena_matches WHERE id = ?', [$matchId]);
        if (!$match || $match['status'] !== 'ready') {
            return ['success' => false, 'message' => 'Match nicht bereit'];
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
    public function recordAction(int $matchId, int $participantId, string $actionType, int $amount): array
    {
        $participant = $this->db->fetchOne(
            'SELECT * FROM arena_participants WHERE id = ? AND match_id = ?',
            [$participantId, $matchId]
        );

        if (!$participant) {
            return ['success' => false, 'message' => 'Teilnehmer nicht gefunden'];
        }

        // Berechne Punkte basierend auf Aktion
        $basePoints = 0;
        $multiplier = 1.0;

        switch ($actionType) {
            case 'wheat_harvest':
                $basePoints = $amount; // 1 Punkt pro Einheit
                $this->db->update('arena_participants', [
                    'wheat_harvested' => $participant['wheat_harvested'] + $amount
                ], 'id = :id', ['id' => $participantId]);
                break;

            case 'bale_production':
                $basePoints = $amount * 10; // 10 Punkte pro Ballen
                $multiplier = $this->getScoreMultiplier($matchId, $participant['cooperative_id']);
                $this->db->update('arena_participants', [
                    'bales_produced' => $participant['bales_produced'] + $amount
                ], 'id = :id', ['id' => $participantId]);
                break;

            case 'transport_delivery':
                $basePoints = $amount * 15;
                $multiplier = $this->getScoreMultiplier($matchId, $participant['cooperative_id']);
                $this->db->update('arena_participants', [
                    'transported_amount' => $participant['transported_amount'] + $amount
                ], 'id = :id', ['id' => $participantId]);
                break;
        }

        $finalPoints = (int)($basePoints * $multiplier);

        $this->db->insert('arena_score_events', [
            'match_id' => $matchId,
            'participant_id' => $participantId,
            'event_type' => $actionType,
            'base_points' => $basePoints,
            'multiplier' => $multiplier,
            'final_points' => $finalPoints
        ]);

        // Aktualisiere Score
        $this->db->update('arena_participants', [
            'score_contribution' => $participant['score_contribution'] + $finalPoints
        ], 'id = :id', ['id' => $participantId]);

        return ['success' => true, 'points' => $finalPoints];
    }

    /**
     * Score-Multiplikator basierend auf Weizenernte
     */
    private function getScoreMultiplier(int $matchId, int $coopId): float
    {
        $totalWheat = $this->db->fetchColumn(
            'SELECT SUM(wheat_harvested) FROM arena_participants WHERE match_id = ? AND cooperative_id = ?',
            [$matchId, $coopId]
        );

        // 1.0x Basis, +0.1x pro 100 Weizen
        return 1.0 + ($totalWheat / 1000);
    }

    /**
     * Match beenden
     */
    public function endMatch(int $matchId): array
    {
        $match = $this->db->fetchOne('SELECT * FROM arena_matches WHERE id = ?', [$matchId]);
        if (!$match) {
            return ['success' => false, 'message' => 'Match nicht gefunden'];
        }

        // Berechne finale Scores
        $challengerScore = $this->db->fetchColumn(
            'SELECT SUM(score_contribution) FROM arena_participants WHERE match_id = ? AND cooperative_id = ?',
            [$matchId, $match['challenger_cooperative_id']]
        );

        $defenderScore = $this->db->fetchColumn(
            'SELECT SUM(score_contribution) FROM arena_participants WHERE match_id = ? AND cooperative_id = ?',
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

            $ranking = $this->db->fetchOne(
                'SELECT * FROM arena_rankings WHERE cooperative_id = ?',
                [$coopId]
            );

            if (!$ranking) {
                $this->db->insert('arena_rankings', ['cooperative_id' => $coopId]);
                $ranking = ['total_matches' => 0, 'wins' => 0, 'losses' => 0, 'draws' => 0, 'ranking_points' => 1000];
            }

            $pointChange = $isDraw ? 0 : ($isWinner ? 25 : -20);

            $this->db->query(
                "INSERT INTO arena_rankings (cooperative_id, total_matches, wins, losses, draws, ranking_points, last_match_at)
                 VALUES (?, 1, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                    total_matches = total_matches + 1,
                    wins = wins + ?,
                    losses = losses + ?,
                    draws = draws + ?,
                    ranking_points = ranking_points + ?,
                    last_match_at = NOW()",
                [
                    $coopId,
                    $isWinner ? 1 : 0,
                    (!$isWinner && !$isDraw) ? 1 : 0,
                    $isDraw ? 1 : 0,
                    1000 + $pointChange,
                    $isWinner ? 1 : 0,
                    (!$isWinner && !$isDraw) ? 1 : 0,
                    $isDraw ? 1 : 0,
                    $pointChange
                ]
            );
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
}
PHP;
    }

    private function generateArenaController(): string
    {
        return <<<'PHP'
<?php
/**
 * ArenaController v2.0
 */

class ArenaController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($this->getFarmId());

        if (!$membership) {
            Session::setFlash('error', 'Du musst in einer Genossenschaft sein', 'warning');
            $this->redirect('/cooperative');
        }

        $arenaModel = new Arena();

        $data = [
            'title' => 'Wettkampf-Arena',
            'membership' => $membership,
            'pendingChallenges' => $arenaModel->getPendingChallenges($membership['cooperative_id']),
            'activeMatches' => $arenaModel->getActiveMatches($membership['cooperative_id']),
            'rankings' => $arenaModel->getArenaRankings(10),
            'cooperatives' => $coopModel->getAllCooperatives()
        ];

        $this->renderWithLayout('arena/index', $data);
    }

    public function rankings(): void
    {
        $arenaModel = new Arena();

        $data = [
            'title' => 'Arena-Rangliste',
            'rankings' => $arenaModel->getArenaRankings(100)
        ];

        $this->renderWithLayout('arena/rankings', $data);
    }

    public function challenge(): void
    {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->redirect('/arena');
        }

        $data = $this->getPostData();
        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($this->getFarmId());

        $arenaModel = new Arena();
        $result = $arenaModel->challengeCooperative(
            $membership['cooperative_id'],
            (int)$data['defender_coop_id']
        );

        Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect('/arena');
    }

    public function accept(): void
    {
        $this->requireAuth();
        if (!$this->validateCsrf()) {
            $this->redirect('/arena');
        }

        $data = $this->getPostData();
        $coopModel = new Cooperative();
        $membership = $coopModel->getMembership($this->getFarmId());

        $arenaModel = new Arena();
        $result = $arenaModel->acceptChallenge((int)$data['match_id'], $membership['cooperative_id']);

        Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect('/arena/match/' . $data['match_id']);
    }
}
PHP;
    }

    // ==================== VIEWS ====================

    public function generateMeadowsView(): string
    {
        return <<<'PHP'
<div class="page-header">
    <h1>Wiesen</h1>
</div>

<div class="field-nav">
    <a href="<?= BASE_URL ?>/fields" class="btn btn-outline">Felder</a>
    <a href="<?= BASE_URL ?>/fields/meadows" class="btn btn-primary">Wiesen</a>
    <a href="<?= BASE_URL ?>/fields/greenhouses" class="btn btn-outline">Gewächshäuser</a>
</div>

<?php if (empty($meadows)): ?>
    <div class="alert alert-info">Du hast noch keine Wiesen.</div>
<?php else: ?>
    <div class="grid grid-3">
        <?php foreach ($meadows as $meadow): ?>
            <div class="card field-card">
                <div class="card-header">
                    <h4>Wiese #<?= $meadow['id'] ?></h4>
                    <span class="badge"><?= $meadow['size_hectares'] ?> ha</span>
                </div>
                <div class="card-body">
                    <p>Status: <strong><?= $meadow['status'] ?></strong></p>
                    <p>Bodenqualität: <?= $meadow['soil_quality'] ?>%</p>

                    <?php if ($meadow['status'] === 'ready'): ?>
                        <form action="<?= BASE_URL ?>/fields/mow" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="field_id" value="<?= $meadow['id'] ?>">
                            <button type="submit" class="btn btn-success">Mähen</button>
                        </form>
                    <?php elseif ($meadow['status'] === 'growing'): ?>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?= $meadow['growth_progress'] ?? 50 ?>%"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
PHP;
    }

    public function generateGreenhousesView(): string
    {
        return <<<'PHP'
<div class="page-header">
    <h1>Gewächshäuser</h1>
</div>

<div class="field-nav">
    <a href="<?= BASE_URL ?>/fields" class="btn btn-outline">Felder</a>
    <a href="<?= BASE_URL ?>/fields/meadows" class="btn btn-outline">Wiesen</a>
    <a href="<?= BASE_URL ?>/fields/greenhouses" class="btn btn-primary">Gewächshäuser</a>
</div>

<?php if (empty($greenhouses)): ?>
    <div class="alert alert-info">Du hast noch keine Gewächshäuser.</div>
<?php else: ?>
    <div class="grid grid-3">
        <?php foreach ($greenhouses as $gh): ?>
            <div class="card greenhouse-card">
                <div class="card-header">
                    <h4>Gewächshaus #<?= $gh['id'] ?></h4>
                </div>
                <div class="card-body">
                    <p>Status: <?= $gh['status'] ?></p>
                    <?php if ($gh['status'] === 'empty'): ?>
                        <form action="<?= BASE_URL ?>/fields/plant" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="field_id" value="<?= $gh['id'] ?>">
                            <select name="crop_id" class="form-select">
                                <?php foreach ($greenhouseCrops as $crop): ?>
                                    <option value="<?= $crop['id'] ?>"><?= $crop['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary mt-2">Pflanzen</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
PHP;
    }

    public function generateArenaIndexView(): string
    {
        return <<<'PHP'
<div class="page-header">
    <h1>Wettkampf-Arena</h1>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header"><h3>Herausforderung senden</h3></div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/arena/challenge" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="form-group">
                    <label>Genossenschaft herausfordern</label>
                    <select name="defender_coop_id" class="form-select">
                        <?php foreach ($cooperatives as $coop): ?>
                            <?php if ($coop['id'] !== $membership['cooperative_id']): ?>
                                <option value="<?= $coop['id'] ?>"><?= htmlspecialchars($coop['name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Herausfordern</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Ausstehende Herausforderungen</h3></div>
        <div class="card-body">
            <?php if (empty($pendingChallenges)): ?>
                <p class="text-muted">Keine Herausforderungen</p>
            <?php else: ?>
                <?php foreach ($pendingChallenges as $challenge): ?>
                    <div class="challenge-item">
                        <strong><?= htmlspecialchars($challenge['challenger_name']) ?></strong>
                        <form action="<?= BASE_URL ?>/arena/accept" method="POST" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="match_id" value="<?= $challenge['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-success">Annehmen</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header"><h3>Top 10 Rangliste</h3></div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Genossenschaft</th>
                    <th>Siege</th>
                    <th>Punkte</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rankings as $i => $rank): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($rank['cooperative_name']) ?></td>
                        <td><?= $rank['wins'] ?>/<?= $rank['total_matches'] ?></td>
                        <td><strong><?= $rank['ranking_points'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="<?= BASE_URL ?>/arena/rankings" class="btn btn-outline">Vollständige Rangliste</a>
    </div>
</div>
PHP;
    }

    public function generateArenaRankingsView(): string
    {
        return '<div class="page-header"><h1>Arena-Rangliste</h1></div><!-- TODO -->';
    }

    public function generateArenaPickBanView(): string
    {
        return '<div class="page-header"><h1>Pick & Ban</h1></div><!-- TODO -->';
    }

    public function generateArenaMatchView(): string
    {
        return '<div class="page-header"><h1>Match</h1></div><!-- TODO -->';
    }

    public function generateArenaResultsView(): string
    {
        return '<div class="page-header"><h1>Match-Ergebnisse</h1></div><!-- TODO -->';
    }

    // ==================== CRONS ====================

    public function generateVehicleCron(): string
    {
        return <<<'PHP'
<?php
/**
 * Vehicle Check Cron
 * Täglich ausführen
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Vehicle.php';

echo "=== Vehicle Daily Check ===\n";
echo date('Y-m-d H:i:s') . "\n\n";

// Tägliche Betriebsstunden
$hoursUpdated = Vehicle::simulateDailyUsage();
echo "Betriebsstunden aktualisiert: {$hoursUpdated} Fahrzeuge\n";

// Reparaturen abschließen
$repairsCompleted = Vehicle::completeRepairs();
echo "Reparaturen abgeschlossen: {$repairsCompleted}\n";

echo "\nDone.\n";
PHP;
    }

    public function generateAnimalCronExtension(): string
    {
        return <<<'PHP'
<?php
/**
 * Animal Check Extension
 * Zum animal_check.php hinzufügen
 */

// Tägliches Update (Alter, Wasser, Stroh, Mist)
Animal::dailyUpdate();
echo "Tägliches Update durchgeführt\n";

// Krankheits-Check
$sickened = Animal::checkSickness();
echo "Neu erkrankt: {$sickened} Tiere\n";

// Nachwuchs-Check
$births = Animal::checkReproduction();
echo "Geburten: {$births}\n";

// Todes-Check
$deaths = Animal::checkDeaths();
echo "Gestorben: {$deaths}\n";
PHP;
    }

    public function generateHarvestCronExtension(): string
    {
        return <<<'PHP'
<?php
/**
 * Harvest Check Extension
 * Zum harvest_check.php hinzufügen
 */

// Wachstumsstufen aktualisieren
$stagesUpdated = Field::updateGrowthStages();
echo "Wachstumsstufen aktualisiert: {$stagesUpdated}\n";

// Unkraut-Wachstum
$weedAffected = Field::checkWeedGrowth();
echo "Unkraut gewachsen auf: {$weedAffected} Feldern\n";
PHP;
    }
}
