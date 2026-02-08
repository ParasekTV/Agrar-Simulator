# Agrar Simulator Browsergame - Entwicklungsfortschritt

## Status: AKTIV - Kontinuierliche Erweiterung

**Letztes Update:** 06.02.2026
**Waehrung:** Agrar Taler (T)
**Live-URL:** https://agrar.lsbg.eu

---

## AKTUELLER STAND (fuer spaetere Fortsetzung)

### Zuletzt erledigt (08.02.2026):
- [x] **Fahrzeuge an Genossenschaft verleihen** (v1.2 Feature - Phase E):
  - Fahrzeuge fuer X Stunden an Genossenschaft verleihen
  - Stundengebuehr optional
  - Andere Mitglieder koennen verliehene Fahrzeuge ausleihen
  - Automatische Gebuehrenabrechnung bei Rueckgabe
  - `sql/v1.2_coop_vehicles.sql` Migration

- [x] **Genossenschafts-Produktionen** (v1.2 Feature - Phase E):
  - Genossenschaft kann Produktionen kaufen
  - Nutzt Genossenschaftskasse fuer Kauf
  - Produktionen starten/stoppen
  - Produktions-Logs
  - `sql/v1.2_coop_productions.sql` Migration

- [x] **Marktplatz Push-Funktion** (v1.2 Feature - Phase D):
  - 3 Push-Optionen: Standard (24h/500T), Premium (48h/1200T), Super (72h/2500T)
  - Gepushte Angebote erscheinen ganz oben
  - Push-Historie
  - `sql/v1.2_market_push.sql` Migration

- [x] **Genossenschafts-Pinnwand** (v1.2 Feature - Phase D):
  - Internes Forum nur fuer Genossenschaftsmitglieder
  - Beitraege erstellen, lesen, loeschen
  - Kommentare hinzufuegen und loeschen
  - Like-System fuer Beitraege
  - Ankuendigungen und angepinnte Beitraege (nur Leader)
  - Ungelesene Beitraege zaehlen
  - `sql/v1.2_coop_board.sql` Migration
  - Neues Model: `CooperativePost.php`
  - Neue Views: `cooperative/board.php`, `cooperative/post.php`

- [x] **Individuelle Tier-Forschung** (v1.2 Feature - Phase C):
  - 8 neue Forschungen: Ziegenhaltung, Entenhaltung, Gaensehaltung, Pferdezucht, Imkerei, Bueffelzucht, Kaninchenzucht, Putenhaltung
  - 8 neue Tierarten: Ziege, Ente, Gans, Pferd, Bienenvolk, Wasserbueffel, Kaninchen, Truthahn
  - 8 neue Staelle/Produktionen fuer neue Tierarten
  - Forschungsbaum mit Abhaengigkeiten (z.B. Gaense benoetigt Enten)
  - `sql/v1.2_research_animals.sql` Migration

- [x] **Individuelle Feldfrucht-Forschung** (v1.2 Feature - Phase C):
  - 6 neue Forschungen: Rapsanbau, Sojabohnenanbau, Weinbau, Obstplantagen, Gewaechshaus-Kulturen, Kraeuteranbau
  - 11 neue Feldfruechte: Raps, Sojabohnen, Weintrauben, Aepfel, Birnen, Tomaten, Paprika, Gurken, Basilikum, Thymian, Rosmarin
  - Neue Produktionen: Weinkellerei, Saftpresse, Gewaechshaus, Kraeutergarten
  - `sql/v1.2_research_crops.sql` Migration

- [x] **Tierkapazitaet durch Staelle** (v1.2 Feature):
  - Tierhaltungs-Produktionen definieren Tierkapazitaet
  - Ohne Stall: 0 Tiere dieser Art moeglich
  - Kapazitaetsuebersicht auf Tiere-Seite
  - Kaufen-Modal mit Kapazitaetspruefung
  - `sql/v1.2_animal_capacity.sql` Migration
  - Animal Model: getAnimalCapacity(), getCapacityOverview(), getAvailableAnimalsWithCapacity()

- [x] **Rankings Online-Status & Erweiterte Stats** (v1.2 Feature):
  - Online-Status-Anzeige (gruen=online, gelb=24h, orange=7d, grau=offline)
  - Erweiterte Statistiken in Rangliste (Tiere, Fahrzeuge, Felder, Produktionen)
  - `last_activity` Tracking in users-Tabelle
  - Session-basiertes Activity-Tracking (alle 5 Min)
  - CSS-Animationen fuer Online-Status
  - `sql/v1.2_rankings_extended.sql` Migration

