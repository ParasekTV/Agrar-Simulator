-- ============================================
-- Fix Gewächshaus-Pflanzen: Duplikate entfernen & erweitern
-- ============================================

-- 1. Spalte is_greenhouse_only hinzufügen (falls nicht vorhanden)
ALTER TABLE crops ADD COLUMN IF NOT EXISTS is_greenhouse_only BOOLEAN DEFAULT FALSE;

-- 2. Duplikate entfernen - SICHER mit Fremdschlüssel-Handling

-- 2a. Fremdschlüsselprüfung temporär deaktivieren
SET FOREIGN_KEY_CHECKS = 0;

-- 2b. Aktualisiere fields-Referenzen auf die niedrigste Crop-ID (vor dem Löschen)
UPDATE fields f
INNER JOIN crops c1 ON f.current_crop_id = c1.id
INNER JOIN crops c2 ON c1.name = c2.name AND c2.id < c1.id
SET f.current_crop_id = c2.id;

-- 2c. Duplikate entfernen (behalte nur den Eintrag mit der niedrigsten ID)
DELETE c1 FROM crops c1
INNER JOIN crops c2 ON c1.name = c2.name
WHERE c1.id > c2.id;

-- 2d. Fremdschlüsselprüfung wieder aktivieren
SET FOREIGN_KEY_CHECKS = 1;

-- 3. Bestehende Gewächshaus-Pflanzen als greenhouse_only markieren
UPDATE crops SET is_greenhouse_only = TRUE WHERE name IN ('Tomaten', 'Paprika', 'Gurken');

-- 4. Forschung für erweiterte Gewächshaus-Kulturen hinzufügen
INSERT INTO research_tree (id, name, description, category, cost, level_required, prerequisite_id, research_time_hours, points_reward) VALUES
(36, 'Erweiterte Gewächshaus-Kulturen', 'Schaltet Salat, Spinat und Auberginen im Gewächshaus frei', 'crops', 18000.00, 12, 34, 14, 450),
(37, 'Exotische Gewächshaus-Pflanzen', 'Schaltet Chili, Zucchini und Erdbeeren im Gewächshaus frei', 'crops', 25000.00, 15, 36, 18, 550),
(38, 'Gewächshaus-Kräuter', 'Schaltet Basilikum, Petersilie und Minze im Gewächshaus frei', 'crops', 15000.00, 10, 34, 10, 350)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- 5. Neue Gewächshaus-Pflanzen hinzufügen

-- Basis Gewächshaus-Kulturen (Forschung 34 - bereits vorhanden)
-- Tomaten, Paprika, Gurken sind bereits vorhanden

-- Erweiterte Gewächshaus-Kulturen (Forschung 36)
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category, is_greenhouse_only) VALUES
('Salat', 'Frischer Kopfsalat aus dem Gewächshaus', 4, 180.00, 55.00, 200, 36, 80, 6.0, 7.0, 0.15, 'vegetable', TRUE),
('Spinat', 'Vitaminreicher Blattspinat', 5, 220.00, 70.00, 180, 36, 75, 6.0, 7.5, 0.2, 'vegetable', TRUE),
('Auberginen', 'Violette Auberginen für mediterrane Küche', 8, 350.00, 120.00, 120, 36, 70, 5.5, 6.8, 0.25, 'vegetable', TRUE)
ON DUPLICATE KEY UPDATE is_greenhouse_only = TRUE, required_research_id = 36;

-- Exotische Gewächshaus-Pflanzen (Forschung 37)
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category, is_greenhouse_only) VALUES
('Chili', 'Scharfe Chilischoten für würzige Gerichte', 7, 400.00, 130.00, 100, 37, 60, 5.5, 6.5, 0.2, 'vegetable', TRUE),
('Zucchini', 'Vielseitige Zucchini für Gemüsegerichte', 6, 260.00, 85.00, 170, 37, 75, 6.0, 7.5, 0.2, 'vegetable', TRUE),
('Erdbeeren', 'Süße Erdbeeren aus dem Gewächshaus', 10, 500.00, 180.00, 90, 37, 85, 5.5, 6.5, 0.3, 'fruit', TRUE)
ON DUPLICATE KEY UPDATE is_greenhouse_only = TRUE, required_research_id = 37;

-- Gewächshaus-Kräuter (Forschung 38)
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category, is_greenhouse_only) VALUES
('Petersilie', 'Klassisches Küchenkraut', 3, 160.00, 50.00, 90, 38, 65, 6.0, 7.0, 0.15, 'herb', TRUE),
('Minze', 'Erfrischende Minze für Tee und Desserts', 4, 200.00, 65.00, 80, 38, 70, 6.0, 7.0, 0.15, 'herb', TRUE),
('Dill', 'Aromatischer Dill für Fisch und Salate', 3, 170.00, 55.00, 85, 38, 60, 5.5, 6.5, 0.15, 'herb', TRUE),
('Schnittlauch', 'Frischer Schnittlauch für Salate', 3, 150.00, 45.00, 100, 38, 65, 6.0, 7.0, 0.1, 'herb', TRUE),
('Koriander', 'Exotisches Kraut für asiatische Gerichte', 4, 220.00, 75.00, 70, 38, 60, 6.0, 6.8, 0.2, 'herb', TRUE)
ON DUPLICATE KEY UPDATE is_greenhouse_only = TRUE, required_research_id = 38;

-- Basilikum, Thymian, Rosmarin als Gewächshaus-optional markieren (wenn Forschung 35 oder 38)
UPDATE crops SET is_greenhouse_only = FALSE WHERE name IN ('Basilikum', 'Thymian', 'Rosmarin');

-- 6. Produkte für neue Gewächshaus-Pflanzen hinzufügen
INSERT IGNORE INTO products (name, name_de, category, base_price, is_crop) VALUES
('lettuce', 'Salat', 'gemuese', 180.00, TRUE),
('spinach', 'Spinat', 'gemuese', 220.00, TRUE),
('eggplant', 'Auberginen', 'gemuese', 350.00, TRUE),
('chili', 'Chili', 'gemuese', 400.00, TRUE),
('zucchini', 'Zucchini', 'gemuese', 260.00, TRUE),
('strawberries', 'Erdbeeren', 'obst', 500.00, TRUE),
('parsley', 'Petersilie', 'kraeuter', 160.00, TRUE),
('mint', 'Minze', 'kraeuter', 200.00, TRUE),
('dill', 'Dill', 'kraeuter', 170.00, TRUE),
('chives', 'Schnittlauch', 'kraeuter', 150.00, TRUE),
('coriander', 'Koriander', 'kraeuter', 220.00, TRUE);

-- 7. Überprüfung ausgeben
SELECT 'Gewächshaus-Pflanzen nach Fix:' AS status;
SELECT id, name, is_greenhouse_only, required_research_id FROM crops WHERE is_greenhouse_only = TRUE OR name IN ('Tomaten', 'Paprika', 'Gurken', 'Salat', 'Spinat', 'Auberginen', 'Chili', 'Zucchini', 'Erdbeeren') ORDER BY required_research_id, name;

SELECT 'Neue Forschungen:' AS status;
SELECT id, name, prerequisite_id FROM research_tree WHERE id IN (34, 36, 37, 38) ORDER BY id;
