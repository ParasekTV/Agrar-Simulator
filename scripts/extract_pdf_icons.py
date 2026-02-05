#!/usr/bin/env python3
"""
PDF Icon Extractor - Extrahiert Grafiken aus Produktionsübersicht.pdf

Funktionen:
1. Alle eingebetteten Bilder aus der PDF extrahieren
2. Bilder per SHA256-Hash deduplizieren
3. Bilder anhand benachbarter Textblöcke benennen
4. In passende Ordner sortieren (products/ oder productions/)
5. Zuordnungsreport generieren
"""

import os
import sys
import hashlib
import json
import re
from pathlib import Path

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

# Bekannte Produktionsnamen (aus productions_migration.sql)
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

# Umlaut-Ersetzungen
UMLAUT_MAP = {
    'ä': 'ae', 'ö': 'oe', 'ü': 'ue', 'ß': 'ss',
    'Ä': 'Ae', 'Ö': 'Oe', 'Ü': 'Ue',
    'é': 'e', 'è': 'e', 'ê': 'e', 'à': 'a', 'â': 'a',
    'ï': 'i', 'î': 'i', 'ô': 'o', 'û': 'u', 'ù': 'u'
}


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

    # Führende/Trailing Unterstriche entfernen
    name = name.strip('_')

    return name


def compute_hash(image_bytes: bytes) -> str:
    """Berechnet SHA256-Hash eines Bildes."""
    return hashlib.sha256(image_bytes).hexdigest()


def extract_text_blocks(page) -> list:
    """Extrahiert Textblöcke mit Position von einer PDF-Seite."""
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
    """Findet den nächstliegenden Textblock zu einem Bild."""
    img_cx = (img_bbox[0] + img_bbox[2]) / 2
    img_cy = (img_bbox[1] + img_bbox[3]) / 2

    best_text = None
    best_distance = max_distance

    for block in text_blocks:
        # Bevorzuge Text rechts neben oder unter dem Bild
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


def is_meaningful_image(pix) -> bool:
    """Prüft ob ein Bild sinnvoll ist (nicht zu klein, nicht einfarbig)."""
    if pix.width < 16 or pix.height < 16:
        return False

    # Zu große Bilder sind wahrscheinlich Seitenhintergründe
    if pix.width > 2000 and pix.height > 2000:
        return False

    return True


def save_image_as_png(pix, output_path: Path) -> bool:
    """Speichert ein PyMuPDF Pixmap als PNG."""
    try:
        # Konvertiere zu RGB falls nötig
        if pix.alpha:
            img_data = pix.tobytes("png")
        else:
            img_data = pix.tobytes("png")

        # Über Pillow für bessere Qualität
        img = Image.open(io.BytesIO(img_data))

        # Konvertiere CMYK zu RGB
        if img.mode == "CMYK":
            img = img.convert("RGB")
        elif img.mode not in ("RGB", "RGBA"):
            img = img.convert("RGBA")

        # Auf vernünftige Größe skalieren (max 256x256 für Icons)
        max_size = 256
        if img.width > max_size or img.height > max_size:
            img.thumbnail((max_size, max_size), Image.LANCZOS)

        img.save(output_path, "PNG", optimize=True)
        return True
    except Exception as e:
        print(f"  Fehler beim Speichern: {e}")
        return False


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


def classify_image(name: str) -> str:
    """Klassifiziert ein Bild als 'products', 'productions' oder 'misc'."""
    # Exakter Match mit Produktionen
    if name in KNOWN_PRODUCTIONS:
        return "productions"

    # Fuzzy-Match mit Produktionen (Levenshtein < 3)
    for prod_name in KNOWN_PRODUCTIONS:
        if levenshtein_distance(name, prod_name) < 3:
            return "productions"

    # Alles andere als Produkt behandeln (oder misc falls unbekannt)
    return "products"


