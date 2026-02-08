SET NAMES utf8mb4;

-- ============================================
-- MARKTPLATZ PUSH-FUNKTION - v1.2 Migration
-- ============================================
-- Spieler koennen Angebote hervorheben fuer bessere Sichtbarkeit

-- ============================================
-- 1. MARKET_LISTINGS TABELLE ERWEITERN
-- ============================================

ALTER TABLE market_listings ADD COLUMN IF NOT EXISTS is_pushed TINYINT(1) DEFAULT 0;
ALTER TABLE market_listings ADD COLUMN IF NOT EXISTS pushed_until TIMESTAMP NULL;
ALTER TABLE market_listings ADD COLUMN IF NOT EXISTS push_cost DECIMAL(10,2) DEFAULT 0;

-- Index fuer Push-Sortierung
CREATE INDEX IF NOT EXISTS idx_market_pushed ON market_listings(is_pushed, pushed_until);

-- ============================================
-- 2. PUSH-KONFIGURATIONS-TABELLE
-- ============================================

CREATE TABLE IF NOT EXISTS market_push_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    duration_hours INT NOT NULL DEFAULT 24,
    cost DECIMAL(10,2) NOT NULL,
    highlight_color VARCHAR(20) DEFAULT '#ffd700',
    icon VARCHAR(50) DEFAULT 'star',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Push-Optionen einfuegen
INSERT INTO market_push_config (name, description, duration_hours, cost, highlight_color, icon) VALUES
('Standard-Push', '24 Stunden Hervorhebung', 24, 500.00, '#ffd700', 'star'),
('Premium-Push', '48 Stunden Hervorhebung + Goldrand', 48, 1200.00, '#ff9900', 'crown'),
('Super-Push', '72 Stunden Hervorhebung + Blinken', 72, 2500.00, '#ff4444', 'fire')
ON DUPLICATE KEY UPDATE cost = VALUES(cost);

-- ============================================
-- 3. PUSH-HISTORIE TABELLE
-- ============================================

CREATE TABLE IF NOT EXISTS market_push_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    farm_id INT NOT NULL,
    push_config_id INT NOT NULL,
    cost_paid DECIMAL(10,2) NOT NULL,
    pushed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_listing_push (listing_id),
    INDEX idx_farm_push (farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

