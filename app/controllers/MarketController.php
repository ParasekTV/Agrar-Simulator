<?php
/**
 * Market Controller
 *
 * Verwaltet den Marktplatz.
 */
class MarketController extends Controller
{
    /**
     * Zeigt den Marktplatz
     */
    public function index(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $farm = new Farm($farmId);
        $marketModel = new Market();

        $page = (int) ($this->getQueryParam('page', 1));
        $itemType = $this->getQueryParam('type');
        $search = $this->getQueryParam('search');

        $listings = $marketModel->getListings($itemType, $search, $page);

        $data = [
            'title' => 'Marktplatz',
            'listings' => $listings['listings'],
            'pagination' => [
                'page' => $listings['page'],
                'totalPages' => $listings['total_pages'],
                'total' => $listings['total']
            ],
            'myListings' => $marketModel->getMyListings($farmId),
            'inventory' => $farm->getInventory(),
            'farm' => $farm->getData(),
            'filter' => [
                'type' => $itemType,
                'search' => $search
            ],
            'pushOptions' => $marketModel->getPushOptions()
        ];

        $this->renderWithLayout('market/index', $data);
    }

    /**
     * Erstellt ein Angebot (POST)
     */
    public function create(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/market');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('item_type')
            ->required('item_id')
            ->required('quantity')
            ->numeric('quantity')
            ->min('quantity', 1)
            ->required('price')
            ->numeric('price')
            ->min('price', 0.01);

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/market');
        }

        // Hole Item-Namen
        $inventory = $this->db->fetchOne(
            'SELECT item_name FROM inventory WHERE farm_id = ? AND item_type = ? AND item_id = ?',
            [$this->getFarmId(), $data['item_type'], $data['item_id']]
        );

        $marketModel = new Market();
        $result = $marketModel->createListing(
            $this->getFarmId(),
            $data['item_type'],
            (int) $data['item_id'],
            $inventory['item_name'] ?? 'Unbekannt',
            (int) $data['quantity'],
            (float) $data['price']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/market');
    }

    /**
     * Kauft von einem Angebot (POST)
     */
    public function buy(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/market');
        }

        $data = $this->getPostData();

        $marketModel = new Market();
        $result = $marketModel->buy(
            (int) $data['listing_id'],
            $this->getFarmId(),
            (int) $data['quantity']
        );

        if ($result['success']) {
            // Aktualisiere Herausforderungsfortschritt
            $ranking = new Ranking();
            $ranking->updateChallengeProgress($this->getFarmId(), 'sales', (int) $result['total_price']);
        }

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/market');
    }

    /**
     * Storniert ein Angebot (POST)
     */
    public function cancel(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/market');
        }

        $data = $this->getPostData();

        $marketModel = new Market();
        $result = $marketModel->cancel((int) $data['listing_id'], $this->getFarmId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/market');
    }

    /**
     * Direktverkauf an NPC (POST)
     */
    public function sellDirect(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/inventory');
        }

        $data = $this->getPostData();

        $marketModel = new Market();
        $result = $marketModel->sellToNpc(
            $this->getFarmId(),
            $data['item_type'],
            (int) $data['item_id'],
            (int) $data['quantity']
        );

        if ($result['success']) {
            // Aktualisiere Herausforderungsfortschritt
            $ranking = new Ranking();
            $ranking->updateChallengeProgress($this->getFarmId(), 'sales', (int) $result['total_price']);
        }

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/inventory');
    }

    /**
     * Zeigt Handelshistorie
     */
    public function history(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $marketModel = new Market();

        $data = [
            'title' => 'Handelshistorie',
            'purchases' => $marketModel->getPurchaseHistory($farmId),
            'sales' => $marketModel->getSalesHistory($farmId)
        ];

        $this->renderWithLayout('market/history', $data);
    }

    /**
     * API: Gibt Marktangebote zurück
     */
    public function listingsApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $page = (int) ($this->getQueryParam('page', 1));
        $type = $this->getQueryParam('type');
        $search = $this->getQueryParam('search');

        $marketModel = new Market();
        $result = $marketModel->getListings($type, $search, $page);

        return $this->json($result);
    }

    /**
     * API: Erstellt ein Angebot
     */
    public function createApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['itemType']) || empty($data['itemId']) ||
            empty($data['quantity']) || empty($data['pricePerUnit'])) {
            return $this->jsonError('itemType, itemId, quantity und pricePerUnit erforderlich');
        }

        // Hole Item-Namen
        $inventory = $this->db->fetchOne(
            'SELECT item_name FROM inventory WHERE farm_id = ? AND item_type = ? AND item_id = ?',
            [$this->getFarmId(), $data['itemType'], $data['itemId']]
        );

        $marketModel = new Market();
        $result = $marketModel->createListing(
            $this->getFarmId(),
            $data['itemType'],
            (int) $data['itemId'],
            $inventory['item_name'] ?? 'Unbekannt',
            (int) $data['quantity'],
            (float) $data['pricePerUnit']
        );

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['listing_id' => $result['listing_id'] ?? null])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Kauft von einem Angebot
     */
    public function buyApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['listingId']) || empty($data['quantity'])) {
            return $this->jsonError('listingId und quantity erforderlich');
        }

        $marketModel = new Market();
        $result = $marketModel->buy(
            (int) $data['listingId'],
            $this->getFarmId(),
            (int) $data['quantity']
        );

        if ($result['success']) {
            // Aktualisiere Herausforderungsfortschritt
            $ranking = new Ranking();
            $ranking->updateChallengeProgress($this->getFarmId(), 'sales', (int) $result['total_price']);
        }

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['total_price' => $result['total_price'] ?? 0])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Storniert ein Angebot
     */
    public function cancelApi(int $id): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $marketModel = new Market();
        $result = $marketModel->cancel($id, $this->getFarmId());

        return $result['success']
            ? $this->jsonSuccess($result['message'])
            : $this->jsonError($result['message']);
    }

    // ==========================================
    // PUSH-FUNKTIONALITÄT (v1.2)
    // ==========================================

    /**
     * Pusht ein Angebot (POST)
     */
    public function push(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/market');
        }

        $data = $this->getPostData();

        $marketModel = new Market();
        $result = $marketModel->pushListing(
            (int) $data['listing_id'],
            (int) $data['push_config_id'],
            $this->getFarmId()
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/market');
    }

    /**
     * API: Gibt Push-Optionen zurück
     */
    public function pushOptionsApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $marketModel = new Market();
        return $this->json(['options' => $marketModel->getPushOptions()]);
    }

    /**
     * API: Pusht ein Angebot
     */
    public function pushApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['listingId']) || empty($data['pushConfigId'])) {
            return $this->jsonError('listingId und pushConfigId erforderlich');
        }

        $marketModel = new Market();
        $result = $marketModel->pushListing(
            (int) $data['listingId'],
            (int) $data['pushConfigId'],
            $this->getFarmId()
        );

        return $result['success']
            ? $this->jsonSuccess($result['message'], ['pushed_until' => $result['pushed_until'] ?? null])
            : $this->jsonError($result['message']);
    }
}
