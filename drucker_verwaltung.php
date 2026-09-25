<?php
// drucker_verwaltung.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Controllers\DruckerController;

$logger = get_logger();

$druckerController = new DruckerController($pdo, $logger);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_GET['action'] ?? '') === 'create') {
        $druckerController->createPrinter();
    } elseif (($_GET['action'] ?? '') === 'delete') {
        $druckerController->deletePrinter();
    }
}
$druckerController->showDruckerVerwaltung();
