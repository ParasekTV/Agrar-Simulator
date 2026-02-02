#!/usr/bin/env python3
"""
Agrargenossenschaft Erweiterung Parser
Generiert SQL-Migrationen für erweiterte Genossenschafts-Funktionen.
"""

import json
from datetime import datetime

# ============================================
# ROLLEN-SYSTEM
# ============================================

COOPERATIVE_ROLES = [
    {
        "key": "founder",
        "name": "Gründer",
        "description": "Vollzugriff auf alle Funktionen der Genossenschaft",
        "permissions": ["all"],
        "is_transferable": False,
        "max_per_coop": 1
    },
    {
        "key": "admin",
        "name": "Administrator",
        "description": "Kann Mitglieder verwalten und Bewerbungen bearbeiten",
        "permissions": ["manage_members", "manage_applications", "view_finances", "manage_challenges"],
        "is_transferable": True,
        "max_per_coop": 3
    },
    {
        "key": "fleet_manager",
        "name": "Fuhrparkmanager",
        "description": "Verwaltet alle Fahrzeuge der Genossenschaft",
        "permissions": ["manage_vehicles", "buy_vehicles", "sell_vehicles", "assign_vehicles"],
        "is_transferable": True,
        "max_per_coop": 2
    },
    {
        "key": "field_manager",
        "name": "Feldmanager",
        "description": "Verwaltet alle Felder der Genossenschaft",
        "permissions": ["manage_fields", "buy_fields", "sell_fields", "plant_fields", "harvest_fields"],
        "is_transferable": True,
        "max_per_coop": 2
    },
    {
        "key": "animal_manager",
        "name": "Tiermanager",
        "description": "Verwaltet alle Tiere der Genossenschaft",
        "permissions": ["manage_animals", "buy_animals", "sell_animals", "feed_animals"],
        "is_transferable": True,
        "max_per_coop": 2
    },
    {
        "key": "production_manager",
        "name": "Produktionsleiter",
        "description": "Verwaltet alle Produktionsstätten der Genossenschaft",
        "permissions": ["manage_productions", "start_production", "collect_products"],
        "is_transferable": True,
        "max_per_coop": 2
    },
    {
        "key": "warehouse_manager",
        "name": "Lagerverwaltung",
        "description": "Verwaltet das Silo und Lager der Genossenschaft",
        "permissions": ["manage_warehouse", "deposit_products", "withdraw_products", "sell_products"],
        "is_transferable": True,
        "max_per_coop": 2
    },
    {
        "key": "treasurer",
        "name": "Kassenwart",
        "description": "Verwaltet die Finanzen der Genossenschaft",
        "permissions": ["view_finances", "payout_members", "view_transactions"],
        "is_transferable": True,
        "max_per_coop": 1
    },
    {
        "key": "researcher",
        "name": "Forschungsleiter",
        "description": "Leitet die Forschungsabteilung der Genossenschaft",
        "permissions": ["manage_research", "start_research", "cancel_research"],
        "is_transferable": True,
        "max_per_coop": 1
    },
    {
        "key": "member",
        "name": "Mitglied",
        "description": "Standardmitglied der Genossenschaft",
        "permissions": ["view_coop", "donate", "deposit_products", "participate_challenges"],
        "is_transferable": False,
        "max_per_coop": 0  # Unbegrenzt
    }
]

# ============================================
# GENOSSENSCHAFTS-FORSCHUNG
# ============================================

