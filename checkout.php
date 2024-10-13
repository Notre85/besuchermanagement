<?php
// checkout.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Controllers\CheckInController;

session_start();

$logger = get_logger();

$checkInController = new CheckInController($pdo, $logger);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkInController->handleCheckOut();
} else {
    header('Location: index.php');
    exit();
}
