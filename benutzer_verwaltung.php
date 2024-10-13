<?php
// benutzer_verwaltung.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Controllers\BenutzerController;

session_start();

$logger = get_logger();

$action = $_GET['action'] ?? null;

$benutzerController = new BenutzerController($pdo, $logger);

switch ($action) {
    case 'create':
        $benutzerController->createUser();
        break;
    case 'update':
        $benutzerController->updateUser();
        break;
    case 'delete':
        $benutzerController->deleteUser();
        break;
    default:
        $benutzerController->showUserManagement();
        break;
}
