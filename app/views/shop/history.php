<div class="history-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/shop" class="back-link">&larr; Zurück zum Shop</a>
        <h1>Einkaufshistorie</h1>
    </div>

    <?php if (empty($history)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128722;</div>
            <h3>Noch keine Einkäufe</h3>
            <p>Du hast noch keine Produkte bei Händlern gekauft.</p>
            <a href="<?= BASE_URL ?>/shop" class="btn btn-primary">Zum Shop</a>
        </div>
    <?php else: ?>
        <div class="history-stats">
            <?php
            $totalSpent = array_sum(array_column($history, 'total_amount'));
            $totalItems = array_sum(array_column($history, 'quantity'));
            ?>
            <div class="stat-card">
                <span class="stat-label">Gesamt ausgegeben</span>
                <span class="stat-value"><?= number_format($totalSpent, 0, ',', '.') ?> T</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Artikel gekauft</span>
                <span class="stat-value"><?= number_format($totalItems) ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Transaktionen</span>
                <span class="stat-value"><?= count($history) ?></span>
            </div>
        </div>

        <div class="history-table">
            <table>
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Produkt</th>
                        <th>Händler</th>
                        <th>Menge</th>
                        <th>Stückpreis</th>
                        <th>Gesamt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $entry): ?>
                        <tr>
                            <td class="date-cell">
                                <?= date('d.m.Y', strtotime($entry['created_at'])) ?>
                                <span class="time"><?= date('H:i', strtotime($entry['created_at'])) ?></span>
                            </td>
                            <td>
                                <div class="product-cell">
                                    <?php if (!empty($entry['icon'])): ?>
                                        <img src="<?= BASE_URL ?>/img/products/<?= htmlspecialchars($entry['icon']) ?>"
                                             alt="" class="product-thumb"
                                             onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($entry['product_name']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($entry['dealer_name']) ?></td>
                            <td><?= number_format($entry['quantity']) ?></td>
                            <td><?= number_format($entry['price_per_unit'], 0, ',', '.') ?> T</td>
                            <td class="total-cell">
                                <span class="total-amount">-<?= number_format($entry['total_amount'], 0, ',', '.') ?> T</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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

.empty-state h3 {
    margin: 0 0 0.5rem;
}

.empty-state p {
    color: var(--color-text-secondary);
    margin: 0 0 1.5rem;
}

.history-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
}

.stat-label {
    display: block;
    font-size: 0.8rem;
    color: var(--color-text-secondary);
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-text);
}

.history-table {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    overflow: hidden;
}

.history-table table {
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
    text-align: right;
}

.total-amount {
    color: var(--color-danger);
    font-weight: 600;
}

@media (max-width: 768px) {
    .history-table {
        overflow-x: auto;
    }

    .history-table table {
        min-width: 600px;
    }
}
</style>
