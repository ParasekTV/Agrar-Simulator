<div class="production-detail-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/productions" class="back-link">&larr; Zurück zu Produktionen</a>
        <h1><?= htmlspecialchars($production['name_de']) ?></h1>
    </div>

    <div class="production-detail-grid">
        <!-- Hauptinfo -->
        <div class="detail-card detail-main">
            <div class="detail-header">
                <div class="production-icon-large">
                    <?php if ($production['icon']): ?>
                        <img src="<?= BASE_URL ?>/img/productions/<?= htmlspecialchars($production['icon']) ?>"
                             alt="<?= htmlspecialchars($production['name_de']) ?>"
                             onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                    <?php else: ?>
                        <span class="icon-placeholder">&#127981;</span>
                    <?php endif; ?>
                </div>
                <div class="production-meta">
                    <span class="badge badge-<?= $production['is_active'] ? 'success' : 'secondary' ?>">
                        <?= $production['is_active'] ? 'Aktiv' : 'Inaktiv' ?>
                    </span>
                    <span class="category-badge"><?= htmlspecialchars($production['category']) ?></span>
                </div>
            </div>

            <?php if ($production['description']): ?>
                <p class="production-description"><?= htmlspecialchars($production['description']) ?></p>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-value"><?= number_format($production['total_produced']) ?></span>
                    <span class="stat-label">Gesamt produziert</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= $production['level'] ?></span>
                    <span class="stat-label">Gebäude-Level</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= gmdate('H:i:s', $production['production_time']) ?></span>
                    <span class="stat-label">Produktionszeit</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= number_format($production['maintenance_cost'], 0, ',', '.') ?> T</span>
                    <span class="stat-label">Unterhalt/Tag</span>
                </div>
            </div>

            <!-- Steuerung -->
            <div class="control-section">
                <h3>Steuerung</h3>
                <div class="control-buttons">
                    <form action="<?= BASE_URL ?>/productions/toggle" method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="farm_production_id" value="<?= $production['id'] ?>">
                        <input type="hidden" name="redirect" value="/productions/<?= $production['id'] ?>">
                        <button type="submit" class="btn <?= $production['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                            <?= $production['is_active'] ? 'Deaktivieren' : 'Aktivieren' ?>
                        </button>
                    </form>

                    <?php if ($production['ready_to_collect']): ?>
                        <form action="<?= BASE_URL ?>/productions/collect" method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="farm_production_id" value="<?= $production['id'] ?>">
                            <input type="hidden" name="redirect" value="/productions/<?= $production['id'] ?>">
                            <button type="submit" class="btn btn-success">Produkte einsammeln</button>
                        </form>
                    <?php elseif (!$production['last_production_at'] && $production['is_active']): ?>
                        <?php if ($production['can_produce']): ?>
                            <form action="<?= BASE_URL ?>/productions/start" method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="farm_production_id" value="<?= $production['id'] ?>">
                                <input type="hidden" name="redirect" value="/productions/<?= $production['id'] ?>">
                                <button type="submit" class="btn btn-primary">Produktion starten</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-outline" disabled>Rohstoffe fehlen</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if ($production['last_production_at'] && !$production['ready_to_collect']): ?>
                    <?php
                    $startTime = strtotime($production['last_production_at']);
                    $endTime = $startTime + $production['production_time'];
                    $remaining = max(0, $endTime - time());
                    $progress = min(100, ((time() - $startTime) / $production['production_time']) * 100);
                    ?>
                    <div class="production-progress">
                        <div class="progress-info">
                            <span>Produktion läuft...</span>
                            <span class="production-timer" data-end-time="<?= date('Y-m-d H:i:s', $endTime) ?>">
                                <?= gmdate('H:i:s', $remaining) ?>
                            </span>
                        </div>
                        <div class="progress-bar progress-bar-lg">
                            <div class="progress-bar-fill" style="width: <?= $progress ?>%"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Inputs -->
        <div class="detail-card">
            <h3>Benötigte Rohstoffe</h3>
            <?php if (empty($production['inputs'])): ?>
                <p class="text-muted">Diese Produktion benötigt keine Rohstoffe.</p>
            <?php else: ?>
                <div class="io-list-detailed">
                    <?php foreach ($production['inputs'] as $input): ?>
                        <?php
                        $inStock = $inputStock[$input['product_id']] ?? 0;
                        $hasEnough = $inStock >= $input['quantity'];
                        ?>
                        <div class="io-item <?= $hasEnough ? 'available' : 'missing' ?>">
                            <div class="io-icon">
                                <?php if ($input['icon']): ?>
                                    <img src="<?= BASE_URL ?>/img/products/<?= htmlspecialchars($input['icon']) ?>"
                                         alt="" onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                                <?php else: ?>
                                    <span>&#128230;</span>
                                <?php endif; ?>
                            </div>
                            <div class="io-info">
                                <span class="io-name"><?= htmlspecialchars($input['name_de']) ?></span>
                                <?php if ($input['is_optional']): ?>
                                    <span class="io-optional-badge">Optional</span>
                                <?php endif; ?>
                            </div>
                            <div class="io-quantity">
                                <span class="needed"><?= $input['quantity'] ?> benötigt</span>
                                <span class="stock <?= $hasEnough ? 'text-success' : 'text-danger' ?>">
                                    <?= $inStock ?> im Lager
                                </span>
                            </div>
                            <div class="io-status">
                                <?php if ($hasEnough): ?>
                                    <span class="status-ok">&#10004;</span>
                                <?php elseif ($input['is_optional']): ?>
                                    <span class="status-optional">&#8212;</span>
                                <?php else: ?>
                                    <span class="status-missing">&#10008;</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="io-summary">
                    <?php if ($production['can_produce']): ?>
                        <span class="text-success">&#10004; Alle benötigten Rohstoffe vorhanden</span>
                    <?php else: ?>
                        <span class="text-danger">&#10008; Es fehlen Rohstoffe</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Outputs -->
        <div class="detail-card">
            <h3>Produzierte Waren</h3>
            <?php if (empty($production['outputs'])): ?>
                <p class="text-muted">Diese Produktion hat keine Outputs definiert.</p>
            <?php else: ?>
                <div class="io-list-detailed">
                    <?php foreach ($production['outputs'] as $output): ?>
                        <div class="io-item output-item">
                            <div class="io-icon">
                                <?php if ($output['icon']): ?>
                                    <img src="<?= BASE_URL ?>/img/products/<?= htmlspecialchars($output['icon']) ?>"
                                         alt="" onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                                <?php else: ?>
                                    <span>&#128230;</span>
                                <?php endif; ?>
                            </div>
                            <div class="io-info">
                                <span class="io-name"><?= htmlspecialchars($output['name_de']) ?></span>
                                <span class="io-category"><?= htmlspecialchars($output['product_category']) ?></span>
                            </div>
                            <div class="io-quantity">
                                <span class="amount"><?= $output['quantity'] ?>x pro Zyklus</span>
                                <span class="price">~<?= number_format($output['base_price'], 0, ',', '.') ?> T/Stück</span>
                            </div>
                            <a href="<?= BASE_URL ?>/salespoints/compare/<?= $output['product_id'] ?>" class="btn btn-sm btn-outline">
                                Preise
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php
                $totalValue = 0;
                foreach ($production['outputs'] as $output) {
                    $totalValue += $output['quantity'] * $output['base_price'];
                }
                ?>
                <div class="io-summary">
                    <span>Geschätzter Wert pro Zyklus: <strong><?= number_format($totalValue, 0, ',', '.') ?> T</strong></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateTimer() {
    const timer = document.querySelector('.production-timer');
    if (!timer) return;

    const endTime = new Date(timer.dataset.endTime).getTime();
    const now = Date.now();
    const remaining = Math.max(0, Math.floor((endTime - now) / 1000));

    if (remaining <= 0) {
        location.reload();
        return;
    }

    const hours = Math.floor(remaining / 3600);
    const minutes = Math.floor((remaining % 3600) / 60);
    const seconds = remaining % 60;

    timer.textContent = [hours, minutes, seconds]
        .map(v => v.toString().padStart(2, '0'))
        .join(':');
}

