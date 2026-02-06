<?php
/**
 * Storage Model
 *
 * Verwaltet das Lager/Inventar für Produkte.
 */
class Storage
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Gibt alle Produkte im Lager einer Farm zurück
     */
    public function getStorageItems(int $farmId): array
    {
        // Hole klassische Inventory-Items (Crops, Animal Products)
        $sql = "SELECT i.*, 'inventory' as source_type
                FROM inventory i
                WHERE i.farm_id = ? AND i.quantity > 0
                ORDER BY i.item_type, i.item_name";

        $inventoryItems = $this->db->fetchAll($sql, [$farmId]);

        // Hole Produkte aus dem neuen Produktsystem
        $sql = "SELECT fs.*, p.name, p.name_de, p.category, p.icon, p.base_price,
                       'product' as source_type
                FROM farm_storage fs
                JOIN products p ON fs.product_id = p.id
                WHERE fs.farm_id = ? AND fs.quantity > 0
                ORDER BY p.category, p.name_de";

        $productItems = $this->db->fetchAll($sql, [$farmId]);

        // Kombiniere beide Listen
        return [
            'inventory' => $inventoryItems,
            'products' => $productItems,
            'all' => array_merge($inventoryItems, $productItems)
        ];
    }

    /**
     * Gibt gruppierte Lagerbestände zurück
     */
    public function getGroupedStorage(int $farmId): array
    {
        $items = $this->getStorageItems($farmId);
        $grouped = [];

        // Gruppiere Inventory-Items
        foreach ($items['inventory'] as $item) {
            $category = $this->mapItemTypeToCategory($item['item_type']);
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = [
                'id' => $item['id'],
                'name' => $item['item_name'],
                'quantity' => $item['quantity'],
                'type' => 'inventory',
                'item_id' => $item['item_id'],
                'item_type' => $item['item_type'],
                'icon' => $this->getItemIcon($item['item_type'], $item['item_name'])
            ];
        }

        // Gruppiere Produkte
        foreach ($items['products'] as $item) {
            $category = $item['category'] ?? 'allgemein';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = [
                'id' => $item['id'],
                'product_id' => $item['product_id'],
                'name' => $item['name_de'],
                'quantity' => $item['quantity'],
                'type' => 'product',
                'icon' => $item['icon'],
                'base_price' => $item['base_price']
            ];
        }

        return $grouped;
    }

    /**
     * Mappt Item-Type zu Kategorie
     */
    private function mapItemTypeToCategory(string $itemType): string
    {
        $mapping = [
            'crop' => 'feldfrucht',
            'animal_product' => 'tierprodukt',
            'product' => 'allgemein'
        ];

        return $mapping[$itemType] ?? 'allgemein';
    }

    /**
     * Gibt Icon für Item zurück
     */
    private function getItemIcon(string $itemType, string $itemName): string
    {
        $name = strtolower(str_replace(' ', '_', $itemName));
        return $name . '.png';
    }

    /**
     * Fügt ein Produkt zum Lager hinzu
     */
    public function addProduct(int $farmId, int $productId, int $quantity): bool
    {
        // Prüfe ob Produkt existiert
        $product = $this->db->fetchOne('SELECT id FROM products WHERE id = ?', [$productId]);
        if (!$product) {
            return false;
        }

        // Prüfe ob bereits im Lager
        $existing = $this->db->fetchOne(
            'SELECT * FROM farm_storage WHERE farm_id = ? AND product_id = ?',
            [$farmId, $productId]
        );

        if ($existing) {
            $this->db->update('farm_storage', [
                'quantity' => $existing['quantity'] + $quantity,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = :id', ['id' => $existing['id']]);
        } else {
            $this->db->insert('farm_storage', [
                'farm_id' => $farmId,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        Logger::info('Product added to storage', [
            'farm_id' => $farmId,
            'product_id' => $productId,
            'quantity' => $quantity
        ]);

        return true;
    }

    /**
     * Entfernt ein Produkt aus dem Lager
     * Entfernt zuerst aus farm_storage, dann aus inventory
     */
    public function removeProduct(int $farmId, int $productId, int $quantity): bool
    {
        $remaining = $quantity;

        // Zuerst aus farm_storage entfernen
        $storageItem = $this->db->fetchOne(
            'SELECT * FROM farm_storage WHERE farm_id = ? AND product_id = ?',
            [$farmId, $productId]
        );

        if ($storageItem && $storageItem['quantity'] > 0) {
            $toRemove = min($remaining, $storageItem['quantity']);
            $newQuantity = $storageItem['quantity'] - $toRemove;

            if ($newQuantity <= 0) {
                $this->db->delete('farm_storage', 'id = :id', ['id' => $storageItem['id']]);
            } else {
                $this->db->update('farm_storage', [
                    'quantity' => $newQuantity,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = :id', ['id' => $storageItem['id']]);
            }

            $remaining -= $toRemove;
        }

        // Wenn noch Menge übrig, aus inventory entfernen
        if ($remaining > 0) {
            $product = $this->db->fetchOne(
                'SELECT name, name_de FROM products WHERE id = ?',
                [$productId]
            );

            if ($product) {
                $inventoryItem = $this->db->fetchOne(
                    'SELECT * FROM inventory WHERE farm_id = ? AND (item_name = ? OR item_name = ?)',
                    [$farmId, $product['name'], $product['name_de']]
                );

                if ($inventoryItem && $inventoryItem['quantity'] >= $remaining) {
                    $newInventoryQty = $inventoryItem['quantity'] - $remaining;

                    if ($newInventoryQty <= 0) {
                        $this->db->delete('inventory', 'id = :id', ['id' => $inventoryItem['id']]);
                    } else {
                        $this->db->update('inventory', [
                            'quantity' => $newInventoryQty
                        ], 'id = :id', ['id' => $inventoryItem['id']]);
                    }

                    $remaining = 0;
                }
            }
        }

        // Prüfe ob alles entfernt wurde
        if ($remaining > 0) {
            return false;
        }

        Logger::info('Product removed from storage', [
            'farm_id' => $farmId,
            'product_id' => $productId,
            'quantity' => $quantity
        ]);

        return true;
    }

    /**
     * Gibt die Menge eines Produkts im Lager zurück
     * Prüft sowohl farm_storage als auch inventory Tabelle
     */
    public function getProductQuantity(int $farmId, int $productId): int
    {
        $total = 0;

        // Prüfe farm_storage (neue Produkte)
        $item = $this->db->fetchOne(
            'SELECT quantity FROM farm_storage WHERE farm_id = ? AND product_id = ?',
            [$farmId, $productId]
        );
        if ($item) {
            $total += (int) $item['quantity'];
        }

        // Prüfe inventory (klassische Ernte-Items) - matche nach Produktname
        $product = $this->db->fetchOne(
            'SELECT name, name_de FROM products WHERE id = ?',
            [$productId]
        );

        if ($product) {
            // Suche nach übereinstimmendem Item in inventory
            $inventoryItem = $this->db->fetchOne(
                'SELECT quantity FROM inventory WHERE farm_id = ? AND (item_name = ? OR item_name = ?)',
                [$farmId, $product['name'], $product['name_de']]
            );
            if ($inventoryItem) {
                $total += (int) $inventoryItem['quantity'];
            }
        }

        return $total;
    }

    /**
     * Transferiert Inventory-Items zu Produkten
     */
    public function convertInventoryToProduct(int $farmId, string $itemType, int $itemId, int $quantity): array
    {
        // Finde das passende Produkt
        $inventoryItem = $this->db->fetchOne(
            'SELECT * FROM inventory WHERE farm_id = ? AND item_type = ? AND item_id = ?',
            [$farmId, $itemType, $itemId]
        );

        if (!$inventoryItem || $inventoryItem['quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Nicht genug im Inventar'];
        }

        // Finde passendes Produkt nach Namen
        $product = $this->db->fetchOne(
            'SELECT id FROM products WHERE name = ? OR name_de = ?',
            [$inventoryItem['item_name'], $inventoryItem['item_name']]
        );

        if (!$product) {
            return ['success' => false, 'message' => 'Kein passendes Produkt gefunden'];
        }

        // Reduziere Inventory
        $newQty = $inventoryItem['quantity'] - $quantity;
        if ($newQty <= 0) {
            $this->db->delete('inventory', 'id = :id', ['id' => $inventoryItem['id']]);
        } else {
            $this->db->update('inventory', ['quantity' => $newQty], 'id = :id', ['id' => $inventoryItem['id']]);
        }

        // Füge zu Produkten hinzu
        $this->addProduct($farmId, $product['id'], $quantity);

        return [
            'success' => true,
            'message' => "{$quantity}x {$inventoryItem['item_name']} ins Produktlager übertragen"
        ];
    }

    /**
     * Gibt Lager-Statistiken zurück
     */
    public function getStorageStats(int $farmId): array
    {
        $items = $this->getStorageItems($farmId);

        $totalItems = 0;
        $totalValue = 0;

        foreach ($items['inventory'] as $item) {
            $totalItems += $item['quantity'];
        }

        foreach ($items['products'] as $item) {
            $totalItems += $item['quantity'];
            $totalValue += $item['quantity'] * ($item['base_price'] ?? 0);
        }

        return [
            'total_items' => $totalItems,
            'total_value' => $totalValue,
            'inventory_count' => count($items['inventory']),
            'product_count' => count($items['products'])
        ];
    }

    /**
     * Gibt alle Produkte für Dropdown zurück
     */
    public function getAllProducts(): array
    {
        return $this->db->fetchAll(
            'SELECT id, name, name_de, category, icon, base_price FROM products ORDER BY category, name_de'
        );
    }

    /**
     * Sucht Produkte nach Name
     */
    public function searchProducts(string $search, int $farmId): array
    {
        $searchTerm = "%{$search}%";

        $sql = "SELECT fs.*, p.name, p.name_de, p.category, p.icon, p.base_price
                FROM farm_storage fs
                JOIN products p ON fs.product_id = p.id
                WHERE fs.farm_id = ? AND fs.quantity > 0
                  AND (p.name LIKE ? OR p.name_de LIKE ?)
                ORDER BY p.name_de
                LIMIT 20";

        return $this->db->fetchAll($sql, [$farmId, $searchTerm, $searchTerm]);
    }

    /**
     * Gibt Kategorien mit deutschen Namen zurück
     */
    public function getCategories(): array
    {
        return [
            'feldfrucht' => 'Feldfrüchte',
            'tierprodukt' => 'Tierprodukte',
            'getraenk' => 'Getränke',
            'mehl' => 'Mehl & Getreideprodukte',
            'allgemein' => 'Allgemein',
            'rohstoff' => 'Rohstoffe',
            'verarbeitet' => 'Verarbeitete Produkte'
        ];
    }
}
