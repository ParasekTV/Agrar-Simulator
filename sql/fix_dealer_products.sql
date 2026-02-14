-- ============================================
-- FIX: Alle Produkte bei allen Haendlern kaufbar machen
-- ============================================
-- Macht alle Produkte bei allen Haendlern verfuegbar

SET NAMES utf8mb4;

-- Zuerst alle bestehenden dealer_products loeschen
DELETE FROM dealer_products;

-- Alle Produkte fuer alle Haendler einfuegen mit unterschiedlichen Preismodifikatoren
-- Landhandel: +15% Aufschlag, ausgewogenes Sortiment
INSERT INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity, is_available)
SELECT
    d.id,
    p.id,
    CASE
        WHEN p.category IN ('crops', 'seeds', 'saatgut') THEN 0.95  -- Saatgut guenstig
        WHEN p.category IN ('fertilizer', 'duenger') THEN 1.00
        ELSE 1.05
    END,
    1,
    100,
    1
FROM dealers d
CROSS JOIN products p
WHERE d.name = 'Landhandel';

-- Bauernmarkt: +25% Aufschlag, Tierprodukte guenstiger
INSERT INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity, is_available)
SELECT
    d.id,
    p.id,
    CASE
        WHEN p.category IN ('animal_products', 'tierprodukte') THEN 0.90  -- Tierprodukte guenstig
        WHEN p.category IN ('food', 'lebensmittel') THEN 0.95
        ELSE 1.10
    END,
    1,
    50,
    1
FROM dealers d
CROSS JOIN products p
WHERE d.name = 'Bauernmarkt';

-- Technik-Partner: +10% Aufschlag, technische Produkte guenstig
INSERT INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity, is_available)
SELECT
    d.id,
    p.id,
    CASE
        WHEN p.name IN ('Diesel', 'Strom', 'Wasser') OR p.name_de IN ('Diesel', 'Strom', 'Wasser') THEN 0.85
        WHEN p.category IN ('equipment', 'containers', 'behaelter') THEN 0.90
        ELSE 1.05
    END,
    1,
    200,
    1
FROM dealers d
CROSS JOIN products p
WHERE d.name = 'Technik-Partner';

-- Grosshandel: +5% Aufschlag, grosse Mengen, alles guenstig
INSERT INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity, is_available)
SELECT
    d.id,
    p.id,
    0.85,  -- Alles 15% unter Normalpreis
    10,    -- Mindestmenge 10
    1000,  -- Grosse Maximalmengen
    1
FROM dealers d
CROSS JOIN products p
WHERE d.name = 'Grosshandel';

-- Bio-Laden: +35% Aufschlag, Premium-Preise
INSERT INTO dealer_products (dealer_id, product_id, price_modifier, min_quantity, max_quantity, is_available)
SELECT
    d.id,
    p.id,
    CASE
        WHEN p.category IN ('organic', 'bio', 'natural') THEN 1.00  -- Bio-Produkte normal
        WHEN p.category IN ('food', 'lebensmittel') THEN 1.10
        ELSE 1.20  -- Alles andere teurer
    END,
    1,
    30,
    1
FROM dealers d
CROSS JOIN products p
WHERE d.name = 'Bio-Laden';

-- Verifizierung: Zeige Anzahl Produkte pro Haendler
SELECT
    d.name_de AS Haendler,
    d.price_modifier AS Haendler_Aufschlag,
    COUNT(dp.id) AS Anzahl_Produkte
FROM dealers d
LEFT JOIN dealer_products dp ON d.id = dp.dealer_id
GROUP BY d.id
ORDER BY d.name_de;

-- Zeige Gesamtstatistik
SELECT
    'Gesamtstatistik' AS Info,
    (SELECT COUNT(*) FROM dealers WHERE is_active = 1) AS Aktive_Haendler,
    (SELECT COUNT(*) FROM products) AS Produkte_Total,
    (SELECT COUNT(*) FROM dealer_products) AS Haendler_Produkt_Zuweisungen;
