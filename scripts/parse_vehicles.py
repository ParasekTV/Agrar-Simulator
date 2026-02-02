#!/usr/bin/env python3
"""
Fahrzeughaendler Parser
Generiert SQL-Migrationen fuer Fahrzeuge, Marken und den Fahrzeughaendler.
Liest Marken aus dem brands_index.json.
"""

import json
import os
from datetime import datetime

# Pfad zur brands_index.json
BRANDS_INDEX_PATH = "../public/img/brands/brands_index.json"

# ============================================
# MARKEN-NAMEN (Lesbare Namen fuer die Marken)
# ============================================

BRAND_NAMES = {
    # Fahrzeuge
    "abi": "ABI",
    "agco": "AGCO",
    "antoniocarraro": "Antonio Carraro",
    "ape": "Piaggio Ape",
    "aprilia": "Aprilia",
    "caseih": "Case IH",
    "challenger": "Challenger",
    "claas": "CLAAS",
    "deutzfahr": "Deutz-Fahr",
    "fendt": "Fendt",
    "fiat": "Fiat",
    "impex": "Impex",
    "international": "International",
    "iseki": "Iseki",
    "jcb": "JCB",
    "johndeere": "John Deere",
    "komatsu": "Komatsu",
    "kubota": "Kubota",
    "landini": "Landini",
    "lindner": "Lindner",
    "lizard": "Lizard",
    "mack": "Mack",
    "manitou": "Manitou",
    "masseyferguson": "Massey Ferguson",
    "mccormick": "McCormick",
    "merlo": "Merlo",
    "newholland": "New Holland",
    "pfanzelt": "Pfanzelt",
    "ponsse": "Ponsse",
    "prinoth": "Prinoth",
    "riedler": "Riedler",
    "rigitrac": "Rigitrac",
    "ropa": "ROPA",
    "rottne": "Rottne",
    "same": "SAME",
    "schaeffer": "Schaeffer",
    "sennebogen": "Sennebogen",
    "skoda": "Skoda",
    "steyr": "Steyr",
    "valtra": "Valtra",
    "volvo": "Volvo",
    "zetor": "Zetor",

    # Erntemaschinen
    "agrifac": "Agrifac",
    "amitytech": "Amity Technology",
    "capello": "Capello",
    "dewulf": "Dewulf",
    "ero": "ERO",
    "geringhoff": "Geringhoff",
    "gregoire": "Gregoire",
    "grimme": "Grimme",
    "holmer": "Holmer",
    "kemper": "Kemper",
    "krone": "Krone",
    "lacotec": "Lacotec",
    "macdon": "MacDon",
    "oxbo": "Oxbo",

    # Geraete
    "agibatco": "AGI Batco",
    "agistorm": "AGI Storm",
    "agiwestfield": "AGI Westfield",
    "agrio": "Agrio",
    "agrisem": "Agrisem",
    "agromasz": "Agromasz",
    "albutt": "Albutt",
    "alpego": "Alpego",
    "amazone": "Amazone",
    "andersongroup": "Anderson Group",
    "annaburger": "Annaburger",
    "arcusin": "Arcusin",
    "bednar": "Bednar",
    "bergmann": "Bergmann",
    "berthoud": "Berthoud",
    "bomech": "Bomech",
    "brandt": "Brandt",
    "brantner": "Brantner",
    "bredal": "Bredal",
    "conveyall": "Conveyall",
    "dalbo": "Dal-Bo",
    "damcon": "Damcon",
    "demco": "Demco",
    "einboeck": "Einboeck",
    "elho": "Elho",
    "elmersmfg": "Elmers Manufacturing",
    "faresin": "Faresin",
    "farmax": "Farmax",
    "farmet": "Farmet",
    "farmtech": "Farmtech",
    "fliegl": "Fliegl",
    "fuhrmann": "Fuhrmann",
    "gessner": "Gessner",
    "gorenc": "Gorenc",
    "goeweil": "Goeweil",
    "greatplains": "Great Plains",
    "hardi": "Hardi",
    "hauer": "Hauer",
    "hawe": "Hawe",
    "heizomat": "Heizomat",
    "holaras": "Holaras",
    "horsch": "Horsch",
    "jenz": "Jenz",
    "jmmanufacturing": "JM Manufacturing",
    "jungheinrich": "Jungheinrich",
    "kaweco": "Kaweco",
    "kesla": "Kesla",
    "kingston": "Kingston",
    "kinze": "Kinze",
    "knoche": "Knoche",
    "kockerling": "Koeckerling",
    "koller": "Koller",
    "kotte": "Kotte",
    "krampe": "Krampe",
    "kroeger": "Kroeger",
    "kronetrailer": "Krone Trailer",
    "kuhn": "Kuhn",
    "kverneland": "Kverneland",
    "lemken": "Lemken",
    "lodeking": "Lode King",
    "magsi": "Magsi",
    "mccormack": "McCormack",
    "meridian": "Meridian",
    "mzuri": "Mzuri",
    "nardi": "Nardi",
    "novag": "Novag",
    "paladin": "Paladin",
    "pittstrailers": "Pitts Trailers",
    "poettinger": "Poettinger",
    "provitis": "Provitis",
    "quicke": "Quicke",
    "reiter": "Reiter",
    "risutec": "Risutec",
    "rudolph": "Rudolph",
    "salek": "Salek",
    "samasz": "SaMASZ",
    "samsonagro": "Samson Agro",
    "schuitemaker": "Schuitemaker",
    "schwarzmueller": "Schwarzmueller",
    "siloking": "Siloking",
    "sip": "SIP",
    "stema": "Stema",
    "streumaster": "Streumaster",
    "summersmfg": "Summers Manufacturing",
    "tajfun": "Tajfun",
    "tenwinkel": "Tenwinkel",
    "thundercreek": "Thunder Creek",
    "tmccancela": "TMC Cancela",
    "treffler": "Treffler",
    "tt": "TT",
    "unia": "Unia",
    "vaederstad": "Vaederstad",
    "vermeer": "Vermeer",
    "walkabout": "Walkabout",
    "westtech": "Westtech",
    "wifo": "Wifo",
    "wilson": "Wilson",
    "zunhammer": "Zunhammer",

    # Diverses
    "agi": "AGI",
    "agisentinel": "AGI Sentinel",
    "agiwesteel": "AGI Westeel",
    "bkt": "BKT",
    "brielmaier": "Brielmaier",
    "continental": "Continental",
    "corteva": "Corteva",
    "easysheds": "Easy Sheds",
    "elten": "Elten",
    "engelbertstrauss": "Engelbert Strauss",
    "groha": "Groha",
    "hella": "Hella",
    "helm": "Helm",
    "rudolfhoermann": "Rudolf Hoermann",
    "husqvarna": "Husqvarna",
    "idagro": "Idagro",
    "jonsered": "Jonsered",
    "kaercher": "Kaercher",
    "lely": "Lely",
    "lincoln": "Lincoln",
    "mcculloch": "McCulloch",
    "michelin": "Michelin",
    "mitas": "Mitas",
    "moescha": "Moescha",
    "neuero": "Neuero",
    "nokiantyres": "Nokian Tyres",
    "pesslinstruments": "Pessl Instruments",
    "pioneer": "Pioneer",
    "planet": "Planet",
    "raniplast": "Raniplast",
    "stihl": "Stihl",
    "trelleborg": "Trelleborg",
    "vredestein": "Vredestein",
    "walterscheid": "Walterscheid",
}

