<?php
// config/db.php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$envFile = getenv('BESUCHERMANAGEMENT_ENV_FILE') ?: dirname(__DIR__) . '.env';
$dotenv = Dotenv::createImmutable(dirname($envFile), basename($envFile));
$dotenv->load();

try {
    $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    error_log('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
    http_response_code(503);
    die('Datenbank momentan nicht verfügbar.');
}
