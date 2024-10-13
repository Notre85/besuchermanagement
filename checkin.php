<?php
// checkin.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Controllers\CheckInController;

session_start();

$logger = get_logger();

// Überprüfen, ob die Anfrage eine POST-Anfrage ist
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkInController = new CheckInController($pdo, $logger);
    $checkInController->handleCheckIn();
} else {
    // Wenn keine POST-Anfrage, leitet zur Hauptseite weiter
    header('Location: index.php');
    exit();
}
?>
