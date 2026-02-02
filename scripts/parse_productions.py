#!/usr/bin/env python3
"""
Produktionsübersicht Parser
Parst die PDF-Daten und generiert SQL-Migrationen für das Browsergame.
"""

import json
import re
from datetime import datetime

# ============================================
# PRODUKTIONSDATEN AUS DER PDF
# ============================================

PRODUCTIONS = [
    # Grundproduktionen
    {
        "name": "Bohrinsel",
        "name_de": "Bohrinsel",
        "cost": 3900000,
        "category": "rohstoffe",
        "research_id": 100,
        "inputs": ["Wartung", "Strom"],
        "outputs": ["Rohöl", "Methan"]
    },
    {
        "name": "Brauerei",
        "name_de": "Brauerei",
        "cost": 2900000,
        "category": "verarbeitung",
        "research_id": 101,
        "inputs": ["Hopfen", "Wartung", "Weizenmalz", "Gerstenmalz", "Hirsemalz", "Hefe", "Leerpalette", "Flaschen", "Zucker", "Wasser", "Strom"],
        "outputs": ["Weizenbierfass", "Gerstenbierfass", "Hirsebierfass", "Gerste Flaschenbier", "Weizen Flaschenbier", "Malzbier", "Schweinefutter", "Alte Leerpalette", "Abwasser"]
    },
    {
        "name": "Brennerei",
        "name_de": "Brennerei",
        "cost": 2800000,
        "category": "verarbeitung",
        "research_id": 102,
        "inputs": ["Apfel", "Birne", "Kirsche", "Pflaume", "Mirabelle", "Weizen", "Sommerweizen", "Roggen", "Mais", "Kartoffeln", "Eier", "Erdbeere", "Himbeere", "Sahne", "Hefe", "Weizenmalz", "Gerstenmalz", "Zucker", "Korn", "Wodka", "Holzfass", "Flaschen", "Trauben", "Kartonrolle", "Papierrolle", "Leerpalette", "Wasser", "Strom", "Wartung"],
        "outputs": ["Obstler", "Korn", "Whiskey", "Wodka", "Eierlikör", "Erdbeerlimes", "Himbeerlimes", "Rotwein", "Kompost", "Essig", "Alte Leerpalette"]
    },
    {
        "name": "Bäckerei",
        "name_de": "Bäckerei",
        "cost": 1900000,
        "category": "verarbeitung",
        "research_id": 103,
        "inputs": ["Mehl", "Eier", "Zucker", "Hefe", "Vollmilch", "Joghurt", "Butter", "Sahne", "Erdbeeren", "Reismehl", "Kartonrolle", "Leerpalette", "Wartung", "Strom", "Wasser"],
        "outputs": ["Backwaren", "Brot", "Brötchen", "Kuchen", "Alte Leerpalette", "Abwasser"]
    },
    {
        "name": "Crepes und Eisverkauf",
        "name_de": "Crepes und Eisverkauf",
        "cost": 190000,
        "category": "verkauf",
        "research_id": 104,
        "inputs": ["Waffeleis", "Eis am Stiel", "Apfelsaft", "Kirschsaft", "Backwaren", "Wartung", "Strom", "Weizenmehl", "Zucker", "Eier", "Vollmilch", "Sonnenblumenöl", "Apfelmus", "Erdbeermarmelade"],
        "outputs": ["Geldkassette"]
    },
    {
        "name": "Düngerherstellung",
        "name_de": "Düngerherstellung",
        "cost": 380000,
        "category": "landwirtschaft",
        "research_id": 105,
        "inputs": ["Gülle", "Gärreste", "Mist", "Kompost", "Wasser", "Wartung", "Strom"],
        "outputs": ["Mineraldünger", "Flüssigdünger"]
    },
    {
        "name": "Eisfabrik",
        "name_de": "Eisfabrik",
        "cost": 1500000,
        "category": "verarbeitung",
        "research_id": 106,
        "inputs": ["Vollmilch", "Sahne", "Zucker", "Leerpalette", "Kartonrolle", "Wartung", "Strom", "Wasser", "Erdbeere", "Himbeere", "Eier", "Butter", "Mehl", "Pfirsich", "Kirsche", "Bretterpalette"],
        "outputs": ["Waffeleis", "Eis am Stiel", "Crushed Ice", "Schweinefutter", "Abwasser", "Alte Leerpalette"]
    },
    {
        "name": "Feld Erdbeere",
        "name_de": "Erdbeerfeld",
        "cost": 450000,
        "category": "plantage",
        "research_id": 107,
        "inputs": ["Saatgut", "Wartung", "Leerpalette", "Kartonpalette", "Kompost", "Stroh", "Strom", "Wasser", "Mist", "Herbizid", "Bienenstock"],
        "outputs": ["Erdbeere", "Himbeere", "Brombeere", "Johannisbeere", "Honig"]
    },
    {
        "name": "Feld Hopfen",
        "name_de": "Hopfenfeld",
        "cost": 400000,
        "category": "plantage",
        "research_id": 108,
        "inputs": ["Setzlingspalette", "Holzkiste", "Leerpalette", "Wartung", "Kompost", "Bretterpalette", "Wasser", "Strom", "Kartonrolle", "Mist", "Herbizid", "Bienenstock"],
        "outputs": ["Hopfen", "Honig"]
    },
    {
        "name": "Feld Kürbis",
        "name_de": "Kürbisfeld",
        "cost": 450000,
        "category": "plantage",
        "research_id": 109,
        "inputs": ["Saatgut", "Holzkiste", "Leerpalette", "Wartung", "Stroh", "Wasser", "Strom", "Kartonrolle", "Mist", "Herbizid", "Bienenstock"],
        "outputs": ["Kürbispaletten", "Honig"]
    },
    {
        "name": "Feld Oliven",
        "name_de": "Olivenfeld",
        "cost": 450000,
        "category": "plantage",
        "research_id": 110,
        "inputs": ["Setzlingspaletten", "Holzkiste", "Kompost", "Leerpalette", "Wartung", "Wasser", "Strom", "Mist", "Herbizid", "Mineraldünger"],
        "outputs": ["Oliven"]
    },
    {
        "name": "Feld Rollrasen",
        "name_de": "Rollrasenfeld",
        "cost": 490000,
        "category": "plantage",
        "research_id": 111,
        "inputs": ["Ackergras", "Erde", "Leerpalette", "Wartung", "Strom", "Wasser", "Herbizid", "Mineraldünger"],
        "outputs": ["Rollrasen"]
    },
    {
        "name": "Feld Tannenbäume",
        "name_de": "Tannenbaumfeld",
        "cost": 250000,
        "category": "plantage",
        "research_id": 112,
        "inputs": ["Kompost", "Wartung", "Strom", "Wasser", "Setzlingspalette", "Mineraldünger", "Herbizid"],
        "outputs": ["Geldkassette"]
    },
    {
        "name": "Feld Trauben",
        "name_de": "Traubenfeld",
        "cost": 450000,
        "category": "plantage",
        "research_id": 113,
        "inputs": ["Setzlingspaletten", "Wartung", "Bretterpaletten", "Kompost", "Strom", "Wasser", "Kartonrolle", "Leerpalette", "Herbizid", "Bienenstock"],
        "outputs": ["Trauben", "Honig"]
    },
    {
        "name": "Fermenter",
        "name_de": "Fermenter",
        "cost": 580000,
        "category": "verarbeitung",
        "research_id": 114,
        "inputs": ["Gras", "Luzerne", "Häckselgut", "Kleegras", "Siliermittel"],
        "outputs": ["Silage", "Gärreste"]
    },
    {
        "name": "Fermenter klein",
        "name_de": "Fermenter (klein)",
        "cost": 100000,
        "category": "verarbeitung",
        "research_id": 115,
        "inputs": ["Gras", "Luzerne", "Häckselgut", "Kleegras", "Siliermittel"],
        "outputs": ["Silage"]
    },
    {
        "name": "Fischbude",
        "name_de": "Fischbude",
        "cost": 190000,
        "category": "verkauf",
        "research_id": 116,
        "inputs": ["Wartung", "Strom", "Scholle", "Hering", "Krabbe", "Hummer", "Dorsch", "Lachs", "Forelle", "Aal", "Geräucherter Lachs", "Geräucherte Forelle", "Geräucherter Aal", "Malzbier", "Traubensaft", "Dorschfilet", "Brötchen", "Butter", "Papierrolle", "Salat", "Mayonnaise"],
        "outputs": ["Geldkassette"]
    },
    {
        "name": "Fischfabrik",
        "name_de": "Fischfabrik",
        "cost": 2000000,
        "category": "verarbeitung",
        "research_id": 117,
        "inputs": ["Scholle", "Hering", "Krabbe", "Dorsch", "Zucker", "Weizenmehl", "Eier", "Mayonnaise", "Tomatensauce", "Sonnenblumenöl", "Rapsöl", "Wartung", "Papierrolle", "Kartonrolle", "Wasser", "Strom", "Leerpalette"],
        "outputs": ["Schollenfilet", "Hering in Tomatensauce", "Krabbensalat", "Dorschfilet", "Fischmehl", "Kompost", "Abwasser", "Alte Leerpalette"]
    },
    {
        "name": "Fischer",
        "name_de": "Fischer",
        "cost": 800000,
        "category": "produktion",
        "research_id": 118,
        "inputs": ["Leerpalette", "Diesel", "Wartung", "Strom", "Rundballennetz", "Crushed Ice", "Seil"],
        "outputs": ["Scholle", "Hering", "Krabbe", "Hummer", "Muscheln"]
    },
    {
        "name": "Fischzucht",
        "name_de": "Fischzucht",
        "cost": 800000,
        "category": "tierhaltung",
        "research_id": 119,
        "inputs": ["Fischfutter", "Rundballennetz", "Crushed Ice", "Leerpalette", "Wartung", "Strom"],
        "outputs": ["Lachs", "Forelle", "Aal", "Dorsch", "Alte Leerpalette"]
    },
    {
        "name": "Futterfabrik",
        "name_de": "Futterfabrik",
        "cost": 690000,
        "category": "verarbeitung",
        "research_id": 120,
        "inputs": ["Wartung", "Strom", "Kleie", "Kalk", "Hefe", "Silage", "Stroh", "Mineralfutter", "Heu", "Luzerne Heu", "Kleeheu", "Mais", "Kartoffeln", "Weizen", "Raps"],
        "outputs": ["Mineralfutter", "Mischration", "Schweinefutter"]
    },
    {
        "name": "Futterfabrik XL",
        "name_de": "Futterfabrik XL",
        "cost": 2400000,
        "category": "verarbeitung",
        "research_id": 121,
        "inputs": ["Strom", "Wartung", "Kleie", "Kalk", "Hefe", "Silage", "Stroh", "Mineralfutter", "Heu", "Luzerne Heu", "Kleeheu", "Weizen", "Mais", "Kartoffeln", "Raps", "Molke", "Leerpalette", "Kartonrolle", "Wasser", "Muscheln", "Fischmehl", "Ackererbsen", "Nebenprodukt", "Rindfleisch", "Hafer", "Erbsen", "Geflügelfleisch", "Sonnenblumen", "Triticale", "Hanf"],
        "outputs": ["Mineralfutter", "Mischration", "Schweinefutter", "Alte Leerpalette", "Abwasser", "Katzenfutter", "Hundefutter", "Vogelfutter", "Fischfutter", "Leerpaletten"]
    },
    {
        "name": "Gemüsefabrik",
        "name_de": "Gemüsefabrik",
        "cost": 3800000,
        "category": "verarbeitung",
        "research_id": 122,
        "inputs": ["Kartoffeln", "Möhren", "Rote Zwiebeln", "Frühlingslauch", "Tomaten", "Knoblauch", "Zwiebeln", "Ackererbsen", "Netzrolle", "Papierrolle", "Kartonrolle", "Leerpalette", "Strom", "Wasser", "Wartung", "Ackerbohnen", "Pastinaken", "Rote Beete", "Chilischoten", "Chinakohl", "Kürbis", "Spinat", "Mais", "Enoki", "Austernpilze", "Pilze", "Wurst", "Rindfleisch", "Schinken", "Geflügelfleisch", "Sahne", "Käse", "Brot", "Rotwein", "Weizenmehl", "Maisöl", "Nudeln"],
        "outputs": ["Karottensack", "Zwiebelsack", "Bohnensuppe", "Eingemachte Bohnen", "Chili Con Carne", "Dreierleisuppe", "Erbsensuppe", "Erbsendose", "Karottendose", "Karottensuppe", "Kartoffelsuppe", "Kohlsuppe", "Kimchi", "Kürbissuppe", "Kürbiskerne", "Nudelsuppe", "Pastinakensuppe", "Pastinakendose", "Pilzsuppe", "Rotebeetesuppe", "Rotebeetedose", "Spinatsack", "Rahmspinat", "Tomatensuppe", "Zwiebelsalz", "Röstzwiebeln", "Zwiebelsuppe", "Alte Leerpalette", "Abwasser", "Kompost"]
    },
    {
        "name": "Einkaufsstation Hafen",
        "name_de": "Einkaufsstation Hafen",
        "cost": 500000,
        "category": "handel",
        "research_id": 123,
        "inputs": ["Geldkassette"],
        "outputs": ["Weizen", "Gerste", "Raps", "Sorghum Hirse", "Sonnenblumen", "Sojabohnen", "Mais", "Roggen", "Dinkel", "Zuckerrohr", "Reis", "Langkornreis", "Hackschnitzel", "Erde"]
    },
    {
        "name": "Getreidefabrik",
        "name_de": "Getreidefabrik",
        "cost": 3900000,
        "category": "verarbeitung",
        "research_id": 124,
        "inputs": ["Sorghumhirse", "Sojabohnen", "Hafer", "Mais", "Trauben", "Honig", "Sonnenblumen", "Sonnenblumenöl", "Rapsöl", "Zucker", "Langkornreis", "Rosinen", "Hafermilch", "Haferflocken", "Leerpalette", "Papierrolle", "Kartonrolle", "Wartung", "Strom", "Wasser"],
        "outputs": ["Hirsebrei", "Rosinen", "Hafermilch", "Sojamilch", "Sojaschnetzel", "Popcorn", "Gemüsemais", "Haferflocken", "Müsli", "Reisrolle", "Reissack", "Reisbox", "Schweinefutter", "Alte Leerpalette", "Abwasser"]
    },
    {
        "name": "Gewächshaus Pilze",
        "name_de": "Gewächshaus Pilze",
        "cost": 250000,
        "category": "plantage",
        "research_id": 125,
        "inputs": ["Saatgut", "Kartonrolle", "Leerpalette", "Kompost", "Wartung", "Strom", "Wasser"],
        "outputs": ["Austernpilz", "Enoki", "Pilze"]
    },
    {
        "name": "Gewächshaus",
        "name_de": "Gewächshaus",
        "cost": 250000,
        "category": "plantage",
        "research_id": 126,
        "inputs": ["Leerpalette", "Erde", "Kompost", "Saatgut", "Leere Holzkiste", "Herbizide", "Flüssigdünger", "Wartung", "Strom", "Wasser"],
        "outputs": ["Tomate", "Baumsetzlingspaletten", "Erdbeere", "Himbeere", "Rotkohl", "Rote Zwiebel", "Johannisbeere", "Brombeere", "Blumenkohl", "Kürbis", "Salat", "Knoblauch", "Frühlingslauch", "Chilischoten", "Chinakohl"]
    },
    {
        "name": "Gewächshaus XL",
        "name_de": "Gewächshaus XL",
        "cost": 600000,
        "category": "plantage",
        "research_id": 127,
        "inputs": ["Leerpalette", "Erde", "Kompost", "Saatgut", "Leere Holzkiste", "Herbizide", "Flüssigdünger", "Wartung", "Strom", "Wasser"],
        "outputs": ["Tomate", "Baumsetzlingspaletten", "Erdbeere", "Himbeere", "Rotkohl", "Rote Zwiebel", "Johannisbeere", "Brombeere", "Blumenkohl", "Kürbis", "Salat", "Knoblauch", "Frühlingslauch", "Chilischoten", "Chinakohl"]
    },
    {
        "name": "Glasfabrik",
        "name_de": "Glasfabrik",
        "cost": 1200000,
        "category": "verarbeitung",
        "research_id": 128,
        "inputs": ["Sand", "Methan", "Wartung", "Kartonrolle", "Leerpalette", "Bretterpalette", "Strom", "Wasser", "Altglas"],
        "outputs": ["Flaschen", "Einmachglas", "Glasscheibe"]
    },
    {
        "name": "Heizkraftwerk",
        "name_de": "Heizkraftwerk",
        "cost": 1400000,
        "category": "energie",
        "research_id": 129,
        "inputs": ["Stroh", "Hackschnitzel", "Holzpellets", "Strohpellets", "Methan", "Wartung"],
        "outputs": ["Strom", "Leerpalette", "Alte Leerpalette"]
    },
    {
        "name": "Heutrocknung XL",
        "name_de": "Heutrocknung XL",
        "cost": 300000,
        "category": "verarbeitung",
        "research_id": 130,
        "inputs": ["Gras", "Luzerne", "Kleegras", "Wartung", "Strom", "Diesel"],
        "outputs": ["Heu", "Luzerne Heu", "Kleeheu"]
    },
    {
        "name": "Heutrocknung",
        "name_de": "Heutrocknung",
        "cost": 100000,
        "category": "verarbeitung",
        "research_id": 131,
        "inputs": ["Gras", "Luzerne", "Kleegras", "Strom", "Diesel"],
        "outputs": ["Heu", "Luzerne Heu", "Kleeheu"]
    },
    {
        "name": "Hofladen",
        "name_de": "Hofladen",
        "cost": 120000,
        "category": "verkauf",
        "research_id": 132,
        "inputs": ["Blumenkohl", "Rotkohl", "Salat", "Tomate", "Rote Zwiebel", "Knoblauch", "Chilischoten", "Frühlingslauch", "Chinakohl", "Kürbis", "Erdbeere", "Himbeere", "Brombeere", "Johannisbeere", "Apfel", "Kirsche", "Birne", "Pflaume", "Pfirsich", "Mirabelle", "Kartoffelsack", "Zwiebelsack", "Karottensack", "Pilze", "Enoki", "Austernpilze", "Eier", "Honig"],
        "outputs": ["Geldkassette"]
    },
    {
        "name": "Holzfäller",
        "name_de": "Holzfäller",
        "cost": 155000,
        "category": "produktion",
        "research_id": 133,
        "inputs": ["Leerpalette", "Diesel", "Wartung", "Strom"],
        "outputs": ["Holzstammpalette"]
    },
    {
        "name": "Holzhacker",
        "name_de": "Holzhacker",
        "cost": 290000,
        "category": "verarbeitung",
        "research_id": 134,
        "inputs": ["Holz", "Holzstammpalette", "Diesel", "Wartung"],
        "outputs": ["Hackschnitzel"]
    },
    {
        "name": "Holzkohlefabrik",
        "name_de": "Holzkohlefabrik",
        "cost": 1100000,
        "category": "verarbeitung",
        "research_id": 135,
        "inputs": ["Leerpalette", "Papierrolle", "Methan", "Wartung", "Bretterpalette", "Holzstammpalette", "Strom", "Wasser"],
        "outputs": ["Holzkohle", "Alte Leerpalette"]
    },
    {
        "name": "Imbiss",
        "name_de": "Imbiss",
        "cost": 190000,
        "category": "verkauf",
        "research_id": 136,
        "inputs": ["Pommes", "Wurst", "Rapsöl", "Gerste Flaschenbier", "Weizen Flaschenbier", "Apfelsaft", "Holzkohle", "Wartung", "Strom", "Ketchup", "Mayo", "Senf"],
        "outputs": ["Geldkassette"]
    },
    {
        "name": "Kalkwerk",
        "name_de": "Kalkwerk",
        "cost": 500000,
        "category": "rohstoffe",
        "research_id": 137,
        "inputs": ["Diesel", "Wartung", "Strom", "Wasser", "Steine", "Muscheln", "Kies"],
        "outputs": ["Kalk"]
    },
    {
        "name": "Kartoffelfabrik",
        "name_de": "Kartoffelfabrik",
        "cost": 1700000,
        "category": "verarbeitung",
        "research_id": 138,
        "inputs": ["Kartoffeln", "Eier", "Rapsöl", "Sonnenblumenöl", "Rote Zwiebel", "Rundballennetz", "Kartonrolle", "Papierrolle", "Leerpalette", "Wartung", "Wasser", "Strom"],
        "outputs": ["Pommes", "Chips", "Kroketten", "Reibekuchen", "Kartoffelsack", "Schweinefutter", "Abwasser", "Kompost"]
    },
    {
        "name": "Ketchup-Mayo-Senffabrik",
        "name_de": "Ketchup-Mayo-Senffabrik",
        "cost": 1600000,
        "category": "verarbeitung",
        "research_id": 139,
        "inputs": ["Tomaten", "Eier", "Senf", "Zucker", "Essig", "Leerpalette", "Einmachglas", "Rapsöl", "Sonnenblumenöl", "Rote Zwiebel", "Flaschen", "Papierrolle", "Kartonrolle", "Wartung", "Strom", "Wasser"],
        "outputs": ["Ketchup", "Tomatensauce", "Mayonnaise", "Senf", "Kompost", "Alte Leerpalette", "Abwasser"]
    },
    {
        "name": "Kieswerk",
        "name_de": "Kieswerk",
        "cost": 1400000,
        "category": "rohstoffe",
        "research_id": 140,
        "inputs": ["Diesel", "Wartung", "Strom"],
        "outputs": ["Kies", "Sand", "Steine", "Erde"]
    },
    {
        "name": "Klärwerk",
        "name_de": "Klärwerk",
        "cost": 600000,
        "category": "infrastruktur",
        "research_id": 141,
        "inputs": ["Gülle", "Gärreste", "Abwasser", "Wartung", "Strom"],
        "outputs": ["Wasser", "Kompost", "Gülle"]
    },
    {
        "name": "Abwasserproduktion",
        "name_de": "Abwasserproduktion",
        "cost": 25000,
        "category": "infrastruktur",
        "research_id": 142,
        "inputs": ["Wartung"],
        "outputs": ["Abwasser"]
    },
    {
        "name": "Komposter",
        "name_de": "Komposter",
        "cost": 390000,
        "category": "verarbeitung",
        "research_id": 143,
        "inputs": ["Gras", "Luzerne", "Stroh", "Mist", "Häckselgut", "Kleegras", "Hülsenfruchtstroh", "Diesel", "Wartung"],
        "outputs": ["Kompost"]
    },
    {
        "name": "Käserei",
        "name_de": "Käserei",
        "cost": 1900000,
        "category": "verarbeitung",
        "research_id": 144,
        "inputs": ["Milch", "Büffelmilch", "Ziegenmilch", "Leerpalette", "Knoblauch", "Frühlingslauch", "Kartonrolle", "Holzkiste", "Wartung", "Strom", "Wasser"],
        "outputs": ["Käse", "Büffelmozzarella", "Ziegenkäse", "Frischkäse", "Mozzarella", "Molke", "Abwasser"]
    },
    {
        "name": "Labor",
        "name_de": "Labor",
        "cost": 1200000,
        "category": "verarbeitung",
        "research_id": 145,
        "inputs": ["Melasse", "Milch", "Ziegenmilch", "Kartonrolle", "Leerpalette", "Strom", "Wasser", "Wartung"],
        "outputs": ["Hefe", "Siliermittel", "Alte Leerpalette", "Abwasser"]
    },
    {
        "name": "Lebensmittelfabrik",
        "name_de": "Lebensmittelfabrik",
        "cost": 2800000,
        "category": "verarbeitung",
        "research_id": 146,
        "inputs": ["Mehl", "Rote Zwiebeln", "Kartoffelsack", "Olivenöl", "Maisöl", "Zucker", "Rindfleisch", "Wurst", "Tomatensauce", "Hefe", "Pommes", "Schollenfilet", "Käse", "Mozzarella", "Büffelmozzarella", "Rotkohl", "Apfel", "Wasser", "Strom", "Wartung", "Leerpalette", "Kartonrolle", "Papierrolle"],
        "outputs": ["Pizza", "Fertigmahlzeit", "Fisch und Pommes", "Kompost", "Abwasser", "Alte Leerpalette"]
    },
    {
        "name": "Malzfabrik",
        "name_de": "Malzfabrik",
        "cost": 800000,
        "category": "verarbeitung",
        "research_id": 147,
        "inputs": ["Weizen", "Sommerweizen", "Gerste", "Sommergerste", "Sorghumhirse", "Leerpalette", "Wasser", "Strom", "Wartung"],
        "outputs": ["Weizenmalz", "Gerstenmalz", "Hirsemalz", "Schweinefutter", "Abwasser"]
    },
    {
        "name": "Marmeladenfabrik",
        "name_de": "Marmeladenfabrik",
        "cost": 1900000,
        "category": "verarbeitung",
        "research_id": 148,
        "inputs": ["Apfel", "Kirsche", "Pflaume", "Mirabelle", "Erdbeere", "Himbeere", "Brombeere", "Pfirsich", "Johannisbeere", "Zucker", "Einmachglas", "Leerpalette", "Papierrolle", "Wartung", "Kartonrolle", "Strom", "Wasser"],
        "outputs": ["Apfelmus", "Pflaumenmus", "Erdbeermarmelade", "Himbeermarmelade", "Kirschmarmelade", "Pfirsichmarmelade", "Brombeergelee", "Mirabellenmarmelade", "Kompost", "Alte Leerpalette", "Abwasser"]
    },
    {
        "name": "Mehlfabrik",
        "name_de": "Mehlfabrik",
        "cost": 1500000,
        "category": "verarbeitung",
        "research_id": 149,
        "inputs": ["Weizen", "Sommerweizen", "Dinkel", "Roggen", "Buchweizen", "Reis", "Langkornreis", "Leerpalette", "Strom", "Wartung"],
        "outputs": ["Weizenmehl", "Dinkelmehl", "Roggenmehl", "Buchweizenmehl", "Reismehl", "Kleie"]
    },
    {
        "name": "Molkerei",
        "name_de": "Molkerei",
        "cost": 1600000,
        "category": "verarbeitung",
        "research_id": 150,
        "inputs": ["Milch", "Büffelmilch", "Ziegenmilch", "Leerpalette", "Papierpalette", "Kartonpalette", "Wartung", "Wasser", "Strom"],
        "outputs": ["Vollmilch", "Quark", "Sahne", "Butter", "Joghurt", "Büffelmilch", "Ziegenmilch", "Abwasser"]
    },
    {
        "name": "Mosterei",
        "name_de": "Mosterei",
        "cost": 1800000,
        "category": "verarbeitung",
        "research_id": 151,
        "inputs": ["Apfel", "Birne", "Kirsche", "Pflaume", "Trauben", "Tomaten", "Karotten", "Zucker", "Wartung", "Flaschen", "Papierrolle", "Kartonrolle", "Leerpalette", "Wasser", "Strom"],
        "outputs": ["Apfelsaft", "Birnensaft", "Kirschsaft", "Pflaumensaft", "Traubensaft", "Tomatensaft", "Karottensaft", "Essig", "Schweinefutter", "Alte Leerpalette", "Strom"]
    },
    {
        "name": "Nudelfabrik",
        "name_de": "Nudelfabrik",
        "cost": 1200000,
        "category": "verarbeitung",
        "research_id": 152,
        "inputs": ["Mehl", "Rapsöl", "Eier", "Rindfleisch", "Wartung", "Papierrolle", "Kartonrolle", "Leerpalette", "Wasser", "Strom"],
        "outputs": ["Nudeln", "Spaghetti", "Tortelloni", "Alte Leerpalette", "Abwasser"]
    },
    {
        "name": "Obstplantagen",
        "name_de": "Obstplantage",
        "cost": 500000,
        "category": "plantage",
        "research_id": 153,
        "inputs": ["Kompost", "Leerpalette", "Wartung", "Kartonrolle", "Rindenmulch", "Strom", "Mineraldünger", "Herbizid", "Bienenstock", "Wasser"],
        "outputs": ["Apfel", "Kirsche", "Birne", "Pflaume", "Pfirsich", "Mirabelle", "Honig"]
    },
    {
        "name": "Pelletsfabrik",
        "name_de": "Pelletsfabrik",
        "cost": 1700000,
        "category": "verarbeitung",
        "research_id": 154,
        "inputs": ["Hackschnitzel", "Stroh", "Heu", "Luzerne Heu", "Kleeheu", "Leerpaletten", "Melasse", "Strom", "Wartung"],
        "outputs": ["Holzpellets", "Strohpellets", "Heupellets"]
    },
    {
        "name": "Herbizidproduktion",
        "name_de": "Herbizidproduktion",
        "cost": 350000,
        "category": "verarbeitung",
        "research_id": 155,
        "inputs": ["Tabak", "Kalk", "Wartung", "Wasser", "Strom"],
        "outputs": ["Herbizid", "Kompost"]
    },
    {
        "name": "Recyclingcenter",
        "name_de": "Recyclingcenter",
        "cost": 50000,
        "category": "infrastruktur",
        "research_id": 156,
        "inputs": ["Strom", "Wartung"],
        "outputs": ["Altglas", "Alte Leerpalette"]
    },
    {
        "name": "Raffinerie",
        "name_de": "Raffinerie",
        "cost": 1400000,
        "category": "verarbeitung",
        "research_id": 157,
        "inputs": ["Raps", "Sonnenblumen", "Rohöl", "Strom", "Wartung"],
        "outputs": ["Diesel", "Schweinefutter"]
    },
    {
        "name": "Räucherei",
        "name_de": "Räucherei",
        "cost": 1200000,
        "category": "verarbeitung",
        "research_id": 158,
        "inputs": ["Lachs", "Forelle", "Aal", "Schweinefleisch", "Holzkohle", "Hackschnitzel", "Leerpalette", "Kartonrolle", "Papierrolle", "Wartung", "Strom", "Wasser"],
        "outputs": ["Geräucherter Lachs", "Geräucherte Forelle", "Geräucherter Aal", "Schinken", "Kompost", "Abwasser", "Alte Leerpalette"]
    },
    {
        "name": "Rübenschnitzel",
        "name_de": "Rübenschnitzel",
        "cost": 220000,
        "category": "verarbeitung",
        "research_id": 159,
        "inputs": ["Zuckerrüben", "Diesel", "Wartung"],
        "outputs": ["Zuckerrübenschnitzel"]
    },
    {
        "name": "Saatgutherstellung",
        "name_de": "Saatgutherstellung",
        "cost": 250000,
        "category": "verarbeitung",
        "research_id": 160,
        "inputs": ["Weizen", "Sommerweizen", "Roggen", "Triticale", "Mais", "Ackerbohnen", "Ackergras", "Mineraldünger", "Wartung", "Strom"],
        "outputs": ["Saatgut"]
    },
    {
        "name": "Sägewerk",
        "name_de": "Sägewerk",
        "cost": 1400000,
        "category": "verarbeitung",
        "research_id": 161,
        "inputs": ["Holz", "Holzstammpalette", "Strom", "Wartung", "Hackschnitzel"],
        "outputs": ["Leerpalette", "Bretterpalette", "OSB-Platte", "Spanplatte", "Holzbalken", "Lange Bretter", "Hackschnitzel", "Rindenmulch"]
    },
    {
        "name": "Schlachter",
        "name_de": "Schlachter",
        "cost": 1600000,
        "category": "verarbeitung",
        "research_id": 162,
        "inputs": ["Rinder", "Wasserbüffel", "Lämmer", "Schweine", "Huhn", "Ente", "Leerpalette", "Kartonrolle", "Crushed Ice", "Wartung", "Strom", "Wasser"],
        "outputs": ["Rindfleisch", "Schweinefleisch", "Hühnerfleisch", "Wurst", "Lammfleisch", "Daunen", "Wolle", "Leder", "Nebenprodukt", "Abwasser"]
    },
    {
        "name": "Separator",
        "name_de": "Separator",
        "cost": 200000,
        "category": "verarbeitung",
        "research_id": 163,
        "inputs": ["Gärreste", "Gülle", "Wartung", "Strom"],
        "outputs": ["Herbizid", "Mist", "Abwasser"]
    },
    {
        "name": "Textilfabrik",
        "name_de": "Textilfabrik",
        "cost": 2900000,
        "category": "verarbeitung",
        "research_id": 164,
        "inputs": ["Wolle", "Baumwolle", "Hanfschwad", "Flachs", "Stroh", "Hülsenfruchtstroh", "Wartung", "Strom", "Wasser", "Kartonrolle", "Bretterpalette", "Leerpalette", "Stoff", "Daunen"],
        "outputs": ["Stoff", "Kleidung", "Schuhe", "Holzklotzen", "Strohhut", "Bindegarn für Quaderballen", "Rundballennetz", "Seil", "Abwasser"]
    },
    {
        "name": "Tischlerei",
        "name_de": "Tischlerei",
        "cost": 2200000,
        "category": "verarbeitung",
        "research_id": 165,
        "inputs": ["Bretterpalette", "Lange Bretter", "Holzbalken", "Glasscheibe", "OSB-Platte", "Isolierung", "Leerpalette", "Alte Leerpalette", "Wartung", "Strom"],
        "outputs": ["Holzkiste", "Holzfass", "Leerpalette", "Bienenstock", "Möbel", "Fenster", "Fertigwand", "Hackschnitzel", "Badewanne", "Eimer", "Alte Leerpalette"]
    },
    {
        "name": "Werkstatt",
        "name_de": "Werkstatt",
        "cost": 175000,
        "category": "infrastruktur",
        "research_id": 166,
        "inputs": ["Diesel", "Strom", "Bretterpalette", "Brot", "Käse"],
        "outputs": ["Wartung"]
    },
    {
        "name": "Zellstofffabrik",
        "name_de": "Zellstofffabrik",
        "cost": 1800000,
        "category": "verarbeitung",
        "research_id": 167,
        "inputs": ["Hackschnitzel", "Leerpaletten", "Flachs", "Hanfschwad", "Wartung", "Strom", "Wasser"],
        "outputs": ["Papierrolle", "Kartonrolle", "Klopapier", "Isolierung", "Abwasser"]
    },
    {
        "name": "Zementfabrik",
        "name_de": "Zementfabrik",
        "cost": 2200000,
        "category": "verarbeitung",
        "research_id": 168,
        "inputs": ["Kies", "Sand", "Leerpalette", "Papierrolle", "Wasser", "Wartung", "Strom"],
        "outputs": ["Zement", "Betonziegel", "Pflastersteine Grau", "Pflastersteine Rot", "Randsteine", "Dachplatte", "Abwasser"]
    },
    {
        "name": "Zuckerfabrik",
        "name_de": "Zuckerfabrik",
        "cost": 1600000,
        "category": "verarbeitung",
        "research_id": 169,
        "inputs": ["Zuckerrüben", "Sorghum", "Zuckerrohr", "Leerpaletten", "Wartung", "Strom", "Wasser"],
        "outputs": ["Zucker", "Melasse", "Zuckerrübenschnitzel", "Kompost", "Kalk", "Abwasser"]
    },
    {
        "name": "Ölmühle",
        "name_de": "Ölmühle",
        "cost": 3000000,
        "category": "verarbeitung",
        "research_id": 170,
        "inputs": ["Sonnenblumen", "Raps", "Mais", "Oliven", "Leinsamen", "Lavendel", "Soja", "Hanf", "Mohn", "Kürbiskerne", "Reis", "Langkornreis", "Papierrolle", "Kartonrolle", "Leerpalette", "Wartung", "Strom"],
        "outputs": ["Sonnenblumenöl", "Rapsöl", "Maisöl", "Olivenöl", "Leinsamenöl", "Lavendelöl", "Sojaöl", "Hanföl", "Mohnöl", "Kürbiskernöl", "Reisöl", "Schweinefutter"]
    },
]

