<div class="login-page">
    <div class="login-container">
        <!-- Linke Seite: Login -->
        <div class="login-section">
            <div class="auth-logo">
                <img src="<?= BASE_URL ?>/img/logo_lsbg.png" alt="LSBG Agrar Simulator Logo">
            </div>
            <div class="auth-card">
                <div class="auth-header">
                    <h2>Anmelden</h2>
                </div>

                <form action="<?= BASE_URL ?>/login" method="POST" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="form-group">
                        <label for="username">Benutzername oder E-Mail</label>
                        <input type="text" id="username" name="username" required autofocus
                               placeholder="Dein Benutzername oder E-Mail">
                    </div>

                    <div class="form-group">
                        <label for="password">Passwort</label>
                        <input type="password" id="password" name="password" required
                               placeholder="Dein Passwort">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Anmelden</button>
                </form>

                <div class="auth-footer">
                    <p>Noch kein Konto? <a href="<?= BASE_URL ?>/register">Jetzt registrieren</a></p>
                </div>

                <div class="auth-discord">
                    <a href="https://discord.com/invite/S53gZ6Rg9C" target="_blank" class="btn btn-discord">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                        </svg>
                        Discord beitreten
                    </a>
                </div>
            </div>
        </div>

        <!-- Rechte Seite: News & Changelog -->
        <div class="news-section">
            <!-- News -->
            <div class="news-card">
                <div class="news-header">
                    <h3>Neuigkeiten</h3>
                </div>
                <div class="news-content">
                    <?php if (empty($news)): ?>
                        <p class="no-content">Keine Neuigkeiten vorhanden.</p>
                    <?php else: ?>
                        <?php foreach ($news as $post): ?>
                            <div class="news-item">
                                <div class="news-item-header">
                                    <span class="news-title"><?= htmlspecialchars($post['title']) ?></span>
                                    <?php if ($post['is_pinned']): ?>
                                        <span class="pinned-badge">Angepinnt</span>
                                    <?php endif; ?>
                                </div>
                                <div class="news-item-meta">
                                    <span class="news-date"><?= date('d.m.Y', strtotime($post['created_at'])) ?></span>
                                    <span class="news-author">von <?= htmlspecialchars($post['admin_name'] ?? 'Admin') ?></span>
                                </div>
                                <div class="news-item-content">
                                    <?= nl2br(htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 150))) ?>
                                    <?php if (strlen($post['content']) > 150): ?>...<?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Changelog -->
            <div class="changelog-card">
                <div class="changelog-header">
                    <h3>Changelog</h3>
                </div>
                <div class="changelog-content">
                    <?php if (empty($changelog)): ?>
                        <p class="no-content">Kein Changelog vorhanden.</p>
                    <?php else: ?>
                        <?php foreach ($changelog as $entry): ?>
                            <div class="changelog-item">
                                <div class="changelog-item-header">
                                    <span class="changelog-version"><?= htmlspecialchars($entry['title']) ?></span>
                                    <span class="changelog-date"><?= date('d.m.Y', strtotime($entry['created_at'])) ?></span>
                                </div>
                                <div class="changelog-item-content">
                                    <?= nl2br(htmlspecialchars(mb_substr(strip_tags($entry['content']), 0, 100))) ?>
                                    <?php if (strlen($entry['content']) > 100): ?>...<?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.login-page {
    min-height: 100vh;
    background: var(--color-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.login-container {
    display: flex;
    gap: 3rem;
    max-width: 1200px;
    width: 100%;
    align-items: flex-start;
}

/* Login Section */
.login-section {
    flex: 0 0 380px;
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

.auth-discord {
    margin-top: 1rem;
    text-align: center;
}

.btn-discord {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    background: #5865F2;
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.2s;
}

.btn-discord:hover {
    background: #4752C4;
}

.btn-discord svg {
    flex-shrink: 0;
}

/* News Section */
.news-section {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    max-height: 80vh;
    overflow-y: auto;
}

.news-card,
.changelog-card {
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border);
    border-radius: 12px;
    overflow: hidden;
}

.news-header,
.changelog-header {
    padding: 1rem 1.25rem;
    background: var(--color-bg);
    border-bottom: 1px solid var(--color-border);
}

.news-header h3,
.changelog-header h3 {
    margin: 0;
    font-size: 1.1rem;
    color: var(--color-text);
}

.news-content,
.changelog-content {
    padding: 1rem 1.25rem;
    max-height: 300px;
    overflow-y: auto;
}

.no-content {
    color: var(--color-text-secondary);
    text-align: center;
    padding: 1rem;
    margin: 0;
}

/* News Items */
.news-item {
    padding: 1rem 0;
    border-bottom: 1px solid var(--color-border);
}

.news-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.news-item:first-child {
    padding-top: 0;
}

.news-item-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
}

.news-title {
    font-weight: 600;
    color: var(--color-text);
}

.pinned-badge {
    background: var(--color-primary);
    color: white;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.news-item-meta {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
    margin-bottom: 0.5rem;
}

.news-item-meta .news-date {
    margin-right: 0.5rem;
}

.news-item-content {
    font-size: 0.9rem;
    color: var(--color-text-secondary);
    line-height: 1.5;
}

/* Changelog Items */
.changelog-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--color-border);
}

.changelog-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.changelog-item:first-child {
    padding-top: 0;
}

.changelog-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.25rem;
}

.changelog-version {
    font-weight: 600;
    color: var(--color-primary);
}

.changelog-date {
    font-size: 0.8rem;
    color: var(--color-text-secondary);
}

.changelog-item-content {
    font-size: 0.85rem;
    color: var(--color-text-secondary);
    line-height: 1.4;
}

/* Responsive */
@media (max-width: 900px) {
    .login-container {
        flex-direction: column;
        align-items: center;
    }

    .login-section {
        flex: none;
        width: 100%;
        max-width: 400px;
    }

    .news-section {
        width: 100%;
        max-height: none;
    }
}

@media (max-width: 480px) {
    .login-page {
        padding: 1rem;
    }

    .auth-card {
        padding: 1.5rem;
    }
}
</style>
