<div class="admin-page">
    <div class="page-header">
        <h1>Beitrag bearbeiten</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/admin/news" class="btn btn-outline">Zurück zur Liste</a>
            <a href="<?= BASE_URL ?>/news/<?= $post['id'] ?>" class="btn btn-outline" target="_blank">Anzeigen</a>
        </div>
    </div>

    <div class="admin-card">
        <form action="<?= BASE_URL ?>/admin/news/<?= $post['id'] ?>/update" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="card-body">
                <div class="post-meta">
                    <span>Erstellt: <?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
                    <span>Views: <?= number_format($post['views']) ?></span>
                    <span>Likes: <?= number_format($post['likes']) ?></span>
                </div>

                <div class="form-group">
                    <label for="category">Kategorie *</label>
                    <select name="category" id="category" class="form-select" required>
                        <option value="admin_news" <?= $post['category'] === 'admin_news' ? 'selected' : '' ?>>
                            News (Allgemeine Ankündigung)
                        </option>
                        <option value="changelog" <?= $post['category'] === 'changelog' ? 'selected' : '' ?>>
                            Changelog (Update-Hinweise)
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">Titel *</label>
                    <input type="text" name="title" id="title" class="form-input"
                           required minlength="3" maxlength="200"
                           value="<?= htmlspecialchars($post['title']) ?>">
                </div>

                <div class="form-group">
                    <label for="content">Inhalt *</label>
                    <textarea name="content" id="content" class="form-textarea" rows="15"
                              required minlength="10"><?= htmlspecialchars($post['content']) ?></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_pinned" value="1"
                               <?= $post['is_pinned'] ? 'checked' : '' ?>>
                        <span>Beitrag oben anpinnen</span>
                    </label>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Speichern</button>
                <a href="<?= BASE_URL ?>/admin/news" class="btn btn-outline">Abbrechen</a>
            </div>
        </form>

        <form action="<?= BASE_URL ?>/admin/news/<?= $post['id'] ?>/delete" method="POST"
              class="delete-form"
              onsubmit="return confirm('Beitrag wirklich löschen?');">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <button type="submit" class="btn btn-danger">Löschen</button>
        </form>
    </div>
</div>

<style>
.post-meta {
    display: flex;
    gap: 1.5rem;
    padding: 1rem;
    background: var(--color-bg-secondary);
    border-radius: 6px;
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
    color: var(--color-text-secondary);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--color-border);
    border-radius: 6px;
    font-size: 1rem;
    background: var(--color-bg);
}

.form-textarea {
    font-family: 'Consolas', 'Monaco', monospace;
    resize: vertical;
    min-height: 200px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
}

.card-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--color-border);
    display: flex;
    gap: 1rem;
    align-items: center;
}

.btn-danger {
    background: var(--color-danger);
    color: white;
    border: none;
}

.delete-form {
    margin-top: 1rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--color-border);
    display: flex;
    justify-content: flex-end;
}
</style>
