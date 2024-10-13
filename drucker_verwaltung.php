<?php
// drucker_verwaltung.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Controllers\DruckerController;

session_start();

$logger = get_logger();

$druckerController = new DruckerController($pdo, $logger);

// Beispiel: Anzeigen der Druckerverwaltung
$druckerController->showDruckerVerwaltung();