# ============================================
# FAHRZEUG-TYPEN PRO KATEGORIE
# ============================================

VEHICLE_TYPES = {
    "fahrzeuge": [
        {"type": "traktor_klein", "name": "Traktor (Klein)", "base_price": 35000, "power": 80, "speed": 40},
        {"type": "traktor_mittel", "name": "Traktor (Mittel)", "base_price": 85000, "power": 150, "speed": 50},
        {"type": "traktor_gross", "name": "Traktor (Gross)", "base_price": 180000, "power": 300, "speed": 50},
        {"type": "traktor_schwer", "name": "Traktor (Schwer)", "base_price": 350000, "power": 500, "speed": 45},
        {"type": "radlader", "name": "Radlader", "base_price": 120000, "power": 200, "speed": 35},
        {"type": "teleskoplader", "name": "Teleskoplader", "base_price": 95000, "power": 150, "speed": 40},
    ],
    "erntemaschinen": [
        {"type": "maehdrescher_klein", "name": "Maehdrescher (Klein)", "base_price": 250000, "power": 300, "speed": 25},
        {"type": "maehdrescher_gross", "name": "Maehdrescher (Gross)", "base_price": 450000, "power": 500, "speed": 30},
        {"type": "feldhacker", "name": "Feldhacksler", "base_price": 380000, "power": 600, "speed": 40},
        {"type": "kartoffelroder", "name": "Kartoffelroder", "base_price": 280000, "power": 400, "speed": 20},
        {"type": "rubenroder", "name": "Ruebenroder", "base_price": 520000, "power": 700, "speed": 20},
        {"type": "traubenvollernter", "name": "Traubenvollernter", "base_price": 320000, "power": 250, "speed": 25},
    ],
    "geraete": [
        {"type": "pflug", "name": "Pflug", "base_price": 25000, "power": 0, "speed": 0},
        {"type": "grubber", "name": "Grubber", "base_price": 18000, "power": 0, "speed": 0},
        {"type": "saemaschine", "name": "Saemaschine", "base_price": 45000, "power": 0, "speed": 0},
        {"type": "duengerstreuer", "name": "Duengerstreuer", "base_price": 35000, "power": 0, "speed": 0},
        {"type": "feldspritze", "name": "Feldspritze", "base_price": 55000, "power": 0, "speed": 0},
        {"type": "anhaenger", "name": "Anhaenger", "base_price": 28000, "power": 0, "speed": 0},
        {"type": "maeher", "name": "Maeher", "base_price": 22000, "power": 0, "speed": 0},
        {"type": "schwader", "name": "Schwader", "base_price": 32000, "power": 0, "speed": 0},
        {"type": "ballenpresse", "name": "Ballenpresse", "base_price": 85000, "power": 0, "speed": 0},
        {"type": "ladewagen", "name": "Ladewagen", "base_price": 65000, "power": 0, "speed": 0},
    ],
    "diverses": [
        {"type": "zubehoer", "name": "Zubehoer", "base_price": 5000, "power": 0, "speed": 0},
        {"type": "werkzeug", "name": "Werkzeug", "base_price": 2500, "power": 0, "speed": 0},
    ]
}

