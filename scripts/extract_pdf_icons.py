#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
PDF Icon Extractor - Extrahiert Grafiken aus Produktionsuebersicht.pdf

Funktionen:
1. Alle eingebetteten Bilder aus der PDF extrahieren
2. Bilder per SHA256-Hash deduplizieren
3. Jede Seite als eine Produktion erkennen (Seitentitel = Produktionsname)
4. Bilder den bekannten DB-Produkten/Produktionen zuordnen
5. In passende Ordner sortieren (products/ oder productions/)
6. Zuordnungsreport generieren
"""

import os
import sys
import hashlib
import json
import re
from pathlib import Path

# Windows-Konsolen-Encoding fix
if sys.platform == "win32":
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")

try:
    import fitz  # PyMuPDF
except ImportError:
    print("PyMuPDF nicht installiert. Installiere mit: pip install PyMuPDF")
    sys.exit(1)

try:
    from PIL import Image
    import io
except ImportError:
    print("Pillow nicht installiert. Installiere mit: pip install Pillow")
    sys.exit(1)

# === Konfiguration ===
BASE_DIR = Path(__file__).resolve().parent.parent
PDF_PATH = BASE_DIR / "Produktionsübersicht.pdf"
PUBLIC_IMG_DIR = BASE_DIR / "public" / "img"
PRODUCTS_DIR = PUBLIC_IMG_DIR / "products"
PRODUCTIONS_DIR = PUBLIC_IMG_DIR / "productions"
MISC_DIR = PUBLIC_IMG_DIR / "misc"
REPORT_PATH = BASE_DIR / "scripts" / "extraction_report.json"

# Umlaut-Ersetzungen
UMLAUT_MAP = {
    'ä': 'ae', 'ö': 'oe', 'ü': 'ue', 'ß': 'ss',
    'Ä': 'Ae', 'Ö': 'Oe', 'Ü': 'Ue',
    'é': 'e', 'è': 'e', 'ê': 'e', 'à': 'a', 'â': 'a',
    'ï': 'i', 'î': 'i', 'ô': 'o', 'û': 'u', 'ù': 'u'
}

# Bekannte Produktnamen (aus products in productions_migration.sql)
# Wird dynamisch aus der SQL-Datei geladen
KNOWN_PRODUCTS = set()
KNOWN_PRODUCTIONS = set()


def load_known_names():
    """Laedt bekannte Produkt- und Produktionsnamen aus der SQL-Datei."""
    global KNOWN_PRODUCTS, KNOWN_PRODUCTIONS

    sql_path = BASE_DIR / "sql" / "productions_migration.sql"
    if not sql_path.exists():
        print(f"WARNUNG: {sql_path} nicht gefunden, verwende Fallback-Listen")
        KNOWN_PRODUCTIONS = {
            "bohrinsel", "brauerei", "brennerei", "baeckerei", "crepes_und_eisverkauf",
            "duengerherstellung", "eisfabrik", "feld_erdbeere", "feld_hopfen",
            "feld_kuerbis", "feld_oliven", "feld_rollrasen", "feld_tannenbaeume",
            "feld_trauben", "fermenter", "fermenter_klein", "fischbude", "fischfabrik",
            "fischer", "fischzucht", "futterfabrik", "futterfabrik_xl", "gemuesefabrik",
            "einkaufsstation_hafen", "getreidefabrik", "gewaechshaus_pilze",
            "gewaechshaus", "gewaechshaus_xl", "glasfabrik", "heizkraftwerk",
            "heutrocknung_xl", "heutrocknung", "hofladen", "holzfaeller", "holzhacker",
            "holzkohlefabrik", "imbiss", "kalkwerk", "kartoffelfabrik",
            "ketchup-mayo-senffabrik", "kieswerk", "klaerwerk", "abwasserproduktion",
            "komposter", "kaeserei", "labor", "lebensmittelfabrik", "malzfabrik",
            "marmeladenfabrik", "mehlfabrik", "molkerei", "mosterei", "nudelfabrik",
            "obstplantagen", "pelletsfabrik", "herbizidproduktion", "recyclingcenter",
            "raffinerie", "raeucherei", "ruebenschnitzel", "saatgutherstellung",
            "saegewerk", "schlachter", "separator", "textilfabrik", "tischlerei",
            "werkstatt", "zellstofffabrik", "zementfabrik", "zuckerfabrik", "oelmuehle"
        }
        return

    content = sql_path.read_text(encoding="utf-8")

    # Produkt-Icons extrahieren: icon-Wert aus INSERT INTO products
    for match in re.finditer(r"INTO products.*?icon,.*?VALUES.*?'([^']+\.png)'", content):
        icon = match.group(1)
        name = icon.replace(".png", "")
        KNOWN_PRODUCTS.add(name)

    # Einfachere Methode: alle icon-Werte direkt parsen
    for match in re.finditer(r"'(\w+\.png)'", content):
        icon = match.group(1)
        name = icon.replace(".png", "")
        if len(name) > 2:
            KNOWN_PRODUCTS.add(name)

    # Produktions-Icons extrahieren
    for match in re.finditer(
        r"INTO productions.*?icon\) VALUES.*?'([^']+\.png)'\)",
        content
    ):
        icon = match.group(1)
        name = icon.replace(".png", "")
        KNOWN_PRODUCTIONS.add(name)

    # Nochmal spezifisch die production INSERT-Zeilen parsen
    for match in re.finditer(
        r"INSERT IGNORE INTO productions.*?'(\w+(?:[-_]\w+)*)\.png'\)",
        content
    ):
        KNOWN_PRODUCTIONS.add(match.group(1))

    print(f"Bekannte Produkte geladen: {len(KNOWN_PRODUCTS)}")
    print(f"Bekannte Produktionen geladen: {len(KNOWN_PRODUCTIONS)}")


def normalize_name(text: str) -> str:
    """Normalisiert einen Text zu einem Dateinamen."""
    name = text.strip()

    # Umlaute ersetzen
    for umlaut, replacement in UMLAUT_MAP.items():
        name = name.replace(umlaut, replacement)

    # Kleinbuchstaben
    name = name.lower()

    # Sonderzeichen durch Unterstrich ersetzen
    name = re.sub(r'[^a-z0-9_\-]', '_', name)

    # Mehrfache Unterstriche reduzieren
    name = re.sub(r'_+', '_', name)

    # Fuehrende/Trailing Unterstriche entfernen
    name = name.strip('_')

    return name


def extract_short_label(text: str) -> str:
    """Extrahiert ein kurzes Label aus einem laengeren Text.

    Beispiel: 'Gerstenmalz  (Schiene Malzbier)' -> 'Gerstenmalz'
              'Strom    Siehe Seite 2' -> 'Strom'
              'Leerpalette    Aus dem Saegewerk' -> 'Leerpalette'
    """
    if not text:
        return ""

    # Entferne alles nach bestimmten Trennwoertern
    for separator in [
        'Siehe Seite', 'Aus dem', 'Aus der', 'Aus einer', 'Aus eigener',
        'Bei der', 'Bei dem', 'Bei einer', 'Bei einem', 'Beim',
        'Von der', 'Von dem', 'Von einer', 'Von einem',
        'Fuer die', 'Fuer den', 'Fuer das', 'F\u00fcr die', 'F\u00fcr den',
        '(Schiene', '(Baukosten', 'Verkauf', 'oder Verkauf',
        'Wichtig,', 'Produktionen k',
    ]:
        idx = text.find(separator)
        if idx > 0:
            text = text[:idx]

    # Nimm nur die erste Zeile / den ersten Satz
    text = text.split('\n')[0]

    # Entferne Klammern und deren Inhalt
    text = re.sub(r'\([^)]*\)', '', text)

    # Whitespace bereinigen
    text = ' '.join(text.split()).strip()

    # Max 50 Zeichen
    if len(text) > 50:
        # Schneide beim letzten Wort-Umbruch ab
        text = text[:50].rsplit(' ', 1)[0]

    return text.strip()


def compute_hash(image_bytes: bytes) -> str:
    """Berechnet SHA256-Hash eines Bildes."""
    return hashlib.sha256(image_bytes).hexdigest()


def extract_page_title(page) -> str:
    """Extrahiert den Seitentitel (groesster/erster Text oben auf der Seite)."""
    text_dict = page.get_text("dict")
    title_candidates = []

    for block in text_dict.get("blocks", []):
        if block.get("type") != 0:
            continue

        # Nur oberer Bereich der Seite (obere 15%)
        page_height = page.rect.height
        if block["bbox"][1] > page_height * 0.15:
            continue

        for line in block.get("lines", []):
            for span in line.get("spans", []):
                text = span.get("text", "").strip()
                font_size = span.get("size", 0)
                if text and len(text) > 2:
                    title_candidates.append({
                        "text": text,
                        "size": font_size,
                        "y": span.get("bbox", [0, 0, 0, 0])[1] if "bbox" in span else block["bbox"][1]
                    })

    if not title_candidates:
        return ""

    # Sortiere nach Schriftgroesse (groesstes zuerst), dann Position (oben zuerst)
    title_candidates.sort(key=lambda x: (-x["size"], x["y"]))

    return title_candidates[0]["text"]


def extract_text_blocks(page) -> list:
    """Extrahiert Textbloecke mit Position von einer PDF-Seite."""
    blocks = []
    text_dict = page.get_text("dict")

    for block in text_dict.get("blocks", []):
        if block.get("type") == 0:  # Textblock
            text = ""
            for line in block.get("lines", []):
                for span in line.get("spans", []):
                    text += span.get("text", "")
                text += " "

            text = text.strip()
            if text and len(text) > 1:
                bbox = block.get("bbox", (0, 0, 0, 0))
                blocks.append({
                    "text": text,
                    "x0": bbox[0],
                    "y0": bbox[1],
                    "x1": bbox[2],
                    "y1": bbox[3],
                    "cx": (bbox[0] + bbox[2]) / 2,
                    "cy": (bbox[1] + bbox[3]) / 2
                })

    return blocks


def find_nearest_text(img_bbox, text_blocks, max_distance=150) -> str:
    """Findet den naechstliegenden Textblock zu einem Bild."""
    img_cx = (img_bbox[0] + img_bbox[2]) / 2
    img_cy = (img_bbox[1] + img_bbox[3]) / 2

    best_text = None
    best_distance = max_distance

    for block in text_blocks:
        dx = block["cx"] - img_cx
        dy = block["cy"] - img_cy
        distance = (dx ** 2 + dy ** 2) ** 0.5

        # Text rechts neben dem Bild bevorzugen
        if 0 < dx < 200 and abs(dy) < 30:
            distance *= 0.5

        # Text direkt unter dem Bild
        if abs(dx) < 50 and 0 < dy < 60:
            distance *= 0.6

        if distance < best_distance:
            best_distance = distance
            best_text = block["text"]

    return best_text


def match_to_known_name(name: str) -> tuple:
    """Versucht den normalisierten Namen einem bekannten DB-Namen zuzuordnen.

    Returns: (matched_name, category, match_type)
    """
    # 1. Exakter Match mit Produktionen
    if name in KNOWN_PRODUCTIONS:
        return (name, "productions", "exact")

    # 2. Exakter Match mit Produkten
    if name in KNOWN_PRODUCTS:
        return (name, "products", "exact")

    # 3. Fuzzy-Match: ist der Name ein Prefix eines bekannten Namens?
    for prod in sorted(KNOWN_PRODUCTIONS, key=len, reverse=True):
        if name.startswith(prod) or prod.startswith(name):
            if abs(len(name) - len(prod)) < 5:
                return (prod, "productions", "prefix")

    for prod in sorted(KNOWN_PRODUCTS, key=len, reverse=True):
        if name.startswith(prod) or prod.startswith(name):
            if abs(len(name) - len(prod)) < 5:
                return (prod, "products", "prefix")

    # 4. Levenshtein-Distanz < 3 mit Produktionen
    for prod in KNOWN_PRODUCTIONS:
        if levenshtein_distance(name, prod) < 3:
            return (prod, "productions", "fuzzy")

    # 5. Levenshtein-Distanz < 3 mit Produkten
    for prod in KNOWN_PRODUCTS:
        if levenshtein_distance(name, prod) < 3:
            return (prod, "products", "fuzzy")

    # 6. Kein Match -> misc
    return (name, "misc", "none")


def levenshtein_distance(s1: str, s2: str) -> int:
    """Berechnet die Levenshtein-Distanz zwischen zwei Strings."""
    if len(s1) < len(s2):
        return levenshtein_distance(s2, s1)

    if len(s2) == 0:
        return len(s1)

    previous_row = range(len(s2) + 1)
    for i, c1 in enumerate(s1):
        current_row = [i + 1]
        for j, c2 in enumerate(s2):
            insertions = previous_row[j + 1] + 1
            deletions = current_row[j] + 1
            substitutions = previous_row[j] + (c1 != c2)
            current_row.append(min(insertions, deletions, substitutions))
        previous_row = current_row

    return previous_row[-1]


def is_meaningful_image(pix) -> bool:
    """Prueft ob ein Bild sinnvoll ist (nicht zu klein, nicht riesig)."""
    if pix.width < 20 or pix.height < 20:
        return False

    # Zu grosse Bilder sind wahrscheinlich Seitenhintergruende oder Diagramme
    if pix.width > 1000 and pix.height > 1000:
        return False

    return True


def save_image_as_png(pix, output_path: Path) -> bool:
    """Speichert ein PyMuPDF Pixmap als PNG."""
    try:
        img_data = pix.tobytes("png")

        # Ueber Pillow fuer bessere Qualitaet
        img = Image.open(io.BytesIO(img_data))

        # Konvertiere CMYK zu RGB
        if img.mode == "CMYK":
            img = img.convert("RGB")
        elif img.mode not in ("RGB", "RGBA"):
            img = img.convert("RGBA")

        # Auf vernuenftige Groesse skalieren (max 256x256 fuer Icons)
        max_size = 256
        if img.width > max_size or img.height > max_size:
            img.thumbnail((max_size, max_size), Image.LANCZOS)

        img.save(output_path, "PNG", optimize=True)
        return True
    except Exception as e:
        print(f"  Fehler beim Speichern {output_path.name}: {e}")
        return False


def extract_images_from_pdf():
    """Hauptfunktion: Extrahiert und verarbeitet alle Bilder aus der PDF."""
    if not PDF_PATH.exists():
        print(f"PDF nicht gefunden: {PDF_PATH}")
        sys.exit(1)

    # Bekannte Namen laden
    load_known_names()

    print(f"\nOeffne PDF: {PDF_PATH}")
    doc = fitz.open(str(PDF_PATH))
    print(f"Seiten: {len(doc)}")

    # Vorherige Extraktion aufraemen
    for directory in [PRODUCTS_DIR, PRODUCTIONS_DIR, MISC_DIR]:
        if directory.exists():
            for f in directory.glob("*.png"):
                f.unlink()
        directory.mkdir(parents=True, exist_ok=True)
        print(f"Ordner bereit: {directory}")

    # Tracking
    seen_hashes = {}        # hash -> {name, path, category}
    name_to_hash = {}       # name -> hash (fuer Duplikat-Erkennung gleicher Namen)
    extracted = []
    duplicates_list = []
    stats = {
        "total_images": 0,
        "unique_images": 0,
        "duplicates": 0,
        "matched_products": 0,
        "matched_productions": 0,
        "unmatched": 0,
        "skipped_small": 0,
        "skipped_large": 0,
        "save_errors": 0,
    }

    for page_num in range(len(doc)):
        page = doc[page_num]

        # Seitentitel extrahieren
        page_title = extract_page_title(page)
        page_title_normalized = normalize_name(page_title) if page_title else ""

        print(f"\n--- Seite {page_num + 1}/{len(doc)}: '{page_title}' ---")

        # Textbloecke extrahieren
        text_blocks = extract_text_blocks(page)

        # Bilder extrahieren
        image_list = page.get_images(full=True)
        print(f"  {len(image_list)} Bilder, {len(text_blocks)} Textbloecke")

        for img_index, img_info in enumerate(image_list):
            xref = img_info[0]
            stats["total_images"] += 1

            try:
                # Bild-Daten extrahieren
                base_image = doc.extract_image(xref)
                if not base_image:
                    continue

                image_bytes = base_image["image"]

                # Pixmap fuer Groessenpruefung
                pix = fitz.Pixmap(doc, xref)

                if pix.width < 20 or pix.height < 20:
                    stats["skipped_small"] += 1
                    pix = None
                    continue

                if pix.width > 1000 and pix.height > 1000:
                    stats["skipped_large"] += 1
                    pix = None
                    continue

                # Hash berechnen
                img_hash = compute_hash(image_bytes)

                if img_hash in seen_hashes:
                    stats["duplicates"] += 1
                    duplicates_list.append({
                        "page": page_num + 1,
                        "hash": img_hash[:12],
                        "duplicate_of": seen_hashes[img_hash]["name"]
                    })
                    pix = None
                    continue

                # Bild-Position auf der Seite finden
                img_rects = page.get_image_rects(xref)
                img_bbox = img_rects[0] if img_rects else fitz.Rect(0, 0, pix.width, pix.height)

                # Naechstliegenden Text finden
                nearest_text = find_nearest_text(img_bbox, text_blocks)

                # Kurzes Label extrahieren
                if nearest_text:
                    short_label = extract_short_label(nearest_text)
                    name = normalize_name(short_label) if short_label else ""
                else:
                    name = ""

                # Fallback: Seitentitel verwenden
                if not name or len(name) < 2:
                    if page_title_normalized and len(page_title_normalized) > 2:
                        name = f"{page_title_normalized}_bild_{img_index}"
                    else:
                        name = f"seite_{page_num + 1}_bild_{img_index}"

                # Dateiname kuerzen (max 80 Zeichen)
                if len(name) > 80:
                    name = name[:80].rsplit('_', 1)[0]

                # Gegen bekannte DB-Namen matchen
                matched_name, category, match_type = match_to_known_name(name)

                if match_type != "none":
                    name = matched_name

                filename = f"{name}.png"

                # Zielordner bestimmen
                if category == "productions":
                    target_dir = PRODUCTIONS_DIR
                    stats["matched_productions"] += 1
                elif category == "products":
                    target_dir = PRODUCTS_DIR
                    stats["matched_products"] += 1
                else:
                    target_dir = MISC_DIR
                    stats["unmatched"] += 1

                # Dateiname-Kollision: suffix anhaengen
                target_path = target_dir / filename
                if target_path.exists():
                    counter = 2
                    while target_path.exists():
                        target_path = target_dir / f"{name}_{counter}.png"
                        counter += 1

                # Dateiname zu lang fuer Windows? (max 255)
                if len(str(target_path)) > 250:
                    name = name[:40]
                    filename = f"{name}.png"
                    target_path = target_dir / filename
                    if target_path.exists():
                        counter = 2
                        while target_path.exists():
                            target_path = target_dir / f"{name}_{counter}.png"
                            counter += 1

                # Speichern
                if save_image_as_png(pix, target_path):
                    seen_hashes[img_hash] = {
                        "name": name,
                        "path": str(target_path.relative_to(BASE_DIR)),
                        "category": category,
                        "match_type": match_type,
                    }
                    stats["unique_images"] += 1
                    extracted.append({
                        "name": name,
                        "filename": target_path.name,
                        "category": category,
                        "match_type": match_type,
                        "path": str(target_path.relative_to(BASE_DIR)),
                        "page": page_num + 1,
                        "page_title": page_title,
                        "hash": img_hash[:12],
                        "size": f"{pix.width}x{pix.height}",
                        "source_text": (nearest_text or "")[:100],
                    })

                    match_label = f"[{category}/{match_type}]"
                    print(f"  {match_label:25s} {target_path.name} ({pix.width}x{pix.height})")
                else:
                    stats["save_errors"] += 1

                pix = None

            except Exception as e:
                print(f"  Fehler bei Bild {img_index}: {e}")
                continue

    doc.close()

    # Report generieren
    report = {
        "stats": stats,
        "extracted": extracted,
        "duplicates": duplicates_list,
        "hash_map": {v["name"]: k[:12] for k, v in seen_hashes.items()},
    }

    with open(REPORT_PATH, "w", encoding="utf-8") as f:
        json.dump(report, f, indent=2, ensure_ascii=False)

    # Zusammenfassung ausgeben
    print("\n" + "=" * 60)
    print("EXTRAKTIONS-BERICHT")
    print("=" * 60)
    print(f"Gesamte Bilder im PDF:     {stats['total_images']}")
    print(f"Zu klein uebersprungen:    {stats['skipped_small']}")
    print(f"Zu gross uebersprungen:    {stats['skipped_large']}")
    print(f"Duplikate (Hash-gleich):   {stats['duplicates']}")
    print(f"Speicherfehler:            {stats['save_errors']}")
    print(f"Einzigartige Bilder:       {stats['unique_images']}")
    print(f"  -> Produkte (DB-Match):  {stats['matched_products']}")
    print(f"  -> Produktionen (Match): {stats['matched_productions']}")
    print(f"  -> Nicht zugeordnet:     {stats['unmatched']}")
    print(f"\nReport: {REPORT_PATH}")
    print(f"Produkte:    {PRODUCTS_DIR}")
    print(f"Produktionen: {PRODUCTIONS_DIR}")
    print(f"Sonstige:     {MISC_DIR}")

    return report


if __name__ == "__main__":
    extract_images_from_pdf()
