<?php
/**
 * Umlaut-Finder
 *
 * Findet alle potentiellen Umlaut-Ersetzungen (ae, oe, ue, Ae, Oe, Ue)
 * in PHP-Dateien und gibt sie als Textdatei aus.
 *
 * Verwendung: php umlaut_finder.php [verzeichnis] [ausgabedatei]
 * Beispiel:   php umlaut_finder.php ../app ../umlaut_report.txt
 */

// Konfiguration
$searchDir = $argv[1] ?? dirname(__DIR__) . '/app';
$outputFile = $argv[2] ?? dirname(__DIR__) . '/umlaut_report.txt';

// Muster für Umlaut-Ersetzungen (ae, oe, ue in Groß- und Kleinschreibung)
$patterns = [
    'ae' => '/\b(\w*ae\w*)\b/i',  // ae/Ae
    'oe' => '/\b(\w*oe\w*)\b/i',  // oe/Oe
    'ue' => '/\b(\w*ue\w*)\b/i',  // ue/Ue
];

// Wörter die NICHT ersetzt werden sollen (korrekte deutsche Wörter, englische Wörter, Code)
$excludeWords = [
    // Korrekte deutsche Wörter mit "ue", "ae", "oe"
    'neues', 'neuer', 'neue', 'neuen', 'neuem', 'neueste', 'neuesten', 'neuester',
    'abenteuer', 'abenteurer', 'bauern', 'bauernhof', 'bauer', 'bauernzeitung',
    'feuer', 'feuerwehr', 'freue', 'freuen', 'freund', 'freunde',
    'kreuz', 'kreuzung', 'treue', 'treuer', 'steuer', 'steuern',
    'teuer', 'teure', 'teuren', 'teures', 'ungeheuer',
    'euer', 'euere', 'eueren', 'euerer',
    'israel', 'israelisch',
    'museum', 'museen',

    // Englische Wörter und Code-Begriffe
    'true', 'false', 'blue', 'value', 'values', 'continue', 'queue', 'query',
    'queryselectorall', 'queryselector', 'foreach', 'issue', 'issues',
    'execute', 'rescue', 'venue', 'revenue', 'avenue',
    'unique', 'technique', 'techniques', 'clue', 'clues',
    'due', 'fuel', 'duel', 'cruel', 'gruel',
    'sue', 'sued', 'suede', 'cue', 'cues', 'hue', 'hues', 'rue',
    'glue', 'statue', 'statues', 'virtue', 'virtues',
    'argue', 'argues', 'argued', 'arguing',
    'league', 'leagues', 'colleague', 'colleagues',
    'plague', 'plagues', 'vague', 'vogue',
    'rogue', 'rogues', 'dialogue', 'dialogues',
    'catalogue', 'catalogues', 'analogue', 'analogues',
    'fatigue', 'intrigue', 'intrigues',
    'ogue', // Endung
    'ogue', // Endung

    // PHP/JavaScript Funktionen und Begriffe
    'urlencode', 'urldecode', 'rawurlencode', 'rawurldecode',
    'htmlspecialchars', 'addslashes', 'stripslashes',
    'preg', 'exec', 'eval', 'isset', 'unset',
    'require', 'required', 'requires', 'requirement', 'requirements',
    'includes', 'include', 'included',
    'type', 'types', 'typeof',
    'file', 'files',

    // CSS/HTML Begriffe
    'pointer', 'border', 'outer', 'container', 'footer', 'header',
    'inner', 'wrapper', 'layer', 'player', 'loader',

    // Kraftstoff ist korrekt
    'kraftstoff',
];

// Ergebnisse sammeln
$results = [];
$totalFound = 0;

/**
 * Rekursiv alle PHP-Dateien durchsuchen
 */
function scanDirectory(string $dir, array $patterns, array $excludeWords, array &$results, int &$totalFound): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            scanFile($file->getPathname(), $patterns, $excludeWords, $results, $totalFound);
        }
    }
}

/**
 * Eine Datei nach Umlaut-Ersetzungen durchsuchen
 */
function scanFile(string $filepath, array $patterns, array $excludeWords, array &$results, int &$totalFound): void
{
    $content = file_get_contents($filepath);
    $lines = explode("\n", $content);

    $relativePath = str_replace(dirname(__DIR__) . '/', '', $filepath);

    foreach ($lines as $lineNum => $line) {
        $lineNumber = $lineNum + 1;

        foreach ($patterns as $type => $pattern) {
            if (preg_match_all($pattern, $line, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[1] as $match) {
                    $word = $match[0];
                    $position = $match[1];

                    // Prüfe ob das Wort ausgeschlossen werden soll
                    if (isExcluded($word, $excludeWords)) {
                        continue;
                    }

                    // Prüfe ob es in einem Kommentar, String oder Code-Kontext ist
                    $context = getContext($line, $word);

                    $results[] = [
                        'file' => $relativePath,
                        'line' => $lineNumber,
                        'column' => $position + 1,
                        'word' => $word,
                        'type' => $type,
                        'context' => trim($line),
                        'context_type' => $context
                    ];
                    $totalFound++;
                }
            }
        }
    }
}