setInterval(updateTimer, 1000);
updateTimer();
</script>

<style>
.production-detail-page {
    padding: 1rem;
    max-width: 1200px;
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
    margin-top: 0.5rem;
}

.production-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.detail-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
}

.detail-main {
    grid-column: 1 / -1;
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.production-icon-large {
    width: 100px;
    height: 100px;
    border-radius: 12px;
    overflow: hidden;
    background: var(--color-bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.production-icon-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.production-icon-large .icon-placeholder {
    font-size: 3rem;
}

.production-meta {
    display: flex;
    gap: 0.5rem;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background: var(--color-success);
    color: white;
}

.badge-secondary {
    background: var(--color-border);
    color: var(--color-text);
}

.category-badge {
    background: var(--color-bg-secondary);
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.75rem;
}

.production-description {
    color: var(--color-text-secondary);
    margin-bottom: 1.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.production-detail-page .stat-item {
    text-align: center;
    padding: 1rem;
    background: var(--color-bg-secondary);
    border-radius: 8px;
}

.production-detail-page .stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-primary);
}

.production-detail-page .stat-label {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
}

.control-section h3 {
    margin-bottom: 1rem;
}

.control-buttons {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.production-progress {
    margin-top: 1rem;
    padding: 1rem;
    background: var(--color-bg-secondary);
    border-radius: 8px;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.production-timer {
    font-family: monospace;
    font-weight: bold;
}

.progress-bar-lg {
    height: 10px;
}

/* IO List Detailed */
.io-list-detailed {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.io-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: var(--color-bg-secondary);
    border-radius: 8px;
}

.io-item.missing {
    border-left: 3px solid var(--color-danger);
}

.io-item.available {
    border-left: 3px solid var(--color-success);
}

.io-item.output-item {
    border-left: 3px solid var(--color-primary);
}

.io-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    overflow: hidden;
    background: var(--color-bg);
    display: flex;
    align-items: center;
    justify-content: center;
}

.io-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.io-info {
    flex: 1;
}

.io-name {
    display: block;
    font-weight: 600;
}

.io-category {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
}

.io-optional-badge {
    font-size: 0.7rem;
    background: var(--color-border);
    padding: 0.1rem 0.4rem;
    border-radius: 3px;
}

.io-quantity {
    text-align: right;
}

.io-quantity span {
    display: block;
    font-size: 0.875rem;
}

.io-quantity .stock,
.io-quantity .price {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
}

.io-status {
    width: 24px;
    text-align: center;
    font-size: 1.25rem;
}

.status-ok {
    color: var(--color-success);
}

.status-missing {
    color: var(--color-danger);
}

.status-optional {
    color: var(--color-text-secondary);
}

.io-summary {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--color-border);
    text-align: center;
}

.text-success {
    color: var(--color-success);
}

.text-danger {
    color: var(--color-danger);
}

.text-muted {
    color: var(--color-text-secondary);
}

@media (max-width: 768px) {
    .production-detail-grid {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .control-buttons {
        flex-direction: column;
    }
}
</style>