COOP_RESEARCH = [
    # Produktions-Freischaltungen
    {
        "name": "Gemeinschafts-Mühle",
        "description": "Schaltet eine Mühle für die Genossenschaft frei",
        "category": "production",
        "cost": 50000,
        "research_time_hours": 24,
        "unlocks": "production:mehlproduktion",
        "required_level": 5
    },
    {
        "name": "Gemeinschafts-Molkerei",
        "description": "Schaltet eine Molkerei für die Genossenschaft frei",
        "category": "production",
        "cost": 75000,
        "research_time_hours": 36,
        "unlocks": "production:molkerei",
        "required_level": 8
    },
    {
        "name": "Gemeinschafts-Bäckerei",
        "description": "Schaltet eine Bäckerei für die Genossenschaft frei",
        "category": "production",
        "cost": 100000,
        "research_time_hours": 48,
        "unlocks": "production:baeckerei",
        "required_level": 10
    },
    {
        "name": "Gemeinschafts-Käserei",
        "description": "Schaltet eine Käserei für die Genossenschaft frei",
        "category": "production",
        "cost": 120000,
        "research_time_hours": 48,
        "unlocks": "production:kaeserei",
        "required_level": 12
    },
    {
        "name": "Gemeinschafts-Schlachterei",
        "description": "Schaltet eine Schlachterei für die Genossenschaft frei",
        "category": "production",
        "cost": 150000,
        "research_time_hours": 72,
        "unlocks": "production:schlachterei",
        "required_level": 15
    },
    {
        "name": "Gemeinschafts-Brauerei",
        "description": "Schaltet eine Brauerei für die Genossenschaft frei",
        "category": "production",
        "cost": 200000,
        "research_time_hours": 96,
        "unlocks": "production:brauerei",
        "required_level": 18
    },
    {
        "name": "Gemeinschafts-Raffinerie",
        "description": "Schaltet eine Ölraffinerie für die Genossenschaft frei",
        "category": "production",
        "cost": 250000,
        "research_time_hours": 120,
        "unlocks": "production:raffinerie",
        "required_level": 20
    },
    # Lager-Erweiterungen
    {
        "name": "Erweitertes Silo",
        "description": "Erhöht die Lagerkapazität um 50%",
        "category": "storage",
        "cost": 30000,
        "research_time_hours": 12,
        "unlocks": "storage:upgrade_50",
        "required_level": 3
    },
    {
        "name": "Großes Silo",
        "description": "Erhöht die Lagerkapazität um 100%",
        "category": "storage",
        "cost": 60000,
        "research_time_hours": 24,
        "unlocks": "storage:upgrade_100",
        "required_level": 7
    },
    {
        "name": "Mega-Silo",
        "description": "Erhöht die Lagerkapazität um 200%",
        "category": "storage",
        "cost": 120000,
        "research_time_hours": 48,
        "unlocks": "storage:upgrade_200",
        "required_level": 12
    },
    # Effizienz-Boni
    {
        "name": "Effiziente Ernte",
        "description": "Erhöht den Ernteertrag aller Mitglieder um 5%",
        "category": "efficiency",
        "cost": 40000,
        "research_time_hours": 24,
        "unlocks": "bonus:harvest_5",
        "required_level": 5
    },
    {
        "name": "Effiziente Tierhaltung",
        "description": "Erhöht die Tierproduktion aller Mitglieder um 5%",
        "category": "efficiency",
        "cost": 45000,
        "research_time_hours": 24,
        "unlocks": "bonus:animal_5",
        "required_level": 6
    },
    {
        "name": "Kostenoptimierung",
        "description": "Reduziert Wartungskosten um 10%",
        "category": "efficiency",
        "cost": 50000,
        "research_time_hours": 36,
        "unlocks": "bonus:maintenance_10",
        "required_level": 8
    },
    {
        "name": "Handelsabkommen",
        "description": "Erhöht Verkaufspreise um 5%",
        "category": "efficiency",
        "cost": 80000,
        "research_time_hours": 48,
        "unlocks": "bonus:sell_price_5",
        "required_level": 10
    },
    {
        "name": "Einkaufsgemeinschaft",
        "description": "Reduziert Einkaufspreise um 5%",
        "category": "efficiency",
        "cost": 80000,
        "research_time_hours": 48,
        "unlocks": "bonus:buy_price_5",
        "required_level": 10
    },
]

# ============================================
# WÖCHENTLICHE GENOSSENSCHAFTS-HERAUSFORDERUNGEN (20)
# ============================================

WEEKLY_COOP_CHALLENGES = [
    # Ernte-Herausforderungen
    {
        "name": "Gemeinsame Ernte",
        "description": "Erntet zusammen 5.000 Einheiten Getreide",
        "type": "harvest",
        "target": 5000,
        "reward_money": 2500,
        "reward_points": 500
    },
    {
        "name": "Weizen-Offensive",
        "description": "Erntet zusammen 3.000 Einheiten Weizen",
        "type": "harvest",
        "target": 3000,
        "reward_money": 2000,
        "reward_points": 400
    },
    {
        "name": "Mais-Marathon",
        "description": "Erntet zusammen 2.500 Einheiten Mais",
        "type": "harvest",
        "target": 2500,
        "reward_money": 1800,
        "reward_points": 350
    },
    {
        "name": "Kartoffel-König",
        "description": "Erntet zusammen 2.000 Einheiten Kartoffeln",
        "type": "harvest",
        "target": 2000,
        "reward_money": 1500,
        "reward_points": 300
    },
    # Tier-Herausforderungen
    {
        "name": "Milchlieferung",
        "description": "Produziert zusammen 1.000 Liter Milch",
        "type": "animal_product",
        "target": 1000,
        "reward_money": 2000,
        "reward_points": 400
    },
    {
        "name": "Eier-Sammlung",
        "description": "Sammelt zusammen 500 Eier",
        "type": "animal_product",
        "target": 500,
        "reward_money": 1500,
        "reward_points": 300
    },
    {
        "name": "Wolle-Woche",
        "description": "Produziert zusammen 200 Einheiten Wolle",
        "type": "animal_product",
        "target": 200,
        "reward_money": 1800,
        "reward_points": 350
    },
    # Verkaufs-Herausforderungen
    {
        "name": "Markt-Meister",
        "description": "Verkauft zusammen Waren im Wert von 25.000 Talern",
        "type": "sales",
        "target": 25000,
        "reward_money": 3000,
        "reward_points": 600
    },
    {
        "name": "Handels-Helden",
        "description": "Führt zusammen 50 Verkäufe durch",
        "type": "sales_count",
        "target": 50,
        "reward_money": 2000,
        "reward_points": 400
    },
    # Produktions-Herausforderungen
    {
        "name": "Brot-Bäcker",
        "description": "Produziert zusammen 100 Einheiten Brot",
        "type": "production",
        "target": 100,
        "reward_money": 2500,
        "reward_points": 500
    },
    {
        "name": "Käse-Könige",
        "description": "Produziert zusammen 50 Einheiten Käse",
        "type": "production",
        "target": 50,
        "reward_money": 2200,
        "reward_points": 450
    },
    # Spenden-Herausforderungen
    {
        "name": "Großzügige Gemeinschaft",
        "description": "Spendet zusammen 10.000 Taler in die Kasse",
        "type": "donation",
        "target": 10000,
        "reward_money": 1500,
        "reward_points": 300
    },
    {
        "name": "Produkt-Spende",
        "description": "Lagert zusammen 500 Produkte ins Silo ein",
        "type": "deposit",
        "target": 500,
        "reward_money": 1800,
        "reward_points": 350
    },
    # Aktivitäts-Herausforderungen
    {
        "name": "Aktive Gemeinschaft",
        "description": "Alle Mitglieder loggen sich mindestens 3x ein",
        "type": "activity",
        "target": 3,
        "reward_money": 1000,
        "reward_points": 200
    },
    {
        "name": "Teamarbeit",
        "description": "Mindestens 5 verschiedene Mitglieder tragen bei",
        "type": "participation",
        "target": 5,
        "reward_money": 1500,
        "reward_points": 300
    },
    # Forschungs-Herausforderungen
    {
        "name": "Forschungsdrang",
        "description": "Schließt eine Genossenschafts-Forschung ab",
        "type": "research",
        "target": 1,
        "reward_money": 3000,
        "reward_points": 600
    },
    # Fahrzeug-Herausforderungen
    {
        "name": "Fuhrpark-Ausbau",
        "description": "Kauft zusammen 3 Fahrzeuge für die Genossenschaft",
        "type": "vehicle_purchase",
        "target": 3,
        "reward_money": 2500,
        "reward_points": 500
    },
    {
        "name": "Fleißige Fahrer",
        "description": "Nutzt Genossenschafts-Fahrzeuge für 100 Stunden",
        "type": "vehicle_usage",
        "target": 100,
        "reward_money": 2000,
        "reward_points": 400
    },
    # Feld-Herausforderungen
    {
        "name": "Feld-Expansion",
        "description": "Kauft zusammen 2 neue Felder für die Genossenschaft",
        "type": "field_purchase",
        "target": 2,
        "reward_money": 2000,
        "reward_points": 400
    },
    {
        "name": "Vielfalt-Anbau",
        "description": "Baut mindestens 5 verschiedene Früchte an",
        "type": "crop_variety",
        "target": 5,
        "reward_money": 1500,
        "reward_points": 300
    },
]

