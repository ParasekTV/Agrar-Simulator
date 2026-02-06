<div class="shop-page">
    <div class="page-header">
        <h1>Einkauf</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/shop/history" class="btn btn-outline">Einkaufshistorie</a>
            <a href="<?= BASE_URL ?>/storage" class="btn btn-outline">Mein Lager</a>
        </div>
    </div>

    <!-- Kontostand -->
    <div class="balance-info">
        <span class="balance-label">Verfügbares Geld:</span>
        <span class="balance-amount"><?= number_format($farm['money'], 0, ',', '.') ?> T</span>
    </div>

    <!-- Tägliche Preisübersicht -->
    <div class="daily-overview">
        <div class="overview-header">
            <h2>Tagespreise - <?= date('d.m.Y') ?></h2>
            <p class="overview-hint">Preise ändern sich täglich und variieren je nach Händler</p>
        </div>

        <div class="deals-section">
            <h3>Günstige Angebote heute</h3>
            <div class="deals-grid">
                <?php foreach ($priceOverview as $overview): ?>
                    <?php if (!empty($overview['good_deals'])): ?>
                        <div class="deal-card deal-good">
                            <div class="deal-header">
                                <span class="dealer-name"><?= htmlspecialchars($overview['dealer']['name']) ?></span>
                                <span class="deal-badge deal-badge-good">Günstig</span>
                            </div>
                            <ul class="deal-products">
                                <?php foreach (array_slice($overview['good_deals'], 0, 3) as $deal): ?>
                                    <li>
                                        <span class="product-name"><?= htmlspecialchars($deal['name_de']) ?></span>
                                        <span class="product-price text-success">
                                            <?= number_format($deal['current_price'], 0, ',', '.') ?> T
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="<?= BASE_URL ?>/shop/<?= $overview['dealer']['id'] ?>" class="btn btn-sm btn-primary">
                                Zum Händler
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Händler -->
    <div class="dealers-section">
        <h2>Alle Händler</h2>

        <div class="dealers-grid">
            <?php foreach ($dealers as $dealer): ?>
                <div class="dealer-card">
                    <div class="dealer-header">
                        <div class="dealer-icon">&#128722;</div>
                        <div class="dealer-info">
                            <h3><?= htmlspecialchars($dealer['name']) ?></h3>
                            <?php if (!empty($dealer['location'])): ?>
                                <span class="dealer-location"><?= htmlspecialchars($dealer['location']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="dealer-modifier">
                            <?php
                            $modPercent = (($dealer['price_modifier'] - 1) * 100);
                            $modClass = $modPercent <= 10 ? 'modifier-good' : ($modPercent >= 25 ? 'modifier-high' : 'modifier-medium');
                            ?>
                            <span class="modifier-badge <?= $modClass ?>">
                                <?= $modPercent >= 0 ? '+' : '' ?><?= number_format($modPercent, 0) ?>%
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($dealer['description'])): ?>
                        <p class="dealer-description"><?= htmlspecialchars($dealer['description']) ?></p>
                    <?php endif; ?>

                    <div class="dealer-stats">
                        <span class="stat"><?= $dealer['product_count'] ?> Produkte</span>
                    </div>

                    <div class="dealer-footer">
                        <a href="<?= BASE_URL ?>/shop/<?= $dealer['id'] ?>" class="btn btn-primary btn-block">
                            Produkte kaufen
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Kaufbare Produkte -->
    <?php if (!empty($buyableProducts)): ?>
        <div class="buyable-products-section">
            <h2>Produktübersicht</h2>

            <div class="products-table">
                <table>
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Kategorie</th>
                            <th>Bester Preis</th>
                            <th>Händler</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($buyableProducts, 0, 20) as $product): ?>
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <?php if (!empty($product['icon'])): ?>
                                            <img src="<?= BASE_URL ?>/img/products/<?= htmlspecialchars($product['icon']) ?>"
                                                 alt="" class="product-thumb"
                                                 onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($product['name_de']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="category-badge"><?= htmlspecialchars(ucfirst($product['category'] ?? 'Allgemein')) ?></span>
                                </td>
                                <td class="price-cell">
                                    <span class="price"><?= number_format($product['best_price'], 0, ',', '.') ?> T</span>
                                </td>
                                <td><?= htmlspecialchars($product['best_dealer'] ?? '-') ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/shop/compare/<?= $product['product_id'] ?>"
                                       class="btn btn-sm btn-outline">Vergleichen</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.shop-page {
    padding: 1rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-actions {
    display: flex;
    gap: 0.5rem;
}

/* Balance Info */
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
    font-size: 1.5rem;
    font-weight: 700;
}

/* Daily Overview */
.daily-overview {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.overview-header {
    margin-bottom: 1.5rem;
}

.overview-header h2 {
    margin: 0;
}

.overview-hint {
    color: var(--color-text-secondary);
    font-size: 0.875rem;
    margin: 0.25rem 0 0;
}

.deals-section h3 {
    font-size: 1rem;
    margin-bottom: 1rem;
}

.deals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
}

.deal-card {
    background: var(--color-bg-secondary);
    border-radius: 8px;
    padding: 1rem;
}

.deal-card.deal-good {
    border-left: 3px solid var(--color-success);
}

.deal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.dealer-name {
    font-weight: 600;
}

.deal-badge {
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.deal-badge-good {
    background: var(--color-success);
    color: white;
}

.deal-products {
    list-style: none;
    padding: 0;
    margin: 0 0 1rem;
}

.deal-products li {
    display: flex;
    justify-content: space-between;
    padding: 0.25rem 0;
    font-size: 0.875rem;
}

.product-price {
    font-weight: 600;
}

/* Dealers */
.dealers-section {
    margin-bottom: 2rem;
}

.dealers-section h2 {
    margin-bottom: 1rem;
}

.dealers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.dealer-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.dealer-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.dealer-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.dealer-icon {
    font-size: 2rem;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-bg-secondary);
    border-radius: 10px;
}

.dealer-info {
    flex: 1;
}

.dealer-info h3 {
    margin: 0;
    font-size: 1.1rem;
}

.dealer-location {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.dealer-modifier {
    text-align: right;
}

.modifier-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.modifier-good {
    background: #d4edda;
    color: #155724;
}

.modifier-medium {
    background: #fff3cd;
    color: #856404;
}

.modifier-high {
    background: #f8d7da;
    color: #721c24;
}

.dealer-description {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    margin-bottom: 1rem;
}

.dealer-stats {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
    margin-bottom: 1rem;
}

.dealer-footer {
    margin-top: auto;
}

.btn-block {
    width: 100%;
}

/* Products Table */
.buyable-products-section {
    margin-bottom: 2rem;
}

.buyable-products-section h2 {
    margin-bottom: 1rem;
}

.products-table {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    overflow: hidden;
}

.products-table table {
    width: 100%;
    border-collapse: collapse;
}

.products-table th,
.products-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--color-border);
}

.products-table th {
    background: var(--color-bg-secondary);
    font-weight: 600;
    font-size: 0.875rem;
}

.products-table tr:last-child td {
    border-bottom: none;
}

.product-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.product-thumb {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    object-fit: cover;
}

.category-badge {
    background: var(--color-bg-secondary);
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
}

.price-cell .price {
    font-weight: 600;
}

.text-success {
    color: var(--color-success);
}

.text-danger {
    color: var(--color-danger);
}

@media (max-width: 768px) {
    .products-table {
        overflow-x: auto;
    }

    .products-table table {
        min-width: 600px;
    }

    .balance-info {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
}
</style>
