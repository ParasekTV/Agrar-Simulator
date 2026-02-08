<div class="post-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/cooperative/board" class="btn btn-outline btn-sm">&#8592; Zurueck zur Pinnwand</a>
    </div>

    <!-- Beitrag -->
    <div class="post-detail <?= $post['is_announcement'] ? 'announcement' : '' ?>">
        <div class="post-detail-header">
            <div class="post-badges">
                <?php if ($post['is_pinned']): ?>
                    <span class="badge badge-primary">&#128204; Angepinnt</span>
                <?php endif; ?>
                <?php if ($post['is_announcement']): ?>
                    <span class="badge badge-warning">&#128227; Ankuendigung</span>
                <?php endif; ?>
            </div>
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <div class="post-meta">
                <span class="author">Von <strong><?= htmlspecialchars($post['author_name']) ?></strong></span>
                <span class="date"><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
                <span class="views">&#128065; <?= $post['views_count'] ?> Aufrufe</span>
            </div>
        </div>

        <div class="post-detail-content">
            <?= nl2br(htmlspecialchars($post['content'])) ?>
        </div>

        <div class="post-detail-actions">
            <div class="action-group">
                <!-- Like Button -->
                <form action="<?= BASE_URL ?>/cooperative/board/like" method="POST" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                    <button type="submit" class="btn <?= $hasLiked ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                        <?= $hasLiked ? '&#128077;' : '&#128078;' ?> <?= $post['like_count'] ?> Likes
                    </button>
                </form>
            </div>

            <?php if ($canEdit): ?>
                <div class="action-group">
                    <?php if ($isLeader): ?>
                        <!-- Pin Toggle -->
                        <form action="<?= BASE_URL ?>/cooperative/board/pin" method="POST" class="inline-form">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <button type="submit" class="btn btn-outline btn-sm">
                                <?= $post['is_pinned'] ? 'Nicht mehr anpinnen' : 'Anpinnen' ?>
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Delete -->
                    <form action="<?= BASE_URL ?>/cooperative/board/delete" method="POST" class="inline-form">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Beitrag wirklich loeschen?')">
                            Loeschen
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kommentare -->
    <div class="comments-section">
        <h3>Kommentare (<?= count($comments) ?>)</h3>

        <!-- Neuer Kommentar -->
        <div class="new-comment">
            <form action="<?= BASE_URL ?>/cooperative/board/comment" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <div class="form-group">
                    <textarea name="content" class="form-input" rows="2"
                              placeholder="Schreibe einen Kommentar..." required minlength="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Kommentieren</button>
            </form>
        </div>

        <!-- Kommentar-Liste -->
        <?php if (empty($comments)): ?>
            <p class="text-muted">Noch keine Kommentare. Sei der Erste!</p>
        <?php else: ?>
            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <strong><?= htmlspecialchars($comment['author_name']) ?></strong>
                            <span class="comment-date"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                        </div>
                        <div class="comment-content">
                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        </div>
                        <?php if ($comment['author_farm_id'] === $this->getFarmId() || $isLeader): ?>
                            <div class="comment-actions">
                                <form action="<?= BASE_URL ?>/cooperative/board/comment/delete" method="POST" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" class="btn btn-link btn-sm text-danger"
                                            onclick="return confirm('Kommentar loeschen?')">
                                        Loeschen
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.post-detail {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.post-detail.announcement {
    border-left: 4px solid var(--color-warning);
}

.post-detail-header {
    margin-bottom: 1.5rem;
}

.post-badges {
    margin-bottom: 0.5rem;
}

.post-detail-header h1 {
    margin: 0 0 0.75rem 0;
    font-size: 1.5rem;
}

.post-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    color: var(--color-text-secondary);
    font-size: 0.9rem;
}

.post-detail-content {
    line-height: 1.7;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--color-border);
}

.post-detail-actions {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.action-group {
    display: flex;
    gap: 0.5rem;
}

.comments-section {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
}

.comments-section h3 {
    margin: 0 0 1rem 0;
}

.new-comment {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--color-border);
}

.comments-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.comment {
    padding: 1rem;
    background: var(--color-bg-secondary);
    border-radius: 8px;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.comment-date {
    color: var(--color-text-secondary);
}

.comment-content {
    line-height: 1.5;
}

.comment-actions {
    margin-top: 0.5rem;
}

.inline-form {
    display: inline-block;
}
</style>