# ============================================
# MONATLICHE GENOSSENSCHAFTS-HERAUSFORDERUNGEN (50)
# ============================================

MONTHLY_COOP_CHALLENGES = [
    # Große Ernte-Herausforderungen
    {
        "name": "Ernte-Giganten",
        "description": "Erntet zusammen 50.000 Einheiten beliebiger Feldfrüchte",
        "type": "harvest",
        "target": 50000,
        "reward_money": 25000,
        "reward_points": 5000
    },
    {
        "name": "Weizen-Imperium",
        "description": "Erntet zusammen 25.000 Einheiten Weizen",
        "type": "harvest",
        "target": 25000,
        "reward_money": 20000,
        "reward_points": 4000
    },
    {
        "name": "Mais-Mogule",
        "description": "Erntet zusammen 20.000 Einheiten Mais",
        "type": "harvest",
        "target": 20000,
        "reward_money": 18000,
        "reward_points": 3600
    },
    {
        "name": "Kartoffel-Könige",
        "description": "Erntet zusammen 15.000 Einheiten Kartoffeln",
        "type": "harvest",
        "target": 15000,
        "reward_money": 15000,
        "reward_points": 3000
    },
    {
        "name": "Raps-Rekord",
        "description": "Erntet zusammen 12.000 Einheiten Raps",
        "type": "harvest",
        "target": 12000,
        "reward_money": 14000,
        "reward_points": 2800
    },
    {
        "name": "Sonnenblumen-Sommer",
        "description": "Erntet zusammen 10.000 Einheiten Sonnenblumen",
        "type": "harvest",
        "target": 10000,
        "reward_money": 12000,
        "reward_points": 2400
    },
    {
        "name": "Zuckerrüben-Ziel",
        "description": "Erntet zusammen 8.000 Einheiten Zuckerrüben",
        "type": "harvest",
        "target": 8000,
        "reward_money": 10000,
        "reward_points": 2000
    },
    {
        "name": "Gersten-Großernte",
        "description": "Erntet zusammen 15.000 Einheiten Gerste",
        "type": "harvest",
        "target": 15000,
        "reward_money": 13000,
        "reward_points": 2600
    },
    {
        "name": "Hafer-Helden",
        "description": "Erntet zusammen 10.000 Einheiten Hafer",
        "type": "harvest",
        "target": 10000,
        "reward_money": 11000,
        "reward_points": 2200
    },
    {
        "name": "Vielfalt-Champion",
        "description": "Erntet mindestens 10 verschiedene Feldfrüchte",
        "type": "crop_variety",
        "target": 10,
        "reward_money": 15000,
        "reward_points": 3000
    },
    # Große Tier-Herausforderungen
    {
        "name": "Milch-Imperium",
        "description": "Produziert zusammen 10.000 Liter Milch",
        "type": "animal_product",
        "target": 10000,
        "reward_money": 20000,
        "reward_points": 4000
    },
    {
        "name": "Eier-Explosion",
        "description": "Sammelt zusammen 5.000 Eier",
        "type": "animal_product",
        "target": 5000,
        "reward_money": 15000,
        "reward_points": 3000
    },
    {
        "name": "Wolle-Weltmeister",
        "description": "Produziert zusammen 2.000 Einheiten Wolle",
        "type": "animal_product",
        "target": 2000,
        "reward_money": 18000,
        "reward_points": 3600
    },
    {
        "name": "Fleisch-Fabrik",
        "description": "Produziert zusammen 500 Einheiten Fleisch",
        "type": "animal_product",
        "target": 500,
        "reward_money": 22000,
        "reward_points": 4400
    },
    {
        "name": "Honig-Helden",
        "description": "Produziert zusammen 300 Einheiten Honig",
        "type": "animal_product",
        "target": 300,
        "reward_money": 12000,
        "reward_points": 2400
    },
    {
        "name": "Tier-Vielfalt",
        "description": "Haltet mindestens 5 verschiedene Tierarten",
        "type": "animal_variety",
        "target": 5,
        "reward_money": 10000,
        "reward_points": 2000
    },
    # Große Verkaufs-Herausforderungen
    {
        "name": "Handels-Imperium",
        "description": "Verkauft zusammen Waren im Wert von 250.000 Talern",
        "type": "sales",
        "target": 250000,
        "reward_money": 30000,
        "reward_points": 6000
    },
    {
        "name": "Markt-Dominanz",
        "description": "Führt zusammen 500 Verkäufe durch",
        "type": "sales_count",
        "target": 500,
        "reward_money": 20000,
        "reward_points": 4000
    },
    {
        "name": "Export-Experten",
        "description": "Verkauft an alle 10 Verkaufsstellen",
        "type": "selling_points",
        "target": 10,
        "reward_money": 15000,
        "reward_points": 3000
    },
    {
        "name": "Gewinn-Giganten",
        "description": "Erzielt zusammen einen Gewinn von 100.000 Talern",
        "type": "profit",
        "target": 100000,
        "reward_money": 25000,
        "reward_points": 5000
    },
    # Große Produktions-Herausforderungen
    {
        "name": "Brot-Barone",
        "description": "Produziert zusammen 1.000 Einheiten Brot",
        "type": "production",
        "target": 1000,
        "reward_money": 25000,
        "reward_points": 5000
    },
    {
        "name": "Käse-Könige",
        "description": "Produziert zusammen 500 Einheiten Käse",
        "type": "production",
        "target": 500,
        "reward_money": 22000,
        "reward_points": 4400
    },
    {
        "name": "Butter-Berge",
        "description": "Produziert zusammen 400 Einheiten Butter",
        "type": "production",
        "target": 400,
        "reward_money": 18000,
        "reward_points": 3600
    },
    {
        "name": "Mehl-Meister",
        "description": "Produziert zusammen 800 Einheiten Mehl",
        "type": "production",
        "target": 800,
        "reward_money": 16000,
        "reward_points": 3200
    },
    {
        "name": "Öl-Offensive",
        "description": "Produziert zusammen 300 Einheiten Öl",
        "type": "production",
        "target": 300,
        "reward_money": 20000,
        "reward_points": 4000
    },
    {
        "name": "Bier-Brauer",
        "description": "Produziert zusammen 200 Einheiten Bier",
        "type": "production",
        "target": 200,
        "reward_money": 24000,
        "reward_points": 4800
    },
    {
        "name": "Saft-Spezialisten",
        "description": "Produziert zusammen 500 Einheiten Saft",
        "type": "production",
        "target": 500,
        "reward_money": 15000,
        "reward_points": 3000
    },
    {
        "name": "Wurst-Wunder",
        "description": "Produziert zusammen 300 Einheiten Wurst",
        "type": "production",
        "target": 300,
        "reward_money": 20000,
        "reward_points": 4000
    },
    {
        "name": "Zucker-Ziel",
        "description": "Produziert zusammen 600 Einheiten Zucker",
        "type": "production",
        "target": 600,
        "reward_money": 18000,
        "reward_points": 3600
    },
    {
        "name": "Produktions-Vielfalt",
        "description": "Produziert mindestens 10 verschiedene Produkte",
        "type": "product_variety",
        "target": 10,
        "reward_money": 20000,
        "reward_points": 4000
    },
    # Große Spenden-Herausforderungen
    {
        "name": "Millionen-Kasse",
        "description": "Spendet zusammen 100.000 Taler in die Kasse",
        "type": "donation",
        "target": 100000,
        "reward_money": 15000,
        "reward_points": 3000
    },
    {
        "name": "Lager-Legende",
        "description": "Lagert zusammen 5.000 Produkte ins Silo ein",
        "type": "deposit",
        "target": 5000,
        "reward_money": 18000,
        "reward_points": 3600
    },
    {
        "name": "Gemeinschafts-Geist",
        "description": "Jedes Mitglied spendet mindestens einmal",
        "type": "donation_participation",
        "target": 100,  # 100% der Mitglieder
        "reward_money": 10000,
        "reward_points": 2000
    },
    # Große Aktivitäts-Herausforderungen
    {
        "name": "Dauerbrenner",
        "description": "Alle Mitglieder loggen sich mindestens 20x ein",
        "type": "activity",
        "target": 20,
        "reward_money": 10000,
        "reward_points": 2000
    },
    {
        "name": "Volle Beteiligung",
        "description": "Alle Mitglieder tragen zu mindestens einer Herausforderung bei",
        "type": "challenge_participation",
        "target": 100,  # 100% der Mitglieder
        "reward_money": 12000,
        "reward_points": 2400
    },
    # Große Forschungs-Herausforderungen
    {
        "name": "Forschungs-Führer",
        "description": "Schließt 5 Genossenschafts-Forschungen ab",
        "type": "research",
        "target": 5,
        "reward_money": 30000,
        "reward_points": 6000
    },
    {
        "name": "Technologie-Titan",
        "description": "Schaltet 3 neue Produktionsstätten frei",
        "type": "production_unlock",
        "target": 3,
        "reward_money": 25000,
        "reward_points": 5000
    },
    # Große Fahrzeug-Herausforderungen
    {
        "name": "Fuhrpark-Imperium",
        "description": "Besitzt zusammen 20 Genossenschafts-Fahrzeuge",
        "type": "vehicle_count",
        "target": 20,
        "reward_money": 25000,
        "reward_points": 5000
    },
    {
        "name": "Fleißige Flotte",
        "description": "Nutzt Genossenschafts-Fahrzeuge für 1.000 Stunden",
        "type": "vehicle_usage",
        "target": 1000,
        "reward_money": 20000,
        "reward_points": 4000
    },
    {
        "name": "Premium-Fuhrpark",
        "description": "Kauft 5 Fahrzeuge über 100.000 Taler",
        "type": "premium_vehicle",
        "target": 5,
        "reward_money": 30000,
        "reward_points": 6000
    },
    # Große Feld-Herausforderungen
    {
        "name": "Land-Barone",
        "description": "Besitzt zusammen 50 Hektar Felder",
        "type": "field_size",
        "target": 50,
        "reward_money": 25000,
        "reward_points": 5000
    },
    {
        "name": "Feld-Führer",
        "description": "Besitzt zusammen 30 Felder",
        "type": "field_count",
        "target": 30,
        "reward_money": 20000,
        "reward_points": 4000
    },
    {
        "name": "Anbau-Meister",
        "description": "Baut alle verfügbaren Feldfrüchte mindestens einmal an",
        "type": "all_crops",
        "target": 1,
        "reward_money": 15000,
        "reward_points": 3000
    },
    # Mitglieder-Herausforderungen
    {
        "name": "Wachsende Gemeinschaft",
        "description": "Rekrutiert 5 neue Mitglieder",
        "type": "recruitment",
        "target": 5,
        "reward_money": 15000,
        "reward_points": 3000
    },
    {
        "name": "Level-Boost",
        "description": "Alle Mitglieder steigen mindestens 1 Level auf",
        "type": "member_levelup",
        "target": 1,
        "reward_money": 20000,
        "reward_points": 4000
    },
    {
        "name": "Punkte-Power",
        "description": "Sammelt zusammen 50.000 Punkte",
        "type": "points",
        "target": 50000,
        "reward_money": 25000,
        "reward_points": 5000
    },
    # Spezielle Herausforderungen
    {
        "name": "Allrounder",
        "description": "Schließt mindestens 10 wöchentliche Herausforderungen ab",
        "type": "weekly_complete",
        "target": 10,
        "reward_money": 30000,
        "reward_points": 6000
    },
    {
        "name": "Perfekter Monat",
        "description": "Schließt alle wöchentlichen Herausforderungen des Monats ab",
        "type": "perfect_month",
        "target": 1,
        "reward_money": 50000,
        "reward_points": 10000
    },
    {
        "name": "Top-Genossenschaft",
        "description": "Erreicht Platz 1-10 in der Rangliste",
        "type": "ranking",
        "target": 10,
        "reward_money": 40000,
        "reward_points": 8000
    },
]


