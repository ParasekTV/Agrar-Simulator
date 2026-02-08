SET NAMES utf8mb4;

-- ============================================
-- FAHRZEUGE AN GENOSSENSCHAFT AUSLEIHEN - v1.2 Migration
-- ============================================

-- ============================================
-- 1. FARM_VEHICLES ERWEITERN
-- ============================================

ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS lent_to_cooperative_id INT NULL;
ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS lent_at TIMESTAMP NULL;
ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS lent_until TIMESTAMP NULL;

-- Index fuer schnelle Suche
CREATE INDEX IF NOT EXISTS idx_vehicle_lent ON farm_vehicles(lent_to_cooperative_id);

-- ============================================
-- 2. AUSLEIHE-HISTORIE
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_vehicle_loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_vehicle_id INT NOT NULL,
    cooperative_id INT NOT NULL,
    lender_farm_id INT NOT NULL,
    borrower_farm_id INT NULL,
    hourly_fee DECIMAL(10,2) DEFAULT 0,
    lent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lent_until TIMESTAMP NOT NULL,
    returned_at TIMESTAMP NULL,
    total_hours_used DECIMAL(10,2) DEFAULT 0,
    total_fee_paid DECIMAL(10,2) DEFAULT 0,
    status ENUM('active', 'borrowed', 'returned', 'expired') DEFAULT 'active',
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (lender_farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_coop_vehicle_loans (cooperative_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

