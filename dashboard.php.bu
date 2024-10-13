<?php
// dashboard.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Controllers\DashboardController;

session_start();

$logger = get_logger();

$dashboardController = new DashboardController($pdo, $logger);

$dashboardController->showDashboard();
