-- ============================================
-- Agrargenossenschaft Erweiterung
-- Generiert am: 2026-02-02 14:15:26
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- ROLLEN-SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_key VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    permissions JSON,
    is_transferable BOOLEAN DEFAULT TRUE,
    max_per_coop INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cooperative_roles (role_key, name, description, permissions, is_transferable, max_per_coop) VALUES ('founder', 'Gruender', 'Vollzugriff auf alle Funktionen der Genossenschaft', '["all"]', 0, 1);
INSERT INTO cooperative_roles (role_key, name, description, permissions, is_transferable, max_per_coop) VALUES ('admin', 'Administrator', 'Kann Mitglieder verwalten und Bewerbungen bearbeiten', '["manage_members", "manage_applications", "view_finances", "manage_challenges"]', 1, 3);
INSERT INTO cooperative_roles (role_key, name, description, permissions, is_transferable, max_per_coop) VALUES ('fleet_manager', 'Fuhrparkmanager', 'Verwaltet alle Fahrzeuge der Genossenschaft', '["manage_vehicles", "buy_vehicles", "sell_vehicles", "assign_vehicles"]', 1, 2);
INSERT INTO cooperative_roles (role_key, name, description, permissions, is_transferable, max_per_coop) VALUES ('field_manager', 'Feldmanager', 'Verwaltet alle Felder der Genossenschaft', '["manage_fields", "buy_fields", "sell_fields", "plant_fields", "harvest_fields"]', 1, 2);
INSERT INTO cooperative_roles (role_key, name, description, permissions, is_transferable, max_per_coop) VALUES ('animal_manager', 'Tiermanager', 'Verwaltet alle Tiere der Genossenschaft', '["manage_animals", "buy_animals", "sell_animals", "feed_animals"]', 1, 2);
INSERT INTO cooperative_roles (role_key, name, description, permissions, is_transferable, max_per_coop) VALUES ('production_manager', 'Produktionsleiter', 'Verwaltet alle Produktionsstaetten der Genossenschaft', '["manage_productions", "start_production", "collect_products"]', 1, 2);
INSERT INTO cooperative_roles (role_key, name, description, permissions, is_transferable, max_per_coop) VALUES ('warehouse_manager', 'Lagerverwaltung', 'Verwaltet das Silo und Lager der Genossenschaft', '["manage_warehouse", "deposit_products", "withdraw_products", "sell_products"]', 1, 2);
INSERT INTO cooperative_roles (role_key, name, description, permissions, is_transferable, max_per_coop) VALUES ('treasurer', 'Kassenwart', 'Verwaltet die Finanzen der Genossenschaft', '["view_finances", "payout_members", "view_transactions"]', 1, 1);
INSERT INTO cooperative_roles (role_key, name, description, permissions, is_transferable, max_per_coop) VALUES ('researcher', 'Forschungsleiter', 'Leitet die Forschungsabteilung der Genossenschaft', '["manage_research", "start_research", "cancel_research"]', 1, 1);
INSERT INTO cooperative_roles (role_key, name, description, permissions, is_transferable, max_per_coop) VALUES ('member', 'Mitglied', 'Standardmitglied der Genossenschaft', '["view_coop", "donate", "deposit_products", "participate_challenges"]', 0, 0);

-- ============================================
-- MITGLIEDER-TABELLE ERWEITERN
-- ============================================

-- Rolle auf role_key aendern (VARCHAR statt ENUM)
ALTER TABLE cooperative_members MODIFY COLUMN role VARCHAR(50) DEFAULT 'member';

