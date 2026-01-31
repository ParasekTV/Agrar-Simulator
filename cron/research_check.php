<?php
/**
 * Cron Job: Research Check
 *
 * Schliesst fertige Forschungen ab und vergibt Belohnungen.
 * Empfohlene Ausfuehrung: Alle 5 Minuten
 *
 * Crontab: /5 * * * * php /path/to/farming-simulator/cron/research_check.php
 */

require_once __DIR__ . '/../config/config.php';

echo "Research Check gestartet: " . date('Y-m-d H:i:s') . "\n";

// Schliesse Forschungen ab
$completed = Research::completeResearch();
echo "Forschungen abgeschlossen: {$completed}\n";

echo "Research Check abgeschlossen: " . date('Y-m-d H:i:s') . "\n";
