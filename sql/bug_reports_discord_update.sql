-- ============================================
-- Bug Reports: Discord Integration Update
-- Erstellt: 2026-02-06
-- ============================================
-- Fügt Spalten für Discord Thread-ID und Admin-Begründung hinzu
-- ============================================

-- Discord Thread-ID zum Speichern der Thread-ID für spätere Updates
ALTER TABLE bug_reports
ADD COLUMN discord_thread_id VARCHAR(50) NULL AFTER status;

-- Admin-Begründung für Status-Änderungen
ALTER TABLE bug_reports
ADD COLUMN admin_reason TEXT NULL AFTER discord_thread_id;

-- ============================================
-- FERTIG
-- ============================================
