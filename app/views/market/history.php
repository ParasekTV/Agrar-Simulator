<div class="market-history-page">
    <div class="page-header">
        <h1>Handelshistorie</h1>
        <a href="<?= BASE_URL ?>/market" class="btn btn-outline">Zurueck zum Marktplatz</a>
    </div>

    <div class="history-tabs">
        <button class="tab active" onclick="showTab('purchases')">Einkaeufe</button>
        <button class="tab" onclick="showTab('sales')">Verkaeufe</button>
    </div>

    <!-- Einkaeufe -->
    <div class="history-section" id="purchases-section">
        <h3>Meine Einkaeufe</h3>
        <?php if (empty($purchases)): ?>
            <div class="empty-state">
                <p class="text-muted">Noch keine Einkaeufe getaetigt.</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Artikel</th>
                        <th>Verkaeufer</th>
                        <th>Menge</th>
                        <th>Gesamtpreis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $purchase): ?>
                        <tr>
                            <td><?= date('d.m.Y H:i', strtotime($purchase['transaction_date'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($purchase['item_name']) ?></strong>
                                <br>
                                <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $purchase['item_type'])) ?></small>
                            </td>
                            <td><?= htmlspecialchars($purchase['seller_name']) ?></td>
                            <td><?= number_format($purchase['quantity']) ?></td>
                            <td class="text-danger">-<?= number_format($purchase['total_price'], 0, ',', '.') ?> T</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Verkaeufe -->
    <div class="history-section" id="sales-section" style="display: none;">
        <h3>Meine Verkaeufe</h3>
        <?php if (empty($sales)): ?>
            <div class="empty-state">
                <p class="text-muted">Noch keine Verkaeufe getaetigt.</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Artikel</th>
                        <th>Kaeufer</th>
                        <th>Menge</th>
                        <th>Einnahmen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?= date('d.m.Y H:i', strtotime($sale['transaction_date'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($sale['item_name']) ?></strong>
                                <br>
                                <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $sale['item_type'])) ?></small>
                            </td>
                            <td><?= htmlspecialchars($sale['buyer_name']) ?></td>
                            <td><?= number_format($sale['quantity']) ?></td>
                            <td class="text-success">+<?= number_format($sale['total_price'], 0, ',', '.') ?> T</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
function showTab(tab) {
    // Tabs
    document.querySelectorAll('.history-tabs .tab').forEach(t => t.classList.remove('active'));
    event.target.classList.add('active');

    // Sections
    document.getElementById('purchases-section').style.display = tab === 'purchases' ? 'block' : 'none';
    document.getElementById('sales-section').style.display = tab === 'sales' ? 'block' : 'none';
}
</script>

<style>
.history-tabs {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 1.5rem;
}
.history-tabs .tab {
    padding: 0.75rem 1.5rem;
    background: white;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    font-size: 1rem;
    transition: var(--transition);
}
.history-tabs .tab.active {
    background: var(--color-primary);
    color: white;
}
.history-section {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}
.history-section h3 {
    margin-bottom: 1rem;
}
</style>
