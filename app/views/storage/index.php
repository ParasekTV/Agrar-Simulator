<div class="storage-page">
    <div class="page-header">
        <h1>Lager</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/salespoints" class="btn btn-primary">Produkte verkaufen</a>
        </div>
    </div>

    <!-- Statistiken -->
    <div class="storage-stats">
        <div class="stat-card">
            <span class="stat-icon">&#128230;</span>
            <div class="stat-content">
                <span class="stat-value"><?= number_format($stats['total_items']) ?></span>
                <span class="stat-label">Produkte gesamt</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">&#128176;</span>
            <div class="stat-content">
                <span class="stat-value"><?= number_format($stats['total_value'], 0, ',', '.') ?> T</span>
                <span class="stat-label">Geschätzter Wert</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">&#127806;</span>
            <div class="stat-content">
                <span class="stat-value"><?= $stats['inventory_count'] ?></span>
                <span class="stat-label">Ernteprodukte</span>
            </div>
        </div>
        <div class="stat-card">
            <span class="stat-icon">&#127981;</span>
            <div class="stat-content">
                <span class="stat-value"><?= $stats['product_count'] ?></span>
                <span class="stat-label">Hergestellte Produkte</span>
            </div>
        </div>
    </div>

    <!-- Suchfeld -->
    <div class="search-section">
        <form action="<?= BASE_URL ?>/storage/search" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Produkt suchen..." class="search-input">
            <button type="submit" class="btn btn-outline">Suchen</button>
        </form>
    </div>

    <?php if (empty($storage) || (empty($storage['feldfrucht']) && empty($storage['tierprodukt']) && empty($storage['allgemein']))): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128230;</div>
            <h3>Lager ist leer</h3>
            <p>Ernte deine Felder oder produziere Waren, um dein Lager zu füllen.</p>
            <div class="empty-actions">
                <a href="<?= BASE_URL ?>/fields" class="btn btn-outline">Zu den Feldern</a>
                <a href="<?= BASE_URL ?>/productions" class="btn btn-outline">Zu den Produktionen</a>
            </div>
        </div>
    <?php else: ?>
        <!-- Kategorien -->
        <?php foreach ($storage as $categoryKey => $items): ?>
            <?php if (empty($items)) continue; ?>
            <div class="storage-category">
                <h2 class="category-title">
                    <?= htmlspecialchars($categories[$categoryKey] ?? ucfirst($categoryKey)) ?>
                    <span class="category-count">(<?= count($items) ?>)</span>
                </h2>

                <div class="storage-grid">
                    <?php foreach ($items as $item): ?>
                        <div class="storage-card">
                            <div class="item-icon">
                                <?php if (!empty($item['icon'])): ?>
                                    <img src="<?= BASE_URL ?>/img/products/<?= htmlspecialchars($item['icon']) ?>"
                                         alt="" onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                                <?php else: ?>
                                    <span class="icon-placeholder">&#128230;</span>
                                <?php endif; ?>
                            </div>
                            <div class="item-info">
                                <h3><?= htmlspecialchars($item['name']) ?></h3>
                                <span class="item-quantity"><?= number_format($item['quantity']) ?> Stück</span>
                            </div>
                            <?php if ($item['type'] === 'product' && !empty($item['base_price'])): ?>
                                <div class="item-value">
                                    <span class="value-label">Wert:</span>
                                    <span class="value-amount">
                                        <?= number_format($item['quantity'] * $item['base_price'], 0, ',', '.') ?> T
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="item-actions">
                                <?php if ($item['type'] === 'product'): ?>
                                    <a href="<?= BASE_URL ?>/storage/product/<?= $item['product_id'] ?>"
                                       class="btn btn-sm btn-outline">Details</a>
                                    <a href="<?= BASE_URL ?>/salespoints/compare/<?= $item['product_id'] ?>"
                                       class="btn btn-sm btn-primary">Verkaufen</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.storage-page {
    padding: 1rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.storage-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.storage-page .stat-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.storage-page .stat-icon {
    font-size: 2rem;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-bg-secondary);
    border-radius: 10px;
}

.storage-page .stat-content {
    display: flex;
    flex-direction: column;
}

.storage-page .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-primary);
}

.storage-page .stat-label {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.search-section {
    margin-bottom: 2rem;
}

.search-form {
    display: flex;
    gap: 0.5rem;
    max-width: 400px;
}

.search-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    background: var(--color-bg);
    color: var(--color-text);
    font-size: 1rem;
}

.search-input:focus {
    outline: none;
    border-color: var(--color-primary);
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

.empty-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 1.5rem;
}

.storage-category {
    margin-bottom: 2rem;
}

.category-title {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--color-border);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.category-count {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    font-weight: normal;
}

.storage-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

.storage-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1rem;
    transition: transform 0.2s, box-shadow 0.2s;
}

.storage-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.item-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    overflow: hidden;
    background: var(--color-bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
}

.item-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.icon-placeholder {
    font-size: 1.5rem;
}

.item-info {
    flex: 1;
    min-width: 120px;
}

.item-info h3 {
    margin: 0;
    font-size: 1rem;
}

.item-quantity {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.item-value {
    text-align: right;
}

.value-label {
    display: block;
    font-size: 0.75rem;
    color: var(--color-text-secondary);
}

.value-amount {
    font-weight: 600;
    color: var(--color-success);
}

.item-actions {
    width: 100%;
    display: flex;
    gap: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--color-border);
}

.item-actions .btn {
    flex: 1;
}
</style>
