<?php
/**
 * Base Controller
 *
 * Basis-Klasse für alle Controller.
 */
abstract class Controller
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Rendert eine View
     */
    protected function render(string $view, array $data = []): void
    {
        // Extrahiere Daten für die View
        extract($data);

        // Hole CSRF-Token
        $csrfToken = Session::generateCsrfToken();

        // Hole Flash-Nachrichten
        $flashes = Session::getAllFlashes();

        // Lade View
        $viewFile = VIEWS_PATH . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View {$view} nicht gefunden");
        }

        include $viewFile;
    }

    /**
     * Rendert eine View mit Layout
     */
    protected function renderWithLayout(string $view, array $data = [], string $layout = 'main'): void
    {
        // Extrahiere Daten für die View
        extract($data);

        // Hole CSRF-Token
        $csrfToken = Session::generateCsrfToken();

        // Hole Flash-Nachrichten
        $flashes = Session::getAllFlashes();

        // Hole eingeloggten Benutzer
        $currentUser = null;
        $currentFarm = null;

        if (Session::isLoggedIn()) {
            $currentUser = $this->db->fetchOne(
                'SELECT * FROM users WHERE id = ?',
                [Session::getUserId()]
            );
            $currentFarm = $this->db->fetchOne(
                'SELECT * FROM farms WHERE user_id = ?',
                [Session::getUserId()]
            );
        }

        // Capture View-Inhalt
        ob_start();
        $viewFile = VIEWS_PATH . '/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new Exception("View {$view} nicht gefunden");
        }
        include $viewFile;
        $content = ob_get_clean();

        // Lade Layout
        $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new Exception("Layout {$layout} nicht gefunden");
        }
        include $layoutFile;
    }

    /**
     * Sendet eine JSON-Antwort
     */
    protected function json(array $data, int $statusCode = 200): array
    {
        http_response_code($statusCode);
        return $data;
    }

    /**
     * Sendet eine Erfolgs-JSON-Antwort
     */
    protected function jsonSuccess(string $message, array $data = []): array
    {
        return $this->json(array_merge(['success' => true, 'message' => $message], $data));
    }

    /**
     * Sendet eine Fehler-JSON-Antwort
     */
    protected function jsonError(string $message, int $statusCode = 400): array
    {
        return $this->json(['success' => false, 'message' => $message], $statusCode);
    }

    /**
     * Leitet um
     */
    protected function redirect(string $url): void
    {
        Router::redirect($url);
    }

    /**
     * Prüft ob Benutzer eingeloggt ist
     */
    protected function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Bitte melde dich an', 'warning');
            $this->redirect('/login');
        }
    }

    /**
     * Prüft ob Benutzer Gäste ist
     */
    protected function requireGuest(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
    }

    /**
     * Validiert CSRF-Token
     */
    protected function validateCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        return Session::validateCsrfToken($token);
    }

    /**
     * Holt POST-Daten
     */
    protected function getPostData(): array
    {
        return $_POST;
    }

    /**
     * Holt JSON-Body-Daten
     */
    protected function getJsonData(): array
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }

    /**
     * Holt GET-Parameter
     */
    protected function getQueryParam(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Holt die aktuelle Farm-ID
     */
    protected function getFarmId(): ?int
    {
        return Session::getFarmId();
    }
}
