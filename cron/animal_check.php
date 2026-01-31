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

echo "Animal Check abgeschlossen: " . date('Y-m-d H:i:s') . "\n";