def generate_sql():
    """Generiert die SQL-Migration"""

    sql_lines = []
    sql_lines.append("-- ============================================")
    sql_lines.append("-- Agrargenossenschaft Erweiterung")
    sql_lines.append(f"-- Generiert am: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("SET NAMES utf8mb4;")
    sql_lines.append("SET FOREIGN_KEY_CHECKS = 0;")
    sql_lines.append("")

    # ============================================
    # 1. ROLLEN-TABELLE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- ROLLEN-SYSTEM")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_roles (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    role_key VARCHAR(50) NOT NULL UNIQUE,")
    sql_lines.append("    name VARCHAR(100) NOT NULL,")
    sql_lines.append("    description TEXT,")
    sql_lines.append("    permissions JSON,")
    sql_lines.append("    is_transferable BOOLEAN DEFAULT TRUE,")
    sql_lines.append("    max_per_coop INT DEFAULT 0,")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # Rollen einfügen
    for role in COOPERATIVE_ROLES:
        perms_json = json.dumps(role["permissions"])
        sql_lines.append(f"INSERT INTO cooperative_roles (role_key, name, description, permissions, is_transferable, max_per_coop) VALUES ('{role['key']}', '{role['name']}', '{role['description']}', '{perms_json}', {1 if role['is_transferable'] else 0}, {role['max_per_coop']});")

    sql_lines.append("")

    # ============================================
    # 2. COOPERATIVE_MEMBERS ERWEITERN
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- MITGLIEDER-TABELLE ERWEITERN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("-- Rolle auf role_key ändern (VARCHAR statt ENUM)")
    sql_lines.append("ALTER TABLE cooperative_members MODIFY COLUMN role VARCHAR(50) DEFAULT 'member';")
    sql_lines.append("")

    # ============================================
    # 3. BEWERBUNGS-SYSTEM
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- BEWERBUNGS-SYSTEM")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_applications (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    cooperative_id INT NOT NULL,")
    sql_lines.append("    farm_id INT NOT NULL,")
    sql_lines.append("    message TEXT,")
    sql_lines.append("    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',")
    sql_lines.append("    reviewed_by INT DEFAULT NULL,")
    sql_lines.append("    review_message TEXT,")
    sql_lines.append("    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    reviewed_at TIMESTAMP NULL,")
    sql_lines.append("    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (reviewed_by) REFERENCES farms(id) ON DELETE SET NULL,")
    sql_lines.append("    UNIQUE KEY unique_application (cooperative_id, farm_id, status)")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # ============================================
    # 4. GENOSSENSCHAFTS-SILO/LAGER
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- GENOSSENSCHAFTS-SILO/LAGER")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_warehouse (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    cooperative_id INT NOT NULL,")
    sql_lines.append("    product_id INT NOT NULL,")
    sql_lines.append("    quantity INT DEFAULT 0,")
    sql_lines.append("    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,")
    sql_lines.append("    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,")
    sql_lines.append("    UNIQUE KEY unique_coop_product (cooperative_id, product_id)")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    sql_lines.append("-- Lager-Transaktionen")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_warehouse_log (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    cooperative_id INT NOT NULL,")
    sql_lines.append("    farm_id INT NOT NULL,")
    sql_lines.append("    product_id INT NOT NULL,")
    sql_lines.append("    quantity INT NOT NULL,")
    sql_lines.append("    transaction_type ENUM('deposit', 'withdraw', 'sale', 'production_input', 'production_output') NOT NULL,")
    sql_lines.append("    notes TEXT,")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # Cooperative settings erweitern
    sql_lines.append("-- Genossenschafts-Einstellungen erweitern")
    sql_lines.append("ALTER TABLE cooperatives ADD COLUMN IF NOT EXISTS warehouse_capacity INT DEFAULT 10000;")
    sql_lines.append("ALTER TABLE cooperatives ADD COLUMN IF NOT EXISTS requires_application BOOLEAN DEFAULT TRUE;")
    sql_lines.append("ALTER TABLE cooperatives ADD COLUMN IF NOT EXISTS min_level_to_join INT DEFAULT 1;")
    sql_lines.append("")

    # ============================================
    # 5. GENOSSENSCHAFTS-FINANZEN
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- GENOSSENSCHAFTS-FINANZEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_transactions (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    cooperative_id INT NOT NULL,")
    sql_lines.append("    farm_id INT DEFAULT NULL,")
    sql_lines.append("    amount DECIMAL(12,2) NOT NULL,")
    sql_lines.append("    transaction_type ENUM('donation', 'payout', 'purchase', 'sale', 'reward', 'fee') NOT NULL,")
    sql_lines.append("    description TEXT,")
    sql_lines.append("    balance_after DECIMAL(12,2),")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    created_by INT DEFAULT NULL,")
    sql_lines.append("    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE SET NULL,")
    sql_lines.append("    FOREIGN KEY (created_by) REFERENCES farms(id) ON DELETE SET NULL")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # ============================================
    # 6. GENOSSENSCHAFTS-FAHRZEUGE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- GENOSSENSCHAFTS-FAHRZEUGE")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_vehicles (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    cooperative_id INT NOT NULL,")
    sql_lines.append("    vehicle_id INT NOT NULL,")
    sql_lines.append("    custom_name VARCHAR(100),")
    sql_lines.append("    condition_percent INT DEFAULT 100,")
    sql_lines.append("    operating_hours INT DEFAULT 0,")
    sql_lines.append("    current_user_farm_id INT DEFAULT NULL,")
    sql_lines.append("    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    purchased_by INT DEFAULT NULL,")
    sql_lines.append("    last_maintenance TIMESTAMP NULL,")
    sql_lines.append("    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (current_user_farm_id) REFERENCES farms(id) ON DELETE SET NULL,")
    sql_lines.append("    FOREIGN KEY (purchased_by) REFERENCES farms(id) ON DELETE SET NULL")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # ============================================
    # 7. GENOSSENSCHAFTS-FELDER
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- GENOSSENSCHAFTS-FELDER")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_fields (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    cooperative_id INT NOT NULL,")
    sql_lines.append("    size_hectares DECIMAL(5,2) NOT NULL,")
    sql_lines.append("    position_x INT DEFAULT 0,")
    sql_lines.append("    position_y INT DEFAULT 0,")
    sql_lines.append("    current_crop_id INT DEFAULT NULL,")
    sql_lines.append("    planted_at TIMESTAMP NULL,")
    sql_lines.append("    harvest_ready_at TIMESTAMP NULL,")
    sql_lines.append("    planted_by INT DEFAULT NULL,")
    sql_lines.append("    soil_quality INT DEFAULT 100,")
    sql_lines.append("    is_irrigated BOOLEAN DEFAULT FALSE,")
    sql_lines.append("    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    purchased_by INT DEFAULT NULL,")
    sql_lines.append("    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (current_crop_id) REFERENCES crops(id) ON DELETE SET NULL,")
    sql_lines.append("    FOREIGN KEY (planted_by) REFERENCES farms(id) ON DELETE SET NULL,")
    sql_lines.append("    FOREIGN KEY (purchased_by) REFERENCES farms(id) ON DELETE SET NULL")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # ============================================
    # 8. GENOSSENSCHAFTS-TIERE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- GENOSSENSCHAFTS-TIERE")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_animals (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    cooperative_id INT NOT NULL,")
    sql_lines.append("    animal_type VARCHAR(50) NOT NULL,")
    sql_lines.append("    quantity INT DEFAULT 1,")
    sql_lines.append("    health_percent INT DEFAULT 100,")
    sql_lines.append("    last_fed TIMESTAMP NULL,")
    sql_lines.append("    last_product_collected TIMESTAMP NULL,")
    sql_lines.append("    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    purchased_by INT DEFAULT NULL,")
    sql_lines.append("    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (purchased_by) REFERENCES farms(id) ON DELETE SET NULL")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # ============================================
    # 9. GENOSSENSCHAFTS-PRODUKTIONEN
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- GENOSSENSCHAFTS-PRODUKTIONEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_productions (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    cooperative_id INT NOT NULL,")
    sql_lines.append("    production_id INT NOT NULL,")
    sql_lines.append("    level INT DEFAULT 1,")
    sql_lines.append("    is_active BOOLEAN DEFAULT TRUE,")
    sql_lines.append("    current_recipe VARCHAR(100) DEFAULT NULL,")
    sql_lines.append("    production_started_at TIMESTAMP NULL,")
    sql_lines.append("    production_ready_at TIMESTAMP NULL,")
    sql_lines.append("    started_by INT DEFAULT NULL,")
    sql_lines.append("    total_produced INT DEFAULT 0,")
    sql_lines.append("    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    purchased_by INT DEFAULT NULL,")
    sql_lines.append("    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (started_by) REFERENCES farms(id) ON DELETE SET NULL,")
    sql_lines.append("    FOREIGN KEY (purchased_by) REFERENCES farms(id) ON DELETE SET NULL")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # ============================================
    # 10. GENOSSENSCHAFTS-FORSCHUNG
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- GENOSSENSCHAFTS-FORSCHUNG")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_research_tree (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    name VARCHAR(100) NOT NULL,")
    sql_lines.append("    description TEXT,")
    sql_lines.append("    category ENUM('production', 'storage', 'efficiency') NOT NULL,")
    sql_lines.append("    cost DECIMAL(10,2) NOT NULL,")
    sql_lines.append("    research_time_hours INT NOT NULL,")
    sql_lines.append("    required_coop_level INT DEFAULT 1,")
    sql_lines.append("    prerequisite_id INT DEFAULT NULL,")
    sql_lines.append("    unlocks VARCHAR(255),")
    sql_lines.append("    is_active BOOLEAN DEFAULT TRUE,")
    sql_lines.append("    FOREIGN KEY (prerequisite_id) REFERENCES cooperative_research_tree(id) ON DELETE SET NULL")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # Forschungen einfügen
    for i, research in enumerate(COOP_RESEARCH, start=1):
        sql_lines.append(f"INSERT INTO cooperative_research_tree (name, description, category, cost, research_time_hours, required_coop_level, unlocks) VALUES ('{research['name']}', '{research['description']}', '{research['category']}', {research['cost']}, {research['research_time_hours']}, {research['required_level']}, '{research['unlocks']}');")

    sql_lines.append("")

    sql_lines.append("-- Abgeschlossene Forschungen")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_research (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    cooperative_id INT NOT NULL,")
    sql_lines.append("    research_id INT NOT NULL,")
    sql_lines.append("    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    completed_at TIMESTAMP NULL,")
    sql_lines.append("    started_by INT DEFAULT NULL,")
    sql_lines.append("    status ENUM('in_progress', 'completed', 'cancelled') DEFAULT 'in_progress',")
    sql_lines.append("    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (research_id) REFERENCES cooperative_research_tree(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (started_by) REFERENCES farms(id) ON DELETE SET NULL,")
    sql_lines.append("    UNIQUE KEY unique_coop_research (cooperative_id, research_id)")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # ============================================
    # 11. GENOSSENSCHAFTS-HERAUSFORDERUNGEN
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- GENOSSENSCHAFTS-HERAUSFORDERUNGEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")

    sql_lines.append("-- Herausforderungs-Vorlagen")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_challenge_templates (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    name VARCHAR(100) NOT NULL,")
    sql_lines.append("    description TEXT,")
    sql_lines.append("    challenge_type VARCHAR(50) NOT NULL,")
    sql_lines.append("    challenge_period ENUM('weekly', 'monthly') NOT NULL,")
    sql_lines.append("    target_value INT NOT NULL,")
    sql_lines.append("    reward_money DECIMAL(10,2) DEFAULT 0,")
    sql_lines.append("    reward_points INT DEFAULT 0,")
    sql_lines.append("    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',")
    sql_lines.append("    is_active BOOLEAN DEFAULT TRUE,")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # Wöchentliche Herausforderungen einfügen
    sql_lines.append("-- Wöchentliche Herausforderungen")
    for challenge in WEEKLY_COOP_CHALLENGES:
        difficulty = "easy" if challenge["reward_points"] <= 350 else ("medium" if challenge["reward_points"] <= 500 else "hard")
        sql_lines.append(f"INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('{challenge['name']}', '{challenge['description']}', '{challenge['type']}', 'weekly', {challenge['target']}, {challenge['reward_money']}, {challenge['reward_points']}, '{difficulty}');")

    sql_lines.append("")

    # Monatliche Herausforderungen einfügen
    sql_lines.append("-- Monatliche Herausforderungen")
    for challenge in MONTHLY_COOP_CHALLENGES:
        difficulty = "easy" if challenge["reward_points"] <= 3000 else ("medium" if challenge["reward_points"] <= 5000 else "hard")
        sql_lines.append(f"INSERT INTO cooperative_challenge_templates (name, description, challenge_type, challenge_period, target_value, reward_money, reward_points, difficulty) VALUES ('{challenge['name']}', '{challenge['description']}', '{challenge['type']}', 'monthly', {challenge['target']}, {challenge['reward_money']}, {challenge['reward_points']}, '{difficulty}');")

    sql_lines.append("")

    sql_lines.append("-- Aktive Herausforderungen pro Genossenschaft")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_challenges (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    cooperative_id INT NOT NULL,")
    sql_lines.append("    template_id INT NOT NULL,")
    sql_lines.append("    current_value INT DEFAULT 0,")
    sql_lines.append("    is_completed BOOLEAN DEFAULT FALSE,")
    sql_lines.append("    completed_at TIMESTAMP NULL,")
    sql_lines.append("    start_date DATE NOT NULL,")
    sql_lines.append("    end_date DATE NOT NULL,")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (template_id) REFERENCES cooperative_challenge_templates(id) ON DELETE CASCADE")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    sql_lines.append("-- Beiträge zu Herausforderungen")
    sql_lines.append("CREATE TABLE IF NOT EXISTS cooperative_challenge_contributions (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    challenge_id INT NOT NULL,")
    sql_lines.append("    farm_id INT NOT NULL,")
    sql_lines.append("    contribution_value INT NOT NULL,")
    sql_lines.append("    contributed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    FOREIGN KEY (challenge_id) REFERENCES cooperative_challenges(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE")
    sql_lines.append(") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;")
    sql_lines.append("")

    # ============================================
    # 12. GENOSSENSCHAFTS-LEVEL
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- GENOSSENSCHAFTS-LEVEL")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("ALTER TABLE cooperatives ADD COLUMN IF NOT EXISTS level INT DEFAULT 1;")
    sql_lines.append("ALTER TABLE cooperatives ADD COLUMN IF NOT EXISTS experience_points INT DEFAULT 0;")
    sql_lines.append("ALTER TABLE cooperatives ADD COLUMN IF NOT EXISTS total_challenges_completed INT DEFAULT 0;")
    sql_lines.append("")

    sql_lines.append("SET FOREIGN_KEY_CHECKS = 1;")
    sql_lines.append("")
    sql_lines.append("-- Ende der Migration")

    return "\n".join(sql_lines)


def generate_json():
    """Generiert JSON-Export"""

    data = {
        "generated_at": datetime.now().isoformat(),
        "roles": COOPERATIVE_ROLES,
        "research": COOP_RESEARCH,
        "weekly_challenges": WEEKLY_COOP_CHALLENGES,
        "monthly_challenges": MONTHLY_COOP_CHALLENGES,
        "statistics": {
            "total_roles": len(COOPERATIVE_ROLES),
            "total_research": len(COOP_RESEARCH),
            "weekly_challenges": len(WEEKLY_COOP_CHALLENGES),
            "monthly_challenges": len(MONTHLY_COOP_CHALLENGES)
        }
    }

    return json.dumps(data, ensure_ascii=False, indent=2)


def main():
    """Hauptfunktion"""

    print("=" * 60)
    print("Agrargenossenschaft Erweiterung Parser")
    print("=" * 60)
    print()

    # Statistiken ausgeben
    print(f"Rollen: {len(COOPERATIVE_ROLES)}")
    print(f"Forschungen: {len(COOP_RESEARCH)}")
    print(f"Wöchentliche Herausforderungen: {len(WEEKLY_COOP_CHALLENGES)}")
    print(f"Monatliche Herausforderungen: {len(MONTHLY_COOP_CHALLENGES)}")
    print()

    # SQL generieren
    print("Generiere SQL-Migration...")
    sql_content = generate_sql()

    sql_path = "../sql/cooperative_extension.sql"
    with open(sql_path, "w", encoding="utf-8", newline='\n') as f:
        f.write(sql_content)
    print(f"SQL gespeichert: {sql_path}")

    # JSON generieren
    print("Generiere JSON-Export...")
    json_content = generate_json()

    json_path = "../sql/cooperative_extension.json"
    with open(json_path, "w", encoding="utf-8") as f:
        f.write(json_content)
    print(f"JSON gespeichert: {json_path}")

    print()
    print("=" * 60)
    print("Fertig!")
    print("=" * 60)


if __name__ == "__main__":
    main()
