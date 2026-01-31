-- Landwirtschafts-Simulator Browsergame
-- Komplettes Datenbank-Schema

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. BENUTZER & ACCOUNTS
-- ============================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS farms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    farm_name VARCHAR(100) NOT NULL,
    money DECIMAL(15,2) DEFAULT 10000.00,
    points INT DEFAULT 0,
    level INT DEFAULT 1,
    experience INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_points (points DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. FORSCHUNGSSYSTEM (muss vor anderen Tabellen kommen wegen FK)
-- ============================================

CREATE TABLE IF NOT EXISTS research_tree (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('crops', 'animals', 'vehicles', 'buildings', 'efficiency') NOT NULL,
    cost DECIMAL(10,2) NOT NULL,
    research_time_hours INT NOT NULL,
    prerequisite_id INT NULL,
    level_required INT DEFAULT 1,
    points_reward INT DEFAULT 50,
    FOREIGN KEY (prerequisite_id) REFERENCES research_tree(id),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS farm_research (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    research_id INT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    status ENUM('in_progress', 'completed', 'cancelled') DEFAULT 'in_progress',
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (research_id) REFERENCES research_tree(id),
    UNIQUE KEY unique_farm_research (farm_id, research_id),
    INDEX idx_farm_id (farm_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. GEBAEUDE
-- ============================================

CREATE TABLE IF NOT EXISTS buildings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('barn', 'silo', 'warehouse', 'garage', 'stable') NOT NULL,
    cost DECIMAL(10,2) NOT NULL,
    unlock_research_id INT NULL,
    storage_capacity INT DEFAULT 0,
    production_bonus DECIMAL(5,2) DEFAULT 0,
    maintenance_cost DECIMAL(8,2) DEFAULT 0,
    image_url VARCHAR(255),
    FOREIGN KEY (unlock_research_id) REFERENCES research_tree(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS farm_buildings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    building_id INT NOT NULL,
    level INT DEFAULT 1,
    position_x INT NOT NULL,
    position_y INT NOT NULL,
    built_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (building_id) REFERENCES buildings(id),
    INDEX idx_farm_id (farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. FELDER & ANBAU
-- ============================================

CREATE TABLE IF NOT EXISTS crops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    growth_time_hours INT NOT NULL,
    sell_price DECIMAL(8,2) NOT NULL,
    buy_price DECIMAL(8,2) NOT NULL,
    yield_per_hectare INT DEFAULT 100,
    required_research_id INT NULL,
    water_need INT DEFAULT 50,
    image_url VARCHAR(255),
    FOREIGN KEY (required_research_id) REFERENCES research_tree(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    size_hectares DECIMAL(5,2) NOT NULL,
    position_x INT NOT NULL,
    position_y INT NOT NULL,
    current_crop_id INT NULL,
    planted_at TIMESTAMP NULL,
    harvest_ready_at TIMESTAMP NULL,
    status ENUM('empty', 'growing', 'ready', 'harvesting') DEFAULT 'empty',
    soil_quality INT DEFAULT 100,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (current_crop_id) REFERENCES crops(id),
    INDEX idx_farm_id (farm_id),
    INDEX idx_status (status),
    INDEX idx_fields_farm_status (farm_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. TIERE
-- ============================================

CREATE TABLE IF NOT EXISTS animals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type ENUM('cow', 'pig', 'chicken', 'sheep', 'horse') NOT NULL,
    cost DECIMAL(8,2) NOT NULL,
    production_item VARCHAR(50) NOT NULL,
    production_time_hours INT NOT NULL,
    production_quantity INT DEFAULT 1,
    required_research_id INT NULL,
    feed_cost DECIMAL(6,2) DEFAULT 5.00,
    image_url VARCHAR(255),
    FOREIGN KEY (required_research_id) REFERENCES research_tree(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS farm_animals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    animal_id INT NOT NULL,
    quantity INT DEFAULT 1,
    last_collection TIMESTAMP NULL,
    last_feeding TIMESTAMP NULL,
    health_status INT DEFAULT 100,
    happiness INT DEFAULT 100,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (animal_id) REFERENCES animals(id),
    INDEX idx_farm_id (farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS animal_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    base_sell_price DECIMAL(8,2) NOT NULL,
    from_animal_type ENUM('cow', 'pig', 'chicken', 'sheep', 'horse') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. FAHRZEUGE & GERAETE
-- ============================================

CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('tractor', 'harvester', 'seeder', 'plow', 'trailer') NOT NULL,
    cost DECIMAL(10,2) NOT NULL,
    efficiency_bonus DECIMAL(5,2) DEFAULT 0,
    fuel_consumption DECIMAL(5,2) DEFAULT 10.00,
    required_research_id INT NULL,
    maintenance_interval_hours INT DEFAULT 100,
    image_url VARCHAR(255),
    FOREIGN KEY (required_research_id) REFERENCES research_tree(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS farm_vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    condition_percent INT DEFAULT 100,
    hours_used INT DEFAULT 0,
    available_for_lending BOOLEAN DEFAULT FALSE,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    INDEX idx_farm_id (farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. MARKTPLATZ
-- ============================================

CREATE TABLE IF NOT EXISTS market_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_farm_id INT NOT NULL,
    item_type ENUM('crop', 'animal_product', 'vehicle', 'material') NOT NULL,
    item_id INT NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(8,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    status ENUM('active', 'sold', 'expired', 'cancelled') DEFAULT 'active',
    FOREIGN KEY (seller_farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_item_type (item_type),
    INDEX idx_created_at (created_at DESC),
    INDEX idx_market_listings_status_type (status, item_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS market_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    buyer_farm_id INT NOT NULL,
    seller_farm_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES market_listings(id),
    FOREIGN KEY (buyer_farm_id) REFERENCES farms(id),
    FOREIGN KEY (seller_farm_id) REFERENCES farms(id),
    INDEX idx_buyer_farm_id (buyer_farm_id),
    INDEX idx_seller_farm_id (seller_farm_id),
    INDEX idx_transaction_date (transaction_date DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. AGRARGENOSSENSCHAFTEN
-- ============================================

CREATE TABLE IF NOT EXISTS cooperatives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    founder_farm_id INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_points INT DEFAULT 0,
    member_limit INT DEFAULT 20,
    treasury DECIMAL(12,2) DEFAULT 0,
    FOREIGN KEY (founder_farm_id) REFERENCES farms(id),
    INDEX idx_total_points (total_points DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cooperative_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    farm_id INT NOT NULL,
    role ENUM('founder', 'admin', 'member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    contribution_points INT DEFAULT 0,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_coop_member (cooperative_id, farm_id),
    INDEX idx_cooperative_id (cooperative_id),
    INDEX idx_farm_id (farm_id),
    INDEX idx_cooperative_members_coop_farm (cooperative_id, farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cooperative_shared_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL,
    farm_vehicle_id INT NOT NULL,
    owner_farm_id INT NOT NULL,
    available BOOLEAN DEFAULT TRUE,
    lending_fee_per_hour DECIMAL(6,2) DEFAULT 0,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_vehicle_id) REFERENCES farm_vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_farm_id) REFERENCES farms(id),
    INDEX idx_cooperative_id (cooperative_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS equipment_lending_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    borrower_farm_id INT NOT NULL,
    lent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    returned_at TIMESTAMP NULL,
    hours_used DECIMAL(5,2) DEFAULT 0,
    fee_paid DECIMAL(8,2) DEFAULT 0,
    FOREIGN KEY (equipment_id) REFERENCES cooperative_shared_equipment(id),
    FOREIGN KEY (borrower_farm_id) REFERENCES farms(id),
    INDEX idx_borrower_farm_id (borrower_farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. SOZIALE FEATURES (ZEITUNG/FORUM)
-- ============================================

CREATE TABLE IF NOT EXISTS news_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_farm_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('announcement', 'market', 'cooperative', 'tips', 'offtopic') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    views INT DEFAULT 0,
    likes INT DEFAULT 0,
    is_pinned BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (author_farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_created_at (created_at DESC),
    INDEX idx_is_pinned (is_pinned)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    author_farm_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    likes INT DEFAULT 0,
    FOREIGN KEY (post_id) REFERENCES news_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (author_farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_post_id (post_id),
    INDEX idx_created_at (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    farm_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES news_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_like (post_id, farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. RANGLISTEN
-- ============================================

CREATE TABLE IF NOT EXISTS rankings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    total_points INT DEFAULT 0,
    total_money DECIMAL(15,2) DEFAULT 0,
    total_sales_value DECIMAL(15,2) DEFAULT 0,
    rank_position INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_farm_ranking (farm_id),
    INDEX idx_total_points (total_points DESC),
    INDEX idx_total_money (total_money DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS weekly_challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challenge_name VARCHAR(100) NOT NULL,
    description TEXT,
    challenge_type ENUM('sales', 'production', 'research', 'cooperative') NOT NULL,
    target_value INT NOT NULL,
    reward_points INT DEFAULT 100,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS challenge_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challenge_id INT NOT NULL,
    farm_id INT NOT NULL,
    current_value INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (challenge_id) REFERENCES weekly_challenges(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_challenge_farm (challenge_id, farm_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. INVENTAR & PRODUKTE
-- ============================================

CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    item_type ENUM('crop', 'animal_product', 'material', 'fuel') NOT NULL,
    item_id INT NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    quantity INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_farm_id (farm_id),
    INDEX idx_item_type (item_type),
    INDEX idx_inventory_farm_type_item (farm_id, item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. SPIEL-EVENTS & LOGS
-- ============================================

CREATE TABLE IF NOT EXISTS game_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    event_type ENUM('harvest', 'sale', 'purchase', 'research', 'building', 'level_up', 'points') NOT NULL,
    description TEXT,
    points_earned INT DEFAULT 0,
    money_change DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    INDEX idx_farm_id (farm_id),
    INDEX idx_created_at (created_at DESC),
    INDEX idx_game_events_farm_created (farm_id, created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    ip_address VARCHAR(45),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 13. RATE LIMITING
-- ============================================

CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- INITIALE DATEN
-- ============================================

-- Forschungsbaum
INSERT INTO research_tree (id, name, description, category, cost, research_time_hours, prerequisite_id, level_required, points_reward) VALUES
(1, 'Grundlagen Ackerbau', 'Erlaubt Anbau von Basis-Pflanzen', 'crops', 0, 1, NULL, 1, 50),
(2, 'Viehzucht Basics', 'Ermoeglicht Haltung von Kuehen und Schweinen', 'animals', 500.00, 2, 1, 1, 100),
(3, 'Mechanisierung Stufe 1', 'Schaltet bessere Traktoren frei', 'vehicles', 1000.00, 3, 1, 1, 100),
(4, 'Spezialkulturen', 'Ermoeglicht Anbau von Sonnenblumen und Zuckerrueben', 'crops', 2000.00, 4, 1, 5, 200),
(5, 'Grossviehzucht', 'Schaltet Schafe frei', 'animals', 3000.00, 5, 2, 5, 250),
(6, 'Moderne Maschinen', 'Schaltet Maehdrescher frei', 'vehicles', 5000.00, 6, 3, 8, 300),
(7, 'Gewaechshaus-Technologie', 'Ermoeglicht ganzjaehrigen Anbau', 'buildings', 10000.00, 12, 4, 10, 400),
(8, 'Automatisierung', 'Reduziert Arbeitszeit um 20%', 'efficiency', 15000.00, 24, 6, 12, 500),
(9, 'Bio-Landwirtschaft', 'Hoehere Verkaufspreise fuer Bio-Produkte', 'efficiency', 8000.00, 8, 4, 8, 350);

-- Basis-Crops
INSERT INTO crops (name, description, growth_time_hours, sell_price, buy_price, yield_per_hectare, required_research_id, water_need) VALUES
('Weizen', 'Grundlegende Getreideart', 4, 150.00, 50.00, 100, NULL, 50),
('Mais', 'Ertragreiches Getreide', 6, 200.00, 80.00, 120, NULL, 60),
('Kartoffeln', 'Vielseitiges Gemuese', 8, 250.00, 100.00, 150, NULL, 70),
('Gerste', 'Fuer Bier und Tierfutter', 5, 180.00, 60.00, 110, NULL, 45),
('Raps', 'Oelpflanze', 7, 220.00, 90.00, 90, NULL, 55),
('Sonnenblumen', 'Oelsaat-Pflanze', 10, 300.00, 120.00, 80, 4, 65),
('Zuckerrueben', 'Industriepflanze', 12, 400.00, 150.00, 200, 4, 80),
('Sojabohnen', 'Proteinreiche Huelsenfrucht', 9, 280.00, 110.00, 95, 4, 60);

-- Basis-Tiere
INSERT INTO animals (name, type, cost, production_item, production_time_hours, production_quantity, required_research_id, feed_cost) VALUES
('Milchkuh', 'cow', 5000.00, 'Milch', 24, 20, 2, 10.00),
('Huhn', 'chicken', 500.00, 'Eier', 24, 12, NULL, 2.00),
('Schwein', 'pig', 2000.00, 'Fleisch', 168, 1, 2, 8.00),
('Schaf', 'sheep', 1500.00, 'Wolle', 168, 5, 5, 5.00),
('Pferd', 'horse', 8000.00, 'Reitdienste', 24, 1, 5, 15.00);

-- Tierprodukte
INSERT INTO animal_products (name, base_sell_price, from_animal_type) VALUES
('Milch', 2.50, 'cow'),
('Eier', 0.50, 'chicken'),
('Fleisch', 150.00, 'pig'),
('Wolle', 25.00, 'sheep'),
('Reitdienste', 100.00, 'horse');

-- Basis-Fahrzeuge
INSERT INTO vehicles (name, type, cost, efficiency_bonus, fuel_consumption, required_research_id, maintenance_interval_hours) VALUES
('Alter Traktor', 'tractor', 0, 0, 5.00, NULL, 50),
('Standard Traktor', 'tractor', 15000.00, 10.00, 8.00, 3, 100),
('Premium Traktor', 'tractor', 35000.00, 20.00, 10.00, 6, 150),
('Saemaschine', 'seeder', 8000.00, 5.00, 3.00, 3, 80),
('Maehdrescher', 'harvester', 50000.00, 25.00, 15.00, 6, 120),
('Pflug', 'plow', 5000.00, 5.00, 2.00, NULL, 60),
('Anhaenger', 'trailer', 3000.00, 0, 0, NULL, 200);

-- Basis-Gebaeude
INSERT INTO buildings (name, description, type, cost, unlock_research_id, storage_capacity, production_bonus, maintenance_cost) VALUES
('Kleine Scheune', 'Grundlegende Lagerung', 'barn', 5000.00, NULL, 1000, 0, 50.00),
('Grosse Scheune', 'Erweiterte Lagerung', 'barn', 15000.00, 3, 5000, 5.00, 150.00),
('Silo', 'Getreidelagerung', 'silo', 10000.00, 3, 3000, 0, 100.00),
('Lagerhalle', 'Allgemeine Lagerung', 'warehouse', 20000.00, 6, 8000, 0, 200.00),
('Garage', 'Fahrzeuglagerung', 'garage', 8000.00, NULL, 0, 0, 80.00),
('Stall', 'Tierhaltung', 'stable', 12000.00, 2, 0, 10.00, 120.00);

-- Woechentliche Herausforderungen (Beispiel)
INSERT INTO weekly_challenges (challenge_name, description, challenge_type, target_value, reward_points, start_date, end_date, active) VALUES
('Erntekoenig', 'Ernte 1000 Einheiten Getreide', 'production', 1000, 150, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), TRUE),
('Marktmeister', 'Verkaufe Waren im Wert von 5000 Euro', 'sales', 5000, 200, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), TRUE),
('Forscher', 'Schliesse 2 Forschungen ab', 'research', 2, 250, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), TRUE);