- [x] **Salespoints Countdown + Suche** (v1.2 Feature):
  - Countdown bis Mitternacht wenn neue Preise gelten
  - Produktsuche: "Wer kauft X?" mit Preisvergleich
  - Alle Verkaufsstellen sortiert nach bestem Preis
  - Neue View: `salespoints/search.php`
  - API-Endpunkte: `/salespoint/search`, `/salespoint/price-change-time`

- [x] **Shop Countdown + Suche** (v1.2 Feature):
  - Countdown bis Mitternacht wenn neue Preise gelten
  - Produktsuche: "Wer verkauft X?" mit Preisvergleich
  - Alle Haendler sortiert nach guenstigstem Preis
  - Neue View: `shop/search.php`
  - API-Endpunkte: `/shop/search`, `/shop/price-change-time`

- [x] **Kontinuierliche Produktion** (v1.2 Feature):
  - Produktionen laufen dauerhaft bis manuell gestoppt
  - Automatischer Stopp wenn Rohstoffe aufgebraucht
  - Effizienz-System: 0-100% basierend auf verfuegbaren Inputs
  - Produktions-Historie mit detaillierten Logs
  - Cron-Job `cron/production_cycle.php` (alle 5 Min)
  - Neue DB-Felder: is_running, started_at, cycles_completed, current_efficiency
  - `sql/v1.2_continuous_production.sql` Migration
  - Neue Views: `productions/logs.php`, aktualisierte `productions/index.php`
  - API-Endpunkte: `/production/start-continuous`, `/production/stop-continuous`, `/production/logs`

### Vorher erledigt (06.02.2026):
- [x] **Shop/Einkauf-System** komplett implementiert:
  - 5 Haendler: Landhandel, Bauernmarkt, Technik-Partner, Grosshandel, Bio-Laden
  - Tagesbasierte Preise mit Seed-Algorithmus
  - Preistrend-Anzeige (steigend/fallend/stabil)
  - Preisvergleich ueber alle Haendler
  - Einkaufshistorie
  - Model, Controller, 4 Views, API-Endpunkte
- [x] **Wasser- und Strom-Produktionen**:
  - Wasserwerk (150.000 T, 10x Wasser, Level 3)
  - Kraftwerk (250.000 T, 10x Strom, Level 5)
  - Brunnen (50.000 T, 4x Wasser, Level 1)
  - Solaranlage (100.000 T, 5x Strom, Level 2)
  - Windkraftanlage (180.000 T, 8x Strom, Level 4)
  - Zugehoerige Forschungen im Forschungsbaum
- [x] **Spielregeln-Seite** erstellt:
  - Respektvoller Umgang, Hassrede-Verbot
  - Keine Politik/Religion in Spielinhalten
  - Faires Spielen, Ein-Account-Regel
  - Konsequenzen bei Verstoessen
- [x] **Admin manuelle Benutzer-Verifizierung**:
  - Button in Benutzer-Bearbeitung
  - Direktes Verifizieren ohne E-Mail
- [x] **Umlaut-Fixes**:
  - Doppelt-kodierte UTF-8 Zeichen in allen Tabellen behoben
  - ASCII-Ersatzzeichen in Challenges durch echte Umlaute ersetzt
- [x] **Bugfix: Registrierung** - Falscher Spaltenname (cost statt price) korrigiert

### Noch beim User hochzuladen/auszufuehren:
1. `app/models/User.php` - Registrierungs-Bugfix
2. `app/models/Shop.php` - Neues Model
3. `app/controllers/ShopController.php` - Neuer Controller
4. `app/controllers/AdminController.php` - Verifizierungs-Fix
5. `app/views/shop/*` - 4 neue Views
6. `app/views/pages/spielregeln.php` - Neue Seite
7. SQL-Migrationen:
   - `sql/shop_migration.sql`
   - `sql/water_electricity_migration.sql`
   - `sql/energy_water_expansion.sql`
   - `sql/fix_all_umlauts.sql`
   - `sql/fix_challenge_umlauts.sql`

### Naechste geplante Schritte:
1. Testen aller neuen Features
2. Phase 2: Erweiterte Tierhaltung umsetzen
3. Phase 3: Echte Traktor-Marken einbauen

---

## Abgeschlossene Komponenten

