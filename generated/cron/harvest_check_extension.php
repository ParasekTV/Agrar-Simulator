<?php
/**
 * Harvest Check Extension
 *
 * Diese Zeilen zum bestehenden cron/harvest_check.php hinzufügen
 */

// ============================================
// ZUM BESTEHENDEN HARVEST_CHECK.PHP HINZUFÜGEN:
// ============================================

echo "\n=== Field Extension Check ===\n";

// 1. Wachstumsstufen aktualisieren
echo "1. Wachstumsstufen aktualisieren...\n";
$stagesUpdated = Field::updateGrowthStages();
echo "   Felder aktualisiert: {$stagesUpdated}\n";

// 2. Unkraut-Wachstum prüfen
echo "2. Unkraut-Check...\n";
$weedAffected = Field::checkWeedGrowth();
echo "   Felder mit neuem Unkraut: {$weedAffected}\n";

echo "=== Field Extension abgeschlossen ===\n";

// ============================================
// VOLLSTÄNDIGES HARVEST_CHECK.PHP BEISPIEL:
// ============================================
/*
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Field.php';

echo "=== Harvest Check Cron ===\n";
echo "Gestartet: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Bestehende Logik: Felder auf "ready" setzen
    echo "Ernte-Status prüfen...\n";
    $readyFields = Field::updateReadyFields();
    echo "Felder bereit zur Ernte: {$readyFields}\n";

    // === NEUE EXTENSION-LOGIK ===

    // 1. Wachstumsstufen aktualisieren
    echo "\n1. Wachstumsstufen aktualisieren...\n";
    $stagesUpdated = Field::updateGrowthStages();
    echo "   Felder aktualisiert: {$stagesUpdated}\n";

    // 2. Unkraut-Wachstum prüfen (nur normale Felder, keine Gewächshäuser)
    echo "2. Unkraut-Check...\n";
    $weedAffected = Field::checkWeedGrowth();
    echo "   Felder mit neuem Unkraut: {$weedAffected}\n";

    echo "\n=== Abgeschlossen ===\n";
    echo "Beendet: " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "FEHLER: " . $e->getMessage() . "\n";
    exit(1);
}
*/
