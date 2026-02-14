<?php
/**
 * Account Model
 *
 * Verwaltet Account-Funktionen: Passwort, E-Mail, Löschung, Urlaubsmodus, Profilbild.
 */
class Account
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // =========================================================================
    // PASSWORT ÄNDERN
    // =========================================================================

    /**
     * Ändert das Passwort des Benutzers
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $user = $this->db->fetchOne(
            'SELECT id, password FROM users WHERE id = ?',
            [$userId]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Benutzer nicht gefunden'];
        }

        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Aktuelles Passwort ist falsch'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Neues Passwort muss mindestens 8 Zeichen haben'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->db->query(
            'UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?',
            [$hashedPassword, $userId]
        );

        $this->logActivity($userId, 'password_change');

        return ['success' => true, 'message' => 'Passwort erfolgreich geändert'];
    }

    // =========================================================================
    // E-MAIL ÄNDERN
    // =========================================================================

    /**
     * Fordert eine E-Mail-Änderung an
     */
    public function requestEmailChange(int $userId, string $newEmail, string $password): array
    {
        // Passwort prüfen
        $user = $this->db->fetchOne(
            'SELECT id, password, email FROM users WHERE id = ?',
            [$userId]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Benutzer nicht gefunden'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Passwort ist falsch'];
        }

        // Prüfen ob E-Mail bereits verwendet
        $exists = $this->db->fetchOne(
            'SELECT id FROM users WHERE email = ? AND id != ?',
            [$newEmail, $userId]
        );

        if ($exists) {
            return ['success' => false, 'message' => 'Diese E-Mail-Adresse wird bereits verwendet'];
        }

        if ($user['email'] === $newEmail) {
            return ['success' => false, 'message' => 'Das ist bereits deine aktuelle E-Mail-Adresse'];
        }

        // Token generieren
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->db->query(
            'UPDATE users SET pending_email = ?, email_change_token = ?, email_change_expires_at = ? WHERE id = ?',
            [$newEmail, $token, $expiresAt, $userId]
        );

        // Bestätigungs-E-Mail senden
        $this->sendEmailChangeConfirmation($newEmail, $user['username'] ?? 'Spieler', $token);

        return [
            'success' => true,
            'message' => 'Bestätigungs-E-Mail wurde an ' . $newEmail . ' gesendet. Bitte prüfe dein Postfach.'
        ];
    }

    /**
     * Bestätigt die E-Mail-Änderung
     */
    public function confirmEmailChange(string $token): array
    {
        $user = $this->db->fetchOne(
            'SELECT id, pending_email, email_change_expires_at FROM users WHERE email_change_token = ?',
            [$token]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Ungültiger oder abgelaufener Link'];
        }

        if (strtotime($user['email_change_expires_at']) < time()) {
            // Token abgelaufen, bereinigen
            $this->db->query(
                'UPDATE users SET pending_email = NULL, email_change_token = NULL, email_change_expires_at = NULL WHERE id = ?',
                [$user['id']]
            );
            return ['success' => false, 'message' => 'Der Link ist abgelaufen. Bitte fordere einen neuen an.'];
        }

        // E-Mail aktualisieren
        $this->db->query(
            'UPDATE users SET email = ?, pending_email = NULL, email_change_token = NULL, email_change_expires_at = NULL, updated_at = NOW() WHERE id = ?',
            [$user['pending_email'], $user['id']]
        );

        $this->logActivity($user['id'], 'email_change');

        return ['success' => true, 'message' => 'E-Mail-Adresse erfolgreich geändert'];
    }

    // =========================================================================
    // ACCOUNT-LÖSCHUNG
    // =========================================================================

    /**
     * Markiert Account zur Löschung
     */
    public function requestDeletion(int $userId): array
    {
        $this->db->query(
            'UPDATE users SET deletion_requested = TRUE, deletion_requested_at = NOW() WHERE id = ?',
            [$userId]
        );

        $this->logActivity($userId, 'deletion_request');

        // E-Mail senden
        $user = $this->db->fetchOne('SELECT email, username FROM users WHERE id = ?', [$userId]);
        if ($user && !empty($user['email'])) {
            $this->sendDeletionConfirmationEmail($user['email'], $user['username']);
        }

        return [
            'success' => true,
            'message' => 'Dein Account wurde zur Löschung markiert und wird in 7 Tagen gelöscht. Wenn du dich in dieser Zeit wieder einloggst, wird die Löschung abgebrochen.',
            'logout' => true
        ];
    }

    /**
     * Bricht Löschungs-Anfrage ab
     */
    public function cancelDeletion(int $userId): array
    {
        $this->db->query(
            'UPDATE users SET deletion_requested = FALSE, deletion_requested_at = NULL WHERE id = ?',
            [$userId]
        );

        $this->logActivity($userId, 'deletion_cancel');

        return ['success' => true, 'message' => 'Account-Löschung wurde abgebrochen'];
    }

    // =========================================================================
    // URLAUBSMODUS
    // =========================================================================

    /**
     * Setzt Urlaubsmodus
     */
    public function setVacationMode(int $userId, bool $enabled): array
    {
        if ($enabled) {
            $this->db->query(
                'UPDATE users SET vacation_mode = TRUE, vacation_started_at = NOW() WHERE id = ?',
                [$userId]
            );
            $this->logActivity($userId, 'vacation_on');
            return ['success' => true, 'message' => 'Urlaubsmodus aktiviert'];
        } else {
            $this->db->query(
                'UPDATE users SET vacation_mode = FALSE, vacation_started_at = NULL WHERE id = ?',
                [$userId]
            );
            $this->logActivity($userId, 'vacation_off');
            return ['success' => true, 'message' => 'Urlaubsmodus deaktiviert'];
        }
    }

    // =========================================================================
    // PROFILBILD
    // =========================================================================

    /**
     * Lädt ein Profilbild hoch
     */
    public function uploadProfilePicture(int $userId, array $file): array
    {
        // Validierung
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'Datei zu groß (Server-Limit)',
                UPLOAD_ERR_FORM_SIZE => 'Datei zu groß',
                UPLOAD_ERR_PARTIAL => 'Datei nur teilweise hochgeladen',
                UPLOAD_ERR_NO_FILE => 'Keine Datei ausgewählt',
            ];
            $msg = $errorMessages[$file['error'] ?? 0] ?? 'Upload fehlgeschlagen';
            return ['success' => false, 'message' => $msg];
        }

        // Größe prüfen (3 MB)
        if ($file['size'] > 3 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Datei darf maximal 3 MB groß sein'];
        }

        // MIME-Type prüfen
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'message' => 'Nur JPG, PNG, GIF und WebP erlaubt'];
        }

        // Bildgröße prüfen und ggf. verkleinern
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'message' => 'Ungültiges Bildformat'];
        }

        list($width, $height) = $imageInfo;

        // Altes Bild löschen
        $this->deleteProfilePicture($userId);

        // Dateiendung
        $ext = match($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'jpg'
        };

        $filename = "avatar_{$userId}_" . time() . ".{$ext}";
        $uploadDir = ROOT_PATH . '/public/uploads/avatars/';

        // Verzeichnis erstellen falls nötig
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetPath = $uploadDir . $filename;

        // Bild verarbeiten (verkleinern auf max 124x124)
        if ($width > 124 || $height > 124) {
            $resized = $this->resizeImage($file['tmp_name'], $mimeType, 124, 124);
            if ($resized === false) {
                return ['success' => false, 'message' => 'Fehler beim Verarbeiten des Bildes'];
            }
            file_put_contents($targetPath, $resized);
        } else {
            // Bild direkt kopieren
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                return ['success' => false, 'message' => 'Fehler beim Speichern des Bildes'];
            }
        }

        // In Datenbank speichern
        $this->db->query(
            'UPDATE users SET profile_picture = ? WHERE id = ?',
            [$filename, $userId]
        );

        $this->logActivity($userId, 'profile_update', ['action' => 'picture_upload']);

        return ['success' => true, 'message' => 'Profilbild erfolgreich hochgeladen'];
    }

    /**
     * Löscht das Profilbild
     */
    public function deleteProfilePicture(int $userId): array
    {
        $user = $this->db->fetchOne('SELECT profile_picture FROM users WHERE id = ?', [$userId]);

        if ($user && !empty($user['profile_picture'])) {
            $filepath = ROOT_PATH . '/public/uploads/avatars/' . $user['profile_picture'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            $this->db->query(
                'UPDATE users SET profile_picture = NULL WHERE id = ?',
                [$userId]
            );

            $this->logActivity($userId, 'profile_update', ['action' => 'picture_delete']);
        }

        return ['success' => true, 'message' => 'Profilbild gelöscht'];
    }

    /**
     * Verkleinert ein Bild
     */
    private function resizeImage(string $sourcePath, string $mimeType, int $maxWidth, int $maxHeight): string|false
    {
        $sourceImage = match($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/gif' => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => false
        };

        if ($sourceImage === false) {
            return false;
        }

        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        // Seitenverhältnis beibehalten
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Transparenz für PNG/GIF/WebP beibehalten
        if (in_array($mimeType, ['image/png', 'image/gif', 'image/webp'])) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefill($newImage, 0, 0, $transparent);
        }

        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // In String ausgeben
        ob_start();
        match($mimeType) {
            'image/jpeg' => imagejpeg($newImage, null, 90),
            'image/png' => imagepng($newImage),
            'image/gif' => imagegif($newImage),
            'image/webp' => imagewebp($newImage, null, 90),
            default => imagejpeg($newImage, null, 90)
        };
        $imageData = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($newImage);

        return $imageData;
    }

    // =========================================================================
    // ACCOUNT-INFORMATIONEN
    // =========================================================================

    /**
     * Holt Account-Informationen
     */
    public function getAccountInfo(int $userId): ?array
    {
        return $this->db->fetchOne(
            'SELECT id, username, email, profile_picture, vacation_mode, vacation_started_at,
                    deletion_requested, deletion_requested_at, created_at, last_login,
                    last_activity_at, is_verified
             FROM users WHERE id = ?',
            [$userId]
        );
    }

    /**
     * Holt öffentliches Profil
     */
    public function getPublicProfile(int $userId): ?array
    {
        $user = $this->db->fetchOne(
            'SELECT u.id, u.username, u.profile_picture, u.vacation_mode, u.created_at, u.last_activity_at,
                    f.id as farm_id, f.farm_name, f.level, f.money, f.points
             FROM users u
             JOIN farms f ON f.user_id = u.id
             WHERE u.id = ?',
            [$userId]
        );

        if (!$user) {
            return null;
        }

        // Online-Status (aktiv in letzten 15 Minuten)
        $user['is_online'] = $user['last_activity_at'] &&
                             strtotime($user['last_activity_at']) > strtotime('-15 minutes');

        // Statistiken laden
        $farmId = $user['farm_id'];

        $user['stats'] = [
            'fields' => (int)$this->db->fetchOne(
                'SELECT COUNT(*) as count FROM fields WHERE farm_id = ?',
                [$farmId]
            )['count'],
            'animals' => (int)$this->db->fetchOne(
                'SELECT COALESCE(SUM(quantity), 0) as count FROM farm_animals WHERE farm_id = ?',
                [$farmId]
            )['count'],
            'productions' => (int)$this->db->fetchOne(
                'SELECT COUNT(*) as count FROM farm_productions WHERE farm_id = ?',
                [$farmId]
            )['count'],
            'vehicles' => (int)$this->db->fetchOne(
                'SELECT COUNT(*) as count FROM farm_vehicles WHERE farm_id = ?',
                [$farmId]
            )['count'],
        ];

        // Genossenschaft
        $coop = $this->db->fetchOne(
            'SELECT c.id, c.name FROM cooperatives c
             JOIN cooperative_members cm ON cm.cooperative_id = c.id
             WHERE cm.farm_id = ?',
            [$farmId]
        );

        $user['cooperative_id'] = $coop['id'] ?? null;
        $user['cooperative_name'] = $coop['name'] ?? null;

        return $user;
    }

    // =========================================================================
    // AKTIVITÄTS-TRACKING
    // =========================================================================

    /**
     * Aktualisiert letzte Aktivität
     */
    public function updateLastActivity(int $userId): void
    {
        $this->db->query(
            'UPDATE users SET last_activity_at = NOW() WHERE id = ?',
            [$userId]
        );
    }

    /**
     * Loggt eine Aktivität
     */
    public function logActivity(int $userId, string $type, ?array $details = null): void
    {
        $this->db->insert('user_activity_log', [
            'user_id' => $userId,
            'activity_type' => $type,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            'details' => $details ? json_encode($details) : null
        ]);
    }

    // =========================================================================
    // CRON-JOBS
    // =========================================================================

    /**
     * Setzt inaktive Accounts in Urlaubsmodus (30 Tage)
     */
    public function processAutoVacation(): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime('-30 days'));

        $result = $this->db->query(
            'UPDATE users
             SET vacation_mode = TRUE, vacation_started_at = NOW()
             WHERE vacation_mode = FALSE
             AND deletion_requested = FALSE
             AND last_activity_at < ?
             AND last_activity_at IS NOT NULL',
            [$cutoffDate]
        );

        return $result->rowCount();
    }

    /**
     * Sendet Löschungs-Warnungen (180 Tage Inaktivität)
     */
    public function sendDeletionWarnings(): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime('-180 days'));

        $users = $this->db->fetchAll(
            'SELECT id, email, username
             FROM users
             WHERE vacation_mode = TRUE
             AND deletion_warning_sent = FALSE
             AND last_activity_at < ?',
            [$cutoffDate]
        );

        $count = 0;
        foreach ($users as $user) {
            if (!empty($user['email'])) {
                $this->sendDeletionWarningEmail($user['email'], $user['username']);
            }

            $this->db->query(
                'UPDATE users SET deletion_warning_sent = TRUE WHERE id = ?',
                [$user['id']]
            );

            $count++;
        }

        return $count;
    }

    /**
     * Löscht Accounts automatisch
     */
    public function processAutoDeletion(): int
    {
        $count = 0;

        // 1. Benutzer die Löschung angefordert haben (7 Tage)
        $requestCutoff = date('Y-m-d H:i:s', strtotime('-7 days'));
        $requestedUsers = $this->db->fetchAll(
            'SELECT id FROM users WHERE deletion_requested = TRUE AND deletion_requested_at < ?',
            [$requestCutoff]
        );

        foreach ($requestedUsers as $user) {
            $this->deleteUserAccount($user['id']);
            $count++;
        }

        // 2. Inaktive Benutzer nach Warnung (187 Tage = 180 + 7)
        $inactiveCutoff = date('Y-m-d H:i:s', strtotime('-187 days'));
        $inactiveUsers = $this->db->fetchAll(
            'SELECT id FROM users
             WHERE vacation_mode = TRUE
             AND deletion_warning_sent = TRUE
             AND last_activity_at < ?',
            [$inactiveCutoff]
        );

        foreach ($inactiveUsers as $user) {
            $this->deleteUserAccount($user['id']);
            $count++;
        }

        return $count;
    }

    /**
     * Löscht einen Benutzer-Account vollständig
     */
    public function deleteUserAccount(int $userId): bool
    {
        // Farm-ID holen
        $farm = $this->db->fetchOne('SELECT id FROM farms WHERE user_id = ?', [$userId]);

        if ($farm) {
            $farmId = $farm['id'];

            // Farm-Daten löschen (Kaskade sollte den Rest erledigen)
            $this->db->query('DELETE FROM farm_fields WHERE farm_id = ?', [$farmId]);
            $this->db->query('DELETE FROM farm_animals WHERE farm_id = ?', [$farmId]);
            $this->db->query('DELETE FROM farm_vehicles WHERE farm_id = ?', [$farmId]);
            $this->db->query('DELETE FROM farm_productions WHERE farm_id = ?', [$farmId]);
            $this->db->query('DELETE FROM farm_inventory WHERE farm_id = ?', [$farmId]);
            $this->db->query('DELETE FROM farm_research WHERE farm_id = ?', [$farmId]);

            // Genossenschafts-Mitgliedschaft
            $this->db->query('DELETE FROM cooperative_members WHERE farm_id = ?', [$farmId]);

            // Farm selbst löschen
            $this->db->query('DELETE FROM farms WHERE id = ?', [$farmId]);
        }

        // Profilbild löschen
        $this->deleteProfilePicture($userId);

        // Benutzer löschen
        $this->db->query('DELETE FROM users WHERE id = ?', [$userId]);

        return true;
    }

    // =========================================================================
    // E-MAIL VERSAND
    // =========================================================================

    /**
     * Sendet E-Mail-Änderungs-Bestätigung
     */
    private function sendEmailChangeConfirmation(string $email, string $username, string $token): void
    {
        $confirmUrl = (defined('SITE_URL') ? SITE_URL : 'https://agrar.sl-wide.de') .
                      BASE_URL . '/account/email/confirm/' . $token;

        $subject = 'E-Mail-Adresse bestätigen - Agrar Simulator';
        $message = "Hallo {$username},\n\n";
        $message .= "du hast eine Änderung deiner E-Mail-Adresse angefordert.\n\n";
        $message .= "Bitte klicke auf folgenden Link um die Änderung zu bestätigen:\n";
        $message .= $confirmUrl . "\n\n";
        $message .= "Der Link ist 24 Stunden gültig.\n\n";
        $message .= "Falls du diese Änderung nicht angefordert hast, ignoriere diese E-Mail.\n\n";
        $message .= "Viele Grüße,\nDein Agrar Simulator Team";

        @mail($email, $subject, $message, "From: noreply@sl-wide.de\r\nContent-Type: text/plain; charset=UTF-8");
    }

    /**
     * Sendet Löschungs-Bestätigung
     */
    private function sendDeletionConfirmationEmail(string $email, string $username): void
    {
        $subject = 'Account-Löschung angefordert - Agrar Simulator';
        $message = "Hallo {$username},\n\n";
        $message .= "du hast die Löschung deines Accounts angefordert.\n\n";
        $message .= "Dein Account wird in 7 Tagen gelöscht.\n\n";
        $message .= "Wenn du dich in dieser Zeit wieder einloggst, wird die Löschung automatisch abgebrochen.\n\n";
        $message .= "Falls du diese Anfrage nicht gestellt hast, logge dich bitte sofort ein um die Löschung zu stoppen.\n\n";
        $message .= "Viele Grüße,\nDein Agrar Simulator Team";

        @mail($email, $subject, $message, "From: noreply@sl-wide.de\r\nContent-Type: text/plain; charset=UTF-8");
    }

    /**
     * Sendet Löschungs-Warnung (180 Tage Inaktivität)
     */
    private function sendDeletionWarningEmail(string $email, string $username): void
    {
        $loginUrl = (defined('SITE_URL') ? SITE_URL : 'https://agrar.sl-wide.de') . BASE_URL . '/login';

        $subject = 'Dein Account wird gelöscht - Agrar Simulator';
        $message = "Hallo {$username},\n\n";
        $message .= "wir haben festgestellt, dass du dich seit über 180 Tagen nicht mehr eingeloggt hast.\n\n";
        $message .= "Dein Account wird in 7 Tagen automatisch gelöscht, wenn du dich nicht einloggst.\n\n";
        $message .= "Um deinen Account zu behalten, logge dich einfach ein:\n";
        $message .= $loginUrl . "\n\n";
        $message .= "Viele Grüße,\nDein Agrar Simulator Team";

        @mail($email, $subject, $message, "From: noreply@sl-wide.de\r\nContent-Type: text/plain; charset=UTF-8");
    }
}
