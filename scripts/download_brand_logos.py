#!/usr/bin/env python3
"""
Brand Logo Downloader

Lädt alle Marken-Logos von farming-simulator.com herunter
und organisiert sie in Kategorieordnern.

Verwendung: python scripts/download_brand_logos.py
"""

import os
import json
import time
import urllib.request
import ssl
from datetime import datetime
from pathlib import Path

# Konfiguration
BASE_URL = 'https://www.farming-simulator.com/img/content/brands/'
SCRIPT_DIR = Path(__file__).parent
TARGET_DIR = SCRIPT_DIR.parent / 'public' / 'img' / 'brands'

# Marken nach Kategorien organisiert
CATEGORIES = {
    'fahrzeuge': [
        'abi', 'agco', 'antoniocarraro', 'ape', 'aprilia', 'caseih', 'challenger',
        'claas', 'deutzfahr', 'fendt', 'fiat', 'impex', 'international', 'iseki',
        'jcb', 'johndeere', 'komatsu', 'kubota', 'landini', 'lindner', 'lizard',
        'mack', 'manitou', 'masseyferguson', 'mccormick', 'merlo', 'newholland',
        'pfanzelt', 'ponsse', 'prinoth', 'riedler', 'rigitrac', 'ropa', 'rottne',
        'same', 'schaeffer', 'sennebogen', 'skoda', 'steyr', 'valtra', 'volvo', 'zetor'
    ],

    'erntemaschinen': [
        'agrifac', 'amitytech', 'capello', 'caseih', 'claas', 'dewulf', 'ero',
        'fendt', 'geringhoff', 'gregoire', 'grimme', 'holmer', 'iseki', 'johndeere',
        'kemper', 'krone', 'lacotec', 'macdon', 'masseyferguson', 'newholland',
        'oxbo', 'ropa'
    ],

    'geraete': [
        'abi', 'agco', 'agibatco', 'agistorm', 'agiwestfield', 'agrifac', 'agrio',
        'agrisem', 'agromasz', 'albutt', 'alpego', 'amazone', 'amitytech',
        'andersongroup', 'annaburger', 'arcusin', 'bednar', 'bergmann', 'berthoud',
        'bomech', 'brandt', 'brantner', 'bredal', 'caseih', 'claas', 'conveyall',
        'dalbo', 'damcon', 'demco', 'einboeck', 'elho', 'elmersmfg', 'faresin',
        'farmax', 'farmet', 'farmtech', 'fendt', 'fliegl', 'fuhrmann', 'gessner',
        'gorenc', 'goeweil', 'greatplains', 'grimme', 'hardi', 'hauer', 'hawe',
        'heizomat', 'holaras', 'horsch', 'iseki', 'jenz', 'jmmanufacturing',
        'johndeere', 'jungheinrich', 'kaweco', 'kemper', 'kesla', 'kingston',
        'kinze', 'knoche', 'kockerling', 'koller', 'kotte', 'krampe', 'kroeger',
        'krone', 'kronetrailer', 'kubota', 'kuhn', 'kverneland', 'lemken', 'lizard',
        'lodeking', 'magsi', 'manitou', 'masseyferguson', 'mccormack', 'meridian',
        'mzuri', 'nardi', 'newholland', 'novag', 'oxbo', 'paladin', 'pfanzelt',
        'pittstrailers', 'poettinger', 'prinoth', 'provitis', 'quicke', 'reiter',
        'riedler', 'risutec', 'ropa', 'rudolph', 'salek', 'samasz', 'samsonagro',
        'schuitemaker', 'schwarzmueller', 'sennebogen', 'siloking', 'sip', 'stema',
        'steyr', 'streumaster', 'summersmfg', 'tajfun', 'tenwinkel', 'thundercreek',
        'tmccancela', 'treffler', 'tt', 'unia', 'vaederstad', 'vermeer', 'volvo',
        'walkabout', 'westtech', 'wifo', 'wilson', 'zunhammer'
    ],

    'diverses': [
        'agi', 'agisentinel', 'agiwesteel', 'bkt', 'brielmaier', 'caseih', 'claas',
        'continental', 'corteva', 'dewulf', 'easysheds', 'elten', 'engelbertstrauss',
        'ero', 'fendt', 'groha', 'hardi', 'hella', 'helm', 'rudolfhoermann',
        'husqvarna', 'idagro', 'johndeere', 'jonsered', 'kaercher', 'krone',
        'kubota', 'lely', 'lincoln', 'lizard', 'masseyferguson', 'mcculloch',
        'meridian', 'michelin', 'mitas', 'moescha', 'neuero', 'newholland',
        'nokiantyres', 'pesslinstruments', 'pioneer', 'planet', 'raniplast',
        'stihl', 'trelleborg', 'unia', 'valtra', 'vermeer', 'volvo', 'vredestein',
        'walterscheid'
    ]
}


