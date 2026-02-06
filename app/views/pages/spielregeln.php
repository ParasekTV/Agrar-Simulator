<div class="legal-page">
    <div class="legal-container">
        <h1>Spielregeln</h1>

        <section class="legal-section">
            <h2>1. Respektvoller Umgang</h2>
            <p>
                Wir erwarten von allen Spielern einen respektvollen und freundlichen Umgang miteinander. Behandle andere so, wie du selbst behandelt werden möchtest. Beleidigungen, Drohungen und respektloses Verhalten werden nicht toleriert.
            </p>
        </section>

        <section class="legal-section">
            <h2>2. Keine Hassrede</h2>
            <p>
                Folgende Inhalte sind strengstens untersagt:
            </p>
            <ul class="rules-list">
                <li><strong>Rassismus:</strong> Diskriminierung aufgrund von Herkunft, Hautfarbe oder Ethnie</li>
                <li><strong>Sexismus:</strong> Diskriminierung aufgrund des Geschlechts</li>
                <li><strong>Diskriminierung:</strong> Benachteiligung aufgrund von Religion, Behinderung, sexueller Orientierung oder anderen persönlichen Merkmalen</li>
                <li><strong>Mobbing:</strong> Systematisches Belästigen, Ausgrenzen oder Schikanieren anderer Spieler</li>
            </ul>
            <p>
                Dies gilt für alle Bereiche des Spiels, einschließlich Farmnamen, Nachrichten, Genossenschaften und Forum.
            </p>
        </section>

        <section class="legal-section">
            <h2>3. Keine Politik und Religion</h2>
            <p>
                Politische und religiöse Themen sind in folgenden Bereichen nicht gestattet:
            </p>
            <ul class="rules-list">
                <li>Farmnamen und Genossenschaftsnamen</li>
                <li>Nachrichten und Chat</li>
                <li>Genossenschaftsbeschreibungen</li>
                <li>Forum und Kommentare</li>
            </ul>
            <p>
                Diese Regel dient dazu, Konflikte zu vermeiden und eine neutrale Spielumgebung zu gewährleisten.
            </p>
        </section>

        <section class="legal-section">
            <h2>4. Faires Spielen</h2>
            <p>
                Wir erwarten von allen Spielern faires und ehrliches Spielverhalten:
            </p>
            <ul class="rules-list">
                <li><strong>Keine Cheats:</strong> Die Verwendung von Cheats, Hacks oder manipulierter Software ist verboten</li>
                <li><strong>Keine Bots:</strong> Automatisierte Programme oder Scripts zur Spielmanipulation sind nicht erlaubt</li>
                <li><strong>Keine Exploits:</strong> Das Ausnutzen von Spielfehlern oder Bugs zum eigenen Vorteil ist untersagt. Bitte melde gefundene Bugs stattdessen</li>
            </ul>
        </section>

        <section class="legal-section">
            <h2>5. Ein Account pro Person</h2>
            <p>
                Jeder Spieler darf nur einen Account besitzen. Mehrfach-Accounts (Multi-Accounts) sind nicht erlaubt und werden ohne Vorwarnung gesperrt. Dies gilt auch für Accounts, die zum Transfer von Ressourcen oder zur Manipulation des Marktplatzes verwendet werden.
            </p>
        </section>

        <section class="legal-section">
            <h2>6. Konsequenzen bei Regelverstößen</h2>
            <p>
                Bei Verstößen gegen die Spielregeln behalten wir uns folgende Maßnahmen vor:
            </p>
            <ul class="rules-list consequences">
                <li>
                    <span class="consequence-level warning">Stufe 1</span>
                    <strong>Verwarnung:</strong> Bei erstmaligen oder leichten Verstößen erhältst du eine Verwarnung
                </li>
                <li>
                    <span class="consequence-level temp-ban">Stufe 2</span>
                    <strong>Temporäre Sperre:</strong> Bei wiederholten Verstößen kann dein Account zeitweise gesperrt werden
                </li>
                <li>
                    <span class="consequence-level perm-ban">Stufe 3</span>
                    <strong>Permanente Löschung:</strong> Bei schweren oder wiederholten Verstößen wird dein Account dauerhaft gelöscht
                </li>
            </ul>
            <p>
                Bei besonders schweren Verstößen behalten wir uns vor, direkt zur Account-Löschung überzugehen.
            </p>
        </section>

        <section class="legal-section">
            <h2>7. Änderungen der Spielregeln</h2>
            <p>
                Diese Spielregeln können jederzeit angepasst werden. Änderungen werden im Changelog bekannt gegeben. Es liegt in der Verantwortung jedes Spielers, sich über die aktuellen Regeln zu informieren.
            </p>
            <p class="last-updated">
                <em>Stand: <?= date('d.m.Y') ?></em>
            </p>
        </section>

        <div class="legal-footer">
            <a href="<?= BASE_URL ?>/login" class="btn btn-outline">&larr; Zurück</a>
        </div>
    </div>
</div>

<style>
.legal-page {
    min-height: 100vh;
    background: var(--color-bg);
    padding: 2rem;
}

.legal-container {
    max-width: 800px;
    margin: 0 auto;
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    padding: 2.5rem;
}

.legal-container h1 {
    margin: 0 0 2rem;
    color: var(--color-text);
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--color-primary);
}

.legal-section {
    margin-bottom: 2rem;
}

.legal-section h2 {
    color: var(--color-text);
    font-size: 1.25rem;
    margin: 0 0 1rem;
}

.legal-section p {
    color: var(--color-text-secondary);
    line-height: 1.7;
    margin: 0 0 1rem;
}

.rules-list {
    color: var(--color-text-secondary);
    line-height: 1.7;
    padding-left: 1.5rem;
    margin: 0 0 1rem;
}

.rules-list li {
    margin-bottom: 0.5rem;
}

.rules-list.consequences {
    list-style: none;
    padding-left: 0;
}

.rules-list.consequences li {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: var(--color-bg);
    border-radius: 8px;
}

.consequence-level {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

.consequence-level.warning {
    background: #fef3cd;
    color: #856404;
}

.consequence-level.temp-ban {
    background: #ffe5d0;
    color: #984c0c;
}

.consequence-level.perm-ban {
    background: #f8d7da;
    color: #721c24;
}

.last-updated {
    color: var(--color-text-muted);
    font-size: 0.875rem;
}

.legal-footer {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--color-border);
}

@media (max-width: 600px) {
    .legal-container {
        padding: 1.5rem;
    }

    .rules-list.consequences li {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>
