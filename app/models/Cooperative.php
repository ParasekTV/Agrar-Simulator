<?php
/**
 * Cooperative Model
 *
 * Verwaltet Agrargenossenschaften.
 */
class Cooperative
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Gruendet eine neue Genossenschaft
     */
    public function create(int $founderFarmId, string $name, string $description = ''): array
    {
        // Pruefe ob bereits Mitglied einer Genossenschaft
        if ($this->getMembership($founderFarmId)) {
            return ['success' => false, 'message' => 'Du bist bereits Mitglied einer Genossenschaft'];
        }

        // Pruefe ob Name verfuegbar
        if ($this->db->exists('cooperatives', 'name = ?', [$name])) {
            return ['success' => false, 'message' => 'Name bereits vergeben'];
        }

        // Gruendungskosten
        $cost = 5000;
        $farm = new Farm($founderFarmId);

        if (!$farm->subtractMoney($cost, "Genossenschaft gegruendet: {$name}")) {
            return ['success' => false, 'message' => 'Nicht genuegend Geld (5000 T benoetigt)'];
        }

        // Erstelle Genossenschaft
        $coopId = $this->db->insert('cooperatives', [
            'name' => $name,
            'founder_farm_id' => $founderFarmId,
            'description' => $description
        ]);

        // Fuege Gruender als Mitglied hinzu
        $this->db->insert('cooperative_members', [
            'cooperative_id' => $coopId,
            'farm_id' => $founderFarmId,
            'role' => 'founder'
        ]);

        $farm->addPoints(100, "Genossenschaft gegruendet: {$name}");

        Logger::info('Cooperative created', [
            'coop_id' => $coopId,
            'founder_farm_id' => $founderFarmId,
            'name' => $name
        ]);

        return [
            'success' => true,
            'message' => "Genossenschaft '{$name}' gegruendet!",
            'cooperative_id' => $coopId
        ];
    }

    /**
     * Tritt einer Genossenschaft bei
     */
    public function join(int $farmId, int $cooperativeId): array
    {
        // Pruefe ob bereits Mitglied einer Genossenschaft
        if ($this->getMembership($farmId)) {
            return ['success' => false, 'message' => 'Du bist bereits Mitglied einer Genossenschaft'];
        }

        // Hole Genossenschaft
        $coop = $this->db->fetchOne('SELECT * FROM cooperatives WHERE id = ?', [$cooperativeId]);

        if (!$coop) {
            return ['success' => false, 'message' => 'Genossenschaft nicht gefunden'];
        }

        // Pruefe Mitgliederlimit
        $memberCount = $this->db->count('cooperative_members', 'cooperative_id = ?', [$cooperativeId]);

        if ($memberCount >= $coop['member_limit']) {
            return ['success' => false, 'message' => 'Genossenschaft ist voll'];
        }

        // Fuege Mitglied hinzu
        $this->db->insert('cooperative_members', [
            'cooperative_id' => $cooperativeId,
            'farm_id' => $farmId,
            'role' => 'member'
        ]);

        $farm = new Farm($farmId);
        $farm->addPoints(20, "Genossenschaft beigetreten: {$coop['name']}");

        Logger::info('Joined cooperative', [
            'farm_id' => $farmId,
            'coop_id' => $cooperativeId
        ]);

        return [
            'success' => true,
            'message' => "Genossenschaft '{$coop['name']}' beigetreten!"
        ];
    }

    /**
     * Verlaesst eine Genossenschaft
     */
    public function leave(int $farmId): array
    {
        $membership = $this->getMembership($farmId);

        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        // Gruender kann nicht verlassen (muss aufloesen)
        if ($membership['role'] === 'founder') {
            return ['success' => false, 'message' => 'Gruender koennen die Genossenschaft nicht verlassen. Loese sie auf oder uebertrage die Gruenderrolle.'];
        }

        // Entferne geteilte Geraete
        $this->db->delete('cooperative_shared_equipment', 'owner_farm_id = ?', [$farmId]);

        // Entferne Mitgliedschaft
        $this->db->delete('cooperative_members', 'farm_id = ?', [$farmId]);

        Logger::info('Left cooperative', [
            'farm_id' => $farmId,
            'coop_id' => $membership['cooperative_id']
        ]);

        return [
            'success' => true,
            'message' => 'Genossenschaft verlassen'
        ];
    }

    /**
     * Loest eine Genossenschaft auf
     */
    public function dissolve(int $farmId): array
    {
        $membership = $this->getMembership($farmId);

        if (!$membership || $membership['role'] !== 'founder') {
            return ['success' => false, 'message' => 'Nur der Gruender kann die Genossenschaft aufloesen'];
        }

        $coop = $this->db->fetchOne('SELECT * FROM cooperatives WHERE id = ?', [$membership['cooperative_id']]);

        // Verteile Treasury gleichmaessig
        if ($coop['treasury'] > 0) {
            $memberCount = $this->db->count('cooperative_members', 'cooperative_id = ?', [$coop['id']]);
            $share = $coop['treasury'] / $memberCount;

            $members = $this->db->fetchAll(
                'SELECT farm_id FROM cooperative_members WHERE cooperative_id = ?',
                [$coop['id']]
            );

            foreach ($members as $member) {
                $farm = new Farm($member['farm_id']);
                $farm->addMoney($share, "Genossenschaft aufgeloest: {$coop['name']}");
            }
        }

        // Loesche Genossenschaft (CASCADE loescht Mitglieder und geteilte Geraete)
        $this->db->delete('cooperatives', 'id = ?', [$coop['id']]);

        Logger::info('Cooperative dissolved', [
            'coop_id' => $coop['id'],
            'name' => $coop['name']
        ]);

        return [
            'success' => true,
            'message' => "Genossenschaft '{$coop['name']}' aufgeloest"
        ];
    }

    /**
     * Gibt die Mitgliedschaft einer Farm zurueck
     */
    public function getMembership(int $farmId): ?array
    {
        return $this->db->fetchOne(
            "SELECT cm.*, c.name as cooperative_name, c.description, c.treasury
             FROM cooperative_members cm
             JOIN cooperatives c ON cm.cooperative_id = c.id
             WHERE cm.farm_id = ?",
            [$farmId]
        );
    }

    /**
     * Gibt alle Genossenschaften zurueck
     */
    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, COUNT(cm.id) as member_count, f.farm_name as founder_name
             FROM cooperatives c
             JOIN cooperative_members cm ON c.id = cm.cooperative_id
             JOIN farms f ON c.founder_farm_id = f.id
             GROUP BY c.id
             ORDER BY c.total_points DESC"
        );
    }

    /**
     * Gibt Details einer Genossenschaft zurueck
     */
    public function getDetails(int $cooperativeId): ?array
    {
        $coop = $this->db->fetchOne(
            "SELECT c.*, f.farm_name as founder_name
             FROM cooperatives c
             JOIN farms f ON c.founder_farm_id = f.id
             WHERE c.id = ?",
            [$cooperativeId]
        );

        if (!$coop) {
            return null;
        }

        // Hole Mitglieder
        $coop['members'] = $this->db->fetchAll(
            "SELECT cm.*, f.farm_name, f.points, f.level
             FROM cooperative_members cm
             JOIN farms f ON cm.farm_id = f.id
             WHERE cm.cooperative_id = ?
             ORDER BY cm.role, cm.contribution_points DESC",
            [$cooperativeId]
        );

        // Hole geteilte Geraete
        $coop['shared_equipment'] = $this->db->fetchAll(
            "SELECT cse.*, v.name as vehicle_name, v.type, f.farm_name as owner_name
             FROM cooperative_shared_equipment cse
             JOIN farm_vehicles fv ON cse.farm_vehicle_id = fv.id
             JOIN vehicles v ON fv.vehicle_id = v.id
             JOIN farms f ON cse.owner_farm_id = f.id
             WHERE cse.cooperative_id = ?",
            [$cooperativeId]
        );

        return $coop;
    }

    /**
     * Teilt ein Geraet mit der Genossenschaft
     */
    public function shareEquipment(int $farmId, int $farmVehicleId, float $feePerHour = 0): array
    {
        $membership = $this->getMembership($farmId);

        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        // Pruefe ob Fahrzeug existiert
        $vehicle = $this->db->fetchOne(
            "SELECT fv.*, v.name
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE fv.id = ? AND fv.farm_id = ?",
            [$farmVehicleId, $farmId]
        );

        if (!$vehicle) {
            return ['success' => false, 'message' => 'Fahrzeug nicht gefunden'];
        }

        // Pruefe ob bereits geteilt
        if ($this->db->exists('cooperative_shared_equipment', 'farm_vehicle_id = ?', [$farmVehicleId])) {
            return ['success' => false, 'message' => 'Fahrzeug bereits geteilt'];
        }

        // Fuege zu geteilten Geraeten hinzu
        $this->db->insert('cooperative_shared_equipment', [
            'cooperative_id' => $membership['cooperative_id'],
            'farm_vehicle_id' => $farmVehicleId,
            'owner_farm_id' => $farmId,
            'lending_fee_per_hour' => $feePerHour
        ]);

        Logger::info('Equipment shared', [
            'farm_id' => $farmId,
            'vehicle' => $vehicle['name'],
            'coop_id' => $membership['cooperative_id']
        ]);

        return [
            'success' => true,
            'message' => "{$vehicle['name']} mit der Genossenschaft geteilt"
        ];
    }

    /**
     * Nimmt ein Geraet aus der Teilung
     */
    public function unshareEquipment(int $farmId, int $sharedEquipmentId): array
    {
        $equipment = $this->db->fetchOne(
            'SELECT * FROM cooperative_shared_equipment WHERE id = ? AND owner_farm_id = ?',
            [$sharedEquipmentId, $farmId]
        );

        if (!$equipment) {
            return ['success' => false, 'message' => 'Geteiltes Geraet nicht gefunden'];
        }

        if (!$equipment['available']) {
            return ['success' => false, 'message' => 'Geraet ist aktuell verliehen'];
        }

        $this->db->delete('cooperative_shared_equipment', 'id = ?', [$sharedEquipmentId]);

        return [
            'success' => true,
            'message' => 'Geraet nicht mehr geteilt'
        ];
    }

    /**
     * Leiht ein Geraet aus
     */
    public function borrowEquipment(int $farmId, int $sharedEquipmentId): array
    {
        $membership = $this->getMembership($farmId);

        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        $equipment = $this->db->fetchOne(
            "SELECT cse.*, v.name as vehicle_name
             FROM cooperative_shared_equipment cse
             JOIN farm_vehicles fv ON cse.farm_vehicle_id = fv.id
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE cse.id = ? AND cse.cooperative_id = ? AND cse.available = TRUE",
            [$sharedEquipmentId, $membership['cooperative_id']]
        );

        if (!$equipment) {
            return ['success' => false, 'message' => 'Geraet nicht verfuegbar'];
        }

        if ($equipment['owner_farm_id'] === $farmId) {
            return ['success' => false, 'message' => 'Du kannst dein eigenes Geraet nicht ausleihen'];
        }

        // Markiere als nicht verfuegbar
        $this->db->update('cooperative_shared_equipment', ['available' => false], 'id = :id', ['id' => $sharedEquipmentId]);

        // Erstelle Ausleih-Log
        $this->db->insert('equipment_lending_log', [
            'equipment_id' => $sharedEquipmentId,
            'borrower_farm_id' => $farmId
        ]);

        Logger::info('Equipment borrowed', [
            'farm_id' => $farmId,
            'equipment_id' => $sharedEquipmentId
        ]);

        return [
            'success' => true,
            'message' => "{$equipment['vehicle_name']} ausgeliehen"
        ];
    }

    /**
     * Gibt ein ausgeliehenes Geraet zurueck
     */
    public function returnEquipment(int $farmId, int $sharedEquipmentId, float $hoursUsed): array
    {
        // Finde aktive Ausleihe
        $lending = $this->db->fetchOne(
            "SELECT el.*, cse.lending_fee_per_hour, cse.owner_farm_id
             FROM equipment_lending_log el
             JOIN cooperative_shared_equipment cse ON el.equipment_id = cse.id
             WHERE el.equipment_id = ? AND el.borrower_farm_id = ? AND el.returned_at IS NULL",
            [$sharedEquipmentId, $farmId]
        );

        if (!$lending) {
            return ['success' => false, 'message' => 'Keine aktive Ausleihe gefunden'];
        }

        // Berechne Gebuehr
        $fee = $lending['lending_fee_per_hour'] * $hoursUsed;

        if ($fee > 0) {
            $borrowerFarm = new Farm($farmId);
            if (!$borrowerFarm->subtractMoney($fee, 'Ausleihgebuehr')) {
                return ['success' => false, 'message' => 'Nicht genuegend Geld fuer Ausleihgebuehr'];
            }

            // Zahle an Eigentuemer
            $ownerFarm = new Farm($lending['owner_farm_id']);
            $ownerFarm->addMoney($fee, 'Ausleihgebuehr erhalten');
        }

        // Aktualisiere Log
        $this->db->update('equipment_lending_log', [
            'returned_at' => date('Y-m-d H:i:s'),
            'hours_used' => $hoursUsed,
            'fee_paid' => $fee
        ], 'id = :id', ['id' => $lending['id']]);

        // Markiere als verfuegbar
        $this->db->update('cooperative_shared_equipment', ['available' => true], 'id = :id', ['id' => $sharedEquipmentId]);

        Logger::info('Equipment returned', [
            'farm_id' => $farmId,
            'equipment_id' => $sharedEquipmentId,
            'hours' => $hoursUsed,
            'fee' => $fee
        ]);

        return [
            'success' => true,
            'message' => "Geraet zurueckgegeben. Gebuehr: {$fee} T"
        ];
    }

    /**
     * Spendet an die Genossenschaftskasse
     */
    public function donate(int $farmId, float $amount): array
    {
        $membership = $this->getMembership($farmId);

        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        $farm = new Farm($farmId);

        if (!$farm->subtractMoney($amount, "Spende an Genossenschaft")) {
            return ['success' => false, 'message' => 'Nicht genuegend Geld'];
        }

        // Erhoehe Treasury
        $this->db->query(
            'UPDATE cooperatives SET treasury = treasury + ? WHERE id = ?',
            [$amount, $membership['cooperative_id']]
        );

        // Erhoehe Beitragspunkte
        $contributionPoints = (int) ($amount / 10);
        $this->db->query(
            'UPDATE cooperative_members SET contribution_points = contribution_points + ? WHERE farm_id = ?',
            [$contributionPoints, $farmId]
        );

        // Erhoehe Genossenschaftspunkte
        $this->db->query(
            'UPDATE cooperatives SET total_points = total_points + ? WHERE id = ?',
            [$contributionPoints, $membership['cooperative_id']]
        );

        $farm->addPoints(10, 'Spende an Genossenschaft');

        return [
            'success' => true,
            'message' => "{$amount} T an die Genossenschaft gespendet"
        ];
    }

    /**
     * Befoerdert ein Mitglied zum Admin
     */
    public function promoteToAdmin(int $promoterFarmId, int $targetFarmId): array
    {
        $membership = $this->getMembership($promoterFarmId);

        if (!$membership || !in_array($membership['role'], ['founder', 'admin'])) {
            return ['success' => false, 'message' => 'Keine Berechtigung'];
        }

        $targetMembership = $this->db->fetchOne(
            'SELECT * FROM cooperative_members WHERE farm_id = ? AND cooperative_id = ?',
            [$targetFarmId, $membership['cooperative_id']]
        );

        if (!$targetMembership) {
            return ['success' => false, 'message' => 'Mitglied nicht gefunden'];
        }

        if ($targetMembership['role'] !== 'member') {
            return ['success' => false, 'message' => 'Kann nur normale Mitglieder befoerdern'];
        }

        $this->db->update('cooperative_members', ['role' => 'admin'], 'id = :id', ['id' => $targetMembership['id']]);

        return [
            'success' => true,
            'message' => 'Mitglied zum Admin befoerdert'
        ];
    }
}
