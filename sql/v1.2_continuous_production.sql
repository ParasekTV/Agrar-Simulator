SET NAMES utf8mb4;

-- ============================================
-- KONTINUIERLICHE PRODUKTION - v1.2 Migration
-- ============================================

-- Neue Felder für kontinuierliche Produktion
ALTER TABLE farm_productions ADD COLUMN IF NOT EXISTS is_running TINYINT(1) DEFAULT 0 AFTER is_active;
ALTER TABLE farm_productions ADD COLUMN IF NOT EXISTS started_at TIMESTAMP NULL AFTER is_running;
ALTER TABLE farm_productions ADD COLUMN IF NOT EXISTS cycles_completed INT DEFAULT 0 AFTER started_at;
ALTER TABLE farm_productions ADD COLUMN IF NOT EXISTS current_efficiency DECIMAL(5,2) DEFAULT 100.00 AFTER cycles_completed;
ALTER TABLE farm_productions ADD COLUMN IF NOT EXISTS last_cycle_at TIMESTAMP NULL AFTER current_efficiency;

-- Index für laufende Produktionen (für Cron-Job Performance)
CREATE INDEX IF NOT EXISTS idx_farm_productions_running ON farm_productions(is_running, is_active);

-- Effizienz-Konfiguration für Inputs
ALTER TABLE production_inputs ADD COLUMN IF NOT EXISTS efficiency_contribution DECIMAL(5,2) DEFAULT NULL AFTER is_optional;

-- Beispiel-Update: Käserei-Inputs mit Effizienz-Beitrag
-- Kuhmilch trägt 60% bei, Ziegenmilch 20%, Büffelmilch 20%
UPDATE production_inputs pi
JOIN productions p ON pi.production_id = p.id
SET pi.efficiency_contribution = 60.00
WHERE p.name = 'Käserei' AND pi.product_id = (SELECT id FROM products WHERE name = 'Kuhmilch' LIMIT 1);

UPDATE production_inputs pi
JOIN productions p ON pi.production_id = p.id
SET pi.efficiency_contribution = 20.00
WHERE p.name = 'Käserei' AND pi.product_id = (SELECT id FROM products WHERE name = 'Ziegenmilch' LIMIT 1);

UPDATE production_inputs pi
JOIN productions p ON pi.production_id = p.id
SET pi.efficiency_contribution = 20.00
WHERE p.name = 'Käserei' AND pi.product_id = (SELECT id FROM products WHERE name = 'Büffelmilch' LIMIT 1);

-- Produktions-Log Tabelle für Historie
CREATE TABLE IF NOT EXISTS production_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    farm_production_id INT NOT NULL,
    farm_id INT NOT NULL,
    cycle_number INT NOT NULL,
    efficiency DECIMAL(5,2) NOT NULL,
    inputs_used JSON,
    outputs_produced JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_production_id) REFERENCES farm_productions(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_production_logs_farm (farm_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