### 1. Datenbankstruktur (100%)
- [x] `sql/install.sql` - Komplettes Datenbankschema
  - 13+ Haupttabellen mit is_admin Feld
  - Foreign Keys und Indizes
  - Initiale Daten (Crops, Animals, Vehicles, Research, Buildings, Challenges)
- [x] `sql/update_admin.sql` - Migration fuer Admin-Funktionalitaet
- [x] `sql/phase1_crops.sql` - Phase 1 Migration (18 Fruechte, pH-System, Duenger, Kalk)
- [x] `sql/shop_migration.sql` - **NEU:** Shop/Einkauf-System (Haendler, Produkte, Historie)
- [x] `sql/water_electricity_migration.sql` - **NEU:** Wasserwerk & Kraftwerk
- [x] `sql/energy_water_expansion.sql` - **NEU:** Brunnen, Solaranlage, Windkraftanlage
- [x] `sql/fix_all_umlauts.sql` - **NEU:** Umlaut-Korrektur fuer alle Tabellen
- [x] `sql/fix_challenge_umlauts.sql` - **NEU:** Umlaut-Korrektur fuer Challenges

### 2. Konfiguration (100%)
- [x] `config/config.php` - Hauptkonfiguration
- [x] `config/database.php` - Datenbankverbindung

### 3. Core-Klassen (100%)
- [x] `app/core/Database.php` - PDO Wrapper (Singleton)
- [x] `app/core/Session.php` - Session-Management mit CSRF
- [x] `app/core/Router.php` - URL-Routing
- [x] `app/core/Validator.php` - Input-Validierung
- [x] `app/core/Logger.php` - Logging-System
- [x] `app/core/Controller.php` - Basis-Controller

### 4. Models (100%)
- [x] `app/models/User.php` - Benutzer & Auth
- [x] `app/models/Farm.php` - Farm-Hauptlogik
- [x] `app/models/Field.php` - Felder & Anbau
- [x] `app/models/Animal.php` - Tierverwaltung
- [x] `app/models/Vehicle.php` - Fahrzeuge
- [x] `app/models/Research.php` - Forschungsbaum
- [x] `app/models/Market.php` - Marktplatz
- [x] `app/models/SalesPoint.php` - Verkaufsstellen
- [x] `app/models/Shop.php` - **NEU:** Einkauf/Haendler
- [x] `app/models/Cooperative.php` - Genossenschaften
- [x] `app/models/News.php` - Forum/Zeitung
- [x] `app/models/Ranking.php` - Ranglisten

### 5. Controllers (100%)
- [x] `app/controllers/AuthController.php`
- [x] `app/controllers/FarmController.php`
- [x] `app/controllers/FieldController.php`
- [x] `app/controllers/AnimalController.php`
- [x] `app/controllers/VehicleController.php`
- [x] `app/controllers/ResearchController.php`
- [x] `app/controllers/MarketController.php`
- [x] `app/controllers/SalesPointController.php` - Verkaufsstellen
- [x] `app/controllers/ShopController.php` - **NEU:** Einkauf/Haendler
- [x] `app/controllers/CooperativeController.php`
- [x] `app/controllers/NewsController.php`
- [x] `app/controllers/RankingController.php`
- [x] `app/controllers/PageController.php` - **NEU:** Statische Seiten (Spielregeln, etc.)
- [x] `app/controllers/AdminController.php` - Admin-Panel (mit manueller Verifizierung)

### 6. Views (100%)

**Layouts:**
- [x] `app/views/layouts/main.php` - Haupt-Layout
- [x] `app/views/layouts/navigation.php` - Navigation (mit Admin-Link)
- [x] `app/views/layouts/footer.php` - Footer

**Auth:**
- [x] `app/views/auth/login.php` - Login-Seite
- [x] `app/views/auth/register.php` - Registrierung

**Dashboard:**
- [x] `app/views/dashboard.php` - Dashboard

**Felder:**
- [x] `app/views/fields/index.php` - Felder-Uebersicht
- [x] `app/views/fields/show.php` - Feld-Detailansicht

**Tiere:**
- [x] `app/views/animals/index.php` - Tiere-Uebersicht

**Fahrzeuge:**
- [x] `app/views/vehicles/index.php` - Fahrzeuge-Uebersicht

**Forschung:**
- [x] `app/views/research/index.php` - Forschungsbaum

**Marktplatz:**
- [x] `app/views/market/index.php` - Marktplatz
- [x] `app/views/market/history.php` - Handelshistorie

