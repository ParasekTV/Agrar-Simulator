<div class="legal-page">
    <div class="legal-container">
        <h1>Impressum</h1>

        <section class="legal-section">
            <h2>Angaben gemäß § 5 TMG</h2>
            <p>
                <strong>Name und Anschrift:</strong><br>
                Florian Müller<br>
                Am Winkel 14<br>
                35708 Haiger-Steinbach<br>
                Deutschland
            </p>
        </section>

        <section class="legal-section">
            <h2>Kontakt</h2>
            <p>
                <strong>E-Mail:</strong> <a href="mailto:info@sl-wide.de">info@sl-wide.de</a><br>
                <strong>Telefon:</strong> +49 170 9326875 (Kein Support)<br>
                <strong>Discord:</strong> <a href="https://discord.com/invite/S53gZ6Rg9C" target="_blank">discord.com/invite/S53gZ6Rg9C</a>
            </p>
        </section>

        <section class="legal-section">
            <h2>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h2>
            <p>
                Florian Müller<br>
                Am Winkel 14<br>
                35708 Haiger-Steinbach
            </p>
        </section>

        <section class="legal-section">
            <h2>Haftungsausschluss</h2>

            <h3>Haftung für Inhalte</h3>
            <p>
                Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt. Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte können wir jedoch keine Gewähr übernehmen. Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich.
            </p>

            <h3>Haftung für Links</h3>
            <p>
                Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich.
            </p>
        </section>

        <section class="legal-section">
            <h2>Urheberrecht</h2>
            <p>
                Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers.
            </p>
        </section>

        <section class="legal-section">
            <h2>Streitschlichtung</h2>
            <p>
                Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit:
                <a href="https://ec.europa.eu/consumers/odr" target="_blank">https://ec.europa.eu/consumers/odr</a>.
            </p>
            <p>
                Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.
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

.legal-section h3 {
    color: var(--color-text);
    font-size: 1rem;
    margin: 1rem 0 0.5rem;
}

.legal-section p {
    color: var(--color-text-secondary);
    line-height: 1.7;
    margin: 0 0 1rem;
}

.legal-section a {
    color: var(--color-primary);
    text-decoration: none;
}

.legal-section a:hover {
    text-decoration: underline;
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
}
</style>
