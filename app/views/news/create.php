<div class="create-post-page">
    <div class="page-header">
        <h1>Neuer Beitrag</h1>
        <a href="<?= BASE_URL ?>/news" class="btn btn-outline">Zurück</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="<?= BASE_URL ?>/news/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="form-group">
                    <label for="title">Titel</label>
                    <input type="text" name="title" id="title" class="form-input"
                           required minlength="5" maxlength="200"
                           placeholder="Gib deinem Beitrag einen aussagekräftigen Titel">
                </div>

                <div class="form-group">
                    <label for="category">Kategorie</label>
                    <select name="category" id="category" class="form-select" required>
                        <option value="">Wähle eine Kategorie...</option>
                        <?php foreach ($categories as $key => $name): ?>
                            <option value="<?= $key ?>"><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">Inhalt</label>
                    <textarea name="content" id="content" class="form-input"
                              rows="12" required minlength="20"
                              placeholder="Schreibe deinen Beitrag..."></textarea>
                    <small>Mindestens 20 Zeichen</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Veröffentlichen</button>
                    <a href="<?= BASE_URL ?>/news" class="btn btn-outline">Abbrechen</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.create-post-page .card { max-width: 800px; }
.form-actions { display: flex; gap: 0.5rem; margin-top: 1.5rem; }
</style>
