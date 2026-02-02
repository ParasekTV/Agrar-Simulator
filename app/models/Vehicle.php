<?php
/**
 * Vehicle Model
 *
 * Verwaltet Fahrzeuge und Geräte.
 */
class Vehicle
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Kauft ein Fahrzeug
     */
    public function buy(int $vehicleId, int $farmId): array
    {
        // Hole Fahrzeug-Daten mit Marken-Info
        $vehicle = $this->db->fetchOne(
            'SELECT v.*, vb.name as brand_name, vb.required_research_id as brand_research_id
             FROM vehicles v
             JOIN vehicle_brands vb ON v.brand_id = vb.id
             WHERE v.id = ?',
            [$vehicleId]
        );

        if (!$vehicle) {
            return ['success' => false, 'message' => 'Fahrzeug nicht gefunden'];
        }

        // Prüfe Marken-Forschung
        if ($vehicle['brand_research_id']) {
            $farm = new Farm($farmId);
            if (!$farm->hasResearch($vehicle['brand_research_id'])) {
                return ['success' => false, 'message' => "Marke {$vehicle['brand_name']} muss erst erforscht werden"];
            }
        }

        // Prüfe Level-Anforderung
        $farm = new Farm($farmId);
        $farmData = $farm->getData();
        if ($farmData['level'] < $vehicle['required_level']) {
            return ['success' => false, 'message' => "Level {$vehicle['required_level']} erforderlich"];
        }

        // Prüfe und ziehe Geld ab
        if (!$farm->subtractMoney($vehicle['price'], "Fahrzeugkauf: {$vehicle['name']}")) {
            return ['success' => false, 'message' => 'Nicht genügend Geld'];
        }

        // Erstelle Fahrzeug-Eintrag
        $farmVehicleId = $this->db->insert('farm_vehicles', [
            'farm_id' => $farmId,
            'vehicle_id' => $vehicleId
        ]);

        $farm->addPoints(POINTS_BUILDING, "Fahrzeug gekauft: {$vehicle['name']}");

        Logger::info('Vehicle purchased', [
            'farm_id' => $farmId,
            'vehicle' => $vehicle['name']
        ]);

        return [
            'success' => true,
            'message' => "{$vehicle['name']} gekauft!",
            'farm_vehicle_id' => $farmVehicleId
        ];
    }

    /**
     * Verkauft ein Fahrzeug
     */
    public function sell(int $farmVehicleId, int $farmId): array
    {
        // Hole Fahrzeug-Daten
        $farmVehicle = $this->db->fetchOne(
            "SELECT fv.*, v.name, v.price
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE fv.id = ? AND fv.farm_id = ?",
            [$farmVehicleId, $farmId]
        );

        if (!$farmVehicle) {
            return ['success' => false, 'message' => 'Fahrzeug nicht gefunden'];
        }

        // Prüfe ob zum Verkauf angeboten
        if ($farmVehicle['is_for_sale']) {
            return ['success' => false, 'message' => 'Fahrzeug ist aktuell zum Verkauf angeboten'];
        }

        // Verkaufspreis: 50% des Kaufpreises * Zustand
        $conditionMultiplier = $farmVehicle['condition_percent'] / 100;
        $sellPrice = ($farmVehicle['price'] * 0.5) * $conditionMultiplier;

        $farm = new Farm($farmId);
        $farm->addMoney($sellPrice, "Fahrzeugverkauf: {$farmVehicle['name']}");

        // Lösche Fahrzeug
        $this->db->delete('farm_vehicles', 'id = ?', [$farmVehicleId]);

        Logger::info('Vehicle sold', [
            'farm_id' => $farmId,
            'vehicle' => $farmVehicle['name'],
            'price' => $sellPrice
        ]);

        return [
            'success' => true,
            'message' => "{$farmVehicle['name']} für " . number_format($sellPrice, 0, ',', '.') . " T verkauft!",
            'income' => $sellPrice
        ];
    }

    /**
     * Repariert ein Fahrzeug
     */
    public function repair(int $farmVehicleId, int $farmId): array
    {
        // Hole Fahrzeug-Daten
        $farmVehicle = $this->db->fetchOne(
            "SELECT fv.*, v.name, v.price
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE fv.id = ? AND fv.farm_id = ?",
            [$farmVehicleId, $farmId]
        );

        if (!$farmVehicle) {
            return ['success' => false, 'message' => 'Fahrzeug nicht gefunden'];
        }

        if ($farmVehicle['condition_percent'] >= 100) {
            return ['success' => false, 'message' => 'Fahrzeug ist bereits in perfektem Zustand'];
        }

        // Reparaturkosten: 10% des Kaufpreises pro fehlende 10%
        $repairNeeded = 100 - $farmVehicle['condition_percent'];
        $repairCost = ($farmVehicle['price'] * 0.1) * ($repairNeeded / 10);

        $farm = new Farm($farmId);
        if (!$farm->subtractMoney($repairCost, "Reparatur: {$farmVehicle['name']}")) {
            return ['success' => false, 'message' => 'Nicht genügend Geld für Reparatur'];
        }

        $this->db->update('farm_vehicles', [
            'condition_percent' => 100,
            'last_maintenance' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $farmVehicleId]);

        Logger::info('Vehicle repaired', [
            'farm_id' => $farmId,
            'vehicle' => $farmVehicle['name']
        ]);

        return [
            'success' => true,
            'message' => "{$farmVehicle['name']} repariert!",
            'cost' => $repairCost
        ];
    }

    /**
     * Gibt verfügbare Fahrzeuge zum Kauf zurück (nach Kategorie)
     */
    public function getAvailableVehicles(int $farmId, ?string $category = null): array
    {
        $params = [$farmId];
        $categoryFilter = "";

        if ($category) {
            $categoryFilter = "AND v.category = ?";
            $params[] = $category;
        }

        $sql = "SELECT v.*, vb.name as brand_name, vb.logo_url as brand_logo,
                       vb.required_research_id as brand_research_id
                FROM vehicles v
                JOIN vehicle_brands vb ON v.brand_id = vb.id
                LEFT JOIN farm_research fr ON vb.required_research_id = fr.research_id
                    AND fr.farm_id = ? AND fr.status = 'completed'
                WHERE v.is_active = TRUE
                  AND (vb.required_research_id IS NULL OR fr.id IS NOT NULL)
                  {$categoryFilter}
                ORDER BY v.category, v.price";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Gibt alle Marken zurück (mit Freischaltungs-Status)
     */
    public function getBrands(int $farmId): array
    {
        $sql = "SELECT vb.*,
                       CASE WHEN fr.id IS NOT NULL THEN 1 ELSE 0 END as is_unlocked
                FROM vehicle_brands vb
                LEFT JOIN farm_research fr ON vb.required_research_id = fr.research_id
                    AND fr.farm_id = ? AND fr.status = 'completed'
                WHERE vb.is_active = TRUE
                ORDER BY vb.name";

        return $this->db->fetchAll($sql, [$farmId]);
    }

    /**
     * Gibt Fahrzeuge einer Farm zurück
     */
    public function getFarmVehicles(int $farmId): array
    {
        return $this->db->fetchAll(
            "SELECT fv.*, v.name, v.vehicle_type, v.category, v.power_hp, v.max_speed,
                    v.fuel_consumption, v.maintenance_cost, v.image_url, v.price,
                    vb.name as brand_name, vb.logo_url as brand_logo
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             JOIN vehicle_brands vb ON v.brand_id = vb.id
             WHERE fv.farm_id = ?
             ORDER BY v.category, v.name",
            [$farmId]
        );
    }

    /**
     * Berechnet den Effizienz-Bonus aller Fahrzeuge (basierend auf PS)
     */
    public function getTotalEfficiencyBonus(int $farmId): float
    {
        // Effizienz basiert auf Gesamt-PS der Fahrzeuge in gutem Zustand
        $sql = "SELECT SUM(v.power_hp * (fv.condition_percent / 100))
                FROM farm_vehicles fv
                JOIN vehicles v ON fv.vehicle_id = v.id
                WHERE fv.farm_id = ? AND fv.condition_percent > 50";

        $totalPower = (float) ($this->db->fetchColumn($sql, [$farmId]) ?? 0);

        // 1% Bonus pro 100 PS
        return $totalPower / 100;
    }

    /**
     * Nutzt ein Fahrzeug (erhöhe Stunden, reduziere Zustand)
     */
    public function use(int $farmVehicleId, int $hours = 1): void
    {
        $farmVehicle = $this->db->fetchOne(
            'SELECT * FROM farm_vehicles WHERE id = ?',
            [$farmVehicleId]
        );

        if ($farmVehicle) {
            $newHours = $farmVehicle['operating_hours'] + $hours;
            $conditionLoss = $hours * 2; // 2% pro Stunde
            $newCondition = max(10, $farmVehicle['condition_percent'] - $conditionLoss);

            $this->db->update('farm_vehicles', [
                'operating_hours' => $newHours,
                'condition_percent' => $newCondition
            ], 'id = :id', ['id' => $farmVehicleId]);
        }
    }

    /**
     * Setzt Verkaufsstatus für Spielermarkt
     */
    public function setForSale(int $farmVehicleId, int $farmId, bool $forSale, ?float $price = null): array
    {
        $farmVehicle = $this->db->fetchOne(
            'SELECT fv.*, v.price as original_price
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE fv.id = ? AND fv.farm_id = ?',
            [$farmVehicleId, $farmId]
        );

        if (!$farmVehicle) {
            return ['success' => false, 'message' => 'Fahrzeug nicht gefunden'];
        }

        if ($forSale && $price === null) {
            // Standardpreis: 60% des Originalpreises * Zustand
            $price = $farmVehicle['original_price'] * 0.6 * ($farmVehicle['condition_percent'] / 100);
        }

        $this->db->update('farm_vehicles', [
            'is_for_sale' => $forSale ? 1 : 0,
            'sale_price' => $forSale ? $price : null
        ], 'id = :id', ['id' => $farmVehicleId]);

        return [
            'success' => true,
            'message' => $forSale ? 'Fahrzeug zum Verkauf angeboten' : 'Verkaufsangebot zurückgezogen'
        ];
    }

    /**
     * Gibt Fahrzeuge zurück, die zum Verkauf stehen
     */
    public function getVehiclesForSale(): array
    {
        return $this->db->fetchAll(
            "SELECT fv.*, v.name, v.vehicle_type, v.category, v.power_hp,
                    vb.name as brand_name, vb.logo_url as brand_logo,
                    f.farm_name as seller_name
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             JOIN vehicle_brands vb ON v.brand_id = vb.id
             JOIN farms f ON fv.farm_id = f.id
             WHERE fv.is_for_sale = TRUE
             ORDER BY fv.sale_price"
        );
    }
}
