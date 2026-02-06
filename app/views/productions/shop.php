<div class="productions-shop-page">
    <div class="page-header">
        <h1>Produktionen kaufen</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/productions" class="btn btn-outline">Zurück zu meinen Produktionen</a>
        </div>
    </div>

    <div class="shop-info">
        <div class="info-card">
            <span class="info-icon">&#128176;</span>
            <span class="info-value"><?= number_format($farm['money'], 0, ',', '.') ?> T</span>
            <span class="info-label">Verfügbar</span>
        </div>
        <div class="info-card">
            <span class="info-icon">&#127942;</span>
            <span class="info-value">Level <?= $farm['level'] ?></span>
            <span class="info-label">Dein Level</span>
        </div>
    </div>

    <?php
    // Gruppiere nach Kategorie
    $grouped = [];
    foreach ($availableProductions as $production) {
        $cat = $production['category'] ?? 'allgemein';
        if (!isset($grouped[$cat])) {
            $grouped[$cat] = [];
        }
        $grouped[$cat][] = $production;
    }
    ?>

    <?php foreach ($grouped as $category => $productions): ?>
        <div class="shop-category">
            <h2 class="category-title"><?= htmlspecialchars($categories[$category] ?? ucfirst($category)) ?></h2>

            <div class="shop-grid">
                <?php foreach ($productions as $production): ?>
                    <?php
                    $owned = $production['owned'] > 0;
                    $canAfford = $farm['money'] >= $production['building_cost'];
                    $levelOk = $farm['level'] >= $production['required_level'];
                    $canBuy = !$owned && $canAfford && $levelOk;
                    ?>

                    <div class="shop-card <?= $owned ? 'owned' : '' ?> <?= !$canBuy && !$owned ? 'locked' : '' ?>">
                        <div class="shop-card-header">
                            <div class="production-icon">
                                <?php if ($production['icon']): ?>
                                    <img src="<?= BASE_URL ?>/img/productions/<?= htmlspecialchars($production['icon']) ?>"
                                         alt="<?= htmlspecialchars($production['name_de']) ?>"
                                         onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                                <?php else: ?>
                                    <span class="icon-placeholder">&#127981;</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($owned): ?>
                                <span class="owned-badge">Besitzt</span>
                            <?php endif; ?>
                        </div>

                        <div class="shop-card-body">
                            <h3><?= htmlspecialchars($production['name_de']) ?></h3>

                            <div class="production-requirements">
                                <div class="requirement <?= $levelOk ? 'met' : 'unmet' ?>">
                                    <span class="req-icon">&#127942;</span>
                                    <span>Level <?= $production['required_level'] ?></span>
                                </div>
                            </div>

                            <div class="production-costs">
                                <div class="cost-item">
                                    <span class="cost-label">Baukosten:</span>
                                    <span class="cost-value <?= !$canAfford ? 'text-danger' : '' ?>">
                                        <?= number_format($production['building_cost'], 0, ',', '.') ?> T
                                    </span>
                                </div>
                                <div class="cost-item">
                                    <span class="cost-label">Unterhalt:</span>
                                    <span class="cost-value">
                                        <?= number_format($production['maintenance_cost'], 0, ',', '.') ?> T/Tag
                                    </span>
                                </div>
                            </div>

                            <?php if ($production['description']): ?>
                                <p class="production-description"><?= htmlspecialchars($production['description']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="shop-card-footer">
                            <?php if ($owned): ?>
                                <span class="text-success">Bereits gekauft</span>
                            <?php elseif (!$levelOk): ?>
                                <span class="text-warning">Level <?= $production['required_level'] ?> benötigt</span>
                            <?php elseif (!$canAfford): ?>
                                <span class="text-danger">Nicht genug Geld</span>
                            <?php else: ?>
                                <form action="<?= BASE_URL ?>/productions/buy" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="production_id" value="<?= $production['id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        Kaufen
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.productions-shop-page {
    padding: 1rem;
}

.shop-info {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.info-card {
    background: var(--color-bg-secondary);
    padding: 1rem 1.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.info-icon {
    font-size: 1.5rem;
}

.info-value {
    font-size: 1.25rem;
    font-weight: 700;
}

.info-label {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.shop-category {
    margin-bottom: 2rem;
}

.category-title {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--color-border);
}

.shop-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.shop-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.shop-card:hover:not(.owned):not(.locked) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.shop-card.owned {
    border-color: var(--color-success);
    background: rgba(40, 167, 69, 0.05);
}

.shop-card.locked {
    opacity: 0.6;
}

.shop-card-header {
    position: relative;
    padding: 1.5rem;
    background: var(--color-bg-secondary);
    display: flex;
    justify-content: center;
}

.production-icon {
    width: 80px;
    height: 80px;
    border-radius: 12px;
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
    font-size: 2.5rem;
}

.owned-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: var(--color-success);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.shop-card-body {
    padding: 1rem;
}

.shop-card-body h3 {
    margin: 0 0 0.75rem;
    font-size: 1.1rem;
}

.production-requirements {
    margin-bottom: 0.75rem;
}

.requirement {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.requirement.met {
    background: rgba(40, 167, 69, 0.1);
    color: var(--color-success);
}

.requirement.unmet {
    background: rgba(220, 53, 69, 0.1);
    color: var(--color-danger);
}

.production-costs {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-bottom: 0.75rem;
}

.cost-item {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
}

.cost-label {
    color: var(--color-text-secondary);
}

.cost-value {
    font-weight: 600;
}

.production-description {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
    margin: 0;
    line-height: 1.4;
}

.shop-card-footer {
    padding: 1rem;
    background: var(--color-bg-secondary);
    border-top: 1px solid var(--color-border);
    text-align: center;
}

.btn-block {
    width: 100%;
}

.text-danger {
    color: var(--color-danger);
}

.text-warning {
    color: var(--color-warning);
}

.text-success {
    color: var(--color-success);
}
</style>
