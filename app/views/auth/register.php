<?= ReCaptcha::getScriptTag() ?>

<div class="register-page">
    <div class="register-container">
        <div class="auth-logo">
            <img src="<?= BASE_URL ?>/img/logo_lsbg.png" alt="LSBG Agrar Simulator Logo">
        </div>
        <div class="auth-card">
            <div class="auth-header">
                <h2>Registrieren</h2>
            </div>

            <?php if (DiscordOAuth::isEnabled()): ?>
                <a href="<?= BASE_URL ?>/auth/discord" class="btn btn-discord btn-block">
                    <svg width="20" height="20" viewBox="0 0 71 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M60.1045 4.8978C55.5792 2.8214 50.7265 1.2916 45.6527 0.41542C45.5603 0.39851 45.468 0.440769 45.4204 0.525289C44.7963 1.6353 44.105 3.0834 43.6209 4.2216C38.1637 3.4046 32.7345 3.4046 27.3892 4.2216C26.905 3.0581 26.1886 1.6353 25.5617 0.525289C25.5141 0.443589 25.4218 0.40133 25.3294 0.41542C20.2584 1.2888 15.4057 2.8186 10.8776 4.8978C10.8384 4.9147 10.8048 4.9429 10.7825 4.9795C1.57795 18.7309 -0.943561 32.1443 0.293408 45.3914C0.299005 45.4562 0.335386 45.5182 0.385761 45.5576C6.45866 50.0174 12.3413 52.7249 18.1147 54.5195C18.2071 54.5477 18.305 54.5139 18.3638 54.4378C19.7295 52.5728 20.9469 50.6063 21.9907 48.5383C22.0523 48.4172 21.9935 48.2735 21.8676 48.2256C19.9366 47.4931 18.0979 46.6 16.3292 45.5858C16.1893 45.5041 16.1781 45.304 16.3068 45.2082C16.679 44.9293 17.0513 44.6391 17.4067 44.3461C17.471 44.2926 17.5606 44.2813 17.6362 44.3151C29.2558 49.6202 41.8354 49.6202 53.3179 44.3151C53.3## 44.2785 53.4831 44.2898 53.5502 44.3433C53.9057 44.6363 54.2779 44.9293 54.6529 45.2082C54.7816 45.304 54.7732 45.5041 54.6333 45.5858C52.8646 46.6197 51.0259 47.4931 49.0921 48.2228C48.9662 48.2707 48.9102 48.4172 48.9718 48.5383C50.0385 50.6034 51.2## 52.5699 52.5765 54.435C52.6324 54.5139 52.7331 54.5765 52.8256 54.5195C58.6247 52.7249 64.5073 50.0174 70.5802 45.5576C70.6334 45.5182 70.667 45.459 70.6726 45.3942C72.1666 29.9999 68.2112 16.7199 60.1482 4.9823C60.1287 4.9429 60.0951 4.9147 60.1045 4.8978ZM23.7259 37.3253C20.2276 37.3253 17.3451 34.1136 17.3451 30.1693C17.3451 26.225 20.1717 23.0133 23.7259 23.0133C27.308 23.0133 30.1626 26.2532 30.1099 30.1693C30.1099 34.1136 27.2680 37.3253 23.7259 37.3253ZM47.3178 37.3253C43.8196 37.3253 40.9370 34.1136 40.9370 30.1693C40.9370 26.225 43.7636 23.0133 47.3178 23.0133C50.8998 23.0133 53.7545 26.2532 53.7017 30.1693C53.7017 34.1136 50.8998 37.3253 47.3178 37.3253Z" fill="currentColor"/>
                    </svg>
                    Mit Discord registrieren
                </a>

                <div class="auth-divider">
                    <span>oder mit E-Mail</span>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/register" method="POST" class="auth-form" id="register-form">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="recaptcha_token" id="recaptcha_token">

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
                    <?php if (Mailer::isVerificationEnabled()): ?>
                        <small>Du erhältst einen Aktivierungslink per E-Mail.</small>
                    <?php endif; ?>
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

                <?php if (ReCaptcha::isEnabled()): ?>
                    <p class="recaptcha-notice">
                        Diese Seite ist durch reCAPTCHA geschützt.
                        <a href="https://policies.google.com/privacy" target="_blank">Datenschutz</a> &
                        <a href="https://policies.google.com/terms" target="_blank">Nutzungsbedingungen</a>
                    </p>
                <?php endif; ?>
            </form>

            <div class="auth-footer">
                <p>Bereits registriert? <a href="<?= BASE_URL ?>/login">Jetzt anmelden</a></p>
            </div>
        </div>
    </div>
</div>

<?= ReCaptcha::getFormScript('register-form', 'register', 'recaptcha_token') ?>

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

.btn-discord {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    background: #5865F2;
    color: white;
    padding: 0.875rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: background-color 0.2s;
}

.btn-discord:hover {
    background: #4752C4;
    color: white;
}

.btn-discord svg {
    flex-shrink: 0;
}

.auth-divider {
    display: flex;
    align-items: center;
    margin: 1.5rem 0;
    color: var(--color-text-secondary);
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--color-border);
}

.auth-divider span {
    padding: 0 1rem;
    font-size: 0.875rem;
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

.recaptcha-notice {
    text-align: center;
    font-size: 0.75rem;
    color: var(--color-text-secondary);
    margin-top: 1rem;
}

.recaptcha-notice a {
    color: var(--color-text-secondary);
    text-decoration: underline;
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
