# Feature Plan v1.2 - Umfangreiche Erweiterungen

## Übersicht der geplanten Features

---

## 1. PRODUKTIONEN - Kontinuierliche Produktion

### Beschreibung
- Produktionen laufen kontinuierlich bis manuell gestoppt
- Automatischer Verbrauch von Rohstoffen aus dem Lager
- Produktion stoppt automatisch wenn Rohstoffe aufgebraucht sind
- Partielle Effizienz bei unvollständigen Inputs

### Effizienz-System
```
Beispiel Käserei:
- Alle Inputs vorhanden: 100% Effizienz
- Nur Kuhmilch: 60% Effizienz → nur Käse
- Nur Ziegenmilch: 40% Effizienz → nur Ziegenkäse
- Nur Büffelmilch: 40% Effizienz → nur Büffelmozzarella
- Mehrere Milchsorten: Effizienz addiert sich
```

### Datenbank-Änderungen
```sql
ALTER TABLE farm_productions ADD COLUMN is_running TINYINT(1) DEFAULT 0;
ALTER TABLE farm_productions ADD COLUMN started_at TIMESTAMP NULL;
ALTER TABLE farm_productions ADD COLUMN cycles_completed INT DEFAULT 0;
ALTER TABLE farm_productions ADD COLUMN current_efficiency DECIMAL(5,2) DEFAULT 100.00;
```

### Betroffene Dateien
- `app/models/Production.php` - Logik für kontinuierliche Produktion
- `app/controllers/ProductionController.php` - Start/Stop Aktionen
- `app/views/productions/show.php` - UI für Start/Stop
- `cron/production_cycle.php` - Neuer Cron-Job für Produktionszyklen

---

## 2. TIERHALTUNG - Kapazitätssystem

### Beschreibung
- Tierhaltungs-Produktionen (Kuhstall, Hühnerstall, etc.) erhöhen Tierkapazität
- Ohne Stall: 0 Tiere dieser Art möglich
- Mit Stall: Kapazität = Stallgröße × Anzahl Ställe

### Datenbank-Änderungen
```sql
ALTER TABLE productions ADD COLUMN animal_type VARCHAR(50) NULL;
ALTER TABLE productions ADD COLUMN animal_capacity INT DEFAULT 0;

-- Beispiel-Updates
UPDATE productions SET animal_type = 'cow', animal_capacity = 20 WHERE name = 'Kuhstall';
UPDATE productions SET animal_type = 'chicken', animal_capacity = 50 WHERE name = 'Hühnerstall';
UPDATE productions SET animal_type = 'pig', animal_capacity = 15 WHERE name = 'Schweinestall';
```

### Betroffene Dateien
- `app/models/Animal.php` - Kapazitätsprüfung
- `app/models/Production.php` - Kapazitätsberechnung
- `app/views/animals/index.php` - Anzeige der Kapazität

---

## 3. FORSCHUNG - Erweitert

### 3.1 Tiere einzeln erforschbar
```sql
INSERT INTO research_tree (name, description, category, cost, research_time_hours, level_required) VALUES
('Bienenzucht', 'Ermöglicht die Haltung von Bienen', 'animals', 5000, 4, 3),
('Entenhaltung', 'Ermöglicht die Haltung von Enten', 'animals', 3000, 3, 2),
('Gänsehaltung', 'Ermöglicht die Haltung von Gänsen', 'animals', 3500, 3, 2),
('Büffelzucht', 'Ermöglicht die Haltung von Wasserbüffeln', 'animals', 8000, 6, 5),
('Ziegenhaltung', 'Ermöglicht die Haltung von Ziegen', 'animals', 4000, 4, 3),
('Pferdezucht', 'Ermöglicht die Haltung von Pferden', 'animals', 10000, 8, 6);
```

### 3.2 Feldfrüchte einzeln erforschbar
```sql
INSERT INTO research_tree (name, description, category, cost, research_time_hours, level_required) VALUES
('Weizenanbau', 'Ermöglicht den Anbau von Weizen', 'crops', 0, 0, 1),
('Maisanbau', 'Ermöglicht den Anbau von Mais', 'crops', 1000, 1, 1),
('Kartoffelanbau', 'Ermöglicht den Anbau von Kartoffeln', 'crops', 1500, 2, 2),
-- ... für alle Feldfrüchte
```

