<div class="dealer-detail-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/shop" class="back-link">&larr; Zurück zu Händlern</a>
        <h1><?= htmlspecialchars($dealer['name']) ?></h1>
        <?php if (!empty($dealer['location'])): ?>
            <p class="location"><?= htmlspecialchars($dealer['location']) ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($dealer['description'])): ?>
        <p class="dealer-description"><?= htmlspecialchars($dealer['description']) ?></p>
    <?php endif; ?>

    <!-- Balance und Preisinfo -->
    <div class="info-bar">
        <div class="balance-info">
            <span class="info-label">Verfügbar:</span>
            <span class="info-value"><?= number_format($farm['money'], 0, ',', '.') ?> T</span>
        </div>
        <div class="date-info">
            <span class="info-label">Preise gültig am:</span>
            <span class="info-value"><?= date('d.m.Y') ?></span>
        </div>
        <div class="modifier-info">
            <span class="info-label">Preisaufschlag:</span>
            <span class="info-value modifier-badge">
                +<?= number_format(($dealer['price_modifier'] - 1) * 100, 0) ?>%
            </span>
        </div>
    </div>

    <?php if (empty($dealer['products'])): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128722;</div>
            <h3>Keine Produkte verfügbar</h3>
            <p>Dieser Händler verkauft aktuell keine Produkte.</p>
        </div>
    <?php else: ?>
        <!-- Produkte gruppiert nach Kategorie -->
        <?php
        $grouped = [];
        foreach ($dealer['products'] as $product) {
            $cat = $product['category'] ?? 'allgemein';
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }
            $grouped[$cat][] = $product;
        }
        ?>

        <?php foreach ($grouped as $category => $products): ?>
            <div class="product-category">
                <h2 class="category-title"><?= htmlspecialchars(ucfirst($category)) ?></h2>

                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <?php
                        $canBuy = $farm['money'] >= $product['current_price'];
                        $baseModified = $product['base_price'] * $dealer['price_modifier'];
                        $priceDiff = (($product['current_price'] - $baseModified) / $baseModified) * 100;
                        $trendIcon = $product['price_trend'] === 'rising' ? '&#8593;' :
                                    ($product['price_trend'] === 'falling' ? '&#8595;' : '&#8596;');
                        $trendClass = $product['price_trend'] === 'rising' ? 'trend-up' :
                                     ($product['price_trend'] === 'falling' ? 'trend-down' : 'trend-stable');
                        $maxAffordable = $canBuy ? floor($farm['money'] / $product['current_price']) : 0;
                        $maxBuyable = min($maxAffordable, $product['max_quantity']);
                        ?>

                        <div class="product-card <?= $canBuy ? '' : 'cannot-afford' ?>">
                            <div class="product-header">
                                <div class="product-icon">
                                    <?php if ($product['icon']): ?>
                                        <img src="<?= BASE_URL ?>/img/products/<?= htmlspecialchars($product['icon']) ?>"
                                             alt="" onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                                    <?php else: ?>
                                        <span class="icon-placeholder">&#128230;</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <h3><?= htmlspecialchars($product['name_de']) ?></h3>
                                    <span class="quantity-limits">
                                        Min: <?= $product['min_quantity'] ?> / Max: <?= $product['max_quantity'] ?>
                                    </span>
                                </div>
                            </div>

                            <div class="product-pricing">
                                <div class="current-price">
                                    <span class="price-label">Aktueller Preis:</span>
                                    <span class="price-value"><?= number_format($product['current_price'], 0, ',', '.') ?> T</span>
                                </div>
                                <div class="price-comparison">
                                    <span class="base-price">Basis: <?= number_format($product['base_price'], 0, ',', '.') ?> T</span>
                                    <span class="price-diff <?= $priceDiff >= 0 ? 'positive' : 'negative' ?>">
                                        <?= $priceDiff >= 0 ? '+' : '' ?><?= number_format($priceDiff, 1) ?>%
                                    </span>
                                    <span class="trend <?= $trendClass ?>"><?= $trendIcon ?></span>
                                </div>
                            </div>

                            <?php if ($canBuy): ?>
                                <form action="<?= BASE_URL ?>/shop/buy" method="POST" class="buy-form">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="dealer_id" value="<?= $dealer['id'] ?>">
                                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

                                    <div class="quantity-selector">
                                        <label>Menge:</label>
                                        <div class="quantity-input-group">
                                            <button type="button" class="qty-btn" onclick="changeQty(this, -1)">-</button>
                                            <input type="number" name="quantity" value="<?= $product['min_quantity'] ?>"
                                                   min="<?= $product['min_quantity'] ?>" max="<?= $maxBuyable ?>"
                                                   class="qty-input" data-max="<?= $maxBuyable ?>"
                                                   data-min="<?= $product['min_quantity'] ?>"
                                                   data-price="<?= $product['current_price'] ?>"
                                                   onchange="updateTotal(this)">
                                            <button type="button" class="qty-btn" onclick="changeQty(this, 1)">+</button>
                                            <button type="button" class="qty-max-btn" onclick="setMaxQty(this)">Max</button>
                                        </div>
                                    </div>

                                    <div class="buy-total">
                                        <span class="total-label">Kosten:</span>
                                        <span class="total-value"><?= number_format($product['current_price'] * $product['min_quantity'], 0, ',', '.') ?> T</span>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-block">Kaufen</button>
                                </form>
                            <?php else: ?>
                                <div class="cannot-afford-message">
                                    <p>Nicht genug Geld</p>
                                    <span class="needed">Benötigt: <?= number_format($product['current_price'], 0, ',', '.') ?> T</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function changeQty(btn, delta) {
    const input = btn.parentElement.querySelector('.qty-input');
    const max = parseInt(input.dataset.max);
    const min = parseInt(input.dataset.min);
    let value = parseInt(input.value) + delta;
    value = Math.max(min, Math.min(max, value));
    input.value = value;
    updateTotal(input);
}

