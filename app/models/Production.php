<?php
/**
 * Production Model
 *
 * Verwaltet Produktionen und deren Inputs/Outputs.
 */
class Production
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Gibt alle verfügbaren Produktionen für eine Farm zurück
     */
    public function getAvailableProductions(int $farmId): array
    {
        $farm = new Farm($farmId);
        $farmLevel = $farm->getData()['level'] ?? 1;

        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM farm_productions fp WHERE fp.production_id = p.id AND fp.farm_id = ?) as owned
                FROM productions p
                LEFT JOIN farm_research fr ON p.required_research_id = fr.research_id
                    AND fr.farm_id = ? AND fr.status = 'completed'
                WHERE p.required_level <= ?
                  AND (p.required_research_id IS NULL OR fr.id IS NOT NULL)
                ORDER BY p.category, p.building_cost";

        return $this->db->fetchAll($sql, [$farmId, $farmId, $farmLevel]);
    }

    /**
     * Gibt alle Produktionen einer Farm zurück
     */
    public function getFarmProductions(int $farmId): array
    {
        $sql = "SELECT fp.*, p.name, p.name_de, p.category, p.icon, p.building_cost,
                       p.maintenance_cost, p.production_time, p.description,
                       TIMESTAMPDIFF(SECOND, fp.last_production_at, NOW()) as seconds_since_production
                FROM farm_productions fp
                JOIN productions p ON fp.production_id = p.id
                WHERE fp.farm_id = ?
                ORDER BY p.category, p.name_de";

        $productions = $this->db->fetchAll($sql, [$farmId]);

        // Füge Inputs und Outputs hinzu
        foreach ($productions as &$production) {
            $production['inputs'] = $this->getProductionInputs($production['production_id']);
            $production['outputs'] = $this->getProductionOutputs($production['production_id']);
            $production['can_produce'] = $this->canProduce($farmId, $production['production_id']);
            $production['ready_to_collect'] = $this->isReadyToCollect($production);
        }

        return $productions;
    }

    /**
     * Gibt eine einzelne Farm-Produktion zurück
     */
    public function getFarmProduction(int $farmProductionId, int $farmId): ?array
    {
        $sql = "SELECT fp.*, p.name, p.name_de, p.category, p.icon, p.building_cost,
                       p.maintenance_cost, p.production_time, p.description,
                       TIMESTAMPDIFF(SECOND, fp.last_production_at, NOW()) as seconds_since_production
                FROM farm_productions fp
                JOIN productions p ON fp.production_id = p.id
                WHERE fp.id = ? AND fp.farm_id = ?";

        $production = $this->db->fetchOne($sql, [$farmProductionId, $farmId]);

        if ($production) {
            $production['inputs'] = $this->getProductionInputs($production['production_id']);
            $production['outputs'] = $this->getProductionOutputs($production['production_id']);
            $production['can_produce'] = $this->canProduce($farmId, $production['production_id']);
            $production['ready_to_collect'] = $this->isReadyToCollect($production);
        }

        return $production;
    }

    /**
     * Gibt die Inputs einer Produktion zurück
     */
    public function getProductionInputs(int $productionId): array
    {
        $sql = "SELECT pi.*, pr.name, pr.name_de, pr.icon, pr.category as product_category
                FROM production_inputs pi
                JOIN products pr ON pi.product_id = pr.id
                WHERE pi.production_id = ?
                ORDER BY pr.name_de";

        return $this->db->fetchAll($sql, [$productionId]);
    }

    /**
     * Gibt die Outputs einer Produktion zurück
     */
    public function getProductionOutputs(int $productionId): array
    {
        $sql = "SELECT po.*, pr.name, pr.name_de, pr.icon, pr.category as product_category, pr.base_price
                FROM production_outputs po
                JOIN products pr ON po.product_id = pr.id
                WHERE po.production_id = ?
                ORDER BY pr.name_de";

        return $this->db->fetchAll($sql, [$productionId]);
    }

    /**
     * Kauft eine Produktion
     */
    public function buyProduction(int $farmId, int $productionId): array
    {
        // Prüfe ob Produktion existiert
        $production = $this->db->fetchOne('SELECT * FROM productions WHERE id = ?', [$productionId]);

        if (!$production) {
            return ['success' => false, 'message' => 'Produktion nicht gefunden'];
        }

        // Prüfe ob bereits gekauft
        $existing = $this->db->fetchOne(
            'SELECT id FROM farm_productions WHERE farm_id = ? AND production_id = ?',
            [$farmId, $productionId]
        );

        if ($existing) {
            return ['success' => false, 'message' => 'Diese Produktion besitzt du bereits'];
        }

        // Prüfe Level
        $farm = new Farm($farmId);
        $farmData = $farm->getData();

        if ($farmData['level'] < $production['required_level']) {
            return ['success' => false, 'message' => "Level {$production['required_level']} erforderlich"];
        }

        // Prüfe Forschung
        if ($production['required_research_id']) {
            if (!$farm->hasResearch($production['required_research_id'])) {
                return ['success' => false, 'message' => 'Forschung erforderlich'];
            }
        }

        // Prüfe und ziehe Geld ab
        if (!$farm->subtractMoney($production['building_cost'], "Gebäude: {$production['name_de']}")) {
            return ['success' => false, 'message' => 'Nicht genügend Geld'];
        }

        // Erstelle Farm-Produktion
        $farmProductionId = $this->db->insert('farm_productions', [
            'farm_id' => $farmId,
            'production_id' => $productionId,
            'is_active' => true
        ]);

        $farm->addPoints(POINTS_BUILDING, "Produktion gebaut: {$production['name_de']}");

        Logger::info('Production purchased', [
            'farm_id' => $farmId,
            'production_id' => $productionId,
            'name' => $production['name_de']
        ]);

        return [
            'success' => true,
            'message' => "{$production['name_de']} erfolgreich gebaut!",
            'farm_production_id' => $farmProductionId
        ];
    }

    /**
     * Aktiviert/Deaktiviert eine Produktion
     */
    public function toggleProduction(int $farmProductionId, int $farmId): array
    {
        $production = $this->db->fetchOne(
            'SELECT fp.*, p.name_de FROM farm_productions fp
             JOIN productions p ON fp.production_id = p.id
             WHERE fp.id = ? AND fp.farm_id = ?',
            [$farmProductionId, $farmId]
        );

        if (!$production) {
            return ['success' => false, 'message' => 'Produktion nicht gefunden'];
        }

        $newStatus = !$production['is_active'];

        $this->db->update('farm_productions', [
            'is_active' => $newStatus ? 1 : 0
        ], 'id = :id', ['id' => $farmProductionId]);

        $statusText = $newStatus ? 'aktiviert' : 'deaktiviert';

        Logger::info('Production toggled', [
            'farm_id' => $farmId,
            'farm_production_id' => $farmProductionId,
            'is_active' => $newStatus
        ]);

        return [
            'success' => true,
            'message' => "{$production['name_de']} wurde {$statusText}",
            'is_active' => $newStatus
        ];
    }

    /**
     * Prüft ob eine Produktion produzieren kann (genug Inputs)
     */
    public function canProduce(int $farmId, int $productionId): bool
    {
        $inputs = $this->getProductionInputs($productionId);
        $storage = new Storage();

        foreach ($inputs as $input) {
            if ($input['is_optional']) {
                continue;
            }

            $available = $storage->getProductQuantity($farmId, $input['product_id']);
            if ($available < $input['quantity']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prüft ob Produktion bereit zum Abholen ist
     */
    private function isReadyToCollect(array $production): bool
    {
        if (!$production['is_active'] || !$production['last_production_at']) {
            return false;
        }

        $secondsSince = $production['seconds_since_production'] ?? 0;
        $productionTime = $production['production_time'] ?? 3600;

        return $secondsSince >= $productionTime;
    }

    /**
     * Startet einen Produktionszyklus
     */
    public function startProduction(int $farmProductionId, int $farmId): array
    {
        $production = $this->getFarmProduction($farmProductionId, $farmId);

        if (!$production) {
            return ['success' => false, 'message' => 'Produktion nicht gefunden'];
        }

        if (!$production['is_active']) {
            return ['success' => false, 'message' => 'Produktion ist deaktiviert'];
        }

        if ($production['last_production_at'] && !$production['ready_to_collect']) {
            return ['success' => false, 'message' => 'Produktion läuft noch'];
        }

        // Prüfe und verbrauche Inputs
        $storage = new Storage();
        foreach ($production['inputs'] as $input) {
            if ($input['is_optional']) {
                continue;
            }

            $available = $storage->getProductQuantity($farmId, $input['product_id']);
            if ($available < $input['quantity']) {
                return [
                    'success' => false,
                    'message' => "Nicht genug {$input['name_de']} im Lager"
                ];
            }
        }

        // Verbrauche Inputs
        foreach ($production['inputs'] as $input) {
            if ($input['is_optional']) {
                // Optionale Inputs nur verbrauchen wenn vorhanden
                $available = $storage->getProductQuantity($farmId, $input['product_id']);
                if ($available >= $input['quantity']) {
                    $storage->removeProduct($farmId, $input['product_id'], $input['quantity']);
                }
            } else {
                $storage->removeProduct($farmId, $input['product_id'], $input['quantity']);
            }
        }

        // Starte Produktion
        $this->db->update('farm_productions', [
            'last_production_at' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $farmProductionId]);

        Logger::info('Production started', [
            'farm_id' => $farmId,
            'farm_production_id' => $farmProductionId,
            'name' => $production['name_de']
        ]);

        return [
            'success' => true,
            'message' => "Produktion in {$production['name_de']} gestartet!",
            'ready_at' => date('Y-m-d H:i:s', time() + $production['production_time'])
        ];
    }

    /**
     * Sammelt fertige Produkte ein
     */
    public function collectProduction(int $farmProductionId, int $farmId): array
    {
        $production = $this->getFarmProduction($farmProductionId, $farmId);

        if (!$production) {
            return ['success' => false, 'message' => 'Produktion nicht gefunden'];
        }

        if (!$production['ready_to_collect']) {
            return ['success' => false, 'message' => 'Produktion ist noch nicht fertig'];
        }

        // Füge Outputs zum Lager hinzu
        $storage = new Storage();
        $producedItems = [];

        foreach ($production['outputs'] as $output) {
            $storage->addProduct($farmId, $output['product_id'], $output['quantity']);
            $producedItems[] = "{$output['quantity']}x {$output['name_de']}";
        }

        // Aktualisiere Statistik
        $this->db->update('farm_productions', [
            'total_produced' => $production['total_produced'] + 1,
            'last_production_at' => null
        ], 'id = :id', ['id' => $farmProductionId]);

        // Vergebe Punkte
        $farm = new Farm($farmId);
        $farm->addPoints(POINTS_FIELD_WORK, "Produktion: {$production['name_de']}");

        Logger::info('Production collected', [
            'farm_id' => $farmId,
            'farm_production_id' => $farmProductionId,
            'outputs' => $producedItems
        ]);

        return [
            'success' => true,
            'message' => "Produziert: " . implode(', ', $producedItems),
            'items' => $producedItems
        ];
    }

    /**
     * Überträgt Produkte vom Lager zur Produktion
     */
    public function transferToProduction(int $farmId, int $farmProductionId, int $productId, int $quantity): array
    {
        $production = $this->getFarmProduction($farmProductionId, $farmId);

        if (!$production) {
            return ['success' => false, 'message' => 'Produktion nicht gefunden'];
        }

        // Prüfe ob Produkt ein Input ist
        $isInput = false;
        foreach ($production['inputs'] as $input) {
            if ($input['product_id'] == $productId) {
                $isInput = true;
                break;
            }
        }

        if (!$isInput) {
            return ['success' => false, 'message' => 'Dieses Produkt wird hier nicht benötigt'];
        }

        $storage = new Storage();
        $available = $storage->getProductQuantity($farmId, $productId);

        if ($available < $quantity) {
            return ['success' => false, 'message' => 'Nicht genug im Lager vorhanden'];
        }

        // Produkte werden beim Start der Produktion verbraucht, nicht vorher transferiert
        // Diese Funktion dient nur zur Info/Planung

        return [
            'success' => true,
            'message' => "Produkte sind für die Produktion verfügbar",
            'available' => $available
        ];
    }

    /**
     * Gibt Produktions-Kategorien zurück
     */
    public function getCategories(): array
    {
        return [
            'rohstoffe' => 'Rohstoffe',
            'verarbeitung' => 'Verarbeitung',
            'plantage' => 'Plantagen',
            'tierhaltung' => 'Tierhaltung',
            'verkauf' => 'Verkauf',
            'handel' => 'Handel',
            'energie' => 'Energie',
            'infrastruktur' => 'Infrastruktur',
            'produktion' => 'Produktion',
            'landwirtschaft' => 'Landwirtschaft'
        ];
    }
}
