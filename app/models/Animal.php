<?php
/**
 * Animal Model
 *
 * Verwaltet Tiere und deren Produktion.
 */
class Animal
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Kauft Tiere
     */
    public function buy(int $animalId, int $quantity, int $farmId): array
    {
        // Hole Tier-Daten
        $animal = $this->db->fetchOne('SELECT * FROM animals WHERE id = ?', [$animalId]);

        if (!$animal) {
            return ['success' => false, 'message' => 'Tier nicht gefunden'];
        }

        // Pruefe Forschungsanforderung
        if ($animal['required_research_id']) {
            $farm = new Farm($farmId);
            if (!$farm->hasResearch($animal['required_research_id'])) {
                return ['success' => false, 'message' => 'Forschung erforderlich'];
            }
        }

        // Berechne Kosten
        $totalCost = $animal['cost'] * $quantity;

        // Pruefe und ziehe Geld ab
        $farm = new Farm($farmId);
        if (!$farm->subtractMoney($totalCost, "Tierkauf: {$quantity}x {$animal['name']}")) {
            return ['success' => false, 'message' => 'Nicht genuegend Geld'];
        }

        // Pruefe ob bereits Tiere dieser Art vorhanden
        $existing = $this->db->fetchOne(
            'SELECT * FROM farm_animals WHERE farm_id = ? AND animal_id = ?',
            [$farmId, $animalId]
        );

        if ($existing) {
            // Erhoehe Anzahl
            $this->db->update('farm_animals', [
                'quantity' => $existing['quantity'] + $quantity
            ], 'id = :id', ['id' => $existing['id']]);
        } else {
            // Erstelle neuen Eintrag
            $this->db->insert('farm_animals', [
                'farm_id' => $farmId,
                'animal_id' => $animalId,
                'quantity' => $quantity,
                'last_feeding' => date('Y-m-d H:i:s')
            ]);
        }

        $farm->addPoints(POINTS_BUILDING, "Tiere gekauft: {$animal['name']}");

        Logger::info('Animals purchased', [
            'farm_id' => $farmId,
            'animal' => $animal['name'],
            'quantity' => $quantity
        ]);

        return [
            'success' => true,
            'message' => "{$quantity}x {$animal['name']} gekauft!",
            'total_cost' => $totalCost
        ];
    }

    /**
     * Fuettert Tiere
     */
    public function feed(int $farmAnimalId, int $farmId): array
    {
        // Hole Tier-Daten
        $farmAnimal = $this->db->fetchOne(
            "SELECT fa.*, a.name, a.feed_cost
             FROM farm_animals fa
             JOIN animals a ON fa.animal_id = a.id
             WHERE fa.id = ? AND fa.farm_id = ?",
            [$farmAnimalId, $farmId]
        );

        if (!$farmAnimal) {
            return ['success' => false, 'message' => 'Tiere nicht gefunden'];
        }

        // Berechne Futterkosten
        $totalCost = $farmAnimal['feed_cost'] * $farmAnimal['quantity'];

        // Pruefe und ziehe Geld ab
        $farm = new Farm($farmId);
        if (!$farm->subtractMoney($totalCost, "Futter: {$farmAnimal['name']}")) {
            return ['success' => false, 'message' => 'Nicht genuegend Geld fuer Futter'];
        }

        // Aktualisiere Fuetterungszeit und verbessere Gesundheit/Glueck
        $newHealth = min(100, $farmAnimal['health_status'] + 10);
        $newHappiness = min(100, $farmAnimal['happiness'] + 15);

        $this->db->update('farm_animals', [
            'last_feeding' => date('Y-m-d H:i:s'),
            'health_status' => $newHealth,
            'happiness' => $newHappiness
        ], 'id = :id', ['id' => $farmAnimalId]);

        Logger::info('Animals fed', [
            'farm_id' => $farmId,
            'animal' => $farmAnimal['name']
        ]);

        return [
            'success' => true,
            'message' => "{$farmAnimal['name']} gefuettert!",
            'cost' => $totalCost,
            'new_health' => $newHealth,
            'new_happiness' => $newHappiness
        ];
    }

    /**
     * Sammelt Tierprodukte
     */
    public function collect(int $farmAnimalId, int $farmId): array
    {
        // Hole Tier-Daten
        $farmAnimal = $this->db->fetchOne(
            "SELECT fa.*, a.name, a.type, a.production_item, a.production_time_hours,
                    a.production_quantity
             FROM farm_animals fa
             JOIN animals a ON fa.animal_id = a.id
             WHERE fa.id = ? AND fa.farm_id = ?",
            [$farmAnimalId, $farmId]
        );

        if (!$farmAnimal) {
            return ['success' => false, 'message' => 'Tiere nicht gefunden'];
        }

        // Pruefe ob Produktion bereit
        if ($farmAnimal['last_collection']) {
            $lastCollection = strtotime($farmAnimal['last_collection']);
            $readyAt = $lastCollection + ($farmAnimal['production_time_hours'] * 3600);

            if (time() < $readyAt) {
                $remaining = $readyAt - time();
                $hours = floor($remaining / 3600);
                $minutes = floor(($remaining % 3600) / 60);
                return [
                    'success' => false,
                    'message' => "Noch {$hours}h {$minutes}m bis zur naechsten Produktion"
                ];
            }
        }

        // Berechne Produktion (mit Gluecks-Bonus)
        $happinessMultiplier = $farmAnimal['happiness'] / 100;
        $baseProduction = $farmAnimal['production_quantity'] * $farmAnimal['quantity'];
        $actualProduction = (int) ceil($baseProduction * $happinessMultiplier);

        // Hole Produktpreis
        $product = $this->db->fetchOne(
            'SELECT * FROM animal_products WHERE from_animal_type = ?',
            [$farmAnimal['type']]
        );

        $sellPrice = $product ? $product['base_sell_price'] : 10;
        $productName = $farmAnimal['production_item'];

        // Fuege zum Inventar hinzu
        $this->addToInventory($farmId, $farmAnimal['animal_id'], $productName, $actualProduction);

        // Aktualisiere Sammelzeit und reduziere Glueck leicht
        $newHappiness = max(50, $farmAnimal['happiness'] - 5);

        $this->db->update('farm_animals', [
            'last_collection' => date('Y-m-d H:i:s'),
            'happiness' => $newHappiness
        ], 'id = :id', ['id' => $farmAnimalId]);

        // Vergebe Punkte
        $farm = new Farm($farmId);
        $farm->addPoints(5, "Produkte gesammelt: {$productName}");

        Logger::info('Animal products collected', [
            'farm_id' => $farmId,
            'animal' => $farmAnimal['name'],
            'product' => $productName,
            'quantity' => $actualProduction
        ]);

        return [
            'success' => true,
            'message' => "{$actualProduction}x {$productName} gesammelt!",
            'quantity' => $actualProduction,
            'product' => $productName,
            'value' => $actualProduction * $sellPrice
        ];
    }

    /**
     * Fuegt Produkte zum Inventar hinzu
     */
    private function addToInventory(int $farmId, int $animalId, string $productName, int $quantity): void
    {
        $existing = $this->db->fetchOne(
            'SELECT * FROM inventory WHERE farm_id = ? AND item_type = ? AND item_name = ?',
            [$farmId, 'animal_product', $productName]
        );

        if ($existing) {
            $this->db->update('inventory', [
                'quantity' => $existing['quantity'] + $quantity
            ], 'id = :id', ['id' => $existing['id']]);
        } else {
            $this->db->insert('inventory', [
                'farm_id' => $farmId,
                'item_type' => 'animal_product',
                'item_id' => $animalId,
                'item_name' => $productName,
                'quantity' => $quantity
            ]);
        }
    }

    /**
     * Gibt verfuegbare Tiere zum Kauf zurueck
     */
    public function getAvailableAnimals(int $farmId): array
    {
        $sql = "SELECT a.*
                FROM animals a
                LEFT JOIN farm_research fr ON a.required_research_id = fr.research_id
                    AND fr.farm_id = ? AND fr.status = 'completed'
                WHERE a.required_research_id IS NULL
                   OR fr.id IS NOT NULL
                ORDER BY a.cost";
        return $this->db->fetchAll($sql, [$farmId]);
    }

    /**
     * Gibt Tiere einer Farm mit Produktionsstatus zurueck
     */
    public function getFarmAnimalsWithStatus(int $farmId): array
    {
        $animals = $this->db->fetchAll(
            "SELECT fa.*, a.name, a.type, a.production_item, a.production_time_hours,
                    a.production_quantity, a.feed_cost, a.image_url
             FROM farm_animals fa
             JOIN animals a ON fa.animal_id = a.id
             WHERE fa.farm_id = ?",
            [$farmId]
        );

        foreach ($animals as &$animal) {
            // Berechne Produktionsstatus
            if ($animal['last_collection']) {
                $lastCollection = strtotime($animal['last_collection']);
                $readyAt = $lastCollection + ($animal['production_time_hours'] * 3600);
                $animal['production_ready'] = time() >= $readyAt;
                $animal['production_ready_at'] = date('Y-m-d H:i:s', $readyAt);
            } else {
                $animal['production_ready'] = true;
                $animal['production_ready_at'] = null;
            }

            // Berechne Fuetterungsstatus (alle 24h)
            if ($animal['last_feeding']) {
                $lastFeeding = strtotime($animal['last_feeding']);
                $feedingNeeded = $lastFeeding + (24 * 3600);
                $animal['needs_feeding'] = time() >= $feedingNeeded;
            } else {
                $animal['needs_feeding'] = true;
            }
        }

        return $animals;
    }

    /**
     * Verkauft Tiere
     */
    public function sell(int $farmAnimalId, int $quantity, int $farmId): array
    {
        $farmAnimal = $this->db->fetchOne(
            "SELECT fa.*, a.name, a.cost
             FROM farm_animals fa
             JOIN animals a ON fa.animal_id = a.id
             WHERE fa.id = ? AND fa.farm_id = ?",
            [$farmAnimalId, $farmId]
        );

        if (!$farmAnimal) {
            return ['success' => false, 'message' => 'Tiere nicht gefunden'];
        }

        if ($farmAnimal['quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Nicht genuegend Tiere'];
        }

        // Verkaufspreis: 50% des Kaufpreises
        $sellPrice = ($farmAnimal['cost'] * 0.5) * $quantity;

        $farm = new Farm($farmId);
        $farm->addMoney($sellPrice, "Tierverkauf: {$quantity}x {$farmAnimal['name']}");

        // Reduziere oder loesche Tiere
        if ($farmAnimal['quantity'] === $quantity) {
            $this->db->delete('farm_animals', 'id = ?', [$farmAnimalId]);
        } else {
            $this->db->update('farm_animals', [
                'quantity' => $farmAnimal['quantity'] - $quantity
            ], 'id = :id', ['id' => $farmAnimalId]);
        }

        Logger::info('Animals sold', [
            'farm_id' => $farmId,
            'animal' => $farmAnimal['name'],
            'quantity' => $quantity,
            'price' => $sellPrice
        ]);

        return [
            'success' => true,
            'message' => "{$quantity}x {$farmAnimal['name']} fuer {$sellPrice} EUR verkauft!",
            'income' => $sellPrice
        ];
    }

    /**
     * Aktualisiert Tier-Gesundheit (fuer Cron)
     */
    public static function updateAnimalHealth(): void
    {
        $db = Database::getInstance();

        // Reduziere Gesundheit und Glueck bei nicht gefuetterten Tieren
        $db->query(
            "UPDATE farm_animals
             SET health_status = GREATEST(10, health_status - 5),
                 happiness = GREATEST(10, happiness - 10)
             WHERE last_feeding IS NULL
                OR last_feeding < DATE_SUB(NOW(), INTERVAL 48 HOUR)"
        );
    }
}
