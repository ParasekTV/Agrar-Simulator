-- ============================================
-- Farming Simulator v2.0 - Automatisch generiert
-- ============================================

-- ============================================
-- FELDER ERWEITERUNGEN
-- ============================================

-- Neue Spalten für fields
ALTER TABLE fields ADD COLUMN IF NOT EXISTS field_type ENUM('field', 'meadow', 'greenhouse') DEFAULT 'field';
ALTER TABLE fields ADD COLUMN IF NOT EXISTS needs_cultivation BOOLEAN DEFAULT FALSE;
ALTER TABLE fields ADD COLUMN IF NOT EXISTS last_cultivated_at TIMESTAMP NULL;
ALTER TABLE fields ADD COLUMN IF NOT EXISTS cultivation_type ENUM('none', 'grubbed', 'plowed') DEFAULT 'none';
ALTER TABLE fields ADD COLUMN IF NOT EXISTS weed_level INT DEFAULT 0;
ALTER TABLE fields ADD COLUMN IF NOT EXISTS weed_appeared_at TIMESTAMP NULL;
ALTER TABLE fields ADD COLUMN IF NOT EXISTS growth_stage INT DEFAULT 0;
ALTER TABLE fields ADD COLUMN IF NOT EXISTS max_growth_stages INT DEFAULT 4;

-- Feld-Limits Tabelle
CREATE TABLE IF NOT EXISTS field_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_type ENUM('field', 'meadow', 'greenhouse') NOT NULL,
    size_hectares DECIMAL(5,2) NOT NULL,
    max_count INT NOT NULL DEFAULT 10,
    price_per_hectare DECIMAL(10,2) NOT NULL DEFAULT 2000.00,
    UNIQUE KEY unique_type_size (field_type, size_hectares)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Feld-Limits Daten (50 Felder, 10 Wiesen, 10 Gewächshäuser)
INSERT INTO field_limits (field_type, size_hectares, max_count, price_per_hectare) VALUES
('field', 1, 10, 2000.00),
('field', 2, 10, 2000.00),
('field', 3, 10, 2000.00),
('field', 5, 10, 2000.00),
('field', 10, 10, 2000.00),
('meadow', 1, 3, 1500.00),
('meadow', 2, 3, 1500.00),
('meadow', 5, 4, 1500.00),
('greenhouse', 0.5, 5, 5000.00),
('greenhouse', 1, 5, 5000.00)
ON DUPLICATE KEY UPDATE max_count = VALUES(max_count);

-- Bodenbearbeitungs-Typen
CREATE TABLE IF NOT EXISTS cultivation_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    name_de VARCHAR(50) NOT NULL,
    type ENUM('grubbing', 'plowing') NOT NULL,
    cost_per_hectare DECIMAL(8,2) NOT NULL,
    time_hours INT NOT NULL DEFAULT 1,
    soil_quality_boost INT DEFAULT 0,
    required_vehicle_type VARCHAR(50) NULL,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO cultivation_types (name, name_de, type, cost_per_hectare, time_hours, soil_quality_boost, description) VALUES
('grubbing', 'Grubbern', 'grubbing', 50.00, 1, 5, 'Lockert den Boden auf'),
('plowing', 'Pflügen', 'plowing', 80.00, 2, 10, 'Tiefes Pflügen für bessere Bodenqualität')
ON DUPLICATE KEY UPDATE cost_per_hectare = VALUES(cost_per_hectare);

-- Herbizid-Typen
CREATE TABLE IF NOT EXISTS herbicide_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    name_de VARCHAR(50) NOT NULL,
    cost_per_hectare DECIMAL(8,2) NOT NULL,
    effectiveness INT DEFAULT 100,
    required_research_id INT NULL,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO herbicide_types (name, name_de, cost_per_hectare, effectiveness, description) VALUES
('standard_herbicide', 'Standard-Herbizid', 30.00, 80, 'Entfernt 80% des Unkrauts'),
('premium_herbicide', 'Premium-Herbizid', 60.00, 100, 'Entfernt 100% des Unkrauts'),
('bio_herbicide', 'Bio-Herbizid', 45.00, 70, 'Umweltfreundlich, 70% Wirksamkeit')
ON DUPLICATE KEY UPDATE cost_per_hectare = VALUES(cost_per_hectare);

