-- ============================================
-- FIX: Fehlende Spalten in cooperative_productions
-- ============================================
-- Stellt sicher, dass alle benoetigten Spalten existieren

SET NAMES utf8mb4;

-- purchased_by Spalte hinzufuegen (wird beim Kauf verwendet)
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS purchased_by INT NULL AFTER production_id;

-- purchased_at Spalte hinzufuegen
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER purchased_by;

-- is_active Spalte hinzufuegen
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1 AFTER purchased_at;

-- is_running Spalte hinzufuegen
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS is_running TINYINT(1) DEFAULT 0 AFTER is_active;

-- started_at Spalte hinzufuegen
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS started_at TIMESTAMP NULL AFTER is_running;

-- cycles_completed Spalte hinzufuegen
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS cycles_completed INT DEFAULT 0 AFTER started_at;

-- current_efficiency Spalte hinzufuegen
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS current_efficiency DECIMAL(5,2) DEFAULT 100.00 AFTER cycles_completed;

-- last_production_at Spalte hinzufuegen
ALTER TABLE cooperative_productions
ADD COLUMN IF NOT EXISTS last_production_at TIMESTAMP NULL AFTER current_efficiency;

-- Falls started_by existiert aber purchased_by nicht gefuellt ist, Daten kopieren
UPDATE cooperative_productions
SET purchased_by = started_by
WHERE purchased_by IS NULL AND started_by IS NOT NULL;

-- ============================================
-- VERIFIZIERUNG
-- ============================================

-- Zeige Tabellenstruktur
DESCRIBE cooperative_productions;

-- Zeige alle Eintraege
SELECT cp.id, cp.cooperative_id, cp.production_id, cp.purchased_by, cp.is_active,
       p.name_de as production_name
FROM cooperative_productions cp
LEFT JOIN productions p ON cp.production_id = p.id;
