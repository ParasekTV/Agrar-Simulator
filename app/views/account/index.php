<div class="account-page">
    <div class="page-header">
        <h1>Account-Verwaltung</h1>
        <a href="<?= BASE_URL ?>/logout" class="btn btn-danger btn-logout">Abmelden</a>
    </div>

    <!-- Willkommen -->
    <div class="welcome-banner">
        <div class="welcome-content">
            <div class="profile-picture-large">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profilbild">
                <?php else: ?>
                    <div class="avatar-placeholder"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="welcome-text">
                <h2>Willkommen zurück, <?= htmlspecialchars($user['username']) ?>!</h2>
                <p class="profile-meta">
                    <span>Mitglied seit: <?= date('d.m.Y', strtotime($user['created_at'])) ?></span>
                    <?php if (!empty($user['last_login'])): ?>
                        <span>Letzter Login: <?= date('d.m.Y H:i', strtotime($user['last_login'])) ?> Uhr</span>
                    <?php endif; ?>
                </p>
                <?php if ($user['vacation_mode']): ?>
                    <span class="badge badge-warning">Urlaubsmodus aktiv</span>
                <?php endif; ?>
                <?php if ($user['deletion_requested']): ?>
                    <span class="badge badge-danger">Löschung angefordert</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="account-grid">
        <!-- Profilbild -->
        <div class="card">
            <div class="card-header"><h3>Profilbild</h3></div>
            <div class="card-body">
                <div class="current-picture">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($user['profile_picture']) ?>"
                             alt="Aktuelles Profilbild" class="preview-image">
                    <?php else: ?>
                        <div class="no-picture">
                            <span class="icon">&#128100;</span>
                            <p>Kein Profilbild</p>
                        </div>
                    <?php endif; ?>
                </div>
                <form action="<?= BASE_URL ?>/account/picture" method="POST" enctype="multipart/form-data" class="mt-3">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="form-group">
                        <label for="picture">Neues Bild hochladen</label>
                        <input type="file" name="picture" id="picture" accept="image/jpeg,image/png,image/gif,image/webp" class="form-input">
                        <small class="form-hint">Max. 124x124 Pixel, max. 3 MB (JPG, PNG, GIF, WebP)</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Hochladen</button>
                </form>
                <?php if (!empty($user['profile_picture'])): ?>
                    <form action="<?= BASE_URL ?>/account/picture/delete" method="POST" class="mt-2">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button type="submit" class="btn btn-outline btn-sm"
                                onclick="return confirm('Profilbild wirklich löschen?')">
                            Bild löschen
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- E-Mail ändern -->
        <div class="card">
            <div class="card-header"><h3>E-Mail ändern</h3></div>
            <div class="card-body">
                <p class="current-value">
                    <strong>Aktuelle E-Mail:</strong><br>
                    <?= htmlspecialchars($user['email']) ?>
                    <?php if ($user['is_verified']): ?>
                        <span class="badge badge-success">Verifiziert</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Nicht verifiziert</span>
                    <?php endif; ?>
                </p>
                <form action="<?= BASE_URL ?>/account/email" method="POST" class="mt-3">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="form-group">
                        <label for="new_email">Neue E-Mail-Adresse</label>
                        <input type="email" name="new_email" id="new_email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="email_password">Passwort bestätigen</label>
                        <input type="password" name="password" id="email_password" class="form-input" required>
                    </div>
                    <button type="submit" class="btn btn-primary">E-Mail ändern</button>
                </form>
            </div>
        </div>

        <!-- Passwort ändern -->
        <div class="card">
            <div class="card-header"><h3>Passwort ändern</h3></div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/account/password" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="form-group">
                        <label for="current_password">Aktuelles Passwort</label>
                        <input type="password" name="current_password" id="current_password" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Neues Passwort</label>
                        <input type="password" name="new_password" id="new_password" class="form-input" required minlength="8">
                        <small class="form-hint">Mindestens 8 Zeichen</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Neues Passwort bestätigen</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-input" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Passwort ändern</button>
                </form>
            </div>
        </div>

        <!-- Urlaubsmodus -->
        <div class="card">
            <div class="card-header"><h3>Urlaubsmodus</h3></div>
            <div class="card-body">
                <?php if ($user['vacation_mode']): ?>
                    <div class="alert alert-info">
                        <strong>Urlaubsmodus aktiv</strong>
                        <?php if (!empty($user['vacation_started_at'])): ?>
                            <br>seit <?= date('d.m.Y', strtotime($user['vacation_started_at'])) ?>
                        <?php endif; ?>
                    </div>
                    <p>Während des Urlaubsmodus sind deine Produktionen pausiert und du bist vor Inaktivitäts-Strafen geschützt.</p>
                    <form action="<?= BASE_URL ?>/account/vacation" method="POST" class="mt-3">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="action" value="disable">
                        <button type="submit" class="btn btn-primary">Urlaubsmodus beenden</button>
                    </form>
                <?php else: ?>
                    <p>Aktiviere den Urlaubsmodus, wenn du längere Zeit nicht spielen kannst. Deine Produktionen werden pausiert.</p>
                    <p class="text-muted"><small>Hinweis: Nach 30 Tagen Inaktivität wird der Urlaubsmodus automatisch aktiviert.</small></p>
                    <form action="<?= BASE_URL ?>/account/vacation" method="POST" class="mt-3">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="action" value="enable">
                        <button type="submit" class="btn btn-outline">Urlaubsmodus aktivieren</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gefahrenzone -->
    <div class="danger-zone">
        <div class="card card-danger">
            <div class="card-header"><h3>Gefahrenzone</h3></div>
            <div class="card-body">
                <?php if ($user['deletion_requested']): ?>
                    <div class="alert alert-danger">
                        <strong>Löschung angefordert!</strong><br>
                        Dein Account wird am <strong><?= date('d.m.Y', strtotime($user['deletion_requested_at'] . ' +7 days')) ?></strong> gelöscht.
                    </div>
                    <p>Wenn du dich in dieser Zeit einloggst, wird die Löschung automatisch abgebrochen.</p>
                    <form action="<?= BASE_URL ?>/account/delete/cancel" method="POST" class="mt-3">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button type="submit" class="btn btn-primary">Löschung abbrechen</button>
                    </form>
                <?php else: ?>
                    <p class="text-danger">
                        <strong>Achtung:</strong> Wenn du deinen Account zur Löschung markierst, werden alle Daten nach 7 Tagen unwiderruflich gelöscht.
                    </p>
                    <p>Du wirst automatisch ausgeloggt. Wenn du dich in den nächsten 7 Tagen wieder einloggst, wird die Löschung abgebrochen.</p>
                    <form action="<?= BASE_URL ?>/account/delete" method="POST" class="mt-3"
                          onsubmit="return confirm('Bist du sicher? Du wirst ausgeloggt und dein Account wird in 7 Tagen gelöscht.');">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="confirm_deletion" required>
                                <span>Ich verstehe, dass mein Account in 7 Tagen gelöscht wird</span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-danger">Account zur Löschung markieren</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.account-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem;
}

