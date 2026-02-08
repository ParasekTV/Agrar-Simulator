<div class="salespoints-page">
    <div class="page-header">
        <h1>Verkaufsstellen</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/salespoints/history" class="btn btn-outline">Verkaufshistorie</a>
            <a href="<?= BASE_URL ?>/storage" class="btn btn-outline">Mein Lager</a>
        </div>
    </div>

    <!-- Suchformular und Countdown -->
    <div class="search-countdown-bar">
        <form action="<?= BASE_URL ?>/salespoints/search" method="get" class="product-search-form">
            <input type="text" name="q" placeholder="Produkt suchen (z.B. Weizen, Milch...)" class="search-input">
            <button type="submit" class="btn btn-primary">Suchen</button>
        </form>
        <div class="price-countdown">
            <span class="countdown-label">Neue Preise in:</span>
            <span class="countdown-timer" id="priceCountdown" data-seconds="<?= $priceChangeTime['total_seconds'] ?>">
                <?= $priceChangeTime['formatted'] ?>
            </span>
        </div>
    </div>

    <!-- Tägliche Preisübersicht -->
    <div class="daily-overview">
        <div class="overview-header">
            <h2>Tagespreise - <?= date('d.m.Y') ?></h2>
            <p class="overview-hint">Preise aktualisieren sich um Mitternacht</p>
        </div>

        <div class="hot-deals-section">
            <h3>Beste Preise heute</h3>
            <div class="deals-grid">
                <?php foreach ($priceOverview as $overview): ?>
                    <?php if (!empty($overview['hot_deals'])): ?>
                        <div class="deal-card">
                            <div class="deal-header">
                                <span class="selling-point-name"><?= htmlspecialchars($overview['selling_point']['name']) ?></span>
                                <span class="deal-badge">+15% oder mehr</span>
                            </div>
                            <ul class="deal-products">
                                <?php foreach (array_slice($overview['hot_deals'], 0, 3) as $deal): ?>
                                    <li>
                                        <span class="product-name"><?= htmlspecialchars($deal['name_de']) ?></span>
                                        <span class="product-price text-success">
                                            <?= number_format($deal['current_price'], 0, ',', '.') ?> T
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <a href="<?= BASE_URL ?>/salespoints/<?= $overview['selling_point']['id'] ?>" class="btn btn-sm btn-primary">
                                Zur Verkaufsstelle
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Verkaufsstellen -->
    <div class="selling-points-section">
        <h2>Alle Verkaufsstellen</h2>

        <div class="selling-points-grid">
            <?php foreach ($sellingPoints as $point): ?>
                <div class="selling-point-card">
                    <div class="sp-header">
                        <div class="sp-icon">&#127978;</div>
                        <div class="sp-info">
                            <h3><?= htmlspecialchars($point['name']) ?></h3>
                            <?php if (!empty($point['location'])): ?>
                                <span class="sp-location"><?= htmlspecialchars($point['location']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($point['description'])): ?>
                        <p class="sp-description"><?= htmlspecialchars($point['description']) ?></p>
                    <?php endif; ?>

                    <div class="sp-footer">
                        <a href="<?= BASE_URL ?>/salespoints/<?= $point['id'] ?>" class="btn btn-primary btn-block">
                            Produkte verkaufen
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Verkaufbare Produkte -->
    <?php if (!empty($sellableProducts)): ?>
        <div class="sellable-products-section">
            <h2>Deine verkaufbaren Produkte</h2>

            <div class="products-table">
                <table>
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Im Lager</th>
                            <th>Bester Preis</th>
                            <th>Verkaufsstelle</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sellableProducts as $product): ?>
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
                                <td><?= number_format($product['quantity']) ?></td>
                                <td class="price-cell">
                                    <span class="price"><?= number_format($product['best_price'], 0, ',', '.') ?> T</span>
                                    <?php
                                    $baseDiff = (($product['best_price'] - $product['base_price']) / $product['base_price']) * 100;
                                    $diffClass = $baseDiff > 0 ? 'text-success' : ($baseDiff < 0 ? 'text-danger' : '');
                                    ?>
                                    <span class="price-diff <?= $diffClass ?>">
                                        <?= $baseDiff >= 0 ? '+' : '' ?><?= number_format($baseDiff, 1) ?>%
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($product['best_selling_point']) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/salespoints/compare/<?= $product['product_id'] ?>"
                                       class="btn btn-sm btn-primary">Verkaufen</a>
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
.salespoints-page {
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

/* Search and Countdown Bar */
.search-countdown-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.product-search-form {
    display: flex;
    gap: 0.5rem;
    flex: 1;
    min-width: 250px;
    max-width: 500px;
}

.search-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    font-size: 0.95rem;
}

.search-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb), 0.1);
}

.price-countdown {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 1rem;
    background: var(--color-bg-secondary);
    border-radius: 8px;
}

.countdown-label {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.countdown-timer {
    font-size: 1.25rem;
    font-weight: 700;
    font-family: monospace;
    color: var(--color-primary);
    min-width: 80px;
    text-align: center;
}

@media (max-width: 600px) {
    .search-countdown-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .product-search-form {
        max-width: none;
    }

    .price-countdown {
        justify-content: center;
    }
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

.hot-deals-section h3 {
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

.deal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.selling-point-name {
    font-weight: 600;
}

.deal-badge {
    background: var(--color-success);
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
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

/* Selling Points */
.selling-points-section {
    margin-bottom: 2rem;
}

.selling-points-section h2 {
    margin-bottom: 1rem;
}

.selling-points-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.selling-point-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.selling-point-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.sp-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.sp-icon {
    font-size: 2rem;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-bg-secondary);
    border-radius: 10px;
}

.sp-info h3 {
    margin: 0;
    font-size: 1.1rem;
}

.sp-location {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.sp-description {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    margin-bottom: 1rem;
}

.sp-footer {
    margin-top: auto;
}

.btn-block {
    width: 100%;
}

/* Sellable Products Table */
.sellable-products-section {
    margin-bottom: 2rem;
}

.sellable-products-section h2 {
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

.price-cell {
    white-space: nowrap;
}

.price-cell .price {
    font-weight: 600;
}

.price-cell .price-diff {
    display: block;
    font-size: 0.75rem;
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
}
</style>

<script>
(function() {
    const countdownEl = document.getElementById('priceCountdown');
    if (!countdownEl) return;

    let seconds = parseInt(countdownEl.dataset.seconds, 10);

    function updateCountdown() {
        if (seconds <= 0) {
            countdownEl.textContent = '00:00:00';
            // Seite neu laden um neue Preise zu zeigen
            setTimeout(() => location.reload(), 1000);
            return;
        }

        const hours = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        countdownEl.textContent =
            String(hours).padStart(2, '0') + ':' +
            String(mins).padStart(2, '0') + ':' +
            String(secs).padStart(2, '0');

        seconds--;
    }

    setInterval(updateCountdown, 1000);
})();
</script>
