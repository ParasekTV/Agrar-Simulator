-- Shop/Einkauf-System Migration

SET NAMES utf8mb4;

-- HAENDLER-TABELLE
CREATE TABLE IF NOT EXISTS dealers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    name_de VARCHAR(100) NOT NULL,
    location VARCHAR(100) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    icon VARCHAR(100) DEFAULT 'shop.png',
    price_modifier DECIMAL(4,2) DEFAULT 1.20,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- HAENDLER-PRODUKTE
CREATE TABLE IF NOT EXISTS dealer_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dealer_id INT NOT NULL,
    product_id INT NOT NULL,
    price_modifier DECIMAL(4,2) DEFAULT 1.00,
    min_quantity INT DEFAULT 1,
    max_quantity INT DEFAULT 100,
    is_available TINYINT(1) DEFAULT 1,
    UNIQUE KEY unique_dealer_product (dealer_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EINKAUFSHISTORIE
CREATE TABLE IF NOT EXISTS purchase_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    dealer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(12,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_farm_purchases (farm_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- HAENDLER EINFUEGEN
INSERT IGNORE INTO dealers (name, name_de, location, description, icon, price_modifier) VALUES ('Landhandel', 'Landhandel Meyer', 'Stadtzentrum', 'Der klassische Landhandel mit breitem Sortiment.', 'landhandel.png', 1.15);
INSERT IGNORE INTO dealers (name, name_de, location, description, icon, price_modifier) VALUES ('Bauernmarkt', 'Bauernmarkt am Dorfplatz', 'Dorfplatz', 'Frische Waren direkt vom Erzeuger.', 'bauernmarkt.png', 1.25);
INSERT IGNORE INTO dealers (name, name_de, location, description, icon, price_modifier) VALUES ('Technik-Partner', 'Technik-Partner GmbH', 'Industriegebiet', 'Spezialist fuer technische Produkte.', 'technik.png', 1.10);
INSERT IGNORE INTO dealers (name, name_de, location, description, icon, price_modifier) VALUES ('Grosshandel', 'Agrar Grosshandel', 'Hafen', 'Grosse Mengen zu guenstigen Preisen.', 'grosshandel.png', 1.05);
INSERT IGNORE INTO dealers (name, name_de, location, description, icon, price_modifier) VALUES ('Bio-Laden', 'Bio-Hof Naturkost', 'Altstadt', 'Spezialisiert auf oekologische Produkte.', 'bioladen.png', 1.35);

-- PRODUKTE FUER LANDHANDEL (Saatgut)
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Landhandel'), id, 1.00, 1, 100 FROM products WHERE name = 'Weizen' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Landhandel'), id, 1.00, 1, 100 FROM products WHERE name = 'Gerste' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Landhandel'), id, 1.00, 1, 100 FROM products WHERE name = 'Hafer' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Landhandel'), id, 1.00, 1, 100 FROM products WHERE name = 'Mais' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Landhandel'), id, 1.00, 1, 100 FROM products WHERE name = 'Kartoffeln' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Landhandel'), id, 1.00, 1, 100 FROM products WHERE name = 'Karotten' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Landhandel'), id, 1.00, 1, 100 FROM products WHERE name = 'Raps' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Landhandel'), id, 1.00, 1, 100 FROM products WHERE name = 'Sonnenblumen' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Landhandel'), id, 1.00, 1, 200 FROM products WHERE name = 'Kalk' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Landhandel'), id, 1.00, 1, 200 FROM products WHERE name = 'Diesel' LIMIT 1;

-- PRODUKTE FUER BAUERNMARKT (Tierprodukte, Lebensmittel)
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bauernmarkt'), id, 1.05, 1, 50 FROM products WHERE name = 'Eier' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bauernmarkt'), id, 1.05, 1, 50 FROM products WHERE name = 'Milch' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bauernmarkt'), id, 1.05, 1, 50 FROM products WHERE name = 'Wolle' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bauernmarkt'), id, 1.05, 1, 30 FROM products WHERE name = 'Butter' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bauernmarkt'), id, 1.05, 1, 30 FROM products WHERE name = 'Kaese' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bauernmarkt'), id, 1.05, 1, 50 FROM products WHERE name = 'Honig' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bauernmarkt'), id, 1.05, 1, 30 FROM products WHERE name = 'Brot' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bauernmarkt'), id, 1.05, 1, 50 FROM products WHERE name = 'Mehl' LIMIT 1;

-- PRODUKTE FUER TECHNIK-PARTNER (Betriebsmittel)
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Technik-Partner'), id, 0.95, 5, 500 FROM products WHERE name = 'Diesel' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Technik-Partner'), id, 0.95, 5, 200 FROM products WHERE name = 'Strom' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Technik-Partner'), id, 0.95, 5, 200 FROM products WHERE name = 'Wasser' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Technik-Partner'), id, 0.95, 1, 100 FROM products WHERE name = 'Holz' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Technik-Partner'), id, 0.95, 1, 100 FROM products WHERE name = 'Eimer' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Technik-Partner'), id, 0.95, 1, 100 FROM products WHERE name = 'Flaschen' LIMIT 1;

-- PRODUKTE FUER GROSSHANDEL (Grosse Mengen)
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Grosshandel'), id, 0.90, 10, 1000 FROM products WHERE name = 'Weizen' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Grosshandel'), id, 0.90, 10, 1000 FROM products WHERE name = 'Gerste' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Grosshandel'), id, 0.90, 10, 1000 FROM products WHERE name = 'Mais' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Grosshandel'), id, 0.90, 10, 500 FROM products WHERE name = 'Kartoffeln' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Grosshandel'), id, 0.90, 10, 500 FROM products WHERE name = 'Zucker' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Grosshandel'), id, 0.90, 10, 500 FROM products WHERE name = 'Mehl' LIMIT 1;

-- PRODUKTE FUER BIO-LADEN (Premium)
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bio-Laden'), id, 1.15, 1, 30 FROM products WHERE name = 'Eier' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bio-Laden'), id, 1.15, 1, 30 FROM products WHERE name = 'Milch' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bio-Laden'), id, 1.15, 1, 20 FROM products WHERE name = 'Honig' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bio-Laden'), id, 1.15, 1, 20 FROM products WHERE name = 'Olivenoel' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bio-Laden'), id, 1.15, 1, 20 FROM products WHERE name = 'Sonnenblumenoel' LIMIT 1;
INSERT IGNORE INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity) SELECT (SELECT id FROM dealers WHERE name = 'Bio-Laden'), id, 1.15, 1, 20 FROM products WHERE name = 'Rapsoel' LIMIT 1;

-- Verifizierung
SELECT 'Haendler' AS typ, id, name_de, price_modifier FROM dealers;
SELECT 'Produkte pro Haendler' AS typ, d.name_de, COUNT(dp.id) AS anzahl FROM dealers d LEFT JOIN dealer_products dp ON d.id = dp.dealer_id GROUP BY d.id;
