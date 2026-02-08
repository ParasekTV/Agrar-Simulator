SET NAMES utf8mb4;

-- ============================================
-- FIX UMLAUT-ENCODING IN COOPERATIVE_RESEARCH_TREE
-- ============================================

-- Gemeinschafts-Mühle
UPDATE cooperative_research_tree SET
    name = 'Gemeinschafts-Mühle',
    description = 'Schaltet eine Mühle für die Genossenschaft frei'
WHERE name LIKE '%Muehle%' OR name LIKE '%Mühle%';

-- Gemeinschafts-Bäckerei
UPDATE cooperative_research_tree SET
    name = 'Gemeinschafts-Bäckerei',
    description = 'Schaltet eine Bäckerei für die Genossenschaft frei'
WHERE name LIKE '%Baeckerei%' OR name LIKE '%Bäckerei%';

-- Gemeinschafts-Käserei
UPDATE cooperative_research_tree SET
    name = 'Gemeinschafts-Käserei',
    description = 'Schaltet eine Käserei für die Genossenschaft frei'
WHERE name LIKE '%Kaeserei%' OR name LIKE '%Käserei%';

-- Großlager
UPDATE cooperative_research_tree SET
    name = 'Großlager',
    description = 'Erhöht die Lagerkapazität der Genossenschaft'
WHERE name LIKE '%Grosslager%' OR name LIKE '%Großlager%';

-- Effizienzsteigerung
UPDATE cooperative_research_tree SET
    description = 'Erhöht die Produktionseffizienz der Genossenschaft'
WHERE name LIKE '%Effizienz%';

-- Kühlhaus
UPDATE cooperative_research_tree SET
    name = 'Kühlhaus',
    description = 'Ermöglicht die Lagerung von verderblichen Waren'
WHERE name LIKE '%Kuehlhaus%' OR name LIKE '%Kühlhaus%';

-- Weitere potentielle Einträge korrigieren
UPDATE cooperative_research_tree SET
    description = REPLACE(description, 'fuer', 'für')
WHERE description LIKE '%fuer%';

UPDATE cooperative_research_tree SET
    description = REPLACE(description, 'erhoehen', 'erhöhen')
WHERE description LIKE '%erhoehen%';

UPDATE cooperative_research_tree SET
    description = REPLACE(description, 'Erhoehung', 'Erhöhung')
WHERE description LIKE '%Erhoehung%';

UPDATE cooperative_research_tree SET
    description = REPLACE(description, 'Kapazitaet', 'Kapazität')
WHERE description LIKE '%Kapazitaet%';

UPDATE cooperative_research_tree SET
    description = REPLACE(description, 'Qualitaet', 'Qualität')
WHERE description LIKE '%Qualitaet%';

SELECT 'Cooperative research tree encoding fixed.' AS status;
