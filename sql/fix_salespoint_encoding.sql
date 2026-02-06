-- ============================================
-- Fix SalesPoint Encoding - Umlaute korrigieren
-- Erstellt am: 2026-02-06
-- ============================================

SET NAMES utf8mb4;

-- ============================================
-- Tabellen auf utf8mb4 konvertieren
-- ============================================

ALTER TABLE selling_points CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE selling_point_products CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE sales_history CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ============================================
-- Doppelt-kodierte Umlaute korrigieren
-- (UTF-8 Bytes als Latin-1 interpretiert und wieder als UTF-8 gespeichert)
-- ============================================

-- selling_points.name
UPDATE selling_points SET name = REPLACE(name, 'Ã¤', 'ä') WHERE name LIKE '%Ã¤%';
UPDATE selling_points SET name = REPLACE(name, 'Ã¶', 'ö') WHERE name LIKE '%Ã¶%';
UPDATE selling_points SET name = REPLACE(name, 'Ã¼', 'ü') WHERE name LIKE '%Ã¼%';
UPDATE selling_points SET name = REPLACE(name, 'Ã„', 'Ä') WHERE name LIKE '%Ã„%';
UPDATE selling_points SET name = REPLACE(name, 'Ã–', 'Ö') WHERE name LIKE '%Ã–%';
UPDATE selling_points SET name = REPLACE(name, 'Ãœ', 'Ü') WHERE name LIKE '%Ãœ%';
UPDATE selling_points SET name = REPLACE(name, 'ÃŸ', 'ß') WHERE name LIKE '%ÃŸ%';

-- selling_points.name_de
UPDATE selling_points SET name_de = REPLACE(name_de, 'Ã¤', 'ä') WHERE name_de LIKE '%Ã¤%';
UPDATE selling_points SET name_de = REPLACE(name_de, 'Ã¶', 'ö') WHERE name_de LIKE '%Ã¶%';
UPDATE selling_points SET name_de = REPLACE(name_de, 'Ã¼', 'ü') WHERE name_de LIKE '%Ã¼%';
UPDATE selling_points SET name_de = REPLACE(name_de, 'Ã„', 'Ä') WHERE name_de LIKE '%Ã„%';
UPDATE selling_points SET name_de = REPLACE(name_de, 'Ã–', 'Ö') WHERE name_de LIKE '%Ã–%';
UPDATE selling_points SET name_de = REPLACE(name_de, 'Ãœ', 'Ü') WHERE name_de LIKE '%Ãœ%';
UPDATE selling_points SET name_de = REPLACE(name_de, 'ÃŸ', 'ß') WHERE name_de LIKE '%ÃŸ%';

-- selling_points.description
UPDATE selling_points SET description = REPLACE(description, 'Ã¤', 'ä') WHERE description LIKE '%Ã¤%';
UPDATE selling_points SET description = REPLACE(description, 'Ã¶', 'ö') WHERE description LIKE '%Ã¶%';
UPDATE selling_points SET description = REPLACE(description, 'Ã¼', 'ü') WHERE description LIKE '%Ã¼%';
UPDATE selling_points SET description = REPLACE(description, 'Ã„', 'Ä') WHERE description LIKE '%Ã„%';
UPDATE selling_points SET description = REPLACE(description, 'Ã–', 'Ö') WHERE description LIKE '%Ã–%';
UPDATE selling_points SET description = REPLACE(description, 'Ãœ', 'Ü') WHERE description LIKE '%Ãœ%';
UPDATE selling_points SET description = REPLACE(description, 'ÃŸ', 'ß') WHERE description LIKE '%ÃŸ%';

-- selling_points.location
UPDATE selling_points SET location = REPLACE(location, 'Ã¤', 'ä') WHERE location LIKE '%Ã¤%';
UPDATE selling_points SET location = REPLACE(location, 'Ã¶', 'ö') WHERE location LIKE '%Ã¶%';
UPDATE selling_points SET location = REPLACE(location, 'Ã¼', 'ü') WHERE location LIKE '%Ã¼%';
UPDATE selling_points SET location = REPLACE(location, 'Ã„', 'Ä') WHERE location LIKE '%Ã„%';
UPDATE selling_points SET location = REPLACE(location, 'Ã–', 'Ö') WHERE location LIKE '%Ã–%';
UPDATE selling_points SET location = REPLACE(location, 'Ãœ', 'Ü') WHERE location LIKE '%Ãœ%';
UPDATE selling_points SET location = REPLACE(location, 'ÃŸ', 'ß') WHERE location LIKE '%ÃŸ%';