-- ============================================
-- BEWERBUNGS-SYSTEM
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    farm_id INT NOT NULL,
    message TEXT,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    reviewed_by INT DEFAULT NULL,
    review_message TEXT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES farms(id) ON DELETE SET NULL,
    UNIQUE KEY unique_application (cooperative_id, farm_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- GENOSSENSCHAFTS-SILO/LAGER
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_warehouse (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    UNIQUE KEY unique_coop_product (cooperative_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lager-Transaktionen
CREATE TABLE IF NOT EXISTS cooperative_warehouse_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    farm_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    transaction_type ENUM('deposit', 'withdraw', 'sale', 'production_input', 'production_output') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Genossenschafts-Einstellungen erweitern
ALTER TABLE cooperatives ADD COLUMN IF NOT EXISTS warehouse_capacity INT DEFAULT 10000;
ALTER TABLE cooperatives ADD COLUMN IF NOT EXISTS requires_application BOOLEAN DEFAULT TRUE;
ALTER TABLE cooperatives ADD COLUMN IF NOT EXISTS min_level_to_join INT DEFAULT 1;

-- ============================================
-- GENOSSENSCHAFTS-FINANZEN
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    farm_id INT DEFAULT NULL,
    amount DECIMAL(12,2) NOT NULL,
    transaction_type ENUM('donation', 'payout', 'purchase', 'sale', 'reward', 'fee') NOT NULL,
    description TEXT,
    balance_after DECIMAL(12,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT DEFAULT NULL,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES farms(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- GENOSSENSCHAFTS-FAHRZEUGE
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    custom_name VARCHAR(100),
    condition_percent INT DEFAULT 100,
    operating_hours INT DEFAULT 0,
    current_user_farm_id INT DEFAULT NULL,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    purchased_by INT DEFAULT NULL,
    last_maintenance TIMESTAMP NULL,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (current_user_farm_id) REFERENCES farms(id) ON DELETE SET NULL,
    FOREIGN KEY (purchased_by) REFERENCES farms(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- GENOSSENSCHAFTS-FELDER
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    size_hectares DECIMAL(5,2) NOT NULL,
    position_x INT DEFAULT 0,
    position_y INT DEFAULT 0,
    current_crop_id INT DEFAULT NULL,
    planted_at TIMESTAMP NULL,
    harvest_ready_at TIMESTAMP NULL,
    planted_by INT DEFAULT NULL,
    soil_quality INT DEFAULT 100,
    is_irrigated BOOLEAN DEFAULT FALSE,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    purchased_by INT DEFAULT NULL,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (current_crop_id) REFERENCES crops(id) ON DELETE SET NULL,
    FOREIGN KEY (planted_by) REFERENCES farms(id) ON DELETE SET NULL,
    FOREIGN KEY (purchased_by) REFERENCES farms(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- GENOSSENSCHAFTS-TIERE
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_animals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    animal_type VARCHAR(50) NOT NULL,
    quantity INT DEFAULT 1,
    health_percent INT DEFAULT 100,
    last_fed TIMESTAMP NULL,
    last_product_collected TIMESTAMP NULL,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    purchased_by INT DEFAULT NULL,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (purchased_by) REFERENCES farms(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- GENOSSENSCHAFTS-PRODUKTIONEN
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_productions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    production_id INT NOT NULL,
    level INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    current_recipe VARCHAR(100) DEFAULT NULL,
    production_started_at TIMESTAMP NULL,
    production_ready_at TIMESTAMP NULL,
    started_by INT DEFAULT NULL,
    total_produced INT DEFAULT 0,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    purchased_by INT DEFAULT NULL,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (started_by) REFERENCES farms(id) ON DELETE SET NULL,
    FOREIGN KEY (purchased_by) REFERENCES farms(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- GENOSSENSCHAFTS-FORSCHUNG
-- ============================================

CREATE TABLE IF NOT EXISTS cooperative_research_tree (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('production', 'storage', 'efficiency') NOT NULL,
    cost DECIMAL(10,2) NOT NULL,
    research_time_hours INT NOT NULL,
    required_coop_level INT DEFAULT 1,
    prerequisite_id INT DEFAULT NULL,
    unlocks VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (prerequisite_id) REFERENCES cooperative_research_tree(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Gemeinschafts-Muehle', 'Schaltet eine Muehle fuer die Genossenschaft frei', 'production', 50000, 24, 5, 'production:mehlproduktion');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Gemeinschafts-Molkerei', 'Schaltet eine Molkerei fuer die Genossenschaft frei', 'production', 75000, 36, 8, 'production:molkerei');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Gemeinschafts-Baeckerei', 'Schaltet eine Baeckerei fuer die Genossenschaft frei', 'production', 100000, 48, 10, 'production:baeckerei');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Gemeinschafts-Kaeserei', 'Schaltet eine Kaeserei fuer die Genossenschaft frei', 'production', 120000, 48, 12, 'production:kaeserei');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Gemeinschafts-Schlachterei', 'Schaltet eine Schlachterei fuer die Genossenschaft frei', 'production', 150000, 72, 15, 'production:schlachterei');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Gemeinschafts-Brauerei', 'Schaltet eine Brauerei fuer die Genossenschaft frei', 'production', 200000, 96, 18, 'production:brauerei');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Gemeinschafts-Raffinerie', 'Schaltet eine Oelraffinerie fuer die Genossenschaft frei', 'production', 250000, 120, 20, 'production:raffinerie');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Erweitertes Silo', 'Erhoeht die Lagerkapazitaet um 50%', 'storage', 30000, 12, 3, 'storage:upgrade_50');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Grosses Silo', 'Erhoeht die Lagerkapazitaet um 100%', 'storage', 60000, 24, 7, 'storage:upgrade_100');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Mega-Silo', 'Erhoeht die Lagerkapazitaet um 200%', 'storage', 120000, 48, 12, 'storage:upgrade_200');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Effiziente Ernte', 'Erhoeht den Ernteertrag aller Mitglieder um 5%', 'efficiency', 40000, 24, 5, 'bonus:harvest_5');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Effiziente Tierhaltung', 'Erhoeht die Tierproduktion aller Mitglieder um 5%', 'efficiency', 45000, 24, 6, 'bonus:animal_5');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Kostenoptimierung', 'Reduziert Wartungskosten um 10%', 'efficiency', 50000, 36, 8, 'bonus:maintenance_10');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Handelsabkommen', 'Erhoeht Verkaufspreise um 5%', 'efficiency', 80000, 48, 10, 'bonus:sell_price_5');
INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('Einkaufsgemeinschaft', 'Reduziert Einkaufspreise um 5%', 'efficiency', 80000, 48, 10, 'bonus:buy_price_5');

-- Abgeschlossene Forschungen
CREATE TABLE IF NOT EXISTS cooperative_research (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    research_id INT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    started_by INT DEFAULT NULL,
    status ENUM('in_progress', 'completed', 'cancelled') DEFAULT 'in_progress',
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (research_id) REFERENCES cooperative_research_tree(id) ON DELETE CASCADE,
    FOREIGN KEY (started_by) REFERENCES farms(id) ON DELETE SET NULL,
    UNIQUE KEY unique_coop_research (cooperative_id, research_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- GENOSSENSCHAFTS-HERAUSFORDERUNGEN
-- ============================================

-- Herausforderungs-Vorlagen
CREATE TABLE IF NOT EXISTS cooperative_challenge_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    challenge_type VARCHAR(50) NOT NULL,
    challenge_period ENUM('weekly', 'monthly') NOT NULL,
    target_value INT NOT NULL,
    reward_money DECIMAL(10,2) DEFAULT 0,
    reward_points INT DEFAULT 0,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Woechentliche Herausforderungen
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Gemeinsame Ernte', 'Erntet zusammen 5.000 Einheiten Getreide', 'harvest', 'weekly', 5000, 2500, 500, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Weizen-Offensive', 'Erntet zusammen 3.000 Einheiten Weizen', 'harvest', 'weekly', 3000, 2000, 400, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Mais-Marathon', 'Erntet zusammen 2.500 Einheiten Mais', 'harvest', 'weekly', 2500, 1800, 350, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Kartoffel-Koenig', 'Erntet zusammen 2.000 Einheiten Kartoffeln', 'harvest', 'weekly', 2000, 1500, 300, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Milchlieferung', 'Produziert zusammen 1.000 Liter Milch', 'animal_product', 'weekly', 1000, 2000, 400, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Eier-Sammlung', 'Sammelt zusammen 500 Eier', 'animal_product', 'weekly', 500, 1500, 300, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Wolle-Woche', 'Produziert zusammen 200 Einheiten Wolle', 'animal_product', 'weekly', 200, 1800, 350, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Markt-Meister', 'Verkauft zusammen Waren im Wert von 25.000 Talern', 'sales', 'weekly', 25000, 3000, 600, 'hard');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Handels-Helden', 'Fuehrt zusammen 50 Verkaeufe durch', 'sales_count', 'weekly', 50, 2000, 400, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Brot-Baecker', 'Produziert zusammen 100 Einheiten Brot', 'production', 'weekly', 100, 2500, 500, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Kaese-Koenige', 'Produziert zusammen 50 Einheiten Kaese', 'production', 'weekly', 50, 2200, 450, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Grosszuegige Gemeinschaft', 'Spendet zusammen 10.000 Taler in die Kasse', 'donation', 'weekly', 10000, 1500, 300, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Produkt-Spende', 'Lagert zusammen 500 Produkte ins Silo ein', 'deposit', 'weekly', 500, 1800, 350, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Aktive Gemeinschaft', 'Alle Mitglieder loggen sich mindestens 3x ein', 'activity', 'weekly', 3, 1000, 200, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Teamarbeit', 'Mindestens 5 verschiedene Mitglieder tragen bei', 'participation', 'weekly', 5, 1500, 300, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Forschungsdrang', 'Schliesst eine Genossenschafts-Forschung ab', 'research', 'weekly', 1, 3000, 600, 'hard');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Fuhrpark-Ausbau', 'Kauft zusammen 3 Fahrzeuge fuer die Genossenschaft', 'vehicle_purchase', 'weekly', 3, 2500, 500, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Fleissige Fahrer', 'Nutzt Genossenschafts-Fahrzeuge fuer 100 Stunden', 'vehicle_usage', 'weekly', 100, 2000, 400, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Feld-Expansion', 'Kauft zusammen 2 neue Felder fuer die Genossenschaft', 'field_purchase', 'weekly', 2, 2000, 400, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Vielfalt-Anbau', 'Baut mindestens 5 verschiedene Fruechte an', 'crop_variety', 'weekly', 5, 1500, 300, 'easy');

-- Monatliche Herausforderungen
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Ernte-Giganten', 'Erntet zusammen 50.000 Einheiten beliebiger Feldfruechte', 'harvest', 'monthly', 50000, 25000, 5000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Weizen-Imperium', 'Erntet zusammen 25.000 Einheiten Weizen', 'harvest', 'monthly', 25000, 20000, 4000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Mais-Mogule', 'Erntet zusammen 20.000 Einheiten Mais', 'harvest', 'monthly', 20000, 18000, 3600, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Kartoffel-Koenige', 'Erntet zusammen 15.000 Einheiten Kartoffeln', 'harvest', 'monthly', 15000, 15000, 3000, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Raps-Rekord', 'Erntet zusammen 12.000 Einheiten Raps', 'harvest', 'monthly', 12000, 14000, 2800, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Sonnenblumen-Sommer', 'Erntet zusammen 10.000 Einheiten Sonnenblumen', 'harvest', 'monthly', 10000, 12000, 2400, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Zuckerrueben-Ziel', 'Erntet zusammen 8.000 Einheiten Zuckerrueben', 'harvest', 'monthly', 8000, 10000, 2000, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Gersten-Grossernte', 'Erntet zusammen 15.000 Einheiten Gerste', 'harvest', 'monthly', 15000, 13000, 2600, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Hafer-Helden', 'Erntet zusammen 10.000 Einheiten Hafer', 'harvest', 'monthly', 10000, 11000, 2200, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Vielfalt-Champion', 'Erntet mindestens 10 verschiedene Feldfruechte', 'crop_variety', 'monthly', 10, 15000, 3000, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Milch-Imperium', 'Produziert zusammen 10.000 Liter Milch', 'animal_product', 'monthly', 10000, 20000, 4000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Eier-Explosion', 'Sammelt zusammen 5.000 Eier', 'animal_product', 'monthly', 5000, 15000, 3000, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Wolle-Weltmeister', 'Produziert zusammen 2.000 Einheiten Wolle', 'animal_product', 'monthly', 2000, 18000, 3600, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Fleisch-Fabrik', 'Produziert zusammen 500 Einheiten Fleisch', 'animal_product', 'monthly', 500, 22000, 4400, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Honig-Helden', 'Produziert zusammen 300 Einheiten Honig', 'animal_product', 'monthly', 300, 12000, 2400, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Tier-Vielfalt', 'Haltet mindestens 5 verschiedene Tierarten', 'animal_variety', 'monthly', 5, 10000, 2000, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Handels-Imperium', 'Verkauft zusammen Waren im Wert von 250.000 Talern', 'sales', 'monthly', 250000, 30000, 6000, 'hard');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Markt-Dominanz', 'Fuehrt zusammen 500 Verkaeufe durch', 'sales_count', 'monthly', 500, 20000, 4000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Export-Experten', 'Verkauft an alle 10 Verkaufsstellen', 'selling_points', 'monthly', 10, 15000, 3000, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Gewinn-Giganten', 'Erzielt zusammen einen Gewinn von 100.000 Talern', 'profit', 'monthly', 100000, 25000, 5000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Brot-Barone', 'Produziert zusammen 1.000 Einheiten Brot', 'production', 'monthly', 1000, 25000, 5000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Kaese-Koenige', 'Produziert zusammen 500 Einheiten Kaese', 'production', 'monthly', 500, 22000, 4400, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Butter-Berge', 'Produziert zusammen 400 Einheiten Butter', 'production', 'monthly', 400, 18000, 3600, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Mehl-Meister', 'Produziert zusammen 800 Einheiten Mehl', 'production', 'monthly', 800, 16000, 3200, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Oel-Offensive', 'Produziert zusammen 300 Einheiten Oel', 'production', 'monthly', 300, 20000, 4000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Bier-Brauer', 'Produziert zusammen 200 Einheiten Bier', 'production', 'monthly', 200, 24000, 4800, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Saft-Spezialisten', 'Produziert zusammen 500 Einheiten Saft', 'production', 'monthly', 500, 15000, 3000, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Wurst-Wunder', 'Produziert zusammen 300 Einheiten Wurst', 'production', 'monthly', 300, 20000, 4000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Zucker-Ziel', 'Produziert zusammen 600 Einheiten Zucker', 'production', 'monthly', 600, 18000, 3600, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Produktions-Vielfalt', 'Produziert mindestens 10 verschiedene Produkte', 'product_variety', 'monthly', 10, 20000, 4000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Millionen-Kasse', 'Spendet zusammen 100.000 Taler in die Kasse', 'donation', 'monthly', 100000, 15000, 3000, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Lager-Legende', 'Lagert zusammen 5.000 Produkte ins Silo ein', 'deposit', 'monthly', 5000, 18000, 3600, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Gemeinschafts-Geist', 'Jedes Mitglied spendet mindestens einmal', 'donation_participation', 'monthly', 100, 10000, 2000, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Dauerbrenner', 'Alle Mitglieder loggen sich mindestens 20x ein', 'activity', 'monthly', 20, 10000, 2000, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Volle Beteiligung', 'Alle Mitglieder tragen zu mindestens einer Herausforderung bei', 'challenge_participation', 'monthly', 100, 12000, 2400, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Forschungs-Fuehrer', 'Schliesst 5 Genossenschafts-Forschungen ab', 'research', 'monthly', 5, 30000, 6000, 'hard');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Technologie-Titan', 'Schaltet 3 neue Produktionsstaetten frei', 'production_unlock', 'monthly', 3, 25000, 5000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Fuhrpark-Imperium', 'Besitzt zusammen 20 Genossenschafts-Fahrzeuge', 'vehicle_count', 'monthly', 20, 25000, 5000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Fleissige Flotte', 'Nutzt Genossenschafts-Fahrzeuge fuer 1.000 Stunden', 'vehicle_usage', 'monthly', 1000, 20000, 4000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Premium-Fuhrpark', 'Kauft 5 Fahrzeuge ueber 100.000 Taler', 'premium_vehicle', 'monthly', 5, 30000, 6000, 'hard');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Land-Barone', 'Besitzt zusammen 50 Hektar Felder', 'field_size', 'monthly', 50, 25000, 5000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Feld-Fuehrer', 'Besitzt zusammen 30 Felder', 'field_count', 'monthly', 30, 20000, 4000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Anbau-Meister', 'Baut alle verfuegbaren Feldfruechte mindestens einmal an', 'all_crops', 'monthly', 1, 15000, 3000, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Wachsende Gemeinschaft', 'Rekrutiert 5 neue Mitglieder', 'recruitment', 'monthly', 5, 15000, 3000, 'easy');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Level-Boost', 'Alle Mitglieder steigen mindestens 1 Level auf', 'member_levelup', 'monthly', 1, 20000, 4000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Punkte-Power', 'Sammelt zusammen 50.000 Punkte', 'points', 'monthly', 50000, 25000, 5000, 'medium');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Allrounder', 'Schliesst mindestens 10 woechentliche Herausforderungen ab', 'weekly_complete', 'monthly', 10, 30000, 6000, 'hard');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Perfekter Monat', 'Schliesst alle woechentlichen Herausforderungen des Monats ab', 'perfect_month', 'monthly', 1, 50000, 10000, 'hard');
INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('Top-Genossenschaft', 'Erreicht Platz 1-10 in der Rangliste', 'ranking', 'monthly', 10, 40000, 8000, 'hard');

-- Aktive Herausforderungen pro Genossenschaft
CREATE TABLE IF NOT EXISTS cooperative_challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    template_id INT NOT NULL,
    current_value INT DEFAULT 0,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES cooperative_challenge_templates(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Beitraege zu Herausforderungen
CREATE TABLE IF NOT EXISTS cooperative_challenge_contributions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challenge_id INT NOT NULL,
    farm_id INT NOT NULL,
    contribution_value INT NOT NULL,
    contributed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (challenge_id) REFERENCES cooperative_challenges(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- GENOSSENSCHAFTS-LEVEL
-- ============================================

ALTER TABLE cooperatives ADD COLUMN IF NOT EXISTS level INT DEFAULT 1;
ALTER TABLE cooperatives ADD COLUMN IF NOT EXISTS experience_points INT DEFAULT 0;
ALTER TABLE cooperatives ADD COLUMN IF NOT EXISTS total_challenges_completed INT DEFAULT 0;

SET FOREIGN_KEY_CHECKS = 1;

-- Ende der Migration