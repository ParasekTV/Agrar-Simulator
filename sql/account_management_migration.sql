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
-- 3. AKTIVITÄTS-LOG TABELLE
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
