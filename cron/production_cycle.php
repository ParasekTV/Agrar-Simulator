<?php
/**
 * Production Cycle Cron Job
 *
 * Verarbeitet kontinuierliche Produktionen.
 * Sollte alle 5 Minuten ausgeführt werden.
 *
 * Crontab: */5 * * * * php /pfad/zu/agrar-simulator/cron/production_cycle.php
 */

// Lade Konfiguration
require_once __DIR__ . '/../config/config.php';

// Hole alle laufenden Produktionen die bereit für nächsten Zyklus sind
$runningProductions = Production::getRunningProductions();

$processed = 0;
$stopped = 0;
$errors = 0;

$productionModel = new Production();

foreach ($runningProductions as $production) {
    try {
        $result = $productionModel->processCycle($production['id']);

        if ($result['success']) {
            $processed++;
            Logger::info('Production cycle completed', [
                'farm_production_id' => $production['id'],
                'farm_id' => $production['farm_id'],
                'efficiency' => $result['efficiency'],
                'cycle' => $result['cycle']
            ]);
        } elseif (isset($result['stopped']) && $result['stopped']) {
            $stopped++;
            Logger::info('Production auto-stopped', [
                'farm_production_id' => $production['id'],
                'farm_id' => $production['farm_id'],
                'reason' => 'no_resources'
            ]);
        }
    } catch (Exception $e) {
        $errors++;
        Logger::error('Production cycle error', [
            'farm_production_id' => $production['id'],
            'error' => $e->getMessage()
        ]);
    }
}

// Log Zusammenfassung
if ($processed > 0 || $stopped > 0 || $errors > 0) {
    Logger::info('Production cycle cron completed', [
        'processed' => $processed,
        'stopped' => $stopped,
        'errors' => $errors,
        'total_checked' => count($runningProductions)
    ]);
}

// Ausgabe für Cron-Log
echo date('Y-m-d H:i:s') . " - Production cycles: {$processed} processed, {$stopped} stopped, {$errors} errors\n";
