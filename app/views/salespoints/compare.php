<div class="compare-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/salespoints" class="back-link">&larr; Zurück zu Verkaufsstellen</a>
        <h1>Preisvergleich: <?= htmlspecialchars($product['name_de']) ?></h1>
    </div>

    <div class="product-overview">
        <div class="product-card-main">
            <div class="product-icon-large">
                <?php if ($product['icon']): ?>
                    <img src="<?= BASE_URL ?>/img/products/<?= htmlspecialchars($product['icon']) ?>"
                         alt="" onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                <?php else: ?>
                    <span class="icon-placeholder">&#128230;</span>
                <?php endif; ?>
            </div>
            <div class="product-details">
                <h2><?= htmlspecialchars($product['name_de']) ?></h2>
                <span class="product-category"><?= htmlspecialchars($product['category']) ?></span>
                <div class="product-stats">
                    <div class="stat">
                        <span class="stat-label">Im Lager:</span>
                        <span class="stat-value <?= $inStock > 0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($inStock) ?> Stück
                        </span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Basispreis:</span>
                        <span class="stat-value"><?= number_format($product['base_price'], 0, ',', '.') ?> T</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($prices)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128722;</div>
            <h3>Kein Käufer gefunden</h3>
            <p>Dieses Produkt wird aktuell von keiner Verkaufsstelle gekauft.</p>
        </div>
    <?php else: ?>
        <div class="prices-section">
            <h2>Preise nach Verkaufsstelle <span class="date-badge"><?= date('d.m.Y') ?></span></h2>

            <div class="prices-list">
                <?php foreach ($prices as $index => $price): ?>
                    <?php
                    $priceDiff = (($price['current_price'] - $product['base_price']) / $product['base_price']) * 100;
                    $isBest = $index === 0;
                    $trendIcon = $price['price_trend'] === 'rising' ? '&#8593;' :
                                ($price['price_trend'] === 'falling' ? '&#8595;' : '&#8596;');
                    $trendClass = $price['price_trend'] === 'rising' ? 'trend-up' :
                                 ($price['price_trend'] === 'falling' ? 'trend-down' : 'trend-stable');
                    ?>

                    <div class="price-card <?= $isBest ? 'best-price' : '' ?>">
                        <?php if ($isBest): ?>
                            <div class="best-badge">Bester Preis!</div>
                        <?php endif; ?>

                        <div class="price-header">
                            <div class="sp-info">
                                <h3><?= htmlspecialchars($price['selling_point_name']) ?></h3>
                                <?php if (!empty($price['location'])): ?>
                                    <span class="sp-location"><?= htmlspecialchars($price['location']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="price-display">
                                <span class="price-amount"><?= number_format($price['current_price'], 0, ',', '.') ?> T</span>
                                <span class="price-diff <?= $priceDiff >= 0 ? 'positive' : 'negative' ?>">
                                    <?= $priceDiff >= 0 ? '+' : '' ?><?= number_format($priceDiff, 1) ?>%
                                </span>
                                <span class="trend <?= $trendClass ?>"><?= $trendIcon ?></span>
                            </div>
                        </div>

                        <?php if ($inStock > 0): ?>
                            <form action="<?= BASE_URL ?>/salespoints/sell" method="POST" class="sell-form">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="selling_point_id" value="<?= $price['selling_point_id'] ?>">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                                <div class="sell-row">
                                    <div class="quantity-group">
                                        <label>Menge:</label>
                                        <div class="quantity-controls">
                                            <input type="number" name="quantity" value="<?= $inStock ?>" min="1" max="<?= $inStock ?>"
                                                   class="qty-input" data-price="<?= $price['current_price'] ?>"
                                                   onchange="updateCompareTotal(this)">
                                            <span class="max-hint">max. <?= $inStock ?></span>
                                        </div>
                                    </div>

                                    <div class="total-group">
                                        <span class="total-label">Erlös:</span>
                                        <span class="total-value"><?= number_format($price['current_price'] * $inStock, 0, ',', '.') ?> T</span>
                                    </div>

                                    <button type="submit" class="btn <?= $isBest ? 'btn-success' : 'btn-primary' ?>">
                                        Hier verkaufen
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="no-stock-info">
                                <span>Keine Produkte im Lager</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateCompareTotal(input) {
    const price = parseFloat(input.dataset.price);
    const qty = parseInt(input.value) || 0;
    const total = price * qty;

    const totalDisplay = input.closest('.sell-form').querySelector('.total-value');
    totalDisplay.textContent = total.toLocaleString('de-DE') + ' T';
}
</script>

<style>
.compare-page {
    padding: 1rem;
    max-width: 900px;
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
    margin-bottom: 2rem;
}

.page-header h1 {
    margin: 0.5rem 0 0;
}

.product-overview {
    margin-bottom: 2rem;
}

.product-card-main {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.product-icon-large {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    overflow: hidden;
    background: var(--color-bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.product-icon-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.icon-placeholder {
    font-size: 2.5rem;
}

.product-details h2 {
    margin: 0;
}

.product-category {
    display: inline-block;
    background: var(--color-bg-secondary);
    padding: 0.2rem 0.6rem;
    border-radius: 4px;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.product-stats {
    display: flex;
    gap: 2rem;
    margin-top: 0.75rem;
}

.stat {
    display: flex;
    flex-direction: column;
}

.compare-page .stat-label {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
}

.compare-page .stat-value {
    font-weight: 600;
}

.prices-section h2 {
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.date-badge {
    font-size: 0.8rem;
    font-weight: normal;
    background: var(--color-bg-secondary);
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
}

.prices-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.price-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.25rem;
    position: relative;
}

.price-card.best-price {
    border-color: var(--color-success);
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.2);
}

.best-badge {
    position: absolute;
    top: -10px;
    right: 1rem;
    background: var(--color-success);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.price-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.sp-info h3 {
    margin: 0;
}

.sp-location {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.price-display {
    text-align: right;
}

.price-amount {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-primary);
}

.price-diff {
    font-size: 0.875rem;
    margin-right: 0.5rem;
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
    padding-top: 1rem;
    border-top: 1px solid var(--color-border);
}

.sell-row {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.quantity-group label {
    display: block;
    font-size: 0.75rem;
    color: var(--color-text-secondary);
    margin-bottom: 0.25rem;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.qty-input {
    width: 80px;
    height: 36px;
    text-align: center;
    border: 1px solid var(--color-border);
    border-radius: 6px;
    background: var(--color-bg);
    color: var(--color-text);
    font-size: 1rem;
}

.max-hint {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
}

.total-group {
    display: flex;
    flex-direction: column;
}

.total-label {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
}

.total-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--color-success);
}

.no-stock-info {
    padding-top: 1rem;
    border-top: 1px solid var(--color-border);
    text-align: center;
    color: var(--color-text-secondary);
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

.text-success {
    color: var(--color-success);
}

.text-danger {
    color: var(--color-danger);
}

@media (max-width: 600px) {
    .sell-row {
        flex-direction: column;
        align-items: stretch;
    }

    .sell-row .btn {
        width: 100%;
    }
}
</style>
