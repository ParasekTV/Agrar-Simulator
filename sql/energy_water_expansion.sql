SET NAMES utf8mb4;

-- ============================================
-- FORSCHUNG - Neue Energie- und Wasserquellen
-- ============================================

INSERT IGNORE INTO research_tree (name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES ('Brunnen', 'Ermöglicht den Bau eines Brunnens zur einfachen Wassergewinnung.', 'buildings', 2000, 2, NULL, 1, 50);

INSERT IGNORE INTO research_tree (name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES ('Solaranlage', 'Ermöglicht den Bau einer Solaranlage zur umweltfreundlichen Stromproduktion.', 'buildings', 4000, 3, NULL, 2, 75);

INSERT IGNORE INTO research_tree (name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES ('Windkraftanlage', 'Ermöglicht den Bau einer Windkraftanlage zur nachhaltigen Stromproduktion.', 'buildings', 6000, 4, NULL, 4, 100);

-- ============================================
-- PRODUKTIONEN - Neue Gebäude
-- ============================================

SET @brunnen_research_id = (SELECT id FROM research_tree WHERE name = 'Brunnen' LIMIT 1);
SET @solar_research_id = (SELECT id FROM research_tree WHERE name = 'Solaranlage' LIMIT 1);
SET @wind_research_id = (SELECT id FROM research_tree WHERE name = 'Windkraftanlage' LIMIT 1);
SET @wasser_id = (SELECT id FROM products WHERE name = 'Wasser' LIMIT 1);
SET @strom_id = (SELECT id FROM products WHERE name = 'Strom' LIMIT 1);

INSERT IGNORE INTO productions (name, name_de, category, building_cost, maintenance_cost, production_time, required_research_id, required_level, icon, description, is_active) VALUES ('Brunnen', 'Brunnen', 'infrastruktur', 50000, 50.00, 3600, @brunnen_research_id, 1, 'brunnen.png', 'Ein einfacher Brunnen zur Grundwassergewinnung. Produziert kleine Mengen Wasser.', TRUE);

INSERT IGNORE INTO productions (name, name_de, category, building_cost, maintenance_cost, production_time, required_research_id, required_level, icon, description, is_active) VALUES ('Solaranlage', 'Solaranlage', 'infrastruktur', 100000, 80.00, 3600, @solar_research_id, 2, 'solaranlage.png', 'Photovoltaik-Anlage zur umweltfreundlichen Stromproduktion. Abhängig vom Sonnenlicht.', TRUE);

INSERT IGNORE INTO productions (name, name_de, category, building_cost, maintenance_cost, production_time, required_research_id, required_level, icon, description, is_active) VALUES ('Windkraftanlage', 'Windkraftanlage', 'infrastruktur', 180000, 120.00, 3600, @wind_research_id, 4, 'windkraftanlage.png', 'Windturbine zur nachhaltigen Stromproduktion. Produziert konstant Energie.', TRUE);

-- ============================================
-- PRODUKTIONS-OUTPUTS
-- ============================================

SET @brunnen_prod_id = (SELECT id FROM productions WHERE name = 'Brunnen' LIMIT 1);
SET @solar_prod_id = (SELECT id FROM productions WHERE name = 'Solaranlage' LIMIT 1);
SET @wind_prod_id = (SELECT id FROM productions WHERE name = 'Windkraftanlage' LIMIT 1);

INSERT IGNORE INTO production_outputs (production_id, product_id, quantity) VALUES (@brunnen_prod_id, @wasser_id, 4);

INSERT IGNORE INTO production_outputs (production_id, product_id, quantity) VALUES (@solar_prod_id, @strom_id, 5);

INSERT IGNORE INTO production_outputs (production_id, product_id, quantity) VALUES (@wind_prod_id, @strom_id, 8);

-- ============================================
-- Verifizierung
-- ============================================

SELECT 'Neue Forschungen' AS typ, id, name, level_required, cost FROM research_tree WHERE name IN ('Brunnen', 'Solaranlage', 'Windkraftanlage');

SELECT 'Neue Produktionen' AS typ, id, name_de, building_cost, required_level FROM productions WHERE name IN ('Brunnen', 'Solaranlage', 'Windkraftanlage');

SELECT 'Outputs' AS typ, p.name_de AS produktion, pr.name_de AS produkt, po.quantity FROM production_outputs po JOIN productions p ON po.production_id = p.id JOIN products pr ON po.product_id = pr.id WHERE p.name IN ('Brunnen', 'Solaranlage', 'Windkraftanlage');
