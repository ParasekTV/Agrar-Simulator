<div class="inventory-page">
    <div class="page-header">
        <h1>Inventar</h1>
        <div class="storage-info">
            <span>Lagerkapazität: <?= number_format($storageUsed) ?> / <?= number_format($storageCapacity) ?></span>
            <div class="progress-bar" style="width: 200px;">
                <div class="progress-bar-fill <?= ($storageUsed / max(1, $storageCapacity)) > 0.9 ? 'bg-warning' : '' ?>"
                     style="width: <?= min(100, ($storageUsed / max(1, $storageCapacity)) * 100) ?>%"></div>
            </div>
        </div>
    </div>

    <?php if (empty($inventory)): ?>
        <div class="empty-state">
            <span class="empty-icon">&#128230;</span>
            <h3>Inventar ist leer</h3>
            <p>Ernte Feldfrüchte oder sammle Tierprodukte!</p>
        </div>
    <?php else: ?>
        <div class="inventory-categories">
            <?php
            $grouped = [];
            foreach ($inventory as $item) {
                $grouped[$item['item_type']][] = $item;
            }
            $typeNames = [
                'crop' => 'Feldfrüchte',
                'animal_product' => 'Tierprodukte',
                'material' => 'Materialien',
                'fuel' => 'Kraftstoff'
            ];
            ?>

            <?php foreach ($grouped as $type => $items): ?>
                <div class="inventory-category">
                    <h3><?= $typeNames[$type] ?? ucfirst($type) ?></h3>
                    <div class="inventory-items">
                        <?php foreach ($items as $item): ?>
                            <div class="inventory-item">
                                <div class="item-info">
                                    <span class="item-name"><?= htmlspecialchars($item['item_name']) ?></span>
                                    <span class="item-quantity"><?= number_format($item['quantity']) ?> Einheiten</span>
                                </div>
                                <div class="item-actions">
                                    <button class="btn btn-sm btn-outline"
                                            onclick="showSellModal('<?= $item['item_type'] ?>', <?= $item['item_id'] ?>, '<?= htmlspecialchars($item['item_name']) ?>', <?= $item['quantity'] ?>)">
                                        Verkaufen
                                    </button>
                                    <button class="btn btn-sm btn-primary"
                                            onclick="showMarketModal('<?= $item['item_type'] ?>', <?= $item['item_id'] ?>, '<?= htmlspecialchars($item['item_name']) ?>', <?= $item['quantity'] ?>)">
                                        Am Markt anbieten
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Direktverkauf -->
<div class="modal" id="sell-modal">
    <div class="modal-backdrop" onclick="closeSellModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Direkt verkaufen</h3>
            <button class="modal-close" onclick="closeSellModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/market/sell-direct" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="item_type" id="sell-item-type">
            <input type="hidden" name="item_id" id="sell-item-id">
            <div class="modal-body">
                <p>Artikel: <strong id="sell-item-name"></strong></p>
                <div class="form-group">
                    <label for="sell-quantity">Menge (max: <span id="sell-max"></span>)</label>
                    <input type="number" name="quantity" id="sell-quantity" class="form-input" min="1" required>
                </div>
                <p class="form-help text-warning">Hinweis: Direktverkauf erfolgt zum Basispreis. Am Marktplatz kannst du oft mehr erzielen!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeSellModal()">Abbrechen</button>
                <button type="submit" class="btn btn-success">Verkaufen</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Am Markt anbieten -->
<div class="modal" id="market-modal">
    <div class="modal-backdrop" onclick="closeMarketModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Am Marktplatz anbieten</h3>
            <button class="modal-close" onclick="closeMarketModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/market/create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="item_type" id="market-item-type">
            <input type="hidden" name="item_id" id="market-item-id">
            <div class="modal-body">
                <p>Artikel: <strong id="market-item-name"></strong></p>
                <div class="form-group">
                    <label for="market-quantity">Menge (max: <span id="market-max"></span>)</label>
                    <input type="number" name="quantity" id="market-quantity" class="form-input" min="1" required>
                </div>
                <div class="form-group">
                    <label for="market-price">Preis pro Stück (T)</label>
                    <input type="number" name="price" id="market-price" class="form-input" min="0.01" step="0.01" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeMarketModal()">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Anbieten</button>
            </div>
        </form>
    </div>
</div>

<script>
function showSellModal(type, id, name, max) {
    document.getElementById('sell-item-type').value = type;
    document.getElementById('sell-item-id').value = id;
    document.getElementById('sell-item-name').textContent = name;
    document.getElementById('sell-max').textContent = max;
    document.getElementById('sell-quantity').max = max;
    document.getElementById('sell-quantity').value = 1;
    document.getElementById('sell-modal').classList.add('active');
}
function closeSellModal() {
    document.getElementById('sell-modal').classList.remove('active');
}
function showMarketModal(type, id, name, max) {
    document.getElementById('market-item-type').value = type;
    document.getElementById('market-item-id').value = id;
    document.getElementById('market-item-name').textContent = name;
    document.getElementById('market-max').textContent = max;
    document.getElementById('market-quantity').max = max;
    document.getElementById('market-quantity').value = 1;
    document.getElementById('market-modal').classList.add('active');
}
function closeMarketModal() {
    document.getElementById('market-modal').classList.remove('active');
}
</script>

<style>
.inventory-category {
    background: white;
    border-radius: var(--radius-lg);
    margin-bottom: 1.5rem;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.inventory-category h3 {
    padding: 1rem 1.25rem;
    margin: 0;
    background: var(--color-gray-100);
    border-bottom: 1px solid var(--color-gray-200);
}
.inventory-items { padding: 0.5rem; }
.inventory-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--color-gray-100);
}
.inventory-item:last-child { border-bottom: none; }
.item-name { font-weight: 500; display: block; }
.item-quantity { font-size: 0.9rem; color: var(--color-gray-600); }
.item-actions { display: flex; gap: 0.5rem; }
.storage-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}
</style>
