SET NAMES utf8mb4;

-- ============================================
-- GENOSSENSCHAFTS-PRODUKTIONEN - v1.2 Migration
-- ============================================

-- ============================================
-- 1. GENOSSENSCHAFTS-PRODUKTIONEN TABELLE
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_productions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    production_id INT NOT NULL,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    purchased_by INT NULL,
    is_active TINYINT(1) DEFAULT 1,
    is_running TINYINT(1) DEFAULT 0,
    started_at TIMESTAMP NULL,
    cycles_completed INT DEFAULT 0,
    current_efficiency DECIMAL(5,2) DEFAULT 100.00,
    last_production_at TIMESTAMP NULL,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (production_id) REFERENCES productions(id),
    FOREIGN KEY (purchased_by) REFERENCES farms(id) ON DELETE SET NULL,
    INDEX idx_coop_productions (cooperative_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. GENOSSENSCHAFTS-PRODUKTIONS-LOGS
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_production_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_production_id INT NOT NULL,
    cooperative_id INT NOT NULL,
    action ENUM('started', 'stopped', 'cycle_complete', 'resources_depleted', 'purchased') NOT NULL,
    details TEXT,
    efficiency DECIMAL(5,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    FOREIGN KEY (cooperative_production_id) REFERENCES cooperative_productions(id) ON DELETE CASCADE,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES farms(id) ON DELETE SET NULL,
    INDEX idx_coop_prod_logs (cooperative_production_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. FORSCHUNGS-CHECK FUER COOP-PRODUKTIONEN
-- ============================================

-- Genossenschaften koennen nur Produktionen kaufen,
-- die durch Genossenschafts-Forschung freigeschaltet sind

