<?php
/**
 * Animal Model Extension v2.0
 * Automatisch generiert - Krankheiten, Stroh, Wasser, Mist, Nachwuchs, Tod, spezifisches Futter
 */

trait AnimalExtension
{
    /**
     * Füttert mit spezifischem Futter aus dem Inventar
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
        if (!$feed) {
            return ['success' => false, 'message' => 'Futterart nicht gefunden'];
        }

        $quantityNeeded = $farmAnimal['quantity'] * ($feedReq['quantity_per_animal'] ?? 1);

        // Prüfe Inventar
        $farm = new Farm($farmId);
        $inventory = $farm->getInventory();
        $hasEnough = false;

        foreach ($inventory as $item) {
            if (strtolower($item['item_name']) === strtolower($feed['name_de'])) {
                if ($item['quantity'] >= $quantityNeeded) {
                    $hasEnough = true;
                    // Reduziere Inventar
                    $farm->removeFromInventory($item['id'], $quantityNeeded);
                    break;
                }
            }
        }

        if (!$hasEnough) {
            // Falls nicht im Inventar, kaufe automatisch
            $cost = $feed['cost_if_purchased'] * $quantityNeeded;
            if (!$farm->hasMoney($cost)) {
                return ['success' => false, 'message' => "Nicht genug {$feed['name_de']} im Lager und nicht genug Geld zum Kaufen"];
            }
            $farm->subtractMoney($cost, "Futter kaufen: {$feed['name_de']}");
        }

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
     * Gibt verfügbare Futterarten für eine Tierart zurück
     */
    public function getAvailableFeedTypes(string $animalType): array
    {
        return $this->db->fetchAll(
            "SELECT ft.*, afr.is_primary, afr.quantity_per_animal
             FROM animal_feed_types ft
             JOIN animal_feed_requirements afr ON ft.id = afr.feed_type_id
             WHERE afr.animal_type = ?
             ORDER BY afr.is_primary DESC, ft.name_de",
            [$animalType]
        );
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
        if (!$farm->hasMoney($cost)) {
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
     * Gibt verfügbare Medikamente zurück
     */
    public function getAvailableMedicines(): array
    {
        return $this->db->fetchAll("SELECT * FROM animal_medicines ORDER BY cost_per_animal");
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

        $cost = 5 * $farmAnimal['quantity']; // 5T pro Tier

        $farm = new Farm($farmId);
        if (!$farm->hasMoney($cost)) {
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

        // Wasser kostet nichts, muss aber Wasser im Inventar haben oder kaufen
        $cost = 2 * $farmAnimal['quantity']; // 2T pro Tier für Wasserkosten

        $farm = new Farm($farmId);
        if (!$farm->hasMoney($cost)) {
            return ['success' => false, 'message' => 'Nicht genug Geld für Wasser'];
        }

        $farm->subtractMoney($cost, 'Wasser für Tiere');

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
        $manureAmount = (int)(($farmAnimal['manure_level'] / 10) * $farmAnimal['quantity']);

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
                // Wähle zufällige Krankheit für diese Tierart
                $sickness = $db->fetchOne(
                    "SELECT * FROM animal_sicknesses
                     WHERE JSON_CONTAINS(affects_animal_types, ?)
                     ORDER BY RAND() LIMIT 1",
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
            "SELECT fa.*, a.type as animal_type, a.cost, a.name FROM farm_animals fa
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
            // 5% Chance pro Tag nach 14 Tagen, steigt mit Alter
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

        // Tod durch Krankheit (health <= 0)
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

        // Tod durch Dehydrierung (water_level = 0 für 3+ Tage)
        $dehydratedAnimals = $db->fetchAll(
            "SELECT fa.*, a.name as animal_name FROM farm_animals fa
             JOIN animals a ON fa.animal_id = a.id
             WHERE fa.water_level = 0
               AND fa.last_watered < DATE_SUB(NOW(), INTERVAL 3 DAY)"
        );

        foreach ($dehydratedAnimals as $animal) {
            $deathCount = max(1, (int)($animal['quantity'] * 0.2)); // 20% sterben

            if ($deathCount >= $animal['quantity']) {
                $db->delete('farm_animals', 'id = ?', [$animal['id']]);
            } else {
                $db->update('farm_animals', [
                    'quantity' => $animal['quantity'] - $deathCount
                ], 'id = :id', ['id' => $animal['id']]);
            }

            $db->insert('animal_deaths', [
                'farm_id' => $animal['farm_id'],
                'animal_id' => $animal['animal_id'],
                'animal_name' => $animal['animal_name'],
                'quantity' => $deathCount,
                'death_reason' => 'dehydration',
                'age_at_death' => $animal['age_days']
            ]);

            $deaths++;
        }

        return $deaths;
    }

    /**
     * Tägliches Update (Cron) - Alter, Wasser, Stroh, Mist, Gesundheit
     */
    public static function dailyUpdate(): void
    {
        $db = Database::getInstance();

        // Erhöhe Alter
        $db->query("UPDATE farm_animals SET age_days = age_days + 1");

        // Reduziere Wasser (-10% pro Tag)
        $db->query("UPDATE farm_animals SET water_level = GREATEST(0, water_level - 10)");

        // Reduziere Stroh (-5% pro Tag)
        $db->query("UPDATE farm_animals SET straw_level = GREATEST(0, straw_level - 5)");

        // Erhöhe Mist (+10% pro Tag)
        $db->query("UPDATE farm_animals SET manure_level = LEAST(100, manure_level + 10)");

        // Reduziere Gesundheit bei schlechter Pflege
        $db->query(
            "UPDATE farm_animals SET health_status = GREATEST(0, health_status - 5)
             WHERE water_level < 20 OR straw_level < 20"
        );

        // Reduziere Happiness bei hohem Mist-Level
        $db->query(
            "UPDATE farm_animals SET happiness = GREATEST(0, happiness - 5)
             WHERE manure_level > 80"
        );

        // Reduziere Gesundheit bei kranken Tieren
        $db->query(
            "UPDATE farm_animals fa
             JOIN animal_sicknesses s ON fa.sickness_type_id = s.id
             SET fa.health_status = GREATEST(0, fa.health_status - s.health_reduction_per_day),
                 fa.happiness = GREATEST(0, fa.happiness - s.happiness_reduction_per_day)
             WHERE fa.is_sick = TRUE"
        );
    }

    /**
     * Gibt Krankheitsinformationen zurück
     */
    public function getSicknessInfo(int $sicknessId): ?array
    {
        return $this->db->fetchOne("SELECT * FROM animal_sicknesses WHERE id = ?", [$sicknessId]);
    }

    /**
     * Gibt Todes-Log für eine Farm zurück
     */
    public function getDeathLog(int $farmId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM animal_deaths WHERE farm_id = ? ORDER BY died_at DESC LIMIT ?",
            [$farmId, $limit]
        );
    }

    /**
     * Gibt Geburten-Log für eine Farm zurück
     */
    public function getBirthLog(int $farmId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM animal_births WHERE farm_id = ? ORDER BY born_at DESC LIMIT ?",
            [$farmId, $limit]
        );
    }
}
