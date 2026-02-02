<div class="auth-container">
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
