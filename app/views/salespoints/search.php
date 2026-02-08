<div class="salespoints-search-page">
    <div class="page-header">
        <h1>Produktsuche</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/salespoints" class="btn btn-outline">Alle Verkaufsstellen</a>
        </div>
    </div>

    <!-- Suchformular und Countdown -->
    <div class="search-countdown-bar">
        <form action="<?= BASE_URL ?>/salespoints/search" method="get" class="product-search-form">
            <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Produkt suchen (z.B. Weizen, Milch...)" class="search-input">
            <button type="submit" class="btn btn-primary">Suchen</button>
        </form>
        <div class="price-countdown">
            <span class="countdown-label">Neue Preise in:</span>
            <span class="countdown-timer" id="priceCountdown" data-seconds="<?= $priceChangeTime['total_seconds'] ?>">
                <?= $priceChangeTime['formatted'] ?>
            </span>
        </div>
    </div>

    <?php if (empty($query)): ?>
        <div class="search-info-card">
            <h3>Wer kauft was?</h3>
            <p>Gib einen Produktnamen ein, um herauszufinden, welche Verkaufsstellen dieses Produkt kaufen und wer den besten Preis bietet.</p>
            <p class="search-examples">Beispiele: Weizen, Milch, Eier, Mehl, Brot, ...</p>
        </div>
    <?php elseif (empty($results)): ?>
        <div class="search-no-results">
            <p>Keine Verkaufsstellen gefunden, die "<?= htmlspecialchars($query) ?>" kaufen.</p>
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
                            <span class="selling-point-count"><?= $result['selling_point_count'] ?> Abnehmer</span>
                            <span class="price-range">
                                <?= number_format($result['worst_price'], 0, ',', '.') ?> - <?= number_format($result['best_price'], 0, ',', '.') ?> T
                            </span>
                        </div>
                    </div>

                    <div class="result-selling-points">
                        <table class="selling-points-table">
                            <thead>
                                <tr>
                                    <th>Verkaufsstelle</th>
                                    <th>Ort</th>
                                    <th>Preis</th>
                                    <th>Trend</th>
                                    <th>Aktion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result['selling_points'] as $index => $sp): ?>
                                    <tr class="<?= $index === 0 ? 'best-price' : '' ?>">
                                        <td>
                                            <?php if ($index === 0): ?>
                                                <span class="best-badge">Bester Preis</span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($sp['selling_point_name']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($sp['location'] ?? '-') ?></td>
                                        <td class="price-cell">
                                            <strong><?= number_format($sp['current_price'], 0, ',', '.') ?> T</strong>
                                            <?php
                                            $diff = (($sp['current_price'] - $sp['base_price']) / $sp['base_price']) * 100;
                                            $diffClass = $diff > 5 ? 'text-success' : ($diff < -5 ? 'text-danger' : 'text-muted');
                                            ?>
                                            <span class="price-diff <?= $diffClass ?>">
                                                <?= $diff >= 0 ? '+' : '' ?><?= number_format($diff, 1) ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $trendIcon = match($sp['price_trend']) {
                                                'rising' => '<span class="trend-up" title="Steigend">&#9650;</span>',
                                                'falling' => '<span class="trend-down" title="Fallend">&#9660;</span>',
                                                default => '<span class="trend-stable" title="Stabil">&#9644;</span>'
                                            };
                                            echo $trendIcon;
                                            ?>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/salespoints/<?= $sp['selling_point_id'] ?>" class="btn btn-sm btn-primary">
                                                Verkaufen
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
.salespoints-search-page {
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

.selling-point-count {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.price-range {
    font-weight: 600;
    color: var(--color-primary);
}

/* Selling Points Table */
.result-selling-points {
    overflow-x: auto;
}

.selling-points-table {
    width: 100%;
    border-collapse: collapse;
}

.selling-points-table th,
.selling-points-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--color-border);
}

.selling-points-table th {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.selling-points-table tr:last-child td {
    border-bottom: none;
}

.selling-points-table tr.best-price {
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

.trend-up { color: var(--color-success); font-size: 0.75rem; }
.trend-down { color: var(--color-danger); font-size: 0.75rem; }
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

    .selling-points-table {
        min-width: 500px;
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