/**
 * Prüft ob ein Wort ausgeschlossen werden soll
 */
function isExcluded(string $word, array $excludeWords): bool
{
    $lowerWord = strtolower($word);

    // Direkter Match
    if (in_array($lowerWord, $excludeWords)) {
        return true;
    }

    // Prüfe ob es eine Variable ($variable) oder Funktion ist
    if (preg_match('/^[a-z_][a-z0-9_]*$/i', $word)) {
        // Typische Code-Muster
        $codePatterns = [
            '/query/i', '/value/i', '/true/i', '/false/i', '/blue/i',
            '/continue/i', '/queue/i', '/execute/i', '/require/i',
            '/include/i', '/fuel/i', '/rescue/i',
        ];

        foreach ($codePatterns as $pattern) {
            if (preg_match($pattern, $word)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Ermittelt den Kontext (String, Kommentar, Code)
 */
function getContext(string $line, string $word): string
{
    // Einfache Heuristik
    if (preg_match('/^\s*(\/\/|#|\*)/', $line)) {
        return 'Kommentar';
    }

    // Prüfe ob in einem String
    $pos = strpos($line, $word);
    if ($pos !== false) {
        $before = substr($line, 0, $pos);
        $singleQuotes = substr_count($before, "'") - substr_count($before, "\\'");
        $doubleQuotes = substr_count($before, '"') - substr_count($before, '\\"');

        if ($singleQuotes % 2 === 1 || $doubleQuotes % 2 === 1) {
            return 'String/Text';
        }
    }

    return 'Code';
}

/**
 * Ergebnisse in Datei schreiben
 */
function writeResults(string $outputFile, array $results, int $totalFound): void
{
    $output = [];
    $output[] = "==========================================================";
    $output[] = "UMLAUT-ERSETZUNGEN BERICHT";
    $output[] = "Generiert: " . date('Y-m-d H:i:s');
    $output[] = "==========================================================";
    $output[] = "";
    $output[] = "Gefundene potentielle Umlaut-Ersetzungen: " . $totalFound;
    $output[] = "";
    $output[] = "Legende:";
    $output[] = "  ae -> ä   |   Ae -> Ä";
    $output[] = "  oe -> ö   |   Oe -> Ö";
    $output[] = "  ue -> ü   |   Ue -> Ü";
    $output[] = "";
    $output[] = "==========================================================";
    $output[] = "";

    // Gruppiere nach Datei
    $byFile = [];
    foreach ($results as $result) {
        $byFile[$result['file']][] = $result;
    }

    ksort($byFile);

    foreach ($byFile as $file => $fileResults) {
        $output[] = "DATEI: " . $file;
        $output[] = str_repeat("-", 60);

        foreach ($fileResults as $r) {
            $output[] = sprintf(
                "  Zeile %4d, Spalte %3d | %-20s | Typ: %s | Kontext: %s",
                $r['line'],
                $r['column'],
                $r['word'],
                strtoupper($r['type']),
                $r['context_type']
            );

            // Zeige die betroffene Zeile (gekürzt)
            $contextLine = $r['context'];
            if (strlen($contextLine) > 80) {
                // Finde das Wort und zeige Kontext drumherum
                $pos = stripos($contextLine, $r['word']);
                if ($pos !== false) {
                    $start = max(0, $pos - 30);
                    $contextLine = ($start > 0 ? '...' : '') .
                                   substr($contextLine, $start, 70) .
                                   (strlen($contextLine) > $start + 70 ? '...' : '');
                }
            }
            $output[] = "           -> " . $contextLine;
            $output[] = "";
        }

        $output[] = "";
    }

    // Zusammenfassung nach Typ
    $output[] = "==========================================================";
    $output[] = "ZUSAMMENFASSUNG NACH TYP";
    $output[] = "==========================================================";

    $byType = ['ae' => 0, 'oe' => 0, 'ue' => 0];
    foreach ($results as $r) {
        $byType[$r['type']]++;
    }

    $output[] = sprintf("  ae (ä/Ä): %d Fälle", $byType['ae']);
    $output[] = sprintf("  oe (ö/Ö): %d Fälle", $byType['oe']);
    $output[] = sprintf("  ue (ü/Ü): %d Fälle", $byType['ue']);
    $output[] = "";
    $output[] = "==========================================================";
    $output[] = "ENDE DES BERICHTS";
    $output[] = "==========================================================";

    file_put_contents($outputFile, implode("\n", $output));
}

// Hauptprogramm
echo "Umlaut-Finder gestartet...\n";
echo "Durchsuche: $searchDir\n";

if (!is_dir($searchDir)) {
    die("Fehler: Verzeichnis '$searchDir' existiert nicht.\n");
}

scanDirectory($searchDir, $patterns, $excludeWords, $results, $totalFound);

echo "Gefunden: $totalFound potentielle Umlaut-Ersetzungen\n";
echo "Schreibe Bericht nach: $outputFile\n";

writeResults($outputFile, $results, $totalFound);

echo "Fertig!\n";
