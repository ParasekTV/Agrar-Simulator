<?php
/**
 * Allgemeine Konfiguration
 */

// Fehleranzeige (in Produktion auf false setzen)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Basis-URL der Anwendung (ohne abschliessenden Slash)
define('BASE_URL', '');
define('SITE_URL', 'https://agrar.sl-wide.de');

// Pfade
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEWS_PATH', APP_PATH . '/views');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Session-Konfiguration
define('SESSION_NAME', 'farming_session');
define('SESSION_LIFETIME', 86400); // 24 Stunden

// Spiel-Konstanten
define('STARTING_MONEY', 10000.00);
define('STARTING_FIELDS', 2);
define('STARTING_FIELD_SIZE', 1.0); // Hektar

// Level-System
define('POINTS_PER_LEVEL_MULTIPLIER', 100);

// Punkte-Belohnungen
define('POINTS_FIELD_WORK', 5);
define('POINTS_HARVEST', 10);
define('POINTS_SALE_PER_100', 1);
define('POINTS_BUILDING', 20);
define('POINTS_DAILY_LOGIN', 5);
define('POINTS_LEVEL_UP_BONUS', 50);
define('POINTS_TRADE', 5);
define('POINTS_PRODUCTION', 10);

// Rate-Limiting
define('RATE_LIMIT_LOGIN_ATTEMPTS', 5);
define('RATE_LIMIT_LOGIN_WINDOW', 900); // 15 Minuten
define('RATE_LIMIT_API_REQUESTS', 100);
define('RATE_LIMIT_API_WINDOW', 3600); // 1 Stunde

// CSRF-Token Lebensdauer
define('CSRF_TOKEN_LIFETIME', 3600);

// Zeitzone
date_default_timezone_set('Europe/Berlin');

// Discord Webhook (für News/Changelog)
define('DISCORD_WEBHOOK_URL', 'https://discord.com/api/webhooks/1467796540679782485/QWckUsK0QvXKam7CVhT6pR2bRVSuP5nStYNyYj72Odx7PUTVF2jy7YJcDn9bDk4jdaNE');
define('DISCORD_WEBHOOK_ENABLED', true);

// ============================================
// reCAPTCHA v3 Konfiguration
// ============================================
define('RECAPTCHA_ENABLED', true); // Auf true setzen und Keys eintragen
define('RECAPTCHA_SITE_KEY', '6LfQmWIsAAAAAFQ8p28FS-rboKZD3Hp1fbLnKVbf'); // Von Google reCAPTCHA Admin
define('RECAPTCHA_SECRET_KEY', '6LfQmWIsAAAAAFS1O-st1BuEbjqD_LdyNia0ZGPw'); // Von Google reCAPTCHA Admin
define('RECAPTCHA_MIN_SCORE', 0.5); // 0.0 = Bot, 1.0 = Mensch

// ============================================
// E-Mail Verifizierung
// ============================================
define('EMAIL_VERIFICATION_ENABLED', true); // Auf true setzen wenn SMTP konfiguriert
define('EMAIL_TOKEN_EXPIRY_HOURS', 24);

// SMTP-Einstellungen
define('SMTP_HOST', 'smtp.sl-wide.de'); // z.B. smtp.example.com
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@sl-wide.de');
define('SMTP_PASSWORD', '@nR874tm4');
define('SMTP_FROM_EMAIL', 'noreply@sl-wide.de');
define('SMTP_FROM_NAME', 'Agrar Simulator');
define('SMTP_ENCRYPTION', 'tls'); // tls oder ssl

// ============================================
// Discord OAuth2 Konfiguration
// ============================================
define('DISCORD_OAUTH_ENABLED', true); // Auf true setzen und Keys eintragen
define('DISCORD_CLIENT_ID', '1469295259023442012'); // Von Discord Developer Portal
define('DISCORD_CLIENT_SECRET', '4b95PQrpif2-A660ZDp95mloOEk_iuGF'); // Von Discord Developer Portal
define('DISCORD_REDIRECT_URI', SITE_URL . '/auth/discord/callback');

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/core/',
        APP_PATH . '/models/',
        APP_PATH . '/controllers/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
