<?php
/**
 * Account Controller
 *
 * Verwaltet die Account-Einstellungen des Spielers.
 */
class AccountController extends Controller
{
    /**
     * Zeigt Account-Verwaltung
     */
    public function index(): void
    {
        $this->requireAuth();

        $accountModel = new Account();
        $user = $accountModel->getAccountInfo(Session::getUserId());

        if (!$user) {
            Session::setFlash('error', 'Benutzer nicht gefunden', 'danger');
            $this->redirect('/dashboard');
        }

        $this->renderWithLayout('account/index', [
            'title' => 'Account-Verwaltung',
            'user' => $user
        ]);
    }

    /**
     * Ändert das Passwort (POST)
     */
    public function changePassword(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/account');
        }

        $data = $this->getPostData();

        // Validierung
        if (empty($data['current_password'])) {
            Session::setFlash('error', 'Aktuelles Passwort erforderlich', 'danger');
            $this->redirect('/account');
        }

        if (empty($data['new_password']) || strlen($data['new_password']) < 8) {
            Session::setFlash('error', 'Neues Passwort muss mindestens 8 Zeichen haben', 'danger');
            $this->redirect('/account');
        }

        if ($data['new_password'] !== ($data['confirm_password'] ?? '')) {
            Session::setFlash('error', 'Passwörter stimmen nicht überein', 'danger');
            $this->redirect('/account');
        }

        $accountModel = new Account();
        $result = $accountModel->changePassword(
            Session::getUserId(),
            $data['current_password'],
            $data['new_password']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/account');
    }

    /**
     * Fordert E-Mail-Änderung an (POST)
     */
    public function requestEmailChange(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/account');
        }

        $data = $this->getPostData();

        // Validierung
        if (empty($data['new_email']) || !filter_var($data['new_email'], FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Gültige E-Mail-Adresse erforderlich', 'danger');
            $this->redirect('/account');
        }

        if (empty($data['password'])) {
            Session::setFlash('error', 'Passwort zur Bestätigung erforderlich', 'danger');
            $this->redirect('/account');
        }

        $accountModel = new Account();
        $result = $accountModel->requestEmailChange(
            Session::getUserId(),
            Validator::sanitizeString($data['new_email']),
            $data['password']
        );

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/account');
    }

    /**
     * Bestätigt E-Mail-Änderung (GET)
     */
    public function confirmEmailChange(string $token): void
    {
        $accountModel = new Account();
        $result = $accountModel->confirmEmailChange($token);

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        if (Session::isLoggedIn()) {
            $this->redirect('/account');
        } else {
            $this->redirect('/login');
        }
    }

    /**
     * Fordert Account-Löschung an (POST)
     */
    public function requestDeletion(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/account');
        }

        $data = $this->getPostData();

        // Checkbox muss aktiviert sein
        if (empty($data['confirm_deletion'])) {
            Session::setFlash('error', 'Bitte bestätige die Löschung', 'danger');
            $this->redirect('/account');
        }

        $accountModel = new Account();
        $result = $accountModel->requestDeletion(Session::getUserId());

        if ($result['success'] && !empty($result['logout'])) {
            // Benutzer ausloggen
            $user = new User();
            $user->logout();

            Session::setFlash('warning', $result['message'], 'warning');
            $this->redirect('/login');
        } else {
            Session::setFlash('error', $result['message'], 'danger');
            $this->redirect('/account');
        }
    }

    /**
     * Bricht Account-Löschung ab (POST)
     */
    public function cancelDeletion(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/account');
        }

        $accountModel = new Account();
        $result = $accountModel->cancelDeletion(Session::getUserId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/account');
    }

    /**
     * Schaltet Urlaubsmodus um (POST)
     */
    public function toggleVacation(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/account');
        }

        $data = $this->getPostData();
        $enable = ($data['action'] ?? '') === 'enable';

        $accountModel = new Account();
        $result = $accountModel->setVacationMode(Session::getUserId(), $enable);

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/account');
    }

    /**
     * Lädt Profilbild hoch (POST)
     */
    public function uploadPicture(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/account');
        }

        if (empty($_FILES['picture'])) {
            Session::setFlash('error', 'Keine Datei ausgewählt', 'danger');
            $this->redirect('/account');
        }

        $accountModel = new Account();
        $result = $accountModel->uploadProfilePicture(Session::getUserId(), $_FILES['picture']);

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/account');
    }

    /**
     * Löscht Profilbild (POST)
     */
    public function deletePicture(): void
    {
        $this->requireAuth();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Sitzung abgelaufen', 'danger');
            $this->redirect('/account');
        }

        $accountModel = new Account();
        $result = $accountModel->deleteProfilePicture(Session::getUserId());

        Session::setFlash(
            $result['success'] ? 'success' : 'error',
            $result['message'],
            $result['success'] ? 'success' : 'danger'
        );

        $this->redirect('/account');
    }

    /**
     * Zeigt öffentliches Spielerprofil
     */
    public function profile(int $id): void
    {
        $this->requireAuth();

        $accountModel = new Account();
        $player = $accountModel->getPublicProfile($id);

        if (!$player) {
            Session::setFlash('error', 'Spieler nicht gefunden', 'danger');
            $this->redirect('/rankings');
        }

        $this->renderWithLayout('account/profile', [
            'title' => $player['username'] . ' - Profil',
            'player' => $player,
            'stats' => $player['stats']
        ]);
    }
}