function setMaxQty(btn) {
    const input = btn.parentElement.querySelector('.qty-input');
    input.value = input.dataset.max;
    updateTotal(input);
}

function updateTotal(input) {
    const price = parseFloat(input.dataset.price);
    const qty = parseInt(input.value);
    const total = price * qty;

    const totalDisplay = input.closest('.buy-form').querySelector('.total-value');
    totalDisplay.textContent = total.toLocaleString('de-DE') + ' T';
}
</script>

<style>
.dealer-detail-page {
    padding: 1rem;
    max-width: 1200px;
    margin: 0 auto;
}

.back-link {
    color: var(--color-text-secondary);
    text-decoration: none;
    font-size: 0.875rem;
}

.back-link:hover {
    color: var(--color-primary);
}

.page-header {
    margin-bottom: 1rem;
}

.page-header h1 {
    margin: 0.5rem 0 0;
}

.location {
    color: var(--color-text-secondary);
    margin: 0.25rem 0 0;
}

.dealer-description {
    color: var(--color-text-secondary);
    margin-bottom: 1.5rem;
    max-width: 600px;
}

.info-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
}

.balance-info,
.date-info,
.modifier-info {
    background: var(--color-bg-secondary);
    padding: 0.75rem 1rem;
    border-radius: 8px;
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.balance-info {
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark, #1a7f37));
    color: white;
}

.info-label {
    font-size: 0.8rem;
    opacity: 0.9;
}

.info-value {
    font-weight: 600;
}

.modifier-badge {
    background: var(--color-warning);
    color: #856404;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--color-bg-secondary);
    border-radius: 12px;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.product-category {
    margin-bottom: 2rem;
}

.category-title {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--color-border);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.product-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.25rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.product-card.cannot-afford {
    opacity: 0.7;
}

.product-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.product-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    overflow: hidden;
    background: var(--color-bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.icon-placeholder {
    font-size: 1.5rem;
}

.product-info h3 {
    margin: 0;
    font-size: 1rem;
}

.quantity-limits {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
}

.product-pricing {
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: var(--color-bg-secondary);
    border-radius: 8px;
}

.current-price {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.price-label {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.price-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-primary);
}

.price-comparison {
    display: flex;
    gap: 0.75rem;
    font-size: 0.8rem;
}

.base-price {
    color: var(--color-text-secondary);
}

.price-diff.positive {
    color: var(--color-danger);
}

.price-diff.negative {
    color: var(--color-success);
}

.trend {
    font-weight: bold;
}

.trend-up {
    color: var(--color-danger);
}

.trend-down {
    color: var(--color-success);
}

.trend-stable {
    color: var(--color-text-secondary);
}

.buy-form {
    border-top: 1px solid var(--color-border);
    padding-top: 1rem;
}

.quantity-selector {
    margin-bottom: 0.75rem;
}

.quantity-selector label {
    display: block;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.quantity-input-group {
    display: flex;
    gap: 0.25rem;
}

.qty-btn {
    width: 36px;
    height: 36px;
    border: 1px solid var(--color-border);
    background: var(--color-bg-secondary);
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    color: var(--color-text);
}

.qty-btn:hover {
    background: var(--color-border);
}

.qty-input {
    width: 60px;
    height: 36px;
    text-align: center;
    border: 1px solid var(--color-border);
    border-radius: 6px;
    background: var(--color-bg);
    color: var(--color-text);
    font-size: 1rem;
}

.qty-max-btn {
    padding: 0 0.75rem;
    height: 36px;
    border: 1px solid var(--color-border);
    background: var(--color-bg-secondary);
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.75rem;
    color: var(--color-text);
}

.qty-max-btn:hover {
    background: var(--color-border);
}

.buy-total {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
}

.total-value {
    font-weight: 700;
    color: var(--color-danger);
}

.cannot-afford-message {
    text-align: center;
    padding: 1rem;
    border-top: 1px solid var(--color-border);
}

.cannot-afford-message p {
    margin: 0 0 0.25rem;
    color: var(--color-danger);
    font-weight: 600;
}

.cannot-afford-message .needed {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.btn-block {
    width: 100%;
}

@media (max-width: 600px) {
    .info-bar {
        flex-direction: column;
    }
}
</style>
