<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Controllers\BackupController;

$controller = new BackupController($pdo, get_logger());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->performBackup();
}

http_response_code(405);
header('Allow: POST');
exit('Methode nicht erlaubt.');
