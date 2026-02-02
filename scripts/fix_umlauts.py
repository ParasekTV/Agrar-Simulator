#!/usr/bin/env python3
"""
Umlaut-Korrektur für SQL-Dateien
Ersetzt deutsche Umlaute durch ASCII-sichere Alternativen.
"""

import sys

# Umlaut-Ersetzungen
REPLACEMENTS = {
    'ä': 'ae',
    'ö': 'oe',
    'ü': 'ue',
    'Ä': 'Ae',
    'Ö': 'Oe',
    'Ü': 'Ue',
    'ß': 'ss',
}

def fix_umlauts(input_file, output_file=None):
    """Ersetzt Umlaute in einer Datei"""

    if output_file is None:
        output_file = input_file

    # Datei lesen
    with open(input_file, 'r', encoding='utf-8') as f:
        content = f.read()

    # Umlaute ersetzen
    for umlaut, replacement in REPLACEMENTS.items():
        content = content.replace(umlaut, replacement)

    # Datei schreiben
    with open(output_file, 'w', encoding='utf-8', newline='\n') as f:
        f.write(content)

    print(f"Umlaute korrigiert: {input_file}")
    if output_file != input_file:
        print(f"Gespeichert als: {output_file}")

def main():
    input_file = "../sql/productions_migration.sql"

    if len(sys.argv) > 1:
        input_file = sys.argv[1]

    fix_umlauts(input_file)
    print("Fertig!")

if __name__ == "__main__":
    main()