# Alle einzigartigen Produkte sammeln
def collect_all_products():
    products = set()
    for prod in PRODUCTIONS:
        for item in prod["inputs"]:
            products.add(item)
        for item in prod["outputs"]:
            products.add(item)
    return sorted(products)

# Verkaufsstellen definieren (da keine in der PDF aufgelistet)
SELLING_POINTS = [
    {
        "name": "Supermarkt Tobi",
        "name_de": "Supermarkt Tobi",
        "accepts": ["Brot", "Brötchen", "Backwaren", "Kuchen", "Vollmilch", "Butter", "Käse", "Joghurt", "Sahne", "Quark", "Wurst", "Schinken", "Eier", "Honig", "Zucker", "Mehl", "Nudeln", "Spaghetti", "Ketchup", "Mayonnaise", "Senf", "Marmelade", "Apfelmus", "Müsli", "Haferflocken", "Chips", "Popcorn", "Klopapier", "Pizza", "Fertigmahlzeit"]
    },
    {
        "name": "Landhandel Markus",
        "name_de": "Landhandel Markus",
        "accepts": ["Weizen", "Gerste", "Roggen", "Mais", "Raps", "Sonnenblumen", "Kartoffeln", "Zuckerrüben", "Stroh", "Heu", "Silage", "Saatgut", "Mineraldünger", "Flüssigdünger", "Herbizid", "Kalk"]
    },
    {
        "name": "Baumarkt Stefan",
        "name_de": "Baumarkt Stefan",
        "accepts": ["Holzbalken", "Bretterpalette", "OSB-Platte", "Spanplatte", "Lange Bretter", "Zement", "Betonziegel", "Pflastersteine Grau", "Pflastersteine Rot", "Randsteine", "Dachplatte", "Glasscheibe", "Fenster", "Fertigwand", "Möbel", "Badewanne", "Isolierung"]
    },
    {
        "name": "Getränkemarkt Lisa",
        "name_de": "Getränkemarkt Lisa",
        "accepts": ["Apfelsaft", "Birnensaft", "Kirschsaft", "Pflaumensaft", "Traubensaft", "Tomatensaft", "Karottensaft", "Weizenbierfass", "Gerstenbierfass", "Hirsebierfass", "Gerste Flaschenbier", "Weizen Flaschenbier", "Malzbier", "Obstler", "Korn", "Whiskey", "Wodka", "Eierlikör", "Erdbeerlimes", "Himbeerlimes", "Rotwein", "Sojamilch", "Hafermilch"]
    },
    {
        "name": "Tankstelle Thomas",
        "name_de": "Tankstelle Thomas",
        "accepts": ["Diesel", "Rohöl", "Methan"]
    },
    {
        "name": "Tierfutterhandel Anna",
        "name_de": "Tierfutterhandel Anna",
        "accepts": ["Schweinefutter", "Mischration", "Mineralfutter", "Fischfutter", "Hundefutter", "Katzenfutter", "Vogelfutter", "Heu", "Stroh", "Silage"]
    },
    {
        "name": "Fischmarkt Klaus",
        "name_de": "Fischmarkt Klaus",
        "accepts": ["Scholle", "Hering", "Krabbe", "Hummer", "Lachs", "Forelle", "Aal", "Dorsch", "Muscheln", "Geräucherter Lachs", "Geräucherte Forelle", "Geräucherter Aal", "Schollenfilet", "Dorschfilet", "Hering in Tomatensauce", "Krabbensalat", "Fischmehl"]
    },
    {
        "name": "Obsthandel Maria",
        "name_de": "Obsthandel Maria",
        "accepts": ["Apfel", "Birne", "Kirsche", "Pflaume", "Pfirsich", "Mirabelle", "Erdbeere", "Himbeere", "Brombeere", "Johannisbeere", "Trauben", "Oliven"]
    },
    {
        "name": "Gemüsehandel Peter",
        "name_de": "Gemüsehandel Peter",
        "accepts": ["Kartoffeln", "Karotten", "Zwiebeln", "Tomaten", "Salat", "Kürbis", "Spinat", "Blumenkohl", "Rotkohl", "Chinakohl", "Knoblauch", "Frühlingslauch", "Chilischoten", "Pilze", "Austernpilz", "Enoki", "Karottensack", "Zwiebelsack", "Kartoffelsack"]
    },
    {
        "name": "Textilhandel Sandra",
        "name_de": "Textilhandel Sandra",
        "accepts": ["Stoff", "Kleidung", "Schuhe", "Strohhut", "Wolle", "Baumwolle", "Leder", "Daunen"]
    }
]