-- Crops-Erweiterung
ALTER TABLE crops ADD COLUMN IF NOT EXISTS growth_stages INT DEFAULT 4;
ALTER TABLE crops ADD COLUMN IF NOT EXISTS is_greenhouse_only BOOLEAN DEFAULT FALSE;
ALTER TABLE crops ADD COLUMN IF NOT EXISTS is_meadow_crop BOOLEAN DEFAULT FALSE;

-- Gewächshaus-Gemüse
INSERT INTO crops (name, category, growth_time_hours, yield_per_hectare, sell_price, buy_price, is_greenhouse_only, growth_stages) VALUES
('tomaten', 'vegetable', 48, 800, 4.00, 1.50, TRUE, 5),
('gurken', 'vegetable', 36, 600, 3.50, 1.20, TRUE, 4),
('paprika', 'vegetable', 60, 500, 4.50, 1.80, TRUE, 5)
ON DUPLICATE KEY UPDATE is_greenhouse_only = TRUE;

-- Wiesen-Gras
INSERT INTO crops (name, category, growth_time_hours, yield_per_hectare, sell_price, buy_price, is_meadow_crop, growth_stages) VALUES
('gras', 'fodder', 24, 1000, 0.50, 0.00, TRUE, 3)
ON DUPLICATE KEY UPDATE is_meadow_crop = TRUE;

-- ============================================
-- TIERE ERWEITERUNGEN
-- ============================================

-- Neue Spalten für farm_animals
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS is_sick BOOLEAN DEFAULT FALSE;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS sickness_type_id INT NULL;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS sick_since TIMESTAMP NULL;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS straw_level INT DEFAULT 100;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS last_straw_change TIMESTAMP NULL;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS manure_level INT DEFAULT 0;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS last_mucked_out TIMESTAMP NULL;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS water_level INT DEFAULT 100;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS last_watered TIMESTAMP NULL;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS born_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS age_days INT DEFAULT 0;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS can_reproduce BOOLEAN DEFAULT TRUE;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS last_reproduction TIMESTAMP NULL;
ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS offspring_count INT DEFAULT 0;

-- Krankheiten
CREATE TABLE IF NOT EXISTS animal_sicknesses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    name_de VARCHAR(100) NOT NULL,
    description TEXT,
    affects_animal_types JSON,
    health_reduction_per_day INT DEFAULT 10,
    happiness_reduction_per_day INT DEFAULT 15,
    production_reduction_percent INT DEFAULT 50,
    contagious BOOLEAN DEFAULT FALSE,
    contagion_chance INT DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO animal_sicknesses (name, name_de, affects_animal_types, health_reduction_per_day, production_reduction_percent, contagious, description) VALUES
('flu', 'Grippe', '["cow", "pig", "sheep", "goat"]', 15, 60, TRUE, 'Ansteckende Atemwegserkrankung'),
('parasites', 'Parasiten', '["cow", "sheep", "goat", "horse"]', 10, 40, FALSE, 'Innere oder äußere Parasiten'),
('foot_rot', 'Moderhinke', '["sheep", "goat"]', 20, 70, TRUE, 'Bakterielle Klauenerkrankung'),
('avian_flu', 'Vogelgrippe', '["chicken", "duck", "goose", "turkey"]', 25, 80, TRUE, 'Hochansteckende Vogelkrankheit'),
('mastitis', 'Mastitis', '["cow", "goat", "buffalo"]', 15, 50, FALSE, 'Euterentzündung')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Medikamente
CREATE TABLE IF NOT EXISTS animal_medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    name_de VARCHAR(100) NOT NULL,
    description TEXT,
    cost_per_animal DECIMAL(8,2) NOT NULL,
    cures_sickness_id INT NULL,
    cure_all BOOLEAN DEFAULT FALSE,
    effectiveness INT DEFAULT 90,
    required_research_id INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO animal_medicines (name, name_de, cost_per_animal, cure_all, effectiveness, description) VALUES
('antibiotics', 'Antibiotika', 50.00, FALSE, 95, 'Gegen bakterielle Infektionen'),
('antiparasitic', 'Antiparasitikum', 30.00, FALSE, 90, 'Gegen Parasiten'),
('universal_medicine', 'Universalmedizin', 100.00, TRUE, 80, 'Wirkt gegen alle Krankheiten'),
('vitamins', 'Vitamine', 20.00, FALSE, 50, 'Stärkt das Immunsystem')
ON DUPLICATE KEY UPDATE cost_per_animal = VALUES(cost_per_animal);

