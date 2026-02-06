<div class="productions-page">
    <div class="page-header">
        <h1>Meine Produktionen</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/productions/shop" class="btn btn-primary">Neue Produktion kaufen</a>
        </div>
    </div>

    <?php if (empty($productions)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#127981;</div>
            <h3>Noch keine Produktionen</h3>
            <p>Kaufe deine erste Produktion im Shop, um Produkte herzustellen.</p>
            <a href="<?= BASE_URL ?>/productions/shop" class="btn btn-primary">Zum Shop</a>
        </div>
    <?php else: ?>
        <!-- Produktions-Kategorien -->
        <?php
        $groupedProductions = [];
        foreach ($productions as $production) {
            $cat = $production['category'] ?? 'allgemein';
            if (!isset($groupedProductions[$cat])) {
                $groupedProductions[$cat] = [];
            }
            $groupedProductions[$cat][] = $production;
        }
        ?>

        <?php foreach ($groupedProductions as $category => $categoryProductions): ?>
            <div class="production-category">
                <h2 class="category-title"><?= htmlspecialchars($categories[$category] ?? ucfirst($category)) ?></h2>

                <div class="productions-grid">
                    <?php foreach ($categoryProductions as $production): ?>
                        <?php
                        $isActive = $production['is_active'];
                        $canProduce = $production['can_produce'];
                        $readyToCollect = $production['ready_to_collect'];
                        $isProducing = $production['last_production_at'] && !$readyToCollect;

                        // Status-Klassen
                        $statusClass = 'idle';
                        if (!$isActive) $statusClass = 'inactive';
                        elseif ($readyToCollect) $statusClass = 'ready';
                        elseif ($isProducing) $statusClass = 'producing';
                        ?>

                        <div class="production-card production-<?= $statusClass ?>" id="production-<?= $production['id'] ?>">
                            <div class="production-header">
                                <div class="production-icon">
                                    <?php if ($production['icon']): ?>
                                        <img src="<?= BASE_URL ?>/img/productions/<?= htmlspecialchars($production['icon']) ?>"
                                             alt="<?= htmlspecialchars($production['name_de']) ?>"
                                             onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                                    <?php else: ?>
                                        <span class="icon-placeholder">&#127981;</span>
                                    <?php endif; ?>
                                </div>
                                <div class="production-info">
                                    <h3><?= htmlspecialchars($production['name_de']) ?></h3>
                                    <span class="production-level">Level <?= $production['level'] ?></span>
                                </div>
                                <div class="production-toggle">
                                    <form action="<?= BASE_URL ?>/productions/toggle" method="POST" class="toggle-form">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="farm_production_id" value="<?= $production['id'] ?>">
                                        <button type="submit" class="toggle-btn <?= $isActive ? 'active' : '' ?>"
                                                title="<?= $isActive ? 'Deaktivieren' : 'Aktivieren' ?>">
                                            <span class="toggle-slider"></span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="production-content">
                                <!-- Status-Anzeige -->
                                <?php if (!$isActive): ?>
                                    <div class="production-status status-inactive">
                                        <span class="status-icon">&#9940;</span>
                                        <span>Deaktiviert</span>
                                    </div>
                                <?php elseif ($readyToCollect): ?>
                                    <div class="production-status status-ready">
                                        <span class="status-icon animate-bounce">&#9989;</span>
                                        <span>Bereit zum Abholen!</span>
                                    </div>
                                    <form action="<?= BASE_URL ?>/productions/collect" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="farm_production_id" value="<?= $production['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-block">Einsammeln</button>
                                    </form>
                                <?php elseif ($isProducing): ?>
                                    <div class="production-status status-producing">
                                        <span class="status-icon animate-spin">&#9881;</span>
                                        <span>Produziert...</span>
                                    </div>
                                    <?php
                                    $startTime = strtotime($production['last_production_at']);
                                    $endTime = $startTime + $production['production_time'];
                                    $remaining = max(0, $endTime - time());
                                    ?>
                                    <div class="timer-display">
                                        <span class="production-timer" data-end-time="<?= date('Y-m-d H:i:s', $endTime) ?>">
                                            <?= gmdate('H:i:s', $remaining) ?>
                                        </span>
                                    </div>
                                    <div class="progress-bar">
                                        <?php
                                        $elapsed = time() - $startTime;
                                        $progress = min(100, ($elapsed / $production['production_time']) * 100);
                                        ?>
                                        <div class="progress-bar-fill" style="width: <?= $progress ?>%"></div>
                                    </div>
                                <?php else: ?>
                                    <div class="production-status status-idle">
                                        <span class="status-icon">&#128260;</span>
                                        <span>Bereit</span>
                                    </div>
                                    <?php if ($canProduce): ?>
                                        <form action="<?= BASE_URL ?>/productions/start" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="farm_production_id" value="<?= $production['id'] ?>">
                                            <button type="submit" class="btn btn-primary btn-block">Produktion starten</button>
                                        </form>
                                    <?php else: ?>
                                        <p class="text-warning">Rohstoffe fehlen</p>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Inputs/Outputs -->
                                <div class="production-io">
                                    <?php if (!empty($production['inputs'])): ?>
                                        <div class="io-section">
                                            <h4>Benötigt:</h4>
                                            <ul class="io-list">
                                                <?php foreach ($production['inputs'] as $input): ?>
                                                    <li class="<?= $input['is_optional'] ? 'optional' : '' ?>">
                                                        <span class="io-quantity"><?= $input['quantity'] ?>x</span>
                                                        <span class="io-name"><?= htmlspecialchars($input['name_de']) ?></span>
                                                        <?php if ($input['is_optional']): ?>
                                                            <span class="io-optional">(optional)</span>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($production['outputs'])): ?>
                                        <div class="io-section">
                                            <h4>Produziert:</h4>
                                            <ul class="io-list io-outputs">
                                                <?php foreach ($production['outputs'] as $output): ?>
                                                    <li>
                                                        <span class="io-quantity"><?= $output['quantity'] ?>x</span>
                                                        <span class="io-name"><?= htmlspecialchars($output['name_de']) ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="production-footer">
                                <span class="stat">
                                    <span class="stat-label">Produziert:</span>
                                    <span class="stat-value"><?= number_format($production['total_produced']) ?>x</span>
                                </span>
                                <a href="<?= BASE_URL ?>/productions/<?= $production['id'] ?>" class="btn btn-sm btn-outline">
                                    Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
