<?php
/**
 * Market Model
 *
 * Verwaltet den Marktplatz und Handelsaktionen.
 */
class Market
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Erstellt ein Marktangebot
     */
    public function createListing(
        int $farmId,
        string $itemType,
        int $itemId,
        string $itemName,
        int $quantity,
        float $pricePerUnit
    ): array {
        // Prüfe Inventar
        $inventory = $this->db->fetchOne(
            'SELECT * FROM inventory WHERE farm_id = ? AND item_type = ? AND item_id = ?',
            [$farmId, $itemType, $itemId]
        );

        if (!$inventory || $inventory['quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Nicht genügend im Inventar'];
        }

        // Ziehe vom Inventar ab
        $this->db->update('inventory', [
            'quantity' => $inventory['quantity'] - $quantity
        ], 'id = :id', ['id' => $inventory['id']]);

        // Erstelle Angebot (7 Tage gültig)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        $listingId = $this->db->insert('market_listings', [
            'seller_farm_id' => $farmId,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'item_name' => $itemName,
            'quantity' => $quantity,
            'price_per_unit' => $pricePerUnit,
            'expires_at' => $expiresAt
        ]);

        Logger::info('Market listing created', [
            'farm_id' => $farmId,
            'item' => $itemName,
            'quantity' => $quantity
        ]);

        return [
            'success' => true,
            'message' => "Angebot erstellt: {$quantity}x {$itemName}",
            'listing_id' => $listingId
        ];
    }

    /**
     * Kauft von einem Marktangebot
     */
    public function buy(int $listingId, int $buyerFarmId, int $quantity): array
    {
        // Hole Angebot
        $listing = $this->db->fetchOne(
            'SELECT * FROM market_listings WHERE id = ? AND status = ?',
            [$listingId, 'active']
        );

        if (!$listing) {
            return ['success' => false, 'message' => 'Angebot nicht gefunden oder nicht mehr verfügbar'];
        }

        // Prüfe ob Käufer nicht Verkäufer ist
        if ($listing['seller_farm_id'] === $buyerFarmId) {
            return ['success' => false, 'message' => 'Du kannst nicht von dir selbst kaufen'];
        }

        // Prüfe Menge
        if ($listing['quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Nicht genügend verfügbar'];
        }

        // Berechne Gesamtpreis
        $totalPrice = $listing['price_per_unit'] * $quantity;

        // Prüfe und ziehe Geld ab
        $buyerFarm = new Farm($buyerFarmId);
        if (!$buyerFarm->subtractMoney($totalPrice, "Marktkauf: {$listing['item_name']}")) {
            return ['success' => false, 'message' => 'Nicht genügend Geld'];
        }

        // Füge Geld zum Verkäufer hinzu
        $sellerFarm = new Farm($listing['seller_farm_id']);
        $sellerFarm->addMoney($totalPrice, "Marktverkauf: {$listing['item_name']}");

        // Füge Items zum Käufer-Inventar hinzu
        $this->addToInventory(
            $buyerFarmId,
            $listing['item_type'],
            $listing['item_id'],
            $listing['item_name'],
            $quantity
        );

        // Aktualisiere Angebot
        $remainingQuantity = $listing['quantity'] - $quantity;

        if ($remainingQuantity <= 0) {
            $this->db->update('market_listings', ['status' => 'sold', 'quantity' => 0], 'id = :id', ['id' => $listingId]);
        } else {
            $this->db->update('market_listings', ['quantity' => $remainingQuantity], 'id = :id', ['id' => $listingId]);
        }

        // Erstelle Transaktion
        $this->db->insert('market_transactions', [
            'listing_id' => $listingId,
            'buyer_farm_id' => $buyerFarmId,
            'seller_farm_id' => $listing['seller_farm_id'],
            'quantity' => $quantity,
            'total_price' => $totalPrice
        ]);

        // Aktualisiere Verkaufswert in Rankings
        $this->db->query(
            'UPDATE rankings SET total_sales_value = total_sales_value + ? WHERE farm_id = ?',
            [$totalPrice, $listing['seller_farm_id']]
        );

        // Punkte für Verkäufer
        $salesPoints = (int) floor($totalPrice / 100) * POINTS_SALE_PER_100;
        if ($salesPoints > 0) {
            $sellerFarm->addPoints($salesPoints, 'Marktverkauf');
        }

        Logger::info('Market purchase', [
            'buyer_farm_id' => $buyerFarmId,
            'seller_farm_id' => $listing['seller_farm_id'],
            'item' => $listing['item_name'],
            'quantity' => $quantity,
            'price' => $totalPrice
        ]);

        return [
            'success' => true,
            'message' => "{$quantity}x {$listing['item_name']} für {$totalPrice} T gekauft!",
            'total_price' => $totalPrice
        ];
    }

    /**
     * Storniert ein Angebot
     */
    public function cancel(int $listingId, int $farmId): array
    {
        $listing = $this->db->fetchOne(
            'SELECT * FROM market_listings WHERE id = ? AND seller_farm_id = ? AND status = ?',
            [$listingId, $farmId, 'active']
        );

        if (!$listing) {
            return ['success' => false, 'message' => 'Angebot nicht gefunden'];
        }

        // Füge Items zurück zum Inventar
        $this->addToInventory(
            $farmId,
            $listing['item_type'],
            $listing['item_id'],
            $listing['item_name'],
            $listing['quantity']
        );

        // Aktualisiere Angebot
        $this->db->update('market_listings', ['status' => 'cancelled'], 'id = :id', ['id' => $listingId]);

        Logger::info('Market listing cancelled', [
            'farm_id' => $farmId,
            'listing_id' => $listingId
        ]);

        return [
            'success' => true,
            'message' => 'Angebot storniert. Waren zurück im Inventar.'
        ];
    }

    /**
     * Fügt Items zum Inventar hinzu
     */
    private function addToInventory(int $farmId, string $itemType, int $itemId, string $itemName, int $quantity): void
    {
        $existing = $this->db->fetchOne(
            'SELECT * FROM inventory WHERE farm_id = ? AND item_type = ? AND item_id = ?',
            [$farmId, $itemType, $itemId]
        );

        if ($existing) {
            $this->db->update('inventory', [
                'quantity' => $existing['quantity'] + $quantity
            ], 'id = :id', ['id' => $existing['id']]);
        } else {
            $this->db->insert('inventory', [
                'farm_id' => $farmId,
                'item_type' => $itemType,
                'item_id' => $itemId,
                'item_name' => $itemName,
                'quantity' => $quantity
            ]);
        }
    }

    /**
     * Gibt aktive Marktangebote zurück
     */
    public function getListings(
        ?string $itemType = null,
        ?string $search = null,
        int $page = 1,
        int $perPage = 20
    ): array {
        $conditions = ['status = ?'];
        $params = ['active'];

        if ($itemType) {
            $conditions[] = 'item_type = ?';
            $params[] = $itemType;
        }

        if ($search) {
            $conditions[] = 'item_name LIKE ?';
            $params[] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $conditions);
        $offset = ($page - 1) * $perPage;

        // Hole Angebote
        $sql = "SELECT ml.*, f.farm_name as seller_name
                FROM market_listings ml
                JOIN farms f ON ml.seller_farm_id = f.id
                WHERE {$whereClause}
                ORDER BY ml.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $listings = $this->db->fetchAll($sql, $params);

        // Hole Gesamtanzahl
        $totalSql = "SELECT COUNT(*) FROM market_listings WHERE {$whereClause}";
        $total = (int) $this->db->fetchColumn($totalSql, $params);

        return [
            'listings' => $listings,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Gibt eigene Angebote zurück
     */
    public function getMyListings(int $farmId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM market_listings
             WHERE seller_farm_id = ?
             ORDER BY status, created_at DESC",
            [$farmId]
        );
    }

    /**
     * Gibt Kaufhistorie zurück
     */
    public function getPurchaseHistory(int $farmId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT mt.*, ml.item_name, ml.item_type, f.farm_name as seller_name
             FROM market_transactions mt
             JOIN market_listings ml ON mt.listing_id = ml.id
             JOIN farms f ON mt.seller_farm_id = f.id
             WHERE mt.buyer_farm_id = ?
             ORDER BY mt.transaction_date DESC
             LIMIT ?",
            [$farmId, $limit]
        );
    }

    /**
     * Gibt Verkaufshistorie zurück
     */
    public function getSalesHistory(int $farmId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT mt.*, ml.item_name, ml.item_type, f.farm_name as buyer_name
             FROM market_transactions mt
             JOIN market_listings ml ON mt.listing_id = ml.id
             JOIN farms f ON mt.buyer_farm_id = f.id
             WHERE mt.seller_farm_id = ?
             ORDER BY mt.transaction_date DESC
             LIMIT ?",
            [$farmId, $limit]
        );
    }

    /**
     * Verkauft direkt an NPC (Basis-Preis)
     */
    public function sellToNpc(int $farmId, string $itemType, int $itemId, int $quantity): array
    {
        // Hole Inventar
        $inventory = $this->db->fetchOne(
            'SELECT * FROM inventory WHERE farm_id = ? AND item_type = ? AND item_id = ?',
            [$farmId, $itemType, $itemId]
        );

        if (!$inventory || $inventory['quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Nicht genügend im Inventar'];
        }

        // Hole Basispreis
        $price = 0;
        if ($itemType === 'crop') {
            $crop = $this->db->fetchOne('SELECT sell_price FROM crops WHERE id = ?', [$itemId]);
            $price = $crop ? $crop['sell_price'] : 0;
        } elseif ($itemType === 'animal_product') {
            $product = $this->db->fetchOne('SELECT base_sell_price FROM animal_products WHERE id = ?', [$itemId]);
            $price = $product ? $product['base_sell_price'] : 0;
        }

        if ($price <= 0) {
            return ['success' => false, 'message' => 'Kann nicht verkauft werden'];
        }

        $totalPrice = $price * $quantity;

        // Aktualisiere Inventar
        $this->db->update('inventory', [
            'quantity' => $inventory['quantity'] - $quantity
        ], 'id = :id', ['id' => $inventory['id']]);

        // Füge Geld hinzu
        $farm = new Farm($farmId);
        $farm->addMoney($totalPrice, "Direktverkauf: {$inventory['item_name']}");

        // Punkte
        $salesPoints = (int) floor($totalPrice / 100) * POINTS_SALE_PER_100;
        if ($salesPoints > 0) {
            $farm->addPoints($salesPoints, 'Direktverkauf');
        }

        Logger::info('Direct NPC sale', [
            'farm_id' => $farmId,
            'item' => $inventory['item_name'],
            'quantity' => $quantity,
            'price' => $totalPrice
        ]);

        return [
            'success' => true,
            'message' => "{$quantity}x {$inventory['item_name']} für {$totalPrice} T verkauft!",
            'total_price' => $totalPrice
        ];
    }

    /**
     * Bereinigt abgelaufene Angebote (für Cron)
     */
    public static function cleanupExpired(): int
    {
        $db = Database::getInstance();

        // Finde abgelaufene Angebote
        $expired = $db->fetchAll(
            "SELECT * FROM market_listings
             WHERE status = 'active' AND expires_at <= NOW()"
        );

        $count = 0;
        foreach ($expired as $listing) {
            // Füge Items zurück zum Inventar
            $existing = $db->fetchOne(
                'SELECT * FROM inventory WHERE farm_id = ? AND item_type = ? AND item_id = ?',
                [$listing['seller_farm_id'], $listing['item_type'], $listing['item_id']]
            );

            if ($existing) {
                $db->update('inventory', [
                    'quantity' => $existing['quantity'] + $listing['quantity']
                ], 'id = :id', ['id' => $existing['id']]);
            } else {
                $db->insert('inventory', [
                    'farm_id' => $listing['seller_farm_id'],
                    'item_type' => $listing['item_type'],
                    'item_id' => $listing['item_id'],
                    'item_name' => $listing['item_name'],
                    'quantity' => $listing['quantity']
                ]);
            }

            // Aktualisiere Status
            $db->update('market_listings', ['status' => 'expired'], 'id = :id', ['id' => $listing['id']]);

            $count++;
        }

        if ($count > 0) {
            Logger::info('Expired listings cleaned up', ['count' => $count]);
        }

        return $count;
    }
}
