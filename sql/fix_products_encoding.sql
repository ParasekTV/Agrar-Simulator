-- ============================================
-- Fix: Products & Productions Encoding
-- Erstellt: 2026-02-06
-- ============================================
-- Problem: UTF-8 Umlaute wurden doppelt kodiert
-- z.B. "BÃ¼ffelmilch" statt "Büffelmilch"
-- ============================================

SET NAMES utf8mb4;

-- ============================================
-- 1. DOPPELT-KODIERTES UTF-8 IN PRODUCTS REPARIEREN
-- ============================================
-- CONVERT(BINARY CONVERT(... USING latin1) USING utf8mb4) dekodiert
-- die falschen Bytes zurück zu korrektem UTF-8.

-- name_de Spalte
UPDATE products
SET name_de = CONVERT(BINARY CONVERT(name_de USING latin1) USING utf8mb4)
WHERE HEX(name_de) LIKE '%C383%'
   OR HEX(name_de) LIKE '%C384%'
   OR HEX(name_de) LIKE '%C396%'
   OR HEX(name_de) LIKE '%C39C%'
   OR HEX(name_de) LIKE '%C382%';

-- name Spalte
UPDATE products
SET name = CONVERT(BINARY CONVERT(name USING latin1) USING utf8mb4)
WHERE HEX(name) LIKE '%C383%'
   OR HEX(name) LIKE '%C384%'
   OR HEX(name) LIKE '%C396%'
   OR HEX(name) LIKE '%C39C%'
   OR HEX(name) LIKE '%C382%';

-- description Spalte (falls vorhanden)
UPDATE products
SET description = CONVERT(BINARY CONVERT(description USING latin1) USING utf8mb4)
WHERE description IS NOT NULL
  AND (HEX(description) LIKE '%C383%'
   OR HEX(description) LIKE '%C384%'
   OR HEX(description) LIKE '%C396%'
   OR HEX(description) LIKE '%C39C%'
   OR HEX(description) LIKE '%C382%');

-- ============================================
-- 2. DOPPELT-KODIERTES UTF-8 IN PRODUCTIONS REPARIEREN
-- ============================================

-- name_de Spalte
UPDATE productions
SET name_de = CONVERT(BINARY CONVERT(name_de USING latin1) USING utf8mb4)
WHERE HEX(name_de) LIKE '%C383%'
   OR HEX(name_de) LIKE '%C384%'
   OR HEX(name_de) LIKE '%C396%'
   OR HEX(name_de) LIKE '%C39C%'
   OR HEX(name_de) LIKE '%C382%';

-- name Spalte
UPDATE productions
SET name = CONVERT(BINARY CONVERT(name USING latin1) USING utf8mb4)
WHERE HEX(name) LIKE '%C383%'
   OR HEX(name) LIKE '%C384%'
   OR HEX(name) LIKE '%C396%'
   OR HEX(name) LIKE '%C39C%'
   OR HEX(name) LIKE '%C382%';

-- description Spalte (falls vorhanden)
UPDATE productions
SET description = CONVERT(BINARY CONVERT(description USING latin1) USING utf8mb4)
WHERE description IS NOT NULL
  AND (HEX(description) LIKE '%C383%'
   OR HEX(description) LIKE '%C384%'
   OR HEX(description) LIKE '%C396%'
   OR HEX(description) LIKE '%C39C%'
   OR HEX(description) LIKE '%C382%');

-- ============================================
-- 3. ASCII-UMLAUTE DURCH ECHTE UMLAUTE ERSETZEN
-- ============================================
-- Für Fälle, wo ae/oe/ue verwendet wurde

