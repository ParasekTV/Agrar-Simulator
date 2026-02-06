<div class="selling-point-detail-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/salespoints" class="back-link">&larr; Zurück zu Verkaufsstellen</a>
        <h1><?= htmlspecialchars($sellingPoint['name']) ?></h1>
        <?php if (!empty($sellingPoint['location'])): ?>
            <p class="location"><?= htmlspecialchars($sellingPoint['location']) ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($sellingPoint['description'])): ?>
        <p class="sp-description"><?= htmlspecialchars($sellingPoint['description']) ?></p>
    <?php endif; ?>

    <div class="date-info">
        <span class="date-label">Preise gültig am:</span>
        <span class="date-value"><?= date('d.m.Y') ?></span>
    </div>

    <?php if (empty($sellingPoint['products'])): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128679;</div>
            <h3>Keine Produkte verfügbar</h3>
            <p>Diese Verkaufsstelle kauft aktuell keine Produkte.</p>
        </div>
    <?php else: ?>
        <!-- Produkte gruppiert nach Kategorie -->
        <?php
        $grouped = [];
        foreach ($sellingPoint['products'] as $product) {
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
                        $inStock = $product['in_stock'] ?? 0;
                        $canSell = $inStock > 0;
                        $priceDiff = (($product['current_price'] - $product['base_price']) / $product['base_price']) * 100;
                        $trendIcon = $product['price_trend'] === 'rising' ? '&#8593;' :
                                    ($product['price_trend'] === 'falling' ? '&#8595;' : '&#8596;');
                        $trendClass = $product['price_trend'] === 'rising' ? 'trend-up' :
                                     ($product['price_trend'] === 'falling' ? 'trend-down' : 'trend-stable');
                        ?>

                        <div class="product-card <?= $canSell ? '' : 'no-stock' ?>">
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
                                    <span class="stock-info <?= $canSell ? 'has-stock' : 'no-stock-text' ?>">
                                        <?= $inStock ?> im Lager
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

                            <?php if ($canSell): ?>
                                <form action="<?= BASE_URL ?>/salespoints/sell" method="POST" class="sell-form">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="selling_point_id" value="<?= $sellingPoint['id'] ?>">
                                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

                                    <div class="quantity-selector">
                                        <label>Menge:</label>
                                        <div class="quantity-input-group">
                                            <button type="button" class="qty-btn" onclick="changeQty(this, -1)">-</button>
                                            <input type="number" name="quantity" value="1" min="1" max="<?= $inStock ?>"
                                                   class="qty-input" data-max="<?= $inStock ?>" data-price="<?= $product['current_price'] ?>"
                                                   onchange="updateTotal(this)">
                                            <button type="button" class="qty-btn" onclick="changeQty(this, 1)">+</button>
                                            <button type="button" class="qty-max-btn" onclick="setMaxQty(this)">Max</button>
                                        </div>
                                    </div>

                                    <div class="sell-total">
                                        <span class="total-label">Erlös:</span>
                                        <span class="total-value"><?= number_format($product['current_price'], 0, ',', '.') ?> T</span>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-block">Verkaufen</button>
                                </form>
                            <?php else: ?>
                                <div class="no-stock-message">
                                    <p>Nicht im Lager vorhanden</p>
                                    <a href="<?= BASE_URL ?>/productions" class="btn btn-sm btn-outline">Produzieren</a>
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
    let value = parseInt(input.value) + delta;
    value = Math.max(1, Math.min(max, value));
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

    const totalDisplay = input.closest('.sell-form').querySelector('.total-value');
    totalDisplay.textContent = total.toLocaleString('de-DE') + ' T';
}
</script>

<style>
.selling-point-detail-page {
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
    margin-bottom: 1.5rem;
}

.page-header h1 {
    margin: 0.5rem 0 0;
}

.location {
    color: var(--color-text-secondary);
    margin: 0.25rem 0 0;
}

.sp-description {
    color: var(--color-text-secondary);
    margin-bottom: 1.5rem;
    max-width: 600px;
}

.date-info {
    background: var(--color-bg-secondary);
    padding: 0.75rem 1rem;
    border-radius: 8px;
    display: inline-block;
    margin-bottom: 2rem;
}

.date-label {
    color: var(--color-text-secondary);
    margin-right: 0.5rem;
}

.date-value {
    font-weight: 600;
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

.product-card.no-stock {
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

.stock-info {
    font-size: 0.8rem;
}

.stock-info.has-stock {
    color: var(--color-success);
}

.stock-info.no-stock-text {
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
    color: var(--color-success);
}

.price-diff.negative {
    color: var(--color-danger);
}

.trend {
    font-weight: bold;
}

.trend-up {
    color: var(--color-success);
}

.trend-down {
    color: var(--color-danger);
}

.trend-stable {
    color: var(--color-text-secondary);
}

.sell-form {
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

.sell-total {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
}

.total-value {
    font-weight: 700;
    color: var(--color-success);
}

.no-stock-message {
    text-align: center;
    padding: 1rem;
    border-top: 1px solid var(--color-border);
}

.no-stock-message p {
    margin: 0 0 0.5rem;
    color: var(--color-text-secondary);
    font-size: 0.875rem;
}

.btn-block {
    width: 100%;
}
</style>
