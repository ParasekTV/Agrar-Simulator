<div class="product-detail-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/storage" class="back-link">&larr; Zurück zum Lager</a>
        <h1><?= htmlspecialchars($product['name_de']) ?></h1>
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
                        <span class="stat-value <?= $quantity > 0 ? 'text-success' : 'text-danger' ?>">
                            <?= number_format($quantity) ?> Stück
                        </span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Basispreis:</span>
                        <span class="stat-value"><?= number_format($product['base_price'], 0, ',', '.') ?> T</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">Gesamtwert:</span>
                        <span class="stat-value text-success">
                            <?= number_format($quantity * $product['base_price'], 0, ',', '.') ?> T
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($product['description'])): ?>
        <div class="product-description-card">
            <h3>Beschreibung</h3>
            <p><?= htmlspecialchars($product['description']) ?></p>
        </div>
    <?php endif; ?>

    <!-- Verkaufsmöglichkeiten -->
    <div class="selling-options">
        <h2>Verkaufsmöglichkeiten</h2>

        <?php if (empty($bestPrices)): ?>
            <div class="empty-state">
                <p>Dieses Produkt wird aktuell von keiner Verkaufsstelle gekauft.</p>
            </div>
        <?php else: ?>
            <div class="prices-list">
                <?php foreach (array_slice($bestPrices, 0, 5) as $index => $price): ?>
                    <?php
                    $priceDiff = (($price['current_price'] - $product['base_price']) / $product['base_price']) * 100;
                    $isBest = $index === 0;
                    ?>
                    <div class="price-item <?= $isBest ? 'best' : '' ?>">
                        <div class="price-info">
                            <span class="sp-name"><?= htmlspecialchars($price['selling_point_name']) ?></span>
                            <?php if ($isBest): ?>
                                <span class="best-badge">Bester Preis</span>
                            <?php endif; ?>
                        </div>
                        <div class="price-value">
                            <span class="price"><?= number_format($price['current_price'], 0, ',', '.') ?> T</span>
                            <span class="diff <?= $priceDiff >= 0 ? 'positive' : 'negative' ?>">
                                <?= $priceDiff >= 0 ? '+' : '' ?><?= number_format($priceDiff, 1) ?>%
                            </span>
                        </div>
                        <?php if ($quantity > 0): ?>
                            <a href="<?= BASE_URL ?>/salespoints/<?= $price['selling_point_id'] ?>" class="btn btn-sm btn-primary">
                                Verkaufen
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($quantity > 0): ?>
                <div class="action-footer">
                    <a href="<?= BASE_URL ?>/salespoints/compare/<?= $product['id'] ?>" class="btn btn-primary">
                        Alle Preise vergleichen
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.product-detail-page {
    padding: 1rem;
    max-width: 800px;
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
    width: 100px;
    height: 100px;
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
    font-size: 3rem;
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
    margin-top: 1rem;
}

.stat {
    display: flex;
    flex-direction: column;
}

.product-detail-page .stat-label {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
}

.product-detail-page .stat-value {
    font-weight: 600;
}

.product-description-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.product-description-card h3 {
    margin: 0 0 0.5rem;
}

.product-description-card p {
    margin: 0;
    color: var(--color-text-secondary);
}

.selling-options {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
}

.selling-options h2 {
    margin: 0 0 1rem;
}

.prices-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.price-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1rem;
    background: var(--color-bg-secondary);
    border-radius: 8px;
}

.price-item.best {
    border: 2px solid var(--color-success);
}

.price-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sp-name {
    font-weight: 600;
}

.best-badge {
    background: var(--color-success);
    color: white;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.price-value {
    text-align: right;
    margin-right: 1rem;
}

.price-value .price {
    display: block;
    font-weight: 700;
    font-size: 1.1rem;
}

.price-value .diff {
    font-size: 0.8rem;
}

.price-value .diff.positive {
    color: var(--color-success);
}

.price-value .diff.negative {
    color: var(--color-danger);
}

.action-footer {
    margin-top: 1.5rem;
    text-align: center;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: var(--color-text-secondary);
}

.text-success {
    color: var(--color-success);
}

.text-danger {
    color: var(--color-danger);
}

@media (max-width: 600px) {
    .product-card-main {
        flex-direction: column;
        text-align: center;
    }

    .product-stats {
        flex-direction: column;
        gap: 0.5rem;
    }

    .price-item {
        flex-wrap: wrap;
    }
}
</style>
