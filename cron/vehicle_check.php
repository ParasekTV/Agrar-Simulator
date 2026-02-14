<?php
/**
 * Vehicle Check Cron Job
 * Täglich ausführen (empfohlen: 00:00 Uhr)
 *
 * Aufgaben:
 * - Tägliche Betriebsstunden simulieren (5-10h zufällig)
 * - Zustand basierend auf Betriebsstunden reduzieren
 * - Diesel-Verbrauch verarbeiten
 * - Fertige Reparaturen abschließen
 */

// Pfad anpassen falls nötig
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Vehicle.php';

echo "=== Vehicle Daily Check ===\n";
echo "Gestartet: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 1. Fertige Reparaturen abschließen
    echo "1. Reparaturen prüfen...\n";
    $repairsCompleted = Vehicle::completeRepairs();
    echo "   Reparaturen abgeschlossen: {$repairsCompleted}\n\n";

    // 2. Tägliche Betriebsstunden simulieren
    echo "2. Betriebsstunden simulieren...\n";
    $hoursUpdated = Vehicle::simulateDailyUsage();
    echo "   Fahrzeuge aktualisiert: {$hoursUpdated}\n\n";

    // 3. Diesel-Verbrauch verarbeiten
    echo "3. Diesel-Verbrauch verarbeiten...\n";
    $dieselProcessed = Vehicle::processDailyDieselConsumption();
    echo "   Fahrzeuge mit Diesel-Verbrauch: {$dieselProcessed}\n\n";

    echo "=== Abgeschlossen ===\n";
    echo "Beendet: " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "FEHLER: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
    exit(1);
}
