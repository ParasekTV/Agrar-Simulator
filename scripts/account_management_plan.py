#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Account-Verwaltung Implementierungsplan
========================================

Dieses Script dokumentiert alle notwendigen Änderungen für die umfassende
Account-Verwaltung im Agrar Simulator.

Autor: Florian Müller
Version: 1.0
"""

import json
from datetime import datetime

# ============================================================================
# FEATURE-ÜBERSICHT
# ============================================================================

FEATURES = {
    "1_password_change": {
        "name": "Passwort ändern",
        "description": "Spieler kann sein Passwort ändern (altes + neues Passwort erforderlich)",
        "priority": "hoch"
    },
    "2_email_change": {
        "name": "E-Mail ändern",
        "description": "Spieler kann seine E-Mail-Adresse ändern (Verifizierung erforderlich)",
        "priority": "hoch"
    },
    "3_account_deletion": {
        "name": "Account-Löschung",
        "description": "Spieler kann Account zur Löschung markieren (7 Tage Frist)",
        "priority": "hoch"
    },
    "4_vacation_mode": {
        "name": "Urlaubsmodus",
        "description": "Manuell aktivierbar, automatisch nach 30 Tagen Inaktivität",
        "priority": "mittel"
    },
    "5_auto_deletion": {
        "name": "Automatische Löschung",
        "description": "Nach 180 Tagen Inaktivität mit E-Mail-Warnung",
        "priority": "mittel"
    },
    "6_profile_picture": {
        "name": "Profilbild",
        "description": "Upload max 124x124 Pixel, max 3MB",
        "priority": "mittel"
    },
    "7_profile_info": {
        "name": "Profil-Informationen",
        "description": "Registrierungsdatum, letzter Login, Willkommens-Nachricht",
        "priority": "niedrig"
    },
    "8_public_profile": {
        "name": "Öffentliches Profil",
        "description": "Andere Spieler können Profil einsehen (Level, Vermögen, Statistiken)",
        "priority": "mittel"
    },
    "9_admin_extensions": {
        "name": "Admin-Erweiterungen",
        "description": "Urlaubsmodus setzen, Löschung markieren, alle Werte bearbeiten",
        "priority": "hoch"
    }
}

# ============================================================================
# DATENBANK-ÄNDERUNGEN
# ============================================================================

SQL_MIGRATION = """
-- ============================================================================
-- ACCOUNT MANAGEMENT MIGRATION
-- Agrar Simulator v1.3
-- ============================================================================

SET NAMES utf8mb4;

-- ----------------------------------------------------------------------------
-- 1. USERS-TABELLE ERWEITERN
-- ----------------------------------------------------------------------------

-- Urlaubsmodus
ALTER TABLE users ADD COLUMN IF NOT EXISTS vacation_mode BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS vacation_started_at DATETIME DEFAULT NULL;

-- Account-Löschung
ALTER TABLE users ADD COLUMN IF NOT EXISTS deletion_requested BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS deletion_requested_at DATETIME DEFAULT NULL;

-- Profilbild
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) DEFAULT NULL;

-- Aktivitäts-Tracking (falls noch nicht vorhanden)
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_activity_at DATETIME DEFAULT NULL;

-- E-Mail-Änderung
ALTER TABLE users ADD COLUMN IF NOT EXISTS pending_email VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_change_token VARCHAR(100) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_change_expires_at DATETIME DEFAULT NULL;

-- Löschungs-Warnung gesendet
ALTER TABLE users ADD COLUMN IF NOT EXISTS deletion_warning_sent BOOLEAN DEFAULT FALSE;

-- ----------------------------------------------------------------------------
-- 2. INDEXE FÜR PERFORMANCE
-- ----------------------------------------------------------------------------

CREATE INDEX IF NOT EXISTS idx_users_vacation_mode ON users(vacation_mode);
CREATE INDEX IF NOT EXISTS idx_users_deletion_requested ON users(deletion_requested);
CREATE INDEX IF NOT EXISTS idx_users_last_activity ON users(last_activity_at);
CREATE INDEX IF NOT EXISTS idx_users_email_change_token ON users(email_change_token);