-- Futterarten
CREATE TABLE IF NOT EXISTS animal_feed_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    name_de VARCHAR(100) NOT NULL,
    source_type ENUM('crop', 'product', 'purchased') NOT NULL,
    source_id INT NULL,
    cost_if_purchased DECIMAL(8,2) DEFAULT 0,
    nutrition_value INT DEFAULT 100,
    happiness_bonus INT DEFAULT 0,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO animal_feed_types (name, name_de, source_type, cost_if_purchased, nutrition_value, happiness_bonus) VALUES
('wheat', 'Weizen', 'crop', 2.00, 100, 5),
('barley', 'Gerste', 'crop', 1.80, 95, 5),
('grass', 'Gras', 'crop', 0.50, 80, 10),
('hay', 'Heu', 'product', 1.00, 90, 8),
('mixed_feed', 'Mischfutter', 'purchased', 3.00, 120, 15),
('pig_feed', 'Schweinefutter', 'purchased', 2.50, 110, 10),
('potatoes', 'Kartoffeln', 'crop', 1.50, 100, 5),
('carrots', 'Karotten', 'crop', 2.00, 90, 15),
('sugar', 'Zucker', 'purchased', 1.00, 100, 5)
ON DUPLICATE KEY UPDATE nutrition_value = VALUES(nutrition_value);

