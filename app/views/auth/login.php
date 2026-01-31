<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1>&#127806; Farming Simulator</h1>
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
    </div>
</div>
