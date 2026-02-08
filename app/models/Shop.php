<?php
/**
 * Shop Model
 *
 * Verwaltet Händler und Einkäufe (Gegenstück zu SalesPoint für Verkäufe).
 */
class Shop
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Gibt alle aktiven Händler zurück
     */
    public function getAllDealers(): array
    {
        $dealers = $this->db->fetchAll(
            'SELECT *, COALESCE(name_de, name) as display_name FROM dealers WHERE is_active = 1 ORDER BY name_de, name'
        );

        foreach ($dealers as &$dealer) {
            $dealer['name'] = $dealer['display_name'];
            $dealer['product_count'] = $this->getDealerProductCount($dealer['id']);
        }

        return $dealers;
    }

    /**
     * Gibt einen einzelnen Händler zurück
     */
    public function getDealer(int $id): ?array
    {
        $dealer = $this->db->fetchOne(
            'SELECT *, COALESCE(name_de, name) as display_name FROM dealers WHERE id = ? AND is_active = 1',
            [$id]
        );

        if ($dealer) {
            $dealer['name'] = $dealer['display_name'];
            $dealer['products'] = $this->getDealerProducts($id);
        }

        return $dealer;
    }

    /**
     * Zählt Produkte eines Händlers
     */
    private function getDealerProductCount(int $dealerId): int
    {
        return (int) $this->db->fetchColumn(
            'SELECT COUNT(*) FROM dealer_products WHERE dealer_id = ? AND is_available = 1',
            [$dealerId]
        );
    }

    /**
     * Gibt Produkte eines Händlers mit Tagespreisen zurück
     */
    public function getDealerProducts(int $dealerId): array
    {
        $sql = "SELECT dp.*, p.name, p.name_de, p.category, p.icon, p.base_price,
                       d.price_modifier as dealer_modifier
                FROM dealer_products dp
                JOIN products p ON dp.product_id = p.id
                JOIN dealers d ON dp.dealer_id = d.id
                WHERE dp.dealer_id = ? AND dp.is_available = 1
                ORDER BY p.category, p.name_de";

        $products = $this->db->fetchAll($sql, [$dealerId]);

        foreach ($products as &$product) {
            $product['current_price'] = $this->calculateDailyPrice(
                $product['base_price'],
                $product['dealer_modifier'] ?? 1.0,
                $product['price_modifier'] ?? 1.0,
                $dealerId,
                $product['product_id']
            );
            $product['price_trend'] = $this->getPriceTrend($product['product_id'], $dealerId);
        }

        return $products;
    }

    /**
     * Berechnet den Tagespreis für ein Produkt (Kaufpreis = höher als Verkaufspreis)
     *
     * Seed: date('Ymd') + (dealerId * 1000) + (productId * 10) + 777
     * Variation: -10% bis +20%
     */
    public function calculateDailyPrice(float $basePrice, float $dealerModifier, float $productModifier, int $dealerId, int $productId): float
    {
        // Seed basierend auf Datum + Händler + Produkt + Offset (777 für Unterschied zu Verkaufspreis)
        $dateSeed = (int) date('Ymd');
        $seed = $dateSeed + ($dealerId * 1000) + ($productId * 10) + 777;
        mt_srand($seed);

        // Variation zwischen -10% und +20% (Kaufpreise tendieren höher)
        $variation = (mt_rand(0, 300) - 100) / 1000; // -0.1 bis +0.2

        // Kombinierter Modifier (Händler + Produkt-spezifisch)
        $combinedModifier = $dealerModifier * $productModifier;

        $finalPrice = $basePrice * $combinedModifier * (1 + $variation);

        // Reset random seed
        mt_srand();

        return round($finalPrice, 2);
    }

    /**
     * Gibt Preistrend zurück (steigend/fallend/stabil)
     */
    public function getPriceTrend(int $productId, int $dealerId): string
    {
        $product = $this->db->fetchOne(
            'SELECT dp.*, p.base_price, d.price_modifier as dealer_modifier
             FROM dealer_products dp
             JOIN products p ON dp.product_id = p.id
             JOIN dealers d ON dp.dealer_id = d.id
             WHERE dp.dealer_id = ? AND dp.product_id = ?',
            [$dealerId, $productId]
        );

        if (!$product) {
            return 'stable';
        }

        $todayPrice = $this->calculateDailyPrice(
            $product['base_price'],
            $product['dealer_modifier'] ?? 1.0,
            $product['price_modifier'] ?? 1.0,
            $dealerId,
            $productId
        );

        // Berechne gestrigen Preis
        $yesterdaySeed = (int) date('Ymd', strtotime('-1 day')) + ($dealerId * 1000) + ($productId * 10) + 777;
        mt_srand($yesterdaySeed);
        $variation = (mt_rand(0, 300) - 100) / 1000;
        $yesterdayPrice = $product['base_price'] * ($product['dealer_modifier'] ?? 1.0) * ($product['price_modifier'] ?? 1.0) * (1 + $variation);
        mt_srand();

        $diff = ($todayPrice - $yesterdayPrice) / $yesterdayPrice;

        if ($diff > 0.05) {
            return 'rising';
        } elseif ($diff < -0.05) {
            return 'falling';
        }

        return 'stable';
    }

    /**
     * Kauft Produkte von einem Händler
     */
    public function buyProduct(int $farmId, int $dealerId, int $productId, int $quantity): array
    {
        // Prüfe Händler
        $dealer = $this->getDealer($dealerId);
        if (!$dealer) {
            return ['success' => false, 'message' => 'Händler nicht gefunden'];
        }

        // Prüfe ob Produkt dort gekauft werden kann
        $productInfo = null;
        foreach ($dealer['products'] as $p) {
            if ($p['product_id'] == $productId) {
                $productInfo = $p;
                break;
            }
        }

        if (!$productInfo) {
            return ['success' => false, 'message' => 'Dieses Produkt wird hier nicht angeboten'];
        }

        // Prüfe Mengenbeschränkungen
        if ($quantity < $productInfo['min_quantity']) {
            return ['success' => false, 'message' => "Mindestbestellmenge: {$productInfo['min_quantity']}"];
        }

        if ($quantity > $productInfo['max_quantity']) {
            return ['success' => false, 'message' => "Maximale Bestellmenge: {$productInfo['max_quantity']}"];
        }

        // Berechne Preis
        $pricePerUnit = $productInfo['current_price'];
        $totalCost = $pricePerUnit * $quantity;

        // Prüfe Geld
        $farm = new Farm($farmId);
        $farmData = $farm->getData();

        if ($farmData['money'] < $totalCost) {
            return ['success' => false, 'message' => 'Nicht genug Geld vorhanden'];
        }

        // Ziehe Geld ab
        $farm->subtractMoney($totalCost, "Einkauf: {$quantity}x {$productInfo['name_de']} bei {$dealer['name']}");

        // Füge zum Lager hinzu
        $storage = new Storage();
        $storage->addProduct($farmId, $productId, $quantity);

        // Speichere Transaktion
        $this->db->insert('purchase_history', [
            'farm_id' => $farmId,
            'dealer_id' => $dealerId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price_per_unit' => $pricePerUnit,
            'total_amount' => $totalCost
        ]);

        Logger::info('Product purchased', [
            'farm_id' => $farmId,
            'dealer' => $dealer['name'],
            'product_id' => $productId,
            'quantity' => $quantity,
            'cost' => $totalCost
        ]);

        return [
            'success' => true,
            'message' => "{$quantity}x {$productInfo['name_de']} für " . number_format($totalCost, 0, ',', '.') . " T gekauft!",
            'cost' => $totalCost,
            'price_per_unit' => $pricePerUnit
        ];
    }

    /**
     * Gibt Einkaufshistorie zurück
     */
    public function getPurchaseHistory(int $farmId, int $limit = 50): array
    {
        $sql = "SELECT ph.*, COALESCE(d.name_de, d.name) as dealer_name, p.name_de as product_name, p.icon
                FROM purchase_history ph
                JOIN dealers d ON ph.dealer_id = d.id
                JOIN products p ON ph.product_id = p.id
                WHERE ph.farm_id = ?
                ORDER BY ph.created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$farmId, $limit]);
    }

    /**
     * Gibt beste Preise für ein Produkt zurück (günstigste zuerst)
     */
    public function getBestPricesForProduct(int $productId): array
    {
        $sql = "SELECT dp.*, COALESCE(d.name_de, d.name) as dealer_name, d.location,
                       p.base_price, d.price_modifier as dealer_modifier
                FROM dealer_products dp
                JOIN dealers d ON dp.dealer_id = d.id
                JOIN products p ON dp.product_id = p.id
                WHERE dp.product_id = ? AND d.is_active = 1 AND dp.is_available = 1";

        $dealers = $this->db->fetchAll($sql, [$productId]);

        foreach ($dealers as &$dealer) {
            $dealer['current_price'] = $this->calculateDailyPrice(
                $dealer['base_price'],
                $dealer['dealer_modifier'] ?? 1.0,
                $dealer['price_modifier'] ?? 1.0,
                $dealer['dealer_id'],
                $productId
            );
            $dealer['price_trend'] = $this->getPriceTrend($productId, $dealer['dealer_id']);
        }

        // Sortiere nach Preis (günstigster zuerst - anders als bei Verkauf!)
        usort($dealers, function ($a, $b) {
            return $a['current_price'] <=> $b['current_price'];
        });

        return $dealers;
    }

    /**
     * Gibt alle kaufbaren Produkte mit Preisvergleich zurück
     */
    public function getAllBuyableProducts(): array
    {
        // Hole alle Produkte die bei mindestens einem Händler verfügbar sind
        $sql = "SELECT DISTINCT p.id as product_id, p.name, p.name_de, p.category, p.icon, p.base_price
                FROM products p
                JOIN dealer_products dp ON p.id = dp.product_id
                JOIN dealers d ON dp.dealer_id = d.id
                WHERE d.is_active = 1 AND dp.is_available = 1
                ORDER BY p.category, p.name_de";

        $products = $this->db->fetchAll($sql);

        foreach ($products as &$product) {
            $bestPrices = $this->getBestPricesForProduct($product['product_id']);
            if (!empty($bestPrices)) {
                $product['best_prices'] = array_slice($bestPrices, 0, 3);
                $product['best_price'] = $bestPrices[0]['current_price'] ?? $product['base_price'];
                $product['best_dealer'] = $bestPrices[0]['dealer_name'] ?? 'Unbekannt';
            }
        }

        return $products;
    }

    /**
     * Gibt tägliche Preisübersicht zurück
     */
    public function getDailyPriceOverview(): array
    {
        $dealers = $this->getAllDealers();
        $overview = [];

        foreach ($dealers as $dealer) {
            $products = $this->getDealerProducts($dealer['id']);

            // Finde Produkte mit besonders guten/schlechten Preisen
            $goodDeals = [];
            $expensiveItems = [];

            foreach ($products as $product) {
                $diff = ($product['current_price'] - $product['base_price'] * ($dealer['price_modifier'] ?? 1.0)) / ($product['base_price'] * ($dealer['price_modifier'] ?? 1.0));

                if ($diff < -0.05) {
                    $goodDeals[] = $product;
                } elseif ($diff > 0.10) {
                    $expensiveItems[] = $product;
                }
            }

            $overview[] = [
                'dealer' => $dealer,
                'good_deals' => array_slice($goodDeals, 0, 5),
                'expensive_items' => array_slice($expensiveItems, 0, 5),
                'total_products' => count($products)
            ];
        }

        return $overview;
    }

    /**
     * Sucht nach Produkten und gibt alle Händler zurück, die das Produkt anbieten
     */
    public function searchProduct(string $query): array
    {
        // Suche passende Produkte
        $sql = "SELECT DISTINCT p.id, p.name, p.name_de, p.category, p.icon, p.base_price
                FROM products p
                JOIN dealer_products dp ON p.id = dp.product_id
                JOIN dealers d ON dp.dealer_id = d.id
                WHERE d.is_active = 1 AND dp.is_available = 1 AND (
                    p.name LIKE ? OR p.name_de LIKE ? OR p.category LIKE ?
                )
                ORDER BY p.name_de";

        $searchTerm = '%' . $query . '%';
        $products = $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm]);

        $results = [];

        foreach ($products as $product) {
            $prices = $this->getBestPricesForProduct($product['id']);

            $results[] = [
                'product' => $product,
                'dealers' => $prices,
                'best_price' => !empty($prices) ? $prices[0]['current_price'] : 0,
                'worst_price' => !empty($prices) ? end($prices)['current_price'] : 0,
                'dealer_count' => count($prices)
            ];
        }

        return $results;
    }

    /**
     * Gibt die Zeit bis zur nächsten Preisaktualisierung zurück (Sekunden bis Mitternacht)
     */
    public static function getSecondsUntilPriceChange(): int
    {
        $now = time();
        $midnight = strtotime('tomorrow midnight');
        return $midnight - $now;
    }

    /**
     * Gibt formatierte Zeit bis zur Preisaktualisierung zurück
     */
    public static function getTimeUntilPriceChange(): array
    {
        $seconds = self::getSecondsUntilPriceChange();
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return [
            'hours' => $hours,
            'minutes' => $minutes,
            'seconds' => $secs,
            'total_seconds' => $seconds,
            'formatted' => sprintf('%02d:%02d:%02d', $hours, $minutes, $secs)
        ];
    }
}
