-- ============================================
-- Icons-Migration
-- Erstellt: 2026-02-05
-- ============================================
-- 1. Icon-Spalte zu research_tree hinzufuegen
-- 2. Forschungs-Icons aus Produktionen uebernehmen
-- 3. Brand-Logo-Pfade korrigieren (.webp -> .png, Prefix logo-)
-- ============================================

SET NAMES utf8mb4;

-- ============================================
-- 1. ICON-SPALTE FUER RESEARCH_TREE
-- ============================================

-- Spalte hinzufuegen (falls nicht vorhanden)
ALTER TABLE research_tree ADD COLUMN IF NOT EXISTS icon VARCHAR(100) DEFAULT NULL;

-- ============================================
-- 2. FORSCHUNGS-ICONS AUS PRODUKTIONEN UEBERNEHMEN
-- ============================================
-- Produktions-Forschungen (ID 100-170) bekommen das Icon
-- der zugehoerigen Produktion zugewiesen.
-- Die Verknuepfung laeuft ueber required_research_id in productions.

UPDATE research_tree rt
JOIN productions p ON p.required_research_id = rt.id
SET rt.icon = p.icon
WHERE rt.id BETWEEN 100 AND 170
  AND rt.category = 'production'
  AND p.icon IS NOT NULL;

-- ============================================
-- 3. BRAND-LOGO-PFADE KORRIGIEREN
-- ============================================
-- Aktuell in DB: /img/brands/fahrzeuge/johndeere.webp
-- Tatsaechliche Datei: /img/brands/fahrzeuge/logo-johndeere.png
--
-- Aenderungen:
-- a) .webp -> .png (Dateien sind PNG, nicht WebP)
-- b) Dateiname: {brand}.webp -> logo-{brand}.png

UPDATE vehicle_brands
SET logo_url = CONCAT(
    -- Pfad bis zum letzten Slash beibehalten
    SUBSTRING(logo_url, 1, LENGTH(logo_url) - LENGTH(SUBSTRING_INDEX(logo_url, '/', -1))),
    -- 'logo-' Prefix hinzufuegen
    'logo-',
    -- Dateiname ohne Endung
    REPLACE(SUBSTRING_INDEX(logo_url, '/', -1), '.webp', ''),
    -- Neue Endung
    '.png'
)
WHERE logo_url LIKE '%.webp';

-- ============================================
-- VERIFIZIERUNG (als SELECT - nicht ausfuehren, nur zur Kontrolle)
-- ============================================
-- Pruefe research_tree Icons:
-- SELECT rt.id, rt.name, rt.icon FROM research_tree rt WHERE rt.icon IS NOT NULL ORDER BY rt.id;
--
-- Pruefe korrigierte Brand-Logos:
-- SELECT id, brand_key, name, logo_url FROM vehicle_brands ORDER BY id;
--
-- Erwartetes Ergebnis fuer vehicle_brands:
-- johndeere: /img/brands/fahrzeuge/logo-johndeere.png
-- fendt:     /img/brands/fahrzeuge/logo-fendt.png
-- krone:     /img/brands/erntemaschinen/logo-krone.png
-- agi:       /img/brands/diverses/logo-agi.png
