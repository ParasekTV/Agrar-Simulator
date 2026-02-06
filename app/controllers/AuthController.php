<?php
/**
 * Auth Controller
 *
 * Verwaltet Authentifizierung: Login, Logout, Registrierung.
 */
class AuthController extends Controller
{
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

        $data = $this->getPostData();

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
            // Automatisch einloggen
            Session::set('user_id', $result['user_id']);
            Session::set('farm_id', $result['farm_id']);
            Session::set('username', $data['username']);

            Session::setFlash('success', 'Willkommen auf deiner neuen Farm!', 'success');
            $this->redirect('/dashboard');
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
}
