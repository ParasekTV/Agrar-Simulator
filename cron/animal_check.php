<?php
/**
 * Cron Job: Animal Check
 *
 * Aktualisiert Tiergesundheit und -glueck.
 * Empfohlene Ausfuehrung: Alle 6 Stunden
 *
 * Crontab: 0 /6 * * * php /path/to/farming-simulator/cron/animal_check.php
 */

require_once __DIR__ . '/../config/config.php';

echo "Animal Check gestartet: " . date('Y-m-d H:i:s') . "\n";

// Aktualisiere Tiergesundheit
Animal::updateAnimalHealth();
echo "Tiergesundheit aktualisiert\n";

// === ANIMAL EXTENSION v2.0 ===
echo "\n=== Animal Extension Check ===\n";

// 1. Tägliches Update (Alter, Wasser, Stroh, Mist, Gesundheit)
echo "1. Tägliches Update...\n";
Animal::dailyUpdate();
echo "   Tägliches Update durchgeführt\n";

// 2. Krankheits-Check
echo "2. Krankheits-Check...\n";
$sickened = Animal::checkSickness();
echo "   Neu erkrankte Tiere: {$sickened}\n";

// 3. Nachwuchs-Check
echo "3. Nachwuchs-Check...\n";
$births = Animal::checkReproduction();
echo "   Geburten: {$births}\n";

// 4. Todes-Check
echo "4. Todes-Check...\n";
$deaths = Animal::checkDeaths();
echo "   Gestorbene Tiere: {$deaths}\n";

echo "=== Animal Extension abgeschlossen ===\n";

echo "Animal Check abgeschlossen: " . date('Y-m-d H:i:s') . "\n";
