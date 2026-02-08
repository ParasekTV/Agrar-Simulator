<?php
/**
 * SalesPoint Controller
 *
 * Verwaltet Verkaufsstellen und Verkäufe.
 */
class SalesPointController extends Controller
{
    /**
     * Zeigt Verkaufsstellen-Übersicht
     */
    public function index(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $salesModel = new SalesPoint();
        $storageModel = new Storage();
        $farm = new Farm($farmId);

        $data = [
            'title' => 'Verkaufsstellen',
            'sellingPoints' => $salesModel->getAllSellingPoints(),
            'priceOverview' => $salesModel->getDailyPriceOverview(),
            'sellableProducts' => $salesModel->getAllSellableProducts($farmId),
            'storageStats' => $storageModel->getStorageStats($farmId),
            'farm' => $farm->getData(),
            'priceChangeTime' => SalesPoint::getTimeUntilPriceChange()
        ];

        $this->renderWithLayout('salespoints/index', $data);
    }

    /**
     * Sucht nach Produkten
     */
    public function search(): void
    {
        $this->requireAuth();

        $query = trim($this->getQueryParam('q', ''));
        $salesModel = new SalesPoint();

        $data = [
            'title' => 'Produktsuche',
            'query' => $query,
            'results' => !empty($query) ? $salesModel->searchProduct($query) : [],
            'priceChangeTime' => SalesPoint::getTimeUntilPriceChange()
        ];

        $this->renderWithLayout('salespoints/search', $data);
    }

    /**
     * Zeigt einzelne Verkaufsstelle
     */
    public function show(int $id): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $salesModel = new SalesPoint();
        $storageModel = new Storage();

        $sellingPoint = $salesModel->getSellingPoint($id);

        if (!$sellingPoint) {
            Session::setFlash('error', 'Verkaufsstelle nicht gefunden', 'danger');
            $this->redirect('/salespoints');
        }

        // Füge Lagerbestände zu Produkten hinzu
        foreach ($sellingPoint['products'] as &$product) {
            $product['in_stock'] = $storageModel->getProductQuantity($farmId, $product['product_id']);
        }

        $data = [
            'title' => $sellingPoint['name'],
            'sellingPoint' => $sellingPoint
        ];

        $this->renderWithLayout('salespoints/show', $data);
    }

    /**
     * Verkauft Produkte (POST)
     */
    public function sell(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/salespoints');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('selling_point_id')
            ->required('product_id')
            ->required('quantity')
            ->numeric('selling_point_id')
            ->numeric('product_id')
            ->numeric('quantity')
            ->min('quantity', 1, 'Mindestens 1 Einheit');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/salespoints/' . ($data['selling_point_id'] ?? ''));
        }

        $salesModel = new SalesPoint();
        $result = $salesModel->sellProduct(
            $this->getFarmId(),
            (int) $data['selling_point_id'],
            (int) $data['product_id'],
            (int) $data['quantity']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/salespoints/' . $data['selling_point_id']);
    }

    /**
     * Zeigt Verkaufshistorie
     */
    public function history(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $salesModel = new SalesPoint();

        $data = [
            'title' => 'Verkaufshistorie',
            'history' => $salesModel->getSalesHistory($farmId, 100)
        ];

        $this->renderWithLayout('salespoints/history', $data);
    }

    /**
     * Zeigt Preisvergleich für ein Produkt
     */
    public function compare(int $productId): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $salesModel = new SalesPoint();
        $storageModel = new Storage();

        $bestPrices = $salesModel->getBestPricesForProduct($productId);

        // Hole Produkt-Info
        $db = Database::getInstance();
        $product = $db->fetchOne('SELECT * FROM products WHERE id = ?', [$productId]);

        if (!$product) {
            Session::setFlash('error', 'Produkt nicht gefunden', 'danger');
            $this->redirect('/salespoints');
        }

        $data = [
            'title' => 'Preisvergleich: ' . $product['name_de'],
            'product' => $product,
            'prices' => $bestPrices,
            'inStock' => $storageModel->getProductQuantity($farmId, $productId)
        ];

        $this->renderWithLayout('salespoints/compare', $data);
    }

    /**
     * API: Gibt Verkaufsstellen zurück
     */
    public function listApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $salesModel = new SalesPoint();
        $sellingPoints = $salesModel->getAllSellingPoints();

        return $this->json(['sellingPoints' => $sellingPoints]);
    }

    /**
     * API: Gibt Preise einer Verkaufsstelle zurück
     */
    public function pricesApi(int $id): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $salesModel = new SalesPoint();
        $sellingPoint = $salesModel->getSellingPoint($id);

        if (!$sellingPoint) {
            return $this->jsonError('Verkaufsstelle nicht gefunden', 404);
        }

        return $this->json([
            'sellingPoint' => $sellingPoint['name'],
            'products' => $sellingPoint['products']
        ]);
    }

    /**
     * API: Verkauft Produkte
     */
    public function sellApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $data = $this->getJsonData();

        if (empty($data['sellingPointId']) || empty($data['productId']) || empty($data['quantity'])) {
            return $this->jsonError('sellingPointId, productId und quantity erforderlich');
        }

        $salesModel = new SalesPoint();
        $result = $salesModel->sellProduct(
            $this->getFarmId(),
            (int) $data['sellingPointId'],
            (int) $data['productId'],
            (int) $data['quantity']
        );

        return $result['success']
            ? $this->jsonSuccess($result['message'], [
                'earnings' => $result['earnings'],
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

        $salesModel = new SalesPoint();
        $prices = $salesModel->getBestPricesForProduct($productId);

        return $this->json(['prices' => $prices]);
    }

    /**
     * API: Gibt Verkaufshistorie zurück
     */
    public function historyApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $limit = (int) ($_GET['limit'] ?? 50);
        $limit = min(100, max(1, $limit));

        $salesModel = new SalesPoint();
        $history = $salesModel->getSalesHistory($this->getFarmId(), $limit);

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

        $salesModel = new SalesPoint();
        $results = $salesModel->searchProduct($query);

        return $this->json([
            'query' => $query,
            'results' => $results,
            'priceChangeTime' => SalesPoint::getTimeUntilPriceChange()
        ]);
    }

    /**
     * API: Gibt Zeit bis zur Preisaktualisierung zurück
     */
    public function priceChangeTimeApi(): array
    {
        return $this->json(SalesPoint::getTimeUntilPriceChange());
    }
}
