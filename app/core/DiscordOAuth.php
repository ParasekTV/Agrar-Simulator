<?php
/**
 * Discord OAuth2 Integration
 *
 * Ermöglicht Login/Registrierung über Discord.
 */
class DiscordOAuth
{
    private const AUTHORIZE_URL = 'https://discord.com/api/oauth2/authorize';
    private const TOKEN_URL = 'https://discord.com/api/oauth2/token';
    private const USER_URL = 'https://discord.com/api/users/@me';
    private const REVOKE_URL = 'https://discord.com/api/oauth2/token/revoke';

    /**
     * Prüft ob Discord OAuth aktiviert ist
     */
    public static function isEnabled(): bool
    {
        return defined('DISCORD_OAUTH_ENABLED') && DISCORD_OAUTH_ENABLED === true;
    }

    /**
     * Gibt die Client ID zurück
     */
    public static function getClientId(): string
    {
        return defined('DISCORD_CLIENT_ID') ? DISCORD_CLIENT_ID : '';
    }

    /**
     * Gibt die Client Secret zurück
     */
    private static function getClientSecret(): string
    {
        return defined('DISCORD_CLIENT_SECRET') ? DISCORD_CLIENT_SECRET : '';
    }

    /**
     * Gibt die Redirect URI zurück
     */
    public static function getRedirectUri(): string
    {
        if (defined('DISCORD_REDIRECT_URI') && !empty(DISCORD_REDIRECT_URI)) {
            return DISCORD_REDIRECT_URI;
        }

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        return rtrim($baseUrl, '/') . '/auth/discord/callback';
    }

    /**
     * Generiert die Authorization URL
     *
     * @param string $state CSRF-Schutz Token
     * @return string Die URL zum Discord Login
     */
    public static function getAuthUrl(string $state = ''): string
    {
        if (!self::isEnabled()) {
            return '';
        }

        // State generieren wenn nicht angegeben
        if (empty($state)) {
            $state = bin2hex(random_bytes(16));
            $_SESSION['discord_oauth_state'] = $state;
        }

        $params = [
            'client_id' => self::getClientId(),
            'redirect_uri' => self::getRedirectUri(),
            'response_type' => 'code',
            'scope' => 'identify email',
            'state' => $state,
            'prompt' => 'consent' // Immer Bestätigung anfordern
        ];

        return self::AUTHORIZE_URL . '?' . http_build_query($params);
    }

    /**
     * Validiert den State-Parameter gegen CSRF
     */
    public static function validateState(string $state): bool
    {
        $storedState = $_SESSION['discord_oauth_state'] ?? '';
        unset($_SESSION['discord_oauth_state']);

        return !empty($state) && hash_equals($storedState, $state);
    }

    /**
     * Tauscht Authorization Code gegen Access Token
     *
     * @param string $code Der Authorization Code von Discord
     * @return array|null Token-Daten oder null bei Fehler
     */
    public static function exchangeCode(string $code): ?array
    {
        if (!self::isEnabled()) {
            return null;
        }

        $data = [
            'client_id' => self::getClientId(),
            'client_secret' => self::getClientSecret(),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => self::getRedirectUri()
        ];

        $ch = curl_init(self::TOKEN_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            Logger::error('Discord Token Exchange fehlgeschlagen', [
                'http_code' => $httpCode,
                'response' => $response
            ]);
            return null;
        }

        $result = json_decode($response, true);

        if (!$result || isset($result['error'])) {
            Logger::error('Discord Token Error', ['error' => $result['error'] ?? 'Unknown']);
            return null;
        }

        return [
            'access_token' => $result['access_token'] ?? '',
            'refresh_token' => $result['refresh_token'] ?? '',
            'expires_in' => $result['expires_in'] ?? 0,
            'token_type' => $result['token_type'] ?? 'Bearer',
            'scope' => $result['scope'] ?? ''
        ];
    }

    /**
     * Holt User-Daten von Discord
     *
     * @param string $accessToken Der Access Token
     * @return array|null User-Daten oder null bei Fehler
     */
    public static function getUser(string $accessToken): ?array
    {
        $ch = curl_init(self::USER_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            Logger::error('Discord User Fetch fehlgeschlagen', ['http_code' => $httpCode]);
            return null;
        }

        $user = json_decode($response, true);

        if (!$user || isset($user['message'])) {
            Logger::error('Discord User Error', ['error' => $user['message'] ?? 'Unknown']);
            return null;
        }

        return [
            'id' => $user['id'] ?? '',
            'username' => $user['username'] ?? '',
            'global_name' => $user['global_name'] ?? $user['username'] ?? '',
            'discriminator' => $user['discriminator'] ?? '0',
            'email' => $user['email'] ?? '',
            'verified' => $user['verified'] ?? false,
            'avatar' => $user['avatar'] ?? null,
            'avatar_url' => self::getAvatarUrl($user['id'] ?? '', $user['avatar'] ?? null)
        ];
    }

    /**
     * Generiert die Avatar-URL
     */
    public static function getAvatarUrl(string $userId, ?string $avatarHash): string
    {
        if (empty($avatarHash)) {
            // Standard-Avatar basierend auf Discriminator
            $defaultIndex = ((int)$userId >> 22) % 6;
            return "https://cdn.discordapp.com/embed/avatars/{$defaultIndex}.png";
        }

        $extension = str_starts_with($avatarHash, 'a_') ? 'gif' : 'png';
        return "https://cdn.discordapp.com/avatars/{$userId}/{$avatarHash}.{$extension}";
    }

    /**
     * Refresht einen Access Token
     *
     * @param string $refreshToken Der Refresh Token
     * @return array|null Neue Token-Daten oder null bei Fehler
     */
    public static function refreshToken(string $refreshToken): ?array
    {
        $data = [
            'client_id' => self::getClientId(),
            'client_secret' => self::getClientSecret(),
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ];

        $ch = curl_init(self::TOKEN_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        $result = json_decode($response, true);
        return $result ?: null;
    }

    /**
     * Widerruft einen Token
     */
    public static function revokeToken(string $token): bool
    {
        $data = [
            'client_id' => self::getClientId(),
            'client_secret' => self::getClientSecret(),
            'token' => $token
        ];

        $ch = curl_init(self::REVOKE_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    /**
     * Generiert einen sicheren Username aus Discord-Daten
     */
    public static function generateUsername(array $discordUser): string
    {
        $baseName = $discordUser['global_name'] ?? $discordUser['username'] ?? 'User';

        // Nur alphanumerische Zeichen und Unterstriche erlauben
        $baseName = preg_replace('/[^a-zA-Z0-9_]/', '', $baseName);

        // Mindestens 3 Zeichen
        if (strlen($baseName) < 3) {
            $baseName = 'User' . substr($discordUser['id'], -4);
        }

        // Maximum 20 Zeichen
        $baseName = substr($baseName, 0, 20);

        return $baseName;
    }
}
