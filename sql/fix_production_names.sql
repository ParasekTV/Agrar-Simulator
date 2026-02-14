-- ============================================
-- FIX: Produktionsnamen mit falschen Umlauten
-- ============================================

SET NAMES utf8mb4;

-- Productions Tabelle
UPDATE productions SET name = 'Büffelstall', name_de = 'Büffelstall' WHERE name = 'Bueffelstall' OR name_de = 'Bueffelstall';
UPDATE productions SET name = 'Gänsestall', name_de = 'Gänsestall' WHERE name = 'Gaensestall' OR name_de = 'Gaensestall';
UPDATE productions SET name = 'Brauerei', name_de = 'Brauerei' WHERE name = 'Braürei' OR name_de = 'Braürei' OR name = 'Brauerei' OR name_de = 'Brauerei';
UPDATE productions SET name = 'Hühnerstall', name_de = 'Hühnerstall' WHERE name = 'Huehnerstall' OR name_de = 'Huehnerstall';
UPDATE productions SET name = 'Kühlung', name_de = 'Kühlung' WHERE name = 'Kuehlung' OR name_de = 'Kuehlung';
UPDATE productions SET name = 'Ölmühle', name_de = 'Ölmühle' WHERE name = 'Oelmuehle' OR name_de = 'Oelmuehle';
UPDATE productions SET name = 'Geflügelschlachterei', name_de = 'Geflügelschlachterei' WHERE name LIKE '%Gefluegel%' OR name_de LIKE '%Gefluegel%';
UPDATE productions SET name = 'Gemüseverarbeitung', name_de = 'Gemüseverarbeitung' WHERE name LIKE '%Gemuese%' OR name_de LIKE '%Gemuese%';
UPDATE productions SET name = 'Süßwarenfabrik', name_de = 'Süßwarenfabrik' WHERE name LIKE '%Suess%' OR name_de LIKE '%Suess%';
UPDATE productions SET name = 'Kräutertrocknung', name_de = 'Kräutertrocknung' WHERE name LIKE '%Kraeuter%' OR name_de LIKE '%Kraeuter%';

-- Beschreibungen in productions
UPDATE productions SET description = REPLACE(description, 'Bueffel', 'Büffel') WHERE description LIKE '%Bueffel%';
UPDATE productions SET description = REPLACE(description, 'Gaense', 'Gänse') WHERE description LIKE '%Gaense%';
UPDATE productions SET description = REPLACE(description, 'Huehner', 'Hühner') WHERE description LIKE '%Huehner%';
UPDATE productions SET description = REPLACE(description, 'fuer', 'für') WHERE description LIKE '%fuer%';
UPDATE productions SET description = REPLACE(description, 'Kaese', 'Käse') WHERE description LIKE '%Kaese%';
UPDATE productions SET description = REPLACE(description, 'Muehle', 'Mühle') WHERE description LIKE '%Muehle%';
UPDATE productions SET description = REPLACE(description, 'Kuehe', 'Kühe') WHERE description LIKE '%Kuehe%';

-- Animals Tabelle
UPDATE animals SET name = 'Büffel', name_de = 'Büffel' WHERE name = 'Bueffel' OR name_de = 'Bueffel';
UPDATE animals SET name = 'Gänse', name_de = 'Gänse' WHERE name = 'Gaense' OR name_de = 'Gaense';
UPDATE animals SET name = 'Hühner', name_de = 'Hühner' WHERE name = 'Huehner' OR name_de = 'Huehner';
UPDATE animals SET name = 'Kühe', name_de = 'Kühe' WHERE name = 'Kuehe' OR name_de = 'Kuehe';

-- Products Tabelle
UPDATE products SET name = 'Büffelmilch', name_de = 'Büffelmilch' WHERE name LIKE '%Bueffelmilch%' OR name_de LIKE '%Bueffelmilch%';
UPDATE products SET name = 'Gänsefleisch', name_de = 'Gänsefleisch' WHERE name LIKE '%Gaensefleisch%' OR name_de LIKE '%Gaensefleisch%';
UPDATE products SET name = 'Gänsefedern', name_de = 'Gänsefedern' WHERE name LIKE '%Gaensefedern%' OR name_de LIKE '%Gaensefedern%';
UPDATE products SET name = 'Hühnerfleisch', name_de = 'Hühnerfleisch' WHERE name LIKE '%Huehnerfleisch%' OR name_de LIKE '%Huehnerfleisch%';
UPDATE products SET name = 'Käse', name_de = 'Käse' WHERE name = 'Kaese' OR name_de = 'Kaese';
UPDATE products SET name = 'Büffelkäse', name_de = 'Büffelkäse' WHERE name LIKE '%Bueffelkaese%' OR name_de LIKE '%Bueffelkaese%';