### Betroffene Dateien
- `app/models/Research.php`
- `app/models/Animal.php` - Prüfung ob Tier erforscht
- `app/models/Field.php` - Prüfung ob Feldfrucht erforscht
- `app/views/research/index.php` - Neue Kategorien

---

## 4. VERKAUFSSTELLEN (Salespoints)

### 4.1 Preis-Countdown
- Anzeige wann sich Preise ändern (in HH:MM:SS)
- Preise ändern sich täglich um 00:00 Uhr

### 4.2 Suchfunktion
- Suche nach Produkt: "Wer kauft Käse?"
- Zeigt alle Verkaufsstellen die das Produkt kaufen
- Sortiert nach bestem Preis

### Betroffene Dateien
- `app/views/salespoints/index.php` - Countdown + Suchfeld
- `app/controllers/SalesPointController.php` - Suchaktion
- `public/js/countdown.js` - Timer-Logik

---

## 5. SHOP (Einkauf)

### 5.1 Preis-Countdown
- Gleiche Logik wie Salespoints

### 5.2 Suchfunktion
- Suche nach Produkt: "Wer verkauft Saatgut?"
- Zeigt alle Händler die das Produkt verkaufen

### 5.3 Alle Produkte verfügbar
- Jeder Händler bietet alle seine Kategorien an
- Alle Produkte im Spiel können gekauft werden

### Datenbank-Änderungen
```sql
-- Mehr Produkte zu Händlern hinzufügen
INSERT INTO dealer_products (dealer_id, product_id, base_price, price_variance)
SELECT d.id, p.id, p.base_price * 1.2, 0.15
FROM dealers d
CROSS JOIN products p
WHERE p.category IN (SELECT category FROM dealer_categories WHERE dealer_id = d.id);
```

---

## 6. MARKTPLATZ - Angebot pushen

### Beschreibung
- Spieler können ihr Angebot für X Taler "pushen"
- Gepushte Angebote erscheinen ganz oben
- Push-Dauer: 24 Stunden
- Kosten: z.B. 500 Taler

### Datenbank-Änderungen
```sql
ALTER TABLE market_listings ADD COLUMN is_pushed TINYINT(1) DEFAULT 0;
ALTER TABLE market_listings ADD COLUMN pushed_until TIMESTAMP NULL;
ALTER TABLE market_listings ADD COLUMN push_cost DECIMAL(10,2) DEFAULT 0;
```

### Betroffene Dateien
- `app/models/Market.php`
- `app/controllers/MarketController.php`
- `app/views/market/index.php` - Push-Button
- `app/views/market/my_listings.php` - Push-Option

---

## 7. GENOSSENSCHAFT - Pinnwand

### Beschreibung
- Internes Forum nur für Genossenschaftsmitglieder
- Lesen, Schreiben, Liken von Beiträgen
- Nur sichtbar für Mitglieder der eigenen Genossenschaft

### Datenbank-Änderungen
```sql
CREATE TABLE cooperative_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL,
    author_farm_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id),
    FOREIGN KEY (author_farm_id) REFERENCES farms(id)
);

CREATE TABLE cooperative_post_likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    farm_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES cooperative_posts(id),
    FOREIGN KEY (farm_id) REFERENCES farms(id),
    UNIQUE KEY unique_like (post_id, farm_id)
);

CREATE TABLE cooperative_post_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    author_farm_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES cooperative_posts(id),
    FOREIGN KEY (author_farm_id) REFERENCES farms(id)
);
```

### Betroffene Dateien
- `app/models/CooperativePost.php` - Neues Model
- `app/controllers/CooperativeController.php` - Neue Aktionen
- `app/views/cooperative/board.php` - Neue View
- `app/views/cooperative/show.php` - Link zur Pinnwand

---

## 8. FAHRZEUGE - Ausleihen an Genossenschaft

### Beschreibung
- Fahrzeug an Genossenschaft ausleihen
- Ausleihdauer in Stunden festlegen
- Ausgeliehene Fahrzeuge unter "Geteilte Geräte" in Genossenschaft

