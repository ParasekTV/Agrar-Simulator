<div class="board-page">
    <div class="page-header">
        <h1>Pinnwand</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/cooperative" class="btn btn-outline">Zurueck</a>
        </div>
    </div>

    <?php if ($unreadCount > 0): ?>
        <div class="alert alert-info">
            <strong><?= $unreadCount ?></strong> ungelesene Beitraege
        </div>
    <?php endif; ?>

    <!-- Neuer Beitrag -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Neuer Beitrag</h3>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/cooperative/board/create" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="form-group">
                    <label for="title">Titel</label>
                    <input type="text" name="title" id="title" class="form-input"
                           placeholder="Betreff..." required minlength="3" maxlength="200">
                </div>
                <div class="form-group">
                    <label for="content">Inhalt</label>
                    <textarea name="content" id="content" class="form-input" rows="4"
                              placeholder="Was moechtest du mitteilen?" required minlength="10"></textarea>
                </div>
                <?php if ($isLeader): ?>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_announcement" value="1">
                            Als Ankuendigung markieren
                        </label>
                    </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">Beitrag erstellen</button>
            </form>
        </div>
    </div>

    <!-- Beitraege -->
    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <span class="empty-icon">&#128221;</span>
            <h3>Noch keine Beitraege</h3>
            <p>Sei der Erste, der etwas auf die Pinnwand schreibt!</p>
        </div>
    <?php else: ?>
        <div class="posts-list">
            <?php foreach ($posts as $post): ?>
                <div class="post-card <?= $post['is_pinned'] ? 'pinned' : '' ?> <?= $post['is_announcement'] ? 'announcement' : '' ?>">
                    <div class="post-header">
                        <div class="post-meta">
                            <?php if ($post['is_pinned']): ?>
                                <span class="post-badge badge-pinned" title="Angepinnt">&#128204;</span>
                            <?php endif; ?>
                            <?php if ($post['is_announcement']): ?>
                                <span class="post-badge badge-announcement" title="Ankuendigung">&#128227;</span>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/cooperative/post/<?= $post['id'] ?>" class="post-title">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </div>
                        <div class="post-author">
                            von <strong><?= htmlspecialchars($post['author_name']) ?></strong>
                            <span class="post-date"><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
                        </div>
                    </div>
                    <div class="post-preview">
                        <?= nl2br(htmlspecialchars(mb_substr($post['content'], 0, 200))) ?>
                        <?= strlen($post['content']) > 200 ? '...' : '' ?>
                    </div>
                    <div class="post-footer">
                        <div class="post-stats">
                            <span class="stat" title="Likes">&#128077; <?= $post['like_count'] ?></span>
                            <span class="stat" title="Kommentare">&#128172; <?= $post['comment_count'] ?></span>
                            <span class="stat" title="Aufrufe">&#128065; <?= $post['views_count'] ?></span>
                        </div>
                        <a href="<?= BASE_URL ?>/cooperative/post/<?= $post['id'] ?>" class="btn btn-outline btn-sm">
                            Lesen
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['totalPages'] > 1): ?>
            <div class="pagination">
                <?php if ($pagination['page'] > 1): ?>
                    <a href="<?= BASE_URL ?>/cooperative/board?page=<?= $pagination['page'] - 1 ?>" class="btn btn-outline btn-sm">Vorherige</a>
                <?php endif; ?>
                <span class="pagination-info">Seite <?= $pagination['page'] ?> von <?= $pagination['totalPages'] ?></span>
                <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                    <a href="<?= BASE_URL ?>/cooperative/board?page=<?= $pagination['page'] + 1 ?>" class="btn btn-outline btn-sm">Naechste</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.posts-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.post-card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.25rem;
    transition: box-shadow 0.2s;
}

.post-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.post-card.pinned {
    border-left: 4px solid var(--color-primary);
    background: linear-gradient(90deg, rgba(var(--color-primary-rgb), 0.05), transparent);
}

.post-card.announcement {
    border-left: 4px solid var(--color-warning);
    background: linear-gradient(90deg, rgba(255, 193, 7, 0.1), transparent);
}

.post-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.post-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.post-badge {
    font-size: 1.1rem;
}

.post-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--color-text);
    text-decoration: none;
}

.post-title:hover {
    color: var(--color-primary);
}

.post-author {
    font-size: 0.85rem;
    color: var(--color-text-secondary);
}

.post-date {
    margin-left: 0.5rem;
}

.post-preview {
    color: var(--color-text-secondary);
    margin-bottom: 1rem;
    line-height: 1.5;
}

.post-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.post-stats {
    display: flex;
    gap: 1rem;
}

.post-stats .stat {
    font-size: 0.85rem;
    color: var(--color-text-secondary);
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
}

.pagination-info {
    color: var(--color-text-secondary);
}
</style>
