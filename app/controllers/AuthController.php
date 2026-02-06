<?php
/**
 * Auth Controller
 *
 * Verwaltet Authentifizierung: Login, Logout, Registrierung.
 * Unterstützt reCAPTCHA, E-Mail-Verifizierung und Discord OAuth.
 */
class AuthController extends Controller
{
    /**
     * Rate Limiting prüfen
     */
    private function checkRateLimit(string $action, int $maxAttempts, int $windowSeconds): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (empty($ip)) return true;

        $db = Database::getInstance();

        // Alte Einträge bereinigen
        $db->query(
            "DELETE FROM rate_limits WHERE first_attempt_at < DATE_SUB(NOW(), INTERVAL ? SECOND)",
            [$windowSeconds]
        );

        // Aktuelle Attempts prüfen
        $record = $db->fetchOne(
            "SELECT * FROM rate_limits WHERE ip_address = ? AND action = ?",
            [$ip, $action]
        );

        if (!$record) {
            $db->insert('rate_limits', [
                'ip_address' => $ip,
                'action' => $action,
                'attempts' => 1
            ]);
            return true;
        }

        if ($record['attempts'] >= $maxAttempts) {
            return false;
        }

        $db->query(
            "UPDATE rate_limits SET attempts = attempts + 1 WHERE id = ?",
            [$record['id']]
        );

