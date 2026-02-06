<?php
/**
 * User Model
 *
 * Verwaltet Benutzer-Accounts und Authentifizierung.
 */
class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Registriert einen neuen Benutzer
     */
    public function register(string $username, string $email, string $password, string $farmName): array
    {
        // Prüfe ob Username bereits existiert
        if ($this->usernameExists($username)) {
            return ['success' => false, 'message' => 'Benutzername bereits vergeben'];
        }

        // Prüfe ob Email bereits existiert
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'E-Mail-Adresse bereits registriert'];
        }

        $this->db->beginTransaction();

        try {
            // Erstelle Benutzer
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            $userId = $this->db->insert('users', [
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash
            ]);

            // Erstelle Farm
            $farmId = $this->db->insert('farms', [
                'user_id' => $userId,
                'farm_name' => $farmName,
                'money' => STARTING_MONEY
            ]);

            // Erstelle Starter-Felder
            for ($i = 0; $i < STARTING_FIELDS; $i++) {
                $this->db->insert('fields', [
                    'farm_id' => $farmId,
                    'size_hectares' => STARTING_FIELD_SIZE,
                    'position_x' => $i * 100,
                    'position_y' => 0
                ]);
            }

            // Gib Starter-Fahrzeug (Alter Traktor)
            $starterVehicle = $this->db->fetchOne(
                'SELECT id FROM vehicles WHERE price = 0 LIMIT 1'
            );

            if ($starterVehicle) {
                $this->db->insert('farm_vehicles', [
                    'farm_id' => $farmId,
                    'vehicle_id' => $starterVehicle['id']
                ]);
            }

            // Starte automatisch erste Forschung
            $starterResearch = $this->db->fetchOne(
                'SELECT id FROM research_tree WHERE cost = 0 AND prerequisite_id IS NULL LIMIT 1'
            );

            if ($starterResearch) {
                $this->db->insert('farm_research', [
                    'farm_id' => $farmId,
                    'research_id' => $starterResearch['id'],
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Erstelle Ranking-Eintrag
            $this->db->insert('rankings', [
                'farm_id' => $farmId,
                'total_money' => STARTING_MONEY
            ]);

            $this->db->commit();

            Logger::info('New user registered', ['user_id' => $userId, 'username' => $username]);

            return [
                'success' => true,
                'message' => 'Registrierung erfolgreich',
                'user_id' => $userId,
                'farm_id' => $farmId
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            Logger::error('Registration failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Registrierung fehlgeschlagen'];
        }
    }

    /**
     * Authentifiziert einen Benutzer
     */
    public function login(string $usernameOrEmail, string $password): array
    {
        // Finde Benutzer
        $user = $this->db->fetchOne(
            'SELECT * FROM users WHERE username = ? OR email = ?',
            [$usernameOrEmail, $usernameOrEmail]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Benutzername oder Passwort falsch'];
        }

        // Prüfe ob Account aktiv ist
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Account ist deaktiviert'];
        }

        // Prüfe Passwort (kann leer sein bei Discord-only Accounts)
        if (empty($user['password_hash'])) {
            return ['success' => false, 'message' => 'Bitte melde dich mit Discord an'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Benutzername oder Passwort falsch'];
        }

        // Hole Farm
        $farm = $this->db->fetchOne(
            'SELECT * FROM farms WHERE user_id = ?',
            [$user['id']]
        );

        // Prüfe E-Mail-Verifizierung (gebe Info zurück, aber blockiere nicht hier)
        $isVerified = isset($user['is_verified']) ? (bool)$user['is_verified'] : true;

        if (!$isVerified) {
            return [
                'success' => true,
                'is_verified' => false,
                'user_id' => $user['id'],
                'message' => 'E-Mail nicht verifiziert'
            ];
        }

        // Aktualisiere letzten Login
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $user['id']]);

        // Setze Session
        Session::set('user_id', $user['id']);
        Session::set('farm_id', $farm['id']);
        Session::set('username', $user['username']);

        // Punkte für täglichen Login
        $this->checkDailyLogin($farm['id']);

        Logger::info('User logged in', ['user_id' => $user['id']]);

        return [
            'success' => true,
            'message' => 'Willkommen zurück!',
            'is_verified' => true,
            'user' => $user,
            'farm' => $farm
        ];
    }

    /**
     * Loggt den Benutzer aus
     */
    public function logout(): void
    {
        $userId = Session::getUserId();
        Session::destroy();
        Logger::info('User logged out', ['user_id' => $userId]);
    }

    /**
     * Prüft täglichen Login-Bonus
     */
    private function checkDailyLogin(int $farmId): void
    {
        $lastEvent = $this->db->fetchOne(
            "SELECT * FROM game_events
             WHERE farm_id = ? AND event_type = 'points' AND description LIKE 'Täglicher Login%'
             AND DATE(created_at) = CURDATE()",
            [$farmId]
        );

        if (!$lastEvent) {
            // Vergebe Login-Bonus
            $this->db->query(
                'UPDATE farms SET points = points + ? WHERE id = ?',
                [POINTS_DAILY_LOGIN, $farmId]
            );

            $this->db->insert('game_events', [
                'farm_id' => $farmId,
                'event_type' => 'points',
                'description' => 'Täglicher Login-Bonus',
                'points_earned' => POINTS_DAILY_LOGIN
            ]);
        }
    }

    /**
     * Findet einen Benutzer nach ID
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    /**
     * Findet einen Benutzer nach Username
     */
    public function findByUsername(string $username): ?array
    {
        return $this->db->fetchOne('SELECT * FROM users WHERE username = ?', [$username]);
    }

    /**
     * Prüft ob Username existiert
     */
    public function usernameExists(string $username): bool
    {
        return $this->db->exists('users', 'username = ?', [$username]);
    }

    /**
     * Prüft ob Email existiert
     */
    public function emailExists(string $email): bool
    {
        return $this->db->exists('users', 'email = ?', [$email]);
    }

    /**
     * Aktualisiert Benutzerdaten
     */
    public function update(int $userId, array $data): bool
    {
        $allowed = ['email', 'is_active'];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered)) {
            return false;
        }

        return $this->db->update('users', $filtered, 'id = :id', ['id' => $userId]) > 0;
    }

    /**
     * Ändert das Passwort
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $user = $this->findById($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'Benutzer nicht gefunden'];
        }

        // Bei Discord-only Accounts: Erlaube Passwort-Erstellung ohne altes Passwort
        if (!empty($user['password_hash']) && !password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Aktuelles Passwort ist falsch'];
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->db->update('users', ['password_hash' => $newHash], 'id = :id', ['id' => $userId]);

        Logger::info('Password changed', ['user_id' => $userId]);

        return ['success' => true, 'message' => 'Passwort erfolgreich geändert'];
    }

    // ==========================================
    // E-MAIL VERIFIZIERUNG
    // ==========================================

    /**
     * Erstellt einen Verifizierungs-Token
     */
    public function createVerificationToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expiryHours = defined('EMAIL_TOKEN_EXPIRY_HOURS') ? EMAIL_TOKEN_EXPIRY_HOURS : 24;
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours"));

        $this->db->update('users', [
            'verification_token' => $token,
            'token_expires_at' => $expiresAt,
            'is_verified' => 0
        ], 'id = :id', ['id' => $userId]);

        return $token;
    }

    /**
     * Verifiziert E-Mail mit Token
     */
    public function verifyEmail(string $token): array
    {
        if (empty($token) || strlen($token) !== 64) {
            return ['success' => false, 'message' => 'Ungültiger Token'];
        }

        $user = $this->db->fetchOne(
            'SELECT u.*, f.id as farm_id FROM users u
             LEFT JOIN farms f ON u.id = f.user_id
             WHERE u.verification_token = ?',
            [$token]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Ungültiger oder bereits verwendeter Link'];
        }

        // Prüfe Ablauf
        if (strtotime($user['token_expires_at']) < time()) {
            return ['success' => false, 'message' => 'Der Aktivierungslink ist abgelaufen'];
        }

        // Verifiziere User
        $this->db->update('users', [
            'is_verified' => 1,
            'verification_token' => null,
            'token_expires_at' => null
        ], 'id = :id', ['id' => $user['id']]);

        Logger::info('Email verified', ['user_id' => $user['id']]);

        return [
            'success' => true,
            'message' => 'E-Mail erfolgreich verifiziert',
            'user_id' => $user['id'],
            'farm_id' => $user['farm_id'],
            'username' => $user['username']
        ];
    }

    /**
     * Löscht abgelaufene Token
     */
    public function deleteExpiredTokens(): int
    {
        return $this->db->query(
            'UPDATE users SET verification_token = NULL, token_expires_at = NULL
             WHERE token_expires_at < NOW() AND verification_token IS NOT NULL'
        );
    }

    // ==========================================
    // DISCORD OAUTH
    // ==========================================

    /**
     * Findet User nach Discord ID
     */
    public function findByDiscordId(string $discordId): ?array
    {
        return $this->db->fetchOne(
            'SELECT u.*, f.id as farm_id FROM users u
             LEFT JOIN farms f ON u.id = f.user_id
             WHERE u.discord_id = ?',
            [$discordId]
        );
    }

    /**
     * Verknüpft Discord-Account mit bestehendem User
     */
    public function linkDiscord(int $userId, array $discordData): array
    {
        // Prüfe ob Discord-ID bereits verwendet
        $existing = $this->findByDiscordId($discordData['id']);
        if ($existing && $existing['id'] !== $userId) {
            return ['success' => false, 'message' => 'Dieser Discord-Account ist bereits mit einem anderen Konto verknüpft'];
        }

        $this->db->update('users', [
            'discord_id' => $discordData['id'],
            'discord_username' => $discordData['global_name'] ?? $discordData['username'],
            'discord_avatar' => $discordData['avatar_url'] ?? null
        ], 'id = :id', ['id' => $userId]);

        Logger::info('Discord linked', ['user_id' => $userId, 'discord_id' => $discordData['id']]);

        return ['success' => true, 'message' => 'Discord erfolgreich verknüpft'];
    }

    /**
     * Aktualisiert Discord-Daten bei Login
     */
    public function updateDiscordData(int $userId, array $discordData): void
    {
        $this->db->update('users', [
            'discord_username' => $discordData['global_name'] ?? $discordData['username'],
            'discord_avatar' => $discordData['avatar_url'] ?? null
        ], 'id = :id', ['id' => $userId]);
    }

    /**
     * Entfernt Discord-Verknüpfung
     */
    public function unlinkDiscord(int $userId): array
    {
        $this->db->update('users', [
            'discord_id' => null,
            'discord_username' => null,
            'discord_avatar' => null
        ], 'id = :id', ['id' => $userId]);

        Logger::info('Discord unlinked', ['user_id' => $userId]);

        return ['success' => true, 'message' => 'Discord-Verknüpfung entfernt'];
    }

    /**
     * Erstellt neuen User über Discord
     */
    public function createFromDiscord(string $username, string $email, string $farmName, array $discordData): array
    {
        // Prüfe ob Username bereits existiert
        if ($this->usernameExists($username)) {
            return ['success' => false, 'message' => 'Benutzername bereits vergeben'];
        }

        // Prüfe ob Discord-ID bereits verwendet
        if ($this->findByDiscordId($discordData['id'])) {
            return ['success' => false, 'message' => 'Dieser Discord-Account ist bereits registriert'];
        }

        // Prüfe E-Mail wenn vorhanden
        if (!empty($email) && $this->emailExists($email)) {
            return ['success' => false, 'message' => 'E-Mail-Adresse bereits registriert'];
        }

        $this->db->beginTransaction();

        try {
            // Erstelle Benutzer (ohne Passwort - nur Discord-Login)
            $userId = $this->db->insert('users', [
                'username' => $username,
                'email' => $email ?: null,
                'password_hash' => '', // Kein Passwort bei Discord-Registrierung
                'is_verified' => 1, // Discord verifiziert bereits
                'discord_id' => $discordData['id'],
                'discord_username' => $discordData['global_name'] ?? $discordData['username'],
                'discord_avatar' => $discordData['avatar_url'] ?? null
            ]);

            // Erstelle Farm (gleicher Code wie normale Registrierung)
            $farmId = $this->db->insert('farms', [
                'user_id' => $userId,
                'farm_name' => $farmName,
                'money' => STARTING_MONEY
            ]);

            // Erstelle Starter-Felder
            for ($i = 0; $i < STARTING_FIELDS; $i++) {
                $this->db->insert('fields', [
                    'farm_id' => $farmId,
                    'size_hectares' => STARTING_FIELD_SIZE,
                    'position_x' => $i * 100,
                    'position_y' => 0
                ]);
            }

            // Gib Starter-Fahrzeug
            $starterVehicle = $this->db->fetchOne(
                'SELECT id FROM vehicles WHERE cost = 0 LIMIT 1'
            );

            if ($starterVehicle) {
                $this->db->insert('farm_vehicles', [
                    'farm_id' => $farmId,
                    'vehicle_id' => $starterVehicle['id']
                ]);
            }

            // Starte automatisch erste Forschung
            $starterResearch = $this->db->fetchOne(
                'SELECT id FROM research_tree WHERE cost = 0 AND prerequisite_id IS NULL LIMIT 1'
            );

            if ($starterResearch) {
                $this->db->insert('farm_research', [
                    'farm_id' => $farmId,
                    'research_id' => $starterResearch['id'],
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Erstelle Ranking-Eintrag
            $this->db->insert('rankings', [
                'farm_id' => $farmId,
                'total_money' => STARTING_MONEY
            ]);

            $this->db->commit();

            Logger::info('User registered via Discord', [
                'user_id' => $userId,
                'username' => $username,
                'discord_id' => $discordData['id']
            ]);

            return [
                'success' => true,
                'message' => 'Registrierung erfolgreich',
                'user_id' => $userId,
                'farm_id' => $farmId
            ];

        } catch (Exception $e) {
            $this->db->rollback();
            Logger::error('Discord registration failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Registrierung fehlgeschlagen'];
        }
    }
}
