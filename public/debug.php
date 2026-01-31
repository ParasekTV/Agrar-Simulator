<?php
/**
 * Debug-Script - Zeigt den genauen Fehler
 * NACH DEM TESTEN LOESCHEN!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug-Informationen</h1>";

// 1. PHP-Version
echo "<h2>1. PHP-Version</h2>";
echo "<p>PHP " . phpversion() . "</p>";

// 2. Config laden
echo "<h2>2. Config laden</h2>";
try {
    require_once __DIR__ . '/../config/config.php';
    echo "<p style='color:green'>OK - Config geladen</p>";
    echo "<p>BASE_URL: " . BASE_URL . "</p>";
    echo "<p>ROOT_PATH: " . ROOT_PATH . "</p>";
    echo "<p>LOGS_PATH: " . LOGS_PATH . "</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>FEHLER: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// 3. Logs-Verzeichnis
echo "<h2>3. Logs-Verzeichnis</h2>";
if (is_dir(LOGS_PATH)) {
    echo "<p style='color:green'>OK - Verzeichnis existiert</p>";
    if (is_writable(LOGS_PATH)) {
        echo "<p style='color:green'>OK - Verzeichnis beschreibbar</p>";
    } else {
        echo "<p style='color:red'>FEHLER - Verzeichnis nicht beschreibbar!</p>";
    }
} else {
    echo "<p style='color:orange'>Verzeichnis existiert nicht, versuche zu erstellen...</p>";
    if (@mkdir(LOGS_PATH, 0755, true)) {
        echo "<p style='color:green'>OK - Verzeichnis erstellt</p>";
    } else {
        echo "<p style='color:red'>FEHLER - Konnte Verzeichnis nicht erstellen!</p>";
    }
}

// 4. Datenbank-Verbindung
echo "<h2>4. Datenbank-Verbindung</h2>";
try {
    $dbConfig = require CONFIG_PATH . '/database.php';
    echo "<p>Host: " . $dbConfig['host'] . "</p>";
    echo "<p>Database: " . $dbConfig['database'] . "</p>";
    echo "<p>Username: " . $dbConfig['username'] . "</p>";

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $dbConfig['host'],
        $dbConfig['database'],
        $dbConfig['charset']
    );

    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
    echo "<p style='color:green'>OK - Datenbankverbindung erfolgreich!</p>";

    // Pruefe ob Tabellen existieren
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Gefundene Tabellen: " . count($tables) . "</p>";
    if (count($tables) === 0) {
        echo "<p style='color:red'>FEHLER - Keine Tabellen gefunden! Hast du install.sql importiert?</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>{$table}</li>";
        }
        echo "</ul>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red'>DATENBANK-FEHLER: " . $e->getMessage() . "</p>";
}

// 5. Session-Test
echo "<h2>5. Session-Test</h2>";
try {
    session_start();
    echo "<p style='color:green'>OK - Session gestartet</p>";
    echo "<p>Session-ID: " . session_id() . "</p>";
} catch (Throwable $e) {
    echo "<p style='color:red'>FEHLER: " . $e->getMessage() . "</p>";
}

// 6. Klassen laden
echo "<h2>6. Klassen laden</h2>";
$classes = ['Database', 'Session', 'Router', 'Validator', 'Logger', 'Controller'];
foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "<p style='color:green'>OK - {$class}</p>";
    } else {
        echo "<p style='color:red'>FEHLER - {$class} nicht gefunden</p>";
    }
}

// 7. Controller laden
echo "<h2>7. Controller laden</h2>";
$controllers = ['AuthController', 'FarmController', 'FieldController'];
foreach ($controllers as $controller) {
    if (class_exists($controller)) {
        echo "<p style='color:green'>OK - {$controller}</p>";
    } else {
        echo "<p style='color:red'>FEHLER - {$controller} nicht gefunden</p>";
    }
}

// 8. Views pruefen
echo "<h2>8. Views pruefen</h2>";
$views = [
    'auth/login.php',
    'auth/register.php',
    'layouts/main.php',
    'dashboard.php'
];
foreach ($views as $view) {
    $path = VIEWS_PATH . '/' . $view;
    if (file_exists($path)) {
        echo "<p style='color:green'>OK - {$view}</p>";
    } else {
        echo "<p style='color:red'>FEHLER - {$view} nicht gefunden</p>";
    }
}

echo "<hr>";
echo "<p><strong>Fertig!</strong> Loesche diese Datei nach dem Debuggen!</p>";
