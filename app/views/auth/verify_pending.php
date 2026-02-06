<div class="verify-page">
    <div class="verify-container">
        <div class="auth-logo">
            <img src="<?= BASE_URL ?>/img/logo_lsbg.png" alt="LSBG Agrar Simulator Logo">
        </div>
        <div class="verify-card">
            <div class="verify-icon">&#9993;</div>
            <h2>E-Mail bestätigen</h2>
            <p>
                Wir haben dir eine E-Mail mit einem Aktivierungslink geschickt.
                Bitte prüfe deinen Posteingang und klicke auf den Link, um deinen Account zu aktivieren.
            </p>

            <div class="verify-tips">
                <h4>Keine E-Mail erhalten?</h4>
                <ul>
                    <li>Prüfe deinen Spam-Ordner</li>
                    <li>Warte einige Minuten</li>
                    <li>Stelle sicher, dass die E-Mail-Adresse korrekt ist</li>
                </ul>
            </div>

            <div class="verify-actions">
                <a href="<?= BASE_URL ?>/auth/verify/resend" class="btn btn-outline">
                    Link erneut senden
                </a>
                <a href="<?= BASE_URL ?>/login" class="btn btn-primary">
                    Zurück zum Login
                </a>
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
    max-width: 500px;
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
    padding: 2.5rem;
    text-align: center;
}

.verify-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.verify-card h2 {
    margin: 0 0 1rem;
    color: var(--color-text);
}

.verify-card p {
    color: var(--color-text-secondary);
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.verify-tips {
    background: var(--color-bg);
    border-radius: 8px;
    padding: 1rem 1.5rem;
    text-align: left;
    margin-bottom: 1.5rem;
}

.verify-tips h4 {
    margin: 0 0 0.5rem;
    font-size: 0.9rem;
    color: var(--color-text);
}

.verify-tips ul {
    margin: 0;
    padding-left: 1.25rem;
    color: var(--color-text-secondary);
    font-size: 0.875rem;
}

.verify-tips li {
    margin-bottom: 0.25rem;
}

.verify-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.verify-actions .btn {
    flex: 1;
    max-width: 200px;
}

@media (max-width: 480px) {
    .verify-actions {
        flex-direction: column;
    }

    .verify-actions .btn {
        max-width: none;
    }
}
</style>
