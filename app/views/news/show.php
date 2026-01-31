<div class="post-page">
    <div class="page-header">
        <a href="<?= BASE_URL ?>/news" class="btn btn-outline">&larr; Zurueck</a>
    </div>

    <article class="post-detail">
        <header class="post-detail-header">
            <span class="post-category"><?= ucfirst($post['category']) ?></span>
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <div class="post-meta">
                <span>von <strong><?= htmlspecialchars($post['author_name']) ?></strong></span>
                <span><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
                <span>&#128065; <?= $post['views'] ?> Aufrufe</span>
            </div>
        </header>

        <div class="post-content">
            <?= nl2br(htmlspecialchars($post['content'])) ?>
        </div>

        <footer class="post-detail-footer">
            <div class="post-actions">
                <form action="<?= BASE_URL ?>/news/like" method="POST" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                    <button type="submit" class="btn btn-outline">
                        &#128077; <?= $post['likes'] ?> Likes
                    </button>
                </form>
                <?php if ($post['author_farm_id'] === Session::getFarmId()): ?>
                    <form action="<?= BASE_URL ?>/news/delete" method="POST" class="inline-form"
                          onsubmit="return confirm('Wirklich loeschen?')">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit" class="btn btn-outline btn-danger">Loeschen</button>
                    </form>
                <?php endif; ?>
            </div>
        </footer>
    </article>

    <!-- Kommentare -->
    <section class="comments-section">
        <h3><?= count($post['comments']) ?> Kommentare</h3>

        <!-- Kommentar-Formular -->
        <form action="<?= BASE_URL ?>/news/comment" method="POST" class="comment-form">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <div class="form-group">
                <textarea name="content" class="form-input" rows="3"
                          placeholder="Schreibe einen Kommentar..." required minlength="5"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Kommentieren</button>
        </form>

        <!-- Kommentar-Liste -->
        <?php if (empty($post['comments'])): ?>
            <p class="text-muted">Noch keine Kommentare. Sei der Erste!</p>
        <?php else: ?>
            <div class="comments-list">
                <?php foreach ($post['comments'] as $comment): ?>
                    <div class="comment">
                        <div class="comment-header">
                            <strong><?= htmlspecialchars($comment['author_name']) ?></strong>
                            <span class="comment-date"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                        </div>
                        <div class="comment-content">
                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<style>
.post-detail {
    background: white;
    border-radius: var(--radius-lg);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-sm);
}
.post-detail-header { margin-bottom: 1.5rem; }
.post-detail-header h1 { margin: 0.5rem 0; }
.post-meta { display: flex; gap: 1.5rem; color: var(--color-gray-600); font-size: 0.9rem; }
.post-content { line-height: 1.8; font-size: 1.05rem; }
.post-detail-footer { margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--color-gray-200); }
.post-actions { display: flex; gap: 0.5rem; }
.comments-section {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}
.comment-form { margin-bottom: 1.5rem; }
.comments-list { display: flex; flex-direction: column; gap: 1rem; }
.comment {
    padding: 1rem;
    background: var(--color-gray-100);
    border-radius: var(--radius);
}
.comment-header { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
.comment-date { font-size: 0.85rem; color: var(--color-gray-500); }
</style>
