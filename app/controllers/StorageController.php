<?php
/**
 * Storage Controller
 *
 * Verwaltet das Lager/Inventar.
 */
class StorageController extends Controller
{
    /**
     * Zeigt Lager-Übersicht
     */
    public function index(): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $storageModel = new Storage();
        $farm = new Farm($farmId);

        $data = [
            'title' => 'Lager',
            'storage' => $storageModel->getGroupedStorage($farmId),
            'stats' => $storageModel->getStorageStats($farmId),
            'categories' => $storageModel->getCategories(),
            'farm' => $farm->getData()
        ];

        $this->renderWithLayout('storage/index', $data);
    }

    /**
     * Zeigt Produkt-Details
     */
    public function product(int $productId): void
    {
        $this->requireAuth();

        $farmId = $this->getFarmId();
        $storageModel = new Storage();
        $salesModel = new SalesPoint();

        $quantity = $storageModel->getProductQuantity($farmId, $productId);
        $bestPrices = $salesModel->getBestPricesForProduct($productId);

        // Hole Produkt-Info
        $db = Database::getInstance();
        $product = $db->fetchOne(
            'SELECT * FROM products WHERE id = ?',
            [$productId]
        );

        if (!$product) {
            Session::setFlash('error', 'Produkt nicht gefunden', 'danger');
            $this->redirect('/storage');
        }

        $data = [
            'title' => $product['name_de'],
            'product' => $product,
            'quantity' => $quantity,
            'bestPrices' => $bestPrices
        ];

        $this->renderWithLayout('storage/product', $data);
    }

    /**
     * Transferiert Items zwischen Systemen (POST)
     */
    public function transfer(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/storage');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('item_type')
            ->required('item_id')
            ->required('quantity')
            ->numeric('item_id')
            ->numeric('quantity')
            ->min('quantity', 1, 'Mindestens 1 Einheit');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/storage');
        }

        $storageModel = new Storage();
        $result = $storageModel->convertInventoryToProduct(
            $this->getFarmId(),
            $data['item_type'],
            (int) $data['item_id'],
            (int) $data['quantity']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/storage');
    }

    /**
     * Sucht Produkte (GET)
     */
    public function search(): void
    {
        $this->requireAuth();

        $search = $_GET['q'] ?? '';
        $farmId = $this->getFarmId();
        $storageModel = new Storage();

        $results = $storageModel->searchProducts($search, $farmId);

        $data = [
            'title' => 'Suche: ' . htmlspecialchars($search),
            'results' => $results,
            'search' => $search
        ];

        $this->renderWithLayout('storage/search', $data);
    }

    /**
     * API: Gibt Lagerbestand zurück
     */
    public function listApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $storageModel = new Storage();
        $storage = $storageModel->getGroupedStorage($this->getFarmId());
        $stats = $storageModel->getStorageStats($this->getFarmId());

        return $this->json([
            'storage' => $storage,
            'stats' => $stats
        ]);
    }

    /**
     * API: Sucht Produkte
     */
    public function searchApi(): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $search = $_GET['q'] ?? '';

        if (strlen($search) < 2) {
            return $this->jsonError('Suchbegriff zu kurz');
        }

        $storageModel = new Storage();
        $results = $storageModel->searchProducts($search, $this->getFarmId());

        return $this->json(['results' => $results]);
    }

    /**
     * API: Gibt Produkt-Menge zurück
     */
    public function quantityApi(int $productId): array
    {
        if (!Session::isLoggedIn()) {
            return $this->jsonError('Nicht eingeloggt', 401);
        }

        $storageModel = new Storage();
        $quantity = $storageModel->getProductQuantity($this->getFarmId(), $productId);

        return $this->json([
            'product_id' => $productId,
            'quantity' => $quantity
        ]);
    }
}
