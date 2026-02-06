-- ============================================
-- Wasser und Strom Produktionen
-- Erstellt am: 2026-02-06
-- ============================================

SET NAMES utf8mb4;

-- ============================================
-- PRODUKTE - Wasser und Strom pruefen/erstellen
-- ============================================

INSERT IGNORE INTO products (name, name_de, category, icon, base_price, is_crop, description)
VALUES ('Wasser', 'Wasser', 'ressource', 'wasser.png', 50.00, FALSE, 'Frisches Wasser fuer Produktionen und Tiere');

INSERT IGNORE INTO products (name, name_de, category, icon, base_price, is_crop, description)
VALUES ('Strom', 'Strom', 'ressource', 'strom.png', 80.00, FALSE, 'Elektrische Energie fuer Produktionsanlagen');

-- ============================================
-- FORSCHUNG - Wasserwerk und Kraftwerk
-- ============================================

INSERT IGNORE INTO research_tree (name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward)
VALUES ('Wasserwerk', 'Ermoeglicht den Bau eines Wasserwerks zur Wasserproduktion.', 'buildings', 5000, 4, NULL, 3, 100);

INSERT IGNORE INTO research_tree (name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward)
VALUES ('Kraftwerk', 'Ermoeglicht den Bau eines Kraftwerks zur Stromproduktion.', 'buildings', 8000, 6, NULL, 5, 150);

-- ============================================
-- PRODUKTIONEN - Wasserwerk und Kraftwerk
-- ============================================

SET @wasser_id = (SELECT id FROM products WHERE name = 'Wasser' LIMIT 1);
SET @strom_id = (SELECT id FROM products WHERE name = 'Strom' LIMIT 1);
SET @wasserwerk_research_id = (SELECT id FROM research_tree WHERE name = 'Wasserwerk' LIMIT 1);
SET @kraftwerk_research_id = (SELECT id FROM research_tree WHERE name = 'Kraftwerk' LIMIT 1);

INSERT IGNORE INTO productions (name, name_de, category, building_cost, maintenance_cost, production_time, required_research_id, required_level, icon, description, is_active)
VALUES ('Wasserwerk', 'Wasserwerk', 'infrastruktur', 150000, 150.00, 3600, @wasserwerk_research_id, 3, 'wasserwerk.png', 'Produziert sauberes Wasser aus Grundwasser. Keine Rohstoffe erforderlich.', TRUE);

INSERT IGNORE INTO productions (name, name_de, category, building_cost, maintenance_cost, production_time, required_research_id, required_level, icon, description, is_active)
VALUES ('Kraftwerk', 'Kraftwerk', 'infrastruktur', 250000, 250.00, 3600, @kraftwerk_research_id, 5, 'kraftwerk.png', 'Produziert elektrische Energie. Keine Rohstoffe erforderlich.', TRUE);

-- ============================================
-- PRODUKTIONS-OUTPUTS
-- ============================================

SET @wasserwerk_prod_id = (SELECT id FROM productions WHERE name = 'Wasserwerk' LIMIT 1);
SET @kraftwerk_prod_id = (SELECT id FROM productions WHERE name = 'Kraftwerk' LIMIT 1);

INSERT IGNORE INTO production_outputs (production_id, product_id, quantity)
VALUES (@wasserwerk_prod_id, @wasser_id, 10);

INSERT IGNORE INTO production_outputs (production_id, product_id, quantity)
VALUES (@kraftwerk_prod_id, @strom_id, 10);

-- ============================================
-- Verifizierung
-- ============================================

SELECT 'Forschung' AS typ, id, name, category, level_required, research_time_hours, cost FROM research_tree WHERE name IN ('Wasserwerk', 'Kraftwerk');

SELECT 'Produktion' AS typ, id, name_de, building_cost, category FROM productions WHERE name IN ('Wasserwerk', 'Kraftwerk');

SELECT 'Output' AS typ, p.name_de AS produktion, pr.name_de AS produkt, po.quantity FROM production_outputs po JOIN productions p ON po.production_id = p.id JOIN products pr ON po.product_id = pr.id WHERE p.name IN ('Wasserwerk', 'Kraftwerk');
