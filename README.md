# Agrar Simulator

Ein umfangreiches Browser-basiertes Landwirtschafts-Simulationsspiel, entwickelt mit PHP 8 und MySQL.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

## Features

### Farming & Produktion
- **Felder bewirtschaften** - Kaufe Felder, pflanze verschiedene Feldfruchte und ernte sie
- **Tiere halten** - Kuehe, Schweine, Huehner und Schafe mit Produkten wie Milch, Eier und Wolle
- **Fahrzeuge nutzen** - Traktoren, Maehdrescher und Transportfahrzeuge

### Wirtschaft & Handel
- **Marktplatz** - Handel mit anderen Spielern in Echtzeit
- **Dynamische Preise** - Angebot und Nachfrage beeinflussen die Preise
- **NPC-Verkauf** - Verkaufe direkt an das System zu festen Preisen

### Forschung & Fortschritt
- **Forschungsbaum** - Schalte neue Technologien und Boni frei
- **Level-System** - Sammle Punkte und steige im Level auf
- **Taeglicher Login-Bonus** - Belohnungen fuer regelmaessiges Spielen

### Community
- **Genossenschaften** - Gruende oder tritt einer Genossenschaft bei
- **Ausruestung teilen** - Verleihe Fahrzeuge an Genossenschaftsmitglieder
- **Forum** - Diskutiere mit anderen Spielern

### Wettbewerb
- **Ranglisten** - Globale Spieler- und Genossenschafts-Rankings
- **Woechentliche Herausforderungen** - Spezielle Aufgaben mit Bonuspunkten

## Screenshots

*Screenshots folgen*

## Technologie-Stack

- **Backend:** PHP 8.x mit MVC-Architektur
- **Datenbank:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Sicherheit:** CSRF-Schutz, bcrypt Passwort-Hashing, Prepared Statements

## Installation

### Voraussetzungen

- PHP 8.0 oder hoeher
- MySQL 5.7 / MariaDB 10.3 oder hoeher
- Apache mit mod_rewrite oder Nginx
- Composer (optional)

### Schnellstart

1. **Repository klonen:**
```bash
git clone https://github.com/dein-username/agrar-simulator.git
cd agrar-simulator
```

2. **Datenbank erstellen:**
```sql
CREATE DATABASE agrar_simulator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'agrar_user'@'localhost' IDENTIFIED BY 'dein_sicheres_passwort';
GRANT ALL PRIVILEGES ON agrar_simulator.* TO 'agrar_user'@'localhost';
FLUSH PRIVILEGES;
```

3. **Schema importieren:**
```bash
mysql -u agrar_user -p agrar_simulator < sql/install.sql
```

4. **Konfiguration anpassen:**

Bearbeite `config/database.php`:
```php
return [
    'host'     => 'localhost',
    'database' => 'agrar_simulator',
    'username' => 'agrar_user',
    'password' => 'dein_sicheres_passwort',
    'charset'  => 'utf8mb4',
];
```

Bearbeite `config/config.php`:
```php
define('BASE_URL', '');  // Leer lassen wenn im Root
define('DEBUG_MODE', false);  // In Produktion auf false
```

5. **Webserver konfigurieren:**

**Apache:** Setze das Document Root auf den `public/` Ordner.

**Nginx:**
```nginx
server {
    root /var/www/agrar-simulator/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

6. **Cron Jobs einrichten:**
```cron
*/5 * * * * php /var/www/agrar-simulator/cron/harvest_check.php
*/5 * * * * php /var/www/agrar-simulator/cron/research_check.php
0 */6 * * * php /var/www/agrar-simulator/cron/animal_check.php
0 * * * * php /var/www/agrar-simulator/cron/rankings_update.php
```

## Projektstruktur

```
agrar-simulator/
├── app/
│   ├── controllers/     # Controller-Klassen
│   ├── core/           # Kernklassen (Database, Router, Session)
│   ├── models/         # Model-Klassen
│   └── views/          # PHP-Templates
├── config/
│   ├── config.php      # Hauptkonfiguration
│   └── database.php    # Datenbankverbindung
├── cron/               # Cron-Job-Skripte
├── logs/               # Log-Dateien
├── public/             # Document Root
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript
│   ├── images/        # Bilder
│   └── index.php      # Entry Point
└── sql/
    └── install.sql    # Datenbank-Schema
```

## API-Endpunkte

Das Spiel bietet eine REST-API fuer AJAX-Interaktionen:

| Endpunkt | Methode | Beschreibung |
|----------|---------|--------------|
| `/api/farm/stats` | GET | Farm-Statistiken |
| `/api/farm/fields` | GET | Alle Felder |
| `/api/field/plant` | POST | Feld bepflanzen |
| `/api/field/harvest` | POST | Feld ernten |
| `/api/market/listings` | GET | Marktangebote |
| `/api/research/tree` | GET | Forschungsbaum |

## Sicherheit

- **CSRF-Token:** Alle Formulare sind mit CSRF-Tokens geschuetzt
- **Prepared Statements:** Schutz vor SQL-Injection
- **Password Hashing:** bcrypt mit Kostenfaktor 12
- **Input Validation:** Serverseitige Validierung aller Eingaben
- **XSS-Schutz:** HTML-Escaping aller Ausgaben

## Mitwirken

Beitraege sind willkommen! Bitte erstelle einen Fork und reiche einen Pull Request ein.

1. Fork das Repository
2. Erstelle einen Feature-Branch (`git checkout -b feature/NeuesFeature`)
3. Committe deine Aenderungen (`git commit -m 'Neues Feature hinzugefuegt'`)
4. Push zum Branch (`git push origin feature/NeuesFeature`)
5. Erstelle einen Pull Request

## Lizenz

Dieses Projekt ist unter der MIT-Lizenz lizenziert. Siehe [LICENSE](LICENSE) fuer Details.

## Autor

Entwickelt mit Claude Code

---

**Live Demo:** [agrar.sl-wide.de](https://agrar.sl-wide.de)
