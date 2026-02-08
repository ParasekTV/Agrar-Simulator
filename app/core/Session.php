<?php
/**
 * Session Management
 *
 * Sichere Session-Verwaltung mit CSRF-Schutz.
 */
class Session
{
    private static bool $started = false;

    /**
     * Startet die Session
     */
    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        // Sichere Session-Einstellungen
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        // HTTPS-Only Cookie in Produktion
        if (!DEBUG_MODE && isset($_SERVER['HTTPS'])) {
            ini_set('session.cookie_secure', '1');
        }

        session_name(SESSION_NAME);
        session_start();

        self::$started = true;

        // Regeneriere Session-ID periodisch
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 1800) {
            // 30 Minuten
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }

    /**
     * Setzt einen Session-Wert
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Holt einen Session-Wert
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Prüft ob ein Session-Wert existiert
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Entfernt einen Session-Wert
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Zerstört die Session komplett
     */
    public static function destroy(): void
    {
        self::start();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }

    /**
     * Generiert ein CSRF-Token
     */
    public static function generateCsrfToken(): string
    {
        self::start();

        if (!isset($_SESSION['csrf_token']) ||
            !isset($_SESSION['csrf_token_time']) ||
            time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Validiert ein CSRF-Token
     */
    public static function validateCsrfToken(?string $token): bool
    {
        self::start();

        if ($token === null || !isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Setzt eine Flash-Nachricht (einmalig angezeigt)
     */
    public static function setFlash(string $key, string $message, string $type = 'info'): void
    {
        self::start();
        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type' => $type
        ];
    }

    /**
     * Holt und entfernt eine Flash-Nachricht
     */
    public static function getFlash(string $key): ?array
    {
        self::start();

        if (!isset($_SESSION['flash'][$key])) {
            return null;
        }

        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);

        return $flash;
    }

    /**
     * Prüft ob eine Flash-Nachricht existiert
     */
    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['flash'][$key]);
    }

    /**
     * Holt alle Flash-Nachrichten und löscht sie
     */
    public static function getAllFlashes(): array
    {
        self::start();

        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        return $flashes;
    }

    /**
     * Prüft ob der Benutzer eingeloggt ist
     */
    public static function isLoggedIn(): bool
    {
        return self::get('user_id') !== null;
    }

    /**
     * Holt die User-ID des eingeloggten Benutzers
     */
    public static function getUserId(): ?int
    {
        return self::get('user_id');
    }

    /**
     * Holt die Farm-ID des eingeloggten Benutzers
     */
    public static function getFarmId(): ?int
    {
        return self::get('farm_id');
    }

    /**
     * Aktualisiert die letzte Aktivität des eingeloggten Benutzers
     * Wird nur alle 5 Minuten aktualisiert um DB-Last zu reduzieren
     */
    public static function updateActivity(): void
    {
        $userId = self::getUserId();
        if ($userId === null) {
            return;
        }

        $lastUpdate = self::get('last_activity_update', 0);
        $now = time();

        // Nur alle 5 Minuten aktualisieren
        if ($now - $lastUpdate < 300) {
            return;
        }

        self::set('last_activity_update', $now);

        // Aktualisiere in der Datenbank
        Ranking::updateUserActivity($userId);
    }
}
