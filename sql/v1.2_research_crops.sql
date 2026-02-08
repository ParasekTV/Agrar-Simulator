SET NAMES utf8mb4;

-- ============================================
-- INDIVIDUELLE FELDFRUCHT-FORSCHUNG - v1.2 Migration
-- ============================================
-- Erweitert das Forschungssystem fuer Feldfruechte
-- Basis-Fruechte (Weizen, Mais, Kartoffeln) bleiben ohne Forschung

-- ============================================
-- 1. FORSCHUNGS-HIERARCHIE AKTUALISIEREN
-- ============================================

-- Die existierenden IDs:
-- 1 = Grundlagen Ackerbau (Weizen, Mais, Kartoffeln - keine Voraussetzung)
-- 4 = Spezialkulturen (Sonnenblumen, Zuckerrueben)
-- 10 = Erweiterte Getreide (Hafer, Roggen, Dinkel)
-- 11 = Futterpflanzen (Klee, Luzerne, Gras)
-- 12 = Industriepflanzen (Hopfen, Tabak, Baumwolle, Hanf, Flachs)
-- 13 = Gemuesebau (Zwiebeln, Karotten, Kohl, Spinat, Sellerie)
-- 14 = Obstanbau Basics (Erdbeeren)

-- ID 30-40 reserviert fuer zusaetzliche Feldfrucht-Forschung

-- Rapsanbau (ID 30) - benoetigt Spezialkulturen
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(30, 'Rapsanbau', 'Ermoeglicht den Anbau von Raps fuer Oel', 'crops', 2500.00, 3, 4, 4, 150)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Sojabohnen (ID 31) - benoetigt Futterpflanzen
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(31, 'Sojabohnenanbau', 'Ermoeglicht den Anbau von Sojabohnen', 'crops', 4000.00, 4, 11, 6, 200)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Weinbau (ID 32) - benoetigt Obstanbau Basics
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(32, 'Weinbau', 'Ermoeglicht den Anbau von Weintrauben', 'crops', 10000.00, 8, 14, 12, 400)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Obstplantagen (ID 33) - benoetigt Obstanbau Basics
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(33, 'Obstplantagen', 'Ermoeglicht den Anbau von Aepfeln und Birnen', 'crops', 15000.00, 10, 14, 15, 500)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Gewaechshaeuser (ID 34) - benoetigt Gemuesebau
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(34, 'Gewaechshaus-Kulturen', 'Ermoeglicht Tomaten, Paprika und Gurken im Gewaechshaus', 'crops', 12000.00, 8, 13, 10, 350)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- Kraeuteranbau (ID 35) - benoetigt Gemuesebau
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(35, 'Kraeuteranbau', 'Ermoeglicht den Anbau von Basilikum, Thymian und Rosmarin', 'crops', 6000.00, 5, 13, 8, 250)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- ============================================
-- 2. NEUE FELDFRUECHTE HINZUFUEGEN
-- ============================================

-- Crops-Tabelle erweitern falls noetig
ALTER TABLE crops ADD COLUMN IF NOT EXISTS category VARCHAR(50) DEFAULT 'grain';
ALTER TABLE crops ADD COLUMN IF NOT EXISTS optimal_ph_min DECIMAL(3,1) DEFAULT 6.0;
ALTER TABLE crops ADD COLUMN IF NOT EXISTS optimal_ph_max DECIMAL(3,1) DEFAULT 7.5;
ALTER TABLE crops ADD COLUMN IF NOT EXISTS ph_degradation DECIMAL(3,2) DEFAULT 0.2;

-- Raps
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Raps', 'Oelpflanze fuer Rapsoel und Biodiesel', 10, 320.00, 110.00, 90, 30, 55, 6.0, 7.0, 0.25, 'oil')
ON DUPLICATE KEY UPDATE required_research_id = 30;

-- Sojabohnen
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Sojabohnen', 'Proteinreiche Huelsenfrucht', 9, 350.00, 120.00, 85, 31, 60, 6.0, 7.0, 0.2, 'legume')
ON DUPLICATE KEY UPDATE required_research_id = 31;

-- Weintrauben
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Weintrauben', 'Hochwertige Trauben fuer Wein', 24, 800.00, 300.00, 50, 32, 45, 6.0, 7.0, 0.3, 'fruit')
ON DUPLICATE KEY UPDATE required_research_id = 32;

-- Aepfel
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Aepfel', 'Beliebtes Kernobst', 168, 600.00, 200.00, 120, 33, 50, 6.0, 7.0, 0.2, 'fruit')
ON DUPLICATE KEY UPDATE required_research_id = 33;

-- Birnen
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Birnen', 'Suesse Birnen vom Baum', 168, 650.00, 220.00, 110, 33, 55, 6.0, 7.0, 0.2, 'fruit')
ON DUPLICATE KEY UPDATE required_research_id = 33;

