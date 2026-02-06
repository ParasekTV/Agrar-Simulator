-- ============================================
-- Fix: Research-Tree Encoding + Icons
-- Erstellt: 2026-02-05
-- ============================================
-- Problem 1: phase1_crops.sql wurde mit latin1-Verbindung importiert
--            -> UTF-8 Umlaute doppelt kodiert (z.B. "GemÃ¼sebau" statt "Gemüsebau")
-- Problem 2: productions_migration.sql + install.sql verwenden ASCII-Umlaute
--            (ae/oe/ue statt ä/ö/ü)
-- Problem 3: Fehlende Icons fuer Forschungseintraege
-- ============================================

SET NAMES utf8mb4;

-- ============================================
-- 1. ICON-SPALTE SICHERSTELLEN
-- ============================================

ALTER TABLE research_tree ADD COLUMN IF NOT EXISTS icon VARCHAR(100) DEFAULT NULL;

-- ============================================
-- 2. DOPPELT-KODIERTES UTF-8 REPARIEREN
-- ============================================
-- Betrifft Zeilen aus phase1_crops.sql (IDs 10-17)
-- CONVERT(BINARY CONVERT(... USING latin1) USING utf8mb4) dekodiert
-- die falschen Bytes zurueck zu korrektem UTF-8.

UPDATE research_tree
SET name = CONVERT(BINARY CONVERT(name USING latin1) USING utf8mb4)
WHERE HEX(name) LIKE '%C383%'
   OR HEX(name) LIKE '%C384%'
   OR HEX(name) LIKE '%C396%'
   OR HEX(name) LIKE '%C39C%';

UPDATE research_tree
SET description = CONVERT(BINARY CONVERT(description USING latin1) USING utf8mb4)
WHERE HEX(description) LIKE '%C383%'
   OR HEX(description) LIKE '%C384%'
   OR HEX(description) LIKE '%C396%'
   OR HEX(description) LIKE '%C39C%';

-- ============================================
-- 3. ASCII-UMLAUTE KORRIGIEREN (install.sql IDs 1-9)
-- ============================================

UPDATE research_tree SET
    name = 'Viehzucht Basics',
    description = 'Ermöglicht Haltung von Kühen und Schweinen'
WHERE id = 2;

UPDATE research_tree SET
    description = 'Ermöglicht Anbau von Sonnenblumen und Zuckerrüben'
WHERE id = 4;

UPDATE research_tree SET
    name = 'Großviehzucht',
    description = 'Schaltet Schafe frei'
WHERE id = 5;

UPDATE research_tree SET
    description = 'Schaltet Mähdrescher frei'
WHERE id = 6;

UPDATE research_tree SET
    name = 'Gewächshaus-Technologie',
    description = 'Ermöglicht ganzjährigen Anbau'
WHERE id = 7;

UPDATE research_tree SET
    description = 'Reduziert Arbeitszeit um 20%'
WHERE id = 8;

UPDATE research_tree SET
    description = 'Höhere Verkaufspreise für Bio-Produkte'
WHERE id = 9;

-- ============================================
-- 4. ASCII-UMLAUTE KORRIGIEREN (productions_migration.sql IDs 100-170)
-- ============================================

UPDATE research_tree SET name = 'Produktion: Bäckerei', description = 'Schaltet die Produktion Bäckerei frei.' WHERE id = 103;
UPDATE research_tree SET name = 'Produktion: Düngerherstellung', description = 'Schaltet die Produktion Düngerherstellung frei.' WHERE id = 105;
UPDATE research_tree SET name = 'Produktion: Kürbisfeld', description = 'Schaltet die Produktion Kürbisfeld frei.' WHERE id = 109;
UPDATE research_tree SET name = 'Produktion: Gemüsefabrik', description = 'Schaltet die Produktion Gemüsefabrik frei.' WHERE id = 122;
UPDATE research_tree SET name = 'Produktion: Gewächshaus Pilze', description = 'Schaltet die Produktion Gewächshaus Pilze frei.' WHERE id = 125;
UPDATE research_tree SET name = 'Produktion: Gewächshaus', description = 'Schaltet die Produktion Gewächshaus frei.' WHERE id = 126;
UPDATE research_tree SET name = 'Produktion: Gewächshaus XL', description = 'Schaltet die Produktion Gewächshaus XL frei.' WHERE id = 127;
UPDATE research_tree SET name = 'Produktion: Holzfäller', description = 'Schaltet die Produktion Holzfäller frei.' WHERE id = 133;
UPDATE research_tree SET name = 'Produktion: Klärwerk', description = 'Schaltet die Produktion Klärwerk frei.' WHERE id = 141;
UPDATE research_tree SET name = 'Produktion: Käserei', description = 'Schaltet die Produktion Käserei frei.' WHERE id = 144;
UPDATE research_tree SET name = 'Produktion: Räucherei', description = 'Schaltet die Produktion Räucherei frei.' WHERE id = 158;
UPDATE research_tree SET name = 'Produktion: Rübenschnitzel', description = 'Schaltet die Produktion Rübenschnitzel frei.' WHERE id = 159;
UPDATE research_tree SET name = 'Produktion: Sägewerk', description = 'Schaltet die Produktion Sägewerk frei.' WHERE id = 161;
UPDATE research_tree SET name = 'Produktion: Ölmühle', description = 'Schaltet die Produktion Ölmühle frei.' WHERE id = 170;

