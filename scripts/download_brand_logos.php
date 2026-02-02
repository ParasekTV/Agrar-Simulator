<?php
/**
 * Brand Logo Downloader
 *
 * Lädt alle Marken-Logos von farming-simulator.com herunter
 * und organisiert sie in Kategorieordnern.
 *
 * Verwendung: php scripts/download_brand_logos.php
 */

// Konfiguration
$baseUrl = 'https://www.farming-simulator.com/img/content/brands/';
$targetDir = __DIR__ . '/../public/img/brands/';

// Marken nach Kategorien organisiert
$categories = [
    'fahrzeuge' => [
        'abi', 'agco', 'antoniocarraro', 'ape', 'aprilia', 'caseih', 'challenger',
        'claas', 'deutzfahr', 'fendt', 'fiat', 'impex', 'international', 'iseki',
        'jcb', 'johndeere', 'komatsu', 'kubota', 'landini', 'lindner', 'lizard',
        'mack', 'manitou', 'masseyferguson', 'mccormick', 'merlo', 'newholland',
        'pfanzelt', 'ponsse', 'prinoth', 'riedler', 'rigitrac', 'ropa', 'rottne',
        'same', 'schaeffer', 'sennebogen', 'skoda', 'steyr', 'valtra', 'volvo', 'zetor'
    ],

    'erntemaschinen' => [
        'agrifac', 'amitytech', 'capello', 'caseih', 'claas', 'dewulf', 'ero',
        'fendt', 'geringhoff', 'gregoire', 'grimme', 'holmer', 'iseki', 'johndeere',
        'kemper', 'krone', 'lacotec', 'macdon', 'masseyferguson', 'newholland',
        'oxbo', 'ropa'
    ],

    'geraete' => [
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

    'diverses' => [
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
];

/**
 * Lädt eine Datei herunter
 */
function downloadFile(string $url, string $destination): bool
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept: image/png,image/*,*/*;q=0.8',
                'Referer: https://www.farming-simulator.com/about.php'
            ],
            'timeout' => 30
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);

    $content = @file_get_contents($url, false, $context);

    if ($content === false) {
        return false;
    }

    return file_put_contents($destination, $content) !== false;
}

/**
 * Erstellt Verzeichnis falls nicht vorhanden
 */
function ensureDirectory(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "  Verzeichnis erstellt: $path\n";
    }
}

// Hauptprogramm
echo "==============================================\n";
echo "  LSBG Agrar Simulator - Brand Logo Downloader\n";
echo "==============================================\n\n";

// Zielverzeichnis erstellen
ensureDirectory($targetDir);

$totalDownloaded = 0;
$totalFailed = 0;
$totalSkipped = 0;
$downloadedBrands = []; // Verhindert Duplikate

foreach ($categories as $category => $brands) {
    echo "\n[$category]\n";
    echo str_repeat('-', 40) . "\n";

    $categoryDir = $targetDir . $category . '/';
    ensureDirectory($categoryDir);

    foreach ($brands as $brand) {
        // Überspringe wenn bereits in anderer Kategorie heruntergeladen
        $logoFileName = "logo-{$brand}.png";
        $destination = $categoryDir . $logoFileName;

        // Prüfe ob Datei bereits existiert
        if (file_exists($destination)) {
            echo "  [SKIP] $brand (bereits vorhanden)\n";
            $totalSkipped++;
            continue;
        }

        // Versuche zuerst die "off" Version (Standard-Logo)
        $url = $baseUrl . "logo-{$brand}-off.png";

        echo "  Lade: $brand ... ";

        if (downloadFile($url, $destination)) {
            echo "OK\n";
            $totalDownloaded++;
            $downloadedBrands[$brand] = true;
        } else {
            // Versuche alternative URL ohne "-off"
            $urlAlt = $baseUrl . "logo-{$brand}.png";
            if (downloadFile($urlAlt, $destination)) {
                echo "OK (alt)\n";
                $totalDownloaded++;
                $downloadedBrands[$brand] = true;
            } else {
                echo "FEHLGESCHLAGEN\n";
                $totalFailed++;
            }
        }

        // Kleine Pause um Server nicht zu überlasten
        usleep(100000); // 100ms
    }
}

// Zusammenfassung
echo "\n==============================================\n";
echo "  ZUSAMMENFASSUNG\n";
echo "==============================================\n";
echo "  Heruntergeladen: $totalDownloaded\n";
echo "  Übersprungen:    $totalSkipped\n";
echo "  Fehlgeschlagen:  $totalFailed\n";
echo "  Zielordner:      $targetDir\n";
echo "==============================================\n";

// Erstelle eine Index-Datei mit allen Marken
$indexFile = $targetDir . 'brands_index.json';
$indexData = [
    'generated_at' => date('Y-m-d H:i:s'),
    'source' => 'https://www.farming-simulator.com/about.php',
    'categories' => $categories,
    'statistics' => [
        'downloaded' => $totalDownloaded,
        'skipped' => $totalSkipped,
        'failed' => $totalFailed
    ]
];

file_put_contents($indexFile, json_encode($indexData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nIndex-Datei erstellt: $indexFile\n";

echo "\nFertig!\n";
