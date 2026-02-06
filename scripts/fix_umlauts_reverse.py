#!/usr/bin/env python3
"""
Umlaut-Rückkorrektur für PHP-Dateien
Ersetzt falsch ASCII-kodierte Umlaute (ae->ä, oe->ö, ue->ü) zurück zu echten Umlauten.

Verwendet eine Wortliste bekannter falscher Schreibweisen um False Positives zu vermeiden.

Nutzung:
    python fix_umlauts_reverse.py              # Report-Modus (Standard)
    python fix_umlauts_reverse.py --report     # Report-Modus (explizit)
    python fix_umlauts_reverse.py --fix        # Dateien korrigieren (mit Backup)
"""

import os
import re
import sys
import shutil
import argparse
from pathlib import Path

# Sicherstellen dass stdout UTF-8 verwendet (Windows-Kompatibilität)
if sys.stdout.encoding != 'utf-8':
    sys.stdout.reconfigure(encoding='utf-8')
if sys.stderr.encoding != 'utf-8':
    sys.stderr.reconfigure(encoding='utf-8')

# Projektverzeichnis (relativ zum Script)
PROJECT_ROOT = Path(__file__).resolve().parent.parent
APP_DIR = PROJECT_ROOT / "app"

# Bekannte Wort-Ersetzungen (falsche ASCII-Kodierung -> echte Umlaute)
# Aus umlaut_report.txt extrahiert
KNOWN_REPLACEMENTS = {
    # --- ue -> ü ---
    'zurueck': 'zurück',
    'zurueckgegeben': 'zurückgegeben',
    'Prueft': 'Prüft',
    'Pruefe': 'Prüfe',
    'genuegend': 'genügend',
    'fuer': 'für',
    'Fuegt': 'Fügt',
    'Fuege': 'Füge',
    'Fuehrt': 'Führt',
    'fuehrt': 'führt',
    'Fuettert': 'Füttert',
    'Fuetterung': 'Fütterung',
    'gefuettert': 'gefüttert',
    'gefuetterten': 'gefütterten',
    'Fuetterungsstatus': 'Fütterungsstatus',
    'Fuetterungszeit': 'Fütterungszeit',
    'verfuegbar': 'verfügbar',
    'verfuegbare': 'verfügbare',
    'Uebersicht': 'Übersicht',
    'ueberein': 'überein',
    'uebereinstimmen': 'übereinstimmen',
    'uebertrage': 'übertrage',
    'Erhoehe': 'Erhöhe',
    'erhoehe': 'erhöhe',
    'Glueck': 'Glück',
    'Gluecks': 'Glücks',
    'Ungueltige': 'Ungültige',
    'gueltig': 'gültig',
    'gueltige': 'gültige',
    'gueltigen': 'gültigen',
    'ungueltigen': 'ungültigen',
    'erfuellt': 'erfüllt',
    'koennen': 'können',
    'Gebuehr': 'Gebühr',
    'Ausleihgebuehr': 'Ausleihgebühr',
    'Eigentuemer': 'Eigentümer',
    'Befoerdert': 'Befördert',
    'befoerdern': 'befördern',
    'befoerdert': 'befördert',
    'Gruendet': 'Gründet',
    'Gruender': 'Gründer',
    'Gruenderrolle': 'Gründerrolle',
    'Gruendungskosten': 'Gründungskosten',
    'gegruendet': 'gegründet',
    'Duengt': 'Düngt',
    'Duengung': 'Düngung',
    'benoetigte': 'benötigte',
    'benoetigt': 'benötigt',
    'rueckgaengig': 'rückgängig',
    'Mindestgroesse': 'Mindestgröße',
    'Maximalgroesse': 'Maximalgröße',
    'Feldgroesse': 'Feldgröße',

    # --- ae -> ä ---
    'Geraet': 'Gerät',
    'Geraete': 'Geräte',
    'Geraeten': 'Geräten',
    'Aendert': 'Ändert',
    'aendern': 'ändern',
    'geaendert': 'geändert',
    'Bestaetigung': 'Bestätigung',
    'Bestaetigt': 'Bestätigt',
    'Beitraege': 'Beiträge',
    'Beitraegen': 'Beiträgen',
    'Bodenqualitaet': 'Bodenqualität',
    'Gesamtlagerkapazitaet': 'Gesamtlagerkapazität',
    'Laedt': 'Lädt',
    'laeuft': 'läuft',
    'naechste': 'nächste',
    'naechstes': 'nächstes',
    'naechsten': 'nächsten',
    'Gebaeude': 'Gebäude',
    'gleichmaessig': 'gleichmäßig',
    'Verlaesst': 'Verlässt',
    'Hoefe': 'Höfe',
    'zusaetzliche': 'zusätzliche',
    'Datensaetze': 'Datensätze',
    'spaeter': 'später',
    'Gaeste': 'Gäste',
    'Kaeufer': 'Käufer',
    'Verkaeufer': 'Verkäufer',
    'Verwaltungsoberflaeche': 'Verwaltungsoberfläche',
    'Laenge': 'Länge',
    'taeglichen': 'täglichen',
    'Taeglicher': 'Täglicher',
    'Zaehlt': 'Zählt',
    'Praefixiere': 'Präfixiere',

    # --- oe -> ö ---
    'loescht': 'löscht',
    'Loesche': 'Lösche',
    'loesche': 'lösche',
    'loeschen': 'löschen',
    'geloescht': 'gelöscht',
    'Loescht': 'Löscht',
    'Loest': 'Löst',
    'aufloesen': 'auflösen',
    'aufgeloest': 'aufgelöst',
    'veroeffentlicht': 'veröffentlicht',
    'Passwoerter': 'Passwörter',
    'woechentliche': 'wöchentliche',
    'Woechentliche': 'Wöchentliche',
    'Zerstoert': 'Zerstört',
    'zugehoerige': 'zugehörige',
    'Selbstloeschung': 'Selbstlöschung',

    # --- ss -> ß (spezielle Fälle) ---
    'Schliesst': 'Schließt',

    # --- Ankündigungen ---
    'Ankuendigungen': 'Ankündigungen',
}