-- Products
UPDATE products SET name_de = REPLACE(name_de, 'ae', 'ä') WHERE name_de LIKE '%ae%' AND name_de NOT LIKE '%Maerkte%';
UPDATE products SET name_de = REPLACE(name_de, 'oe', 'ö') WHERE name_de LIKE '%oe%';
UPDATE products SET name_de = REPLACE(name_de, 'ue', 'ü') WHERE name_de LIKE '%ue%' AND name_de NOT LIKE '%quer%' AND name_de NOT LIKE '%Quer%';
UPDATE products SET name_de = REPLACE(name_de, 'Ae', 'Ä') WHERE name_de LIKE '%Ae%';
UPDATE products SET name_de = REPLACE(name_de, 'Oe', 'Ö') WHERE name_de LIKE '%Oe%';
UPDATE products SET name_de = REPLACE(name_de, 'Ue', 'Ü') WHERE name_de LIKE '%Ue%';
UPDATE products SET name_de = REPLACE(name_de, 'ss', 'ß') WHERE name_de LIKE '%strasse%' OR name_de LIKE '%Strasse%';

-- Productions
UPDATE productions SET name_de = REPLACE(name_de, 'ae', 'ä') WHERE name_de LIKE '%ae%' AND name_de NOT LIKE '%Maerkte%';
UPDATE productions SET name_de = REPLACE(name_de, 'oe', 'ö') WHERE name_de LIKE '%oe%';
UPDATE productions SET name_de = REPLACE(name_de, 'ue', 'ü') WHERE name_de LIKE '%ue%' AND name_de NOT LIKE '%quer%' AND name_de NOT LIKE '%Quer%';
UPDATE productions SET name_de = REPLACE(name_de, 'Ae', 'Ä') WHERE name_de LIKE '%Ae%';
UPDATE productions SET name_de = REPLACE(name_de, 'Oe', 'Ö') WHERE name_de LIKE '%Oe%';
UPDATE productions SET name_de = REPLACE(name_de, 'Ue', 'Ü') WHERE name_de LIKE '%Ue%';

-- ============================================
-- 4. SPEZIFISCHE KORREKTUREN
-- ============================================

-- Bekannte Produkte mit Umlauten
UPDATE products SET name_de = 'Büffelmilch' WHERE name = 'Bueffelmilch' OR name_de LIKE '%ffelmilch%';
UPDATE products SET name_de = 'Büffelmozzarella' WHERE name = 'Bueffelmozzarella' OR name_de LIKE '%ffelmozzarella%';
UPDATE products SET name_de = 'Flüssigdünger' WHERE name = 'Fluessigduenger' OR name_de LIKE '%ssigd%nger%';
UPDATE products SET name_de = 'Brötchen' WHERE name = 'Broetchen' OR name_de LIKE '%r%tchen%';
UPDATE products SET name_de = 'Käse' WHERE name = 'Kaese' AND name_de LIKE '%K%se%';
UPDATE products SET name_de = 'Frischkäse' WHERE name = 'Frischkaese' OR name_de LIKE '%Frischk%se%';
UPDATE products SET name_de = 'Frühlingslauch' WHERE name = 'Fruehlingslauch' OR name_de LIKE '%Fr%hlingslauch%';
UPDATE products SET name_de = 'Eierlikör' WHERE name = 'Eierlikoer' OR name_de LIKE '%Eierlik%r%';
UPDATE products SET name_de = 'Gemüse' WHERE name_de LIKE '%Gem%se%';
UPDATE products SET name_de = 'Öl' WHERE name = 'Oel' AND name_de LIKE '%l%';
UPDATE products SET name_de = 'Ölmühle' WHERE name_de LIKE '%lm%hle%';
UPDATE products SET name_de = 'Sägewerk' WHERE name_de LIKE '%S%gewerk%';
UPDATE products SET name_de = 'Räucherei' WHERE name_de LIKE '%R%ucherei%';

-- ============================================
-- VERIFIZIERUNG (SELECT - nur zur Kontrolle)
-- ============================================
-- Prüfe auf verbleibende Encoding-Probleme:
-- SELECT id, name, name_de FROM products WHERE name_de LIKE '%Ã%' OR name_de LIKE '%ue%' LIMIT 20;
-- SELECT id, name, name_de FROM productions WHERE name_de LIKE '%Ã%' OR name_de LIKE '%ue%' LIMIT 20;
