-- ============================================
-- FIX: Produktionsnamen mit falschen Umlauten (v2)
-- ============================================
-- Behandelt Duplikate: Loescht falsch kodierte Eintraege wenn korrekte existieren

SET NAMES utf8mb4;

-- ============================================
-- STRATEGIE:
-- 1. Wenn korrekter Name existiert -> Referenzen vom falschen zum korrekten umleiten, dann falschen loeschen
-- 2. Wenn nur falscher Name existiert -> Umbenennen
-- ============================================

-- Hilfsprozedur fuer sicheres Umbenennen/Zusammenfuehren
DELIMITER //

DROP PROCEDURE IF EXISTS fix_production_name//

CREATE PROCEDURE fix_production_name(
    IN wrong_name VARCHAR(255),
    IN correct_name VARCHAR(255)
)
BEGIN
    DECLARE correct_id INT DEFAULT NULL;
    DECLARE wrong_id INT DEFAULT NULL;

    -- Finde IDs
    SELECT id INTO correct_id FROM productions WHERE name = correct_name OR name_de = correct_name LIMIT 1;
    SELECT id INTO wrong_id FROM productions WHERE (name = wrong_name OR name_de = wrong_name) AND id != COALESCE(correct_id, 0) LIMIT 1;

    IF wrong_id IS NOT NULL THEN
        IF correct_id IS NOT NULL THEN
            -- Beide existieren: Referenzen umleiten und falschen loeschen
            UPDATE IGNORE farm_productions SET production_id = correct_id WHERE production_id = wrong_id;
            UPDATE IGNORE cooperative_productions SET production_id = correct_id WHERE production_id = wrong_id;
            UPDATE IGNORE production_inputs SET production_id = correct_id WHERE production_id = wrong_id;
            UPDATE IGNORE production_outputs SET production_id = correct_id WHERE production_id = wrong_id;
            DELETE FROM productions WHERE id = wrong_id;
            SELECT CONCAT('Zusammengefuehrt: ', wrong_name, ' -> ', correct_name) AS result;
        ELSE
            -- Nur falscher existiert: Umbenennen
            UPDATE productions SET name = correct_name, name_de = correct_name WHERE id = wrong_id;
            SELECT CONCAT('Umbenannt: ', wrong_name, ' -> ', correct_name) AS result;
        END IF;
    ELSE
        SELECT CONCAT('Nichts zu tun fuer: ', wrong_name) AS result;
    END IF;
END//

DELIMITER ;

-- ============================================
-- PRODUKTIONEN FIXEN
-- ============================================

CALL fix_production_name('Bueffelstall', 'Büffelstall');
CALL fix_production_name('Gaensestall', 'Gänsestall');
CALL fix_production_name('Braürei', 'Brauerei');
CALL fix_production_name('Huehnerstall', 'Hühnerstall');
CALL fix_production_name('Kuehlung', 'Kühlung');
CALL fix_production_name('Oelmuehle', 'Ölmühle');
CALL fix_production_name('Gefluegel%', 'Geflügelschlachterei');
CALL fix_production_name('Gemuese%', 'Gemüseverarbeitung');
CALL fix_production_name('Suesswarenfabrik', 'Süßwarenfabrik');
CALL fix_production_name('Kraeutertrocknung', 'Kräutertrocknung');
CALL fix_production_name('Baeckerei', 'Bäckerei');
CALL fix_production_name('Kaeserei', 'Käserei');
CALL fix_production_name('Muehle', 'Mühle');

-- Prozedur aufraeumen
DROP PROCEDURE IF EXISTS fix_production_name;

-- ============================================
-- BESCHREIBUNGEN FIXEN (direkte Updates, keine Duplikat-Gefahr)
-- ============================================