-- Futter-Anforderungen pro Tierart
CREATE TABLE IF NOT EXISTS animal_feed_requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_type VARCHAR(50) NOT NULL,
    feed_type_id INT NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    quantity_per_animal INT DEFAULT 1,
    FOREIGN KEY (feed_type_id) REFERENCES animal_feed_types(id) ON DELETE CASCADE,
    UNIQUE KEY unique_animal_feed (animal_type, feed_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Futter-Zuordnungen
INSERT INTO animal_feed_requirements (animal_type, feed_type_id, is_primary) VALUES
-- Hühner: Weizen, Gerste
('chicken', 1, TRUE), ('chicken', 2, FALSE),
-- Schafe: Gras, Heu
('sheep', 3, TRUE), ('sheep', 4, FALSE),
-- Schweine: Schweinefutter, Kartoffeln
('pig', 6, TRUE), ('pig', 7, FALSE),
-- Kühe: Mischfutter, Gras, Heu
('cow', 5, TRUE), ('cow', 3, FALSE), ('cow', 4, FALSE),
-- Pferde: Gras, Heu
('horse', 3, TRUE), ('horse', 4, FALSE),
-- Kaninchen: Gras, Heu, Karotten
('rabbit', 3, TRUE), ('rabbit', 4, FALSE), ('rabbit', 8, FALSE),
-- Bienen: Zucker
('bee', 9, TRUE),
-- Ziegen: Gras, Heu
('goat', 3, TRUE), ('goat', 4, FALSE),
-- Enten: Gras, Weizen
('duck', 3, TRUE), ('duck', 1, FALSE),
-- Gänse: Gras, Weizen
('goose', 3, TRUE), ('goose', 1, FALSE),
-- Truthahn: Gras, Weizen
('turkey', 3, TRUE), ('turkey', 1, FALSE),
-- Wasserbüffel: Gras, Heu, Mischfutter
('buffalo', 3, TRUE), ('buffalo', 4, FALSE), ('buffalo', 5, FALSE)
ON DUPLICATE KEY UPDATE is_primary = VALUES(is_primary);

-- Todes-Log
CREATE TABLE IF NOT EXISTS animal_deaths (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    animal_id INT NOT NULL,
    animal_name VARCHAR(100) NOT NULL,
    quantity INT DEFAULT 1,
    death_reason ENUM('age', 'sickness', 'starvation', 'dehydration') NOT NULL,
    age_at_death INT DEFAULT 0,
    died_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Geburten-Log
CREATE TABLE IF NOT EXISTS animal_births (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    parent_animal_id INT NOT NULL,
    animal_type VARCHAR(50) NOT NULL,
    offspring_quantity INT DEFAULT 1,
    born_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- FAHRZEUGE ERWEITERUNGEN
-- ============================================

-- Neue Spalten für farm_vehicles
ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS daily_operating_hours DECIMAL(5,2) DEFAULT 0;
ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS total_operating_hours DECIMAL(10,2) DEFAULT 0;
ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS is_in_workshop BOOLEAN DEFAULT FALSE;
ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS workshop_started_at TIMESTAMP NULL;
ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS workshop_finished_at TIMESTAMP NULL;
ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS diesel_consumed_today DECIMAL(8,2) DEFAULT 0;
ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS last_diesel_check TIMESTAMP NULL;

-- Werkstatt-Reparaturen
CREATE TABLE IF NOT EXISTS workshop_repairs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_vehicle_id INT NOT NULL,
    farm_id INT NOT NULL,
    repair_cost DECIMAL(10,2) NOT NULL,
    duration_hours INT NOT NULL DEFAULT 2,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    finished_at TIMESTAMP NULL,
    status ENUM('in_progress', 'completed', 'cancelled') DEFAULT 'in_progress',
    condition_before INT NOT NULL,
    condition_after INT DEFAULT 100,
    FOREIGN KEY (farm_vehicle_id) REFERENCES farm_vehicles(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Diesel-Verbrauch Log
CREATE TABLE IF NOT EXISTS diesel_consumption_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    farm_vehicle_id INT NOT NULL,
    liters_consumed DECIMAL(8,2) NOT NULL,
    activity_type VARCHAR(100),
    consumed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_vehicle_id) REFERENCES farm_vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- WETTKAMPF-ARENA
-- ============================================

-- Arena Matches
CREATE TABLE IF NOT EXISTS arena_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challenger_cooperative_id INT NOT NULL,
    defender_cooperative_id INT NOT NULL,
    status ENUM('pending', 'pick_ban', 'ready', 'in_progress', 'finished', 'cancelled') DEFAULT 'pending',
    challenge_sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accepted_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    finished_at TIMESTAMP NULL,
    winner_cooperative_id INT NULL,
    challenger_score INT DEFAULT 0,
    defender_score INT DEFAULT 0,
    match_duration_minutes INT DEFAULT 15,
    FOREIGN KEY (challenger_cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (defender_cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (winner_cooperative_id) REFERENCES cooperatives(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pick & Ban
CREATE TABLE IF NOT EXISTS arena_picks_bans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    cooperative_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    action_type ENUM('pick', 'ban') NOT NULL,
    action_order INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES arena_matches(id) ON DELETE CASCADE,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Teilnehmer
CREATE TABLE IF NOT EXISTS arena_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    farm_id INT NOT NULL,
    cooperative_id INT NOT NULL,
    role ENUM('harvest_specialist', 'bale_producer', 'transport') NOT NULL,
    assigned_vehicle_id INT NULL,
    score_contribution INT DEFAULT 0,
    wheat_harvested INT DEFAULT 0,
    bales_produced INT DEFAULT 0,
    transported_amount INT DEFAULT 0,
    is_ready BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (match_id) REFERENCES arena_matches(id) ON DELETE CASCADE,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Punkte-Ereignisse
CREATE TABLE IF NOT EXISTS arena_score_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    participant_id INT NOT NULL,
    event_type ENUM('wheat_harvest', 'bale_production', 'transport_delivery', 'bonus', 'penalty') NOT NULL,
    base_points INT NOT NULL,
    multiplier DECIMAL(5,2) DEFAULT 1.0,
    final_points INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES arena_matches(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES arena_participants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fahrzeug-Pool für Arena
CREATE TABLE IF NOT EXISTS arena_vehicles_pool (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    category ENUM('harvester', 'baler', 'tractor', 'trailer') NOT NULL,
    arena_power_rating INT DEFAULT 100,
    is_available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rangliste
CREATE TABLE IF NOT EXISTS arena_rankings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cooperative_id INT NOT NULL UNIQUE,
    total_matches INT DEFAULT 0,
    wins INT DEFAULT 0,
    losses INT DEFAULT 0,
    draws INT DEFAULT 0,
    total_score INT DEFAULT 0,
    ranking_points INT DEFAULT 1000,
    last_match_at TIMESTAMP NULL,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- ENDE DER MIGRATION
-- ============================================
