<div class="verify-page">
    <div class="verify-container">
        <div class="auth-logo">
            <img src="<?= BASE_URL ?>/img/logo_lsbg.png" alt="LSBG Agrar Simulator Logo">
        </div>
        <div class="verify-card error">
            <div class="verify-icon">&#9888;</div>
            <h2>Link abgelaufen</h2>
            <p><?= htmlspecialchars($message ?? 'Der Aktivierungslink ist leider abgelaufen oder ungültig.') ?></p>

            <div class="verify-actions">
                <a href="<?= BASE_URL ?>/auth/verify/resend" class="btn btn-primary">
                    Neuen Link anfordern
                </a>
                <a href="<?= BASE_URL ?>/login" class="btn btn-outline">
                    Zum Login
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

.verify-card.error {
    border-color: var(--color-danger);
}

.verify-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.verify-card.error .verify-icon {
    color: var(--color-danger);
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