UPDATE productions SET description = REPLACE(description, 'Bueffel', 'Büffel') WHERE description LIKE '%Bueffel%';
UPDATE productions SET description = REPLACE(description, 'Gaense', 'Gänse') WHERE description LIKE '%Gaense%';
UPDATE productions SET description = REPLACE(description, 'Huehner', 'Hühner') WHERE description LIKE '%Huehner%';
UPDATE productions SET description = REPLACE(description, 'fuer', 'für') WHERE description LIKE '%fuer%';
UPDATE productions SET description = REPLACE(description, 'Kaese', 'Käse') WHERE description LIKE '%Kaese%';
UPDATE productions SET description = REPLACE(description, 'Muehle', 'Mühle') WHERE description LIKE '%Muehle%';
UPDATE productions SET description = REPLACE(description, 'Kuehe', 'Kühe') WHERE description LIKE '%Kuehe%';
UPDATE productions SET description = REPLACE(description, 'Gefluegel', 'Geflügel') WHERE description LIKE '%Gefluegel%';
UPDATE productions SET description = REPLACE(description, 'Gemuese', 'Gemüse') WHERE description LIKE '%Gemuese%';

-- ============================================
-- ANIMALS TABELLE (nur name-Spalte, kein name_de)
-- ============================================

-- Einfache Updates da Animals weniger Referenzen haben
UPDATE IGNORE animals SET name = 'Büffel' WHERE name = 'Bueffel' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM animals WHERE name = 'Büffel') t);
UPDATE IGNORE animals SET name = 'Gänse' WHERE name = 'Gaense' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM animals WHERE name = 'Gänse') t);
UPDATE IGNORE animals SET name = 'Hühner' WHERE name = 'Huehner' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM animals WHERE name = 'Hühner') t);
UPDATE IGNORE animals SET name = 'Kühe' WHERE name = 'Kuehe' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM animals WHERE name = 'Kühe') t);

-- Loesche Duplikate bei Animals (behalte den mit korrektem Namen)
DELETE a1 FROM animals a1
INNER JOIN animals a2 ON a1.id > a2.id
WHERE (a1.name = 'Bueffel' AND a2.name = 'Büffel')
   OR (a1.name = 'Gaense' AND a2.name = 'Gänse')
   OR (a1.name = 'Huehner' AND a2.name = 'Hühner')
   OR (a1.name = 'Kuehe' AND a2.name = 'Kühe');

-- ============================================
-- PRODUCTS TABELLE
-- ============================================

UPDATE IGNORE products SET name = 'Büffelmilch', name_de = 'Büffelmilch' WHERE name LIKE '%Bueffelmilch%' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM products WHERE name = 'Büffelmilch') t);
UPDATE IGNORE products SET name = 'Gänsefleisch', name_de = 'Gänsefleisch' WHERE name LIKE '%Gaensefleisch%' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM products WHERE name = 'Gänsefleisch') t);
UPDATE IGNORE products SET name = 'Gänsefedern', name_de = 'Gänsefedern' WHERE name LIKE '%Gaensefedern%' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM products WHERE name = 'Gänsefedern') t);
UPDATE IGNORE products SET name = 'Hühnerfleisch', name_de = 'Hühnerfleisch' WHERE name LIKE '%Huehnerfleisch%' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM products WHERE name = 'Hühnerfleisch') t);
UPDATE IGNORE products SET name = 'Käse', name_de = 'Käse' WHERE name = 'Kaese' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM products WHERE name = 'Käse') t);
UPDATE IGNORE products SET name = 'Büffelkäse', name_de = 'Büffelkäse' WHERE name LIKE '%Bueffelkaese%' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM products WHERE name = 'Büffelkäse') t);

-- ============================================
-- VERIFIZIERUNG
-- ============================================

SELECT 'Produktionen mit Umlauten' AS Tabelle, name_de FROM productions WHERE name_de REGEXP '[äöüÄÖÜß]' LIMIT 20;
SELECT 'Tiere mit Umlauten' AS Tabelle, name FROM animals WHERE name REGEXP '[äöüÄÖÜß]' LIMIT 10;
SELECT 'Produkte mit Umlauten' AS Tabelle, name_de FROM products WHERE name_de REGEXP '[äöüÄÖÜß]' LIMIT 10;
