#!/usr/bin/env python3
"""
Herausforderungen Parser
Generiert SQL-Migrationen fuer woechentliche und monatliche Herausforderungen.
"""

import json
from datetime import datetime

# ============================================
# WOECHENTLICHE HERAUSFORDERUNGEN (10-20)
# ============================================

WEEKLY_CHALLENGES = [
    # Ernte-Herausforderungen
    {
        "name": "Erntekoenig",
        "description": "Ernte 1.000 Einheiten beliebiger Feldfruechte",
        "type": "production",
        "target": 1000,
        "reward_points": 150,
        "reward_money": 500
    },
    {
        "name": "Getreideexperte",
        "description": "Ernte 500 Einheiten Weizen",
        "type": "production",
        "target": 500,
        "reward_points": 100,
        "reward_money": 300
    },
    {
        "name": "Maismeister",
        "description": "Ernte 400 Einheiten Mais",
        "type": "production",
        "target": 400,
        "reward_points": 100,
        "reward_money": 300
    },
    {
        "name": "Kartoffelbauer",
        "description": "Ernte 600 Einheiten Kartoffeln",
        "type": "production",
        "target": 600,
        "reward_points": 120,
        "reward_money": 400
    },

    # Verkaufs-Herausforderungen
    {
        "name": "Marktmeister",
        "description": "Verkaufe Waren im Wert von 5.000 Talern",
        "type": "sales",
        "target": 5000,
        "reward_points": 200,
        "reward_money": 750
    },
    {
        "name": "Handelsprofi",
        "description": "Verkaufe 20 verschiedene Produkte auf dem Markt",
        "type": "sales",
        "target": 20,
        "reward_points": 180,
        "reward_money": 600
    },
    {
        "name": "Schnellverkaeufer",
        "description": "Verkaufe 50 Einheiten innerhalb von 24 Stunden",
        "type": "sales",
        "target": 50,
        "reward_points": 150,
        "reward_money": 400
    },

    # Forschungs-Herausforderungen
    {
        "name": "Forscher",
        "description": "Schliesse 2 Forschungen ab",
        "type": "research",
        "target": 2,
        "reward_points": 250,
        "reward_money": 1000
    },
    {
        "name": "Wissensdurst",
        "description": "Starte 3 neue Forschungsprojekte",
        "type": "research",
        "target": 3,
        "reward_points": 200,
        "reward_money": 800
    },

    # Tier-Herausforderungen
    {
        "name": "Milchbauer",
        "description": "Produziere 200 Liter Milch",
        "type": "production",
        "target": 200,
        "reward_points": 130,
        "reward_money": 450
    },
    {
        "name": "Eiersammler",
        "description": "Sammle 100 Eier",
        "type": "production",
        "target": 100,
        "reward_points": 100,
        "reward_money": 300
    },
    {
        "name": "Wollproduzent",
        "description": "Produziere 50 Einheiten Wolle",
        "type": "production",
        "target": 50,
        "reward_points": 120,
        "reward_money": 400
    },

    # Genossenschafts-Herausforderungen
    {
        "name": "Teamplayer",
        "description": "Hilf 3 Genossenschaftsmitgliedern",
        "type": "cooperative",
        "target": 3,
        "reward_points": 180,
        "reward_money": 500
    },
    {
        "name": "Gemeinschaftsgeist",
        "description": "Spende 1.000 Taler an die Genossenschaftskasse",
        "type": "cooperative",
        "target": 1000,
        "reward_points": 200,
        "reward_money": 0
    },

    # Produktions-Herausforderungen
    {
        "name": "Baecker",
        "description": "Produziere 30 Einheiten Brot",
        "type": "production",
        "target": 30,
        "reward_points": 140,
        "reward_money": 500
    },
    {
        "name": "Kaesemeister",
        "description": "Produziere 20 Einheiten Kaese",
        "type": "production",
        "target": 20,
        "reward_points": 160,
        "reward_money": 600
    },

    # Aktivitaets-Herausforderungen
    {
        "name": "Fleissiger Bauer",
        "description": "Logge dich 5 Tage hintereinander ein",
        "type": "activity",
        "target": 5,
        "reward_points": 100,
        "reward_money": 250
    },
    {
        "name": "Feldarbeiter",
        "description": "Bearbeite 10 Felder",
        "type": "activity",
        "target": 10,
        "reward_points": 120,
        "reward_money": 350
    },
]

