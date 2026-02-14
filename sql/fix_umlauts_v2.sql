-- ============================================
-- FIX: Umlaute in der Datenbank korrigieren (v2)
-- Vermeidet Duplikate
-- ============================================

SET NAMES utf8mb4;

-- ============================================
-- COOPERATIVE_RESEARCH_TREE
-- ============================================

UPDATE cooperative_research_tree SET
    name = 'Gemeinschafts-Mühle',
    description = 'Schaltet eine Mühle für die Genossenschaft frei'
WHERE name = 'Gemeinschafts-Muehle';

UPDATE cooperative_research_tree SET
    name = 'Gemeinschafts-Bäckerei',
    description = 'Schaltet eine Bäckerei für die Genossenschaft frei'
WHERE name = 'Gemeinschafts-Baeckerei';

UPDATE cooperative_research_tree SET
    name = 'Gemeinschafts-Käserei',
    description = 'Schaltet eine Käserei für die Genossenschaft frei'
WHERE name = 'Gemeinschafts-Kaeserei';

UPDATE cooperative_research_tree SET
    name = 'Großes Silo'
WHERE name = 'Grosses Silo';

UPDATE cooperative_research_tree SET
    description = 'Schaltet eine Molkerei für die Genossenschaft frei'
WHERE name = 'Gemeinschafts-Molkerei';

UPDATE cooperative_research_tree SET
    description = 'Schaltet eine Schlachterei für die Genossenschaft frei'
WHERE name = 'Gemeinschafts-Schlachterei';

UPDATE cooperative_research_tree SET
    description = 'Schaltet eine Brauerei für die Genossenschaft frei'
WHERE name = 'Gemeinschafts-Brauerei';

UPDATE cooperative_research_tree SET
    description = 'Schaltet eine Ölraffinerie für die Genossenschaft frei'
WHERE name = 'Gemeinschafts-Raffinerie';

UPDATE cooperative_research_tree SET
    description = 'Erhöht die Lagerkapazität um 50%'
WHERE name = 'Erweitertes Silo';

UPDATE cooperative_research_tree SET
    description = 'Erhöht die Lagerkapazität um 100%'
WHERE name = 'Grosses Silo' OR name = 'Großes Silo';

UPDATE cooperative_research_tree SET
    description = 'Erhöht die Lagerkapazität um 200%'
WHERE name = 'Mega-Silo';

UPDATE cooperative_research_tree SET
    description = 'Erhöht den Ernteertrag aller Mitglieder um 5%'
WHERE name = 'Effiziente Ernte';

UPDATE cooperative_research_tree SET
    description = 'Erhöht die Tierproduktion aller Mitglieder um 5%'
WHERE name = 'Effiziente Tierhaltung';

UPDATE cooperative_research_tree SET
    description = 'Erhöht Verkaufspreise um 5%'
WHERE name = 'Handelsabkommen';

UPDATE cooperative_research_tree SET
    description = 'Reduziert Einkaufspreise um 5%'
WHERE name = 'Einkaufsgemeinschaft';

-- ============================================
-- COOPERATIVE_CHALLENGE_TEMPLATES
-- ============================================

UPDATE cooperative_challenge_templates SET name = 'Kartoffel-König' WHERE name = 'Kartoffel-Koenig';
UPDATE cooperative_challenge_templates SET name = 'Brot-Bäcker' WHERE name = 'Brot-Baecker';
UPDATE cooperative_challenge_templates SET name = 'Käse-Könige', description = 'Produziert zusammen 50 Einheiten Käse' WHERE name = 'Kaese-Koenige';
UPDATE cooperative_challenge_templates SET name = 'Großzügige Gemeinschaft' WHERE name = 'Grosszuegige Gemeinschaft';
UPDATE cooperative_challenge_templates SET name = 'Fleißige Fahrer', description = 'Nutzt Genossenschafts-Fahrzeuge für 100 Stunden' WHERE name = 'Fleissige Fahrer';
UPDATE cooperative_challenge_templates SET name = 'Geflügelbaron' WHERE name = 'Gefluegelbaron';
UPDATE cooperative_challenge_templates SET description = 'Führt zusammen 50 Verkäufe durch' WHERE name = 'Handels-Helden';
UPDATE cooperative_challenge_templates SET description = 'Schließt eine Genossenschafts-Forschung ab' WHERE name = 'Forschungsdrang';
UPDATE cooperative_challenge_templates SET description = 'Kauft zusammen 3 Fahrzeuge für die Genossenschaft' WHERE name = 'Fuhrpark-Ausbau';
UPDATE cooperative_challenge_templates SET description = 'Kauft zusammen 2 neue Felder für die Genossenschaft' WHERE name = 'Feld-Expansion';
UPDATE cooperative_challenge_templates SET description = 'Baut mindestens 5 verschiedene Früchte an' WHERE name = 'Vielfalt-Anbau';

-- ============================================
-- ANIMALS (nur wenn ASCII-Version existiert)
-- ============================================

UPDATE animals SET name = 'Gänse' WHERE name = 'Gaense' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM animals WHERE name = 'Gänse') t);
UPDATE animals SET name = 'Hühner' WHERE name = 'Huehner' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM animals WHERE name = 'Hühner') t);
UPDATE animals SET name = 'Kühe' WHERE name = 'Kuehe' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM animals WHERE name = 'Kühe') t);

-- ============================================
-- PRODUCTIONS - Nur Beschreibungen aktualisieren, keine Namen ändern
-- (da die korrekten Namen bereits existieren könnten)
-- ============================================

