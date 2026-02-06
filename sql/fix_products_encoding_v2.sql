-- ============================================
-- Fix: Products & Productions Encoding v2
-- Erstellt: 2026-02-06
-- ============================================
-- Einfache direkte Updates für bekannte Umlaut-Probleme
-- ============================================

SET NAMES utf8mb4;

-- ============================================
-- 1. PRODUCTS: ASCII-UMLAUTE ERSETZEN
-- ============================================

-- Büffel-Produkte
UPDATE products SET name_de = 'Büffelmilch' WHERE name = 'Bueffelmilch';
UPDATE products SET name_de = 'Büffelmozzarella' WHERE name = 'Bueffelmozzarella';

-- Getränke/Liköre
UPDATE products SET name_de = 'Eierlikör' WHERE name = 'Eierlikoer';
UPDATE products SET name_de = 'Kräuterlikör' WHERE name = 'Kraeuterlikoer';

-- Bäckerei
UPDATE products SET name_de = 'Brötchen' WHERE name = 'Broetchen';

-- Käse
UPDATE products SET name_de = 'Frischkäse' WHERE name = 'Frischkaese';
UPDATE products SET name_de = 'Käse' WHERE name = 'Kaese';
UPDATE products SET name_de = 'Ziegenkäse' WHERE name = 'Ziegenkaese';
UPDATE products SET name_de = 'Schafskäse' WHERE name = 'Schafskaese';

-- Gemüse
UPDATE products SET name_de = 'Frühlingslauch' WHERE name = 'Fruehlingslauch';
UPDATE products SET name_de = 'Gemüse' WHERE name = 'Gemuese';
UPDATE products SET name_de = 'Gemüsesuppe' WHERE name = 'Gemuesesuppe';

-- Dünger
UPDATE products SET name_de = 'Flüssigdünger' WHERE name = 'Fluessigduenger';
UPDATE products SET name_de = 'Dünger' WHERE name = 'Duenger';

-- Öl
UPDATE products SET name_de = 'Öl' WHERE name = 'Oel';
UPDATE products SET name_de = 'Rapsöl' WHERE name = 'Rapsoel';
UPDATE products SET name_de = 'Sonnenblumenöl' WHERE name = 'Sonnenblumenoel';
UPDATE products SET name_de = 'Olivenöl' WHERE name = 'Olivenoel';

-- Säfte
UPDATE products SET name_de = 'Trübsaft' WHERE name = 'Truebsaft';

-- Sonstiges
UPDATE products SET name_de = 'Gülle' WHERE name = 'Guelle';
UPDATE products SET name_de = 'Hühner' WHERE name = 'Huehner';
UPDATE products SET name_de = 'Geflügel' WHERE name = 'Gefluegel';
UPDATE products SET name_de = 'Geflügelfleisch' WHERE name = 'Gefluegelfleisch';
UPDATE products SET name_de = 'Hühnerfleisch' WHERE name = 'Huehnerfleisch';
UPDATE products SET name_de = 'Würstchen' WHERE name = 'Wuerstchen';
UPDATE products SET name_de = 'Süßkartoffel' WHERE name = 'Suesskartoffel';
UPDATE products SET name_de = 'Süßkartoffeln' WHERE name = 'Suesskartoffeln';
UPDATE products SET name_de = 'Kürbis' WHERE name = 'Kuerbis';
UPDATE products SET name_de = 'Kürbisse' WHERE name = 'Kuerbisse';
UPDATE products SET name_de = 'Kürbissuppe' WHERE name = 'Kuerbissuppe';
UPDATE products SET name_de = 'Nüsse' WHERE name = 'Nuesse';
UPDATE products SET name_de = 'Müsli' WHERE name = 'Muesli';
UPDATE products SET name_de = 'Hülsenfrüchte' WHERE name = 'Huelsenfruechte';
UPDATE products SET name_de = 'Früchte' WHERE name = 'Fruechte';
UPDATE products SET name_de = 'Trockenfrüchte' WHERE name = 'Trockenfruechte';

-- ============================================
-- 2. PRODUCTIONS: ASCII-UMLAUTE ERSETZEN
-- ============================================