-- ============================================
-- Alternative Kodierungsprobleme beheben
-- (Variante: Ã¤ als Ã¤ gespeichert)
-- ============================================

-- Weitere mögliche falsche Kodierungen
UPDATE selling_points SET name = REPLACE(name, 'Ã¤', 'ä') WHERE name LIKE '%Ã¤%';
UPDATE selling_points SET name = REPLACE(name, 'Ã¶', 'ö') WHERE name LIKE '%Ã¶%';
UPDATE selling_points SET name = REPLACE(name, 'Ã¼', 'ü') WHERE name LIKE '%Ã¼%';
UPDATE selling_points SET name = REPLACE(name, 'Ã„', 'Ä') WHERE name LIKE '%Ã„%';
UPDATE selling_points SET name = REPLACE(name, 'Ã–', 'Ö') WHERE name LIKE '%Ã–%';
UPDATE selling_points SET name = REPLACE(name, 'Ãœ', 'Ü') WHERE name LIKE '%Ãœ%';
UPDATE selling_points SET name = REPLACE(name, 'ÃŸ', 'ß') WHERE name LIKE '%ÃŸ%';

UPDATE selling_points SET name_de = REPLACE(name_de, 'Ã¤', 'ä') WHERE name_de LIKE '%Ã¤%';
UPDATE selling_points SET name_de = REPLACE(name_de, 'Ã¶', 'ö') WHERE name_de LIKE '%Ã¶%';
UPDATE selling_points SET name_de = REPLACE(name_de, 'Ã¼', 'ü') WHERE name_de LIKE '%Ã¼%';
UPDATE selling_points SET name_de = REPLACE(name_de, 'Ã„', 'Ä') WHERE name_de LIKE '%Ã„%';
UPDATE selling_points SET name_de = REPLACE(name_de, 'Ã–', 'Ö') WHERE name_de LIKE '%Ã–%';
UPDATE selling_points SET name_de = REPLACE(name_de, 'Ãœ', 'Ü') WHERE name_de LIKE '%Ãœ%';
UPDATE selling_points SET name_de = REPLACE(name_de, 'ÃŸ', 'ß') WHERE name_de LIKE '%ÃŸ%';

UPDATE selling_points SET description = REPLACE(description, 'Ã¤', 'ä') WHERE description LIKE '%Ã¤%';
UPDATE selling_points SET description = REPLACE(description, 'Ã¶', 'ö') WHERE description LIKE '%Ã¶%';
UPDATE selling_points SET description = REPLACE(description, 'Ã¼', 'ü') WHERE description LIKE '%Ã¼%';
UPDATE selling_points SET description = REPLACE(description, 'Ã„', 'Ä') WHERE description LIKE '%Ã„%';
UPDATE selling_points SET description = REPLACE(description, 'Ã–', 'Ö') WHERE description LIKE '%Ã–%';
UPDATE selling_points SET description = REPLACE(description, 'Ãœ', 'Ü') WHERE description LIKE '%Ãœ%';
UPDATE selling_points SET description = REPLACE(description, 'ÃŸ', 'ß') WHERE description LIKE '%ÃŸ%';

UPDATE selling_points SET location = REPLACE(location, 'Ã¤', 'ä') WHERE location LIKE '%Ã¤%';
UPDATE selling_points SET location = REPLACE(location, 'Ã¶', 'ö') WHERE location LIKE '%Ã¶%';
UPDATE selling_points SET location = REPLACE(location, 'Ã¼', 'ü') WHERE location LIKE '%Ã¼%';
UPDATE selling_points SET location = REPLACE(location, 'Ã„', 'Ä') WHERE location LIKE '%Ã„%';
UPDATE selling_points SET location = REPLACE(location, 'Ã–', 'Ö') WHERE location LIKE '%Ã–%';
UPDATE selling_points SET location = REPLACE(location, 'Ãœ', 'Ü') WHERE location LIKE '%Ãœ%';
UPDATE selling_points SET location = REPLACE(location, 'ÃŸ', 'ß') WHERE location LIKE '%ÃŸ%';

-- ============================================
-- Verifizierung
-- ============================================

-- Zeige alle Verkaufsstellen zur Kontrolle
SELECT id, name, name_de, location FROM selling_points ORDER BY id;
