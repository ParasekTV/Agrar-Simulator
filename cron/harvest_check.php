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

echo "Harvest Check abgeschlossen: " . date('Y-m-d H:i:s') . "\n";
