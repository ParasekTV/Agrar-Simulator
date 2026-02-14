<?php
/**
 * Account Maintenance Cron Job
 *
 * Führt automatische Account-Wartung durch:
 * - Urlaubsmodus nach 30 Tagen Inaktivität
 * - Löschungs-Warnungen nach 180 Tagen
 * - Automatische Löschung nach 187 Tagen (180+7)
 * - Löschung nach 7 Tagen bei manueller Anfrage
 *
 * Empfohlen: Täglich um 2:00 Uhr ausführen
 * Cron: 0 2 * * * php /var/www/agrar-simulator/cron/account_maintenance.php
 */

// Lade Konfiguration
require_once __DIR__ . '/../config/config.php';

// Nur CLI erlauben
if (php_sapi_name() !== 'cli') {
    die('This script must be run from command line.');
}

echo "===========================================\n";
echo "Account Maintenance Cron - " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

try {
    $accountModel = new Account();

    // 1. Auto-Urlaubsmodus (30 Tage Inaktivität)
    echo "1. Prüfe inaktive Accounts für Urlaubsmodus...\n";
    $vacationCount = $accountModel->processAutoVacation();
    echo "   -> {$vacationCount} Accounts in Urlaubsmodus versetzt\n\n";

    // 2. Löschungs-Warnungen senden (180 Tage)
    echo "2. Sende Löschungs-Warnungen...\n";
    $warningCount = $accountModel->sendDeletionWarnings();
    echo "   -> {$warningCount} Warnungen gesendet\n\n";

    // 3. Automatische Löschung
    echo "3. Verarbeite automatische Löschungen...\n";
    $deletionCount = $accountModel->processAutoDeletion();
    echo "   -> {$deletionCount} Accounts gelöscht\n\n";

    // Log schreiben
    $logMessage = sprintf(
        "[%s] Account Maintenance: %d vacation, %d warnings, %d deleted\n",
        date('Y-m-d H:i:s'),
        $vacationCount,
        $warningCount,
        $deletionCount
    );

    $logFile = ROOT_PATH . '/logs/account_maintenance.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    echo "===========================================\n";
    echo "Fertig!\n";
    echo "Urlaubsmodus: {$vacationCount}\n";
    echo "Warnungen: {$warningCount}\n";
    echo "Gelöscht: {$deletionCount}\n";
    echo "===========================================\n";

} catch (Exception $e) {
    $errorMessage = "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    echo "FEHLER: " . $e->getMessage() . "\n";

    $errorLogFile = ROOT_PATH . '/logs/account_maintenance_errors.log';
    $logDir = dirname($errorLogFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($errorLogFile, $errorMessage, FILE_APPEND);

    exit(1);
}
