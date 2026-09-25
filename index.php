<?php
// index.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Controllers\CheckInController;

$logger = get_logger();

// Initialisieren des Controllers
$checkInController = new CheckInController($pdo, $logger);

// Überprüfen des 'action' Parameters
$action = $_POST['action'] ?? $_GET['action'] ?? null;

switch ($action) {
    case 'checkin':
        // Handle Check-In via Name, Vorname, Firma oder VisitorID
        $checkInController->handleCheckIn();
        break;
    
    case 'checkout':
        // Handle Check-Out via Visit ID oder Visitor ID
        $checkInController->handleCheckOut();
        break;
    case 'issue_key':
        $checkInController->handleIssueKey();
        break;
    
    default:
        if (isset($_SESSION['user'])) {
            $checkInController->showCheckInForm();
        } else {
            // Der Haupteinstieg bleibt die klassische Loginseite.
            include __DIR__ . '/views/login_form.php';
        }
        break;
}
?>