        return true;
    }

    /**
     * Zeigt Login-Seite
     */
    public function loginForm(): void
    {
        $this->requireGuest();

        // Hole öffentliche News und Changelog für Gäste
        $newsModel = new News();
        $newsData = $newsModel->getPublicPosts(5);
        $changelogData = $newsModel->getPublicChangelog(5);

        $this->renderWithLayout('auth/login', [
            'title' => 'Anmelden',
            'news' => $newsData,
            'changelog' => $changelogData
        ]);
    }

    /**
     * Verarbeitet Login
     */
    public function login(): void
    {
        $this->requireGuest();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen. Bitte erneut versuchen.', 'danger');
            $this->redirect('/login');
        }

        // Rate Limiting
        if (!$this->checkRateLimit('login', 10, 3600)) {
            Session::setFlash('error', 'Zu viele Anmeldeversuche. Bitte warte eine Stunde.', 'danger');
            $this->redirect('/login');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('username', 'Benutzername erforderlich')
            ->required('password', 'Passwort erforderlich');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/login');
        }

        $user = new User();
        $result = $user->login($data['username'], $data['password']);

        if ($result['success']) {
            // Prüfe E-Mail-Verifizierung
            if (Mailer::isVerificationEnabled() && isset($result['is_verified']) && !$result['is_verified']) {
                Session::set('pending_verification_user_id', $result['user_id']);
                Session::setFlash('warning', 'Bitte bestätige zuerst deine E-Mail-Adresse.', 'warning');
                $this->redirect('/auth/verify/pending');
            }

            Session::setFlash('success', $result['message'], 'success');
            $this->redirect('/dashboard');
        } else {
            Session::setFlash('error', $result['message'], 'danger');
            $this->redirect('/login');
        }
    }

    /**
     * Zeigt Registrierungs-Seite
     */
    public function registerForm(): void
    {
        $this->requireGuest();
        $this->renderWithLayout('auth/register', ['title' => 'Registrieren']);
    }

    /**
     * Verarbeitet Registrierung
     */
    public function register(): void
    {
        $this->requireGuest();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen. Bitte erneut versuchen.', 'danger');
            $this->redirect('/register');
        }

        // Rate Limiting
        if (!$this->checkRateLimit('register', 5, 86400)) {
            Session::setFlash('error', 'Zu viele Registrierungsversuche. Bitte versuche es morgen erneut.', 'danger');
            $this->redirect('/register');
        }

        $data = $this->getPostData();

        // reCAPTCHA prüfen
        if (ReCaptcha::isEnabled()) {
            $recaptchaToken = $data['recaptcha_token'] ?? '';
            $recaptchaResult = ReCaptcha::verify($recaptchaToken, 'register');

            if (!$recaptchaResult['success']) {
                Session::setFlash('error', $recaptchaResult['message'], 'danger');
                $this->redirect('/register');
            }
        }

        $validator = new Validator($data);
        $validator
            ->required('username', 'Benutzername erforderlich')
            ->username('username')
            ->required('email', 'E-Mail erforderlich')
            ->email('email')
            ->required('password', 'Passwort erforderlich')
            ->minLength('password', 8, 'Passwort muss mindestens 8 Zeichen lang sein')
            ->required('password_confirm', 'Passwort-Bestätigung erforderlich')
            ->matches('password_confirm', 'password', 'Passwörter stimmen nicht überein')
            ->required('farm_name', 'Farm-Name erforderlich')
            ->minLength('farm_name', 3, 'Farm-Name muss mindestens 3 Zeichen lang sein')
            ->maxLength('farm_name', 50, 'Farm-Name darf maximal 50 Zeichen lang sein');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/register');
        }

        $user = new User();
        $result = $user->register(
            Validator::sanitizeString($data['username']),
            Validator::sanitizeString($data['email']),
            $data['password'],
            Validator::sanitizeString($data['farm_name'])
        );

        if ($result['success']) {
            // E-Mail-Verifizierung senden wenn aktiviert
            if (Mailer::isVerificationEnabled()) {
                $token = $user->createVerificationToken($result['user_id']);
                Mailer::sendVerificationEmail($data['email'], $data['username'], $token);

                Session::setFlash('success', 'Registrierung erfolgreich! Bitte prüfe deine E-Mails.', 'success');
                $this->redirect('/auth/verify/pending');
            } else {
                // Direkt einloggen wenn keine Verifizierung nötig
                Session::set('user_id', $result['user_id']);
                Session::set('farm_id', $result['farm_id']);
                Session::set('username', $data['username']);

                Session::setFlash('success', 'Willkommen auf deiner neuen Farm!', 'success');
                $this->redirect('/dashboard');
            }
        } else {
            Session::setFlash('error', $result['message'], 'danger');
            $this->redirect('/register');
        }
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        $user = new User();
        $user->logout();

        Session::setFlash('success', 'Erfolgreich abgemeldet', 'success');
        $this->redirect('/login');
    }

    /**
     * API: Prüft Login-Status
     */
    public function checkApi(): array
    {
        return $this->json([
            'logged_in' => Session::isLoggedIn(),
            'user_id' => Session::getUserId(),
            'farm_id' => Session::getFarmId()
        ]);
    }

    // ==========================================
    // E-MAIL VERIFIZIERUNG
    // ==========================================

    /**
     * Zeigt "Bitte E-Mail prüfen" Seite
     */
    public function verifyPending(): void
    {
        $this->renderWithLayout('auth/verify_pending', [
            'title' => 'E-Mail bestätigen'
        ]);
    }

    /**
     * Verifiziert E-Mail mit Token
     */
    public function verify(string $token): void
    {
        $user = new User();
        $result = $user->verifyEmail($token);

        if ($result['success']) {
            // Automatisch einloggen
            Session::set('user_id', $result['user_id']);
            Session::set('farm_id', $result['farm_id']);
            Session::set('username', $result['username']);

            Session::setFlash('success', 'E-Mail erfolgreich bestätigt! Willkommen!', 'success');
            $this->redirect('/dashboard');
        } else {
            $this->renderWithLayout('auth/verify_expired', [
                'title' => 'Link abgelaufen',
                'message' => $result['message']
            ]);
        }
    }

    /**
     * Zeigt Formular zum erneuten Senden
     */
    public function resendVerificationForm(): void
    {
        $this->renderWithLayout('auth/resend_verification', [
            'title' => 'Aktivierungslink erneut senden'
        ]);
    }

    /**
     * Sendet Verifizierungs-E-Mail erneut
     */
    public function resendVerification(): void
    {
        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Ungültige Anfrage', 'danger');
            $this->redirect('/auth/verify/resend');
        }

        // Rate Limiting
        if (!$this->checkRateLimit('resend_verification', 3, 3600)) {
            Session::setFlash('error', 'Zu viele Anfragen. Bitte warte eine Stunde.', 'danger');
            $this->redirect('/auth/verify/resend');
        }

        $data = $this->getPostData();
        $email = Validator::sanitizeString($data['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Bitte gib eine gültige E-Mail-Adresse ein.', 'danger');
            $this->redirect('/auth/verify/resend');
        }

        $user = new User();
        $userData = $this->db->fetchOne(
            'SELECT id, username, is_verified FROM users WHERE email = ?',
            [$email]
        );

        if ($userData && !$userData['is_verified']) {
            $token = $user->createVerificationToken($userData['id']);
            Mailer::sendVerificationEmail($email, $userData['username'], $token);
        }

        // Immer gleiche Nachricht (Sicherheit)
        Session::setFlash('success', 'Falls ein Account mit dieser E-Mail existiert, wurde ein neuer Link gesendet.', 'success');
        $this->redirect('/auth/verify/pending');
    }

    // ==========================================
    // DISCORD OAUTH
    // ==========================================

    /**
     * Startet Discord OAuth Flow
     */
    public function discordAuth(): void
    {
        if (!DiscordOAuth::isEnabled()) {
            Session::setFlash('error', 'Discord-Login ist nicht aktiviert.', 'danger');
            $this->redirect('/login');
        }

        $authUrl = DiscordOAuth::getAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Discord OAuth Callback
     */
    public function discordCallback(): void
    {
        if (!DiscordOAuth::isEnabled()) {
            Session::setFlash('error', 'Discord-Login ist nicht aktiviert.', 'danger');
            $this->redirect('/login');
        }

        $code = $_GET['code'] ?? '';
        $state = $_GET['state'] ?? '';
        $error = $_GET['error'] ?? '';

        // Fehler von Discord
        if (!empty($error)) {
            Logger::warning('Discord OAuth abgebrochen', ['error' => $error]);
            Session::setFlash('error', 'Discord-Anmeldung abgebrochen.', 'danger');
            $this->redirect('/login');
        }

        // State validieren (CSRF-Schutz)
        if (!DiscordOAuth::validateState($state)) {
            Session::setFlash('error', 'Ungültige Anfrage. Bitte erneut versuchen.', 'danger');
            $this->redirect('/login');
        }

        // Code gegen Token tauschen
        $tokens = DiscordOAuth::exchangeCode($code);
        if (!$tokens) {
            Session::setFlash('error', 'Discord-Authentifizierung fehlgeschlagen.', 'danger');
            $this->redirect('/login');
        }

        // User-Daten von Discord abrufen
        $discordUser = DiscordOAuth::getUser($tokens['access_token']);
        if (!$discordUser) {
            Session::setFlash('error', 'Discord-Benutzerdaten konnten nicht abgerufen werden.', 'danger');
            $this->redirect('/login');
        }

        $user = new User();

        // Prüfen ob Discord-Account bereits verknüpft
        $existingUser = $user->findByDiscordId($discordUser['id']);

        if ($existingUser) {
            // Einloggen
            Session::set('user_id', $existingUser['id']);
            Session::set('farm_id', $existingUser['farm_id']);
            Session::set('username', $existingUser['username']);

            // Discord-Daten aktualisieren
            $user->updateDiscordData($existingUser['id'], $discordUser);

            Session::setFlash('success', 'Willkommen zurück, ' . $existingUser['username'] . '!', 'success');
            $this->redirect('/dashboard');
            return;
        }

        // Prüfen ob eingeloggt (Account verknüpfen)
        if (Session::isLoggedIn()) {
            $result = $user->linkDiscord(Session::getUserId(), $discordUser);
            if ($result['success']) {
                Session::setFlash('success', 'Discord-Account erfolgreich verknüpft!', 'success');
            } else {
                Session::setFlash('error', $result['message'], 'danger');
            }
            $this->redirect('/settings');
            return;
        }

        // Neuen Account erstellen
        // Prüfen ob E-Mail bereits verwendet
        if (!empty($discordUser['email'])) {
            $emailCheck = $this->db->fetchOne(
                'SELECT id FROM users WHERE email = ?',
                [$discordUser['email']]
            );

            if ($emailCheck) {
                Session::setFlash('error', 'Diese E-Mail-Adresse ist bereits registriert. Bitte melde dich an und verknüpfe deinen Discord-Account.', 'warning');
                $this->redirect('/login');
                return;
            }
        }

        // Discord-Daten in Session speichern für Registrierung
        Session::set('discord_registration', $discordUser);
        $this->redirect('/auth/discord/complete');
    }

    /**
     * Zeigt Formular zur Vervollständigung der Discord-Registrierung
     */
    public function discordComplete(): void
    {
        $discordUser = Session::get('discord_registration');

        if (!$discordUser) {
            $this->redirect('/login');
        }

        $suggestedUsername = DiscordOAuth::generateUsername($discordUser);

        $this->renderWithLayout('auth/discord_complete', [
            'title' => 'Registrierung abschließen',
            'discord' => $discordUser,
            'suggestedUsername' => $suggestedUsername
        ]);
    }

    /**
     * Verarbeitet Discord-Registrierung
     */
    public function discordRegister(): void
    {
        $discordUser = Session::get('discord_registration');

        if (!$discordUser) {
            $this->redirect('/login');
        }

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Ungültige Anfrage', 'danger');
            $this->redirect('/auth/discord/complete');
        }

        $data = $this->getPostData();

        $validator = new Validator($data);
        $validator
            ->required('username', 'Benutzername erforderlich')
            ->username('username')
            ->required('farm_name', 'Farm-Name erforderlich')
            ->minLength('farm_name', 3, 'Farm-Name muss mindestens 3 Zeichen lang sein')
            ->maxLength('farm_name', 50, 'Farm-Name darf maximal 50 Zeichen lang sein');

        if (!$validator->isValid()) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $this->redirect('/auth/discord/complete');
        }

        $user = new User();
        $result = $user->createFromDiscord(
            Validator::sanitizeString($data['username']),
            $discordUser['email'] ?? '',
            Validator::sanitizeString($data['farm_name']),
            $discordUser
        );

        if ($result['success']) {
            Session::remove('discord_registration');

            Session::set('user_id', $result['user_id']);
            Session::set('farm_id', $result['farm_id']);
            Session::set('username', $data['username']);

            Session::setFlash('success', 'Willkommen auf deiner neuen Farm!', 'success');
            $this->redirect('/dashboard');
        } else {
            Session::setFlash('error', $result['message'], 'danger');
            $this->redirect('/auth/discord/complete');
        }
    }

    /**
     * Entfernt Discord-Verknüpfung
     */
    public function unlinkDiscord(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Ungültige Anfrage', 'danger');
            $this->redirect('/settings');
        }

        $user = new User();
        $userData = $this->db->fetchOne(
            'SELECT password_hash FROM users WHERE id = ?',
            [Session::getUserId()]
        );

        // Prüfen ob User ein Passwort hat (sonst kann er sich nicht mehr einloggen)
        if (empty($userData['password_hash'])) {
            Session::setFlash('error', 'Du musst zuerst ein Passwort setzen, bevor du Discord trennen kannst.', 'danger');
            $this->redirect('/settings');
        }

        $result = $user->unlinkDiscord(Session::getUserId());

        if ($result['success']) {
            Session::setFlash('success', 'Discord-Verknüpfung entfernt.', 'success');
        } else {
            Session::setFlash('error', $result['message'], 'danger');
        }

        $this->redirect('/settings');
    }
}
