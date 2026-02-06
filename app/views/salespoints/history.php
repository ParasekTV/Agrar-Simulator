<div class="history-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/salespoints" class="back-link">&larr; Zurück zu Verkaufsstellen</a>
        <h1>Verkaufshistorie</h1>
    </div>

    <?php if (empty($history)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128203;</div>
            <h3>Noch keine Verkäufe</h3>
            <p>Sobald du Produkte verkaufst, erscheinen sie hier.</p>
            <a href="<?= BASE_URL ?>/salespoints" class="btn btn-primary">Produkte verkaufen</a>
        </div>
    <?php else: ?>
        <div class="history-table-wrapper">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Produkt</th>
                        <th>Menge</th>
                        <th>Preis/Stück</th>
                        <th>Gesamt</th>
                        <th>Verkaufsstelle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $sale): ?>
                        <tr>
                            <td class="date-cell">
                                <?= date('d.m.Y', strtotime($sale['created_at'])) ?>
                                <span class="time"><?= date('H:i', strtotime($sale['created_at'])) ?></span>
                            </td>
                            <td class="product-cell">
                                <?php if (!empty($sale['icon'])): ?>
                                    <img src="<?= BASE_URL ?>/img/products/<?= htmlspecialchars($sale['icon']) ?>"
                                         alt="" class="product-thumb"
                                         onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                                <?php endif; ?>
                                <span><?= htmlspecialchars($sale['product_name']) ?></span>
                            </td>
                            <td><?= number_format($sale['quantity']) ?></td>
                            <td><?= number_format($sale['price_per_unit'], 0, ',', '.') ?> T</td>
                            <td class="total-cell"><?= number_format($sale['total_amount'], 0, ',', '.') ?> T</td>
                            <td><?= htmlspecialchars($sale['selling_point_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        $totalSales = 0;
        $totalItems = 0;
        foreach ($history as $sale) {
            $totalSales += $sale['total_amount'];
            $totalItems += $sale['quantity'];
        }
        ?>

        <div class="history-summary">
            <div class="summary-card">
                <span class="summary-label">Gesamtumsatz:</span>
                <span class="summary-value"><?= number_format($totalSales, 0, ',', '.') ?> T</span>
            </div>
            <div class="summary-card">
                <span class="summary-label">Verkaufte Produkte:</span>
                <span class="summary-value"><?= number_format($totalItems) ?></span>
            </div>
            <div class="summary-card">
                <span class="summary-label">Transaktionen:</span>
                <span class="summary-value"><?= count($history) ?></span>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.history-page {
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
    margin-bottom: 2rem;
}

.page-header h1 {
    margin: 0.5rem 0 0;
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

.history-table-wrapper {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
}

.history-table th,
.history-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--color-border);
}

.history-table th {
    background: var(--color-bg-secondary);
    font-weight: 600;
    font-size: 0.875rem;
}

.history-table tr:last-child td {
    border-bottom: none;
}

.history-table tr:hover {
    background: var(--color-bg-secondary);
}

.date-cell {
    white-space: nowrap;
}

.date-cell .time {
    display: block;
    font-size: 0.75rem;
    color: var(--color-text-secondary);
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

.total-cell {
    font-weight: 600;
    color: var(--color-success);
}

.history-summary {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.summary-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: 1rem 1.5rem;
    flex: 1;
    min-width: 150px;
}

.summary-label {
    display: block;
    font-size: 0.8rem;
    color: var(--color-text-secondary);
    margin-bottom: 0.25rem;
}

.summary-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-primary);
}

@media (max-width: 768px) {
    .history-table-wrapper {
        overflow-x: auto;
    }

    .history-table {
        min-width: 600px;
    }
}
</style>
