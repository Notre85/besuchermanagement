<?php
// report.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Controllers\ReportController;

session_start();

$logger = get_logger();

$reportController = new ReportController($pdo, $logger);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportController->generateReport();
} else {
    $reportController->showReportForm();
}

