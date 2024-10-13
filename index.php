<?php
// index.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Controllers\CheckInController;

// Starten der Session
session_start();

$logger = get_logger();

// Initialisieren des Controllers
$checkInController = new CheckInController($pdo, $logger);

// Überprüfen des 'action' Parameters
$action = $_GET['action'] ?? null;

switch ($action) {
    case 'checkin':
        // Handle Check-In via Name, Vorname, Firma oder VisitorID
        $checkInController->handleCheckIn();
        break;
    
    case 'checkout':
        // Handle Check-Out via Visit ID oder Visitor ID
        $checkInController->handleCheckOut();
        break;
    
    default:
        // Standardanzeige der Check-In Seite
        $checkInController->showCheckInForm();
        break;
}
?>