// Timer-Update für laufende Produktionen
function updateProductionTimers() {
    document.querySelectorAll('.production-timer').forEach(function(timer) {
        const endTime = new Date(timer.dataset.endTime).getTime();
        const now = Date.now();
        const remaining = Math.max(0, Math.floor((endTime - now) / 1000));

        if (remaining <= 0) {
            // Seite neu laden wenn Timer abläuft
            location.reload();
            return;
        }

        const hours = Math.floor(remaining / 3600);
        const minutes = Math.floor((remaining % 3600) / 60);
        const seconds = remaining % 60;

        timer.textContent = [hours, minutes, seconds]
            .map(v => v.toString().padStart(2, '0'))
            .join(':');
    });
}

// Alle Sekunde aktualisieren
setInterval(updateProductionTimers, 1000);
updateProductionTimers();
</script>

<style>
.productions-page {
    padding: 1rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
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

.production-category {
    margin-bottom: 2rem;
}

.category-title {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--color-border);
}

.productions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.production-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.production-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.production-inactive {
    opacity: 0.7;
}

.production-ready {
    border-color: var(--color-success);
    box-shadow: 0 0 10px rgba(40, 167, 69, 0.3);
}

.production-producing {
    border-color: var(--color-primary);
}

.production-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--color-bg-secondary);
}

.production-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    overflow: hidden;
    background: var(--color-bg);
    display: flex;
    align-items: center;
    justify-content: center;
}

.production-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.icon-placeholder {
    font-size: 1.5rem;
}

.production-info {
    flex: 1;
}

.production-info h3 {
    margin: 0;
    font-size: 1rem;
}

.production-level {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
}

/* Toggle Switch */
.toggle-btn {
    width: 50px;
    height: 26px;
    border-radius: 13px;
    background: var(--color-border);
    border: none;
    cursor: pointer;
    position: relative;
    transition: background 0.3s;
}

.toggle-btn.active {
    background: var(--color-success);
}

.toggle-slider {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: white;
    transition: transform 0.3s;
}

.toggle-btn.active .toggle-slider {
    transform: translateX(24px);
}

.production-content {
    padding: 1rem;
}

.production-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.status-icon {
    font-size: 1.25rem;
}

.status-ready {
    color: var(--color-success);
}

.status-producing {
    color: var(--color-primary);
}

.status-inactive {
    color: var(--color-text-secondary);
}

.timer-display {
    text-align: center;
    font-size: 1.5rem;
    font-weight: bold;
    font-family: monospace;
    margin: 0.5rem 0;
}

.production-io {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--color-border);
}

.io-section {
    margin-bottom: 0.75rem;
}

.io-section h4 {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--color-text-secondary);
    margin-bottom: 0.25rem;
}

.io-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.io-list li {
    background: var(--color-bg-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
}

.io-list li.optional {
    opacity: 0.7;
}

.io-quantity {
    font-weight: 600;
    color: var(--color-primary);
}

.io-optional {
    font-size: 0.7rem;
    color: var(--color-text-secondary);
}

.io-outputs li {
    background: rgba(40, 167, 69, 0.1);
}

.production-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: var(--color-bg-secondary);
    border-top: 1px solid var(--color-border);
}

.stat {
    font-size: 0.875rem;
}

.productions-page .stat-label {
    color: var(--color-text-secondary);
}

.productions-page .stat-value {
    font-weight: 600;
}

.btn-block {
    width: 100%;
}

/* Animationen */
.animate-bounce {
    animation: bounce 1s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.animate-spin {
    animation: spin 2s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Progress Bar */
.progress-bar {
    height: 6px;
    background: var(--color-bg-secondary);
    border-radius: 3px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.progress-bar-fill {
    height: 100%;
    background: var(--color-primary);
    transition: width 0.3s ease;
}
</style>