# Sortiere nach Länge absteigend, damit längere Wörter zuerst gefunden werden
# (z.B. 'Fuetterungsstatus' vor 'Fuetterung')
SORTED_REPLACEMENTS = sorted(KNOWN_REPLACEMENTS.items(), key=lambda x: len(x[0]), reverse=True)

# Kompiliere Regex-Pattern für jedes Wort (mit Wortgrenzen)
REPLACEMENT_PATTERNS = []
for wrong, correct in SORTED_REPLACEMENTS:
    pattern = re.compile(r'\b' + re.escape(wrong) + r'\b')
    REPLACEMENT_PATTERNS.append((pattern, wrong, correct))


def find_php_files(app_dir):
    """Findet alle PHP-Dateien im app-Verzeichnis."""
    php_files = []
    for root, dirs, files in os.walk(app_dir):
        for f in files:
            if f.endswith('.php'):
                php_files.append(os.path.join(root, f))
    php_files.sort()
    return php_files


def is_in_variable_name(line, match_start, match_end):
    """Prüft ob der Match innerhalb eines PHP-Variablennamens liegt ($varName)."""
    # Suche rückwärts vom Match nach einem $-Zeichen ohne Leerzeichen dazwischen
    before = line[:match_start]
    # Prüfe ob direkt vor dem Match ein $ oder Buchstabe/Unterstrich steht
    # der auf einen Variablennamen hindeutet
    stripped = before.rstrip()
    if stripped and stripped[-1] == '$':
        return True
    # Prüfe ob das Match Teil eines $variable ist
    # z.B. $genuegend oder $fuer
    var_pattern = re.compile(r'\$[a-zA-Z_][a-zA-Z0-9_]*')
    for var_match in var_pattern.finditer(line):
        var_start = var_match.start()
        var_end = var_match.end()
        if var_start < match_end and var_end > match_start:
            return True
    return False


def is_in_function_call(line, match_start, match_end):
    """Prüft ob der Match Teil eines Funktionsnamens oder Methodenaufrufs ist."""
    # Prüfe ob direkt nach dem Match ein ( folgt (Funktionsaufruf)
    after = line[match_end:]
    if after and after[0] == '(':
        # Könnte ein Funktionsname sein - prüfe ob davor -> oder :: steht
        before = line[:match_start].rstrip()
        if before.endswith('->') or before.endswith('::') or before.endswith('function '):
            return True
        # Prüfe ob der Match allein als Funktionsname steht
        if not before or before[-1] in (' ', '\t', '.', '=', '(', ',', '{'):
            # Nur blockieren wenn es ein englischer Funktionsname sein könnte
            return False
    return False


def is_in_identifier(line, match_start, match_end):
    """Prüft ob der Match Teil eines Code-Identifiers ist (Klasse, Methode, Konstante)."""
    # Prüfe ob direkt davor oder danach alphanumerische Zeichen stehen
    # die nicht vom Regex-Wortgrenzen-Match abgedeckt sind
    # -> wird schon durch \b im Pattern abgedeckt

    # Prüfe ob es in einem Array-Key steht: ['key']
    before = line[:match_start]
    after = line[match_end:]

    # Prüfe CSS-Klassen oder HTML-Attribute
    if "class=" in before or "id=" in before:
        # In HTML-Attributen nicht ersetzen
        return True

    return False