# Marken-Preismodifikatoren (Premium-Marken kosten mehr)
BRAND_PRICE_MODIFIERS = {
    "johndeere": 1.3,
    "fendt": 1.35,
    "claas": 1.25,
    "caseih": 1.2,
    "newholland": 1.15,
    "masseyferguson": 1.1,
    "deutzfahr": 1.1,
    "kubota": 1.15,
    "valtra": 1.1,
    "steyr": 1.05,
    "krone": 1.2,
    "grimme": 1.25,
    "horsch": 1.2,
    "lemken": 1.15,
    "kuhn": 1.1,
    "amazone": 1.1,
    "poettinger": 1.15,
    "kverneland": 1.1,
    "lizard": 0.7,  # Budget-Marke
}


def load_brands():
    """Laedt Marken aus der brands_index.json"""

    with open(BRANDS_INDEX_PATH, "r", encoding="utf-8") as f:
        data = json.load(f)

    return data["categories"]


def get_unique_brands(categories):
    """Extrahiert einzigartige Marken aus allen Kategorien"""

    unique = set()
    for category, brands in categories.items():
        for brand in brands:
            unique.add(brand)

    return sorted(unique)


def get_brand_categories(brand, categories):
    """Gibt alle Kategorien zurueck, in denen eine Marke vorkommt"""

    result = []
    for category, brands in categories.items():
        if brand in brands:
            result.append(category)
    return result


