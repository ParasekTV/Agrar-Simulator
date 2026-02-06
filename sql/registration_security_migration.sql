-- ============================================
-- Registration Security Migration
-- Erstellt: 2026-02-06
-- ============================================
-- Fügt E-Mail-Verifizierung und Discord OAuth hinzu
-- ============================================

-- E-Mail-Verifizierung Spalten
ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER is_active;
ALTER TABLE users ADD COLUMN verification_token VARCHAR(64) NULL AFTER is_verified;
ALTER TABLE users ADD COLUMN token_expires_at DATETIME NULL AFTER verification_token;

-- Discord OAuth Spalten
ALTER TABLE users ADD COLUMN discord_id VARCHAR(32) NULL AFTER token_expires_at;
ALTER TABLE users ADD COLUMN discord_username VARCHAR(100) NULL AFTER discord_id;
ALTER TABLE users ADD COLUMN discord_avatar VARCHAR(255) NULL AFTER discord_username;

-- Indizes für schnelle Lookups
CREATE INDEX idx_users_discord_id ON users(discord_id);
CREATE INDEX idx_users_verification_token ON users(verification_token);

-- Bestehende User als verifiziert markieren
UPDATE users SET is_verified = 1 WHERE is_verified = 0;

-- ============================================
-- Rate Limiting Tabelle
-- ============================================
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    action VARCHAR(50) NOT NULL,
    attempts INT DEFAULT 1,
    first_attempt_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_attempt_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rate_limits_ip_action (ip_address, action),
    INDEX idx_rate_limits_cleanup (first_attempt_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FERTIG
-- ============================================
