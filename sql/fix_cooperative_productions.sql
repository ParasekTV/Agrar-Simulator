-- ============================================
-- FIX: Fehlende Spalten in cooperative_productions
-- ============================================

-- Spalte is_running hinzufuegen falls nicht vorhanden
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS is_running TINYINT(1) DEFAULT 0 AFTER is_active;

-- Spalte started_at hinzufuegen falls nicht vorhanden
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS started_at TIMESTAMP NULL AFTER is_running;

-- Spalte cycles_completed hinzufuegen falls nicht vorhanden
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS cycles_completed INT DEFAULT 0 AFTER started_at;

-- Spalte current_efficiency hinzufuegen falls nicht vorhanden
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS current_efficiency DECIMAL(5,2) DEFAULT 100.00 AFTER cycles_completed;

-- Spalte last_production_at hinzufuegen falls nicht vorhanden
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS last_production_at TIMESTAMP NULL AFTER current_efficiency;

-- ============================================
-- Logs-Tabelle falls nicht vorhanden
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
    INDEX idx_coop_prod_logs (cooperative_production_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
