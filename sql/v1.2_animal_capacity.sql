SET NAMES utf8mb4;

-- ============================================
-- TIERKAPAZITÄT DURCH STÄLLE - v1.2 Migration
-- ============================================

-- Neue Felder für Tierkapazität in Produktionen
ALTER TABLE productions ADD COLUMN IF NOT EXISTS animal_type VARCHAR(50) NULL AFTER category;
ALTER TABLE productions ADD COLUMN IF NOT EXISTS animal_capacity INT DEFAULT 0 AFTER animal_type;

-- Index für Tiertyp-Suche
CREATE INDEX IF NOT EXISTS idx_productions_animal_type ON productions(animal_type);

-- Kuhstall
UPDATE productions SET animal_type = 'cow', animal_capacity = 20 WHERE name LIKE '%Kuhstall%' OR name_de LIKE '%Kuhstall%';

-- Hühnerstall
UPDATE productions SET animal_type = 'chicken', animal_capacity = 50 WHERE name LIKE '%Hühnerstall%' OR name_de LIKE '%Hühnerstall%' OR name LIKE '%Huhnerstall%' OR name_de LIKE '%Huhnerstall%';

-- Schweinestall
UPDATE productions SET animal_type = 'pig', animal_capacity = 15 WHERE name LIKE '%Schweinestall%' OR name_de LIKE '%Schweinestall%';

-- Schafstall
UPDATE productions SET animal_type = 'sheep', animal_capacity = 25 WHERE name LIKE '%Schafstall%' OR name_de LIKE '%Schafstall%';

-- Ziegenstall
UPDATE productions SET animal_type = 'goat', animal_capacity = 20 WHERE name LIKE '%Ziegenstall%' OR name_de LIKE '%Ziegenstall%';

-- Pferdestall
UPDATE productions SET animal_type = 'horse', animal_capacity = 8 WHERE name LIKE '%Pferdestall%' OR name_de LIKE '%Pferdestall%';

-- Entenstall
UPDATE productions SET animal_type = 'duck', animal_capacity = 30 WHERE name LIKE '%Entenstall%' OR name_de LIKE '%Entenstall%';

-- Gänsestall
UPDATE productions SET animal_type = 'goose', animal_capacity = 25 WHERE name LIKE '%Gänsestall%' OR name_de LIKE '%Gänsestall%' OR name LIKE '%Gaensestall%' OR name_de LIKE '%Gaensestall%';

-- Bienenhaus
UPDATE productions SET animal_type = 'bee', animal_capacity = 10 WHERE name LIKE '%Bienenhaus%' OR name_de LIKE '%Bienenhaus%' OR name LIKE '%Imkerei%' OR name_de LIKE '%Imkerei%';

-- Büffelstall
UPDATE productions SET animal_type = 'buffalo', animal_capacity = 12 WHERE name LIKE '%Büffelstall%' OR name_de LIKE '%Büffelstall%' OR name LIKE '%Buffelstall%' OR name_de LIKE '%Buffelstall%';

-- Weide (falls als Produktion vorhanden)
UPDATE productions SET animal_type = 'cattle', animal_capacity = 30 WHERE name LIKE '%Weide%' OR name_de LIKE '%Weide%';
