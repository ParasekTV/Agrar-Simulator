<div class="register-page">
    <div class="register-container">
        <div class="auth-logo">
            <img src="<?= BASE_URL ?>/img/logo_lsbg.png" alt="LSBG Agrar Simulator Logo">
        </div>
        <div class="auth-card">
            <div class="discord-user-info">
                <img src="<?= htmlspecialchars($discord['avatar_url']) ?>" alt="" class="discord-avatar">
                <div class="discord-name">
                    <strong><?= htmlspecialchars($discord['global_name'] ?? $discord['username']) ?></strong>
                    <span class="discord-tag">via Discord</span>
                </div>
            </div>

            <div class="auth-header">
                <h2>Registrierung abschließen</h2>
                <p>Wähle einen Benutzernamen und Namen für deine Farm.</p>
            </div>

            <form action="<?= BASE_URL ?>/auth/discord/register" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="form-group">
                    <label for="username">Benutzername</label>
                    <input type="text" id="username" name="username" required
                           value="<?= htmlspecialchars($suggestedUsername) ?>"
                           placeholder="3-20 Zeichen, Buchstaben, Zahlen, _"
                           pattern="[a-zA-Z0-9_]{3,20}">
                    <small>Nur Buchstaben, Zahlen und Unterstriche. 3-20 Zeichen.</small>
                </div>

                <div class="form-group">
                    <label for="farm_name">Name deiner Farm</label>
                    <input type="text" id="farm_name" name="farm_name" required
                           placeholder="z.B. Sonnenhof" minlength="3" maxlength="50">
                    <small>Gib deiner Farm einen Namen!</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Farm gründen</button>
            </form>

            <div class="auth-footer">
                <p>
                    <a href="<?= BASE_URL ?>/login">Abbrechen</a>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.register-page {
    min-height: 100vh;
    background: var(--color-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.register-container {
    max-width: 450px;
    width: 100%;
}

.auth-logo {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-logo img {
    max-width: 180px;
    height: auto;
}

.auth-card {
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 2rem;
}

.discord-user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(88, 101, 242, 0.1);
    border: 1px solid rgba(88, 101, 242, 0.3);
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.discord-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
}

.discord-name {
    display: flex;
    flex-direction: column;
}

.discord-name strong {
    color: var(--color-text);
}

.discord-tag {
    font-size: 0.8rem;
    color: #5865F2;
}

.auth-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.auth-header h2 {
    margin: 0 0 0.5rem;
    color: var(--color-text);
}

.auth-header p {
    margin: 0;
    color: var(--color-text-secondary);
    font-size: 0.9rem;
}

.auth-form .form-group {
    margin-bottom: 1.25rem;
}

.auth-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--color-text);
}

.auth-form input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    background: var(--color-bg);
    color: var(--color-text);
    font-size: 1rem;
}

.auth-form input:focus {
    outline: none;
    border-color: var(--color-primary);
}

.auth-form small {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.btn-block {
    width: 100%;
    padding: 0.875rem;
}

.auth-footer {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--color-border);
}

.auth-footer a {
    color: var(--color-text-secondary);
    text-decoration: none;
}

.auth-footer a:hover {
    color: var(--color-primary);
}
</style>
