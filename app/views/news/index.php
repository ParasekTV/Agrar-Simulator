<div class="news-page">
    <div class="page-header">
        <h1>Bauernzeitung</h1>
        <a href="<?= BASE_URL ?>/news/create" class="btn btn-primary">Beitrag schreiben</a>
    </div>

    <div class="news-layout">
        <div class="news-main">
            <!-- Kategorie-Filter -->
            <div class="category-tabs">
                <a href="<?= BASE_URL ?>/news" class="tab <?= !$category ? 'active' : '' ?>">Alle</a>
                <?php foreach ($categories as $key => $name): ?>
                    <a href="<?= BASE_URL ?>/news?category=<?= $key ?>" class="tab <?= $category === $key ? 'active' : '' ?>">
                        <?= $name ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Beitraege -->
            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <span class="empty-icon">&#128240;</span>
                    <h3>Keine Beitraege</h3>
                    <p>Sei der Erste und schreibe einen Beitrag!</p>
                </div>
            <?php else: ?>
                <div class="posts-list">
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card <?= $post['is_pinned'] ? 'pinned' : '' ?>">
                            <?php if ($post['is_pinned']): ?>
                                <span class="pin-badge">Angepinnt</span>
                            <?php endif; ?>
                            <div class="post-header">
                                <span class="post-category category-<?= $post['category'] ?>">
                                    <?= $categories[$post['category']] ?? $post['category'] ?>
                                </span>
                                <span class="post-date"><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
                            </div>
                            <h3 class="post-title">
                                <a href="<?= BASE_URL ?>/news/<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                            </h3>
                            <p class="post-excerpt">
                                <?= htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 200)) ?>...
                            </p>
                            <div class="post-footer">
                                <span class="post-author">von <?= htmlspecialchars($post['author_name']) ?></span>
                                <div class="post-stats">
                                    <span>&#128065; <?= $post['views'] ?></span>
                                    <span>&#128077; <?= $post['likes'] ?></span>
                                    <span>&#128172; <?= $post['comment_count'] ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['totalPages'] > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                            <a href="<?= BASE_URL ?>/news?page=<?= $i ?>&category=<?= urlencode($category ?? '') ?>"
                               class="pagination-link <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <aside class="news-sidebar">
            <!-- Suche -->
            <div class="card">
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/news/search" method="GET">
                        <div class="search-form">
                            <input type="text" name="q" class="form-input" placeholder="Suchen...">
                            <button type="submit" class="btn btn-primary">&#128269;</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Beliebte Beitraege -->
            <div class="card">
                <div class="card-header">
                    <h4>Beliebt</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($popularPosts)): ?>
                        <p class="text-muted">Keine beliebten Beitraege.</p>
                    <?php else: ?>
                        <ul class="sidebar-posts">
                            <?php foreach ($popularPosts as $post): ?>
                                <li>
                                    <a href="<?= BASE_URL ?>/news/<?= $post['id'] ?>">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                    <small><?= $post['likes'] ?> Likes</small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </div>
</div>

<style>
.news-layout { display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem; }
.category-tabs { display: flex; gap: 0.25rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.tab {
    padding: 0.5rem 1rem;
    background: white;
    border-radius: var(--radius);
    color: var(--color-gray-700);
    font-size: 0.9rem;
}
.tab.active { background: var(--color-primary); color: white; }
.posts-list { display: flex; flex-direction: column; gap: 1rem; }
.post-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.25rem;
    box-shadow: var(--shadow-sm);
    position: relative;
}
.post-card.pinned { border: 2px solid var(--color-primary); }
.pin-badge {
    position: absolute;
    top: -8px;
    right: 1rem;
    background: var(--color-primary);
    color: white;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
}
.post-header { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
.post-category {
    font-size: 0.8rem;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
    background: var(--color-gray-200);
}
.post-date { font-size: 0.85rem; color: var(--color-gray-500); }
.post-title { margin: 0 0 0.5rem; }
.post-title a { color: var(--color-gray-800); }
.post-title a:hover { color: var(--color-primary); }
.post-excerpt { font-size: 0.95rem; color: var(--color-gray-600); margin-bottom: 0.75rem; }
.post-footer { display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--color-gray-500); }
.post-stats { display: flex; gap: 1rem; }
.search-form { display: flex; gap: 0.5rem; }
.search-form .form-input { flex: 1; }
.sidebar-posts { list-style: none; }
.sidebar-posts li {
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--color-gray-200);
}
.sidebar-posts li:last-child { border-bottom: none; }
.sidebar-posts a { display: block; font-size: 0.9rem; }
.sidebar-posts small { color: var(--color-gray-500); }
@media (max-width: 768px) {
    .news-layout { grid-template-columns: 1fr; }
    .news-sidebar { order: -1; }
}
</style>
