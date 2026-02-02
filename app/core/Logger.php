<?php
/**
 * Logger
 *
 * Einfaches Logging-System für Fehler und Events.
 */
class Logger
{
    private const LEVELS = ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'];

    /**
     * Schreibt einen Log-Eintrag
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        if (!in_array($level, self::LEVELS)) {
            $level = 'INFO';
        }

        $logFile = LOGS_PATH . '/' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? json_encode($context) : '';

        $logMessage = "[{$timestamp}] [{$level}] {$message}";
        if ($contextString) {
            $logMessage .= " {$contextString}";
        }
        $logMessage .= PHP_EOL;

        // Stelle sicher, dass das Log-Verzeichnis existiert
        if (!is_dir(LOGS_PATH)) {
            mkdir(LOGS_PATH, 0755, true);
        }

        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Debug-Level Log
     */
    public static function debug(string $message, array $context = []): void
    {
        if (DEBUG_MODE) {
            self::log('DEBUG', $message, $context);
        }
    }

    /**
     * Info-Level Log
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    /**
     * Warning-Level Log
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    /**
     * Error-Level Log
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    /**
     * Critical-Level Log
     */
    public static function critical(string $message, array $context = []): void
    {
        self::log('CRITICAL', $message, $context);
    }

    /**
     * Loggt eine Exception
     */
    public static function exception(Throwable $e, array $context = []): void
    {
        $context['file'] = $e->getFile();
        $context['line'] = $e->getLine();
        $context['trace'] = $e->getTraceAsString();

        self::error($e->getMessage(), $context);
    }
}
