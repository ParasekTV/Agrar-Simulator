-- ============================================
-- Storage und Sales System - Migration
-- ============================================

SET NAMES utf8mb4;

-- ============================================
-- FARM STORAGE (Produktlager)
-- ============================================

CREATE TABLE IF NOT EXISTS farm_storage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_farm_product (farm_id, product_id)
);

-- ============================================
-- SALES HISTORY (Verkaufshistorie)
-- ============================================

CREATE TABLE IF NOT EXISTS sales_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    selling_point_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (selling_point_id) REFERENCES selling_points(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================
-- ERWEITERE SELLING_POINTS TABELLE
-- (falls Spalten fehlen)
-- ============================================

-- Füge location Spalte hinzu falls nicht vorhanden
SET @dbname = DATABASE();
SET @tablename = 'selling_points';
SET @columnname = 'location';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) DEFAULT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Füge description Spalte hinzu falls nicht vorhanden
SET @columnname = 'description';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TEXT DEFAULT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================
-- AKTUALISIERE BESTEHENDE VERKAUFSSTELLEN
-- ============================================

UPDATE selling_points SET location = 'Stadtzentrum', description = 'Lokaler Supermarkt mit fairen Preisen' WHERE name = 'Supermarkt Tobi';
UPDATE selling_points SET location = 'Gewerbegebiet', description = 'Großhandel für landwirtschaftliche Produkte' WHERE name = 'Landhandel Markus';
UPDATE selling_points SET location = 'Industriegebiet', description = 'Baumaterialien und Werkzeuge' WHERE name = 'Baumarkt Stefan';
UPDATE selling_points SET location = 'Einkaufszentrum', description = 'Getränke aller Art' WHERE name = 'Getraenkemarkt Lisa';
UPDATE selling_points SET location = 'Hauptstraße', description = 'Kraftstoffe und Snacks' WHERE name = 'Tankstelle Thomas';
UPDATE selling_points SET location = 'Am Stadtrand', description = 'Tierfutter und Zubehör' WHERE name = 'Tierfutterhandel Anna';
UPDATE selling_points SET location = 'Hafen', description = 'Frischer Fisch direkt vom Boot' WHERE name = 'Fischmarkt Klaus';
UPDATE selling_points SET location = 'Marktplatz', description = 'Frisches Obst aus der Region' WHERE name = 'Obsthandel Maria';
UPDATE selling_points SET location = 'Marktplatz', description = 'Frisches Gemüse aus der Region' WHERE name = 'Gemuesehandel Peter';
UPDATE selling_points SET location = 'Innenstadt', description = 'Stoffe und Textilwaren' WHERE name = 'Textilhandel Sandra';

-- ============================================
-- ERWEITERE SELLING_POINT_PRODUCTS
-- (füge fehlende is_active Spalte hinzu)
-- ============================================

SET @tablename = 'selling_point_products';
SET @columnname = 'is_active';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' BOOLEAN DEFAULT TRUE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
