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
                                <?php
                                $isPushed = !empty($listing['is_pushed']) && strtotime($listing['pushed_until'] ?? '') > time();
                                $highlightColor = $isPushed ? ($listing['highlight_color'] ?? '#ffd700') : '';
                                ?>
                                <tr class="<?= $isPushed ? 'listing-row-pushed' : '' ?>"
                                    <?php if ($isPushed): ?>style="background: linear-gradient(90deg, <?= htmlspecialchars($highlightColor) ?>22 0%, transparent 100%);"<?php endif; ?>>
                                    <td>
                                        <div class="market-item-cell">
                                            <?php if ($isPushed): ?>
                                                <span class="pushed-icon" title="Gepushtes Angebot">&#11088;</span>
                                            <?php endif; ?>
                                            <?php if (!empty($listing['product_icon'])): ?>
                                                <img src="<?= BASE_URL ?>/img/products/<?= htmlspecialchars($listing['product_icon']) ?>"
                                                     class="product-icon" alt="" onerror="this.style.display='none'">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= htmlspecialchars($listing['item_name']) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $listing['item_type'])) ?></small>
                                            </div>
                                        </div>
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
                    <?php
                    $isPushed = !empty($listing['is_pushed']) && strtotime($listing['pushed_until'] ?? '') > time();
                    $pushRemainingHours = $isPushed ? round((strtotime($listing['pushed_until']) - time()) / 3600) : 0;
                    ?>
                    <div class="my-listing-item <?= $listing['status'] !== 'active' ? 'listing-inactive' : '' ?> <?= $isPushed ? 'listing-pushed' : '' ?>">
                        <div class="listing-info">
                            <strong><?= htmlspecialchars($listing['item_name']) ?></strong>
                            <span><?= $listing['quantity'] ?>x @ <?= number_format($listing['price_per_unit'], 0, ',', '.') ?> T</span>
                            <small class="listing-status status-<?= $listing['status'] ?>">
                                <?= ucfirst($listing['status']) ?>
                            </small>
                            <?php if ($isPushed): ?>
                                <small class="push-badge">Gepusht (<?= $pushRemainingHours ?>h)</small>
                            <?php endif; ?>
                        </div>
                        <?php if ($listing['status'] === 'active'): ?>
                            <div class="listing-actions">
                                <?php if (!$isPushed && !empty($pushOptions)): ?>
                                    <button type="button" class="btn btn-warning btn-sm"
                                            onclick="showPushModal(<?= $listing['id'] ?>, '<?= htmlspecialchars(addslashes($listing['item_name'])) ?>')">
                                        Nach oben pushen
                                    </button>
                                <?php endif; ?>
                                <form action="<?= BASE_URL ?>/market/cancel" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">Stornieren</button>
                                </form>
                            </div>
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

<!-- Modal: Angebot pushen -->
<div class="modal" id="push-modal">
    <div class="modal-backdrop" onclick="closePushModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Angebot nach oben pushen</h3>
            <button class="modal-close" onclick="closePushModal()">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/market/push" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="listing_id" id="push-listing-id">
            <div class="modal-body">
                <p>Artikel: <strong id="push-item-name"></strong></p>
                <p class="push-info">Gepushte Angebote erscheinen ganz oben in der Liste und werden hervorgehoben.</p>

                <div class="push-options">
                    <?php if (!empty($pushOptions)): ?>
                        <?php foreach ($pushOptions as $option): ?>
                            <label class="push-option">
                                <input type="radio" name="push_config_id" value="<?= $option['id'] ?>" required>
                                <div class="push-option-content" style="border-left: 4px solid <?= htmlspecialchars($option['highlight_color'] ?? '#ffd700') ?>">
                                    <strong><?= htmlspecialchars($option['name']) ?></strong>
                                    <span class="push-duration"><?= $option['duration_hours'] ?> Stunden</span>
                                    <span class="push-cost"><?= number_format($option['cost'], 0, ',', '.') ?> T</span>
                                    <?php if (!empty($option['description'])): ?>
                                        <small><?= htmlspecialchars($option['description']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Keine Push-Optionen verfuegbar.</p>
                    <?php endif; ?>
                </div>

                <p class="form-help">Dein Guthaben: <?= number_format($farm['money'], 0, ',', '.') ?> T</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closePushModal()">Abbrechen</button>
                <button type="submit" class="btn btn-warning">Angebot pushen</button>
            </div>
        </form>
    </div>
</div>

<script>
function showPushModal(listingId, itemName) {
    document.getElementById('push-listing-id').value = listingId;
    document.getElementById('push-item-name').textContent = itemName;
    document.getElementById('push-modal').classList.add('active');
}

function closePushModal() {
    document.getElementById('push-modal').classList.remove('active');
}

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

<style>
.market-item-cell { display: flex; align-items: center; gap: 0.5rem; }
.product-icon { width: 32px; height: 32px; object-fit: contain; flex-shrink: 0; }

/* Push-Stile */
.pushed-icon {
    font-size: 1.2rem;
    margin-right: 0.25rem;
}

.listing-row-pushed {
    border-left: 3px solid #ffd700;
}

.listing-pushed {
    background: linear-gradient(90deg, #ffd70022 0%, transparent 100%);
    border-left: 3px solid #ffd700;
}

.push-badge {
    display: inline-block;
    background: #ffd700;
    color: #000;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

.listing-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.push-info {
    background: #fff3cd;
    color: #856404;
    padding: 0.75rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.push-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.push-option {
    cursor: pointer;
}

.push-option input[type="radio"] {
    display: none;
}

.push-option-content {
    padding: 1rem;
    background: var(--color-bg-secondary, #f5f5f5);
    border-radius: 8px;
    transition: all 0.2s;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0.25rem;
}

.push-option-content:hover {
    background: var(--color-bg-hover, #eee);
}

.push-option input[type="radio"]:checked + .push-option-content {
    background: var(--color-primary-light, #e3f2fd);
    outline: 2px solid var(--color-primary, #007bff);
}

.push-option-content strong {
    font-size: 1rem;
}

.push-duration {
    color: var(--color-text-secondary, #666);
    font-size: 0.85rem;
}

.push-cost {
    font-weight: 700;
    color: var(--color-warning, #f5a623);
    font-size: 1.1rem;
}

.push-option-content small {
    grid-column: 1 / -1;
    color: var(--color-text-secondary, #666);
    font-size: 0.8rem;
}

.btn-warning {
    background: #f5a623;
    color: #000;
    border: none;
}

.btn-warning:hover {
    background: #e09600;
}

.my-listing-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: var(--color-bg-secondary, #f5f5f5);
    border-radius: 8px;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}
</style>