**Farm:**
- [x] `app/views/farm/overview.php` - Farm-Uebersicht
- [x] `app/views/farm/inventory.php` - Inventar
- [x] `app/views/farm/events.php` - Ereignis-Timeline

**Genossenschaften:**
- [x] `app/views/cooperative/index.php` - Genossenschafts-Uebersicht
- [x] `app/views/cooperative/show.php` - Genossenschafts-Details

**Forum/News:**
- [x] `app/views/news/index.php` - Forum-Uebersicht
- [x] `app/views/news/show.php` - Beitrags-Detailansicht
- [x] `app/views/news/create.php` - Beitrag erstellen
- [x] `app/views/news/search.php` - Forum-Suche

**Ranglisten:**
- [x] `app/views/rankings/index.php` - Spieler-Rangliste
- [x] `app/views/rankings/cooperatives.php` - Genossenschafts-Rangliste
- [x] `app/views/rankings/challenges.php` - Woechentliche Herausforderungen

**Verkauf:**
- [x] `app/views/salespoints/index.php` - Verkaufsstellen-Uebersicht
- [x] `app/views/salespoints/show.php` - Verkaufsstelle Details
- [x] `app/views/salespoints/history.php` - Verkaufshistorie
- [x] `app/views/salespoints/compare.php` - Preisvergleich

**Einkauf/Shop:** (NEU)
- [x] `app/views/shop/index.php` - Haendler-Uebersicht
- [x] `app/views/shop/show.php` - Haendler Details
- [x] `app/views/shop/history.php` - Einkaufshistorie
- [x] `app/views/shop/compare.php` - Preisvergleich

**Statische Seiten:** (NEU)
- [x] `app/views/pages/spielregeln.php` - Spielregeln
- [x] `app/views/pages/impressum.php` - Impressum
- [x] `app/views/pages/datenschutz.php` - Datenschutz

**Admin-Panel:**
- [x] `app/views/admin/index.php` - Admin Dashboard
- [x] `app/views/admin/users.php` - Benutzer-Liste
- [x] `app/views/admin/user_edit.php` - Benutzer bearbeiten
- [x] `app/views/admin/farms.php` - Hoefe-Liste
- [x] `app/views/admin/farm_edit.php` - Hof bearbeiten
- [x] `app/views/admin/cooperatives.php` - Genossenschaften-Liste
- [x] `app/views/admin/cooperative_edit.php` - Genossenschaft bearbeiten

**Fehlerseiten:**
- [x] `app/views/errors/404.php` - 404-Fehlerseite

### 7. Public Assets (100%)
- [x] `public/index.php` - Entry Point mit Routing
- [x] `public/.htaccess` - URL Rewriting
- [x] `public/css/main.css` - Hauptstile
- [x] `public/css/farm.css` - Farm-spezifische Stile
- [x] `public/css/responsive.css` - Responsive Design
- [x] `public/js/app.js` - Haupt-JavaScript
- [x] `public/js/timers.js` - Timer-System
- [x] `public/js/farm.js` - Farm-Interaktionen

### 8. Cron Jobs (100%)
- [x] `cron/harvest_check.php` - Ernte-Pruefung
- [x] `cron/research_check.php` - Forschungs-Pruefung
- [x] `cron/animal_check.php` - Tier-Status
- [x] `cron/rankings_update.php` - Ranglisten-Update

---

## Admin-Panel Funktionen

Das Admin-Panel ermoeglicht vollstaendige Verwaltung:

### Benutzer-Verwaltung
- Alle Benutzer anzeigen mit Suche
- Benutzername, E-Mail, Passwort aendern
- Aktiv/Inaktiv Status setzen
- **Manuelle Verifizierung** - Benutzer ohne E-Mail-Bestaetigung freischalten
- Admin-Rechte vergeben/entziehen
- Farm-Daten bearbeiten (Geld, Punkte, Level)
- Benutzer loeschen

### Hoefe-Verwaltung
- Alle Hoefe mit Statistiken
- Farm-Name, Geld, Punkte, Level bearbeiten
- Felder, Tiere, Fahrzeuge einsehen

### Genossenschaften-Verwaltung
- Alle Genossenschaften verwalten
- Name, Beschreibung, Kasse, Level bearbeiten
- Mitglieder-Limit anpassen
- Mitglieder entfernen
- Genossenschaft loeschen

---

## Geplante Erweiterungen (Zukunft)

