<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

$controller = new App\Controllers\TemplateController($pdo, get_logger());
$action = $_GET['action'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') $controller->create();
    if ($action === 'version') $controller->createVersion();
    if ($action === 'activate') $controller->activate();
    if ($action === 'preview') $controller->preview();
}
$controller->show();