-- ----------------------------------------------------------------------------
-- 3. AKTIVITÄTS-LOG TABELLE (OPTIONAL)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS user_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('login', 'logout', 'password_change', 'email_change',
                       'vacation_on', 'vacation_off', 'deletion_request',
                       'deletion_cancel', 'profile_update') NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    details JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_activity_user (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_activity_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. BESTEHENDE DATEN AKTUALISIEREN
-- ----------------------------------------------------------------------------

-- Setze last_activity_at auf last_login falls NULL
UPDATE users SET last_activity_at = last_login WHERE last_activity_at IS NULL AND last_login IS NOT NULL;

-- Setze last_activity_at auf created_at falls immer noch NULL
UPDATE users SET last_activity_at = created_at WHERE last_activity_at IS NULL;

SELECT 'Account Management Migration completed.' AS status;
"""

# ============================================================================
# NEUE DATEIEN
# ============================================================================

NEW_FILES = {
    # -------------------------------------------------------------------------
    # MODEL
    # -------------------------------------------------------------------------
    "app/models/Account.php": {
        "type": "model",
        "description": "Account-Verwaltungs-Model",
        "methods": [
            "changePassword(int $userId, string $currentPassword, string $newPassword): array",
            "requestEmailChange(int $userId, string $newEmail): array",
            "confirmEmailChange(string $token): array",
            "requestDeletion(int $userId): array",
            "cancelDeletion(int $userId): array",
            "setVacationMode(int $userId, bool $enabled): array",
            "uploadProfilePicture(int $userId, array $file): array",
            "deleteProfilePicture(int $userId): array",
            "getAccountInfo(int $userId): ?array",
            "getPublicProfile(int $userId): ?array",
            "updateLastActivity(int $userId): void",
            "logActivity(int $userId, string $type, ?array $details = null): void",
            "processAutoVacation(): int",  # Cron
            "processAutoDeletion(): int",  # Cron
            "sendDeletionWarnings(): int",  # Cron
        ]
    },

    # -------------------------------------------------------------------------
    # CONTROLLER
    # -------------------------------------------------------------------------
    "app/controllers/AccountController.php": {
        "type": "controller",
        "description": "Account-Verwaltungs-Controller",
        "methods": [
            "index(): void",  # Hauptseite Account-Verwaltung
            "changePassword(): void",  # POST
            "requestEmailChange(): void",  # POST
            "confirmEmailChange(string $token): void",  # GET
            "requestDeletion(): void",  # POST
            "cancelDeletion(): void",  # POST
            "toggleVacation(): void",  # POST
            "uploadPicture(): void",  # POST
            "deletePicture(): void",  # POST
            "profile(int $id): void",  # Öffentliches Profil
        ]
    },

    # -------------------------------------------------------------------------
    # VIEWS
    # -------------------------------------------------------------------------
    "app/views/account/index.php": {
        "type": "view",
        "description": "Hauptseite Account-Verwaltung",
        "sections": [
            "Willkommen zurück, [Username]!",
            "Profil-Informationen (Registriert am, Letzter Login)",
            "Profilbild (Upload/Ändern/Löschen)",
            "E-Mail ändern",
            "Passwort ändern",
            "Urlaubsmodus",
            "Account löschen (Gefahrenzone)"
        ]
    },
    "app/views/account/profile.php": {
        "type": "view",
        "description": "Öffentliches Spielerprofil",
        "sections": [
            "Profilbild",
            "Username",
            "Level",
            "Vermögen (optional verbergen)",
            "Statistiken (Tiere, Produktionen, Fahrzeuge, Felder)",
            "Mitglied seit",
            "Online-Status / Urlaubsmodus-Badge"
        ]
    },
    "app/views/account/confirm_email.php": {
        "type": "view",
        "description": "E-Mail-Bestätigungsseite"
    },

    # -------------------------------------------------------------------------
    # ADMIN-ERWEITERUNGEN
    # -------------------------------------------------------------------------
    "app/views/admin/user_account.php": {
        "type": "view",
        "description": "Admin-Bereich für Account-Verwaltung eines Users",
        "sections": [
            "Urlaubsmodus aktivieren/deaktivieren",
            "Account zur Löschung markieren",
            "Löschung abbrechen",
            "Profilbild löschen",
            "Passwort zurücksetzen",
            "E-Mail ändern"
        ]
    },

    # -------------------------------------------------------------------------
    # CRON-JOBS
    # -------------------------------------------------------------------------
    "cron/account_maintenance.php": {
        "type": "cron",
        "description": "Automatische Account-Wartung",
        "tasks": [
            "Auto-Urlaubsmodus nach 30 Tagen Inaktivität",
            "Löschungs-Warnungen nach 180 Tagen senden",
            "Accounts nach 187 Tagen (180+7) löschen",
            "Accounts nach 7 Tagen Löschungs-Request löschen"
        ],
        "schedule": "0 2 * * *"  # Täglich um 2 Uhr nachts
    },

    # -------------------------------------------------------------------------
    # E-MAIL-TEMPLATES
    # -------------------------------------------------------------------------
    "app/views/emails/email_change.php": {
        "type": "email",
        "description": "E-Mail zur Bestätigung der E-Mail-Änderung"
    },
    "app/views/emails/deletion_warning.php": {
        "type": "email",
        "description": "Warnung: Account wird in 7 Tagen gelöscht"
    },
    "app/views/emails/deletion_confirmed.php": {
        "type": "email",
        "description": "Bestätigung: Account zur Löschung markiert"
    }
}

# ============================================================================
# BESTEHENDE DATEIEN ZU ÄNDERN
# ============================================================================

MODIFY_FILES = {
    # -------------------------------------------------------------------------
    # ROUTES
    # -------------------------------------------------------------------------
    "public/index.php": {
        "action": "add_routes",
        "routes": [
            "# Account-Verwaltung",
            "$router->get('/account', 'Account', 'index');",
            "$router->post('/account/password', 'Account', 'changePassword');",
            "$router->post('/account/email', 'Account', 'requestEmailChange');",
            "$router->get('/account/email/confirm/{token}', 'Account', 'confirmEmailChange');",
            "$router->post('/account/delete', 'Account', 'requestDeletion');",
            "$router->post('/account/delete/cancel', 'Account', 'cancelDeletion');",
            "$router->post('/account/vacation', 'Account', 'toggleVacation');",
            "$router->post('/account/picture', 'Account', 'uploadPicture');",
            "$router->post('/account/picture/delete', 'Account', 'deletePicture');",
            "$router->get('/player/{id}', 'Account', 'profile');",
            "",
            "# Admin Account-Erweiterungen",
            "$router->post('/admin/users/{id}/vacation', 'Admin', 'toggleUserVacation');",
            "$router->post('/admin/users/{id}/deletion', 'Admin', 'toggleUserDeletion');",
            "$router->post('/admin/users/{id}/reset-password', 'Admin', 'resetUserPassword');",
        ]
    },

    # -------------------------------------------------------------------------
    # NAVIGATION
    # -------------------------------------------------------------------------
    "app/views/layouts/navigation.php": {
        "action": "add_menu_item",
        "location": "user_dropdown",
        "item": '<a href="<?= BASE_URL ?>/account" class="nav-dropdown-item">Account</a>'
    },

    # -------------------------------------------------------------------------
    # ADMIN CONTROLLER
    # -------------------------------------------------------------------------
    "app/controllers/AdminController.php": {
        "action": "add_methods",
        "methods": [
            "toggleUserVacation(int $id): void",
            "toggleUserDeletion(int $id): void",
            "resetUserPassword(int $id): void"
        ]
    },

    # -------------------------------------------------------------------------
    # ADMIN USER EDIT VIEW
    # -------------------------------------------------------------------------
    "app/views/admin/user_edit.php": {
        "action": "add_sections",
        "sections": [
            "Urlaubsmodus-Toggle",
            "Löschungs-Toggle",
            "Passwort-Reset-Button",
            "Profilbild anzeigen/löschen"
        ]
    },

    # -------------------------------------------------------------------------
    # ADMIN USERS LIST
    # -------------------------------------------------------------------------
    "app/views/admin/users.php": {
        "action": "add_columns",
        "columns": [
            "Urlaubsmodus (Badge)",
            "Löschung angefragt (Badge mit Datum)",
            "Letzte Aktivität"
        ]
    },

    # -------------------------------------------------------------------------
    # RANKINGS VIEW
    # -------------------------------------------------------------------------
    "app/views/rankings/index.php": {
        "action": "make_names_clickable",
        "description": "Spielernamen als Links zu /player/{id}"
    },

    # -------------------------------------------------------------------------
    # AUTH CONTROLLER
    # -------------------------------------------------------------------------
    "app/controllers/AuthController.php": {
        "action": "modify_login",
        "changes": [
            "Bei Login: last_activity_at aktualisieren",
            "Bei Login: Löschungs-Request abbrechen falls vorhanden",
            "Bei Login: Urlaubsmodus beenden falls aktiv",
            "Bei Login: Aktivität loggen"
        ]
    },

    # -------------------------------------------------------------------------
    # BASE CONTROLLER
    # -------------------------------------------------------------------------
    "app/core/Controller.php": {
        "action": "add_activity_tracking",
        "description": "Bei jeder authentifizierten Anfrage last_activity_at aktualisieren"
    }
}

# ============================================================================
# UPLOAD-VERZEICHNIS
# ============================================================================

UPLOAD_CONFIG = {
    "directory": "public/uploads/avatars/",
    "max_size_bytes": 3 * 1024 * 1024,  # 3 MB
    "max_width": 124,
    "max_height": 124,
    "allowed_types": ["image/jpeg", "image/png", "image/gif", "image/webp"],
    "filename_format": "avatar_{user_id}_{timestamp}.{ext}"
}

# ============================================================================
# ZEITINTERVALLE (in Tagen)
# ============================================================================

TIME_INTERVALS = {
    "auto_vacation_days": 30,      # Nach 30 Tagen Inaktivität -> Urlaubsmodus
    "deletion_warning_days": 180,  # Nach 180 Tagen -> Löschungs-Warnung
    "deletion_grace_days": 7,      # 7 Tage nach Warnung/Request -> Löschung
    "email_change_expires_hours": 24  # E-Mail-Änderungs-Token gültig
}

# ============================================================================
# IMPLEMENTIERUNGSREIHENFOLGE
# ============================================================================

IMPLEMENTATION_ORDER = [
    {
        "step": 1,
        "name": "Datenbank-Migration",
        "files": ["sql/account_management_migration.sql"],
        "description": "Neue Spalten und Tabellen anlegen"
    },
    {
        "step": 2,
        "name": "Upload-Verzeichnis",
        "files": ["public/uploads/avatars/.gitkeep"],
        "description": "Verzeichnis für Profilbilder erstellen"
    },
    {
        "step": 3,
        "name": "Account Model",
        "files": ["app/models/Account.php"],
        "description": "Kernlogik für Account-Verwaltung"
    },
    {
        "step": 4,
        "name": "Account Controller",
        "files": ["app/controllers/AccountController.php"],
        "description": "HTTP-Endpunkte für Account-Funktionen"
    },
    {
        "step": 5,
        "name": "Account Views",
        "files": [
            "app/views/account/index.php",
            "app/views/account/profile.php",
            "app/views/account/confirm_email.php"
        ],
        "description": "Benutzeroberfläche"
    },
    {
        "step": 6,
        "name": "E-Mail Templates",
        "files": [
            "app/views/emails/email_change.php",
            "app/views/emails/deletion_warning.php",
            "app/views/emails/deletion_confirmed.php"
        ],
        "description": "E-Mail-Vorlagen"
    },
    {
        "step": 7,
        "name": "Routes hinzufügen",
        "files": ["public/index.php"],
        "description": "Neue Routen registrieren"
    },
    {
        "step": 8,
        "name": "Navigation aktualisieren",
        "files": ["app/views/layouts/navigation.php"],
        "description": "Account-Link im Menü"
    },
    {
        "step": 9,
        "name": "Admin-Erweiterungen",
        "files": [
            "app/controllers/AdminController.php",
            "app/views/admin/user_edit.php",
            "app/views/admin/users.php"
        ],
        "description": "Admin-Funktionen erweitern"
    },
    {
        "step": 10,
        "name": "Rankings verlinken",
        "files": ["app/views/rankings/index.php"],
        "description": "Spielernamen klickbar machen"
    },
    {
        "step": 11,
        "name": "Auth-Anpassungen",
        "files": ["app/controllers/AuthController.php"],
        "description": "Login-Logik anpassen"
    },
    {
        "step": 12,
        "name": "Activity Tracking",
        "files": ["app/core/Controller.php"],
        "description": "Aktivitäts-Tracking bei jeder Anfrage"
    },
    {
        "step": 13,
        "name": "Cron-Job",
        "files": ["cron/account_maintenance.php"],
        "description": "Automatische Wartung einrichten"
    }
]

# ============================================================================
# CODE-SNIPPETS
# ============================================================================

CODE_SNIPPETS = {
    # -------------------------------------------------------------------------
    # Account Model - Passwort ändern
    # -------------------------------------------------------------------------
    "change_password": '''
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

    $this->db->execute(
        'UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?',
        [$hashedPassword, $userId]
    );

    $this->logActivity($userId, 'password_change');

    return ['success' => true, 'message' => 'Passwort erfolgreich geändert'];
}
''',

    # -------------------------------------------------------------------------
    # Account Model - Löschung anfordern
    # -------------------------------------------------------------------------
    "request_deletion": '''
public function requestDeletion(int $userId): array
{
    $this->db->execute(
        'UPDATE users SET deletion_requested = TRUE, deletion_requested_at = NOW() WHERE id = ?',
        [$userId]
    );

    $this->logActivity($userId, 'deletion_request');

    // E-Mail senden
    $user = $this->db->fetchOne('SELECT email, username FROM users WHERE id = ?', [$userId]);
    $this->sendDeletionConfirmationEmail($user['email'], $user['username']);

    return [
        'success' => true,
        'message' => 'Dein Account wurde zur Löschung markiert. Er wird in 7 Tagen gelöscht. ' .
                     'Wenn du dich in dieser Zeit wieder einloggst, wird die Löschung abgebrochen.'
    ];
}
''',

    # -------------------------------------------------------------------------
    # Auth Controller - Login Anpassung
    # -------------------------------------------------------------------------
    "login_modifications": '''
// Nach erfolgreichem Login hinzufügen:

// Löschungs-Request abbrechen
if ($user['deletion_requested']) {
    $this->db->execute(
        'UPDATE users SET deletion_requested = FALSE, deletion_requested_at = NULL WHERE id = ?',
        [$user['id']]
    );
    Session::setFlash('info', 'Deine Account-Löschung wurde abgebrochen.', 'info');
}

// Urlaubsmodus beenden
if ($user['vacation_mode']) {
    $this->db->execute(
        'UPDATE users SET vacation_mode = FALSE, vacation_started_at = NULL WHERE id = ?',
        [$user['id']]
    );
    Session::setFlash('info', 'Willkommen zurück! Dein Urlaubsmodus wurde beendet.', 'info');
}

// Aktivität aktualisieren
$this->db->execute(
    'UPDATE users SET last_login = NOW(), last_activity_at = NOW() WHERE id = ?',
    [$user['id']]
);
''',

    # -------------------------------------------------------------------------
    # Profilbild Upload
    # -------------------------------------------------------------------------
    "profile_picture_upload": '''
public function uploadProfilePicture(int $userId, array $file): array
{
    // Validierung
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload fehlgeschlagen'];
    }

    if ($file['size'] > 3 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Datei darf maximal 3 MB groß sein'];
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Nur JPG, PNG, GIF und WebP erlaubt'];
    }

    // Bildgröße prüfen und ggf. verkleinern
    list($width, $height) = getimagesize($file['tmp_name']);

    if ($width > 124 || $height > 124) {
        // Bild verkleinern
        $image = $this->resizeImage($file['tmp_name'], $mimeType, 124, 124);
    } else {
        $image = file_get_contents($file['tmp_name']);
    }

    // Altes Bild löschen
    $this->deleteProfilePicture($userId);

    // Neues Bild speichern
    $ext = match($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        default => 'jpg'
    };

    $filename = "avatar_{$userId}_" . time() . ".{$ext}";
    $path = UPLOADS_PATH . '/avatars/' . $filename;

    file_put_contents($path, $image);

    $this->db->execute(
        'UPDATE users SET profile_picture = ? WHERE id = ?',
        [$filename, $userId]
    );

    $this->logActivity($userId, 'profile_update', ['action' => 'picture_upload']);

    return ['success' => true, 'message' => 'Profilbild erfolgreich hochgeladen'];
}
''',

    # -------------------------------------------------------------------------
    # Cron - Auto Vacation
    # -------------------------------------------------------------------------
    "cron_auto_vacation": '''
public function processAutoVacation(): int
{
    $cutoffDate = date('Y-m-d H:i:s', strtotime('-30 days'));

    $result = $this->db->execute(
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
''',

    # -------------------------------------------------------------------------
    # Cron - Deletion Warnings
    # -------------------------------------------------------------------------
    "cron_deletion_warnings": '''
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
        $this->sendDeletionWarningEmail($user['email'], $user['username']);

        $this->db->execute(
            'UPDATE users SET deletion_warning_sent = TRUE WHERE id = ?',
            [$user['id']]
        );

        $count++;
    }

    return $count;
}
''',

    # -------------------------------------------------------------------------
    # Cron - Auto Deletion
    # -------------------------------------------------------------------------
    "cron_auto_deletion": '''
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
'''
}

# ============================================================================
# VIEW STRUKTUR - Account Index
# ============================================================================

ACCOUNT_INDEX_VIEW = '''
<div class="account-page">
    <div class="page-header">
        <h1>Account-Verwaltung</h1>
    </div>

    <!-- Willkommen -->
    <div class="welcome-banner">
        <div class="welcome-content">
            <div class="profile-picture-large">
                <?php if ($user['profile_picture']): ?>
                    <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profilbild">
                <?php else: ?>
                    <div class="avatar-placeholder"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                <?php endif; ?>
            </div>
            <div class="welcome-text">
                <h2>Willkommen zurück, <?= htmlspecialchars($user['username']) ?>!</h2>
                <p class="text-muted">
                    Mitglied seit: <?= date('d.m.Y', strtotime($user['created_at'])) ?><br>
                    Letzter Login: <?= date('d.m.Y H:i', strtotime($user['last_login'])) ?> Uhr
                </p>
            </div>
        </div>
    </div>

    <div class="account-grid">
        <!-- Profilbild -->
        <div class="card">
            <div class="card-header"><h3>Profilbild</h3></div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/account/picture" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="form-group">
                        <label>Neues Bild hochladen</label>
                        <input type="file" name="picture" accept="image/*" class="form-input">
                        <small class="form-hint">Max. 124x124 Pixel, max. 3 MB</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Hochladen</button>
                </form>
                <?php if ($user['profile_picture']): ?>
                    <form action="<?= BASE_URL ?>/account/picture/delete" method="POST" class="mt-2">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button type="submit" class="btn btn-outline btn-danger">Bild löschen</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- E-Mail ändern -->
        <div class="card">
            <div class="card-header"><h3>E-Mail ändern</h3></div>
            <div class="card-body">
                <p class="current-email">Aktuelle E-Mail: <strong><?= htmlspecialchars($user['email']) ?></strong></p>
                <form action="<?= BASE_URL ?>/account/email" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="form-group">
                        <label>Neue E-Mail-Adresse</label>
                        <input type="email" name="new_email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Passwort bestätigen</label>
                        <input type="password" name="password" class="form-input" required>
                    </div>
                    <button type="submit" class="btn btn-primary">E-Mail ändern</button>
                </form>
            </div>
        </div>

        <!-- Passwort ändern -->
        <div class="card">
            <div class="card-header"><h3>Passwort ändern</h3></div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/account/password" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="form-group">
                        <label>Aktuelles Passwort</label>
                        <input type="password" name="current_password" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label>Neues Passwort</label>
                        <input type="password" name="new_password" class="form-input" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label>Neues Passwort bestätigen</label>
                        <input type="password" name="confirm_password" class="form-input" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Passwort ändern</button>
                </form>
            </div>
        </div>

        <!-- Urlaubsmodus -->
        <div class="card">
            <div class="card-header"><h3>Urlaubsmodus</h3></div>
            <div class="card-body">
                <?php if ($user['vacation_mode']): ?>
                    <div class="alert alert-info">
                        <strong>Urlaubsmodus aktiv</strong> seit <?= date('d.m.Y', strtotime($user['vacation_started_at'])) ?>
                    </div>
                    <p>Während des Urlaubsmodus werden deine Produktionen pausiert und du bist vor Inaktivitäts-Strafen geschützt.</p>
                    <form action="<?= BASE_URL ?>/account/vacation" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="action" value="disable">
                        <button type="submit" class="btn btn-primary">Urlaubsmodus beenden</button>
                    </form>
                <?php else: ?>
                    <p>Aktiviere den Urlaubsmodus wenn du längere Zeit nicht spielen kannst. Deine Produktionen werden pausiert.</p>
                    <p class="text-muted"><small>Hinweis: Nach 30 Tagen Inaktivität wird der Urlaubsmodus automatisch aktiviert.</small></p>
                    <form action="<?= BASE_URL ?>/account/vacation" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="action" value="enable">
                        <button type="submit" class="btn btn-outline">Urlaubsmodus aktivieren</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gefahrenzone -->
    <div class="danger-zone">
        <div class="card card-danger">
            <div class="card-header"><h3>Gefahrenzone</h3></div>
            <div class="card-body">
                <?php if ($user['deletion_requested']): ?>
                    <div class="alert alert-danger">
                        <strong>Löschung angefordert!</strong><br>
                        Dein Account wird am <?= date('d.m.Y', strtotime($user['deletion_requested_at'] . ' +7 days')) ?> gelöscht.
                    </div>
                    <form action="<?= BASE_URL ?>/account/delete/cancel" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button type="submit" class="btn btn-primary">Löschung abbrechen</button>
                    </form>
                <?php else: ?>
                    <p class="text-danger">
                        <strong>Achtung:</strong> Wenn du deinen Account löschst, werden alle Daten unwiderruflich gelöscht.
                    </p>
                    <form action="<?= BASE_URL ?>/account/delete" method="POST"
                          onsubmit="return confirm('Bist du sicher? Dein Account wird in 7 Tagen gelöscht.');">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="confirm_deletion" required>
                                Ich verstehe, dass mein Account in 7 Tagen gelöscht wird
                            </label>
                        </div>
                        <button type="submit" class="btn btn-danger">Account zur Löschung markieren</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
'''

# ============================================================================
# VIEW STRUKTUR - Public Profile
# ============================================================================

PUBLIC_PROFILE_VIEW = '''
<div class="profile-page">
    <div class="profile-header">
        <div class="profile-picture-container">
            <?php if ($player['profile_picture']): ?>
                <img src="<?= BASE_URL ?>/uploads/avatars/<?= htmlspecialchars($player['profile_picture']) ?>"
                     alt="<?= htmlspecialchars($player['username']) ?>" class="profile-picture-xl">
            <?php else: ?>
                <div class="avatar-placeholder-xl"><?= strtoupper(substr($player['username'], 0, 1)) ?></div>
            <?php endif; ?>

            <?php if ($player['vacation_mode']): ?>
                <span class="badge badge-warning vacation-badge">Im Urlaub</span>
            <?php elseif ($player['is_online']): ?>
                <span class="badge badge-success online-badge">Online</span>
            <?php endif; ?>
        </div>

        <div class="profile-info">
            <h1><?= htmlspecialchars($player['username']) ?></h1>
            <p class="profile-meta">
                <span class="level">Level <?= $player['level'] ?></span>
                <span class="member-since">Mitglied seit <?= date('d.m.Y', strtotime($player['created_at'])) ?></span>
            </p>
        </div>
    </div>

    <div class="profile-stats-grid">
        <div class="stat-card">
            <div class="stat-icon">&#128176;</div>
            <div class="stat-value"><?= number_format($player['money'], 0, ',', '.') ?> T</div>
            <div class="stat-label">Vermögen</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#127806;</div>
            <div class="stat-value"><?= $stats['fields'] ?></div>
            <div class="stat-label">Felder</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128004;</div>
            <div class="stat-value"><?= $stats['animals'] ?></div>
            <div class="stat-label">Tiere</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#127981;</div>
            <div class="stat-value"><?= $stats['productions'] ?></div>
            <div class="stat-label">Produktionen</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#128666;</div>
            <div class="stat-value"><?= $stats['vehicles'] ?></div>
            <div class="stat-label">Fahrzeuge</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">&#127942;</div>
            <div class="stat-value"><?= number_format($player['points']) ?></div>
            <div class="stat-label">Punkte</div>
        </div>
    </div>

    <?php if ($player['cooperative_name']): ?>
        <div class="profile-cooperative">
            <h3>Genossenschaft</h3>
            <p>
                <a href="<?= BASE_URL ?>/cooperative/<?= $player['cooperative_id'] ?>">
                    <?= htmlspecialchars($player['cooperative_name']) ?>
                </a>
            </p>
        </div>
    <?php endif; ?>

    <div class="profile-footer">
        <a href="<?= BASE_URL ?>/rankings" class="btn btn-outline">&larr; Zurück zur Rangliste</a>
    </div>
</div>
'''

# ============================================================================
# HAUPTFUNKTION - PLAN AUSGEBEN
# ============================================================================

def print_plan():
    print("=" * 80)
    print("ACCOUNT-VERWALTUNG - IMPLEMENTIERUNGSPLAN")
    print("Agrar Simulator v1.3")
    print("=" * 80)
    print()

    print("FEATURES:")
    print("-" * 40)
    for key, feature in FEATURES.items():
        print(f"  [{feature['priority'].upper():6}] {feature['name']}")
        print(f"           {feature['description']}")
        print()

    print()
    print("IMPLEMENTIERUNGSREIHENFOLGE:")
    print("-" * 40)
    for step in IMPLEMENTATION_ORDER:
        print(f"  Schritt {step['step']}: {step['name']}")
        print(f"           Dateien: {', '.join(step['files'])}")
        print(f"           {step['description']}")
        print()

    print()
    print("NEUE DATEIEN:")
    print("-" * 40)
    for path, info in NEW_FILES.items():
        print(f"  {path}")
        print(f"    Typ: {info['type']}")
        print(f"    {info['description']}")
        print()

    print()
    print("ZU ÄNDERNDE DATEIEN:")
    print("-" * 40)
    for path, info in MODIFY_FILES.items():
        print(f"  {path}")
        print(f"    Aktion: {info['action']}")
        print()

    print()
    print("ZEITINTERVALLE:")
    print("-" * 40)
    for key, value in TIME_INTERVALS.items():
        unit = "Stunden" if "hours" in key else "Tage"
        print(f"  {key}: {value} {unit}")

    print()
    print("=" * 80)
    print("SQL-MIGRATION:")
    print("=" * 80)
    print(SQL_MIGRATION)


def save_sql_migration():
    """Speichert die SQL-Migration als separate Datei."""
    with open('sql/account_management_migration.sql', 'w', encoding='utf-8') as f:
        f.write(SQL_MIGRATION)
    print("SQL-Migration gespeichert: sql/account_management_migration.sql")


def save_plan_as_json():
    """Speichert den kompletten Plan als JSON."""
    plan = {
        "version": "1.3",
        "created_at": datetime.now().isoformat(),
        "features": FEATURES,
        "new_files": NEW_FILES,
        "modify_files": MODIFY_FILES,
        "implementation_order": IMPLEMENTATION_ORDER,
        "time_intervals": TIME_INTERVALS,
        "upload_config": UPLOAD_CONFIG
    }

    with open('scripts/account_management_plan.json', 'w', encoding='utf-8') as f:
        json.dump(plan, f, indent=2, ensure_ascii=False)
    print("Plan gespeichert: scripts/account_management_plan.json")


if __name__ == "__main__":
    print_plan()