-- ============================================
-- 5. ASCII-UMLAUTE KORRIGIEREN (Tierhaltung IDs 180-187)
-- ============================================

UPDATE research_tree SET name = 'Tierhaltung: Hühnerstall', description = 'Schaltet Hühnerstall frei.' WHERE id = 180;
UPDATE research_tree SET name = 'Tierhaltung: Büffelstall', description = 'Schaltet Büffelstall frei.' WHERE id = 186;

-- ============================================
-- 6. ICON-PFADE VEREINHEITLICHEN
-- ============================================
-- Alle Icons bekommen den vollen Unterpfad relativ zu img/
-- View wird angepasst: img/{icon} statt img/productions/{icon}

-- Bestehende Produktions-Icons: Pfad-Prefix ergaenzen
UPDATE research_tree
SET icon = CONCAT('productions/', icon)
WHERE icon IS NOT NULL
  AND icon != ''
  AND icon NOT LIKE '%/%';

-- ============================================
-- 7. ICONS FUER PRODUKTIONS-FORSCHUNGEN SETZEN
-- ============================================
-- Kopiere Icon aus der zugehoerigen Produktion (mit Pfad-Prefix)

UPDATE research_tree rt
JOIN productions p ON p.required_research_id = rt.id
SET rt.icon = CONCAT('productions/', p.icon)
WHERE rt.id BETWEEN 100 AND 170
  AND p.icon IS NOT NULL
  AND (rt.icon IS NULL OR rt.icon = '');

-- ============================================
-- 8. ICONS FUER BASIS-FORSCHUNGEN SETZEN
-- ============================================
-- Verwende passende vorhandene Icons aus misc/ und products/

-- Basis (IDs 1-9)
UPDATE research_tree SET icon = 'misc/aus_eigener_ernte.png' WHERE id = 1 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/aus_einem_rinderstall.png' WHERE id = 2 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/fuer_eine_quaderballenpresse_oder.png' WHERE id = 3 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/aus_eigener_ernte_2.png' WHERE id = 4 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/schweine_aus_einem_schweinestall.png' WHERE id = 5 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/fuer_eine_quaderballenpresse_oder.png' WHERE id = 6 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/aus_einem_gewaechshaus.png' WHERE id = 7 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/aus_dem_labor.png' WHERE id = 8 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/aus_eigener_ernte_3.png' WHERE id = 9 AND (icon IS NULL OR icon = '');

-- Phase 1 Crops (IDs 10-17)
UPDATE research_tree SET icon = 'misc/aus_eigener_ernte_4.png' WHERE id = 10 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/aus_eigener_ernte_luzerne_heu.png' WHERE id = 11 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/aus_eigener_ernte_5.png' WHERE id = 12 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/aus_der_gemuesefabrik.png' WHERE id = 13 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/aus_einer_obstplantage.png' WHERE id = 14 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/von_der_duengerproduktion.png' WHERE id = 15 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/aus_dem_labor_2.png' WHERE id = 16 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/erde.png' WHERE id = 17 AND (icon IS NULL OR icon = '');

-- Tierhaltung (IDs 180-187)
UPDATE research_tree SET icon = 'misc/aus_einem_huehnerstall.png' WHERE id = 180 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/eier_aus_einem_huehnerstall.png' WHERE id = 181 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/schweine_aus_einem_schweinestall.png' WHERE id = 182 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/aus_einem_rinderstall.png' WHERE id = 183 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'products/lammfleisch.png' WHERE id = 184 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/ziegenmilch_aus_einem_ziegenstall.png' WHERE id = 185 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'misc/aus_einem_rinderstall_oder_ziegenstall.png' WHERE id = 186 AND (icon IS NULL OR icon = '');
UPDATE research_tree SET icon = 'products/bienenstock.png' WHERE id = 187 AND (icon IS NULL OR icon = '');

-- ============================================
-- VERIFIZIERUNG (SELECT - nur zur Kontrolle)
-- ============================================
-- SELECT id, name, icon FROM research_tree ORDER BY id;
-- Prüfe auf verbleibende Encoding-Probleme:
-- SELECT id, name FROM research_tree WHERE name LIKE '%Ã%' OR name LIKE '%ae%' OR name LIKE '%oe%' OR name LIKE '%ue%';
