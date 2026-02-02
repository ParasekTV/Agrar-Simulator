-- ============================================
-- Phase 1: Erweiterte Feldfrüchte - Migration
-- ============================================
-- Dieses Script erweitert das Spiel um:
-- - 18 neue Feldfrüchte in 5 Forschungs-Tiers
-- - 8 neue Forschungsknoten
-- - Kalk-System für pH-Balance
-- - 4 Düngertypen mit unterschiedlichen Effekten
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. NEUE TABELLEN
-- ============================================

-- Düngertypen-Tabelle
CREATE TABLE IF NOT EXISTS fertilizer_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    quality_boost INT NOT NULL DEFAULT 20,
    yield_multiplier DECIMAL(4,2) DEFAULT 1.00,
    cost_per_hectare DECIMAL(8,2) NOT NULL,
    effect_duration_hours INT DEFAULT 0,
    instant_effect BOOLEAN DEFAULT TRUE,
    prevents_quality_loss BOOLEAN DEFAULT FALSE,
    required_research_id INT NULL,
    FOREIGN KEY (required_research_id) REFERENCES research_tree(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kalktypen-Tabelle
CREATE TABLE IF NOT EXISTS lime_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    ph_increase DECIMAL(3,1) NOT NULL,
    cost_per_hectare DECIMAL(8,2) NOT NULL,
    duration_harvests INT DEFAULT 3,
    required_research_id INT NULL,
    FOREIGN KEY (required_research_id) REFERENCES research_tree(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. TABELLEN-ERWEITERUNGEN
-- ============================================

-- crops-Tabelle erweitern um pH-Werte und Kategorie
ALTER TABLE crops
ADD COLUMN IF NOT EXISTS optimal_ph_min DECIMAL(3,1) DEFAULT 6.0,
ADD COLUMN IF NOT EXISTS optimal_ph_max DECIMAL(3,1) DEFAULT 7.5,
ADD COLUMN IF NOT EXISTS ph_degradation DECIMAL(3,1) DEFAULT 0.2,
ADD COLUMN IF NOT EXISTS category VARCHAR(50) DEFAULT 'grain';

-- fields-Tabelle erweitern um pH und Dünger-Tracking
ALTER TABLE fields
ADD COLUMN IF NOT EXISTS soil_ph DECIMAL(3,1) DEFAULT 7.0,
ADD COLUMN IF NOT EXISTS last_limed_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS active_fertilizer_id INT NULL,
ADD COLUMN IF NOT EXISTS fertilizer_applied_at TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS fertilizer_expires_at TIMESTAMP NULL;

-- ============================================
-- 3. NEUE FORSCHUNGSKNOTEN
-- ============================================

-- Erweiterte Getreide (ID 10) - benötigt Grundlagen Ackerbau
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(10, 'Erweiterte Getreide', 'Ermöglicht Anbau von Hafer, Roggen und Dinkel', 'crops', 1000.00, 2, 1, 3, 100)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Futterpflanzen (ID 11) - benötigt Erweiterte Getreide
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(11, 'Futterpflanzen', 'Ermöglicht Anbau von Klee, Luzerne und Gras für Tierfutter', 'crops', 2500.00, 3, 10, 5, 150)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Industriepflanzen (ID 12) - benötigt Spezialkulturen
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(12, 'Industriepflanzen', 'Ermöglicht Anbau von Hopfen, Tabak, Baumwolle, Hanf und Flachs', 'crops', 5000.00, 6, 4, 8, 250)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Gemüsebau (ID 13) - benötigt Spezialkulturen
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(13, 'Gemüsebau', 'Ermöglicht Anbau von Zwiebeln, Karotten, Kohl, Spinat und Sellerie', 'crops', 3000.00, 4, 4, 6, 180)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Obstanbau Basics (ID 14) - benötigt Gemüsebau
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(14, 'Obstanbau Basics', 'Ermöglicht Anbau von Erdbeeren', 'crops', 8000.00, 8, 13, 10, 300)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Erweiterte Düngung (ID 15) - benötigt Grundlagen Ackerbau
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(15, 'Erweiterte Düngung', 'Schaltet NPK-Dünger und Bio-Dünger frei', 'efficiency', 3000.00, 4, 1, 4, 150)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Präzisionslandwirtschaft (ID 16) - benötigt Erweiterte Düngung
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(16, 'Präzisionslandwirtschaft', 'Schaltet Flüssigdünger frei für maximalen Ertrag', 'efficiency', 6000.00, 6, 15, 8, 200)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Bodenkunde (ID 17) - benötigt Grundlagen Ackerbau
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(17, 'Bodenkunde', 'Schaltet Kalkung für pH-Balance frei', 'efficiency', 2000.00, 3, 1, 3, 120)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ============================================
-- 4. EXISTIERENDE FELDFRÜCHTE AKTUALISIEREN
-- ============================================

UPDATE crops SET category = 'grain', optimal_ph_min = 6.0, optimal_ph_max = 7.5, ph_degradation = 0.2 WHERE name = 'Weizen';
UPDATE crops SET category = 'grain', optimal_ph_min = 5.5, optimal_ph_max = 7.5, ph_degradation = 0.2 WHERE name = 'Mais';
UPDATE crops SET category = 'vegetable', optimal_ph_min = 5.5, optimal_ph_max = 6.5, ph_degradation = 0.2 WHERE name = 'Kartoffeln';
UPDATE crops SET category = 'grain', optimal_ph_min = 6.0, optimal_ph_max = 7.0, ph_degradation = 0.2 WHERE name = 'Gerste';
UPDATE crops SET category = 'oil', optimal_ph_min = 6.0, optimal_ph_max = 7.5, ph_degradation = 0.2 WHERE name = 'Raps';
UPDATE crops SET category = 'oil', optimal_ph_min = 6.0, optimal_ph_max = 7.5, ph_degradation = 0.2 WHERE name = 'Sonnenblumen';
UPDATE crops SET category = 'industrial', optimal_ph_min = 6.5, optimal_ph_max = 7.5, ph_degradation = 0.25 WHERE name = 'Zuckerrueben';
UPDATE crops SET category = 'legume', optimal_ph_min = 6.0, optimal_ph_max = 7.0, ph_degradation = 0.1 WHERE name = 'Sojabohnen';

-- ============================================
-- 5. NEUE FELDFRÜCHTE
-- ============================================

-- Tier 1: Erweiterte Getreide (Forschung ID 10)
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Hafer', 'Vielseitiges Getreide für Futter und Nahrung', 5, 165.00, 55.00, 105, 10, 50, 5.5, 7.0, 0.2, 'grain'),
('Roggen', 'Robustes Wintergetreide', 6, 190.00, 65.00, 115, 10, 45, 5.0, 7.0, 0.2, 'grain'),
('Dinkel', 'Premium-Urgetreide mit hohem Marktwert', 7, 210.00, 75.00, 100, 10, 55, 6.0, 7.5, 0.2, 'grain')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Tier 2: Futterpflanzen (Forschung ID 11)
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Klee', 'Stickstofffixierende Futterpflanze', 4, 120.00, 40.00, 130, 11, 60, 6.0, 7.0, 0.1, 'legume'),
('Luzerne', 'Proteinreiches Tierfutter', 6, 170.00, 60.00, 140, 11, 55, 6.5, 7.5, 0.1, 'legume'),
('Gras', 'Schnellwachsendes Grundfutter', 3, 90.00, 30.00, 160, 11, 65, 5.5, 7.0, 0.15, 'fodder')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Tier 3: Industriepflanzen (Forschung ID 12)
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Hopfen', 'Unverzichtbar für die Bierproduktion', 14, 550.00, 200.00, 60, 12, 70, 6.0, 7.5, 0.3, 'industrial'),
('Tabak', 'Wertvolle Industriepflanze', 12, 500.00, 180.00, 70, 12, 65, 5.5, 6.5, 0.3, 'industrial'),
('Baumwolle', 'Wichtige Textilfaser', 10, 380.00, 140.00, 85, 12, 75, 5.8, 7.0, 0.25, 'industrial'),
('Hanf', 'Vielseitige Nutzpflanze für Fasern und Öl', 8, 350.00, 130.00, 90, 12, 50, 6.0, 7.5, 0.2, 'industrial'),
('Flachs', 'Für Leinen und Leinöl', 9, 320.00, 120.00, 80, 12, 55, 5.5, 7.0, 0.2, 'industrial')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Tier 4: Gemüsebau (Forschung ID 13)
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Zwiebeln', 'Beliebtes Grundgemüse', 6, 200.00, 70.00, 120, 13, 50, 6.0, 7.0, 0.2, 'vegetable'),
('Karotten', 'Vitaminreiches Wurzelgemüse', 5, 180.00, 65.00, 130, 13, 60, 6.0, 6.8, 0.2, 'vegetable'),
('Kohl', 'Ertragreiches Blattgemüse', 7, 220.00, 80.00, 110, 13, 65, 6.5, 7.5, 0.25, 'vegetable'),
('Spinat', 'Schnellwachsendes Blattgemüse', 4, 160.00, 55.00, 100, 13, 60, 6.5, 7.5, 0.2, 'vegetable'),
('Sellerie', 'Aromatisches Gemüse', 6, 210.00, 75.00, 95, 13, 70, 6.0, 7.0, 0.2, 'vegetable')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Tier 5: Obstanbau Basics (Forschung ID 14)
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need, optimal_ph_min, optimal_ph_max, ph_degradation, category) VALUES
('Erdbeeren', 'Beliebte Sommerfrüchte mit hohem Marktwert', 8, 450.00, 160.00, 70, 14, 75, 5.5, 6.5, 0.25, 'fruit')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- ============================================
-- 6. DÜNGERTYPEN
-- ============================================

INSERT INTO fertilizer_types (name, description, quality_boost, yield_multiplier, cost_per_hectare, effect_duration_hours, instant_effect, prevents_quality_loss, required_research_id) VALUES
('Basis-Dünger', 'Standard-Mineraldünger für Bodenverbesserung', 20, 1.00, 50.00, 0, TRUE, FALSE, NULL),
('NPK-Dünger', 'Ausgewogener Nährstoffmix für 15% höheren Ertrag', 25, 1.15, 120.00, 0, TRUE, FALSE, 15),
('Bio-Dünger', 'Nachhaltiger Dünger - verhindert Bodenqualitätsverlust', 15, 1.10, 80.00, 48, FALSE, TRUE, 15),
('Flüssigdünger', 'Premium-Dünger für 25% höheren Ertrag', 30, 1.25, 200.00, 0, TRUE, FALSE, 16)
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- ============================================
-- 7. KALKTYPEN
-- ============================================

INSERT INTO lime_types (name, description, ph_increase, cost_per_hectare, duration_harvests, required_research_id) VALUES
('Kohlensaurer Kalk', 'Standard-Kalk zur pH-Korrektur', 0.5, 100.00, 3, 17),
('Branntkalk', 'Schnell wirkender Kalk mit starker pH-Anhebung', 1.0, 200.00, 2, 17),
('Dolomitkalk', 'Langzeitwirkung mit zusätzlichem Magnesium', 0.3, 150.00, 5, 17)
ON DUPLICATE KEY UPDATE description = VALUES(description);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- MIGRATION ABGESCHLOSSEN
-- ============================================
-- Nach Ausführung dieses Scripts:
-- - 8 neue Forschungsknoten verfügbar (ID 10-17)
-- - 18 neue Feldfrüchte (nach Forschung freigeschaltet)
-- - 4 Düngertypen (1 ohne Forschung, 3 mit Forschung)
-- - 3 Kalktypen (alle benötigen Bodenkunde-Forschung)
-- ============================================
