<?php
/**
 * SQL Generator
 *
 * Generiert SQL-Migrationen basierend auf Feature-Definitionen
 */

class SQLGenerator
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Generiert alle SQL-Statements
     */
    public function generateAll(array $features, ?string $featureFilter = null): string
    {
        $sql = "-- ============================================\n";
        $sql .= "-- Farming Simulator v2.0 - Automatisch generiert\n";
        $sql .= "-- Generiert am: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- ============================================\n\n";

        if (!$featureFilter || $featureFilter === 'fields') {
            $sql .= $this->generateFieldsSQL($features['fields'] ?? []);
        }

        if (!$featureFilter || $featureFilter === 'animals') {
            $sql .= $this->generateAnimalsSQL($features['animals'] ?? []);
        }

        if (!$featureFilter || $featureFilter === 'vehicles') {
            $sql .= $this->generateVehiclesSQL($features['vehicles'] ?? []);
        }

        if (!$featureFilter || $featureFilter === 'arena') {
            $sql .= $this->generateArenaSQL($features['arena'] ?? []);
        }

        return $sql;
    }

    /**
     * Generiert SQL für Felder-Erweiterungen
     */
    private function generateFieldsSQL(array $feature): string
    {
        $sql = "-- ============================================\n";
        $sql .= "-- FELDER ERWEITERUNGEN\n";
        $sql .= "-- ============================================\n\n";

        // ALTER TABLE fields
        $sql .= "-- Neue Spalten für fields\n";
        $sql .= "ALTER TABLE fields ADD COLUMN IF NOT EXISTS field_type ENUM('field', 'meadow', 'greenhouse') DEFAULT 'field';\n";
        $sql .= "ALTER TABLE fields ADD COLUMN IF NOT EXISTS needs_cultivation BOOLEAN DEFAULT FALSE;\n";
        $sql .= "ALTER TABLE fields ADD COLUMN IF NOT EXISTS last_cultivated_at TIMESTAMP NULL;\n";
        $sql .= "ALTER TABLE fields ADD COLUMN IF NOT EXISTS cultivation_type ENUM('none', 'grubbed', 'plowed') DEFAULT 'none';\n";
        $sql .= "ALTER TABLE fields ADD COLUMN IF NOT EXISTS weed_level INT DEFAULT 0;\n";
        $sql .= "ALTER TABLE fields ADD COLUMN IF NOT EXISTS weed_appeared_at TIMESTAMP NULL;\n";
        $sql .= "ALTER TABLE fields ADD COLUMN IF NOT EXISTS growth_stage INT DEFAULT 0;\n";
        $sql .= "ALTER TABLE fields ADD COLUMN IF NOT EXISTS max_growth_stages INT DEFAULT 4;\n";
        $sql .= "\n";

        // field_limits Tabelle
        $sql .= "-- Feld-Limits Tabelle\n";
        $sql .= "CREATE TABLE IF NOT EXISTS field_limits (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    field_type ENUM('field', 'meadow', 'greenhouse') NOT NULL,\n";
        $sql .= "    size_hectares DECIMAL(5,2) NOT NULL,\n";
        $sql .= "    max_count INT NOT NULL DEFAULT 10,\n";
        $sql .= "    price_per_hectare DECIMAL(10,2) NOT NULL DEFAULT 2000.00,\n";
        $sql .= "    UNIQUE KEY unique_type_size (field_type, size_hectares)\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        // Insert field_limits
        $sql .= "-- Feld-Limits Daten\n";
        $fieldLimits = $this->config['field_limits'] ?? [];
        foreach ($fieldLimits as $type => $limits) {
            foreach ($limits as $limit) {
                $price = $type === 'greenhouse' ? 5000.00 : ($type === 'meadow' ? 1500.00 : 2000.00);
                $sql .= "INSERT INTO field_limits (field_type, size_hectares, max_count, price_per_hectare) VALUES ";
                $sql .= "('{$type}', {$limit['size']}, {$limit['count']}, {$price}) ON DUPLICATE KEY UPDATE max_count = {$limit['count']};\n";
            }
        }
        $sql .= "\n";

        // cultivation_types Tabelle
        $sql .= "-- Bodenbearbeitungs-Typen\n";
        $sql .= "CREATE TABLE IF NOT EXISTS cultivation_types (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    name VARCHAR(50) NOT NULL,\n";
        $sql .= "    name_de VARCHAR(50) NOT NULL,\n";
        $sql .= "    type ENUM('grubbing', 'plowing') NOT NULL,\n";
        $sql .= "    cost_per_hectare DECIMAL(8,2) NOT NULL,\n";
        $sql .= "    time_hours INT NOT NULL DEFAULT 1,\n";
        $sql .= "    soil_quality_boost INT DEFAULT 0,\n";
        $sql .= "    required_vehicle_type VARCHAR(50) NULL,\n";
        $sql .= "    description TEXT\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        $sql .= "INSERT INTO cultivation_types (name, name_de, type, cost_per_hectare, time_hours, soil_quality_boost, description) VALUES\n";
        $sql .= "('grubbing', 'Grubbern', 'grubbing', 50.00, 1, 5, 'Lockert den Boden auf'),\n";
        $sql .= "('plowing', 'Pflügen', 'plowing', 80.00, 2, 10, 'Tiefes Pflügen für bessere Bodenqualität')\n";
        $sql .= "ON DUPLICATE KEY UPDATE cost_per_hectare = VALUES(cost_per_hectare);\n\n";

        // herbicide_types Tabelle
        $sql .= "-- Herbizid-Typen\n";
        $sql .= "CREATE TABLE IF NOT EXISTS herbicide_types (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    name VARCHAR(50) NOT NULL,\n";
        $sql .= "    name_de VARCHAR(50) NOT NULL,\n";
        $sql .= "    cost_per_hectare DECIMAL(8,2) NOT NULL,\n";
        $sql .= "    effectiveness INT DEFAULT 100,\n";
        $sql .= "    required_research_id INT NULL,\n";
        $sql .= "    description TEXT\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        $sql .= "INSERT INTO herbicide_types (name, name_de, cost_per_hectare, effectiveness, description) VALUES\n";
        $sql .= "('standard_herbicide', 'Standard-Herbizid', 30.00, 80, 'Entfernt 80% des Unkrauts'),\n";
        $sql .= "('premium_herbicide', 'Premium-Herbizid', 60.00, 100, 'Entfernt 100% des Unkrauts'),\n";
        $sql .= "('bio_herbicide', 'Bio-Herbizid', 45.00, 70, 'Umweltfreundlich, 70% Wirksamkeit')\n";
        $sql .= "ON DUPLICATE KEY UPDATE cost_per_hectare = VALUES(cost_per_hectare);\n\n";

        // Crops Erweiterung für Gewächshaus
        $sql .= "-- Crops-Erweiterung\n";
        $sql .= "ALTER TABLE crops ADD COLUMN IF NOT EXISTS growth_stages INT DEFAULT 4;\n";
        $sql .= "ALTER TABLE crops ADD COLUMN IF NOT EXISTS is_greenhouse_only BOOLEAN DEFAULT FALSE;\n";
        $sql .= "ALTER TABLE crops ADD COLUMN IF NOT EXISTS is_meadow_crop BOOLEAN DEFAULT FALSE;\n\n";

        $sql .= "-- Gewächshaus-Gemüse\n";
        $sql .= "INSERT INTO crops (name, category, growth_time_hours, yield_per_hectare, sell_price, buy_price, is_greenhouse_only, growth_stages) VALUES\n";
        $sql .= "('tomaten', 'vegetable', 48, 800, 4.00, 1.50, TRUE, 5),\n";
        $sql .= "('gurken', 'vegetable', 36, 600, 3.50, 1.20, TRUE, 4),\n";
        $sql .= "('paprika', 'vegetable', 60, 500, 4.50, 1.80, TRUE, 5)\n";
        $sql .= "ON DUPLICATE KEY UPDATE is_greenhouse_only = TRUE;\n\n";

        $sql .= "-- Wiesen-Gras\n";
        $sql .= "INSERT INTO crops (name, category, growth_time_hours, yield_per_hectare, sell_price, buy_price, is_meadow_crop, growth_stages) VALUES\n";
        $sql .= "('gras', 'fodder', 24, 1000, 0.50, 0.00, TRUE, 3)\n";
        $sql .= "ON DUPLICATE KEY UPDATE is_meadow_crop = TRUE;\n\n";

        return $sql;
    }

    /**
     * Generiert SQL für Tiere-Erweiterungen
     */
    private function generateAnimalsSQL(array $feature): string
    {
        $sql = "-- ============================================\n";
        $sql .= "-- TIERE ERWEITERUNGEN\n";
        $sql .= "-- ============================================\n\n";

        // ALTER TABLE farm_animals
        $sql .= "-- Neue Spalten für farm_animals\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS is_sick BOOLEAN DEFAULT FALSE;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS sickness_type_id INT NULL;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS sick_since TIMESTAMP NULL;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS straw_level INT DEFAULT 100;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS last_straw_change TIMESTAMP NULL;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS manure_level INT DEFAULT 0;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS last_mucked_out TIMESTAMP NULL;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS water_level INT DEFAULT 100;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS last_watered TIMESTAMP NULL;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS born_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS age_days INT DEFAULT 0;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS can_reproduce BOOLEAN DEFAULT TRUE;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS last_reproduction TIMESTAMP NULL;\n";
        $sql .= "ALTER TABLE farm_animals ADD COLUMN IF NOT EXISTS offspring_count INT DEFAULT 0;\n";
        $sql .= "\n";

        // animal_sicknesses Tabelle
        $sql .= "-- Krankheiten\n";
        $sql .= "CREATE TABLE IF NOT EXISTS animal_sicknesses (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    name VARCHAR(100) NOT NULL,\n";
        $sql .= "    name_de VARCHAR(100) NOT NULL,\n";
        $sql .= "    description TEXT,\n";
        $sql .= "    affects_animal_types JSON,\n";
        $sql .= "    health_reduction_per_day INT DEFAULT 10,\n";
        $sql .= "    happiness_reduction_per_day INT DEFAULT 15,\n";
        $sql .= "    production_reduction_percent INT DEFAULT 50,\n";
        $sql .= "    contagious BOOLEAN DEFAULT FALSE,\n";
        $sql .= "    contagion_chance INT DEFAULT 20,\n";
        $sql .= "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        $sql .= "INSERT INTO animal_sicknesses (name, name_de, affects_animal_types, health_reduction_per_day, production_reduction_percent, contagious) VALUES\n";
        $sql .= "('flu', 'Grippe', '[\"cow\", \"pig\", \"sheep\", \"goat\"]', 15, 60, TRUE),\n";
        $sql .= "('parasites', 'Parasiten', '[\"cow\", \"sheep\", \"goat\", \"horse\"]', 10, 40, FALSE),\n";
        $sql .= "('foot_rot', 'Moderhinke', '[\"sheep\", \"goat\"]', 20, 70, TRUE),\n";
        $sql .= "('avian_flu', 'Vogelgrippe', '[\"chicken\", \"duck\", \"goose\", \"turkey\"]', 25, 80, TRUE),\n";
        $sql .= "('mastitis', 'Mastitis', '[\"cow\", \"goat\", \"buffalo\"]', 15, 50, FALSE)\n";
        $sql .= "ON DUPLICATE KEY UPDATE name = VALUES(name);\n\n";

        // animal_medicines Tabelle
        $sql .= "-- Medikamente\n";
        $sql .= "CREATE TABLE IF NOT EXISTS animal_medicines (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    name VARCHAR(100) NOT NULL,\n";
        $sql .= "    name_de VARCHAR(100) NOT NULL,\n";
        $sql .= "    description TEXT,\n";
        $sql .= "    cost_per_animal DECIMAL(8,2) NOT NULL,\n";
        $sql .= "    cures_sickness_id INT NULL,\n";
        $sql .= "    cure_all BOOLEAN DEFAULT FALSE,\n";
        $sql .= "    effectiveness INT DEFAULT 90,\n";
        $sql .= "    required_research_id INT NULL\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        $sql .= "INSERT INTO animal_medicines (name, name_de, cost_per_animal, cure_all, effectiveness, description) VALUES\n";
        $sql .= "('antibiotics', 'Antibiotika', 50.00, FALSE, 95, 'Gegen bakterielle Infektionen'),\n";
        $sql .= "('antiparasitic', 'Antiparasitikum', 30.00, FALSE, 90, 'Gegen Parasiten'),\n";
        $sql .= "('universal_medicine', 'Universalmedizin', 100.00, TRUE, 80, 'Wirkt gegen alle Krankheiten'),\n";
        $sql .= "('vitamins', 'Vitamine', 20.00, FALSE, 50, 'Stärkt das Immunsystem')\n";
        $sql .= "ON DUPLICATE KEY UPDATE cost_per_animal = VALUES(cost_per_animal);\n\n";

        // animal_feed_types Tabelle
        $sql .= "-- Futterarten\n";
        $sql .= "CREATE TABLE IF NOT EXISTS animal_feed_types (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    name VARCHAR(100) NOT NULL,\n";
        $sql .= "    name_de VARCHAR(100) NOT NULL,\n";
        $sql .= "    source_type ENUM('crop', 'product', 'purchased') NOT NULL,\n";
        $sql .= "    source_id INT NULL,\n";
        $sql .= "    cost_if_purchased DECIMAL(8,2) DEFAULT 0,\n";
        $sql .= "    nutrition_value INT DEFAULT 100,\n";
        $sql .= "    happiness_bonus INT DEFAULT 0,\n";
        $sql .= "    description TEXT\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        $sql .= "INSERT INTO animal_feed_types (name, name_de, source_type, cost_if_purchased, nutrition_value, happiness_bonus) VALUES\n";
        $sql .= "('wheat', 'Weizen', 'crop', 2.00, 100, 5),\n";
        $sql .= "('barley', 'Gerste', 'crop', 1.80, 95, 5),\n";
        $sql .= "('grass', 'Gras', 'crop', 0.50, 80, 10),\n";
        $sql .= "('hay', 'Heu', 'product', 1.00, 90, 8),\n";
        $sql .= "('mixed_feed', 'Mischfutter', 'purchased', 3.00, 120, 15),\n";
        $sql .= "('pig_feed', 'Schweinefutter', 'purchased', 2.50, 110, 10),\n";
        $sql .= "('potatoes', 'Kartoffeln', 'crop', 1.50, 100, 5),\n";
        $sql .= "('carrots', 'Karotten', 'crop', 2.00, 90, 15),\n";
        $sql .= "('sugar', 'Zucker', 'purchased', 1.00, 100, 5)\n";
        $sql .= "ON DUPLICATE KEY UPDATE nutrition_value = VALUES(nutrition_value);\n\n";

        // animal_feed_requirements Tabelle
        $sql .= "-- Futter-Anforderungen pro Tierart\n";
        $sql .= "CREATE TABLE IF NOT EXISTS animal_feed_requirements (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    animal_type VARCHAR(50) NOT NULL,\n";
        $sql .= "    feed_type_id INT NOT NULL,\n";
        $sql .= "    is_primary BOOLEAN DEFAULT FALSE,\n";
        $sql .= "    quantity_per_animal INT DEFAULT 1,\n";
        $sql .= "    FOREIGN KEY (feed_type_id) REFERENCES animal_feed_types(id) ON DELETE CASCADE,\n";
        $sql .= "    UNIQUE KEY unique_animal_feed (animal_type, feed_type_id)\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        // Insert feed requirements basierend auf config
        $feedMapping = $this->config['feed_mapping'] ?? [];
        $feedTypeIds = [
            'Weizen' => 1, 'Gerste' => 2, 'Gras' => 3, 'Heu' => 4,
            'Mischfutter' => 5, 'Schweinefutter' => 6, 'Kartoffeln' => 7,
            'Karotten' => 8, 'Zucker' => 9
        ];

        $sql .= "-- Futter-Zuordnungen\n";
        foreach ($feedMapping as $animal => $feeds) {
            foreach ($feeds as $i => $feed) {
                $feedId = $feedTypeIds[$feed] ?? 1;
                $isPrimary = $i === 0 ? 'TRUE' : 'FALSE';
                $sql .= "INSERT INTO animal_feed_requirements (animal_type, feed_type_id, is_primary) VALUES ";
                $sql .= "('{$animal}', {$feedId}, {$isPrimary}) ON DUPLICATE KEY UPDATE is_primary = {$isPrimary};\n";
            }
        }
        $sql .= "\n";

        // animal_deaths Log
        $sql .= "-- Todes-Log\n";
        $sql .= "CREATE TABLE IF NOT EXISTS animal_deaths (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    farm_id INT NOT NULL,\n";
        $sql .= "    animal_id INT NOT NULL,\n";
        $sql .= "    animal_name VARCHAR(100) NOT NULL,\n";
        $sql .= "    quantity INT DEFAULT 1,\n";
        $sql .= "    death_reason ENUM('age', 'sickness', 'starvation', 'dehydration') NOT NULL,\n";
        $sql .= "    age_at_death INT DEFAULT 0,\n";
        $sql .= "    died_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        $sql .= "    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        // animal_births Log
        $sql .= "-- Geburten-Log\n";
        $sql .= "CREATE TABLE IF NOT EXISTS animal_births (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    farm_id INT NOT NULL,\n";
        $sql .= "    parent_animal_id INT NOT NULL,\n";
        $sql .= "    animal_type VARCHAR(50) NOT NULL,\n";
        $sql .= "    offspring_quantity INT DEFAULT 1,\n";
        $sql .= "    born_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        $sql .= "    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        return $sql;
    }

    /**
     * Generiert SQL für Fahrzeuge-Erweiterungen
     */
    private function generateVehiclesSQL(array $feature): string
    {
        $sql = "-- ============================================\n";
        $sql .= "-- FAHRZEUGE ERWEITERUNGEN\n";
        $sql .= "-- ============================================\n\n";

        // ALTER TABLE farm_vehicles
        $sql .= "-- Neue Spalten für farm_vehicles\n";
        $sql .= "ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS daily_operating_hours DECIMAL(5,2) DEFAULT 0;\n";
        $sql .= "ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS total_operating_hours DECIMAL(10,2) DEFAULT 0;\n";
        $sql .= "ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS is_in_workshop BOOLEAN DEFAULT FALSE;\n";
        $sql .= "ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS workshop_started_at TIMESTAMP NULL;\n";
        $sql .= "ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS workshop_finished_at TIMESTAMP NULL;\n";
        $sql .= "ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS diesel_consumed_today DECIMAL(8,2) DEFAULT 0;\n";
        $sql .= "ALTER TABLE farm_vehicles ADD COLUMN IF NOT EXISTS last_diesel_check TIMESTAMP NULL;\n";
        $sql .= "\n";

        // workshop_repairs Tabelle
        $sql .= "-- Werkstatt-Reparaturen\n";
        $sql .= "CREATE TABLE IF NOT EXISTS workshop_repairs (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    farm_vehicle_id INT NOT NULL,\n";
        $sql .= "    farm_id INT NOT NULL,\n";
        $sql .= "    repair_cost DECIMAL(10,2) NOT NULL,\n";
        $sql .= "    duration_hours INT NOT NULL DEFAULT 2,\n";
        $sql .= "    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        $sql .= "    finished_at TIMESTAMP NULL,\n";
        $sql .= "    status ENUM('in_progress', 'completed', 'cancelled') DEFAULT 'in_progress',\n";
        $sql .= "    condition_before INT NOT NULL,\n";
        $sql .= "    condition_after INT DEFAULT 100,\n";
        $sql .= "    FOREIGN KEY (farm_vehicle_id) REFERENCES farm_vehicles(id) ON DELETE CASCADE,\n";
        $sql .= "    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        // diesel_consumption_log Tabelle
        $sql .= "-- Diesel-Verbrauch Log\n";
        $sql .= "CREATE TABLE IF NOT EXISTS diesel_consumption_log (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    farm_id INT NOT NULL,\n";
        $sql .= "    farm_vehicle_id INT NOT NULL,\n";
        $sql .= "    liters_consumed DECIMAL(8,2) NOT NULL,\n";
        $sql .= "    activity_type VARCHAR(100),\n";
        $sql .= "    consumed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        $sql .= "    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,\n";
        $sql .= "    FOREIGN KEY (farm_vehicle_id) REFERENCES farm_vehicles(id) ON DELETE CASCADE\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        return $sql;
    }

    /**
     * Generiert SQL für Arena
     */
    private function generateArenaSQL(array $feature): string
    {
        $sql = "-- ============================================\n";
        $sql .= "-- WETTKAMPF-ARENA\n";
        $sql .= "-- ============================================\n\n";

        // arena_matches
        $sql .= "-- Arena Matches\n";
        $sql .= "CREATE TABLE IF NOT EXISTS arena_matches (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    challenger_cooperative_id INT NOT NULL,\n";
        $sql .= "    defender_cooperative_id INT NOT NULL,\n";
        $sql .= "    status ENUM('pending', 'pick_ban', 'ready', 'in_progress', 'finished', 'cancelled') DEFAULT 'pending',\n";
        $sql .= "    challenge_sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        $sql .= "    accepted_at TIMESTAMP NULL,\n";
        $sql .= "    started_at TIMESTAMP NULL,\n";
        $sql .= "    finished_at TIMESTAMP NULL,\n";
        $sql .= "    winner_cooperative_id INT NULL,\n";
        $sql .= "    challenger_score INT DEFAULT 0,\n";
        $sql .= "    defender_score INT DEFAULT 0,\n";
        $sql .= "    match_duration_minutes INT DEFAULT 15,\n";
        $sql .= "    FOREIGN KEY (challenger_cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,\n";
        $sql .= "    FOREIGN KEY (defender_cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,\n";
        $sql .= "    FOREIGN KEY (winner_cooperative_id) REFERENCES cooperatives(id) ON DELETE SET NULL\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        // arena_picks_bans
        $sql .= "-- Pick & Ban\n";
        $sql .= "CREATE TABLE IF NOT EXISTS arena_picks_bans (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    match_id INT NOT NULL,\n";
        $sql .= "    cooperative_id INT NOT NULL,\n";
        $sql .= "    vehicle_id INT NOT NULL,\n";
        $sql .= "    action_type ENUM('pick', 'ban') NOT NULL,\n";
        $sql .= "    action_order INT NOT NULL,\n";
        $sql .= "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        $sql .= "    FOREIGN KEY (match_id) REFERENCES arena_matches(id) ON DELETE CASCADE,\n";
        $sql .= "    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,\n";
        $sql .= "    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        // arena_participants
        $sql .= "-- Teilnehmer\n";
        $sql .= "CREATE TABLE IF NOT EXISTS arena_participants (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    match_id INT NOT NULL,\n";
        $sql .= "    farm_id INT NOT NULL,\n";
        $sql .= "    cooperative_id INT NOT NULL,\n";
        $sql .= "    role ENUM('harvest_specialist', 'bale_producer', 'transport') NOT NULL,\n";
        $sql .= "    assigned_vehicle_id INT NULL,\n";
        $sql .= "    score_contribution INT DEFAULT 0,\n";
        $sql .= "    wheat_harvested INT DEFAULT 0,\n";
        $sql .= "    bales_produced INT DEFAULT 0,\n";
        $sql .= "    transported_amount INT DEFAULT 0,\n";
        $sql .= "    is_ready BOOLEAN DEFAULT FALSE,\n";
        $sql .= "    FOREIGN KEY (match_id) REFERENCES arena_matches(id) ON DELETE CASCADE,\n";
        $sql .= "    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,\n";
        $sql .= "    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,\n";
        $sql .= "    FOREIGN KEY (assigned_vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        // arena_score_events
        $sql .= "-- Punkte-Ereignisse\n";
        $sql .= "CREATE TABLE IF NOT EXISTS arena_score_events (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    match_id INT NOT NULL,\n";
        $sql .= "    participant_id INT NOT NULL,\n";
        $sql .= "    event_type ENUM('wheat_harvest', 'bale_production', 'transport_delivery', 'bonus', 'penalty') NOT NULL,\n";
        $sql .= "    base_points INT NOT NULL,\n";
        $sql .= "    multiplier DECIMAL(5,2) DEFAULT 1.0,\n";
        $sql .= "    final_points INT NOT NULL,\n";
        $sql .= "    description TEXT,\n";
        $sql .= "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
        $sql .= "    FOREIGN KEY (match_id) REFERENCES arena_matches(id) ON DELETE CASCADE,\n";
        $sql .= "    FOREIGN KEY (participant_id) REFERENCES arena_participants(id) ON DELETE CASCADE\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        // arena_vehicles_pool
        $sql .= "-- Fahrzeug-Pool für Arena\n";
        $sql .= "CREATE TABLE IF NOT EXISTS arena_vehicles_pool (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    vehicle_id INT NOT NULL,\n";
        $sql .= "    category ENUM('harvester', 'baler', 'tractor', 'trailer') NOT NULL,\n";
        $sql .= "    arena_power_rating INT DEFAULT 100,\n";
        $sql .= "    is_available BOOLEAN DEFAULT TRUE,\n";
        $sql .= "    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        // arena_rankings
        $sql .= "-- Rangliste\n";
        $sql .= "CREATE TABLE IF NOT EXISTS arena_rankings (\n";
        $sql .= "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
        $sql .= "    cooperative_id INT NOT NULL UNIQUE,\n";
        $sql .= "    total_matches INT DEFAULT 0,\n";
        $sql .= "    wins INT DEFAULT 0,\n";
        $sql .= "    losses INT DEFAULT 0,\n";
        $sql .= "    draws INT DEFAULT 0,\n";
        $sql .= "    total_score INT DEFAULT 0,\n";
        $sql .= "    ranking_points INT DEFAULT 1000,\n";
        $sql .= "    last_match_at TIMESTAMP NULL,\n";
        $sql .= "    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE\n";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";

        return $sql;
    }
}