### Phase 1: Erweiterte Feldfruchte ✅ ABGESCHLOSSEN
- [x] 18 neue Feldfruechte (Hafer, Roggen, Dinkel, Klee, Luzerne, Gras, Hopfen, Tabak, Baumwolle, Hanf, Flachs, Zwiebeln, Karotten, Kohl, Spinat, Sellerie, Erdbeeren)
- [x] Feldfruechte in 5 Forschungs-Tiers organisiert
- [x] 8 neue Forschungsknoten (Erweiterte Getreide, Futterpflanzen, Industriepflanzen, Gemuesebau, Obstanbau, Erweiterte Duengung, Praezisionslandwirtschaft, Bodenkunde)
- [x] Verschiedene Anbauzeiten und Ertraege pro Frucht
- [x] pH-System: Boden-pH beeinflusst Ertrag (optimal/akzeptabel/schlecht)
- [x] 4 Duengertypen: Basis, NPK (+15% Ertrag), Bio (kein Qualitaetsverlust), Fluessig (+25% Ertrag)
- [x] 3 Kalktypen: Kohlensaurer Kalk, Branntkalk, Dolomitkalk
- [x] UI erweitert: pH-Anzeige, kategorisierte Pflanzen, Duenger/Kalk-Dropdowns
- [x] Migration: `sql/phase1_crops.sql`

### Phase 2: Erweiterte Tierhaltung
- [ ] Viel mehr Tierarten (Gaense, Enten, Ziegen, Pferde, Bienen, etc.)
- [ ] Tiere muessen erforscht werden
- [ ] Staelle fuer verschiedene Tierarten (erforschbar)
- [ ] Weiden fuer Weidetiere (erforschbar)
- [ ] Tierversorgung: Futter, Wasser, Stroh
- [ ] Mist- und Guelle-Management
  - Lagerung in Silos/Gruben
  - Als Duenger auf Feldern nutzbar
  - Auf Marktplatz verkaufbar

### Phase 3: Echte Traktor-Marken
- [ ] Bekannte Hersteller (John Deere, Fendt, Case IH, New Holland, Claas, etc.)
- [ ] Realistische Traktor-Modelle
- [ ] Traktoren muessen erforscht werden
- [ ] Unterschiedliche Leistungswerte und Preise
- [ ] Anbaugeraete passend zu Traktoren

### Phase 4: Hof-Gebaeude
- [ ] Erforschbare Gebaeude
- [ ] Scheunen, Silos, Garagen
- [ ] Lagerhallen fuer Inventar-Erweiterung
- [ ] Wohnhaus-Upgrades

### Phase 5: Produktionen ✅ TEILWEISE ABGESCHLOSSEN
- [x] 71+ Produktionsstaetten bereits implementiert
- [x] Wasserversorgung: Brunnen, Wasserwerk
- [x] Stromproduktion: Solaranlage, Windkraftanlage, Kraftwerk
- [x] Baeckerei, Molkerei, Oelmuehle, Brauerei etc. bereits vorhanden

### Phase 6: Erweiterte Wirtschaft ✅ TEILWEISE ABGESCHLOSSEN
- [x] 10 Verkaufsstellen mit unterschiedlichen Preisen
- [x] 5 Haendler/Einkaufsstellen (Landhandel, Bauernmarkt, Technik-Partner, Grosshandel, Bio-Laden)
- [x] Tagesbasierte Preisschwankungen bei Kauf und Verkauf
- [x] Preistrend-Anzeige (steigend/fallend/stabil)
- [x] Preisvergleich ueber alle Haendler/Verkaufsstellen
- [ ] Vertraege mit Abnehmern
- [ ] Lieferauftraege

### v1.2 Feature-Paket (IN ARBEIT)
Siehe `docs/feature_plan_v1.2.md` fuer detaillierten Plan.

**Phase A: Grundlegende Verbesserungen** - ABGESCHLOSSEN
- [x] Salespoints Countdown + Suche
- [x] Shop Countdown + Suche
- [x] Rankings Online-Status + erweiterte Stats

**Phase B: Produktionssystem** - ABGESCHLOSSEN
- [x] Kontinuierliche Produktion (laeuft bis gestoppt/Rohstoffe leer)
- [x] Effizienz-System fuer partielle Inputs
- [x] Tierkapazitaet durch Staelle

**Phase C: Forschung** - ABGESCHLOSSEN
- [x] Tiere einzeln erforschbar (8 neue Tierforschungen)
- [x] Feldfruechte einzeln erforschbar (6 neue Pflanzenforschungen)