UPDATE productions SET name_de = 'Bäckerei' WHERE name = 'Baeckerei';
UPDATE productions SET name_de = 'Käserei' WHERE name = 'Kaeserei';
UPDATE productions SET name_de = 'Sägewerk' WHERE name = 'Saegewerk';
UPDATE productions SET name_de = 'Ölmühle' WHERE name = 'Oelmuehle';
UPDATE productions SET name_de = 'Räucherei' WHERE name = 'Raeucherei';
UPDATE productions SET name_de = 'Klärwerk' WHERE name = 'Klaerwerk';
UPDATE productions SET name_de = 'Gewächshaus' WHERE name = 'Gewaechshaus';
UPDATE productions SET name_de = 'Gewächshaus XL' WHERE name = 'Gewaechshaus XL';
UPDATE productions SET name_de = 'Gewächshaus Pilze' WHERE name = 'Gewaechshaus Pilze';
UPDATE productions SET name_de = 'Holzfäller' WHERE name = 'Holzfaeller';
UPDATE productions SET name_de = 'Düngerherstellung' WHERE name = 'Duengerherstellung';
UPDATE productions SET name_de = 'Gemüsefabrik' WHERE name = 'Gemuesefabrik';
UPDATE productions SET name_de = 'Kürbisfeld' WHERE name = 'Kuerbisfeld';
UPDATE productions SET name_de = 'Rübenschnitzel' WHERE name = 'Ruebenschnitzel';
UPDATE productions SET name_de = 'Büffelstall' WHERE name = 'Bueffelstall';
UPDATE productions SET name_de = 'Hühnerstall' WHERE name = 'Huehnerstall';
UPDATE productions SET name_de = 'Geflügelfarm' WHERE name = 'Gefluegelfarm';
UPDATE productions SET name_de = 'Mühle' WHERE name = 'Muehle';
UPDATE productions SET name_de = 'Getreidemühle' WHERE name = 'Getreidemuehle';

-- ============================================
-- 3. ALLGEMEINE ERSETZUNGEN FÜR RESTLICHE FÄLLE
-- ============================================

-- Products: Ersetze verbleibende ASCII-Umlaute
UPDATE products SET name_de = REPLACE(name_de, 'ue', 'ü')
WHERE name_de LIKE '%ue%'
  AND name_de NOT LIKE '%queue%'
  AND name_de NOT LIKE '%quer%'
  AND name_de NOT LIKE '%Quer%'
  AND name_de NOT LIKE '%bauer%'
  AND name_de NOT LIKE '%Bauer%'
  AND name_de NOT LIKE '%euer%'
  AND name_de NOT LIKE '%Euer%'
  AND name_de NOT LIKE '%steuer%'
  AND name_de NOT LIKE '%Steuer%'
  AND name_de NOT LIKE '%neuer%'
  AND name_de NOT LIKE '%Neuer%'
  AND name_de NOT LIKE '%teuer%'
  AND name_de NOT LIKE '%Teuer%';

UPDATE products SET name_de = REPLACE(name_de, 'ae', 'ä')
WHERE name_de LIKE '%ae%'
  AND name_de NOT LIKE '%mae%'
  AND name_de NOT LIKE '%Mae%'
  AND name_de NOT LIKE '%israel%'
  AND name_de NOT LIKE '%Israel%';

UPDATE products SET name_de = REPLACE(name_de, 'oe', 'ö')
WHERE name_de LIKE '%oe%'
  AND name_de NOT LIKE '%poet%'
  AND name_de NOT LIKE '%Poet%'
  AND name_de NOT LIKE '%boeing%'
  AND name_de NOT LIKE '%Boeing%';

-- Productions: Ersetze verbleibende ASCII-Umlaute
UPDATE productions SET name_de = REPLACE(name_de, 'ue', 'ü')
WHERE name_de LIKE '%ue%'
  AND name_de NOT LIKE '%queue%'
  AND name_de NOT LIKE '%quer%'
  AND name_de NOT LIKE '%Quer%'
  AND name_de NOT LIKE '%bauer%'
  AND name_de NOT LIKE '%Bauer%';

UPDATE productions SET name_de = REPLACE(name_de, 'ae', 'ä')
WHERE name_de LIKE '%ae%';

UPDATE productions SET name_de = REPLACE(name_de, 'oe', 'ö')
WHERE name_de LIKE '%oe%';

-- ============================================
-- 4. GROßBUCHSTABEN-UMLAUTE
-- ============================================

UPDATE products SET name_de = REPLACE(name_de, 'Ue', 'Ü') WHERE name_de LIKE '%Ue%';
UPDATE products SET name_de = REPLACE(name_de, 'Ae', 'Ä') WHERE name_de LIKE '%Ae%';
UPDATE products SET name_de = REPLACE(name_de, 'Oe', 'Ö') WHERE name_de LIKE '%Oe%';

UPDATE productions SET name_de = REPLACE(name_de, 'Ue', 'Ü') WHERE name_de LIKE '%Ue%';
UPDATE productions SET name_de = REPLACE(name_de, 'Ae', 'Ä') WHERE name_de LIKE '%Ae%';
UPDATE productions SET name_de = REPLACE(name_de, 'Oe', 'Ö') WHERE name_de LIKE '%Oe%';

-- ============================================
-- FERTIG
-- ============================================
