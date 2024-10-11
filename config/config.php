<?php
// config/config.php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/csrf.php';

// Laden der Umgebungsvariablen
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = parse_ini_file(__DIR__ . '/.env', false, INI_SCANNER_TYPED);
    foreach ($dotenv as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Fehlerberichterstattung
if ($_ENV['APP_DEBUG']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
?>
