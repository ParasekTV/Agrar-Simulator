-- ============================================
-- FIX: Umlaute in der Datenbank korrigieren
-- ============================================

SET NAMES utf8mb4;

-- ============================================
-- COOPERATIVE_RESEARCH_TREE
-- ============================================

UPDATE cooperative_research_tree SET
    name = 'Gemeinschafts-Mühle',
    description = 'Schaltet eine Mühle für die Genossenschaft frei'
WHERE name LIKE '%Muehle%' OR name LIKE '%Mühle%';

UPDATE cooperative_research_tree SET
    name = 'Gemeinschafts-Bäckerei',
    description = 'Schaltet eine Bäckerei für die Genossenschaft frei'
WHERE name LIKE '%Baeckerei%' OR name LIKE '%Bäckerei%';

UPDATE cooperative_research_tree SET
    name = 'Gemeinschafts-Käserei',
    description = 'Schaltet eine Käserei für die Genossenschaft frei'
WHERE name LIKE '%Kaeserei%' OR name LIKE '%Käserei%';

UPDATE cooperative_research_tree SET
    description = 'Schaltet eine Molkerei für die Genossenschaft frei'
WHERE name LIKE '%Molkerei%';

UPDATE cooperative_research_tree SET
    description = 'Schaltet eine Schlachterei für die Genossenschaft frei'
WHERE name LIKE '%Schlachterei%';

UPDATE cooperative_research_tree SET
    description = 'Schaltet eine Brauerei für die Genossenschaft frei'
WHERE name LIKE '%Brauerei%';

UPDATE cooperative_research_tree SET
    description = 'Schaltet eine Ölraffinerie für die Genossenschaft frei'
WHERE name LIKE '%Raffinerie%';

UPDATE cooperative_research_tree SET
    description = 'Erhöht die Lagerkapazität um 50%'
WHERE name LIKE '%Erweitertes Silo%';

UPDATE cooperative_research_tree SET
    description = 'Erhöht die Lagerkapazität um 100%'
WHERE name LIKE '%Grosses Silo%' OR name LIKE '%Großes Silo%';

UPDATE cooperative_research_tree SET
    name = 'Großes Silo',
    description = 'Erhöht die Lagerkapazität um 200%'
WHERE name LIKE '%Mega-Silo%';

UPDATE cooperative_research_tree SET
    description = 'Erhöht den Ernteertrag aller Mitglieder um 5%'
WHERE name LIKE '%Effiziente Ernte%';

UPDATE cooperative_research_tree SET
    description = 'Erhöht die Tierproduktion aller Mitglieder um 5%'
WHERE name LIKE '%Effiziente Tierhaltung%';

UPDATE cooperative_research_tree SET
    description = 'Erhöht Verkaufspreise um 5%'
WHERE name LIKE '%Handelsabkommen%';

UPDATE cooperative_research_tree SET
    description = 'Reduziert Einkaufspreise um 5%'
WHERE name LIKE '%Einkaufsgemeinschaft%';

-- ============================================
-- COOPERATIVE_CHALLENGE_TEMPLATES
-- ============================================

UPDATE cooperative_challenge_templates SET
    name = 'Kartoffel-König'
WHERE name = 'Kartoffel-Koenig';

UPDATE cooperative_challenge_templates SET
    name = 'Brot-Bäcker'
WHERE name = 'Brot-Baecker';

UPDATE cooperative_challenge_templates SET
    name = 'Käse-Könige',
    description = 'Produziert zusammen 50 Einheiten Käse'
WHERE name LIKE '%Kaese%' OR name LIKE '%Käse%';

UPDATE cooperative_challenge_templates SET
    name = 'Großzügige Gemeinschaft'
WHERE name = 'Grosszuegige Gemeinschaft';

UPDATE cooperative_challenge_templates SET
    description = 'Führt zusammen 50 Verkäufe durch'
WHERE name = 'Handels-Helden';

UPDATE cooperative_challenge_templates SET
    description = 'Schließt eine Genossenschafts-Forschung ab'
WHERE name = 'Forschungsdrang';

UPDATE cooperative_challenge_templates SET
    description = 'Kauft zusammen 3 Fahrzeuge für die Genossenschaft'
WHERE name = 'Fuhrpark-Ausbau';

UPDATE cooperative_challenge_templates SET
    description = 'Kauft zusammen 2 neue Felder für die Genossenschaft'
WHERE name = 'Feld-Expansion';

UPDATE cooperative_challenge_templates SET
    description = 'Baut mindestens 5 verschiedene Früchte an'
WHERE name = 'Vielfalt-Anbau';

UPDATE cooperative_challenge_templates SET
    description = 'Nutzt Genossenschafts-Fahrzeuge für 100 Stunden'
WHERE name = 'Fleissige Fahrer';

UPDATE cooperative_challenge_templates SET
    name = 'Fleißige Fahrer'
WHERE name = 'Fleissige Fahrer';

UPDATE cooperative_challenge_templates SET
    name = 'Geflügelbaron'
WHERE name = 'Gefluegelbaron';

-- ============================================
-- ANIMALS (Tiernamen)
-- ============================================

UPDATE animals SET name = 'Gänse' WHERE name = 'Gaense';
UPDATE animals SET name = 'Hühner' WHERE name = 'Huehner';
UPDATE animals SET name = 'Kühe' WHERE name = 'Kuehe';
UPDATE animals SET name = 'Ziegen' WHERE name = 'Ziege' OR name = 'Ziegen';

