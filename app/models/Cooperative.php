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
     * Gründet eine neue Genossenschaft
     */
    public function create(int $founderFarmId, string $name, string $description = ''): array
    {
        // Prüfe ob bereits Mitglied einer Genossenschaft
        if ($this->getMembership($founderFarmId)) {
            return ['success' => false, 'message' => 'Du bist bereits Mitglied einer Genossenschaft'];
        }

        // Prüfe ob Name verfügbar
        if ($this->db->exists('cooperatives', 'name = ?', [$name])) {
            return ['success' => false, 'message' => 'Name bereits vergeben'];
        }

        // Gründungskosten
        $cost = 5000;
        $farm = new Farm($founderFarmId);

        if (!$farm->subtractMoney($cost, "Genossenschaft gegründet: {$name}")) {
            return ['success' => false, 'message' => 'Nicht genügend Geld (5000 T benötigt)'];
        }

        // Erstelle Genossenschaft
        $coopId = $this->db->insert('cooperatives', [
            'name' => $name,
            'founder_farm_id' => $founderFarmId,
            'description' => $description
        ]);

        // Füge Gründer als Mitglied hinzu
        $this->db->insert('cooperative_members', [
            'cooperative_id' => $coopId,
            'farm_id' => $founderFarmId,
            'role' => 'founder'
        ]);

        $farm->addPoints(100, "Genossenschaft gegründet: {$name}");

        Logger::info('Cooperative created', [
            'coop_id' => $coopId,
            'founder_farm_id' => $founderFarmId,
            'name' => $name
        ]);

        return [
            'success' => true,
            'message' => "Genossenschaft '{$name}' gegründet!",
            'cooperative_id' => $coopId
        ];
    }

    /**
     * Tritt einer Genossenschaft bei
     */
    public function join(int $farmId, int $cooperativeId): array
    {
        // Prüfe ob bereits Mitglied einer Genossenschaft
        if ($this->getMembership($farmId)) {
            return ['success' => false, 'message' => 'Du bist bereits Mitglied einer Genossenschaft'];
        }

        // Hole Genossenschaft
        $coop = $this->db->fetchOne('SELECT * FROM cooperatives WHERE id = ?', [$cooperativeId]);

        if (!$coop) {
            return ['success' => false, 'message' => 'Genossenschaft nicht gefunden'];
        }

        // Prüfe Mitgliederlimit
        $memberCount = $this->db->count('cooperative_members', 'cooperative_id = ?', [$cooperativeId]);

        if ($memberCount >= $coop['member_limit']) {
            return ['success' => false, 'message' => 'Genossenschaft ist voll'];
        }

        // Füge Mitglied hinzu
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
     * Verlässt eine Genossenschaft
     */
    public function leave(int $farmId): array
    {
        $membership = $this->getMembership($farmId);

        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        // Gründer kann nicht verlassen (muss auflösen)
        if ($membership['role'] === 'founder') {
            return ['success' => false, 'message' => 'Gründer können die Genossenschaft nicht verlassen. Löse sie auf oder übertrage die Gründerrolle.'];
        }

        // Entferne geteilte Geräte
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
     * Löst eine Genossenschaft auf
     */
    public function dissolve(int $farmId): array
    {
        $membership = $this->getMembership($farmId);

        if (!$membership || $membership['role'] !== 'founder') {
            return ['success' => false, 'message' => 'Nur der Gründer kann die Genossenschaft auflösen'];
        }

        $coop = $this->db->fetchOne('SELECT * FROM cooperatives WHERE id = ?', [$membership['cooperative_id']]);

        // Verteile Treasury gleichmäßig
        if ($coop['treasury'] > 0) {
            $memberCount = $this->db->count('cooperative_members', 'cooperative_id = ?', [$coop['id']]);
            $share = $coop['treasury'] / $memberCount;

            $members = $this->db->fetchAll(
                'SELECT farm_id FROM cooperative_members WHERE cooperative_id = ?',
                [$coop['id']]
            );

            foreach ($members as $member) {
                $farm = new Farm($member['farm_id']);
                $farm->addMoney($share, "Genossenschaft aufgelöst: {$coop['name']}");
            }
        }

        // Lösche Genossenschaft (CASCADE löscht Mitglieder und geteilte Geräte)
        $this->db->delete('cooperatives', 'id = ?', [$coop['id']]);

        Logger::info('Cooperative dissolved', [
            'coop_id' => $coop['id'],
            'name' => $coop['name']
        ]);

        return [
            'success' => true,
            'message' => "Genossenschaft '{$coop['name']}' aufgelöst"
        ];
    }

    /**
     * Gibt die Mitgliedschaft einer Farm zurück
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
     * Gibt alle Genossenschaften zurück
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
     * Gibt Details einer Genossenschaft zurück
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

        // Hole geteilte Geräte (altes System)
        $oldEquipment = $this->db->fetchAll(
            "SELECT cse.id, cse.farm_vehicle_id, cse.available, fv.farm_id as owner_farm_id,
                    v.name as vehicle_name, v.vehicle_type, v.power_hp,
                    vb.name as brand_name, f.farm_name as owner_name
             FROM cooperative_shared_equipment cse
             JOIN farm_vehicles fv ON cse.farm_vehicle_id = fv.id
             JOIN vehicles v ON fv.vehicle_id = v.id
             LEFT JOIN vehicle_brands vb ON v.brand_id = vb.id
             JOIN farms f ON fv.farm_id = f.id
             WHERE cse.cooperative_id = ?",
            [$cooperativeId]
        );

        // Hole verliehene Fahrzeuge (neues System v1.2)
        $lentVehicles = $this->db->fetchAll(
            "SELECT fv.id, fv.id as farm_vehicle_id, fv.farm_id as owner_farm_id,
                    v.name as vehicle_name, v.vehicle_type, v.power_hp,
                    vb.name as brand_name, f.farm_name as owner_name,
                    CASE WHEN cvl.status = 'borrowed' THEN 0 ELSE 1 END as available
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             LEFT JOIN vehicle_brands vb ON v.brand_id = vb.id
             JOIN farms f ON fv.farm_id = f.id
             LEFT JOIN cooperative_vehicle_loans cvl ON fv.id = cvl.farm_vehicle_id AND cvl.status IN ('active', 'borrowed')
             WHERE fv.lent_to_cooperative_id = ?",
            [$cooperativeId]
        );

        // Kombiniere beide Listen
        $coop['shared_equipment'] = array_merge($oldEquipment, $lentVehicles);

        return $coop;
    }

    /**
     * Teilt ein Gerät mit der Genossenschaft
     */
    public function shareEquipment(int $farmId, int $farmVehicleId, float $feePerHour = 0): array
    {
        $membership = $this->getMembership($farmId);

        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        // Prüfe ob Fahrzeug existiert
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

        // Prüfe ob bereits geteilt
        if ($this->db->exists('cooperative_shared_equipment', 'farm_vehicle_id = ?', [$farmVehicleId])) {
            return ['success' => false, 'message' => 'Fahrzeug bereits geteilt'];
        }

        // Füge zu geteilten Geräten hinzu
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
     * Nimmt ein Gerät aus der Teilung
     */
    public function unshareEquipment(int $farmId, int $sharedEquipmentId): array
    {
        $equipment = $this->db->fetchOne(
            'SELECT * FROM cooperative_shared_equipment WHERE id = ? AND owner_farm_id = ?',
            [$sharedEquipmentId, $farmId]
        );

        if (!$equipment) {
            return ['success' => false, 'message' => 'Geteiltes Gerät nicht gefunden'];
        }

        if (!$equipment['available']) {
            return ['success' => false, 'message' => 'Gerät ist aktuell verliehen'];
        }

        $this->db->delete('cooperative_shared_equipment', 'id = ?', [$sharedEquipmentId]);

        return [
            'success' => true,
            'message' => 'Gerät nicht mehr geteilt'
        ];
    }

    /**
     * Leiht ein Gerät aus
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
            return ['success' => false, 'message' => 'Gerät nicht verfügbar'];
        }

        if ($equipment['owner_farm_id'] === $farmId) {
            return ['success' => false, 'message' => 'Du kannst dein eigenes Gerät nicht ausleihen'];
        }

        // Markiere als nicht verfügbar
        $this->db->update('cooperative_shared_equipment', ['available' => 0], 'id = :id', ['id' => $sharedEquipmentId]);

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
     * Gibt ein ausgeliehenes Gerät zurück
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

        // Berechne Gebühr
        $fee = $lending['lending_fee_per_hour'] * $hoursUsed;

        if ($fee > 0) {
            $borrowerFarm = new Farm($farmId);
            if (!$borrowerFarm->subtractMoney($fee, 'Ausleihgebühr')) {
                return ['success' => false, 'message' => 'Nicht genügend Geld für Ausleihgebühr'];
            }

            // Zahle an Eigentümer
            $ownerFarm = new Farm($lending['owner_farm_id']);
            $ownerFarm->addMoney($fee, 'Ausleihgebühr erhalten');
        }

        // Aktualisiere Log
        $this->db->update('equipment_lending_log', [
            'returned_at' => date('Y-m-d H:i:s'),
            'hours_used' => $hoursUsed,
            'fee_paid' => $fee
        ], 'id = :id', ['id' => $lending['id']]);

        // Markiere als verfügbar
        $this->db->update('cooperative_shared_equipment', ['available' => 1], 'id = :id', ['id' => $sharedEquipmentId]);

        Logger::info('Equipment returned', [
            'farm_id' => $farmId,
            'equipment_id' => $sharedEquipmentId,
            'hours' => $hoursUsed,
            'fee' => $fee
        ]);

        return [
            'success' => true,
            'message' => "Gerät zurückgegeben. Gebühr: {$fee} T"
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
            return ['success' => false, 'message' => 'Nicht genügend Geld'];
        }

        // Erhöhe Treasury
        $this->db->query(
            'UPDATE cooperatives SET treasury = treasury + ? WHERE id = ?',
            [$amount, $membership['cooperative_id']]
        );

        // Erhöhe Beitragspunkte
        $contributionPoints = (int) ($amount / 10);
        $this->db->query(
            'UPDATE cooperative_members SET contribution_points = contribution_points + ? WHERE farm_id = ?',
            [$contributionPoints, $farmId]
        );

        // Erhöhe Genossenschaftspunkte
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
     * Befördert ein Mitglied zum Admin
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
            return ['success' => false, 'message' => 'Kann nur normale Mitglieder befördern'];
        }

        $this->db->update('cooperative_members', ['role' => 'admin'], 'id = :id', ['id' => $targetMembership['id']]);

        return [
            'success' => true,
            'message' => 'Mitglied zum Admin befördert'
        ];
    }

    // ============================================
    // ERWEITERTE FUNKTIONEN
    // ============================================

    /**
     * Gibt alle verfügbaren Rollen zurück
     */
    public function getRoles(): array
    {
        return $this->db->fetchAll('SELECT * FROM cooperative_roles ORDER BY id ASC');
    }

    /**
     * Weist einem Mitglied eine Rolle zu
     */
    public function assignRole(int $assignerFarmId, int $targetFarmId, string $roleKey): array
    {
        $membership = $this->getMembership($assignerFarmId);

        if (!$membership || !in_array($membership['role'], ['founder', 'admin'])) {
            return ['success' => false, 'message' => 'Keine Berechtigung'];
        }

        // Nur Gründer kann Admin-Rollen vergeben
        $role = $this->db->fetchOne('SELECT * FROM cooperative_roles WHERE role_key = ?', [$roleKey]);
        if (!$role) {
            return ['success' => false, 'message' => 'Rolle nicht gefunden'];
        }

        if (in_array($roleKey, ['founder', 'admin']) && $membership['role'] !== 'founder') {
            return ['success' => false, 'message' => 'Nur der Gründer kann diese Rolle vergeben'];
        }

        $targetMembership = $this->db->fetchOne(
            'SELECT * FROM cooperative_members WHERE farm_id = ? AND cooperative_id = ?',
            [$targetFarmId, $membership['cooperative_id']]
        );

        if (!$targetMembership) {
            return ['success' => false, 'message' => 'Mitglied nicht gefunden'];
        }

        $this->db->update('cooperative_members', ['role' => $roleKey], 'farm_id = :farm_id AND cooperative_id = :coop_id', [
            'farm_id' => $targetFarmId,
            'coop_id' => $membership['cooperative_id']
        ]);

        $this->logTransaction($membership['cooperative_id'], 'fee', 0, "Rolle geändert zu: {$role['name']}", $assignerFarmId);

        return ['success' => true, 'message' => "Rolle '{$role['name']}' zugewiesen"];
    }

    /**
     * Prüft ob ein Mitglied eine bestimmte Berechtigung hat
     */
    public function hasPermission(int $farmId, string $permission): bool
    {
        $membership = $this->getMembership($farmId);
        if (!$membership) {
            return false;
        }

        $role = $this->db->fetchOne('SELECT * FROM cooperative_roles WHERE role_key = ?', [$membership['role']]);
        if (!$role) {
            return false;
        }

        $permissions = json_decode($role['permissions'] ?? '[]', true);
        return in_array('all', $permissions) || in_array($permission, $permissions);
    }

    // ============================================
    // BEWERBUNGSSYSTEM
    // ============================================

    /**
     * Sendet eine Bewerbung an eine Genossenschaft
     */
    public function applyToJoin(int $farmId, int $cooperativeId, string $message = ''): array
    {
        if ($this->getMembership($farmId)) {
            return ['success' => false, 'message' => 'Du bist bereits Mitglied einer Genossenschaft'];
        }

        $coop = $this->db->fetchOne('SELECT * FROM cooperatives WHERE id = ?', [$cooperativeId]);
        if (!$coop) {
            return ['success' => false, 'message' => 'Genossenschaft nicht gefunden'];
        }

        // Prüfe ob bereits beworben
        $existing = $this->db->fetchOne(
            'SELECT * FROM cooperative_applications WHERE farm_id = ? AND cooperative_id = ? AND status = ?',
            [$farmId, $cooperativeId, 'pending']
        );
        if ($existing) {
            return ['success' => false, 'message' => 'Du hast dich bereits beworben'];
        }

        $this->db->insert('cooperative_applications', [
            'cooperative_id' => $cooperativeId,
            'farm_id' => $farmId,
            'message' => $message
        ]);

        return ['success' => true, 'message' => 'Bewerbung gesendet'];
    }

    /**
     * Gibt offene Bewerbungen einer Genossenschaft zurück
     */
    public function getPendingApplications(int $cooperativeId): array
    {
        return $this->db->fetchAll(
            "SELECT ca.*, f.farm_name, f.level, f.points
             FROM cooperative_applications ca
             JOIN farms f ON ca.farm_id = f.id
             WHERE ca.cooperative_id = ? AND ca.status = 'pending'
             ORDER BY ca.applied_at DESC",
            [$cooperativeId]
        );
    }

    /**
     * Bearbeitet eine Bewerbung (annehmen/ablehnen)
     */
    public function processApplication(int $reviewerFarmId, int $applicationId, bool $accept): array
    {
        $membership = $this->getMembership($reviewerFarmId);
        if (!$membership || !$this->hasPermission($reviewerFarmId, 'manage_members')) {
            return ['success' => false, 'message' => 'Keine Berechtigung'];
        }

        $application = $this->db->fetchOne(
            "SELECT * FROM cooperative_applications WHERE id = ? AND cooperative_id = ? AND status = 'pending'",
            [$applicationId, $membership['cooperative_id']]
        );

        if (!$application) {
            return ['success' => false, 'message' => 'Bewerbung nicht gefunden'];
        }

        if ($accept) {
            // Prüfe Mitgliederlimit
            $coop = $this->db->fetchOne('SELECT * FROM cooperatives WHERE id = ?', [$membership['cooperative_id']]);
            $memberCount = $this->db->count('cooperative_members', 'cooperative_id = ?', [$membership['cooperative_id']]);

            if ($memberCount >= $coop['member_limit']) {
                return ['success' => false, 'message' => 'Genossenschaft ist voll'];
            }

            // Füge als Mitglied hinzu
            $this->db->insert('cooperative_members', [
                'cooperative_id' => $membership['cooperative_id'],
                'farm_id' => $application['farm_id'],
                'role' => 'member'
            ]);

            $this->db->update('cooperative_applications', [
                'status' => 'accepted',
                'reviewed_by' => $reviewerFarmId,
                'reviewed_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $applicationId]);

            $this->logTransaction($membership['cooperative_id'], 'fee', 0, 'Neues Mitglied durch Bewerbung', $application['farm_id']);

            return ['success' => true, 'message' => 'Bewerbung angenommen'];
        } else {
            $this->db->update('cooperative_applications', [
                'status' => 'rejected',
                'reviewed_by' => $reviewerFarmId,
                'reviewed_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $applicationId]);

            return ['success' => true, 'message' => 'Bewerbung abgelehnt'];
        }
    }

    // ============================================
    // MITGLIED ENTFERNEN
    // ============================================

    /**
     * Entfernt ein Mitglied aus der Genossenschaft
     */
    public function kickMember(int $kickerFarmId, int $targetFarmId): array
    {
        $membership = $this->getMembership($kickerFarmId);
        if (!$membership || !$this->hasPermission($kickerFarmId, 'manage_members')) {
            return ['success' => false, 'message' => 'Keine Berechtigung'];
        }

        $targetMembership = $this->db->fetchOne(
            'SELECT cm.*, f.farm_name FROM cooperative_members cm JOIN farms f ON cm.farm_id = f.id WHERE cm.farm_id = ? AND cm.cooperative_id = ?',
            [$targetFarmId, $membership['cooperative_id']]
        );

        if (!$targetMembership) {
            return ['success' => false, 'message' => 'Mitglied nicht gefunden'];
        }

        if ($targetMembership['role'] === 'founder') {
            return ['success' => false, 'message' => 'Der Gründer kann nicht entfernt werden'];
        }

        // Prüfe Hierarchie
        $kickerRole = $this->db->fetchOne('SELECT * FROM cooperative_roles WHERE role_key = ?', [$membership['role']]);
        $targetRole = $this->db->fetchOne('SELECT * FROM cooperative_roles WHERE role_key = ?', [$targetMembership['role']]);

        if ($targetRole && $kickerRole && $targetRole['id'] <= $kickerRole['id']) {
            return ['success' => false, 'message' => 'Du kannst keine gleichrangigen oder höheren Mitglieder entfernen'];
        }

        // Entferne geteilte Geräte
        $this->db->delete('cooperative_shared_equipment', 'owner_farm_id = ?', [$targetFarmId]);

        // Entferne Mitgliedschaft
        $this->db->delete('cooperative_members', 'farm_id = ? AND cooperative_id = ?', [$targetFarmId, $membership['cooperative_id']]);

        $this->logTransaction($membership['cooperative_id'], 'fee', 0, "Mitglied entfernt: {$targetMembership['farm_name']}", $kickerFarmId);

        return ['success' => true, 'message' => "{$targetMembership['farm_name']} wurde entfernt"];
    }

    // ============================================
    // LAGER/SILO SYSTEM
    // ============================================

    /**
     * Gibt das Genossenschaftslager zurück
     */
    public function getWarehouse(int $cooperativeId): array
    {
        return $this->db->fetchAll(
            'SELECT cw.*, p.name as product_name, p.category as product_category
             FROM cooperative_warehouse cw
             LEFT JOIN products p ON cw.product_id = p.id
             WHERE cw.cooperative_id = ?
             ORDER BY p.category, p.name',
            [$cooperativeId]
        );
    }

    /**
     * Lagert Items im Genossenschaftslager ein
     */
    public function depositToWarehouse(int $farmId, int $productId, int $quantity): array
    {
        $membership = $this->getMembership($farmId);
        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        // Prüfe ob Farm das Item hat (item_id in inventory-Tabelle)
        $farmItem = $this->db->fetchOne(
            'SELECT * FROM inventory WHERE farm_id = ? AND item_id = ? AND quantity >= ?',
            [$farmId, $productId, $quantity]
        );

        if (!$farmItem) {
            return ['success' => false, 'message' => 'Nicht genügend Items im Inventar'];
        }

        $product = $this->db->fetchOne('SELECT * FROM products WHERE id = ?', [$productId]);

        // Reduziere Farm-Inventar
        $newQty = $farmItem['quantity'] - $quantity;
        if ($newQty <= 0) {
            $this->db->delete('inventory', 'id = ?', [$farmItem['id']]);
        } else {
            $this->db->update('inventory', ['quantity' => $newQty], 'id = :id', ['id' => $farmItem['id']]);
        }

        // Erhöhe Genossenschaftslager
        $existing = $this->db->fetchOne(
            'SELECT * FROM cooperative_warehouse WHERE cooperative_id = ? AND product_id = ?',
            [$membership['cooperative_id'], $productId]
        );

        if ($existing) {
            $this->db->update('cooperative_warehouse',
                ['quantity' => $existing['quantity'] + $quantity],
                'id = :id', ['id' => $existing['id']]
            );
        } else {
            $this->db->insert('cooperative_warehouse', [
                'cooperative_id' => $membership['cooperative_id'],
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        // Log
        $this->db->insert('cooperative_warehouse_log', [
            'cooperative_id' => $membership['cooperative_id'],
            'farm_id' => $farmId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'transaction_type' => 'deposit'
        ]);

        $productName = $product['name'] ?? 'Produkt';
        return ['success' => true, 'message' => "{$quantity}x {$productName} eingelagert"];
    }

    /**
     * Entnimmt Items aus dem Genossenschaftslager
     */
    public function withdrawFromWarehouse(int $farmId, int $productId, int $quantity): array
    {
        $membership = $this->getMembership($farmId);
        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        if (!$this->hasPermission($farmId, 'manage_warehouse')) {
            return ['success' => false, 'message' => 'Keine Berechtigung zur Entnahme'];
        }

        $warehouseItem = $this->db->fetchOne(
            'SELECT * FROM cooperative_warehouse WHERE cooperative_id = ? AND product_id = ? AND quantity >= ?',
            [$membership['cooperative_id'], $productId, $quantity]
        );

        if (!$warehouseItem) {
            return ['success' => false, 'message' => 'Nicht genügend Items im Lager'];
        }

        $product = $this->db->fetchOne('SELECT * FROM products WHERE id = ?', [$productId]);

        // Reduziere Lager
        $newQty = $warehouseItem['quantity'] - $quantity;
        if ($newQty <= 0) {
            $this->db->delete('cooperative_warehouse', 'id = ?', [$warehouseItem['id']]);
        } else {
            $this->db->update('cooperative_warehouse', ['quantity' => $newQty], 'id = :id', ['id' => $warehouseItem['id']]);
        }

        // Erhöhe Farm-Inventar
        $farmItem = $this->db->fetchOne(
            'SELECT * FROM inventory WHERE farm_id = ? AND item_id = ?',
            [$farmId, $productId]
        );

        if ($farmItem) {
            $this->db->update('inventory',
                ['quantity' => $farmItem['quantity'] + $quantity],
                'id = :id', ['id' => $farmItem['id']]
            );
        } else {
            // Bestimme item_type aus Produkt-Kategorie
            $itemType = 'material';
            if ($product) {
                $cat = $product['category'] ?? '';
                if (in_array($cat, ['getreide', 'gemuese', 'obst', 'oelsaaten'])) {
                    $itemType = 'crop';
                } elseif (in_array($cat, ['fleisch', 'milchprodukte', 'eier'])) {
                    $itemType = 'animal_product';
                } elseif ($cat === 'kraftstoff') {
                    $itemType = 'fuel';
                }
            }
            $this->db->insert('inventory', [
                'farm_id' => $farmId,
                'item_id' => $productId,
                'item_type' => $itemType,
                'item_name' => $product['name'] ?? 'Unbekannt',
                'quantity' => $quantity
            ]);
        }

        // Log
        $this->db->insert('cooperative_warehouse_log', [
            'cooperative_id' => $membership['cooperative_id'],
            'farm_id' => $farmId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'transaction_type' => 'withdraw'
        ]);

        $productName = $product['name'] ?? 'Produkt';
        return ['success' => true, 'message' => "{$quantity}x {$productName} entnommen"];
    }

    // ============================================
    // FINANZEN
    // ============================================

    /**
     * Hebt Geld aus der Genossenschaftskasse ab
     */
    public function withdrawMoney(int $farmId, float $amount, string $reason = ''): array
    {
        $membership = $this->getMembership($farmId);
        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        if (!$this->hasPermission($farmId, 'manage_finances')) {
            return ['success' => false, 'message' => 'Keine Berechtigung'];
        }

        $coop = $this->db->fetchOne('SELECT * FROM cooperatives WHERE id = ?', [$membership['cooperative_id']]);
        if ($coop['treasury'] < $amount) {
            return ['success' => false, 'message' => 'Nicht genügend Geld in der Kasse'];
        }

        // Reduziere Treasury
        $this->db->query('UPDATE cooperatives SET treasury = treasury - ? WHERE id = ?', [$amount, $membership['cooperative_id']]);

        // Zahle an Farm
        $farm = new Farm($farmId);
        $farm->addMoney($amount, "Entnahme aus Genossenschaftskasse: {$reason}");

        $this->logTransaction($membership['cooperative_id'], 'payout', -$amount, $reason ?: 'Entnahme', $farmId);

        return ['success' => true, 'message' => number_format($amount, 0, ',', '.') . ' T entnommen'];
    }

    /**
     * Gibt Transaktionshistorie zurück
     */
    public function getTransactions(int $cooperativeId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT ct.*, f.farm_name
             FROM cooperative_transactions ct
             LEFT JOIN farms f ON ct.farm_id = f.id
             WHERE ct.cooperative_id = ?
             ORDER BY ct.created_at DESC
             LIMIT ?",
            [$cooperativeId, $limit]
        );
    }

    /**
     * Loggt eine Transaktion
     */
    private function logTransaction(int $cooperativeId, string $type, float $amount, string $description, ?int $farmId = null): void
    {
        $this->db->insert('cooperative_transactions', [
            'cooperative_id' => $cooperativeId,
            'farm_id' => $farmId,
            'transaction_type' => $type,
            'amount' => $amount,
            'description' => $description
        ]);
    }

    // ============================================
    // FORSCHUNG
    // ============================================

    /**
     * Gibt den Genossenschafts-Forschungsbaum zurück
     */
    public function getResearchTree(int $cooperativeId): array
    {
        $research = $this->db->fetchAll(
            "SELECT crt.*, cr.status, cr.started_at, cr.completed_at
             FROM cooperative_research_tree crt
             LEFT JOIN cooperative_research cr ON crt.id = cr.research_id AND cr.cooperative_id = ?
             ORDER BY crt.required_coop_level, crt.cost",
            [$cooperativeId]
        );

        return $research;
    }

    /**
     * Startet eine Genossenschafts-Forschung
     */
    public function startResearch(int $farmId, int $researchId): array
    {
        $membership = $this->getMembership($farmId);
        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        if (!$this->hasPermission($farmId, 'manage_research')) {
            return ['success' => false, 'message' => 'Keine Berechtigung'];
        }

        // Prüfe ob bereits aktive Forschung
        $active = $this->db->fetchOne(
            "SELECT * FROM cooperative_research WHERE cooperative_id = ? AND status = 'in_progress'",
            [$membership['cooperative_id']]
        );
        if ($active) {
            return ['success' => false, 'message' => 'Es läuft bereits eine Forschung'];
        }

        $research = $this->db->fetchOne('SELECT * FROM cooperative_research_tree WHERE id = ?', [$researchId]);
        if (!$research) {
            return ['success' => false, 'message' => 'Forschung nicht gefunden'];
        }

        // Prüfe Voraussetzungen
        if ($research['prerequisite_id']) {
            $prereq = $this->db->fetchOne(
                "SELECT * FROM cooperative_research WHERE cooperative_id = ? AND research_id = ? AND status = 'completed'",
                [$membership['cooperative_id'], $research['prerequisite_id']]
            );
            if (!$prereq) {
                return ['success' => false, 'message' => 'Voraussetzung nicht erfüllt'];
            }
        }

        // Prüfe Kosten
        $coop = $this->db->fetchOne('SELECT * FROM cooperatives WHERE id = ?', [$membership['cooperative_id']]);
        if ($coop['treasury'] < $research['cost']) {
            return ['success' => false, 'message' => 'Nicht genügend Geld in der Kasse'];
        }

        // Ziehe Kosten ab
        $this->db->query('UPDATE cooperatives SET treasury = treasury - ? WHERE id = ?', [$research['cost'], $membership['cooperative_id']]);

        // Starte Forschung
        $this->db->insert('cooperative_research', [
            'cooperative_id' => $membership['cooperative_id'],
            'research_id' => $researchId,
            'status' => 'in_progress',
            'started_at' => date('Y-m-d H:i:s')
        ]);

        $this->logTransaction($membership['cooperative_id'], 'purchase', -$research['cost'], "Forschung gestartet: {$research['name']}", $farmId);

        return ['success' => true, 'message' => "Forschung '{$research['name']}' gestartet"];
    }

    /**
     * Prüft und schließt fertige Forschungen ab
     */
    public function checkResearchCompletion(int $cooperativeId): void
    {
        $activeResearch = $this->db->fetchOne(
            "SELECT cr.*, crt.research_time_hours, crt.name
             FROM cooperative_research cr
             JOIN cooperative_research_tree crt ON cr.research_id = crt.id
             WHERE cr.cooperative_id = ? AND cr.status = 'in_progress'",
            [$cooperativeId]
        );

        if ($activeResearch) {
            $startTime = strtotime($activeResearch['started_at']);
            $endTime = $startTime + ($activeResearch['research_time_hours'] * 3600);

            if (time() >= $endTime) {
                $this->db->update('cooperative_research', [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s')
                ], 'id = :id', ['id' => $activeResearch['id']]);

                $this->logTransaction($cooperativeId, 'reward', 0, "Forschung abgeschlossen: {$activeResearch['name']}");
            }
        }
    }

    // ============================================
    // HERAUSFORDERUNGEN
    // ============================================

    /**
     * Gibt aktive Genossenschafts-Herausforderungen zurück
     */
    public function getActiveChallenges(int $cooperativeId): array
    {
        return $this->db->fetchAll(
            "SELECT cc.*, cct.name, cct.description, cct.challenge_period, cct.challenge_type,
                    cct.target_value, cct.reward_money, cct.reward_points
             FROM cooperative_challenges cc
             JOIN cooperative_challenge_templates cct ON cc.template_id = cct.id
             WHERE cc.cooperative_id = ? AND cc.is_completed = 0
             ORDER BY cc.end_date",
            [$cooperativeId]
        );
    }

    /**
     * Gibt Herausforderungs-Beiträge zurück
     */
    public function getChallengeContributions(int $challengeId): array
    {
        return $this->db->fetchAll(
            "SELECT ccc.*, f.farm_name
             FROM cooperative_challenge_contributions ccc
             JOIN farms f ON ccc.farm_id = f.id
             WHERE ccc.challenge_id = ?
             ORDER BY ccc.contribution_value DESC",
            [$challengeId]
        );
    }

    /**
     * Aktualisiert Herausforderungsfortschritt
     */
    public function updateChallengeProgress(int $cooperativeId, string $challengeType, int $amount, ?int $farmId = null): void
    {
        $challenges = $this->db->fetchAll(
            "SELECT cc.*, cct.challenge_type, cct.target_value
             FROM cooperative_challenges cc
             JOIN cooperative_challenge_templates cct ON cc.template_id = cct.id
             WHERE cc.cooperative_id = ? AND cc.is_completed = 0 AND cct.challenge_type = ?",
            [$cooperativeId, $challengeType]
        );

        foreach ($challenges as $challenge) {
            $newProgress = min($challenge['current_value'] + $amount, $challenge['target_value']);

            $this->db->update('cooperative_challenges', [
                'current_value' => $newProgress
            ], 'id = :id', ['id' => $challenge['id']]);

            // Logge Beitrag
            if ($farmId) {
                $this->db->insert('cooperative_challenge_contributions', [
                    'challenge_id' => $challenge['id'],
                    'farm_id' => $farmId,
                    'contribution_value' => $amount
                ]);
            }

            // Prüfe ob abgeschlossen
            if ($newProgress >= $challenge['target_value']) {
                $this->completeChallenge($challenge['id']);
            }
        }
    }

    /**
     * Schließt eine Herausforderung ab
     */
    private function completeChallenge(int $challengeId): void
    {
        $challenge = $this->db->fetchOne(
            "SELECT cc.*, cct.reward_money, cct.reward_points, cct.name
             FROM cooperative_challenges cc
             JOIN cooperative_challenge_templates cct ON cc.template_id = cct.id
             WHERE cc.id = ?",
            [$challengeId]
        );

        if (!$challenge || $challenge['is_completed']) {
            return;
        }

        $this->db->update('cooperative_challenges', [
            'is_completed' => 1,
            'completed_at' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $challengeId]);

        // Belohnung zur Kasse
        if ($challenge['reward_money'] > 0) {
            $this->db->query(
                'UPDATE cooperatives SET treasury = treasury + ? WHERE id = ?',
                [$challenge['reward_money'], $challenge['cooperative_id']]
            );
        }

        if ($challenge['reward_points'] > 0) {
            $this->db->query(
                'UPDATE cooperatives SET total_points = total_points + ? WHERE id = ?',
                [$challenge['reward_points'], $challenge['cooperative_id']]
            );
        }

        $this->logTransaction(
            $challenge['cooperative_id'],
            'reward',
            $challenge['reward_money'],
            "Herausforderung abgeschlossen: {$challenge['name']}"
        );
    }

    /**
     * Generiert neue Herausforderungen (wöchentlich/monatlich)
     */
    public function generateChallenges(int $cooperativeId): void
    {
        // Wöchentliche Herausforderungen
        $weeklyActive = $this->db->count(
            'cooperative_challenges cc JOIN cooperative_challenge_templates cct ON cc.template_id = cct.id',
            "cc.cooperative_id = ? AND cc.is_completed = 0 AND cct.challenge_period = 'weekly'",
            [$cooperativeId]
        );

        if ($weeklyActive < 3) {
            $templates = $this->db->fetchAll(
                "SELECT * FROM cooperative_challenge_templates
                 WHERE challenge_period = 'weekly' AND is_active = 1
                 ORDER BY RAND() LIMIT ?",
                [3 - $weeklyActive]
            );

            foreach ($templates as $template) {
                $this->db->insert('cooperative_challenges', [
                    'cooperative_id' => $cooperativeId,
                    'template_id' => $template['id'],
                    'start_date' => date('Y-m-d'),
                    'end_date' => date('Y-m-d', strtotime('+7 days'))
                ]);
            }
        }

        // Monatliche Herausforderungen
        $monthlyActive = $this->db->count(
            'cooperative_challenges cc JOIN cooperative_challenge_templates cct ON cc.template_id = cct.id',
            "cc.cooperative_id = ? AND cc.is_completed = 0 AND cct.challenge_period = 'monthly'",
            [$cooperativeId]
        );

        if ($monthlyActive < 2) {
            $templates = $this->db->fetchAll(
                "SELECT * FROM cooperative_challenge_templates
                 WHERE challenge_period = 'monthly' AND is_active = 1
                 ORDER BY RAND() LIMIT ?",
                [2 - $monthlyActive]
            );

            foreach ($templates as $template) {
                $this->db->insert('cooperative_challenges', [
                    'cooperative_id' => $cooperativeId,
                    'template_id' => $template['id'],
                    'start_date' => date('Y-m-d'),
                    'end_date' => date('Y-m-d', strtotime('+30 days'))
                ]);
            }
        }
    }

    // ============================================
    // FAHRZEUGVERLEIH (v1.2)
    // ============================================

    /**
     * Verleiht ein Fahrzeug an die Genossenschaft
     */
    public function lendVehicle(int $farmId, int $farmVehicleId, int $durationHours, float $hourlyFee = 0): array
    {
        $membership = $this->getMembership($farmId);
        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        // Prüfe ob Fahrzeug dem Spieler gehört
        $vehicle = $this->db->fetchOne(
            'SELECT fv.*, v.name as vehicle_name FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE fv.id = ? AND fv.farm_id = ?',
            [$farmVehicleId, $farmId]
        );

        if (!$vehicle) {
            return ['success' => false, 'message' => 'Fahrzeug nicht gefunden'];
        }

        // Prüfe ob bereits verliehen
        if ($vehicle['lent_to_cooperative_id']) {
            return ['success' => false, 'message' => 'Fahrzeug ist bereits verliehen'];
        }

        $lentUntil = date('Y-m-d H:i:s', strtotime("+{$durationHours} hours"));

        // Aktualisiere Fahrzeug
        $this->db->update('farm_vehicles', [
            'lent_to_cooperative_id' => $membership['cooperative_id'],
            'lent_at' => date('Y-m-d H:i:s'),
            'lent_until' => $lentUntil
        ], 'id = :id', ['id' => $farmVehicleId]);

        // Erstelle Verleih-Eintrag
        $this->db->insert('cooperative_vehicle_loans', [
            'farm_vehicle_id' => $farmVehicleId,
            'cooperative_id' => $membership['cooperative_id'],
            'lender_farm_id' => $farmId,
            'hourly_fee' => $hourlyFee,
            'lent_until' => $lentUntil,
            'status' => 'active'
        ]);

        Logger::info('Vehicle lent to cooperative', [
            'farm_id' => $farmId,
            'vehicle_id' => $farmVehicleId,
            'coop_id' => $membership['cooperative_id']
        ]);

        return [
            'success' => true,
            'message' => "{$vehicle['vehicle_name']} für {$durationHours} Stunden an die Genossenschaft verliehen"
        ];
    }

    /**
     * Holt ein verliehenes Fahrzeug zurück
     */
    public function returnLentVehicle(int $farmId, int $farmVehicleId): array
    {
        $vehicle = $this->db->fetchOne(
            'SELECT * FROM farm_vehicles WHERE id = ? AND farm_id = ? AND lent_to_cooperative_id IS NOT NULL',
            [$farmVehicleId, $farmId]
        );

        if (!$vehicle) {
            return ['success' => false, 'message' => 'Fahrzeug nicht gefunden oder nicht verliehen'];
        }

        // Aktualisiere Verleih-Eintrag
        $loan = $this->db->fetchOne(
            "SELECT * FROM cooperative_vehicle_loans WHERE farm_vehicle_id = ? AND status IN ('active', 'borrowed')",
            [$farmVehicleId]
        );

        if ($loan) {
            $hoursUsed = (time() - strtotime($loan['lent_at'])) / 3600;
            $totalFee = $hoursUsed * $loan['hourly_fee'];

            $this->db->update('cooperative_vehicle_loans', [
                'returned_at' => date('Y-m-d H:i:s'),
                'total_hours_used' => round($hoursUsed, 2),
                'total_fee_paid' => $totalFee,
                'status' => 'returned'
            ], 'id = :id', ['id' => $loan['id']]);

            // Zahle Gebühr an Verleiher
            if ($totalFee > 0) {
                $farm = new Farm($farmId);
                $farm->addMoney($totalFee, "Fahrzeugverleih-Gebühr");
            }
        }

        // Setze Fahrzeug zurück
        $this->db->update('farm_vehicles', [
            'lent_to_cooperative_id' => null,
            'lent_at' => null,
            'lent_until' => null
        ], 'id = :id', ['id' => $farmVehicleId]);

        return ['success' => true, 'message' => 'Fahrzeug zurückgeholt'];
    }

    /**
     * Gibt verliehene Fahrzeuge der Genossenschaft zurück
     */
    public function getLentVehicles(int $cooperativeId): array
    {
        return $this->db->fetchAll(
            "SELECT fv.*, v.name as vehicle_name, v.image_url, v.vehicle_type,
                    vb.name as brand_name, vb.logo_url as brand_logo,
                    f.farm_name as lender_name, cvl.hourly_fee, cvl.lent_at, cvl.lent_until,
                    cvl.status as loan_status, cvl.borrower_farm_id,
                    bf.farm_name as borrower_name
             FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             LEFT JOIN vehicle_brands vb ON v.brand_id = vb.id
             JOIN farms f ON fv.farm_id = f.id
             LEFT JOIN cooperative_vehicle_loans cvl ON fv.id = cvl.farm_vehicle_id AND cvl.status IN ('active', 'borrowed')
             LEFT JOIN farms bf ON cvl.borrower_farm_id = bf.id
             WHERE fv.lent_to_cooperative_id = ?
             ORDER BY v.name",
            [$cooperativeId]
        );
    }

    /**
     * Leiht ein Fahrzeug von der Genossenschaft aus
     */
    public function borrowVehicleFromCoop(int $farmId, int $farmVehicleId): array
    {
        $membership = $this->getMembership($farmId);
        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        $vehicle = $this->db->fetchOne(
            'SELECT fv.*, v.name as vehicle_name FROM farm_vehicles fv
             JOIN vehicles v ON fv.vehicle_id = v.id
             WHERE fv.id = ? AND fv.lent_to_cooperative_id = ?',
            [$farmVehicleId, $membership['cooperative_id']]
        );

        if (!$vehicle) {
            return ['success' => false, 'message' => 'Fahrzeug nicht gefunden'];
        }

        // Prüfe ob bereits ausgeliehen
        $loan = $this->db->fetchOne(
            "SELECT * FROM cooperative_vehicle_loans WHERE farm_vehicle_id = ? AND status = 'borrowed'",
            [$farmVehicleId]
        );

        if ($loan) {
            return ['success' => false, 'message' => 'Fahrzeug wird bereits von jemandem genutzt'];
        }

        // Aktualisiere Verleih-Status
        $this->db->query(
            "UPDATE cooperative_vehicle_loans SET borrower_farm_id = ?, status = 'borrowed' WHERE farm_vehicle_id = ? AND status = 'active'",
            [$farmId, $farmVehicleId]
        );

        return ['success' => true, 'message' => "{$vehicle['vehicle_name']} ausgeliehen"];
    }

    // ============================================
    // GENOSSENSCHAFTS-PRODUKTIONEN (v1.2)
    // ============================================

    /**
     * Gibt Genossenschafts-Produktionen zurück
     */
    public function getCoopProductions(int $cooperativeId): array
    {
        return $this->db->fetchAll(
            "SELECT cp.*, p.name, p.name_de, p.description, p.category, p.icon,
                    p.production_time, f.farm_name as purchased_by_name
             FROM cooperative_productions cp
             JOIN productions p ON cp.production_id = p.id
             LEFT JOIN farms f ON cp.purchased_by = f.id
             WHERE cp.cooperative_id = ? AND cp.is_active = 1
             ORDER BY p.name_de",
            [$cooperativeId]
        );
    }

    /**
     * Gibt verfügbare Produktionen zum Kauf zurück
     * Prüft welche Produktionen durch Genossenschafts-Forschung freigeschaltet wurden
     */
    public function getAvailableProductions(int $cooperativeId): array
    {
        // Hole alle freigeschalteten Produktionen durch Coop-Forschung
        $unlockedProductions = $this->db->fetchAll(
            "SELECT crt.unlocks
             FROM cooperative_research cr
             JOIN cooperative_research_tree crt ON cr.research_id = crt.id
             WHERE cr.cooperative_id = ? AND cr.status = 'completed'
               AND crt.unlocks LIKE 'production:%'",
            [$cooperativeId]
        );

        // Extrahiere Produktionsnamen aus unlocks
        $unlockedNames = [];
        foreach ($unlockedProductions as $row) {
            if (preg_match('/^production:(.+)$/', $row['unlocks'], $matches)) {
                $unlockedNames[] = $matches[1];
            }
        }

        if (empty($unlockedNames)) {
            return [];
        }

        // Hole die verfügbaren Produktionen
        $placeholders = implode(',', array_fill(0, count($unlockedNames), '?'));
        $params = array_merge($unlockedNames, [$cooperativeId]);

        return $this->db->fetchAll(
            "SELECT p.*, crt.name as research_name
             FROM productions p
             LEFT JOIN cooperative_research_tree crt ON crt.unlocks = CONCAT('production:', p.name)
             WHERE LOWER(p.name) IN ({$placeholders})
               AND p.is_active = 1
               AND p.id NOT IN (
                   SELECT production_id FROM cooperative_productions
                   WHERE cooperative_id = ? AND is_active = 1
               )
             ORDER BY p.building_cost ASC",
            $params
        );
    }

    /**
     * Kauft eine Produktion für die Genossenschaft
     */
    public function buyCoopProduction(int $farmId, int $productionId): array
    {
        $membership = $this->getMembership($farmId);
        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        if (!$this->hasPermission($farmId, 'manage_finances')) {
            return ['success' => false, 'message' => 'Keine Berechtigung'];
        }

        $production = $this->db->fetchOne('SELECT * FROM productions WHERE id = ?', [$productionId]);
        if (!$production) {
            return ['success' => false, 'message' => 'Produktion nicht gefunden'];
        }

        // Prüfe ob Produktion durch Genossenschafts-Forschung freigeschaltet ist
        $unlockKey = 'production:' . strtolower($production['name']);
        $hasResearch = $this->db->fetchOne(
            "SELECT cr.id FROM cooperative_research cr
             JOIN cooperative_research_tree crt ON cr.research_id = crt.id
             WHERE cr.cooperative_id = ? AND cr.status = 'completed'
               AND LOWER(crt.unlocks) = ?",
            [$membership['cooperative_id'], $unlockKey]
        );
        if (!$hasResearch) {
            return ['success' => false, 'message' => 'Genossenschafts-Forschung erforderlich'];
        }

        // Prüfe Kosten
        $coop = $this->db->fetchOne('SELECT * FROM cooperatives WHERE id = ?', [$membership['cooperative_id']]);
        if ($coop['treasury'] < $production['building_cost']) {
            return ['success' => false, 'message' => 'Nicht genügend Geld in der Kasse'];
        }

        // Ziehe Kosten ab
        $this->db->query(
            'UPDATE cooperatives SET treasury = treasury - ? WHERE id = ?',
            [$production['building_cost'], $membership['cooperative_id']]
        );

        // Erstelle Produktion
        $coopProdId = $this->db->insert('cooperative_productions', [
            'cooperative_id' => $membership['cooperative_id'],
            'production_id' => $productionId,
            'purchased_by' => $farmId
        ]);

        // Log
        $this->db->insert('cooperative_production_logs', [
            'cooperative_production_id' => $coopProdId,
            'cooperative_id' => $membership['cooperative_id'],
            'action' => 'purchased',
            'details' => "Produktion gekauft: {$production['name_de']}",
            'created_by' => $farmId
        ]);

        $this->logTransaction(
            $membership['cooperative_id'],
            'purchase',
            -$production['cost'],
            "Produktion gekauft: {$production['name_de']}",
            $farmId
        );

        return ['success' => true, 'message' => "{$production['name_de']} für die Genossenschaft gekauft"];
    }

    /**
     * Startet/Stoppt eine Genossenschafts-Produktion
     */
    public function toggleCoopProduction(int $farmId, int $coopProductionId): array
    {
        $membership = $this->getMembership($farmId);
        if (!$membership) {
            return ['success' => false, 'message' => 'Du bist in keiner Genossenschaft'];
        }

        $coopProd = $this->db->fetchOne(
            'SELECT cp.*, p.name_de FROM cooperative_productions cp
             JOIN productions p ON cp.production_id = p.id
             WHERE cp.id = ? AND cp.cooperative_id = ?',
            [$coopProductionId, $membership['cooperative_id']]
        );

        if (!$coopProd) {
            return ['success' => false, 'message' => 'Produktion nicht gefunden'];
        }

        $newStatus = $coopProd['is_running'] ? 0 : 1;
        $action = $newStatus ? 'started' : 'stopped';

        $this->db->update('cooperative_productions', [
            'is_running' => $newStatus,
            'started_at' => $newStatus ? date('Y-m-d H:i:s') : null
        ], 'id = :id', ['id' => $coopProductionId]);

        // Log
        $this->db->insert('cooperative_production_logs', [
            'cooperative_production_id' => $coopProductionId,
            'cooperative_id' => $membership['cooperative_id'],
            'action' => $action,
            'details' => ($newStatus ? 'Gestartet' : 'Gestoppt') . ": {$coopProd['name_de']}",
            'created_by' => $farmId
        ]);

        return [
            'success' => true,
            'message' => $newStatus ? 'Produktion gestartet' : 'Produktion gestoppt',
            'is_running' => $newStatus
        ];
    }
}
