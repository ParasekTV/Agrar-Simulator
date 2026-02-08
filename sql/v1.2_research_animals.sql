SET NAMES utf8mb4;

-- ============================================
-- INDIVIDUELLE TIER-FORSCHUNG - v1.2 Migration
-- ============================================
-- Jedes Tier kann einzeln erforscht werden
-- Basis-Tiere (Huhn) sind ohne Forschung verfuegbar

-- ============================================
-- 1. ANIMALS ENUM ERWEITERN
-- ============================================

-- Neuen ENUM-Typ fuer erweiterte Tierarten
ALTER TABLE animals MODIFY COLUMN type ENUM(
    'cow', 'pig', 'chicken', 'sheep', 'horse',
    'goat', 'duck', 'goose', 'bee', 'buffalo', 'rabbit', 'turkey'
) NOT NULL;

-- ============================================
-- 2. FORSCHUNG FUER EINZELNE TIERE
-- ============================================

-- ID 20-35 reserviert fuer Tier-Forschung

-- Basisviehzucht bleibt ID 2 als Voraussetzung fuer viele

-- Ziegenhaltung (ID 20) - benoetigt Viehzucht Basics
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(20, 'Ziegenhaltung', 'Ermoeglicht die Haltung von Ziegen', 'animals', 2000.00, 3, 2, 3, 120)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Entenhaltung (ID 21) - benoetigt Viehzucht Basics
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(21, 'Entenhaltung', 'Ermoeglicht die Haltung von Enten', 'animals', 1500.00, 2, 2, 2, 100)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Gaensehaltung (ID 22) - benoetigt Entenhaltung
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(22, 'Gaensehaltung', 'Ermoeglicht die Haltung von Gaensen', 'animals', 2500.00, 3, 21, 4, 150)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Pferdezucht (ID 23) - benoetigt Grossviehzucht
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(23, 'Pferdezucht', 'Ermoeglicht die Haltung von Pferden', 'animals', 8000.00, 6, 5, 8, 300)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Bienenzucht (ID 24) - benoetigt Viehzucht Basics
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(24, 'Imkerei', 'Ermoeglicht die Haltung von Bienen', 'animals', 5000.00, 4, 2, 5, 200)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Bueffelzucht (ID 25) - benoetigt Grossviehzucht
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(25, 'Bueffelzucht', 'Ermoeglicht die Haltung von Wasserbueffeln', 'animals', 12000.00, 8, 5, 10, 400)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Kaninchenzucht (ID 26) - ohne Voraussetzung (Anfaengerfreundlich)
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(26, 'Kaninchenzucht', 'Ermoeglicht die Haltung von Kaninchen', 'animals', 800.00, 1, NULL, 1, 80)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Putenhaltung (ID 27) - benoetigt Viehzucht Basics
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(27, 'Putenhaltung', 'Ermoeglicht die Haltung von Truthaehnern', 'animals', 3500.00, 4, 2, 4, 180)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- ============================================
-- 3. NEUE TIERE HINZUFUEGEN
-- ============================================

-- Aktualisiere bestehende Tiere mit korrekter Forschung
UPDATE animals SET required_research_id = 2 WHERE type = 'cow' AND required_research_id IS NULL;
UPDATE animals SET required_research_id = 2 WHERE type = 'pig' AND required_research_id IS NULL;
UPDATE animals SET required_research_id = 5 WHERE type = 'sheep' AND required_research_id IS NULL;
UPDATE animals SET required_research_id = 23 WHERE type = 'horse' AND required_research_id IS NULL;
-- Huhn bleibt NULL (Startier)

-- Neue Tiere einfuegen
INSERT INTO animals (name, type, cost, production_item, production_time_hours, production_quantity, required_research_id, feed_cost) VALUES
('Ziege', 'goat', 3000.00, 'Ziegenmilch', 24, 8, 20, 8.00),
('Ente', 'duck', 800.00, 'Enteneier', 24, 6, 21, 3.00),
('Gans', 'goose', 1200.00, 'Gaenseeier', 24, 4, 22, 4.00),
('Pferd', 'horse', 15000.00, 'Pferdemist', 48, 5, 23, 25.00),
('Bienenvolk', 'bee', 5000.00, 'Honig', 72, 10, 24, 5.00),
('Wasserbueffel', 'buffalo', 20000.00, 'Bueffelmilch', 24, 15, 25, 20.00),
('Kaninchen', 'rabbit', 500.00, 'Kaninchenfell', 48, 2, 26, 2.00),
('Truthahn', 'turkey', 1500.00, 'Truthahnfleisch', 168, 1, 27, 6.00)
ON DUPLICATE KEY UPDATE required_research_id = VALUES(required_research_id);

-- ============================================
-- 4. TIERPRODUKTE HINZUFUEGEN
-- ============================================

-- Zuerst ENUM fuer animal_products erweitern
ALTER TABLE animal_products MODIFY COLUMN from_animal_type ENUM(
    'cow', 'pig', 'chicken', 'sheep', 'horse',
    'goat', 'duck', 'goose', 'bee', 'buffalo', 'rabbit', 'turkey'
) NOT NULL;