-- ============================================
-- PRODUCTIONS (Produktionen)
-- ============================================

UPDATE productions SET name = 'Bäckerei', name_de = 'Bäckerei' WHERE name = 'Baeckerei' OR name_de = 'Baeckerei';
UPDATE productions SET name = 'Käserei', name_de = 'Käserei' WHERE name = 'Kaeserei' OR name_de = 'Kaeserei';
UPDATE productions SET name = 'Mühle', name_de = 'Mühle' WHERE name = 'Muehle' OR name_de = 'Muehle';
UPDATE productions SET name = 'Süßwarenfabrik', name_de = 'Süßwarenfabrik' WHERE name LIKE '%Suess%' OR name_de LIKE '%Suess%';
UPDATE productions SET name = 'Gemüseverarbeitung', name_de = 'Gemüseverarbeitung' WHERE name LIKE '%Gemuese%' OR name_de LIKE '%Gemuese%';
UPDATE productions SET name = 'Kräutertrocknung', name_de = 'Kräutertrocknung' WHERE name LIKE '%Kraeuter%' OR name_de LIKE '%Kraeuter%';
UPDATE productions SET name = 'Gewächshaus', name_de = 'Gewächshaus' WHERE name LIKE '%Gewaechs%' OR name_de LIKE '%Gewaechs%';

-- ============================================
-- PRODUCTS (Produkte)
-- ============================================

UPDATE products SET name = 'Käse', name_de = 'Käse' WHERE name = 'Kaese' OR name_de = 'Kaese';
UPDATE products SET name = 'Hühnerfleisch', name_de = 'Hühnerfleisch' WHERE name LIKE '%Huehnerfleisch%' OR name_de LIKE '%Huehnerfleisch%';
UPDATE products SET name = 'Gänsefleisch', name_de = 'Gänsefleisch' WHERE name LIKE '%Gaensefleisch%' OR name_de LIKE '%Gaensefleisch%';
UPDATE products SET name = 'Dünger', name_de = 'Dünger' WHERE name = 'Duenger' OR name_de = 'Duenger';
UPDATE products SET name = 'Süßwaren', name_de = 'Süßwaren' WHERE name LIKE '%Suess%' OR name_de LIKE '%Suess%';
UPDATE products SET name = 'Kräuter', name_de = 'Kräuter' WHERE name LIKE '%Kraeuter%' OR name_de LIKE '%Kraeuter%';
UPDATE products SET name = 'Gemüse', name_de = 'Gemüse' WHERE name LIKE '%Gemuese%' OR name_de LIKE '%Gemuese%';
UPDATE products SET name = 'Früchte', name_de = 'Früchte' WHERE name = 'Fruechte' OR name_de = 'Fruechte';
UPDATE products SET name = 'Nüsse', name_de = 'Nüsse' WHERE name = 'Nuesse' OR name_de = 'Nuesse';
UPDATE products SET name = 'Blätter', name_de = 'Blätter' WHERE name = 'Blaetter' OR name_de = 'Blaetter';
UPDATE products SET name = 'Apfelmost', name_de = 'Apfelmost' WHERE name LIKE '%most%';

-- ============================================
-- RESEARCH_TREE (Forschungsbaum)
-- ============================================

UPDATE research_tree SET name = 'Gänsehaltung' WHERE name = 'Gaensehaltung';
UPDATE research_tree SET name = 'Hühnerhaltung' WHERE name = 'Huehnerhaltung';
UPDATE research_tree SET name = 'Kühltechnik' WHERE name = 'Kuehltechnik';
UPDATE research_tree SET name = 'Käseproduktion' WHERE name = 'Kaese%';
UPDATE research_tree SET name = 'Bäckereiproduktion' WHERE name LIKE '%Baeckerei%';
UPDATE research_tree SET name = 'Mühlenproduktion' WHERE name LIKE '%Muehlen%';
UPDATE research_tree SET name = 'Düngertechnik' WHERE name LIKE '%Duenger%';
UPDATE research_tree SET name = 'Grünlandpflege' WHERE name = 'Gruenlandpflege';

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

-- ============================================
-- Allgemeine REPLACE für alle Tabellen
-- ============================================

-- cooperative_research_tree Beschreibungen
UPDATE cooperative_research_tree SET description = REPLACE(description, 'fuer', 'für') WHERE description LIKE '%fuer%';
UPDATE cooperative_research_tree SET description = REPLACE(description, 'Erhoeht', 'Erhöht') WHERE description LIKE '%Erhoeht%';
UPDATE cooperative_research_tree SET description = REPLACE(description, 'Kapazitaet', 'Kapazität') WHERE description LIKE '%Kapazitaet%';
UPDATE cooperative_research_tree SET description = REPLACE(description, 'Oelraffinerie', 'Ölraffinerie') WHERE description LIKE '%Oelraffinerie%';

-- cooperative_challenge_templates Beschreibungen
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Fuehrt', 'Führt') WHERE description LIKE '%Fuehrt%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Verkaeufe', 'Verkäufe') WHERE description LIKE '%Verkaeufe%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Schliesst', 'Schließt') WHERE description LIKE '%Schliesst%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'fuer', 'für') WHERE description LIKE '%fuer%';
UPDATE cooperative_challenge_templates SET description = REPLACE(description, 'Fruechte', 'Früchte') WHERE description LIKE '%Fruechte%';