.account-page .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.btn-logout {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.welcome-banner {
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark, #2563eb) 100%);
    color: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.welcome-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.profile-picture-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(255,255,255,0.2);
    flex-shrink: 0;
}

.profile-picture-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
    background: rgba(255,255,255,0.3);
}

.welcome-text h2 {
    margin: 0 0 0.5rem;
}

.profile-meta {
    opacity: 0.9;
    font-size: 0.9rem;
}

.profile-meta span {
    display: block;
}

.account-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.card {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    overflow: hidden;
}

.card-header {
    background: var(--color-bg-secondary);
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--color-border);
}

.card-header h3 {
    margin: 0;
    font-size: 1.1rem;
}

.card-body {
    padding: 1.25rem;
}

.current-picture {
    text-align: center;
    padding: 1rem;
    background: var(--color-bg-secondary);
    border-radius: 8px;
}

.preview-image {
    width: 124px;
    height: 124px;
    object-fit: cover;
    border-radius: 8px;
}

.no-picture {
    color: var(--color-text-secondary);
}

.no-picture .icon {
    font-size: 3rem;
    display: block;
    margin-bottom: 0.5rem;
}

.current-value {
    padding: 0.75rem;
    background: var(--color-bg-secondary);
    border-radius: 6px;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--color-border);
    border-radius: 6px;
    font-size: 1rem;
    background: var(--color-bg);
    color: var(--color-text);
}

.form-hint {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-label input {
    margin-top: 0.25rem;
}

.danger-zone {
    margin-top: 2rem;
}

.card-danger {
    border-color: var(--color-danger);
}

.card-danger .card-header {
    background: rgba(239, 68, 68, 0.1);
    color: var(--color-danger);
}

.text-danger {
    color: var(--color-danger);
}

.text-muted {
    color: var(--color-text-secondary);
}

.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.alert-info {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: #1d4ed8;
}

.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #dc2626;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 4px;
    margin-left: 0.5rem;
}

.badge-success {
    background: var(--color-success);
    color: white;
}

.badge-warning {
    background: var(--color-warning);
    color: #000;
}

.badge-danger {
    background: var(--color-danger);
    color: white;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
    text-decoration: none;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--color-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--color-primary-dark, #2563eb);
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--color-border);
    color: var(--color-text);
}

.btn-outline:hover {
    background: var(--color-bg-secondary);
}

.btn-danger {
    background: var(--color-danger);
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.mt-2 {
    margin-top: 0.5rem;
}

.mt-3 {
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .welcome-content {
        flex-direction: column;
        text-align: center;
    }

    .account-grid {
        grid-template-columns: 1fr;
    }
}
</style>
