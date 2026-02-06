<div class="bugreport-page">
    <div class="page-header">
        <h1>Bug melden</h1>
        <p class="page-description">Hast du einen Fehler gefunden? Melde ihn uns!</p>
    </div>

    <div class="bugreport-container">
        <div class="bugreport-form-card">
            <form action="<?= BASE_URL ?>/bugreport/submit" method="POST" class="bugreport-form">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="form-group">
                    <label for="title">Titel des Bugs</label>
                    <input type="text" id="title" name="title" required
                           placeholder="Kurze Beschreibung des Problems"
                           minlength="5" maxlength="200">
                    <small>Beschreibe das Problem in wenigen Worten (5-200 Zeichen)</small>
                </div>

                <div class="form-group">
                    <label for="description">Beschreibung</label>
                    <textarea id="description" name="description" rows="8" required
                              placeholder="Beschreibe den Bug so detailliert wie möglich:&#10;- Was hast du gemacht?&#10;- Was ist passiert?&#10;- Was hättest du erwartet?"
                              minlength="20"></textarea>
                    <small>Je mehr Details, desto besser können wir den Bug beheben (min. 20 Zeichen)</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Bug melden</button>
            </form>
        </div>

        <div class="bugreport-info-card">
            <div class="info-icon">&#128172;</div>
            <h3>Feedback im Discord</h3>
            <p>
                Deine Bug-Meldung wird automatisch in unserem Discord-Server gepostet.
                Dort kannst du Feedback zu deiner Meldung erhalten und mit den Entwicklern diskutieren.
            </p>
            <a href="https://discord.com/invite/S53gZ6Rg9C" target="_blank" class="btn btn-discord">
                <span class="discord-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                    </svg>
                </span>
                Discord beitreten
            </a>
        </div>
    </div>
</div>

<style>
.bugreport-page {
    padding: 1rem;
    max-width: 900px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    margin-bottom: 0.5rem;
}

.page-description {
    color: var(--color-text-secondary);
    margin: 0;
}

.bugreport-container {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.5rem;
    align-items: start;
}

.bugreport-form-card,
.bugreport-info-card {
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 1.5rem;
}

.bugreport-form .form-group {
    margin-bottom: 1.25rem;
}

.bugreport-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--color-text);
}

.bugreport-form input,
.bugreport-form textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    background: var(--color-bg);
    color: var(--color-text);
    font-size: 1rem;
    font-family: inherit;
    transition: border-color 0.2s;
}

.bugreport-form input:focus,
.bugreport-form textarea:focus {
    outline: none;
    border-color: var(--color-primary);
}

.bugreport-form textarea {
    resize: vertical;
    min-height: 150px;
}

.bugreport-form small {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.bugreport-info-card {
    text-align: center;
}

.info-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.bugreport-info-card h3 {
    margin: 0 0 0.75rem 0;
    color: var(--color-text);
}

.bugreport-info-card p {
    color: var(--color-text-secondary);
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1.25rem;
}

.btn-discord {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #5865F2;
    color: white;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.2s;
}

.btn-discord:hover {
    background: #4752C4;
}

.discord-icon {
    display: flex;
    align-items: center;
}

@media (max-width: 768px) {
    .bugreport-container {
        grid-template-columns: 1fr;
    }

    .bugreport-info-card {
        order: -1;
    }
}
</style>