# ============================================
# MONATLICHE HERAUSFORDERUNGEN (10-30)
# ============================================

MONTHLY_CHALLENGES = [
    # Grosse Ernte-Herausforderungen
    {
        "name": "Erntegigant",
        "description": "Ernte 10.000 Einheiten beliebiger Feldfruechte",
        "type": "production",
        "target": 10000,
        "reward_points": 1000,
        "reward_money": 5000
    },
    {
        "name": "Weizenkoenig",
        "description": "Ernte 5.000 Einheiten Weizen",
        "type": "production",
        "target": 5000,
        "reward_points": 800,
        "reward_money": 4000
    },
    {
        "name": "Vielfaltbauer",
        "description": "Ernte mindestens 10 verschiedene Feldfruchtsorten",
        "type": "production",
        "target": 10,
        "reward_points": 600,
        "reward_money": 3000
    },
    {
        "name": "Zuckermagnat",
        "description": "Ernte 3.000 Einheiten Zuckerrueben",
        "type": "production",
        "target": 3000,
        "reward_points": 700,
        "reward_money": 3500
    },
    {
        "name": "Rapsbaron",
        "description": "Ernte 2.500 Einheiten Raps",
        "type": "production",
        "target": 2500,
        "reward_points": 650,
        "reward_money": 3200
    },

    # Grosse Verkaufs-Herausforderungen
    {
        "name": "Handelsimperium",
        "description": "Verkaufe Waren im Wert von 50.000 Talern",
        "type": "sales",
        "target": 50000,
        "reward_points": 1500,
        "reward_money": 10000
    },
    {
        "name": "Marktdominanz",
        "description": "Verkaufe 500 Einheiten auf dem Spielermarkt",
        "type": "sales",
        "target": 500,
        "reward_points": 1000,
        "reward_money": 6000
    },
    {
        "name": "Exportmeister",
        "description": "Verkaufe an 5 verschiedene Verkaufsstellen",
        "type": "sales",
        "target": 5,
        "reward_points": 500,
        "reward_money": 2500
    },
    {
        "name": "Gewinnmaximierer",
        "description": "Erziele einen Gewinn von 25.000 Talern",
        "type": "sales",
        "target": 25000,
        "reward_points": 1200,
        "reward_money": 7500
    },

    # Grosse Forschungs-Herausforderungen
    {
        "name": "Wissenschaftler",
        "description": "Schliesse 8 Forschungen ab",
        "type": "research",
        "target": 8,
        "reward_points": 1500,
        "reward_money": 8000
    },
    {
        "name": "Technologiepionier",
        "description": "Erreiche Forschungslevel 10",
        "type": "research",
        "target": 10,
        "reward_points": 2000,
        "reward_money": 12000
    },
    {
        "name": "Innovator",
        "description": "Schalte 5 neue Produktionen frei",
        "type": "research",
        "target": 5,
        "reward_points": 1000,
        "reward_money": 5000
    },

    # Grosse Tier-Herausforderungen
    {
        "name": "Milchimperium",
        "description": "Produziere 2.000 Liter Milch",
        "type": "production",
        "target": 2000,
        "reward_points": 900,
        "reward_money": 4500
    },
    {
        "name": "Gefluegelbaron",
        "description": "Sammle 1.000 Eier",
        "type": "production",
        "target": 1000,
        "reward_points": 700,
        "reward_money": 3500
    },
    {
        "name": "Viehzuechter",
        "description": "Besitze insgesamt 50 Tiere",
        "type": "activity",
        "target": 50,
        "reward_points": 800,
        "reward_money": 4000
    },
    {
        "name": "Honigproduzent",
        "description": "Produziere 100 Einheiten Honig",
        "type": "production",
        "target": 100,
        "reward_points": 600,
        "reward_money": 3000
    },

    # Grosse Genossenschafts-Herausforderungen
    {
        "name": "Genossenschaftsheld",
        "description": "Hilf 20 Genossenschaftsmitgliedern",
        "type": "cooperative",
        "target": 20,
        "reward_points": 1200,
        "reward_money": 6000
    },
    {
        "name": "Grossspender",
        "description": "Spende 10.000 Taler an die Genossenschaftskasse",
        "type": "cooperative",
        "target": 10000,
        "reward_points": 1500,
        "reward_money": 0
    },
    {
        "name": "Genossenschaftsgruender",
        "description": "Werbe 3 neue Mitglieder fuer die Genossenschaft",
        "type": "cooperative",
        "target": 3,
        "reward_points": 1000,
        "reward_money": 5000
    },

    # Grosse Produktions-Herausforderungen
    {
        "name": "Industriebaron",
        "description": "Betreibe 5 Produktionsstaetten gleichzeitig",
        "type": "production",
        "target": 5,
        "reward_points": 1000,
        "reward_money": 5000
    },
    {
        "name": "Brauermeister",
        "description": "Produziere 200 Einheiten Bier",
        "type": "production",
        "target": 200,
        "reward_points": 800,
        "reward_money": 4000
    },
    {
        "name": "Backwarenkoenig",
        "description": "Produziere 300 Einheiten Backwaren",
        "type": "production",
        "target": 300,
        "reward_points": 750,
        "reward_money": 3800
    },
    {
        "name": "Oelmagnat",
        "description": "Produziere 150 Einheiten Oel (Raps-, Sonnenblumen- oder Olivenoel)",
        "type": "production",
        "target": 150,
        "reward_points": 850,
        "reward_money": 4200
    },
    {
        "name": "Fleischproduzent",
        "description": "Produziere 100 Einheiten Fleisch",
        "type": "production",
        "target": 100,
        "reward_points": 900,
        "reward_money": 4500
    },

    # Grosse Aktivitaets-Herausforderungen
    {
        "name": "Dauerbrenner",
        "description": "Logge dich 25 Tage im Monat ein",
        "type": "activity",
        "target": 25,
        "reward_points": 800,
        "reward_money": 4000
    },
    {
        "name": "Grossgrundbesitzer",
        "description": "Besitze 20 Felder",
        "type": "activity",
        "target": 20,
        "reward_points": 1000,
        "reward_money": 5000
    },
    {
        "name": "Leveljaeger",
        "description": "Steige 3 Level auf",
        "type": "activity",
        "target": 3,
        "reward_points": 1500,
        "reward_money": 7500
    },
    {
        "name": "Punktesammler",
        "description": "Sammle 5.000 Punkte",
        "type": "activity",
        "target": 5000,
        "reward_points": 1000,
        "reward_money": 5000
    },
    {
        "name": "Vermoegensaufbau",
        "description": "Erreiche ein Vermoegen von 100.000 Talern",
        "type": "activity",
        "target": 100000,
        "reward_points": 2000,
        "reward_money": 10000
    },
]