### Datenbank-Änderungen
```sql
ALTER TABLE farm_vehicles ADD COLUMN lent_to_cooperative_id INT NULL;
ALTER TABLE farm_vehicles ADD COLUMN lent_until TIMESTAMP NULL;
ALTER TABLE farm_vehicles ADD COLUMN lent_at TIMESTAMP NULL;

-- Oder separate Tabelle
CREATE TABLE cooperative_shared_vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    farm_vehicle_id INT NOT NULL,
    cooperative_id INT NOT NULL,
    lender_farm_id INT NOT NULL,
    lent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lent_until TIMESTAMP NOT NULL,
    returned_at TIMESTAMP NULL,
    FOREIGN KEY (farm_vehicle_id) REFERENCES farm_vehicles(id),
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id),
    FOREIGN KEY (lender_farm_id) REFERENCES farms(id)
);
```

### Betroffene Dateien
- `app/models/Vehicle.php`
- `app/controllers/VehicleController.php`
- `app/views/vehicles/index.php` - Ausleihen-Button
- `app/views/cooperative/equipment.php` - Geteilte Geräte

---

## 9. GENOSSENSCHAFT - Produktionen

### Beschreibung
- Neuer Menüpunkt "Produktionen" in Genossenschaft
- Nutzt Genossenschafts-Lager als Input/Output
- Gleiche Logik wie normale Produktionen

### Betroffene Dateien
- `app/models/CooperativeProduction.php` - Neues Model
- `app/controllers/CooperativeController.php`
- `app/views/cooperative/productions.php` - Neue View
- `app/views/cooperative/show.php` - Neuer Menüpunkt

---

## 10. RANGLISTEN - Online-Status & Erweiterte Stats

### 10.1 Online-Status
- 🟢 Grün: Gerade online (aktive Session)
- 🟡 Gelb: In letzten 24h online
- 🔴 Rot: Länger als 7 Tage nicht online

### 10.2 Erweiterte Statistiken
- Anzahl Tiere
- Anzahl Fahrzeuge
- Anzahl Felder
- Anzahl Produktionen
- Gesamtwert des Inventars

### Datenbank-Änderungen
```sql
ALTER TABLE users ADD COLUMN last_activity TIMESTAMP NULL;
-- Oder nutze last_login Feld

-- View für erweiterte Stats
CREATE VIEW ranking_extended AS
SELECT
    f.id AS farm_id,
    f.farm_name,
    u.username,
    u.last_login,
    f.money,
    f.points,
    f.level,
    (SELECT COUNT(*) FROM farm_animals WHERE farm_id = f.id) AS animal_count,
    (SELECT COUNT(*) FROM farm_vehicles WHERE farm_id = f.id) AS vehicle_count,
    (SELECT COUNT(*) FROM fields WHERE farm_id = f.id) AS field_count,
    (SELECT COUNT(*) FROM farm_productions WHERE farm_id = f.id) AS production_count
FROM farms f
JOIN users u ON f.user_id = u.id;
```

### Betroffene Dateien
- `app/models/Ranking.php`
- `app/views/rankings/index.php`
- Session-Management für "gerade online"

---

## Implementierungs-Reihenfolge

### Phase A: Grundlegende Verbesserungen
1. ✅ Salespoints Countdown + Suche
2. ✅ Shop Countdown + Suche
3. ✅ Rankings Online-Status + erweiterte Stats

### Phase B: Produktionssystem
4. Kontinuierliche Produktion
5. Effizienz-System für partielle Inputs
6. Tierkapazität durch Ställe

### Phase C: Forschung
7. Tiere einzeln erforschbar
8. Feldfrüchte einzeln erforschbar

### Phase D: Marktplatz & Community
9. Marktplatz Push-Funktion
10. Genossenschaft Pinnwand

### Phase E: Genossenschaft Erweitert
11. Fahrzeuge ausleihen
12. Genossenschafts-Produktionen

---

## Geschätzte SQL-Migrationen

1. `sql/v1.2_continuous_production.sql`
2. `sql/v1.2_animal_capacity.sql`
3. `sql/v1.2_research_animals.sql`
4. `sql/v1.2_research_crops.sql`
5. `sql/v1.2_market_push.sql`
6. `sql/v1.2_coop_board.sql`
7. `sql/v1.2_coop_vehicles.sql`
8. `sql/v1.2_coop_productions.sql`
9. `sql/v1.2_rankings_extended.sql`
