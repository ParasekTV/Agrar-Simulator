<div class="shop-search-page">
    <div class="page-header">
        <h1>Produktsuche</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/shop" class="btn btn-outline">Alle Haendler</a>
        </div>
    </div>

    <!-- Suchformular und Countdown -->
    <div class="search-countdown-bar">
        <form action="<?= BASE_URL ?>/shop/search" method="get" class="product-search-form">
            <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Produkt suchen (z.B. Saatgut, Duenger...)" class="search-input">
            <button type="submit" class="btn btn-primary">Suchen</button>
        </form>
        <div class="price-countdown">
            <span class="countdown-label">Neue Preise in:</span>
            <span class="countdown-timer" id="priceCountdown" data-seconds="<?= $priceChangeTime['total_seconds'] ?>">
                <?= $priceChangeTime['formatted'] ?>
            </span>
        </div>
    </div>

    <!-- Kontostand -->
    <div class="balance-info">
        <span class="balance-label">Verfuegbares Geld:</span>
        <span class="balance-amount"><?= number_format($farm['money'], 0, ',', '.') ?> T</span>
    </div>

    <?php if (empty($query)): ?>
        <div class="search-info-card">
            <h3>Wer verkauft was?</h3>
            <p>Gib einen Produktnamen ein, um herauszufinden, welche Haendler dieses Produkt anbieten und wer den guenstigsten Preis hat.</p>
            <p class="search-examples">Beispiele: Weizen, Saatgut, Duenger, Diesel, ...</p>
        </div>
    <?php elseif (empty($results)): ?>
        <div class="search-no-results">
            <p>Keine Haendler gefunden, die "<?= htmlspecialchars($query) ?>" anbieten.</p>
            <p>Versuche einen anderen Suchbegriff.</p>
        </div>
    <?php else: ?>
        <div class="search-results">
            <h2><?= count($results) ?> Produkt<?= count($results) > 1 ? 'e' : '' ?> gefunden</h2>

            <?php foreach ($results as $result): ?>
                <div class="search-result-card">
                    <div class="result-header">
                        <div class="result-product">
                            <?php if (!empty($result['product']['icon'])): ?>
                                <img src="<?= BASE_URL ?>/img/products/<?= htmlspecialchars($result['product']['icon']) ?>"
                                     alt="" class="product-icon"
                                     onerror="this.style.display='none'">
                            <?php endif; ?>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($result['product']['name_de'] ?? $result['product']['name']) ?></h3>
                                <span class="product-category"><?= htmlspecialchars($result['product']['category']) ?></span>
                            </div>
                        </div>
                        <div class="result-summary">
                            <span class="dealer-count"><?= $result['dealer_count'] ?> Haendler</span>
                            <span class="price-range">
                                <?= number_format($result['best_price'], 0, ',', '.') ?> - <?= number_format($result['worst_price'], 0, ',', '.') ?> T
                            </span>
                        </div>
                    </div>

                    <div class="result-dealers">
                        <table class="dealers-table">
                            <thead>
                                <tr>
                                    <th>Haendler</th>
                                    <th>Ort</th>
                                    <th>Preis</th>
                                    <th>Trend</th>
                                    <th>Aktion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result['dealers'] as $index => $dealer): ?>
                                    <tr class="<?= $index === 0 ? 'best-price' : '' ?>">
                                        <td>
                                            <?php if ($index === 0): ?>
                                                <span class="best-badge">Guenstigster</span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($dealer['dealer_name']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($dealer['location'] ?? '-') ?></td>
                                        <td class="price-cell">
                                            <strong><?= number_format($dealer['current_price'], 0, ',', '.') ?> T</strong>
                                            <?php
                                            $baseWithMod = $dealer['base_price'] * ($dealer['dealer_modifier'] ?? 1.0);
                                            $diff = (($dealer['current_price'] - $baseWithMod) / $baseWithMod) * 100;
                                            $diffClass = $diff < -5 ? 'text-success' : ($diff > 5 ? 'text-danger' : 'text-muted');
                                            ?>
                                            <span class="price-diff <?= $diffClass ?>">
                                                <?= $diff >= 0 ? '+' : '' ?><?= number_format($diff, 1) ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $trendIcon = match($dealer['price_trend']) {
                                                'rising' => '<span class="trend-up" title="Steigend">&#9650;</span>',
                                                'falling' => '<span class="trend-down" title="Fallend">&#9660;</span>',
                                                default => '<span class="trend-stable" title="Stabil">&#9644;</span>'
                                            };
                                            echo $trendIcon;
                                            ?>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/shop/<?= $dealer['dealer_id'] ?>" class="btn btn-sm btn-primary">
                                                Kaufen
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.shop-search-page {
    padding: 1rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
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
}

/* Balance Info */
.balance-info {
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark, #1a7f37));
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
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

/* Info Card */
.search-info-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
}

.search-info-card h3 {
    margin-bottom: 1rem;
}

.search-examples {
    color: var(--color-text-secondary);
    font-style: italic;
    margin-top: 1rem;
}

/* No Results */
.search-no-results {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    color: var(--color-text-secondary);
}

/* Search Results */
.search-results h2 {
    margin-bottom: 1.5rem;
}

.search-result-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.result-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem;
    border-bottom: 1px solid var(--color-border);
    background: var(--color-bg-secondary);
    flex-wrap: wrap;
    gap: 1rem;
}

.result-product {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.product-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: cover;
}

.product-info h3 {
    margin: 0;
    font-size: 1.1rem;
}

.product-category {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
    text-transform: capitalize;
}

.result-summary {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.25rem;
}

.dealer-count {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.price-range {
    font-weight: 600;
    color: var(--color-primary);
}

/* Dealers Table */
.result-dealers {
    overflow-x: auto;
}

.dealers-table {
    width: 100%;
    border-collapse: collapse;
}

.dealers-table th,
.dealers-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--color-border);
}

.dealers-table th {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.dealers-table tr:last-child td {
    border-bottom: none;
}

.dealers-table tr.best-price {
    background: rgba(var(--color-success-rgb), 0.05);
}

.best-badge {
    display: inline-block;
    background: var(--color-success);
    color: white;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    margin-right: 0.5rem;
    text-transform: uppercase;
}

.price-cell {
    white-space: nowrap;
}

.price-diff {
    display: block;
    font-size: 0.75rem;
}

.text-success { color: var(--color-success); }
.text-danger { color: var(--color-danger); }
.text-muted { color: var(--color-text-secondary); }

.trend-up { color: var(--color-danger); font-size: 0.75rem; }
.trend-down { color: var(--color-success); font-size: 0.75rem; }
.trend-stable { color: var(--color-text-secondary); }

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

    .dealers-table {
        min-width: 500px;
    }

    .balance-info {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
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
