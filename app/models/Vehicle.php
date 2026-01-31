<?php
/**
 * Vehicle Model
 *
 * Verwaltet Fahrzeuge und Geraete.
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
        // Hole Fahrzeug-Daten
        $vehicle = $this->db->fetchOne('SELECT * FROM vehicles WHERE id = ?', [$vehicleId]);

        if (!$vehicle) {
            return ['success' => false, 'message' => 'Fahrzeug nicht gefunden'];
        }

        // Pruefe Forschungsanforderung
        if ($vehicle['required_research_id']) {
            $farm = new Farm($farmId);
            if (!$farm->hasResearch($vehicle['required_research_id'])) {
                return ['success' => false, 'message' => 'Forschung erforderlich'];
            }
        }

        // Pruefe und ziehe Geld ab
        $farm = new Farm($farmId);
        if (!$farm->subtractMoney($vehicle['cost'], "Fahrzeugkauf: {$vehicle['name']}")) {
            return ['success' => false, 'message' => 'Nicht genuegend Geld'];
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
            "SELECT fv.*, v.name, v.cost
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE fv.id = ? AND fv.farm_id = ?",
            [$farmVehicleId, $farmId]
        );

        if (!$farmVehicle) {
            return ['success' => false, 'message' => 'Fahrzeug nicht gefunden'];
        }

        // Pruefe ob verliehen
        $isLent = $this->db->exists(
            'cooperative_shared_equipment',
            'farm_vehicle_id = ? AND available = FALSE',
            [$farmVehicleId]
        );

        if ($isLent) {
            return ['success' => false, 'message' => 'Fahrzeug ist aktuell verliehen'];
        }

        // Verkaufspreis: 50% des Kaufpreises * Zustand
        $conditionMultiplier = $farmVehicle['condition_percent'] / 100;
        $sellPrice = ($farmVehicle['cost'] * 0.5) * $conditionMultiplier;

        $farm = new Farm($farmId);
        $farm->addMoney($sellPrice, "Fahrzeugverkauf: {$farmVehicle['name']}");

        // Loesche Fahrzeug
        $this->db->delete('farm_vehicles', 'id = ?', [$farmVehicleId]);

        Logger::info('Vehicle sold', [
            'farm_id' => $farmId,
            'vehicle' => $farmVehicle['name'],
            'price' => $sellPrice
        ]);

        return [
            'success' => true,
            'message' => "{$farmVehicle['name']} fuer {$sellPrice} T verkauft!",
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
            "SELECT fv.*, v.name, v.cost
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
        $repairCost = ($farmVehicle['cost'] * 0.1) * ($repairNeeded / 10);

        $farm = new Farm($farmId);
        if (!$farm->subtractMoney($repairCost, "Reparatur: {$farmVehicle['name']}")) {
            return ['success' => false, 'message' => 'Nicht genuegend Geld fuer Reparatur'];
        }

        $this->db->update('farm_vehicles', ['condition_percent' => 100], 'id = :id', ['id' => $farmVehicleId]);

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
     * Gibt verfuegbare Fahrzeuge zum Kauf zurueck
     */
    public function getAvailableVehicles(int $farmId): array
    {
        $sql = "SELECT v.*
                FROM vehicles v
                LEFT JOIN farm_research fr ON v.required_research_id = fr.research_id
                    AND fr.farm_id = ? AND fr.status = 'completed'
                WHERE v.required_research_id IS NULL
                   OR fr.id IS NOT NULL
                ORDER BY v.cost";
        return $this->db->fetchAll($sql, [$farmId]);
    }

    /**
     * Gibt Fahrzeuge einer Farm zurueck
     */
    public function getFarmVehicles(int $farmId): array
    {
        return $this->db->fetchAll(
            "SELECT fv.*, v.name, v.type, v.efficiency_bonus, v.fuel_consumption, v.image_url
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE fv.farm_id = ?",
            [$farmId]
        );
    }

    /**
     * Berechnet den Effizienz-Bonus aller Fahrzeuge
     */
    public function getTotalEfficiencyBonus(int $farmId): float
    {
        $sql = "SELECT SUM(v.efficiency_bonus)
                FROM farm_vehicles fv
                JOIN vehicles v ON fv.vehicle_id = v.id
                WHERE fv.farm_id = ? AND fv.condition_percent > 50";
        return (float) ($this->db->fetchColumn($sql, [$farmId]) ?? 0);
    }

    /**
     * Nutzt ein Fahrzeug (erhoehe Stunden, reduziere Zustand)
     */
    public function use(int $farmVehicleId, int $hours = 1): void
    {
        $farmVehicle = $this->db->fetchOne(
            'SELECT * FROM farm_vehicles WHERE id = ?',
            [$farmVehicleId]
        );

        if ($farmVehicle) {
            $newHours = $farmVehicle['hours_used'] + $hours;
            $conditionLoss = $hours * 2; // 2% pro Stunde
            $newCondition = max(10, $farmVehicle['condition_percent'] - $conditionLoss);

            $this->db->update('farm_vehicles', [
                'hours_used' => $newHours,
                'condition_percent' => $newCondition
            ], 'id = :id', ['id' => $farmVehicleId]);
        }
    }

    /**
     * Setzt Verleihstatus
     */
    public function setLendingAvailability(int $farmVehicleId, int $farmId, bool $available): array
    {
        $farmVehicle = $this->db->fetchOne(
            'SELECT * FROM farm_vehicles WHERE id = ? AND farm_id = ?',
            [$farmVehicleId, $farmId]
        );

        if (!$farmVehicle) {
            return ['success' => false, 'message' => 'Fahrzeug nicht gefunden'];
        }

        $this->db->update('farm_vehicles', [
            'available_for_lending' => $available
        ], 'id = :id', ['id' => $farmVehicleId]);

        return [
            'success' => true,
            'message' => $available ? 'Fahrzeug zum Verleihen freigegeben' : 'Fahrzeug nicht mehr zum Verleihen verfuegbar'
        ];
    }
}
