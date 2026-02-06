<div class="compare-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/shop" class="back-link">&larr; Zurück zum Shop</a>
        <h1>Preisvergleich</h1>
    </div>

    <!-- Produkt-Info -->
    <div class="product-info-card">
        <div class="product-header">
            <div class="product-icon">
                <?php if (!empty($product['icon'])): ?>
                    <img src="<?= BASE_URL ?>/img/products/<?= htmlspecialchars($product['icon']) ?>"
                         alt="" onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                <?php else: ?>
                    <span class="icon-placeholder">&#128230;</span>
                <?php endif; ?>
            </div>
            <div class="product-details">
                <h2><?= htmlspecialchars($product['name_de']) ?></h2>
                <div class="product-meta">
                    <span class="category"><?= htmlspecialchars(ucfirst($product['category'] ?? 'Allgemein')) ?></span>
                    <span class="base-price">Basispreis: <?= number_format($product['base_price'], 0, ',', '.') ?> T</span>
                </div>
            </div>
            <div class="product-stock">
                <span class="stock-label">Im Lager:</span>
                <span class="stock-value"><?= number_format($inStock) ?></span>
            </div>
        </div>
    </div>

    <!-- Verfügbares Geld -->
    <div class="balance-info">
        <span class="balance-label">Verfügbares Geld:</span>
        <span class="balance-amount"><?= number_format($farm['money'], 0, ',', '.') ?> T</span>
    </div>

    <?php if (empty($prices)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128722;</div>
            <h3>Nicht verfügbar</h3>
            <p>Dieses Produkt wird derzeit von keinem Händler angeboten.</p>
        </div>
    <?php else: ?>
        <div class="prices-section">
            <h3>Preise bei allen Händlern <span class="date-hint">(<?= date('d.m.Y') ?>)</span></h3>

            <div class="prices-grid">
                <?php foreach ($prices as $index => $price): ?>
                    <?php
                    $isBest = $index === 0;
                    $canBuy = $farm['money'] >= $price['current_price'];
                    $priceDiff = (($price['current_price'] - $product['base_price']) / $product['base_price']) * 100;
                    $trendIcon = $price['price_trend'] === 'rising' ? '&#8593;' :
                                ($price['price_trend'] === 'falling' ? '&#8595;' : '&#8596;');
                    $trendClass = $price['price_trend'] === 'rising' ? 'trend-up' :
                                 ($price['price_trend'] === 'falling' ? 'trend-down' : 'trend-stable');
                    ?>

                    <div class="price-card <?= $isBest ? 'best-price' : '' ?> <?= $canBuy ? '' : 'cannot-afford' ?>">
                        <?php if ($isBest): ?>
                            <div class="best-badge">Günstigster Preis</div>
                        <?php endif; ?>

                        <div class="dealer-info">
                            <h4><?= htmlspecialchars($price['dealer_name']) ?></h4>
                            <?php if (!empty($price['location'])): ?>
                                <span class="location"><?= htmlspecialchars($price['location']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="price-display">
                            <span class="current-price"><?= number_format($price['current_price'], 0, ',', '.') ?> T</span>
                            <div class="price-meta">
                                <span class="price-diff <?= $priceDiff >= 0 ? 'higher' : 'lower' ?>">
                                    <?= $priceDiff >= 0 ? '+' : '' ?><?= number_format($priceDiff, 1) ?>%
                                </span>
                                <span class="trend <?= $trendClass ?>"><?= $trendIcon ?></span>
                            </div>
                        </div>

                        <div class="quantity-info">
                            <span>Min: <?= $price['min_quantity'] ?></span>
                            <span>Max: <?= $price['max_quantity'] ?></span>
                        </div>

                        <div class="card-actions">
                            <?php if ($canBuy): ?>
                                <a href="<?= BASE_URL ?>/shop/<?= $price['dealer_id'] ?>" class="btn btn-primary btn-block">
                                    Beim Händler kaufen
                                </a>
                            <?php else: ?>
                                <button class="btn btn-outline btn-block" disabled>Nicht genug Geld</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.compare-page {
    padding: 1rem;
    max-width: 1000px;
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

.product-info-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.product-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.product-icon {
    width: 64px;
    height: 64px;
    border-radius: 12px;
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
    font-size: 2rem;
}

.product-details {
    flex: 1;
}

.product-details h2 {
    margin: 0 0 0.5rem;
}

.product-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.category {
    background: var(--color-bg-secondary);
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
}

.product-stock {
    text-align: right;
}

.stock-label {
    display: block;
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.stock-value {
    font-size: 1.5rem;
    font-weight: 700;
}

.balance-info {
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark, #1a7f37));
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.balance-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.balance-amount {
    font-size: 1.25rem;
    font-weight: 700;
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

.prices-section h3 {
    margin-bottom: 1rem;
}

.date-hint {
    font-weight: normal;
    color: var(--color-text-secondary);
    font-size: 0.875rem;
}

.prices-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.price-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
}

.price-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.price-card.best-price {
    border-color: var(--color-success);
    border-width: 2px;
}

.price-card.cannot-afford {
    opacity: 0.7;
}

.best-badge {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--color-success);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.dealer-info {
    margin-bottom: 1rem;
}

.dealer-info h4 {
    margin: 0 0 0.25rem;
}

.dealer-info .location {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.price-display {
    background: var(--color-bg-secondary);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    text-align: center;
}

.current-price {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-primary);
}

.price-meta {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    margin-top: 0.5rem;
    font-size: 0.875rem;
}

.price-diff.higher {
    color: var(--color-danger);
}

.price-diff.lower {
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

.quantity-info {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    color: var(--color-text-secondary);
    margin-bottom: 1rem;
}

.card-actions {
    margin-top: auto;
}

.btn-block {
    width: 100%;
}

@media (max-width: 600px) {
    .product-header {
        flex-direction: column;
        text-align: center;
    }

    .product-stock {
        text-align: center;
    }

    .balance-info {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
}
</style>
