<div class="admin-page">
    <div class="page-header">
        <h1>News/Changelog erstellen</h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/admin/news" class="btn btn-outline">Zurück zur Liste</a>
        </div>
    </div>

    <div class="admin-card">
        <form action="<?= BASE_URL ?>/admin/news/store" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="card-body">
                <div class="form-group">
                    <label for="category">Kategorie *</label>
                    <select name="category" id="category" class="form-select" required>
                        <option value="admin_news">News (Allgemeine Ankündigung)</option>
                        <option value="changelog">Changelog (Update-Hinweise)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">Titel *</label>
                    <input type="text" name="title" id="title" class="form-input"
                           required minlength="3" maxlength="200"
                           placeholder="z.B. Update v1.2.0 - Neue Features">
                </div>

                <div class="form-group">
                    <label for="content">Inhalt *</label>
                    <textarea name="content" id="content" class="form-textarea" rows="15"
                              required minlength="10"
                              placeholder="Schreibe hier den Inhalt...

Für Changelogs:
- Neue Feature 1
- Neue Feature 2
- Bugfix: Problem XYZ behoben

Markdown wird unterstützt."></textarea>
                    <small class="form-help">Tipp: Verwende Markdown für Formatierung (Listen, Fett, etc.)</small>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_pinned" value="1">
                        <span>Beitrag oben anpinnen</span>
                    </label>
                    <small class="form-help">Angepinnte Beiträge werden immer oben in der Zeitung angezeigt.</small>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Veröffentlichen</button>
                <a href="<?= BASE_URL ?>/admin/news" class="btn btn-outline">Abbrechen</a>
            </div>
        </form>
    </div>
</div>

<style>
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

.form-help {
    display: block;
    margin-top: 0.25rem;
    color: var(--color-text-secondary);
    font-size: 0.875rem;
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
}
</style>
