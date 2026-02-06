<?php
/**
 * SalesPoint Model
 *
 * Verwaltet Verkaufsstellen und dynamische Preise.
 */
class SalesPoint
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Gibt alle Verkaufsstellen zurück
     */
    public function getAllSellingPoints(): array
    {
        $points = $this->db->fetchAll(
            'SELECT *, COALESCE(name_de, name) as display_name FROM selling_points WHERE is_active = 1 ORDER BY name_de, name'
        );

        // Setze name für Views
        foreach ($points as &$point) {
            $point['name'] = $point['display_name'];
        }

        return $points;
    }

    /**
     * Gibt eine einzelne Verkaufsstelle zurück
     */
    public function getSellingPoint(int $id): ?array
    {
        $point = $this->db->fetchOne(
            'SELECT *, COALESCE(name_de, name) as display_name FROM selling_points WHERE id = ? AND is_active = 1',
            [$id]
        );

        if ($point) {
            $point['name'] = $point['display_name'];
            $point['products'] = $this->getSellingPointProducts($id);
        }

        return $point;
    }

    /**
     * Gibt Produkte einer Verkaufsstelle mit Tagespreisen zurück
     */
    public function getSellingPointProducts(int $sellingPointId): array
    {
        $sql = "SELECT spp.*, p.name, p.name_de, p.category, p.icon, p.base_price
                FROM selling_point_products spp
                JOIN products p ON spp.product_id = p.id
                WHERE spp.selling_point_id = ?
                ORDER BY p.category, p.name_de";

        $products = $this->db->fetchAll($sql, [$sellingPointId]);

        // Berechne Tagespreise
        foreach ($products as &$product) {
            $product['current_price'] = $this->calculateDailyPrice(
                $product['base_price'],
                $product['price_modifier'] ?? 1.0,
                $sellingPointId,
                $product['product_id']
            );
            $product['price_trend'] = $this->getPriceTrend($product['product_id'], $sellingPointId);
        }

        return $products;
    }

    /**
     * Berechnet den Tagespreis für ein Produkt
     * Preis variiert basierend auf:
     * - Tag des Jahres
     * - Verkaufsstelle
     * - Zufallsfaktor (täglich neu)
     */
    public function calculateDailyPrice(float $basePrice, float $modifier, int $sellingPointId, int $productId): float
    {
        // Seed basierend auf Datum + Verkaufsstelle + Produkt
        $dateSeed = (int) date('Ymd');
        $seed = $dateSeed + ($sellingPointId * 1000) + ($productId * 10);
        mt_srand($seed);

        // Variation zwischen -20% und +30%
        $variation = (mt_rand(0, 500) - 200) / 1000; // -0.2 bis +0.3

        // Saisonaler Faktor (optional, basierend auf Monat)
        $month = (int) date('n');
        $seasonalFactor = $this->getSeasonalFactor($productId, $month);

        $finalPrice = $basePrice * $modifier * (1 + $variation) * $seasonalFactor;

        // Reset random seed
        mt_srand();

        return round($finalPrice, 2);
    }

    /**
     * Gibt saisonalen Preisfaktor zurück
     */
    private function getSeasonalFactor(int $productId, int $month): float
    {
        // Hole Produkt-Kategorie
        $product = $this->db->fetchOne('SELECT category FROM products WHERE id = ?', [$productId]);
        $category = $product['category'] ?? 'allgemein';

        // Saisonale Anpassungen
        $seasonalFactors = [
            'feldfrucht' => [
                // Höhere Preise im Winter/Frühling (weniger Angebot)
                1 => 1.15, 2 => 1.20, 3 => 1.15, 4 => 1.10,
                // Niedrigere Preise zur Erntezeit
                5 => 1.00, 6 => 0.95, 7 => 0.85, 8 => 0.80,
                9 => 0.85, 10 => 0.90, 11 => 1.00, 12 => 1.10
            ],
            'tierprodukt' => [
                // Relativ stabil
                1 => 1.05, 2 => 1.05, 3 => 1.00, 4 => 0.95,
                5 => 0.95, 6 => 0.95, 7 => 1.00, 8 => 1.00,
                9 => 1.00, 10 => 1.05, 11 => 1.10, 12 => 1.15
            ],
            'getraenk' => [
                // Höhere Preise im Sommer
                1 => 0.90, 2 => 0.90, 3 => 0.95, 4 => 1.00,
                5 => 1.10, 6 => 1.20, 7 => 1.25, 8 => 1.20,
                9 => 1.10, 10 => 1.00, 11 => 0.95, 12 => 1.00
            ]
        ];

        return $seasonalFactors[$category][$month] ?? 1.0;
    }

    /**
     * Gibt Preistrend zurück (steigend/fallend/stabil)
     */
    public function getPriceTrend(int $productId, int $sellingPointId): string
    {
        // Vergleiche mit gestrigem Preis
        $product = $this->db->fetchOne(
            'SELECT spp.*, p.base_price FROM selling_point_products spp
             JOIN products p ON spp.product_id = p.id
             WHERE spp.selling_point_id = ? AND spp.product_id = ?',
            [$sellingPointId, $productId]
        );

        if (!$product) {
            return 'stable';
        }

        $todayPrice = $this->calculateDailyPrice(
            $product['base_price'],
            $product['price_modifier'] ?? 1.0,
            $sellingPointId,
            $productId
        );

        // Berechne gestrigen Preis
        $yesterdaySeed = (int) date('Ymd', strtotime('-1 day')) + ($sellingPointId * 1000) + ($productId * 10);
        mt_srand($yesterdaySeed);
        $variation = (mt_rand(0, 500) - 200) / 1000;
        $yesterdayPrice = $product['base_price'] * ($product['price_modifier'] ?? 1.0) * (1 + $variation);
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
     * Verkauft Produkte an eine Verkaufsstelle
     */
    public function sellProduct(int $farmId, int $sellingPointId, int $productId, int $quantity): array
    {
        // Prüfe Verkaufsstelle
        $sellingPoint = $this->getSellingPoint($sellingPointId);
        if (!$sellingPoint) {
            return ['success' => false, 'message' => 'Verkaufsstelle nicht gefunden'];
        }

        // Prüfe ob Produkt dort verkauft werden kann
        $productInfo = null;
        foreach ($sellingPoint['products'] as $p) {
            if ($p['product_id'] == $productId) {
                $productInfo = $p;
                break;
            }
        }

        if (!$productInfo) {
            return ['success' => false, 'message' => 'Dieses Produkt wird hier nicht gekauft'];
        }

        // Prüfe Lagerbestand
        $storage = new Storage();
        $available = $storage->getProductQuantity($farmId, $productId);

        if ($available < $quantity) {
            return ['success' => false, 'message' => 'Nicht genug im Lager vorhanden'];
        }

        // Berechne Preis
        $pricePerUnit = $productInfo['current_price'];
        $totalEarnings = $pricePerUnit * $quantity;

        // Entferne aus Lager
        $storage->removeProduct($farmId, $productId, $quantity);

        // Füge Geld hinzu
        $farm = new Farm($farmId);
        $farm->addMoney($totalEarnings, "Verkauf: {$quantity}x {$productInfo['name_de']} bei {$sellingPoint['name']}");

        // Vergebe Punkte
        $farm->addPoints(POINTS_TRADE, "Verkauf bei {$sellingPoint['name']}");

        // Speichere Transaktion
        $this->db->insert('sales_history', [
            'farm_id' => $farmId,
            'selling_point_id' => $sellingPointId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price_per_unit' => $pricePerUnit,
            'total_amount' => $totalEarnings
        ]);

        Logger::info('Product sold', [
            'farm_id' => $farmId,
            'selling_point' => $sellingPoint['name'],
            'product_id' => $productId,
            'quantity' => $quantity,
            'earnings' => $totalEarnings
        ]);

        return [
            'success' => true,
            'message' => "{$quantity}x {$productInfo['name_de']} für " . number_format($totalEarnings, 0, ',', '.') . " T verkauft!",
            'earnings' => $totalEarnings,
            'price_per_unit' => $pricePerUnit
        ];
    }

    /**
     * Gibt Verkaufshistorie zurück
     */
    public function getSalesHistory(int $farmId, int $limit = 50): array
    {
        $sql = "SELECT sh.*, COALESCE(sp.name_de, sp.name) as selling_point_name, p.name_de as product_name, p.icon
                FROM sales_history sh
                JOIN selling_points sp ON sh.selling_point_id = sp.id
                JOIN products p ON sh.product_id = p.id
                WHERE sh.farm_id = ?
                ORDER BY sh.created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$farmId, $limit]);
    }

    /**
     * Gibt beste Preise für ein Produkt zurück
     */
    public function getBestPricesForProduct(int $productId): array
    {
        $sql = "SELECT spp.*, COALESCE(sp.name_de, sp.name) as selling_point_name, sp.location,
                       p.base_price
                FROM selling_point_products spp
                JOIN selling_points sp ON spp.selling_point_id = sp.id
                JOIN products p ON spp.product_id = p.id
                WHERE spp.product_id = ? AND sp.is_active = 1";

        $points = $this->db->fetchAll($sql, [$productId]);

        // Berechne aktuelle Preise
        foreach ($points as &$point) {
            $point['current_price'] = $this->calculateDailyPrice(
                $point['base_price'],
                $point['price_modifier'] ?? 1.0,
                $point['selling_point_id'],
                $productId
            );
            $point['price_trend'] = $this->getPriceTrend($productId, $point['selling_point_id']);
        }

        // Sortiere nach Preis (höchster zuerst)
        usort($points, function ($a, $b) {
            return $b['current_price'] <=> $a['current_price'];
        });

        return $points;
    }

    /**
     * Gibt alle verkaufbaren Produkte mit Preisvergleich zurück
     */
    public function getAllSellableProducts(int $farmId): array
    {
        $storage = new Storage();
        $items = $storage->getStorageItems($farmId);

        $sellable = [];

        foreach ($items['products'] as $item) {
            $bestPrices = $this->getBestPricesForProduct($item['product_id']);
            if (!empty($bestPrices)) {
                $item['best_prices'] = array_slice($bestPrices, 0, 3); // Top 3 Preise
                $item['best_price'] = $bestPrices[0]['current_price'] ?? $item['base_price'];
                $item['best_selling_point'] = $bestPrices[0]['selling_point_name'] ?? 'Unbekannt';
                $sellable[] = $item;
            }
        }

        return $sellable;
    }

    /**
     * Erstellt tägliche Preisübersicht
     */
    public function getDailyPriceOverview(): array
    {
        $sellingPoints = $this->getAllSellingPoints();
        $overview = [];

        foreach ($sellingPoints as $point) {
            $products = $this->getSellingPointProducts($point['id']);

            // Finde Produkte mit besonders guten/schlechten Preisen
            $hotDeals = [];
            $lowPrices = [];

            foreach ($products as $product) {
                $diff = ($product['current_price'] - $product['base_price']) / $product['base_price'];

                if ($diff > 0.15) {
                    $hotDeals[] = $product;
                } elseif ($diff < -0.10) {
                    $lowPrices[] = $product;
                }
            }

            $overview[] = [
                'selling_point' => $point,
                'hot_deals' => array_slice($hotDeals, 0, 5),
                'low_prices' => array_slice($lowPrices, 0, 5),
                'total_products' => count($products)
            ];
        }

        return $overview;
    }
}
