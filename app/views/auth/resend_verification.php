<div class="verify-page">
    <div class="verify-container">
        <div class="auth-logo">
            <img src="<?= BASE_URL ?>/img/logo_lsbg.png" alt="LSBG Agrar Simulator Logo">
        </div>
        <div class="verify-card">
            <h2>Aktivierungslink erneut senden</h2>
            <p>Gib deine E-Mail-Adresse ein, um einen neuen Aktivierungslink zu erhalten.</p>

            <form action="<?= BASE_URL ?>/auth/verify/resend" method="POST" class="resend-form">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="form-group">
                    <label for="email">E-Mail-Adresse</label>
                    <input type="email" id="email" name="email" required
                           placeholder="deine@email.de" autofocus>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Neuen Link senden
                </button>
            </form>

            <div class="verify-footer">
                <a href="<?= BASE_URL ?>/login">Zurück zum Login</a>
            </div>
        </div>
    </div>
</div>

<style>
.verify-page {
    min-height: 100vh;
    background: var(--color-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.verify-container {
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

.verify-card {
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 2rem;
}

.verify-card h2 {
    margin: 0 0 0.5rem;
    color: var(--color-text);
    text-align: center;
}

.verify-card p {
    color: var(--color-text-secondary);
    text-align: center;
    margin-bottom: 1.5rem;
}

.resend-form .form-group {
    margin-bottom: 1.25rem;
}

.resend-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--color-text);
}

.resend-form input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    background: var(--color-bg);
    color: var(--color-text);
    font-size: 1rem;
}

.resend-form input:focus {
    outline: none;
    border-color: var(--color-primary);
}

.btn-block {
    width: 100%;
    padding: 0.875rem;
}

.verify-footer {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--color-border);
}

.verify-footer a {
    color: var(--color-primary);
    text-decoration: none;
}

.verify-footer a:hover {
    text-decoration: underline;
}
</style>
