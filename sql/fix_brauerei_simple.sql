-- ============================================
-- FIX: Brauerei Umlaut-Problem
-- ============================================

SET NAMES utf8mb4;

-- Zeige alle Produktionen mit "Brau" im Namen
SELECT id, name, name_de FROM productions WHERE name LIKE '%rau%' OR name_de LIKE '%rau%';

-- Korrigiere Braürei -> Brauerei (direkt)
UPDATE productions SET name = 'Brauerei', name_de = 'Brauerei' WHERE name = 'Braürei' OR name_de = 'Braürei';

-- Falls es auch "Gemeinschafts-Braürei" gibt
UPDATE productions SET
    name = REPLACE(name, 'Braürei', 'Brauerei'),
    name_de = REPLACE(name_de, 'Braürei', 'Brauerei')
WHERE name LIKE '%Braürei%' OR name_de LIKE '%Braürei%';

-- Zeige Ergebnis
SELECT id, name, name_de FROM productions WHERE name LIKE '%rau%' OR name_de LIKE '%rau%';

-- ============================================
-- DEBUG: Zeige alle cooperative_productions
-- ============================================

SELECT
    cp.id,
    cp.cooperative_id,
    cp.production_id,
    cp.purchased_by,
    cp.is_active,
    p.name_de as production_name,
    c.name as coop_name
FROM cooperative_productions cp
LEFT JOIN productions p ON cp.production_id = p.id
LEFT JOIN cooperatives c ON cp.cooperative_id = c.id;

-- ============================================
-- DEBUG: Zeige alle Produktionen mit "Bäckerei"
-- ============================================

SELECT id, name, name_de, building_cost FROM productions
WHERE name LIKE '%ckerei%' OR name_de LIKE '%ckerei%' OR name LIKE '%aeckerei%' OR name_de LIKE '%aeckerei%';