def generate_sql(categories):
    """Generiert die SQL-Migration"""

    sql_lines = []
    sql_lines.append("-- ============================================")
    sql_lines.append("-- Fahrzeughaendler - Automatisch generiert")
    sql_lines.append(f"-- Generiert am: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("SET NAMES utf8mb4;")
    sql_lines.append("")
    sql_lines.append("-- Foreign Key Checks deaktivieren")
    sql_lines.append("SET FOREIGN_KEY_CHECKS = 0;")
    sql_lines.append("")

    unique_brands = get_unique_brands(categories)

    # ============================================
    # 0. ALTE TABELLEN LOESCHEN (falls vorhanden)
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- ALTE TABELLEN LOESCHEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("DROP TABLE IF EXISTS farm_brand_unlocks;")
    sql_lines.append("DROP TABLE IF EXISTS farm_vehicles;")
    sql_lines.append("DROP TABLE IF EXISTS vehicles;")
    sql_lines.append("DROP TABLE IF EXISTS vehicle_dealers;")
    sql_lines.append("DROP TABLE IF EXISTS vehicle_brands;")
    sql_lines.append("")

    # ============================================
    # 1. MARKEN TABELLE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- MARKEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE vehicle_brands (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    brand_key VARCHAR(50) NOT NULL UNIQUE,")
    sql_lines.append("    name VARCHAR(100) NOT NULL,")
    sql_lines.append("    logo_url VARCHAR(255),")
    sql_lines.append("    description TEXT,")
    sql_lines.append("    country VARCHAR(50),")
    sql_lines.append("    price_modifier DECIMAL(3,2) DEFAULT 1.00,")
    sql_lines.append("    required_research_id INT DEFAULT NULL,")
    sql_lines.append("    is_active BOOLEAN DEFAULT TRUE,")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # ============================================
    # 2. FAHRZEUGE TABELLE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- FAHRZEUGE")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE vehicles (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    brand_id INT NOT NULL,")
    sql_lines.append("    name VARCHAR(150) NOT NULL,")
    sql_lines.append("    vehicle_type VARCHAR(50) NOT NULL,")
    sql_lines.append("    category ENUM('fahrzeuge', 'erntemaschinen', 'geraete', 'diverses') NOT NULL,")
    sql_lines.append("    price DECIMAL(12,2) NOT NULL,")
    sql_lines.append("    power_hp INT DEFAULT 0,")
    sql_lines.append("    max_speed INT DEFAULT 0,")
    sql_lines.append("    fuel_consumption DECIMAL(5,2) DEFAULT 0,")
    sql_lines.append("    maintenance_cost DECIMAL(10,2) DEFAULT 0,")
    sql_lines.append("    work_width DECIMAL(5,2) DEFAULT 0,")
    sql_lines.append("    capacity INT DEFAULT 0,")
    sql_lines.append("    required_level INT DEFAULT 1,")
    sql_lines.append("    image_url VARCHAR(255),")
    sql_lines.append("    description TEXT,")
    sql_lines.append("    is_active BOOLEAN DEFAULT TRUE,")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    FOREIGN KEY (brand_id) REFERENCES vehicle_brands(id) ON DELETE CASCADE")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # ============================================
    # 3. SPIELER-FAHRZEUGE TABELLE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- SPIELER-FAHRZEUGE (Besitz)")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE farm_vehicles (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    farm_id INT NOT NULL,")
    sql_lines.append("    vehicle_id INT NOT NULL,")
    sql_lines.append("    custom_name VARCHAR(100),")
    sql_lines.append("    condition_percent INT DEFAULT 100,")
    sql_lines.append("    operating_hours INT DEFAULT 0,")
    sql_lines.append("    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    last_maintenance TIMESTAMP NULL,")
    sql_lines.append("    is_for_sale BOOLEAN DEFAULT FALSE,")
    sql_lines.append("    sale_price DECIMAL(12,2) DEFAULT NULL,")
    sql_lines.append("    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # ============================================
    # 4. FAHRZEUGHAENDLER TABELLE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- FAHRZEUGHAENDLER")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE vehicle_dealers (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    name VARCHAR(100) NOT NULL,")
    sql_lines.append("    description TEXT,")
    sql_lines.append("    specialization ENUM('fahrzeuge', 'erntemaschinen', 'geraete', 'diverses', 'alle') DEFAULT 'alle',")
    sql_lines.append("    discount_percent DECIMAL(4,2) DEFAULT 0,")
    sql_lines.append("    is_active BOOLEAN DEFAULT TRUE,")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # Haendler einfuegen
    sql_lines.append("-- Haendler einfuegen")
    sql_lines.append("INSERT INTO vehicle_dealers (name, description, specialization) VALUES")
    sql_lines.append("('Traktorenhaus Mueller', 'Ihr Spezialist fuer Traktoren und Zugmaschinen aller Art.', 'fahrzeuge'),")
    sql_lines.append("('Erntecenter Schmidt', 'Maehdrescher, Haecksler und Erntetechnik vom Feinsten.', 'erntemaschinen'),")
    sql_lines.append("('Landmaschinen Weber', 'Geraete und Anbauteile fuer jeden Bedarf.', 'geraete'),")
    sql_lines.append("('Agrar-Markt Zentrale', 'Das Komplettangebot - alle Marken unter einem Dach.', 'alle');")
    sql_lines.append("")

    # ============================================
    # 5. MARKEN-FORSCHUNG (research_tree erweitern)
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- FORSCHUNG FUER MARKEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")

    # Erweitere ENUM fuer Marken-Forschung
    sql_lines.append("-- Erweitere research_tree Kategorien um 'brands'")
    sql_lines.append("ALTER TABLE research_tree MODIFY COLUMN category ENUM('crops', 'animals', 'vehicles', 'buildings', 'efficiency', 'production', 'brands') NOT NULL;")
    sql_lines.append("")

    # Forschungs-IDs beginnen bei 300
    research_id = 300

    # Sortiere Marken nach "Wichtigkeit" (Premium zuerst, dann alphabetisch)
    premium_brands = ["johndeere", "fendt", "claas", "caseih", "newholland", "masseyferguson",
                      "deutzfahr", "kubota", "valtra", "steyr", "krone", "grimme"]

    sorted_brands = []
    for brand in premium_brands:
        if brand in unique_brands:
            sorted_brands.append(brand)

    for brand in unique_brands:
        if brand not in sorted_brands:
            sorted_brands.append(brand)

    # Marken einfuegen mit Forschungs-IDs
    sql_lines.append("-- Marken einfuegen")
    for i, brand_key in enumerate(sorted_brands):
        brand_name = BRAND_NAMES.get(brand_key, brand_key.title())
        brand_name_safe = brand_name.replace("'", "''")
        price_mod = BRAND_PRICE_MODIFIERS.get(brand_key, 1.0)

        # Bestimme Kategorie fuer Logo-Pfad
        brand_cats = get_brand_categories(brand_key, categories)
        logo_category = brand_cats[0] if brand_cats else "fahrzeuge"
        logo_url = f"/img/brands/{logo_category}/{brand_key}.webp"

        current_research_id = research_id + i

        sql_lines.append(f"INSERT INTO vehicle_brands (brand_key, name, logo_url, price_modifier, required_research_id) VALUES ('{brand_key}', '{brand_name_safe}', '{logo_url}', {price_mod}, {current_research_id});")

    sql_lines.append("")

    # Forschungseintraege fuer Marken
    sql_lines.append("-- Forschungseintraege fuer Marken")
    for i, brand_key in enumerate(sorted_brands):
        brand_name = BRAND_NAMES.get(brand_key, brand_key.title())
        brand_name_safe = brand_name.replace("'", "''")

        current_research_id = research_id + i

        # Bestimme Level-Anforderung basierend auf Preismodifikator
        price_mod = BRAND_PRICE_MODIFIERS.get(brand_key, 1.0)
        if price_mod >= 1.3:
            req_level = 15
            research_cost = 25000
            research_time = 8
        elif price_mod >= 1.2:
            req_level = 10
            research_cost = 15000
            research_time = 6
        elif price_mod >= 1.1:
            req_level = 5
            research_cost = 8000
            research_time = 4
        elif price_mod < 0.9:
            req_level = 1
            research_cost = 1000
            research_time = 1
        else:
            req_level = 3
            research_cost = 5000
            research_time = 2

        sql_lines.append(f"INSERT IGNORE INTO research_tree (id, name, description, cost, research_time_hours, level_required, category) VALUES ({current_research_id}, 'Marke: {brand_name_safe}', 'Schaltet Fahrzeuge der Marke {brand_name_safe} frei.', {research_cost}, {research_time}, {req_level}, 'brands');")

    sql_lines.append("")

    # ============================================
    # 6. FAHRZEUGE EINFUEGEN
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- FAHRZEUGE EINFUEGEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")

    vehicle_count = 0
    for brand_key in sorted_brands:
        brand_name = BRAND_NAMES.get(brand_key, brand_key.title())
        brand_name_safe = brand_name.replace("'", "''")
        brand_cats = get_brand_categories(brand_key, categories)
        price_mod = BRAND_PRICE_MODIFIERS.get(brand_key, 1.0)

        for category in brand_cats:
            if category not in VEHICLE_TYPES:
                continue

            for vtype in VEHICLE_TYPES[category]:
                vehicle_name = f"{brand_name} {vtype['name']}"
                vehicle_name_safe = vehicle_name.replace("'", "''")

                # Preis berechnen
                price = int(vtype["base_price"] * price_mod)

                # Wartungskosten (0.5% des Preises pro Stunde)
                maintenance = price * 0.005

                # Level-Anforderung basierend auf Preis
                req_level = max(1, min(50, price // 20000))

                # Kraftstoffverbrauch basierend auf PS
                fuel = vtype["power"] * 0.05 if vtype["power"] > 0 else 0

                sql_lines.append(f"INSERT INTO vehicles (brand_id, name, vehicle_type, category, price, power_hp, max_speed, fuel_consumption, maintenance_cost, required_level) SELECT id, '{vehicle_name_safe}', '{vtype['type']}', '{category}', {price}, {vtype['power']}, {vtype['speed']}, {fuel:.2f}, {maintenance:.2f}, {req_level} FROM vehicle_brands WHERE brand_key = '{brand_key}';")
                vehicle_count += 1

    sql_lines.append("")
    sql_lines.append(f"-- Insgesamt {vehicle_count} Fahrzeuge eingefuegt")
    sql_lines.append("")

    # ============================================
    # 7. SPIELER-MARKEN FREISCHALTUNG
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- SPIELER-MARKEN FREISCHALTUNG")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE farm_brand_unlocks (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    farm_id INT NOT NULL,")
    sql_lines.append("    brand_id INT NOT NULL,")
    sql_lines.append("    unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (brand_id) REFERENCES vehicle_brands(id) ON DELETE CASCADE,")
    sql_lines.append("    UNIQUE KEY unique_farm_brand (farm_id, brand_id)")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # Budget-Marke (Lizard) fuer alle freischalten
    sql_lines.append("-- Lizard ist die Standard-Marke (fuer alle freigeschaltet)")
    sql_lines.append("-- Dies muss im Spiel-Code gehandhabt werden")
    sql_lines.append("")

    sql_lines.append("-- Foreign Key Checks wieder aktivieren")
    sql_lines.append("SET FOREIGN_KEY_CHECKS = 1;")
    sql_lines.append("")
    sql_lines.append("-- Ende der Migration")

    return "\n".join(sql_lines), len(sorted_brands), vehicle_count


def generate_json(categories):
    """Generiert JSON-Export der Fahrzeugdaten"""

    unique_brands = get_unique_brands(categories)

    brands_data = []
    for brand_key in unique_brands:
        brand_cats = get_brand_categories(brand_key, categories)
        brands_data.append({
            "key": brand_key,
            "name": BRAND_NAMES.get(brand_key, brand_key.title()),
            "categories": brand_cats,
            "price_modifier": BRAND_PRICE_MODIFIERS.get(brand_key, 1.0)
        })

    data = {
        "generated_at": datetime.now().isoformat(),
        "brands": brands_data,
        "vehicle_types": VEHICLE_TYPES,
        "statistics": {
            "total_brands": len(unique_brands),
            "brands_per_category": {cat: len(brands) for cat, brands in categories.items()}
        }
    }

    return json.dumps(data, ensure_ascii=False, indent=2)


def main():
    """Hauptfunktion"""

    print("=" * 60)
    print("Fahrzeughaendler-Parser fuer Farming Simulator Browsergame")
    print("=" * 60)
    print()

    # Marken laden
    print("Lade Marken aus brands_index.json...")
    categories = load_brands()

    unique_brands = get_unique_brands(categories)

    # Statistiken ausgeben
    print(f"Gefundene einzigartige Marken: {len(unique_brands)}")
    for cat, brands in categories.items():
        print(f"  - {cat}: {len(brands)} Marken")
    print()

    # SQL generieren
    print("Generiere SQL-Migration...")
    sql_content, brand_count, vehicle_count = generate_sql(categories)

    sql_path = "../sql/vehicles_migration.sql"
    with open(sql_path, "w", encoding="utf-8", newline='\n') as f:
        f.write(sql_content)
    print(f"SQL gespeichert: {sql_path}")
    print(f"  - {brand_count} Marken")
    print(f"  - {vehicle_count} Fahrzeuge")

    # JSON generieren
    print("Generiere JSON-Export...")
    json_content = generate_json(categories)

    json_path = "../sql/vehicles_data.json"
    with open(json_path, "w", encoding="utf-8") as f:
        f.write(json_content)
    print(f"JSON gespeichert: {json_path}")

    print()
    print("=" * 60)
    print("Fertig!")
    print("=" * 60)


if __name__ == "__main__":
    main()
