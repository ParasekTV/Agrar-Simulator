<div class="register-page">
    <div class="register-container">
        <div class="auth-logo">
            <img src="<?= BASE_URL ?>/img/logo_lsbg.png" alt="LSBG Agrar Simulator Logo">
        </div>
        <div class="auth-card">
            <div class="auth-header">
                <h2>Registrieren</h2>
            </div>

            <form action="<?= BASE_URL ?>/register" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="form-group">
                    <label for="username">Benutzername</label>
                    <input type="text" id="username" name="username" required autofocus
                           placeholder="3-20 Zeichen, Buchstaben, Zahlen, _"
                           pattern="[a-zA-Z0-9_]{3,20}">
                    <small>Nur Buchstaben, Zahlen und Unterstriche. 3-20 Zeichen.</small>
                </div>

                <div class="form-group">
                    <label for="email">E-Mail-Adresse</label>
                    <input type="email" id="email" name="email" required
                           placeholder="deine@email.de">
                </div>

                <div class="form-group">
                    <label for="password">Passwort</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Mindestens 8 Zeichen" minlength="8">
                    <small>Mindestens 8 Zeichen</small>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Passwort bestätigen</label>
                    <input type="password" id="password_confirm" name="password_confirm" required
                           placeholder="Passwort wiederholen">
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
                <p>Bereits registriert? <a href="<?= BASE_URL ?>/login">Jetzt anmelden</a></p>
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

.auth-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.auth-header h2 {
    margin: 0;
    color: var(--color-text);
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
    transition: border-color 0.2s;
}

.auth-form input:focus {
    outline: none;
    border-color: var(--color-primary);
}

.auth-form input::placeholder {
    color: var(--color-text-secondary);
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
    font-size: 1rem;
}

.auth-footer {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--color-border);
}

.auth-footer p {
    margin: 0;
    color: var(--color-text-secondary);
}

.auth-footer a {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 500;
}

.auth-footer a:hover {
    text-decoration: underline;
}

@media (max-width: 480px) {
    .register-page {
        padding: 1rem;
    }

    .auth-card {
        padding: 1.5rem;
    }
}
</style>
