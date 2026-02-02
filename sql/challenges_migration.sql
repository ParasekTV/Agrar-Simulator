-- ============================================
-- Herausforderungen - Automatisch generiert
-- Generiert am: 2026-02-02 10:07:14
-- ============================================

SET NAMES utf8mb4;

-- ============================================
-- TABELLEN ANPASSEN
-- ============================================

-- Erweitere weekly_challenges Tabelle
ALTER TABLE weekly_challenges ADD COLUMN IF NOT EXISTS reward_money DECIMAL(10,2) DEFAULT 0;
ALTER TABLE weekly_challenges ADD COLUMN IF NOT EXISTS challenge_period ENUM('weekly', 'monthly') DEFAULT 'weekly';

-- Erweitere challenge_type ENUM
ALTER TABLE weekly_challenges MODIFY COLUMN challenge_type ENUM('sales', 'production', 'research', 'cooperative', 'activity') NOT NULL;

-- ============================================
-- CHALLENGE TEMPLATES (Vorlagen)
-- ============================================

CREATE TABLE IF NOT EXISTS challenge_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    challenge_type ENUM('sales', 'production', 'research', 'cooperative', 'activity') NOT NULL,
    challenge_period ENUM('weekly', 'monthly') NOT NULL,
    target_value INT NOT NULL,
    reward_points INT DEFAULT 100,
    reward_money DECIMAL(10,2) DEFAULT 0,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WOECHENTLICHE HERAUSFORDERUNGEN
-- ============================================

INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Erntekoenig', 'Ernte 1.000 Einheiten beliebiger Feldfruechte', 'production', 'weekly', 1000, 150, 500, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Getreideexperte', 'Ernte 500 Einheiten Weizen', 'production', 'weekly', 500, 100, 300, 'easy');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Maismeister', 'Ernte 400 Einheiten Mais', 'production', 'weekly', 400, 100, 300, 'easy');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Kartoffelbauer', 'Ernte 600 Einheiten Kartoffeln', 'production', 'weekly', 600, 120, 400, 'easy');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Marktmeister', 'Verkaufe Waren im Wert von 5.000 Talern', 'sales', 'weekly', 5000, 200, 750, 'hard');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Handelsprofi', 'Verkaufe 20 verschiedene Produkte auf dem Markt', 'sales', 'weekly', 20, 180, 600, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Schnellverkaeufer', 'Verkaufe 50 Einheiten innerhalb von 24 Stunden', 'sales', 'weekly', 50, 150, 400, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Forscher', 'Schliesse 2 Forschungen ab', 'research', 'weekly', 2, 250, 1000, 'hard');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Wissensdurst', 'Starte 3 neue Forschungsprojekte', 'research', 'weekly', 3, 200, 800, 'hard');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Milchbauer', 'Produziere 200 Liter Milch', 'production', 'weekly', 200, 130, 450, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Eiersammler', 'Sammle 100 Eier', 'production', 'weekly', 100, 100, 300, 'easy');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Wollproduzent', 'Produziere 50 Einheiten Wolle', 'production', 'weekly', 50, 120, 400, 'easy');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Teamplayer', 'Hilf 3 Genossenschaftsmitgliedern', 'cooperative', 'weekly', 3, 180, 500, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Gemeinschaftsgeist', 'Spende 1.000 Taler an die Genossenschaftskasse', 'cooperative', 'weekly', 1000, 200, 0, 'hard');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Baecker', 'Produziere 30 Einheiten Brot', 'production', 'weekly', 30, 140, 500, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Kaesemeister', 'Produziere 20 Einheiten Kaese', 'production', 'weekly', 20, 160, 600, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Fleissiger Bauer', 'Logge dich 5 Tage hintereinander ein', 'activity', 'weekly', 5, 100, 250, 'easy');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Feldarbeiter', 'Bearbeite 10 Felder', 'activity', 'weekly', 10, 120, 350, 'easy');

-- ============================================
-- MONATLICHE HERAUSFORDERUNGEN
-- ============================================

INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Erntegigant', 'Ernte 10.000 Einheiten beliebiger Feldfruechte', 'production', 'monthly', 10000, 1000, 5000, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Weizenkoenig', 'Ernte 5.000 Einheiten Weizen', 'production', 'monthly', 5000, 800, 4000, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Vielfaltbauer', 'Ernte mindestens 10 verschiedene Feldfruchtsorten', 'production', 'monthly', 10, 600, 3000, 'easy');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Zuckermagnat', 'Ernte 3.000 Einheiten Zuckerrueben', 'production', 'monthly', 3000, 700, 3500, 'easy');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Rapsbaron', 'Ernte 2.500 Einheiten Raps', 'production', 'monthly', 2500, 650, 3200, 'easy');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Handelsimperium', 'Verkaufe Waren im Wert von 50.000 Talern', 'sales', 'monthly', 50000, 1500, 10000, 'hard');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Marktdominanz', 'Verkaufe 500 Einheiten auf dem Spielermarkt', 'sales', 'monthly', 500, 1000, 6000, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Exportmeister', 'Verkaufe an 5 verschiedene Verkaufsstellen', 'sales', 'monthly', 5, 500, 2500, 'easy');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Gewinnmaximierer', 'Erziele einen Gewinn von 25.000 Talern', 'sales', 'monthly', 25000, 1200, 7500, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Wissenschaftler', 'Schliesse 8 Forschungen ab', 'research', 'monthly', 8, 1500, 8000, 'hard');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Technologiepionier', 'Erreiche Forschungslevel 10', 'research', 'monthly', 10, 2000, 12000, 'hard');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Innovator', 'Schalte 5 neue Produktionen frei', 'research', 'monthly', 5, 1000, 5000, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Milchimperium', 'Produziere 2.000 Liter Milch', 'production', 'monthly', 2000, 900, 4500, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Gefluegelbaron', 'Sammle 1.000 Eier', 'production', 'monthly', 1000, 700, 3500, 'easy');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Viehzuechter', 'Besitze insgesamt 50 Tiere', 'activity', 'monthly', 50, 800, 4000, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Honigproduzent', 'Produziere 100 Einheiten Honig', 'production', 'monthly', 100, 600, 3000, 'easy');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Genossenschaftsheld', 'Hilf 20 Genossenschaftsmitgliedern', 'cooperative', 'monthly', 20, 1200, 6000, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Grossspender', 'Spende 10.000 Taler an die Genossenschaftskasse', 'cooperative', 'monthly', 10000, 1500, 0, 'hard');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Genossenschaftsgruender', 'Werbe 3 neue Mitglieder fuer die Genossenschaft', 'cooperative', 'monthly', 3, 1000, 5000, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Industriebaron', 'Betreibe 5 Produktionsstaetten gleichzeitig', 'production', 'monthly', 5, 1000, 5000, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Brauermeister', 'Produziere 200 Einheiten Bier', 'production', 'monthly', 200, 800, 4000, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Backwarenkoenig', 'Produziere 300 Einheiten Backwaren', 'production', 'monthly', 300, 750, 3800, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Oelmagnat', 'Produziere 150 Einheiten Oel (Raps-, Sonnenblumen- oder Olivenoel)', 'production', 'monthly', 150, 850, 4200, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Fleischproduzent', 'Produziere 100 Einheiten Fleisch', 'production', 'monthly', 100, 900, 4500, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Dauerbrenner', 'Logge dich 25 Tage im Monat ein', 'activity', 'monthly', 25, 800, 4000, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Grossgrundbesitzer', 'Besitze 20 Felder', 'activity', 'monthly', 20, 1000, 5000, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Leveljaeger', 'Steige 3 Level auf', 'activity', 'monthly', 3, 1500, 7500, 'hard');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Punktesammler', 'Sammle 5.000 Punkte', 'activity', 'monthly', 5000, 1000, 5000, 'medium');
INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('Vermoegensaufbau', 'Erreiche ein Vermoegen von 100.000 Talern', 'activity', 'monthly', 100000, 2000, 10000, 'hard');

-- ============================================
-- AKTIVE HERAUSFORDERUNGEN ERSTELLEN
-- ============================================

-- Loesche alte Herausforderungen
DELETE FROM weekly_challenges WHERE active = TRUE;

-- Erstelle 5 zufaellige woechentliche Herausforderungen
INSERT INTO weekly_challenges (challenge_name, description, challenge_type, target_value, reward_points, reward_money, challenge_period, start_date, end_date, active)
SELECT name, description, challenge_type, target_value, reward_points, reward_money, 'weekly',
       CURDATE(),
       DATE_ADD(CURDATE(), INTERVAL 7 DAY),
       TRUE
FROM challenge_templates
WHERE challenge_period = 'weekly' AND is_active = TRUE
ORDER BY RAND()
LIMIT 5;

-- Erstelle 3 zufaellige monatliche Herausforderungen
INSERT INTO weekly_challenges (challenge_name, description, challenge_type, target_value, reward_points, reward_money, challenge_period, start_date, end_date, active)
SELECT name, description, challenge_type, target_value, reward_points, reward_money, 'monthly',
       DATE_FORMAT(CURDATE(), '%Y-%m-01'),
       LAST_DAY(CURDATE()),
       TRUE
FROM challenge_templates
WHERE challenge_period = 'monthly' AND is_active = TRUE
ORDER BY RAND()
LIMIT 3;

-- Ende der Migration