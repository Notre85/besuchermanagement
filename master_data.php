<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Controllers\MasterDataController;

$controller = new MasterDataController($pdo, get_logger());
$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create_host') {
        $controller->createHost();
    } elseif ($action === 'create_location') {
        $controller->createLocation();
    } elseif ($action === 'delete_host') {
        $controller->deleteHost();
    } elseif ($action === 'delete_location') {
        $controller->deleteLocation();
    }
}
$controller->show();
