<?php
// index.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';

use App\Controllers\CheckInController;

session_start();

$logger = get_logger();

$checkInController = new CheckInController($pdo, $logger);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkInController->handleCheckIn();
} else {
    $checkInController->showCheckInForm();
}
