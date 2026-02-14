<?php
/**
 * Animal Check Extension
 *
 * Diese Zeilen zum bestehenden cron/animal_check.php hinzufügen
 * nach dem require_once für config.php
 */

// ============================================
// ZUM BESTEHENDEN ANIMAL_CHECK.PHP HINZUFÜGEN:
// ============================================

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

// ============================================
// VOLLSTÄNDIGES ANIMAL_CHECK.PHP BEISPIEL:
// ============================================
/*
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Animal.php';

echo "=== Animal Check Cron ===\n";
echo "Gestartet: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Bestehende Logik: Gesundheit und Happiness aktualisieren
    echo "Basis-Gesundheitsupdate...\n";
    $updated = Animal::updateAnimalHealth();
    echo "Tiere aktualisiert: {$updated}\n";

    // === NEUE EXTENSION-LOGIK ===

    // 1. Tägliches Update (Alter, Wasser, Stroh, Mist)
    echo "\n1. Tägliches Update...\n";
    Animal::dailyUpdate();
    echo "   Durchgeführt\n";

    // 2. Krankheits-Check
    echo "2. Krankheits-Check...\n";
    $sickened = Animal::checkSickness();
    echo "   Neu erkrankt: {$sickened}\n";

    // 3. Nachwuchs-Check
    echo "3. Nachwuchs-Check...\n";
    $births = Animal::checkReproduction();
    echo "   Geburten: {$births}\n";

    // 4. Todes-Check
    echo "4. Todes-Check...\n";
    $deaths = Animal::checkDeaths();
    echo "   Gestorben: {$deaths}\n";

    echo "\n=== Abgeschlossen ===\n";
    echo "Beendet: " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "FEHLER: " . $e->getMessage() . "\n";
    exit(1);
}
*/