**Phase D: Marktplatz & Community** - ABGESCHLOSSEN
- [x] Marktplatz Push-Funktion (Angebot hervorheben)
- [x] Genossenschaft Pinnwand (internes Forum)

**Phase E: Genossenschaft Erweitert** - ABGESCHLOSSEN
- [x] Fahrzeuge an Genossenschaft ausleihen
- [x] Genossenschafts-Produktionen (nutzt Coop-Lager)

### Phase 7: Infrastruktur & Ressourcen ✅ ABGESCHLOSSEN
- [x] Wasserproduktion: Brunnen (Level 1), Wasserwerk (Level 3)
- [x] Stromproduktion: Solaranlage (Level 2), Windkraftanlage (Level 4), Kraftwerk (Level 5)
- [x] Forschungsbaum fuer Infrastruktur-Gebaeude
- [x] Produkte: Wasser und Strom fuer Produktionen

---

## Installation

### 1. Datenbank erstellen:
```sql
CREATE DATABASE agrar_simulator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'agrar_user'@'localhost' IDENTIFIED BY 'dein_passwort';
GRANT ALL PRIVILEGES ON agrar_simulator.* TO 'agrar_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Schema importieren:
```bash
mysql -u agrar_user -p agrar_simulator < sql/install.sql
```

### 3. Admin-Benutzer erstellen:
Nach der Registrierung in der Datenbank:
```sql
UPDATE users SET is_admin = TRUE WHERE username = 'dein_benutzername';
```

### 4. Konfiguration anpassen:
- `config/database.php` - Datenbankzugangsdaten eintragen
- `config/config.php` - BASE_URL anpassen

### 5. Webserver konfigurieren:
- DocumentRoot auf `public/` setzen
- mod_rewrite aktivieren (Apache) oder entsprechende Nginx-Konfiguration

### 6. Cron Jobs einrichten:
```
*/5 * * * * php /pfad/zu/agrar-simulator/cron/harvest_check.php
*/5 * * * * php /pfad/zu/agrar-simulator/cron/research_check.php
0 */6 * * * php /pfad/zu/agrar-simulator/cron/animal_check.php
0 * * * * php /pfad/zu/agrar-simulator/cron/rankings_update.php
```

---

## Technologie-Stack

- **Backend:** PHP 8.x, MVC-Architektur
- **Datenbank:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Sicherheit:** PDO, bcrypt, CSRF-Tokens
- **Responsive:** Mobile-first Design
- **Waehrung:** Agrar Taler (T)

---

**Das Spiel ist vollstaendig implementiert und wird kontinuierlich erweitert!**

---

## Changelog

### Version 1.2.0 (08.02.2026) - FERTIGGESTELLT
- Rankings: Online-Status-Anzeige (gruen/gelb/orange/grau)
- Rankings: Erweiterte Statistiken (Tiere, Fahrzeuge, Felder, Produktionen)
- Activity-Tracking fuer Online-Status
- Salespoints: Countdown bis Preisaenderung + Produktsuche
- Shop: Countdown bis Preisaenderung + Produktsuche
- Produktionen: Kontinuierlicher Modus mit Effizienz-System
- Produktionen: Automatisches Stoppen bei Rohstoffmangel
- Produktionen: Produktions-Historie mit detaillierten Logs
- Tierkapazitaet durch Staelle (ohne Stall keine Tiere)
- 8 neue Tierarten mit individueller Forschung (Ziege, Ente, Gans, Pferd, Bienen, Bueffel, Kaninchen, Pute)
- 11 neue Feldfruechte mit individueller Forschung (Raps, Soja, Weintrauben, Aepfel, Birnen, Tomaten, Paprika, Gurken, Kraeuter)
- Marktplatz Push-Funktion (Angebote hervorheben fuer 24-72h)
- Genossenschafts-Pinnwand (internes Forum mit Kommentaren und Likes)
- Fahrzeuge an Genossenschaft verleihen
- Genossenschafts-Produktionen
- v1.2 Feature-Paket VOLLSTAENDIG abgeschlossen (Phase A-E)

### Version 1.1.0 (06.02.2026)
- Shop/Einkauf-System mit 5 Haendlern
- Wasser- und Stromproduktion (Brunnen, Solar, Wind, Wasserwerk, Kraftwerk)
- Spielregeln-Seite
- Admin manuelle Benutzer-Verifizierung
- Umlaut-Fixes fuer alle Tabellen
- Bugfix: Registrierung funktioniert wieder

### Version 1.0.0 (02.02.2026)
- Initiales Release mit allen Kernfunktionen