UPDATE productions SET description = REPLACE(description, 'fuer', 'für') WHERE description LIKE '%fuer%';
UPDATE productions SET description = REPLACE(description, 'Kaese', 'Käse') WHERE description LIKE '%Kaese%';
UPDATE productions SET description = REPLACE(description, 'Baeckerei', 'Bäckerei') WHERE description LIKE '%Baeckerei%';
UPDATE productions SET description = REPLACE(description, 'Muehle', 'Mühle') WHERE description LIKE '%Muehle%';

-- Lösche doppelte Einträge mit ASCII-Namen falls korrekte existieren
DELETE p1 FROM productions p1
INNER JOIN productions p2
WHERE p1.id > p2.id
AND (
    (p1.name = 'Baeckerei' AND p2.name = 'Bäckerei') OR
    (p1.name = 'Kaeserei' AND p2.name = 'Käserei') OR
    (p1.name = 'Muehle' AND p2.name = 'Mühle')
);

-- ============================================
-- PRODUCTS - Nur Beschreibungen aktualisieren
-- ============================================

UPDATE products SET description = REPLACE(description, 'fuer', 'für') WHERE description LIKE '%fuer%';
UPDATE products SET description = REPLACE(description, 'Kaese', 'Käse') WHERE description LIKE '%Kaese%';
UPDATE products SET description = REPLACE(description, 'Huehner', 'Hühner') WHERE description LIKE '%Huehner%';
UPDATE products SET description = REPLACE(description, 'Gaense', 'Gänse') WHERE description LIKE '%Gaense%';

-- ============================================
-- RESEARCH_TREE
-- ============================================

UPDATE research_tree SET name = 'Gänsehaltung' WHERE name = 'Gaensehaltung' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM research_tree WHERE name = 'Gänsehaltung') t);
UPDATE research_tree SET name = 'Hühnerhaltung' WHERE name = 'Huehnerhaltung' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM research_tree WHERE name = 'Hühnerhaltung') t);
UPDATE research_tree SET name = 'Kühltechnik' WHERE name = 'Kuehltechnik' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM research_tree WHERE name = 'Kühltechnik') t);
UPDATE research_tree SET name = 'Grünlandpflege' WHERE name = 'Gruenlandpflege' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM research_tree WHERE name = 'Grünlandpflege') t);
UPDATE research_tree SET name = 'Düngertechnik' WHERE name = 'Duengertechnik' AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM research_tree WHERE name = 'Düngertechnik') t);

-- Beschreibungen aktualisieren
UPDATE research_tree SET description = REPLACE(description, 'fuer', 'für') WHERE description LIKE '%fuer%';
UPDATE research_tree SET description = REPLACE(description, 'Gaense', 'Gänse') WHERE description LIKE '%Gaense%';
UPDATE research_tree SET description = REPLACE(description, 'Huehner', 'Hühner') WHERE description LIKE '%Huehner%';
UPDATE research_tree SET description = REPLACE(description, 'Kaese', 'Käse') WHERE description LIKE '%Kaese%';
UPDATE research_tree SET description = REPLACE(description, 'Baeckerei', 'Bäckerei') WHERE description LIKE '%Baeckerei%';
UPDATE research_tree SET description = REPLACE(description, 'Muehle', 'Mühle') WHERE description LIKE '%Muehle%';
UPDATE research_tree SET description = REPLACE(description, 'erhoehen', 'erhöhen') WHERE description LIKE '%erhoehen%';
UPDATE research_tree SET description = REPLACE(description, 'Erhoeht', 'Erhöht') WHERE description LIKE '%Erhoeht%';
UPDATE research_tree SET description = REPLACE(description, 'groesser', 'größer') WHERE description LIKE '%groesser%';
UPDATE research_tree SET description = REPLACE(description, 'Duenger', 'Dünger') WHERE description LIKE '%Duenger%';
UPDATE research_tree SET description = REPLACE(description, 'Gruen', 'Grün') WHERE description LIKE '%Gruen%';
UPDATE research_tree SET description = REPLACE(description, 'Kuehe', 'Kühe') WHERE description LIKE '%Kuehe%';
UPDATE research_tree SET description = REPLACE(description, 'Kueh', 'Küh') WHERE description LIKE '%Kueh%';

-- ============================================
-- Allgemeine Beschreibungs-Korrekturen
-- ============================================

UPDATE cooperative_research_tree SET description = REPLACE(description, 'fuer', 'für') WHERE description LIKE '%fuer%';
UPDATE cooperative_research_tree SET description = REPLACE(description, 'Erhoeht', 'Erhöht') WHERE description LIKE '%Erhoeht%';
UPDATE cooperative_research_tree SET description = REPLACE(description, 'Kapazitaet', 'Kapazität') WHERE description LIKE '%Kapazitaet%';
UPDATE cooperative_research_tree SET description = REPLACE(description, 'Oelraffinerie', 'Ölraffinerie') WHERE description LIKE '%Oelraffinerie%';

UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Fuehrt', 'Führt') WHERE description LIKE '%Fuehrt%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Verkaeufe', 'Verkäufe') WHERE description LIKE '%Verkaeufe%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Schliesst', 'Schließt') WHERE description LIKE '%Schliesst%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'fuer', 'für') WHERE description LIKE '%fuer%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Fruechte', 'Früchte') WHERE description LIKE '%Fruechte%';
