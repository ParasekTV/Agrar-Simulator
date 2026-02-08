<div class="production-logs-page">
    <div class="page-header">
        <h1>Produktions-Historie</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/productions" class="btn btn-outline">Zurück zu Produktionen</a>
        </div>
    </div>

    <?php if (empty($logs)): ?>
        <div class="empty-state">
            <p>Noch keine Produktionszyklen abgeschlossen.</p>
            <p>Starte eine kontinuierliche Produktion um automatisch Rohstoffe zu verarbeiten.</p>
        </div>
    <?php else: ?>
        <div class="logs-table-container">
            <table class="logs-table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Produktion</th>
                        <th>Zyklus</th>
                        <th>Effizienz</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= date('d.m.Y H:i', strtotime($log['created_at'])) ?></td>
                            <td><strong><?= htmlspecialchars($log['production_name']) ?></strong></td>
                            <td>#<?= $log['cycle_number'] ?></td>
                            <td>
                                <span class="efficiency-badge efficiency-<?= $log['efficiency'] >= 80 ? 'high' : ($log['efficiency'] >= 50 ? 'medium' : 'low') ?>">
                                    <?= number_format($log['efficiency'], 1) ?>%
                                </span>
                            </td>
                            <td>
                                <?php
                                $inputs = json_decode($log['inputs_used'], true) ?: [];
                                $outputs = json_decode($log['outputs_produced'], true) ?: [];
                                $inputCount = array_sum($inputs);
                                $outputCount = array_sum($outputs);
                                ?>
                                <span class="log-detail" title="<?= $inputCount ?> Inputs verbraucht, <?= $outputCount ?> Outputs produziert">
                                    -<?= $inputCount ?> / +<?= $outputCount ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.production-logs-page {
    padding: 1rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.empty-state {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 3rem;
    text-align: center;
    color: var(--color-text-secondary);
}

.logs-table-container {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    overflow: hidden;
}

.logs-table {
    width: 100%;
    border-collapse: collapse;
}

.logs-table th,
.logs-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--color-border);
}

.logs-table th {
    background: var(--color-bg-secondary);
    font-weight: 600;
    font-size: 0.875rem;
}

.logs-table tr:last-child td {
    border-bottom: none;
}

.logs-table tr:hover {
    background: var(--color-bg-secondary);
}

.efficiency-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.efficiency-high {
    background: #d4edda;
    color: #155724;
}

.efficiency-medium {
    background: #fff3cd;
    color: #856404;
}

.efficiency-low {
    background: #f8d7da;
    color: #721c24;
}

.log-detail {
    font-size: 0.85rem;
    color: var(--color-text-secondary);
    cursor: help;
}

@media (max-width: 768px) {
    .logs-table-container {
        overflow-x: auto;
    }

    .logs-table {
        min-width: 500px;
    }
}
</style>