def download_file(url: str, destination: Path) -> bool:
    """Lädt eine Datei herunter"""
    try:
        # SSL-Kontext ohne Verifikation (für einfachere Kompatibilität)
        ctx = ssl.create_default_context()
        ctx.check_hostname = False
        ctx.verify_mode = ssl.CERT_NONE

        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Accept': 'image/png,image/*,*/*;q=0.8',
            'Referer': 'https://www.farming-simulator.com/about.php'
        }

        request = urllib.request.Request(url, headers=headers)
        with urllib.request.urlopen(request, timeout=30, context=ctx) as response:
            content = response.read()

            # Prüfe ob wir tatsächlich ein Bild erhalten haben
            if len(content) < 100:  # Zu klein für ein echtes Logo
                return False

            with open(destination, 'wb') as f:
                f.write(content)
            return True

    except Exception as e:
        return False


def ensure_directory(path: Path) -> None:
    """Erstellt Verzeichnis falls nicht vorhanden"""
    if not path.exists():
        path.mkdir(parents=True)
        print(f"  Verzeichnis erstellt: {path}")


def main():
    print("=" * 50)
    print("  LSBG Agrar Simulator - Brand Logo Downloader")
    print("=" * 50)
    print()

    # Zielverzeichnis erstellen
    ensure_directory(TARGET_DIR)

    total_downloaded = 0
    total_failed = 0
    total_skipped = 0
    downloaded_brands = set()
    failed_brands = []

    for category, brands in CATEGORIES.items():
        print(f"\n[{category}]")
        print("-" * 40)

        category_dir = TARGET_DIR / category
        ensure_directory(category_dir)

        for brand in brands:
            logo_filename = f"logo-{brand}.png"
            destination = category_dir / logo_filename

            # Prüfe ob Datei bereits existiert
            if destination.exists():
                print(f"  [SKIP] {brand} (bereits vorhanden)")
                total_skipped += 1
                continue

            print(f"  Lade: {brand} ... ", end="", flush=True)

            # Versuche zuerst die "off" Version (Standard-Logo)
            url = f"{BASE_URL}logo-{brand}-off.png"

            if download_file(url, destination):
                print("OK")
                total_downloaded += 1
                downloaded_brands.add(brand)
            else:
                # Versuche alternative URL ohne "-off"
                url_alt = f"{BASE_URL}logo-{brand}.png"
                if download_file(url_alt, destination):
                    print("OK (alt)")
                    total_downloaded += 1
                    downloaded_brands.add(brand)
                else:
                    # Versuche mit "-on" Suffix
                    url_on = f"{BASE_URL}logo-{brand}-on.png"
                    if download_file(url_on, destination):
                        print("OK (on)")
                        total_downloaded += 1
                        downloaded_brands.add(brand)
                    else:
                        print("FEHLGESCHLAGEN")
                        total_failed += 1
                        failed_brands.append(f"{category}/{brand}")

            # Kleine Pause um Server nicht zu überlasten
            time.sleep(0.1)

    # Zusammenfassung
    print()
    print("=" * 50)
    print("  ZUSAMMENFASSUNG")
    print("=" * 50)
    print(f"  Heruntergeladen: {total_downloaded}")
    print(f"  Übersprungen:    {total_skipped}")
    print(f"  Fehlgeschlagen:  {total_failed}")
    print(f"  Zielordner:      {TARGET_DIR}")
    print("=" * 50)

    if failed_brands:
        print("\n  Fehlgeschlagene Marken:")
        for brand in failed_brands[:20]:  # Max 20 anzeigen
            print(f"    - {brand}")
        if len(failed_brands) > 20:
            print(f"    ... und {len(failed_brands) - 20} weitere")

    # Erstelle eine Index-Datei mit allen Marken
    index_file = TARGET_DIR / 'brands_index.json'
    index_data = {
        'generated_at': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
        'source': 'https://www.farming-simulator.com/about.php',
        'categories': CATEGORIES,
        'statistics': {
            'downloaded': total_downloaded,
            'skipped': total_skipped,
            'failed': total_failed
        },
        'failed_brands': failed_brands
    }

    with open(index_file, 'w', encoding='utf-8') as f:
        json.dump(index_data, f, indent=2, ensure_ascii=False)
    print(f"\nIndex-Datei erstellt: {index_file}")

    print("\nFertig!")


if __name__ == '__main__':
    main()
