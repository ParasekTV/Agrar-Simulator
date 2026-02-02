<div class="news-search-page">
    <div class="page-header">
        <h1>Forum durchsuchen</h1>
        <a href="<?= BASE_URL ?>/news" class="btn btn-outline">&larr; Zurück zum Forum</a>
    </div>

    <!-- Suchformular -->
    <div class="search-card">
        <form action="<?= BASE_URL ?>/news/search" method="GET" class="search-form">
            <div class="search-row">
                <div class="form-group search-input-group">
                    <input type="text" name="q" id="search-query" class="form-control form-control-lg"
                           placeholder="Suchbegriff eingeben..."
                           value="<?= htmlspecialchars($query ?? '') ?>" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">Suchen</button>
            </div>

            <div class="search-filters">
                <div class="filter-group">
                    <label>Kategorie:</label>
                    <select name="category" class="form-select">
                        <option value="">Alle Kategorien</option>
                        <option value="general" <?= ($category ?? '') === 'general' ? 'selected' : '' ?>>Allgemein</option>
                        <option value="tips" <?= ($category ?? '') === 'tips' ? 'selected' : '' ?>>Tipps & Tricks</option>
                        <option value="trading" <?= ($category ?? '') === 'trading' ? 'selected' : '' ?>>Handel</option>
                        <option value="cooperatives" <?= ($category ?? '') === 'cooperatives' ? 'selected' : '' ?>>Genossenschaften</option>
                        <option value="off_topic" <?= ($category ?? '') === 'off_topic' ? 'selected' : '' ?>>Off-Topic</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Sortierung:</label>
                    <select name="sort" class="form-select">
                        <option value="relevance" <?= ($sort ?? 'relevance') === 'relevance' ? 'selected' : '' ?>>Relevanz</option>
                        <option value="newest" <?= ($sort ?? '') === 'newest' ? 'selected' : '' ?>>Neueste zuerst</option>
                        <option value="oldest" <?= ($sort ?? '') === 'oldest' ? 'selected' : '' ?>>Älteste zuerst</option>
                        <option value="most_liked" <?= ($sort ?? '') === 'most_liked' ? 'selected' : '' ?>>Beliebteste</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Suchergebnisse -->
    <?php if (isset($query) && !empty($query)): ?>
        <div class="search-results">
            <div class="results-header">
                <h3>
                    <?php if (!empty($results)): ?>
                        <?= count($results) ?> Ergebnis<?= count($results) !== 1 ? 'se' : '' ?> für "<?= htmlspecialchars($query) ?>"
                    <?php else: ?>
                        Keine Ergebnisse für "<?= htmlspecialchars($query) ?>"
                    <?php endif; ?>
                </h3>
            </div>

            <?php if (empty($results)): ?>
                <div class="empty-state">
                    <span class="empty-icon">&#128269;</span>
                    <h3>Keine Beiträge gefunden</h3>
                    <p>Versuche andere Suchbegriffe oder erweitere deine Suche.</p>
                </div>
            <?php else: ?>
                <div class="results-list">
                    <?php foreach ($results as $post): ?>
                        <div class="search-result-item">
                            <div class="result-category">
                                <span class="category-badge category-<?= $post['category'] ?>">
                                    <?php
                                    $categories = [
                                        'general' => 'Allgemein',
                                        'tips' => 'Tipps & Tricks',
                                        'trading' => 'Handel',
                                        'cooperatives' => 'Genossenschaften',
                                        'off_topic' => 'Off-Topic'
                                    ];
                                    echo $categories[$post['category']] ?? $post['category'];
                                    ?>
                                </span>
                            </div>
                            <div class="result-content">
                                <a href="<?= BASE_URL ?>/news/<?= $post['id'] ?>" class="result-title">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                                <p class="result-excerpt">
                                    <?= htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 200)) ?>...
                                </p>
                                <div class="result-meta">
                                    <span class="meta-author">
                                        Von <?= htmlspecialchars($post['author_name']) ?>
                                    </span>
                                    <span class="meta-date">
                                        <?= date('d.m.Y', strtotime($post['created_at'])) ?>
                                    </span>
                                    <span class="meta-stats">
                                        &#10084; <?= $post['like_count'] ?> &bull;
                                        &#128172; <?= $post['comment_count'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?= BASE_URL ?>/news/search?q=<?= urlencode($query) ?>&page=<?= $page - 1 ?><?= $category ? '&category=' . $category : '' ?><?= $sort ? '&sort=' . $sort : '' ?>"
                               class="pagination-link">&laquo;</a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="<?= BASE_URL ?>/news/search?q=<?= urlencode($query) ?>&page=<?= $i ?><?= $category ? '&category=' . $category : '' ?><?= $sort ? '&sort=' . $sort : '' ?>"
                               class="pagination-link <?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="<?= BASE_URL ?>/news/search?q=<?= urlencode($query) ?>&page=<?= $page + 1 ?><?= $category ? '&category=' . $category : '' ?><?= $sort ? '&sort=' . $sort : '' ?>"
                               class="pagination-link">&raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="search-hints">
            <h3>Suchtipps</h3>
            <ul>
                <li>Verwende spezifische Begriffe für bessere Ergebnisse</li>
                <li>Suche nach Nutzernamen um Beiträge eines Spielers zu finden</li>
                <li>Filtere nach Kategorie um die Suche einzugrenzen</li>
            </ul>

            <h4>Beliebte Themen</h4>
            <div class="popular-tags">
                <a href="<?= BASE_URL ?>/news/search?q=Anfänger" class="tag">Anfänger</a>
                <a href="<?= BASE_URL ?>/news/search?q=Handel" class="tag">Handel</a>
                <a href="<?= BASE_URL ?>/news/search?q=Ernte" class="tag">Ernte</a>
                <a href="<?= BASE_URL ?>/news/search?q=Genossenschaft" class="tag">Genossenschaft</a>
                <a href="<?= BASE_URL ?>/news/search?q=Forschung" class="tag">Forschung</a>
                <a href="<?= BASE_URL ?>/news/search?q=Tiere" class="tag">Tiere</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.search-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.5rem;
}
.search-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}
.search-input-group {
    flex: 1;
    margin: 0;
}
.search-filters {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.filter-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.filter-group label {
    font-weight: 500;
    white-space: nowrap;
}
.filter-group .form-select {
    min-width: 150px;
}
.search-results {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}
.results-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--color-gray-200);
}
.results-header h3 {
    margin: 0;
}
.results-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.search-result-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: var(--color-gray-50);
    border-radius: var(--radius);
    transition: var(--transition);
}
.search-result-item:hover {
    background: var(--color-gray-100);
}
.result-category {
    flex-shrink: 0;
}
.category-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}
.category-general { background: #e3f2fd; color: #1565c0; }
.category-tips { background: #e8f5e9; color: #2e7d32; }
.category-trading { background: #fff3e0; color: #ef6c00; }
.category-cooperatives { background: #f3e5f5; color: #7b1fa2; }
.category-off_topic { background: #fce4ec; color: #c2185b; }
.result-content {
    flex: 1;
    min-width: 0;
}
.result-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--color-gray-800);
    text-decoration: none;
    display: block;
    margin-bottom: 0.5rem;
}
.result-title:hover {
    color: var(--color-primary);
}
.result-excerpt {
    color: var(--color-gray-600);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    line-height: 1.5;
}
.result-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.85rem;
    color: var(--color-gray-500);
}
.search-hints {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}
.search-hints h3 {
    margin-bottom: 1rem;
}
.search-hints ul {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}
.search-hints li {
    margin-bottom: 0.5rem;
    color: var(--color-gray-600);
}
.search-hints h4 {
    margin-bottom: 0.75rem;
}
.popular-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.tag {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: var(--color-gray-100);
    color: var(--color-gray-700);
    border-radius: 20px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: var(--transition);
}
.tag:hover {
    background: var(--color-primary);
    color: white;
}
@media (max-width: 768px) {
    .search-row {
        flex-direction: column;
    }
    .search-filters {
        flex-direction: column;
        gap: 1rem;
    }
    .filter-group {
        flex-direction: column;
        align-items: flex-start;
    }
    .filter-group .form-select {
        width: 100%;
    }
    .search-result-item {
        flex-direction: column;
    }
    .result-meta {
        flex-wrap: wrap;
    }
}
</style>