def extract_images_from_pdf():
    """Hauptfunktion: Extrahiert und verarbeitet alle Bilder aus der PDF."""
    if not PDF_PATH.exists():
        print(f"PDF nicht gefunden: {PDF_PATH}")
        sys.exit(1)

    print(f"Öffne PDF: {PDF_PATH}")
    doc = fitz.open(str(PDF_PATH))
    print(f"Seiten: {len(doc)}")

    # Erstelle Ausgabeordner
    for directory in [PRODUCTS_DIR, PRODUCTIONS_DIR, MISC_DIR]:
        directory.mkdir(parents=True, exist_ok=True)
        print(f"Ordner erstellt: {directory}")

    # Tracking
    seen_hashes = {}  # hash -> (name, path)
    extracted = []
    duplicates = []
    unmatched = []
    stats = {
        "total_images": 0,
        "unique_images": 0,
        "duplicates": 0,
        "products": 0,
        "productions": 0,
        "misc": 0,
        "skipped_small": 0
    }

    for page_num in range(len(doc)):
        page = doc[page_num]
        print(f"\n--- Seite {page_num + 1}/{len(doc)} ---")

        # Textblöcke extrahieren
        text_blocks = extract_text_blocks(page)

        # Bilder extrahieren
        image_list = page.get_images(full=True)
        print(f"  {len(image_list)} Bilder gefunden, {len(text_blocks)} Textblöcke")

        for img_index, img_info in enumerate(image_list):
            xref = img_info[0]
            stats["total_images"] += 1

            try:
                # Bild-Daten extrahieren
                base_image = doc.extract_image(xref)
                if not base_image:
                    continue

                image_bytes = base_image["image"]
                img_ext = base_image.get("ext", "png")

                # Pixmap für Größenprüfung
                pix = fitz.Pixmap(doc, xref)

                if not is_meaningful_image(pix):
                    stats["skipped_small"] += 1
                    if pix.alpha:
                        pix = None
                    continue

                # Hash berechnen
                img_hash = compute_hash(image_bytes)

                if img_hash in seen_hashes:
                    stats["duplicates"] += 1
                    duplicates.append({
                        "page": page_num + 1,
                        "hash": img_hash[:16],
                        "duplicate_of": seen_hashes[img_hash]["name"]
                    })
                    continue

                # Bild-Position auf der Seite finden
                img_rects = page.get_image_rects(xref)
                img_bbox = img_rects[0] if img_rects else (0, 0, pix.width, pix.height)

                # Nächstliegenden Text finden
                nearest_text = find_nearest_text(img_bbox, text_blocks)

                if nearest_text:
                    name = normalize_name(nearest_text)
                else:
                    name = f"unbekannt_s{page_num + 1}_{img_index}"

                # Leeren Namen vermeiden
                if not name or len(name) < 2:
                    name = f"bild_s{page_num + 1}_{img_index}"

                # Klassifizieren
                category = classify_image(name)
                filename = f"{name}.png"

                # Zielordner bestimmen
                if category == "productions":
                    target_dir = PRODUCTIONS_DIR
                    stats["productions"] += 1
                elif category == "products":
                    target_dir = PRODUCTS_DIR
                    stats["products"] += 1
                else:
                    target_dir = MISC_DIR
                    stats["misc"] += 1

                # Dateiname-Kollision vermeiden
                target_path = target_dir / filename
                counter = 1
                while target_path.exists():
                    target_path = target_dir / f"{name}_{counter}.png"
                    counter += 1

                # Speichern
                if save_image_as_png(pix, target_path):
                    seen_hashes[img_hash] = {
                        "name": name,
                        "path": str(target_path.relative_to(BASE_DIR)),
                        "category": category
                    }
                    stats["unique_images"] += 1
                    extracted.append({
                        "name": name,
                        "filename": filename,
                        "category": category,
                        "path": str(target_path.relative_to(BASE_DIR)),
                        "page": page_num + 1,
                        "hash": img_hash[:16],
                        "size": f"{pix.width}x{pix.height}",
                        "source_text": nearest_text or "N/A"
                    })
                    print(f"  [{category}] {filename} ({pix.width}x{pix.height}) <- '{nearest_text or 'N/A'}'")

                # Cleanup
                pix = None

            except Exception as e:
                print(f"  Fehler bei Bild {img_index}: {e}")
                continue

    doc.close()

    # Report generieren
    report = {
        "stats": stats,
        "extracted": extracted,
        "duplicates": duplicates,
        "hash_map": {v["name"]: k[:16] for k, v in seen_hashes.items()}
    }

    with open(REPORT_PATH, "w", encoding="utf-8") as f:
        json.dump(report, f, indent=2, ensure_ascii=False)

    # Zusammenfassung ausgeben
    print("\n" + "=" * 60)
    print("EXTRAKTIONS-BERICHT")
    print("=" * 60)
    print(f"Gesamte Bilder im PDF:     {stats['total_images']}")
    print(f"Zu klein/übersprungen:     {stats['skipped_small']}")
    print(f"Duplikate:                 {stats['duplicates']}")
    print(f"Einzigartige Bilder:       {stats['unique_images']}")
    print(f"  → Produkte:             {stats['products']}")
    print(f"  → Produktionen:          {stats['productions']}")
    print(f"  → Sonstige:             {stats['misc']}")
    print(f"\nReport gespeichert: {REPORT_PATH}")
    print(f"Produkte-Ordner:   {PRODUCTS_DIR}")
    print(f"Produktions-Ordner: {PRODUCTIONS_DIR}")

    return report


if __name__ == "__main__":
    extract_images_from_pdf()