-- Tierprodukte einfuegen (Spalte heisst 'name', nicht 'product_name')
INSERT INTO animal_products (from_animal_type, name, base_sell_price) VALUES
('goat', 'Ziegenmilch', 18.00),
('duck', 'Enteneier', 8.00),
('goose', 'Gaenseeier', 12.00),
('horse', 'Pferdemist', 5.00),
('bee', 'Honig', 35.00),
('buffalo', 'Bueffelmilch', 25.00),
('rabbit', 'Kaninchenfell', 40.00),
('turkey', 'Truthahnfleisch', 80.00)
ON DUPLICATE KEY UPDATE base_sell_price = VALUES(base_sell_price);

-- ============================================
-- 5. STAELLE FUER NEUE TIERE (Produktionen)
-- ============================================

-- Pruefen ob Produktionen-Tabelle die richtigen Felder hat
-- (sollte durch v1.2_animal_capacity.sql bereits vorhanden sein)

-- Ziegenstall hinzufuegen
INSERT INTO productions (name, name_de, description, category, building_cost, production_time, required_research_id, required_level, animal_type, animal_capacity) VALUES
('goat_barn', 'Ziegenstall', 'Stall fuer bis zu 20 Ziegen', 'housing', 80000.00, 0, 20, 3, 'goat', 20)
ON DUPLICATE KEY UPDATE animal_type = 'goat', animal_capacity = 20;

-- Entenstall hinzufuegen
INSERT INTO productions (name, name_de, description, category, building_cost, production_time, required_research_id, required_level, animal_type, animal_capacity) VALUES
('duck_coop', 'Entenstall', 'Stall fuer bis zu 30 Enten', 'housing', 40000.00, 0, 21, 2, 'duck', 30)
ON DUPLICATE KEY UPDATE animal_type = 'duck', animal_capacity = 30;

-- Gaensestall hinzufuegen
INSERT INTO productions (name, name_de, description, category, building_cost, production_time, required_research_id, required_level, animal_type, animal_capacity) VALUES
('goose_coop', 'Gaensestall', 'Stall fuer bis zu 25 Gaense', 'housing', 60000.00, 0, 22, 4, 'goose', 25)
ON DUPLICATE KEY UPDATE animal_type = 'goose', animal_capacity = 25;

-- Pferdestall hinzufuegen
INSERT INTO productions (name, name_de, description, category, building_cost, production_time, required_research_id, required_level, animal_type, animal_capacity) VALUES
('horse_stable', 'Pferdestall', 'Stall fuer bis zu 8 Pferde', 'housing', 200000.00, 0, 23, 8, 'horse', 8)
ON DUPLICATE KEY UPDATE animal_type = 'horse', animal_capacity = 8;

-- Bienenhaus hinzufuegen
INSERT INTO productions (name, name_de, description, category, building_cost, production_time, required_research_id, required_level, animal_type, animal_capacity) VALUES
('apiary', 'Bienenhaus', 'Platz fuer bis zu 10 Bienenvoelker', 'housing', 50000.00, 0, 24, 5, 'bee', 10)
ON DUPLICATE KEY UPDATE animal_type = 'bee', animal_capacity = 10;

-- Bueffelstall hinzufuegen
INSERT INTO productions (name, name_de, description, category, building_cost, production_time, required_research_id, required_level, animal_type, animal_capacity) VALUES
('buffalo_barn', 'Bueffelstall', 'Stall fuer bis zu 12 Wasserbueffel', 'housing', 300000.00, 0, 25, 10, 'buffalo', 12)
ON DUPLICATE KEY UPDATE animal_type = 'buffalo', animal_capacity = 12;

-- Kaninchenstall hinzufuegen
INSERT INTO productions (name, name_de, description, category, building_cost, production_time, required_research_id, required_level, animal_type, animal_capacity) VALUES
('rabbit_hutch', 'Kaninchenstall', 'Platz fuer bis zu 40 Kaninchen', 'housing', 20000.00, 0, 26, 1, 'rabbit', 40)
ON DUPLICATE KEY UPDATE animal_type = 'rabbit', animal_capacity = 40;

-- Putenstall hinzufuegen
INSERT INTO productions (name, name_de, description, category, building_cost, production_time, required_research_id, required_level, animal_type, animal_capacity) VALUES
('turkey_coop', 'Putenstall', 'Stall fuer bis zu 25 Truthaehne', 'housing', 70000.00, 0, 27, 4, 'turkey', 25)
ON DUPLICATE KEY UPDATE animal_type = 'turkey', animal_capacity = 25;

-- ============================================
-- 6. PRODUKTE TABELLE AKTUALISIEREN
-- ============================================

-- Neue Tierprodukte als Produkte hinzufuegen (fuer Verkauf/Shop)
INSERT IGNORE INTO products (name, name_de, category, base_price, is_crop) VALUES
('goat_milk', 'Ziegenmilch', 'tierprodukt', 18.00, FALSE),
('duck_eggs', 'Enteneier', 'tierprodukt', 8.00, FALSE),
('goose_eggs', 'Gaenseeier', 'tierprodukt', 12.00, FALSE),
('horse_manure', 'Pferdemist', 'tierprodukt', 5.00, FALSE),
('honey', 'Honig', 'tierprodukt', 35.00, FALSE),
('buffalo_milk', 'Bueffelmilch', 'tierprodukt', 25.00, FALSE),
('rabbit_fur', 'Kaninchenfell', 'tierprodukt', 40.00, FALSE),
('turkey_meat', 'Truthahnfleisch', 'tierprodukt', 80.00, FALSE);