def generate_sql():
    """Generiert die SQL-Migration"""

    sql_lines = []
    sql_lines.append("-- ============================================")
    sql_lines.append("-- Herausforderungen - Automatisch generiert")
    sql_lines.append(f"-- Generiert am: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("SET NAMES utf8mb4;")
    sql_lines.append("")

    # ============================================
    # 1. TABELLEN ERWEITERN/ERSTELLEN
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- TABELLEN ANPASSEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")

    # Erweitere weekly_challenges um reward_money und challenge_period
    sql_lines.append("-- Erweitere weekly_challenges Tabelle")
    sql_lines.append("ALTER TABLE weekly_challenges ADD COLUMN IF NOT EXISTS reward_money DECIMAL(10,2) DEFAULT 0;")
    sql_lines.append("ALTER TABLE weekly_challenges ADD COLUMN IF NOT EXISTS challenge_period ENUM('weekly', 'monthly') DEFAULT 'weekly';")
    sql_lines.append("")

    # Erweitere challenge_type ENUM um 'activity'
    sql_lines.append("-- Erweitere challenge_type ENUM")
    sql_lines.append("ALTER TABLE weekly_challenges MODIFY COLUMN challenge_type ENUM('sales', 'production', 'research', 'cooperative', 'activity') NOT NULL;")
    sql_lines.append("")

    # ============================================
    # 2. CHALLENGE TEMPLATES TABELLE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- CHALLENGE TEMPLATES (Vorlagen)")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS challenge_templates (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    name VARCHAR(100) NOT NULL,")
    sql_lines.append("    description TEXT,")
    sql_lines.append("    challenge_type ENUM('sales', 'production', 'research', 'cooperative', 'activity') NOT NULL,")
    sql_lines.append("    challenge_period ENUM('weekly', 'monthly') NOT NULL,")
    sql_lines.append("    target_value INT NOT NULL,")
    sql_lines.append("    reward_points INT DEFAULT 100,")
    sql_lines.append("    reward_money DECIMAL(10,2) DEFAULT 0,")
    sql_lines.append("    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',")
    sql_lines.append("    is_active BOOLEAN DEFAULT TRUE,")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # ============================================
    # 3. WOECHENTLICHE HERAUSFORDERUNGEN EINFUEGEN
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- WOECHENTLICHE HERAUSFORDERUNGEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")

    for challenge in WEEKLY_CHALLENGES:
        safe_name = challenge["name"].replace("'", "''")
        safe_desc = challenge["description"].replace("'", "''")

        # Schwierigkeit basierend auf target und reward
        if challenge["reward_points"] <= 120:
            difficulty = "easy"
        elif challenge["reward_points"] <= 180:
            difficulty = "medium"
        else:
            difficulty = "hard"

        sql_lines.append(f"INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('{safe_name}', '{safe_desc}', '{challenge['type']}', 'weekly', {challenge['target']}, {challenge['reward_points']}, {challenge['reward_money']}, '{difficulty}');")

    sql_lines.append("")

    # ============================================
    # 4. MONATLICHE HERAUSFORDERUNGEN EINFUEGEN
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- MONATLICHE HERAUSFORDERUNGEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")

    for challenge in MONTHLY_CHALLENGES:
        safe_name = challenge["name"].replace("'", "''")
        safe_desc = challenge["description"].replace("'", "''")

        # Schwierigkeit basierend auf target und reward
        if challenge["reward_points"] <= 700:
            difficulty = "easy"
        elif challenge["reward_points"] <= 1200:
            difficulty = "medium"
        else:
            difficulty = "hard"

        sql_lines.append(f"INSERT INTO challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_points, reward_money, difficulty) VALUES ('{safe_name}', '{safe_desc}', '{challenge['type']}', 'monthly', {challenge['target']}, {challenge['reward_points']}, {challenge['reward_money']}, '{difficulty}');")

    sql_lines.append("")

    # ============================================
    # 5. AKTIVE HERAUSFORDERUNGEN ERSTELLEN
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- AKTIVE HERAUSFORDERUNGEN ERSTELLEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("-- Loesche alte Herausforderungen")
    sql_lines.append("DELETE FROM weekly_challenges WHERE active = TRUE;")
    sql_lines.append("")

    sql_lines.append("-- Erstelle 5 zufaellige woechentliche Herausforderungen")
    sql_lines.append("INSERT INTO weekly_challenges (challenge_name, description, challenge_type, target_value, reward_points, reward_money, challenge_period, start_date, end_date, active)")
    sql_lines.append("SELECT name, description, challenge_type, target_value, reward_points, reward_money, 'weekly',")
    sql_lines.append("       CURDATE(),")
    sql_lines.append("       DATE_ADD(CURDATE(), INTERVAL 7 DAY),")
    sql_lines.append("       TRUE")
    sql_lines.append("FROM challenge_templates")
    sql_lines.append("WHERE challenge_period = 'weekly' AND is_active = TRUE")
    sql_lines.append("ORDER BY RAND()")
    sql_lines.append("LIMIT 5;")
    sql_lines.append("")

    sql_lines.append("-- Erstelle 3 zufaellige monatliche Herausforderungen")
    sql_lines.append("INSERT INTO weekly_challenges (challenge_name, description, challenge_type, target_value, reward_points, reward_money, challenge_period, start_date, end_date, active)")
    sql_lines.append("SELECT name, description, challenge_type, target_value, reward_points, reward_money, 'monthly',")
    sql_lines.append("       DATE_FORMAT(CURDATE(), '%Y-%m-01'),")
    sql_lines.append("       LAST_DAY(CURDATE()),")
    sql_lines.append("       TRUE")
    sql_lines.append("FROM challenge_templates")
    sql_lines.append("WHERE challenge_period = 'monthly' AND is_active = TRUE")
    sql_lines.append("ORDER BY RAND()")
    sql_lines.append("LIMIT 3;")
    sql_lines.append("")

    sql_lines.append("-- Ende der Migration")

    return "\n".join(sql_lines)


def generate_json():
    """Generiert JSON-Export der Herausforderungen"""

    data = {
        "generated_at": datetime.now().isoformat(),
        "weekly_challenges": WEEKLY_CHALLENGES,
        "monthly_challenges": MONTHLY_CHALLENGES,
        "statistics": {
            "total_weekly": len(WEEKLY_CHALLENGES),
            "total_monthly": len(MONTHLY_CHALLENGES),
            "weekly_by_type": {},
            "monthly_by_type": {}
        }
    }

    # Statistiken berechnen
    for c in WEEKLY_CHALLENGES:
        t = c["type"]
        data["statistics"]["weekly_by_type"][t] = data["statistics"]["weekly_by_type"].get(t, 0) + 1

    for c in MONTHLY_CHALLENGES:
        t = c["type"]
        data["statistics"]["monthly_by_type"][t] = data["statistics"]["monthly_by_type"].get(t, 0) + 1

    return json.dumps(data, ensure_ascii=False, indent=2)


def main():
    """Hauptfunktion"""

    print("=" * 60)
    print("Herausforderungen-Parser fuer Farming Simulator Browsergame")
    print("=" * 60)
    print()

    # Statistiken ausgeben
    print(f"Woechentliche Herausforderungen: {len(WEEKLY_CHALLENGES)}")
    print(f"Monatliche Herausforderungen: {len(MONTHLY_CHALLENGES)}")
    print(f"Gesamt: {len(WEEKLY_CHALLENGES) + len(MONTHLY_CHALLENGES)}")
    print()

    # SQL generieren
    print("Generiere SQL-Migration...")
    sql_content = generate_sql()

    sql_path = "../sql/challenges_migration.sql"
    with open(sql_path, "w", encoding="utf-8", newline='\n') as f:
        f.write(sql_content)
    print(f"SQL gespeichert: {sql_path}")

    # JSON generieren
    print("Generiere JSON-Export...")
    json_content = generate_json()

    json_path = "../sql/challenges_data.json"
    with open(json_path, "w", encoding="utf-8") as f:
        f.write(json_content)
    print(f"JSON gespeichert: {json_path}")

    print()
    print("=" * 60)
    print("Fertig!")
    print("=" * 60)


if __name__ == "__main__":
    main()