def process_line(line, line_num, filepath, replacements_found):
    """Verarbeitet eine einzelne Zeile und findet/ersetzt Umlaute."""
    new_line = line
    line_replacements = []

    for pattern, wrong, correct in REPLACEMENT_PATTERNS:
        for match in pattern.finditer(new_line):
            m_start = match.start()
            m_end = match.end()

            # Sicherheitschecks
            if is_in_variable_name(new_line, m_start, m_end):
                continue
            if is_in_identifier(new_line, m_start, m_end):
                continue

            line_replacements.append((wrong, correct, m_start))

    # Ersetze alle gefundenen Wörter (von hinten nach vorne um Positionen nicht zu verschieben)
    if line_replacements:
        # Sortiere nach Position absteigend
        line_replacements.sort(key=lambda x: x[2], reverse=True)

        for wrong, correct, pos in line_replacements:
            # Ersetze nur diese eine Stelle
            new_line = new_line[:pos] + correct + new_line[pos + len(wrong):]
            replacements_found.append({
                'file': filepath,
                'line_num': line_num,
                'wrong': wrong,
                'correct': correct,
                'original_line': line.rstrip(),
                'new_line': new_line.rstrip(),
            })

    return new_line


def process_file(filepath, fix_mode=False):
    """Verarbeitet eine PHP-Datei."""
    replacements = []

    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            lines = f.readlines()
    except (UnicodeDecodeError, IOError) as e:
        print(f"  WARNUNG: Kann Datei nicht lesen: {filepath} ({e})")
        return replacements

    new_lines = []
    for i, line in enumerate(lines, start=1):
        new_line = process_line(line, i, filepath, replacements)
        new_lines.append(new_line)

    if fix_mode and replacements:
        # Backup erstellen
        backup_path = filepath + '.bak'
        shutil.copy2(filepath, backup_path)

        # Datei schreiben
        with open(filepath, 'w', encoding='utf-8', newline='\n') as f:
            f.writelines(new_lines)

    return replacements


def relative_path(filepath):
    """Gibt den relativen Pfad zum Projekt zurück."""
    try:
        return os.path.relpath(filepath, PROJECT_ROOT)
    except ValueError:
        return filepath


def print_report(all_replacements):
    """Gibt einen übersichtlichen Report aus."""
    if not all_replacements:
        print("\nKeine Ersetzungen gefunden.")
        return

    print("\n" + "=" * 70)
    print("UMLAUT-KORREKTUR REPORT")
    print("=" * 70)

    current_file = None
    file_count = 0
    total_count = 0

    for r in all_replacements:
        if r['file'] != current_file:
            current_file = r['file']
            file_count += 1
            print(f"\n{'=' * 60}")
            print(f"DATEI: {relative_path(current_file)}")
            print(f"{'-' * 60}")

        total_count += 1
        print(f"  Zeile {r['line_num']:>4d} | {r['wrong']:<30s} -> {r['correct']}")

    print(f"\n{'=' * 70}")
    print(f"ZUSAMMENFASSUNG")
    print(f"{'=' * 70}")
    print(f"  Betroffene Dateien: {file_count}")
    print(f"  Gesamte Ersetzungen: {total_count}")
    print(f"{'=' * 70}")


def main():
    parser = argparse.ArgumentParser(
        description='Korrigiert falsch ASCII-kodierte Umlaute in PHP-Dateien zurück zu echten Umlauten.'
    )
    parser.add_argument(
        '--fix',
        action='store_true',
        help='Dateien direkt korrigieren (mit Backup als .bak)'
    )
    parser.add_argument(
        '--report',
        action='store_true',
        default=True,
        help='Nur Report ausgeben, keine Dateien ändern (Standard)'
    )
    args = parser.parse_args()

    # --fix überschreibt den reinen Report-Modus
    fix_mode = args.fix

    if not APP_DIR.exists():
        print(f"FEHLER: App-Verzeichnis nicht gefunden: {APP_DIR}")
        sys.exit(1)

    print(f"Scanne PHP-Dateien in: {APP_DIR}")
    print(f"Modus: {'FIX (Dateien werden geändert)' if fix_mode else 'REPORT (nur Anzeige)'}")

    php_files = find_php_files(str(APP_DIR))
    print(f"Gefundene PHP-Dateien: {len(php_files)}")

    all_replacements = []
    for filepath in php_files:
        replacements = process_file(filepath, fix_mode=fix_mode)
        all_replacements.extend(replacements)

    print_report(all_replacements)

    if fix_mode and all_replacements:
        # Zeige welche Backup-Dateien erstellt wurden
        backup_files = set(r['file'] for r in all_replacements)
        print(f"\nBackup-Dateien erstellt ({len(backup_files)}):")
        for bf in sorted(backup_files):
            print(f"  {relative_path(bf)}.bak")
        print("\nDateien wurden korrigiert!")
    elif not fix_mode and all_replacements:
        print(f"\nHinweis: Verwende --fix um die {len(all_replacements)} Ersetzungen durchzuführen.")


if __name__ == "__main__":
    main()
