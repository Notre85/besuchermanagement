<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

$controller = new App\Controllers\PrintProfileController($pdo, get_logger());
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'create') $controller->create();
$controller->show();
