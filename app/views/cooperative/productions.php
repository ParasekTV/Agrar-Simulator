<div class="coop-productions-page">
    <div class="page-header">
        <h1>Genossenschafts-Produktionen</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/cooperative" class="btn btn-outline">Zurück</a>
        </div>
    </div>

    <!-- Kasse anzeigen -->
    <div class="treasury-info">
        <strong>Genossenschaftskasse:</strong>
        <span class="treasury-amount"><?= number_format($coopDetails['treasury'] ?? 0, 0, ',', '.') ?> T</span>
    </div>

    <?php if (empty($coopProductions)): ?>
        <div class="empty-state">
            <span class="empty-icon">&#127981;</span>
            <h3>Keine Produktionen vorhanden</h3>
            <p>Kaufe Produktionen für die Genossenschaft um gemeinsam zu produzieren.</p>
        </div>
    <?php else: ?>
        <div class="productions-grid">
            <?php foreach ($coopProductions as $prod): ?>
                <div class="production-card <?= $prod['is_running'] ? 'running' : '' ?>">
                    <div class="production-header">
                        <?php if (!empty($prod['icon'])): ?>
                            <img src="<?= BASE_URL ?>/img/<?= htmlspecialchars($prod['icon']) ?>"
                                 class="production-icon" alt="" onerror="this.style.display='none'">
                        <?php endif; ?>
                        <h4><?= htmlspecialchars($prod['name_de'] ?? $prod['name']) ?></h4>
                    </div>

                    <p class="production-desc"><?= htmlspecialchars($prod['description'] ?? '') ?></p>

                    <div class="production-stats">
                        <span class="stat">
                            <strong>Zyklen:</strong> <?= $prod['cycles_completed'] ?>
                        </span>
                        <span class="stat">
                            <strong>Effizienz:</strong> <?= number_format($prod['current_efficiency'], 0) ?>%
                        </span>
                    </div>

                    <div class="production-status">
                        <?php if ($prod['is_running']): ?>
                            <span class="badge badge-success">Läuft</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Gestoppt</span>
                        <?php endif; ?>
                    </div>

                    <div class="production-actions">
                        <form action="<?= BASE_URL ?>/cooperative/productions/toggle" method="POST" class="inline-form">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="coop_production_id" value="<?= $prod['id'] ?>">
                            <button type="submit" class="btn <?= $prod['is_running'] ? 'btn-warning' : 'btn-success' ?> btn-sm">
                                <?= $prod['is_running'] ? 'Stoppen' : 'Starten' ?>
                            </button>
                        </form>
                    </div>

                    <div class="production-meta">
                        <small>Gekauft von <?= htmlspecialchars($prod['purchased_by_name'] ?? 'Unbekannt') ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($canManage && !empty($availableProductions)): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Neue Produktion kaufen</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    Produktionen benötigen ggf. abgeschlossene Genossenschafts-Forschungen.
                </p>
                <div class="available-productions-grid">
                    <?php foreach ($availableProductions as $prod): ?>
                        <div class="available-production-card">
                            <div class="prod-header">
                                <?php if (!empty($prod['icon'])): ?>
                                    <img src="<?= BASE_URL ?>/img/<?= htmlspecialchars($prod['icon']) ?>"
                                         class="prod-icon" alt="" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <div>
                                    <h5><?= htmlspecialchars($prod['name_de'] ?? $prod['name']) ?></h5>
                                    <span class="prod-category"><?= htmlspecialchars($prod['category']) ?></span>
                                </div>
                            </div>
                            <p class="prod-desc"><?= htmlspecialchars($prod['description'] ?? '') ?></p>
                            <div class="prod-price">
                                <strong><?= number_format($prod['building_cost'], 0, ',', '.') ?> T</strong>
                            </div>
                            <?php if (!empty($prod['research_name']) && empty($prod['research_completed'])): ?>
                                <div class="prod-locked">
                                    <span class="badge badge-warning">Forschung erforderlich: <?= htmlspecialchars($prod['research_name']) ?></span>
                                </div>
                            <?php else: ?>
                                <form action="<?= BASE_URL ?>/cooperative/productions/buy" method="POST" class="mt-2">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="production_id" value="<?= $prod['id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm"
                                            <?= ($coopDetails['treasury'] ?? 0) < $prod['building_cost'] ? 'disabled' : '' ?>>
                                        Kaufen
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php elseif ($canManage): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Neue Produktion kaufen</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Alle verfügbaren Produktionen wurden bereits gekauft.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.treasury-info {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
}

.treasury-amount {
    font-weight: 700;
    color: var(--color-success);
}

.productions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.production-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.25rem;
    transition: box-shadow 0.2s;
}

.production-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.production-card.running {
    border-left: 4px solid var(--color-success);
}

.production-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.production-icon {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.production-header h4 {
    margin: 0;
}

.production-desc {
    color: var(--color-text-secondary);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.production-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.75rem;
    font-size: 0.85rem;
}

.production-status {
    margin-bottom: 1rem;
}

.production-actions {
    margin-bottom: 0.75rem;
}

.production-meta {
    color: var(--color-text-secondary);
    font-size: 0.8rem;
    border-top: 1px solid var(--color-border);
    padding-top: 0.75rem;
}

.inline-form {
    display: inline-block;
}

/* Verfügbare Produktionen */
.available-productions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

.available-production-card {
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: 1rem;
}

.available-production-card .prod-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}

.available-production-card .prod-icon {
    width: 36px;
    height: 36px;
    object-fit: contain;
}

.available-production-card h5 {
    margin: 0;
    font-size: 1rem;
}

.available-production-card .prod-category {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.available-production-card .prod-desc {
    font-size: 0.85rem;
    color: var(--color-text-secondary);
    margin-bottom: 0.5rem;
}

.available-production-card .prod-price {
    font-size: 1.1rem;
    color: var(--color-primary);
    margin-bottom: 0.5rem;
}

.available-production-card .prod-locked {
    margin-top: 0.5rem;
}
</style>
