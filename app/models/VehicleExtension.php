<?php
/**
 * Vehicle Model Extension v2.0
 * Automatisch generiert - Betriebsstunden, Werkstatt, Diesel-Verbrauch
 */

trait VehicleExtension
{
    /**
     * In Werkstatt schicken (nicht sofortige Reparatur)
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

        if ($vehicle['condition_percent'] >= 100) {
            return ['success' => false, 'message' => 'Fahrzeug ist in perfektem Zustand'];
        }

        // Berechne Kosten und Dauer
        $repairNeeded = 100 - $vehicle['condition_percent'];
        $repairCost = ($vehicle['price'] * 0.1) * ($repairNeeded / 100);
        $durationHours = max(1, (int)ceil($repairNeeded / 20)); // 1h pro 20% Schaden

        $farm = new Farm($farmId);
        if (!$farm->hasMoney($repairCost)) {
            return ['success' => false, 'message' => 'Nicht genug Geld. Benötigt: ' . number_format($repairCost, 0, ',', '.') . ' T'];
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
            'message' => "Fahrzeug in Werkstatt geschickt. Fertig in {$durationHours} Stunden.",
            'finished_at' => $finishedAt
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
                'finished_at' => date('Y-m-d H:i:s'),
                'condition_after' => 100
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
            "SELECT fv.*, v.fuel_consumption FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE fv.is_in_workshop = FALSE"
        );

        $updated = 0;
        foreach ($vehicles as $vehicle) {
            $hours = rand(5, 10);
            $conditionLoss = $hours * 0.5; // 0.5% pro Stunde

            $db->update('farm_vehicles', [
                'operating_hours' => ($vehicle['operating_hours'] ?? 0) + $hours,
                'daily_operating_hours' => $hours,
                'total_operating_hours' => ($vehicle['total_operating_hours'] ?? 0) + $hours,
                'condition_percent' => max(10, $vehicle['condition_percent'] - $conditionLoss)
            ], 'id = :id', ['id' => $vehicle['id']]);

            $updated++;
        }

        return $updated;
    }

    /**
     * Täglichen Diesel-Verbrauch verarbeiten (Cron)
     */
    public static function processDailyDieselConsumption(): int
    {
        $db = Database::getInstance();

        // Hole alle Fahrzeuge mit Betriebsstunden heute
        $vehicles = $db->fetchAll(
            "SELECT fv.*, v.fuel_consumption, f.id as farm_id
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             JOIN farms f ON fv.farm_id = f.id
             WHERE fv.daily_operating_hours > 0 AND fv.is_in_workshop = FALSE"
        );

        $processed = 0;
        foreach ($vehicles as $vehicle) {
            $consumption = $vehicle['fuel_consumption'] ?? 10;
            $litersUsed = $vehicle['daily_operating_hours'] * ($consumption / 10); // Verbrauch pro Stunde

            // Prüfe Diesel im Inventar
            $diesel = $db->fetchOne(
                "SELECT * FROM inventory WHERE farm_id = ? AND item_name = 'Diesel'",
                [$vehicle['farm_id']]
            );

            if ($diesel && $diesel['quantity'] >= $litersUsed) {
                // Reduziere Diesel
                $db->update('inventory', [
                    'quantity' => $diesel['quantity'] - $litersUsed
                ], 'id = :id', ['id' => $diesel['id']]);

                // Log
                $db->insert('diesel_consumption_log', [
                    'farm_id' => $vehicle['farm_id'],
                    'farm_vehicle_id' => $vehicle['id'],
                    'liters_consumed' => $litersUsed,
                    'activity_type' => 'daily_usage'
                ]);

                $processed++;
            }
            // Wenn kein Diesel: Fahrzeug funktioniert trotzdem, aber keine Effizienz-Boni
        }

        // Reset daily hours
        $db->query("UPDATE farm_vehicles SET daily_operating_hours = 0");

        return $processed;
    }

    /**
     * Diesel manuell verbrauchen
     */
    public function consumeDiesel(int $farmVehicleId, int $farmId, float $liters, string $activity = ''): bool
    {
        // Prüfe Diesel im Inventar
        $diesel = $this->db->fetchOne(
            "SELECT * FROM inventory WHERE farm_id = ? AND item_name = 'Diesel'",
            [$farmId]
        );

        if (!$diesel || $diesel['quantity'] < $liters) {
            return false;
        }

        // Reduziere Diesel
        $this->db->update('inventory', [
            'quantity' => $diesel['quantity'] - $liters
        ], 'id = :id', ['id' => $diesel['id']]);

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
     * Prüft ob Fahrzeug verfügbar ist (nicht in Werkstatt)
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
     * Gibt Fahrzeuge in der Werkstatt zurück
     */
    public function getVehiclesInWorkshop(int $farmId): array
    {
        return $this->db->fetchAll(
            "SELECT fv.*, v.name, v.brand_id, vb.name as brand_name,
                    wr.repair_cost, wr.duration_hours, wr.started_at, wr.condition_before
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             LEFT JOIN vehicle_brands vb ON v.brand_id = vb.id
             LEFT JOIN workshop_repairs wr ON fv.id = wr.farm_vehicle_id AND wr.status = 'in_progress'
             WHERE fv.farm_id = ? AND fv.is_in_workshop = TRUE
             ORDER BY fv.workshop_finished_at",
            [$farmId]
        );
    }

    /**
     * Gibt Fahrzeuge die Reparatur brauchen zurück
     */
    public function getVehiclesNeedingRepair(int $farmId, int $threshold = 70): array
    {
        return $this->db->fetchAll(
            "SELECT fv.*, v.name, v.brand_id, v.price, vb.name as brand_name
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             LEFT JOIN vehicle_brands vb ON v.brand_id = vb.id
             WHERE fv.farm_id = ? AND fv.condition_percent < ? AND fv.is_in_workshop = FALSE
             ORDER BY fv.condition_percent ASC",
            [$farmId, $threshold]
        );
    }

    /**
     * Gibt Diesel-Verbrauch-Statistik zurück
     */
    public function getDieselStats(int $farmId, int $days = 7): array
    {
        return $this->db->fetchAll(
            "SELECT DATE(consumed_at) as date, SUM(liters_consumed) as total_liters
             FROM diesel_consumption_log
             WHERE farm_id = ? AND consumed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(consumed_at)
             ORDER BY date DESC",
            [$farmId, $days]
        );
    }

    /**
     * Effizienz-Bonus (angepasst: Werkstatt = halber Bonus)
     */
    public function getTotalEfficiencyBonusExtended(int $farmId): float
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

        return $totalPower / 100; // 1% pro 100 PS
    }

    /**
     * Berechnet geschätzte Reparaturkosten
     */
    public function estimateRepairCost(int $farmVehicleId): array
    {
        $vehicle = $this->db->fetchOne(
            'SELECT fv.*, v.price, v.name FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE fv.id = ?',
            [$farmVehicleId]
        );

        if (!$vehicle) {
            return ['cost' => 0, 'hours' => 0];
        }

        $repairNeeded = 100 - $vehicle['condition_percent'];
        $repairCost = ($vehicle['price'] * 0.1) * ($repairNeeded / 100);
        $durationHours = max(1, (int)ceil($repairNeeded / 20));

        return [
            'cost' => $repairCost,
            'hours' => $durationHours,
            'condition_before' => $vehicle['condition_percent']
        ];
    }
}
