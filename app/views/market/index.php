<div class="market-page">
    <div class="page-header">
        <h1>Marktplatz</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/market/history" class="btn btn-outline">Handelshistorie</a>
            <button class="btn btn-primary" onclick="showCreateListingModal()">Angebot erstellen</button>
        </div>
    </div>

    <!-- Filter -->
    <div class="market-filters">
        <form action="<?= BASE_URL ?>/market" method="GET" class="filter-form">
            <select name="type" class="form-select">
                <option value="">Alle Kategorien</option>
                <option value="crop" <?= ($filter['type'] ?? '') === 'crop' ? 'selected' : '' ?>>Feldfrüchte</option>
                <option value="animal_product" <?= ($filter['type'] ?? '') === 'animal_product' ? 'selected' : '' ?>>Tierprodukte</option>
                <option value="material" <?= ($filter['type'] ?? '') === 'material' ? 'selected' : '' ?>>Materialien</option>
            </select>
            <input type="text" name="search" class="form-input" placeholder="Suchen..."
                   value="<?= htmlspecialchars($filter['search'] ?? '') ?>">
            <button type="submit" class="btn btn-outline">Filtern</button>
        </form>
    </div>

    <div class="market-layout">
        <!-- Angebote -->
        <div class="market-listings">
            <h3>Aktuelle Angebote</h3>
            <?php if (empty($listings)): ?>
                <div class="empty-state">
                    <p>Keine Angebote gefunden.</p>
                </div>
            <?php else: ?>
                <div class="listings-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Artikel</th>
                                <th>Verkäufer</th>
                                <th>Menge</th>
                                <th>Preis/Stück</th>
                                <th>Gesamt</th>
                                <th>Aktion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listings as $listing): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($listing['item_name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $listing['item_type'])) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($listing['seller_name']) ?></td>
                                    <td><?= number_format($listing['quantity']) ?></td>
                                    <td><?= number_format($listing['price_per_unit'], 0, ',', '.') ?> T</td>
                                    <td><strong><?= number_format($listing['price_per_unit'] * $listing['quantity'], 0, ',', '.') ?> T</strong></td>
                                    <td>
                                        <button class="btn btn-success btn-sm"
                                                onclick="showBuyModal(<?= $listing['id'] ?>, '<?= htmlspecialchars($listing['item_name']) ?>', <?= $listing['quantity'] ?>, <?= $listing['price_per_unit'] ?>)">
                                            Kaufen
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['totalPages'] > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                            <a href="<?= BASE_URL ?>/market?page=<?= $i ?>&type=<?= urlencode($filter['type'] ?? '') ?>&search=<?= urlencode($filter['search'] ?? '') ?>"
                               class="pagination-link <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Meine Angebote -->
        <div class="my-listings">
            <h3>Meine Angebote</h3>
            <?php if (empty($myListings)): ?>
                <p class="text-muted">Du hast keine aktiven Angebote.</p>
            <?php else: ?>
                <?php foreach ($myListings as $listing): ?>
                    <div class="my-listing-item <?= $listing['status'] !== 'active' ? 'listing-inactive' : '' ?>">
                        <div class="listing-info">
                            <strong><?= htmlspecialchars($listing['item_name']) ?></strong>
                            <span><?= $listing['quantity'] ?>x @ <?= number_format($listing['price_per_unit'], 0, ',', '.') ?> T</span>
                            <small class="listing-status status-<?= $listing['status'] ?>">
                                <?= ucfirst($listing['status']) ?>
                            </small>
                        </div>
                        <?php if ($listing['status'] === 'active'): ?>
                            <form action="<?= BASE_URL ?>/market/cancel" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                                <button type="submit" class="btn btn-outline btn-sm">Stornieren</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Angebot erstellen -->
<div class="modal" id="create-listing-modal">
    <div class="modal-backdrop" onclick="closeCreateListingModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Angebot erstellen</h3>
            <button class="modal-close" onclick="closeCreateListingModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/market/create" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label>Artikel wählen</label>
                    <select name="item_type" id="listing-item-type" class="form-select" required onchange="updateItemList()">
                        <option value="">Kategorie wählen...</option>
                        <option value="crop">Feldfrüchte</option>
                        <option value="animal_product">Tierprodukte</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Artikel</label>
                    <select name="item_id" id="listing-item-id" class="form-select" required>
                        <option value="">Erst Kategorie wählen...</option>
                        <?php foreach ($inventory as $item): ?>
                            <option value="<?= $item['item_id'] ?>"
                                    data-type="<?= $item['item_type'] ?>"
                                    data-max="<?= $item['quantity'] ?>"
                                    style="display: none;">
                                <?= htmlspecialchars($item['item_name']) ?> (<?= $item['quantity'] ?> verfügbar)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="listing-quantity">Menge</label>
                    <input type="number" name="quantity" id="listing-quantity" class="form-input"
                           min="1" required>
                </div>
                <div class="form-group">
                    <label for="listing-price">Preis pro Stück (T)</label>
                    <input type="number" name="price" id="listing-price" class="form-input"
                           min="0.01" step="0.01" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeCreateListingModal()">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Angebot erstellen</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Kaufen -->
<div class="modal" id="buy-modal">
    <div class="modal-backdrop" onclick="closeBuyModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Artikel kaufen</h3>
            <button class="modal-close" onclick="closeBuyModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/market/buy" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="listing_id" id="buy-listing-id">
            <div class="modal-body">
                <p>Artikel: <strong id="buy-item-name"></strong></p>
                <p>Preis pro Stück: <span id="buy-price-per-unit"></span> T</p>
                <div class="form-group">
                    <label for="buy-quantity">Menge (max: <span id="buy-max-quantity"></span>)</label>
                    <input type="number" name="quantity" id="buy-quantity" class="form-input"
                           min="1" required onchange="updateTotal()">
                </div>
                <p class="total-price">Gesamtpreis: <strong id="buy-total">0</strong> T</p>
                <p class="form-help">Dein Guthaben: <?= number_format($farm['money'], 0, ',', '.') ?> T</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeBuyModal()">Abbrechen</button>
                <button type="submit" class="btn btn-success">Kaufen</button>
            </div>
        </form>
    </div>
</div>

<script>
function showCreateListingModal() {
    document.getElementById('create-listing-modal').classList.add('active');
}

function closeCreateListingModal() {
    document.getElementById('create-listing-modal').classList.remove('active');
}

function updateItemList() {
    const type = document.getElementById('listing-item-type').value;
    const options = document.querySelectorAll('#listing-item-id option[data-type]');

    options.forEach(opt => {
        opt.style.display = opt.dataset.type === type ? 'block' : 'none';
    });

    document.getElementById('listing-item-id').value = '';
}

function showBuyModal(listingId, itemName, maxQuantity, pricePerUnit) {
    document.getElementById('buy-listing-id').value = listingId;
    document.getElementById('buy-item-name').textContent = itemName;
    document.getElementById('buy-max-quantity').textContent = maxQuantity;
    document.getElementById('buy-price-per-unit').textContent = Math.round(pricePerUnit);
    document.getElementById('buy-quantity').max = maxQuantity;
    document.getElementById('buy-quantity').value = 1;
    window.buyPricePerUnit = pricePerUnit;
    updateTotal();
    document.getElementById('buy-modal').classList.add('active');
}

function closeBuyModal() {
    document.getElementById('buy-modal').classList.remove('active');
}

function updateTotal() {
    const quantity = parseInt(document.getElementById('buy-quantity').value) || 0;
    const total = quantity * window.buyPricePerUnit;
    document.getElementById('buy-total').textContent = Math.round(total);
}
</script>
