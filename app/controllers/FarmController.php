<?php
/**
 * Farm Controller
 *
 * Verwaltet die Hauptansicht und Statistiken der Farm.
 */
class FarmController extends Controller
{
    /**
     * Zeigt das Dashboard
     */
    public function dashboard(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);

        $data = [
            'title' => 'Dashboard',
            'farm' => $farm->getData(),
            'stats' => $farm->getStats(),
            'fields' => $farm->getFields(),
            'recentEvents' => $farm->getRecentEvents(5),
            'activeResearch' => $farm->getActiveResearch()
        ];

        // Hole Herausforderungen
        $ranking = new Ranking();
        $data['challenges'] = $ranking->getChallengeProgress($farmId);

        $this->renderWithLayout('dashboard', $data);
    }

    /**
     * Zeigt die Farm-Uebersicht
     */
    public function overview(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);

        $data = [
            'title' => 'Meine Farm',
            'farm' => $farm->getData(),
            'stats' => $farm->getStats(),
            'fields' => $farm->getFields(),
            'animals' => $farm->getAnimals(),
            'vehicles' => $farm->getVehicles(),
            'buildings' => $farm->getBuildings()
        ];

        $this->renderWithLayout('farm/overview', $data);
    }

    /**
     * Zeigt das Inventar
     */
    public function inventory(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);

        $data = [
            'title' => 'Inventar',
            'inventory' => $farm->getInventory(),
            'storageUsed' => $farm->getCurrentStorageUsed(),
            'storageCapacity' => $farm->getTotalStorageCapacity()
        ];

        $this->renderWithLayout('farm/inventory', $data);
    }

    /**
     * Zeigt das Event-Log
     */
    public function events(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);

        $page = (int) ($this->getQueryParam('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $events = $this->db->fetchAll(
            "SELECT * FROM game_events
             WHERE farm_id = ?
             ORDER BY created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            [$farmId]
        );

        $total = $this->db->count('game_events', 'farm_id = ?', [$farmId]);

        $data = [
            'title' => 'Ereignisse',
            'events' => $events,
            'page' => $page,
            'totalPages' => ceil($total / $perPage)
        ];

        $this->renderWithLayout('farm/events', $data);
    }

    /**
     * API: Gibt Farm-Statistiken zurueck
     */
    public function statsApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);

        return $this->json($farm->getStats());
    }

    /**
     * API: Gibt Farm-Daten zurueck
     */
    public function dataApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);

        return $this->json([
            'farm' => $farm->getData(),
            'stats' => $farm->getStats()
        ]);
    }

    /**
     * API: Gibt Felder zurueck
     */
    public function fieldsApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);

        return $this->json(['fields' => $farm->getFields()]);
    }

    /**
     * API: Gibt Tiere zurueck
     */
    public function animalsApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $farmId = $this->getFarmId();
        $animal = new Animal();

        return $this->json(['animals' => $animal->getFarmAnimalsWithStatus($farmId)]);
    }

    /**
     * API: Gibt Fahrzeuge zurueck
     */
    public function vehiclesApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $farmId = $this->getFarmId();
        $vehicle = new Vehicle();

        return $this->json(['vehicles' => $vehicle->getFarmVehicles($farmId)]);
    }

    /**
     * API: Gibt Inventar zurueck
     */
    public function inventoryApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);

        return $this->json([
            'inventory' => $farm->getInventory(),
            'storage_used' => $farm->getCurrentStorageUsed(),
            'storage_capacity' => $farm->getTotalStorageCapacity()
        ]);
    }

    /**
     * API: Gibt Events zurueck
     */
    public function eventsApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);

        $limit = (int) ($this->getQueryParam('limit', 10));

        return $this->json(['events' => $farm->getRecentEvents($limit)]);
    }
}