-- Tomaten (Gewaechshaus)
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Tomaten', 'Saftige Tomaten aus dem Gewaechshaus', 6, 280.00, 95.00, 150, 34, 70, 6.0, 6.8, 0.25, 'vegetable')
ON DUPLICATE KEY UPDATE required_research_id = 34;

-- Paprika (Gewaechshaus)
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Paprika', 'Bunte Paprika fuer die Kueche', 7, 320.00, 105.00, 130, 34, 65, 6.0, 7.0, 0.25, 'vegetable')
ON DUPLICATE KEY UPDATE required_research_id = 34;

-- Gurken (Gewaechshaus)
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Gurken', 'Knackige Gurken fuer Salat', 5, 240.00, 80.00, 160, 34, 75, 5.5, 7.0, 0.2, 'vegetable')
ON DUPLICATE KEY UPDATE required_research_id = 34;

-- Basilikum
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Basilikum', 'Aromatisches Kuechenkraut', 3, 180.00, 60.00, 80, 35, 50, 6.0, 7.0, 0.15, 'herb')
ON DUPLICATE KEY UPDATE required_research_id = 35;

-- Thymian
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Thymian', 'Mediterranes Gewuerzkraut', 4, 200.00, 70.00, 70, 35, 40, 6.5, 7.5, 0.15, 'herb')
ON DUPLICATE KEY UPDATE required_research_id = 35;

-- Rosmarin
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Rosmarin', 'Wuerziges Kraut fuer Fleischgerichte', 4, 220.00, 75.00, 65, 35, 35, 6.0, 7.5, 0.15, 'herb')
ON DUPLICATE KEY UPDATE required_research_id = 35;

-- ============================================
-- 3. PRODUKTE TABELLE AKTUALISIEREN
-- ============================================

-- Neue Feldprodukte als verkaufbare Produkte
INSERT IGNORE INTO products (name, name_de, category, base_price, is_crop) VALUES
('rapeseed', 'Raps', 'feldfrucht', 320.00, TRUE),
('soybeans', 'Sojabohnen', 'feldfrucht', 350.00, TRUE),
('grapes', 'Weintrauben', 'feldfrucht', 800.00, TRUE),
('apples', 'Aepfel', 'feldfrucht', 600.00, TRUE),
('pears', 'Birnen', 'feldfrucht', 650.00, TRUE),
('tomatoes', 'Tomaten', 'feldfrucht', 280.00, TRUE),
('peppers', 'Paprika', 'feldfrucht', 320.00, TRUE),
('cucumbers', 'Gurken', 'feldfrucht', 240.00, TRUE),
('basil', 'Basilikum', 'kraeuter', 180.00, TRUE),
('thyme', 'Thymian', 'kraeuter', 200.00, TRUE),
('rosemary', 'Rosmarin', 'kraeuter', 220.00, TRUE);

-- ============================================
-- 4. PRODUKTIONEN FUER NEUE PRODUKTE
-- ============================================

-- Weinkellerei - benoetigt Weinbau-Forschung
INSERT INTO productions (name, name_de, description, category, building_cost, production_time, required_research_id, required_level) VALUES
('winery', 'Weinkellerei', 'Produziert edlen Wein aus Weintrauben', 'processing', 350000.00, 172800, 32, 12)
ON DUPLICATE KEY UPDATE required_research_id = 32;

-- Saftpresse - benoetigt Obstplantagen-Forschung
INSERT INTO productions (name, name_de, description, category, building_cost, production_time, required_research_id, required_level) VALUES
('juice_press', 'Saftpresse', 'Produziert frische Obstsaefte', 'processing', 150000.00, 21600, 33, 15)
ON DUPLICATE KEY UPDATE required_research_id = 33;

-- Gewaechshaus - benoetigt Gewaechshaus-Forschung
INSERT INTO productions (name, name_de, description, category, building_cost, production_time, required_research_id, required_level) VALUES
('greenhouse', 'Gewaechshaus', 'Ermoeglicht Anbau von Tomaten, Paprika und Gurken', 'farming', 200000.00, 0, 34, 10)
ON DUPLICATE KEY UPDATE required_research_id = 34;

-- Kraeutergarten - benoetigt Kraeuteranbau-Forschung
INSERT INTO productions (name, name_de, description, category, building_cost, production_time, required_research_id, required_level) VALUES
('herb_garden', 'Kraeutergarten', 'Ermoeglicht Anbau von Kuechenkraeutern', 'farming', 80000.00, 0, 35, 8)
ON DUPLICATE KEY UPDATE required_research_id = 35;

-- Oelmuehle erweitern fuer Raps
UPDATE productions SET required_research_id = NULL WHERE name = 'oil_mill' AND required_research_id IS NULL;

