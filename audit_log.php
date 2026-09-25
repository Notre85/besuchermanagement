<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

$controller = new App\Controllers\AuditLogController($pdo, get_logger());
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv'])) $controller->exportCsv();
$controller->show();