# Forschungskategorien
RESEARCH_CATEGORIES = {
    "rohstoffe": {"name": "Rohstoffgewinnung", "base_level": 5},
    "verarbeitung": {"name": "Verarbeitung", "base_level": 3},
    "plantage": {"name": "Plantagenbau", "base_level": 4},
    "tierhaltung": {"name": "Tierhaltung", "base_level": 6},
    "produktion": {"name": "Produktion", "base_level": 5},
    "verkauf": {"name": "Verkauf", "base_level": 2},
    "handel": {"name": "Handel", "base_level": 4},
    "energie": {"name": "Energieversorgung", "base_level": 8},
    "infrastruktur": {"name": "Infrastruktur", "base_level": 2},
    "landwirtschaft": {"name": "Landwirtschaft", "base_level": 3}
}

def generate_sql():
    """Generiert die SQL-Migration"""

    sql_lines = []
    sql_lines.append("-- ============================================")
    sql_lines.append("-- Produktionssystem - Automatisch generiert")
    sql_lines.append(f"-- Generiert am: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("SET NAMES utf8mb4;")
    sql_lines.append("")

    # ============================================
    # 1. PRODUKTE TABELLE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- PRODUKTE")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS products (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    name VARCHAR(100) NOT NULL UNIQUE,")
    sql_lines.append("    name_de VARCHAR(100) NOT NULL,")
    sql_lines.append("    category VARCHAR(50) DEFAULT 'allgemein',")
    sql_lines.append("    icon VARCHAR(100) DEFAULT NULL,")
    sql_lines.append("    base_price DECIMAL(10,2) DEFAULT 100.00,")
    sql_lines.append("    is_crop BOOLEAN DEFAULT FALSE,")
    sql_lines.append("    required_research_id INT DEFAULT NULL,")
    sql_lines.append("    description TEXT,")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
    sql_lines.append(");")
    sql_lines.append("")

    # Produkte einfügen
    all_products = collect_all_products()

    # Kategorisierung der Produkte
    crop_products = ["Weizen", "Gerste", "Roggen", "Mais", "Raps", "Sonnenblumen", "Kartoffeln",
                     "Zuckerrüben", "Sojabohnen", "Hafer", "Dinkel", "Triticale", "Buchweizen",
                     "Reis", "Langkornreis", "Sorghum", "Sorghumhirse", "Zuckerrohr", "Tabak",
                     "Baumwolle", "Hanf", "Flachs", "Mohn", "Lavendel", "Leinsamen", "Senf",
                     "Ackerbohnen", "Ackererbsen", "Ackergras", "Gras", "Luzerne", "Kleegras",
                     "Karotten", "Möhren", "Zwiebeln", "Spinat", "Pastinaken", "Rote Beete"]

    animal_products = ["Milch", "Büffelmilch", "Ziegenmilch", "Eier", "Wolle", "Daunen",
                       "Gülle", "Mist", "Rinder", "Wasserbüffel", "Schweine", "Lämmer",
                       "Huhn", "Ente"]

    for idx, product in enumerate(all_products, start=1):
        category = "allgemein"
        is_crop = "FALSE"
        base_price = 100.00

        if product in crop_products:
            category = "feldfrucht"
            is_crop = "TRUE"
            base_price = 80.00
        elif product in animal_products:
            category = "tierprodukt"
            base_price = 150.00
        elif "öl" in product.lower() or "Öl" in product:
            category = "oel"
            base_price = 200.00
        elif "fleisch" in product.lower() or "Fleisch" in product:
            category = "fleisch"
            base_price = 300.00
        elif "saft" in product.lower() or "Saft" in product:
            category = "getraenk"
            base_price = 120.00
        elif "bier" in product.lower() or "Bier" in product:
            category = "getraenk"
            base_price = 180.00
        elif "mehl" in product.lower() or "Mehl" in product:
            category = "mehl"
            base_price = 90.00

        safe_name = product.replace("'", "''")
        icon_name = product.lower().replace(" ", "_").replace("ä", "ae").replace("ö", "oe").replace("ü", "ue").replace("ß", "ss")

        sql_lines.append(f"INSERT IGNORE INTO products (name, name_de, category, icon, base_price, is_crop) VALUES ('{safe_name}', '{safe_name}', '{category}', '{icon_name}.png', {base_price}, {is_crop});")

    sql_lines.append("")

    # ============================================
    # 2. PRODUKTIONEN TABELLE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- PRODUKTIONEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS productions (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    name VARCHAR(100) NOT NULL UNIQUE,")
    sql_lines.append("    name_de VARCHAR(100) NOT NULL,")
    sql_lines.append("    category VARCHAR(50) NOT NULL,")
    sql_lines.append("    building_cost DECIMAL(12,2) NOT NULL,")
    sql_lines.append("    maintenance_cost DECIMAL(10,2) DEFAULT 0,")
    sql_lines.append("    production_time INT DEFAULT 3600 COMMENT 'Sekunden pro Zyklus',")
    sql_lines.append("    required_research_id INT DEFAULT NULL,")
    sql_lines.append("    required_level INT DEFAULT 1,")
    sql_lines.append("    icon VARCHAR(100) DEFAULT NULL,")
    sql_lines.append("    description TEXT,")
    sql_lines.append("    is_active BOOLEAN DEFAULT TRUE,")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
    sql_lines.append(");")
    sql_lines.append("")

    for prod in PRODUCTIONS:
        safe_name = prod["name"].replace("'", "''")
        safe_name_de = prod["name_de"].replace("'", "''")
        icon_name = prod["name"].lower().replace(" ", "_").replace("ä", "ae").replace("ö", "oe").replace("ü", "ue").replace("ß", "ss")

        # Berechne benötigtes Level basierend auf Kosten
        req_level = max(1, min(50, int(prod["cost"] / 100000)))

        # Wartungskosten = 0.1% der Baukosten pro Stunde
        maintenance = prod["cost"] * 0.001

        sql_lines.append(f"INSERT IGNORE INTO productions (name, name_de, category, building_cost, maintenance_cost, required_research_id, required_level, icon) VALUES ('{safe_name}', '{safe_name_de}', '{prod['category']}', {prod['cost']}, {maintenance:.2f}, {prod['research_id']}, {req_level}, '{icon_name}.png');")

    sql_lines.append("")

    # ============================================
    # 3. PRODUKTIONS-INPUTS TABELLE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- PRODUKTIONS-INPUTS")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS production_inputs (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    production_id INT NOT NULL,")
    sql_lines.append("    product_id INT NOT NULL,")
    sql_lines.append("    quantity INT DEFAULT 1,")
    sql_lines.append("    is_optional BOOLEAN DEFAULT FALSE,")
    sql_lines.append("    FOREIGN KEY (production_id) REFERENCES productions(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,")
    sql_lines.append("    UNIQUE KEY unique_input (production_id, product_id)")
    sql_lines.append(");")
    sql_lines.append("")

    for prod in PRODUCTIONS:
        safe_prod_name = prod["name"].replace("'", "''")
        for inp in prod["inputs"]:
            safe_inp = inp.replace("'", "''")
            sql_lines.append(f"INSERT IGNORE INTO production_inputs (production_id, product_id, quantity) SELECT p.id, pr.id, 1 FROM productions p, products pr WHERE p.name = '{safe_prod_name}' AND pr.name = '{safe_inp}';")

    sql_lines.append("")

    # ============================================
    # 4. PRODUKTIONS-OUTPUTS TABELLE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- PRODUKTIONS-OUTPUTS")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS production_outputs (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    production_id INT NOT NULL,")
    sql_lines.append("    product_id INT NOT NULL,")
    sql_lines.append("    quantity INT DEFAULT 1,")
    sql_lines.append("    probability DECIMAL(3,2) DEFAULT 1.00 COMMENT '0.00-1.00 Wahrscheinlichkeit',")
    sql_lines.append("    FOREIGN KEY (production_id) REFERENCES productions(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,")
    sql_lines.append("    UNIQUE KEY unique_output (production_id, product_id)")
    sql_lines.append(");")
    sql_lines.append("")

    for prod in PRODUCTIONS:
        safe_prod_name = prod["name"].replace("'", "''")
        for out in prod["outputs"]:
            safe_out = out.replace("'", "''")
            sql_lines.append(f"INSERT IGNORE INTO production_outputs (production_id, product_id, quantity) SELECT p.id, pr.id, 1 FROM productions p, products pr WHERE p.name = '{safe_prod_name}' AND pr.name = '{safe_out}';")

    sql_lines.append("")

    # ============================================
    # 5. FORSCHUNGSKNOTEN
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- FORSCHUNGSKNOTEN FUER PRODUKTIONEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")

    # Erweitere den ENUM um neue Kategorien
    sql_lines.append("-- Erweitere research_tree Kategorien")
    sql_lines.append("ALTER TABLE research_tree MODIFY COLUMN category ENUM('crops', 'animals', 'vehicles', 'buildings', 'efficiency', 'production') NOT NULL;")
    sql_lines.append("")

    for prod in PRODUCTIONS:
        safe_name = f"Produktion: {prod['name_de']}".replace("'", "''")
        cat_info = RESEARCH_CATEGORIES.get(prod["category"], {"base_level": 5})
        req_level = max(1, min(50, int(prod["cost"] / 150000) + cat_info["base_level"]))
        research_cost = int(prod["cost"] * 0.1)  # 10% der Baukosten als Forschungskosten
        research_time = max(1, int(prod["cost"] / 500000))  # Stunden

        sql_lines.append(f"INSERT IGNORE INTO research_tree (id, name, description, cost, research_time_hours, level_required, category) VALUES ({prod['research_id']}, '{safe_name}', 'Schaltet die Produktion {prod['name_de']} frei.', {research_cost}, {research_time}, {req_level}, 'production');")

    sql_lines.append("")

    # ============================================
    # 6. VERKAUFSSTELLEN
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- VERKAUFSSTELLEN")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS selling_points (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    name VARCHAR(100) NOT NULL UNIQUE,")
    sql_lines.append("    name_de VARCHAR(100) NOT NULL,")
    sql_lines.append("    icon VARCHAR(100) DEFAULT NULL,")
    sql_lines.append("    price_modifier DECIMAL(3,2) DEFAULT 1.00 COMMENT 'Preismultiplikator',")
    sql_lines.append("    is_active BOOLEAN DEFAULT TRUE,")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")
    sql_lines.append(");")
    sql_lines.append("")

    for sp in SELLING_POINTS:
        safe_name = sp["name"].replace("'", "''")
        safe_name_de = sp["name_de"].replace("'", "''")
        icon_name = sp["name"].lower().replace(" ", "_").replace("ä", "ae").replace("ö", "oe").replace("ü", "ue").replace("ß", "ss")
        sql_lines.append(f"INSERT IGNORE INTO selling_points (name, name_de, icon) VALUES ('{safe_name}', '{safe_name_de}', '{icon_name}.png');")

    sql_lines.append("")

    # Verkaufsstellen-Produkte Zuordnung
    sql_lines.append("CREATE TABLE IF NOT EXISTS selling_point_products (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    selling_point_id INT NOT NULL,")
    sql_lines.append("    product_id INT NOT NULL,")
    sql_lines.append("    price_modifier DECIMAL(3,2) DEFAULT 1.00,")
    sql_lines.append("    FOREIGN KEY (selling_point_id) REFERENCES selling_points(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,")
    sql_lines.append("    UNIQUE KEY unique_sp_product (selling_point_id, product_id)")
    sql_lines.append(");")
    sql_lines.append("")

    for sp in SELLING_POINTS:
        safe_sp_name = sp["name"].replace("'", "''")
        for prod_name in sp["accepts"]:
            safe_prod_name = prod_name.replace("'", "''")
            sql_lines.append(f"INSERT IGNORE INTO selling_point_products (selling_point_id, product_id) SELECT sp.id, p.id FROM selling_points sp, products p WHERE sp.name = '{safe_sp_name}' AND p.name = '{safe_prod_name}';")

    sql_lines.append("")

    # ============================================
    # 7. FARM-PRODUKTIONEN (Spieler-Instanzen)
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- FARM-PRODUKTIONEN (Spieler-Instanzen)")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("CREATE TABLE IF NOT EXISTS farm_productions (")
    sql_lines.append("    id INT AUTO_INCREMENT PRIMARY KEY,")
    sql_lines.append("    farm_id INT NOT NULL,")
    sql_lines.append("    production_id INT NOT NULL,")
    sql_lines.append("    level INT DEFAULT 1,")
    sql_lines.append("    is_active BOOLEAN DEFAULT TRUE,")
    sql_lines.append("    last_production_at TIMESTAMP NULL,")
    sql_lines.append("    total_produced INT DEFAULT 0,")
    sql_lines.append("    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,")
    sql_lines.append("    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,")
    sql_lines.append("    FOREIGN KEY (production_id) REFERENCES productions(id) ON DELETE CASCADE,")
    sql_lines.append("    UNIQUE KEY unique_farm_production (farm_id, production_id)")
    sql_lines.append(");")
    sql_lines.append("")

    # ============================================
    # 8. INVENTAR-ERWEITERUNG
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- INVENTAR-ERWEITERUNG FÜR PRODUKTE")
    sql_lines.append("-- ============================================")
    sql_lines.append("")
    sql_lines.append("ALTER TABLE inventory ADD COLUMN IF NOT EXISTS product_id INT DEFAULT NULL;")
    sql_lines.append("ALTER TABLE inventory ADD COLUMN IF NOT EXISTS product_quantity INT DEFAULT 0;")
    sql_lines.append("")

    # ============================================
    # 9. NEUE FELDFRÜCHTE FÜR CROPS TABELLE
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- NEUE FELDFRÜCHTE")
    sql_lines.append("-- ============================================")
    sql_lines.append("")

    new_crops = [
        ("Sorghum Hirse", "sorghum_hirse", 6, 70, 200, 110, 200),
        ("Sojabohnen", "sojabohnen", 5, 85, 250, 95, 201),
        ("Buchweizen", "buchweizen", 5, 60, 175, 100, 202),
        ("Reis", "reis", 8, 90, 280, 85, 203),
        ("Langkornreis", "langkornreis", 9, 100, 300, 80, 204),
        ("Zuckerrohr", "zuckerrohr", 10, 80, 220, 120, 205),
        ("Tabak", "tabak", 12, 150, 420, 70, 206),
        ("Baumwolle", "baumwolle", 8, 120, 350, 90, 207),
        ("Hanf", "hanf", 6, 100, 280, 100, 208),
        ("Flachs", "flachs", 7, 90, 260, 85, 209),
        ("Mohn", "mohn", 6, 130, 380, 60, 210),
        ("Lavendel", "lavendel", 8, 140, 400, 55, 211),
        ("Leinsamen", "leinsamen", 5, 95, 270, 75, 212),
        ("Triticale", "triticale", 5, 55, 160, 115, 213),
        ("Ackerbohnen", "ackerbohnen", 4, 65, 185, 105, 214),
        ("Ackererbsen", "ackererbsen", 4, 60, 170, 110, 215),
        ("Pastinaken", "pastinaken", 6, 70, 195, 95, 216),
        ("Rote Beete", "rote_beete", 5, 65, 180, 100, 217),
        ("Luzerne", "luzerne", 4, 50, 140, 140, 218),
        ("Kleegras", "kleegras", 3, 40, 110, 150, 219),
    ]

    for crop_name, crop_key, growth_h, buy_price, sell_price, yield_per_ha, research_id in new_crops:
        safe_name = crop_name.replace("'", "''")
        sql_lines.append(f"INSERT IGNORE INTO crops (name, growth_time_hours, buy_price, sell_price, yield_per_hectare, required_research_id, water_need, image_url) VALUES ('{safe_name}', {growth_h}, {buy_price}, {sell_price}, {yield_per_ha}, {research_id}, 50, '{crop_key}.png');")

    sql_lines.append("")

    # ============================================
    # 10. TIERHALTUNG
    # ============================================
    sql_lines.append("-- ============================================")
    sql_lines.append("-- TIERHALTUNG")
    sql_lines.append("-- ============================================")
    sql_lines.append("")

    animals = [
        {
            "name": "Hühnerstall",
            "name_de": "Hühnerstall",
            "cost": 50000,
            "category": "tierhaltung",
            "research_id": 180,
            "inputs": ["Weizen", "Mais", "Wasser"],
            "outputs": ["Eier", "Huhn", "Mist"]
        },
        {
            "name": "Entenstall",
            "name_de": "Entenstall",
            "cost": 60000,
            "category": "tierhaltung",
            "research_id": 181,
            "inputs": ["Weizen", "Mais", "Wasser"],
            "outputs": ["Eier", "Ente", "Mist", "Daunen"]
        },
        {
            "name": "Schweinestall",
            "name_de": "Schweinestall",
            "cost": 150000,
            "category": "tierhaltung",
            "research_id": 182,
            "inputs": ["Schweinefutter", "Wasser"],
            "outputs": ["Schweine", "Gülle"]
        },
        {
            "name": "Kuhstall",
            "name_de": "Kuhstall",
            "cost": 200000,
            "category": "tierhaltung",
            "research_id": 183,
            "inputs": ["Mischration", "Heu", "Wasser"],
            "outputs": ["Milch", "Rinder", "Gülle", "Mist"]
        },
        {
            "name": "Schafstall",
            "name_de": "Schafstall",
            "cost": 120000,
            "category": "tierhaltung",
            "research_id": 184,
            "inputs": ["Heu", "Gras", "Wasser"],
            "outputs": ["Wolle", "Lämmer", "Mist"]
        },
        {
            "name": "Ziegenstall",
            "name_de": "Ziegenstall",
            "cost": 100000,
            "category": "tierhaltung",
            "research_id": 185,
            "inputs": ["Heu", "Gras", "Wasser"],
            "outputs": ["Ziegenmilch", "Mist"]
        },
        {
            "name": "Büffelstall",
            "name_de": "Büffelstall",
            "cost": 300000,
            "category": "tierhaltung",
            "research_id": 186,
            "inputs": ["Mischration", "Heu", "Wasser"],
            "outputs": ["Büffelmilch", "Wasserbüffel", "Gülle", "Mist"]
        },
        {
            "name": "Bienenhaus",
            "name_de": "Bienenhaus",
            "cost": 25000,
            "category": "tierhaltung",
            "research_id": 187,
            "inputs": ["Bienenstock"],
            "outputs": ["Honig"]
        }
    ]

    for animal in animals:
        safe_name = animal["name"].replace("'", "''")
        safe_name_de = animal["name_de"].replace("'", "''")
        icon_name = animal["name"].lower().replace(" ", "_").replace("ä", "ae").replace("ö", "oe").replace("ü", "ue").replace("ß", "ss")
        req_level = max(1, min(50, int(animal["cost"] / 50000)))
        maintenance = animal["cost"] * 0.001

        sql_lines.append(f"INSERT IGNORE INTO productions (name, name_de, category, building_cost, maintenance_cost, required_research_id, required_level, icon) VALUES ('{safe_name}', '{safe_name_de}', '{animal['category']}', {animal['cost']}, {maintenance:.2f}, {animal['research_id']}, {req_level}, '{icon_name}.png');")

    sql_lines.append("")

    # Tierhaltung Inputs
    for animal in animals:
        safe_prod_name = animal["name"].replace("'", "''")
        for inp in animal["inputs"]:
            safe_inp = inp.replace("'", "''")
            sql_lines.append(f"INSERT IGNORE INTO production_inputs (production_id, product_id, quantity) SELECT p.id, pr.id, 1 FROM productions p, products pr WHERE p.name = '{safe_prod_name}' AND pr.name = '{safe_inp}';")

    sql_lines.append("")

    # Tierhaltung Outputs
    for animal in animals:
        safe_prod_name = animal["name"].replace("'", "''")
        for out in animal["outputs"]:
            safe_out = out.replace("'", "''")
            sql_lines.append(f"INSERT IGNORE INTO production_outputs (production_id, product_id, quantity) SELECT p.id, pr.id, 1 FROM productions p, products pr WHERE p.name = '{safe_prod_name}' AND pr.name = '{safe_out}';")

    sql_lines.append("")

    # Tierhaltung Forschung
    for animal in animals:
        safe_name = f"Tierhaltung: {animal['name_de']}".replace("'", "''")
        req_level = max(1, int(animal["cost"] / 30000))
        research_cost = int(animal["cost"] * 0.15)
        research_time = max(1, int(animal["cost"] / 100000))

        sql_lines.append(f"INSERT IGNORE INTO research_tree (id, name, description, cost, research_time_hours, level_required, category) VALUES ({animal['research_id']}, '{safe_name}', 'Schaltet {animal['name_de']} frei.', {research_cost}, {research_time}, {req_level}, 'animals');")

    sql_lines.append("")
    sql_lines.append("-- Ende der Migration")

    return "\n".join(sql_lines)


def generate_json():
    """Generiert JSON-Export der Produktionsdaten"""

    data = {
        "generated_at": datetime.now().isoformat(),
        "productions": PRODUCTIONS,
        "selling_points": SELLING_POINTS,
        "products": collect_all_products(),
        "research_categories": RESEARCH_CATEGORIES
    }

    return json.dumps(data, ensure_ascii=False, indent=2)


def main():
    """Hauptfunktion"""

    print("=" * 60)
    print("Produktions-Parser für Farming Simulator Browsergame")
    print("=" * 60)
    print()

    # Statistiken ausgeben
    all_products = collect_all_products()
    print(f"Gefundene Produktionen: {len(PRODUCTIONS)}")
    print(f"Einzigartige Produkte: {len(all_products)}")
    print(f"Verkaufsstellen: {len(SELLING_POINTS)}")
    print()

    # SQL generieren
    print("Generiere SQL-Migration...")
    sql_content = generate_sql()

    sql_path = "../sql/productions_migration.sql"
    # UTF-8 ohne BOM für MySQL-Kompatibilität
    with open(sql_path, "w", encoding="utf-8", newline='\n') as f:
        f.write(sql_content)
    print(f"SQL gespeichert: {sql_path}")

    # JSON generieren
    print("Generiere JSON-Export...")
    json_content = generate_json()

    json_path = "../sql/productions_data.json"
    with open(json_path, "w", encoding="utf-8") as f:
        f.write(json_content)
    print(f"JSON gespeichert: {json_path}")

    print()
    print("=" * 60)
    print("Fertig!")
    print("=" * 60)


if __name__ == "__main__":
    main()