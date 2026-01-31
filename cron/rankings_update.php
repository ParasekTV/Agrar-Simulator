<?php
/**
 * Cron Job: Rankings Update
 *
 * Aktualisiert die Ranglisten-Positionen.
 * Empfohlene Ausfuehrung: Jede Stunde
 *
 * Crontab: 0 * * * * php /path/to/farming-simulator/cron/rankings_update.php
 */

require_once __DIR__ . '/../config/config.php';

echo "Rankings Update gestartet: " . date('Y-m-d H:i:s') . "\n";

// Aktualisiere Positionen
Ranking::updatePositions();
echo "Ranglisten aktualisiert\n";

// Bereinige abgelaufene Herausforderungen
$expired = Ranking::expireChallenges();
echo "Abgelaufene Herausforderungen: {$expired}\n";

// Bereinige abgelaufene Marktangebote
$cleanedListings = Market::cleanupExpired();
echo "Abgelaufene Marktangebote bereinigt: {$cleanedListings}\n";

echo "Rankings Update abgeschlossen: " . date('Y-m-d H:i:s') . "\n";
