<?php
/**
 * Datenbank-Konfiguration
 *
 * Passe diese Werte an deine lokale Umgebung an.
 */

return [
    'host'     => 'localhost',
    'database' => 'farming_simulator',
    'username' => 'farm_user',
    'password' => 'PGdNwpnn%x6mb92_',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
