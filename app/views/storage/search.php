<div class="search-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/storage" class="back-link">&larr; Zurück zum Lager</a>
        <h1>Suche: "<?= htmlspecialchars($search) ?>"</h1>
    </div>

    <!-- Suchfeld -->
    <div class="search-section">
        <form action="<?= BASE_URL ?>/storage/search" method="GET" class="search-form">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Produkt suchen..." class="search-input">
            <button type="submit" class="btn btn-primary">Suchen</button>
        </form>
    </div>

    <?php if (empty($results)): ?>
        <div class="empty-state">
            <div class="empty-icon">&#128269;</div>
            <h3>Keine Ergebnisse</h3>
            <p>Für "<?= htmlspecialchars($search) ?>" wurden keine Produkte in deinem Lager gefunden.</p>
        </div>
    <?php else: ?>
        <div class="results-info">
            <span><?= count($results) ?> Produkt(e) gefunden</span>
        </div>

        <div class="results-grid">
            <?php foreach ($results as $product): ?>
                <div class="result-card">
                    <div class="product-icon">
                        <?php if (!empty($product['icon'])): ?>
                            <img src="<?= BASE_URL ?>/img/products/<?= htmlspecialchars($product['icon']) ?>"
                                 alt="" onerror="this.src='<?= BASE_URL ?>/img/placeholder.png'">
                        <?php else: ?>
                            <span class="icon-placeholder">&#128230;</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($product['name_de']) ?></h3>
                        <span class="product-category"><?= htmlspecialchars($product['category']) ?></span>
                    </div>
                    <div class="product-quantity">
                        <span class="quantity"><?= number_format($product['quantity']) ?></span>
                        <span class="unit">Stück</span>
                    </div>
                    <div class="product-actions">
                        <a href="<?= BASE_URL ?>/storage/product/<?= $product['product_id'] ?>" class="btn btn-sm btn-outline">
                            Details
                        </a>
                        <a href="<?= BASE_URL ?>/salespoints/compare/<?= $product['product_id'] ?>" class="btn btn-sm btn-primary">
                            Verkaufen
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.search-page {
    padding: 1rem;
    max-width: 900px;
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
    margin-bottom: 1.5rem;
}

.page-header h1 {
    margin: 0.5rem 0 0;
}

.search-section {
    margin-bottom: 2rem;
}

.search-form {
    display: flex;
    gap: 0.5rem;
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

.results-info {
    margin-bottom: 1rem;
    color: var(--color-text-secondary);
    font-size: 0.875rem;
}

.results-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.result-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.result-card:hover {
    border-color: var(--color-primary);
}

.product-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    overflow: hidden;
    background: var(--color-bg-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.product-icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.icon-placeholder {
    font-size: 1.5rem;
}

.product-info {
    flex: 1;
}

.product-info h3 {
    margin: 0;
    font-size: 1rem;
}

.product-category {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
}

.product-quantity {
    text-align: center;
    padding: 0 1rem;
}

.product-quantity .quantity {
    display: block;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-primary);
}

.product-quantity .unit {
    font-size: 0.75rem;
    color: var(--color-text-secondary);
}

.product-actions {
    display: flex;
    gap: 0.5rem;
}

@media (max-width: 600px) {
    .result-card {
        flex-wrap: wrap;
    }

    .product-actions {
        width: 100%;
        margin-top: 0.5rem;
    }

    .product-actions .btn {
        flex: 1;
    }
}
</style>
