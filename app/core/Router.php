<?php
/**
 * Router
 *
 * Einfaches URL-Routing für die Anwendung.
 */
class Router
{
    private array $routes = [];
    private array $apiRoutes = [];
    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath;
    }

    /**
     * Registriert eine GET-Route
     */
    public function get(string $path, string $controller, string $action): self
    {
        $this->addRoute('GET', $path, $controller, $action);
        return $this;
    }

    /**
     * Registriert eine POST-Route
     */
    public function post(string $path, string $controller, string $action): self
    {
        $this->addRoute('POST', $path, $controller, $action);
        return $this;
    }

    /**
     * Registriert eine API-Route (JSON-Response)
     */
    public function api(string $method, string $path, string $controller, string $action): self
    {
        $this->apiRoutes[] = [
            'method' => strtoupper($method),
            'path' => '/api' . $path,
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }

    private function addRoute(string $method, string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Verarbeitet die aktuelle Anfrage
     */
    public function dispatch(): void
    {
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];

        // Aktualisiere Benutzer-Aktivität für Ranking Online-Status
        Session::updateActivity();

        // Prüfe API-Routen zuerst
        if (str_starts_with($uri, '/api/')) {
            $this->handleApiRequest($uri, $method);
            return;
        }

        // Prüfe normale Routen
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchRoute($route['path'], $uri);
            if ($params !== false) {
                $this->callAction($route['controller'], $route['action'], $params);
                return;
            }
        }

        // 404 - Nicht gefunden
        $this->notFound();
    }

    private function handleApiRequest(string $uri, string $method): void
    {
        header('Content-Type: application/json; charset=utf-8');

        foreach ($this->apiRoutes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchRoute($route['path'], $uri);
            if ($params !== false) {
                $this->callApiAction($route['controller'], $route['action'], $params);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'API-Endpunkt nicht gefunden']);
    }

    private function matchRoute(string $routePath, string $uri): array|false
    {
        // Konvertiere Route-Parameter zu Regex
        $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // Filtere nur benannte Gruppen
            return array_filter($matches, fn($key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    private function callAction(string $controllerName, string $action, array $params): void
    {
        $controllerClass = $controllerName . 'Controller';

        if (!class_exists($controllerClass)) {
            $this->notFound("Controller {$controllerClass} nicht gefunden");
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            $this->notFound("Action {$action} nicht gefunden");
            return;
        }

        call_user_func_array([$controller, $action], $params);
    }

    private function callApiAction(string $controllerName, string $action, array $params): void
    {
        $controllerClass = $controllerName . 'Controller';

        if (!class_exists($controllerClass)) {
            http_response_code(404);
            echo json_encode(['error' => 'Controller nicht gefunden']);
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            echo json_encode(['error' => 'Action nicht gefunden']);
            return;
        }

        try {
            $result = call_user_func_array([$controller, $action], $params);
            if ($result !== null) {
                echo json_encode($result);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => DEBUG_MODE ? $e->getMessage() : 'Interner Serverfehler'
            ]);
        }
    }

    private function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Entferne Query-String
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Entferne Basis-Pfad
        if ($this->basePath && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
        }

        // Stelle sicher, dass URI mit / beginnt
        if (!str_starts_with($uri, '/')) {
            $uri = '/' . $uri;
        }

        // Entferne trailing slash (ausser bei root)
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        return $uri;
    }

    private function notFound(string $message = 'Seite nicht gefunden'): void
    {
        http_response_code(404);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $message]);
        } else {
            include VIEWS_PATH . '/errors/404.php';
        }
    }

    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Leitet zu einer anderen URL um
     */
    public static function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . $url);
        exit;
    }
}
