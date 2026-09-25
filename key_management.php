<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

$controller = new App\Controllers\KeyController($pdo, get_logger());
$action = $_GET['action'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') $controller->create();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'deactivate') $controller->deactivate();
$controller->show();
