<?php
/**
 * Shop Controller
 *
 * Verwaltet Händler und Einkäufe.
 */
class ShopController extends Controller
{
    /**
     * Zeigt Shop-Übersicht mit allen Händlern
     */
    public function index(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $shopModel = new Shop();
        $farm = new Farm($farmId);

        $data = [
            'title' => 'Einkauf',
            'dealers' => $shopModel->getAllDealers(),
            'priceOverview' => $shopModel->getDailyPriceOverview(),
            'buyableProducts' => $shopModel->getAllBuyableProducts(),
            'farm' => $farm->getData(),
            'priceChangeTime' => Shop::getTimeUntilPriceChange()
        ];

        $this->renderWithLayout('shop/index', $data);
    }

    /**
     * Sucht nach Produkten
     */
    public function search(): void
    {
        $this->requireAuth();

        $query = trim($this->getQueryParam('q', ''));
        $shopModel = new Shop();
        $farm = new Farm($this->getFarmId());

        $data = [
            'title' => 'Produktsuche',
            'query' => $query,
            'results' => !empty($query) ? $shopModel->searchProduct($query) : [],
            'priceChangeTime' => Shop::getTimeUntilPriceChange(),
            'farm' => $farm->getData()
        ];

        $this->renderWithLayout('shop/search', $data);
    }

    /**
     * Zeigt einzelnen Händler
     */
    public function show(int $id): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $shopModel = new Shop();
        $farm = new Farm($farmId);

        $dealer = $shopModel->getDealer($id);

        if (!$dealer) {
            Session::setFlash('error', 'Händler nicht gefunden', 'danger');
            $this->redirect('/shop');
        }

        $data = [
            'title' => $dealer['name'],
            'dealer' => $dealer,
            'farm' => $farm->getData()
        ];

        $this->renderWithLayout('shop/show', $data);
    }

    /**
     * Kauft Produkte (POST)
     */
    public function buy(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/shop');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('dealer_id')
            ->required('product_id')
            ->required('quantity')
            ->numeric('dealer_id')
            ->numeric('product_id')
            ->numeric('quantity')
            ->min('quantity', 1, 'Mindestens 1 Einheit');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/shop/' . ($data['dealer_id'] ?? ''));
        }

        $shopModel = new Shop();
        $result = $shopModel->buyProduct(
            $this->getFarmId(),
            (int) $data['dealer_id'],
            (int) $data['product_id'],
            (int) $data['quantity']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/shop/' . $data['dealer_id']);
    }

    /**
     * Zeigt Einkaufshistorie
     */
    public function history(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $shopModel = new Shop();

        $data = [
            'title' => 'Einkaufshistorie',
            'history' => $shopModel->getPurchaseHistory($farmId, 100)
        ];

        $this->renderWithLayout('shop/history', $data);
    }

    /**
     * Zeigt Preisvergleich für ein Produkt
     */
    public function compare(int $productId): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $shopModel = new Shop();
        $storageModel = new Storage();
        $farm = new Farm($farmId);

        $bestPrices = $shopModel->getBestPricesForProduct($productId);

        // Hole Produkt-Info
        $db = Database::getInstance();
        $product = $db->fetchOne('SELECT * FROM products WHERE id = ?', [$productId]);

        if (!$product) {
            Session::setFlash('error', 'Produkt nicht gefunden', 'danger');
            $this->redirect('/shop');
        }

        $data = [
            'title' => 'Preisvergleich: ' . $product['name_de'],
            'product' => $product,
            'prices' => $bestPrices,
            'inStock' => $storageModel->getProductQuantity($farmId, $productId),
            'farm' => $farm->getData()
        ];

        $this->renderWithLayout('shop/compare', $data);
    }

    /**
     * API: Gibt Händler zurück
     */
    public function listApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $shopModel = new Shop();
        $dealers = $shopModel->getAllDealers();

        return $this->json(['dealers' => $dealers]);
    }

    /**
     * API: Gibt Preise eines Händlers zurück
     */
    public function pricesApi(int $id): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $shopModel = new Shop();
        $dealer = $shopModel->getDealer($id);

        if (!$dealer) {
            return $this->jsonError('Händler nicht gefunden', 404);
        }

        return $this->json([
            'dealer' => $dealer['name'],
            'products' => $dealer['products']
        ]);
    }

    /**
     * API: Kauft Produkte
     */
    public function buyApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['dealerId']) || empty($data['productId']) || empty($data['quantity'])) {
            return $this->jsonError('dealerId, productId und quantity erforderlich');
        }

        $shopModel = new Shop();
        $result = $shopModel->buyProduct(
            $this->getFarmId(),
            (int) $data['dealerId'],
            (int) $data['productId'],
            (int) $data['quantity']
        );

        return $result['success']
            ? $this->jsonSuccess($result['message'], [
                'cost' => $result['cost'],
                'price_per_unit' => $result['price_per_unit']
            ])
            : $this->jsonError($result['message']);
    }

    /**
     * API: Gibt beste Preise für Produkt zurück
     */
    public function bestPricesApi(int $productId): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $shopModel = new Shop();
        $prices = $shopModel->getBestPricesForProduct($productId);

        return $this->json(['prices' => $prices]);
    }

    /**
     * API: Gibt Einkaufshistorie zurück
     */
    public function historyApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $limit = (int) ($_GET['limit'] ?? 50);
        $limit = min(100, max(1, $limit));

        $shopModel = new Shop();
        $history = $shopModel->getPurchaseHistory($this->getFarmId(), $limit);

        return $this->json(['history' => $history]);
    }

    /**
     * API: Sucht nach Produkten
     */
    public function searchApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $query = trim($_GET['q'] ?? '');

        if (empty($query)) {
            return $this->jsonError('Suchbegriff erforderlich');
        }

        $shopModel = new Shop();
        $results = $shopModel->searchProduct($query);

        return $this->json([
            'query' => $query,
            'results' => $results,
            'priceChangeTime' => Shop::getTimeUntilPriceChange()
        ]);
    }

    /**
     * API: Gibt Zeit bis zur Preisaktualisierung zurück
     */
    public function priceChangeTimeApi(): array
    {
        return $this->json(Shop::getTimeUntilPriceChange());
    }
}
