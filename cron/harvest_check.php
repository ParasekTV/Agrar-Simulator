<?php
/**
 * Cron Job: Harvest Check
 *
 * Aktualisiert den Status von Feldern die bereit zur Ernte sind.
 * Empfohlene Ausfuehrung: Alle 5 Minuten
 *
 * Crontab: /5 * * * * php /path/to/farming-simulator/cron/harvest_check.php
 */

require_once __DIR__ . '/../config/config.php';

echo "Harvest Check gestartet: " . date('Y-m-d H:i:s') . "\n";

// Aktualisiere Felder
$updated = Field::updateReadyFields();
echo "Felder aktualisiert: {$updated}\n";

// === FIELD EXTENSION v2.0 ===
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

echo "Harvest Check abgeschlossen: " . date('Y-m-d H:i:s') . "\n";
