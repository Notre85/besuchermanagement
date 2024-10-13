<?php
// router.php

// Einfache Routing-Logik basierend auf dem URL-Pfad

$request = $_SERVER['REQUEST_URI'];
$script = dirname($_SERVER['SCRIPT_NAME']);
$path = str_replace($script, '', $request);
$path = trim($path, '/');

switch ($path) {
    case '':
    case 'index.php':
        require 'index.php';
        break;
    case 'checkin.php':
        require 'index.php';
        break;
    case 'checkout.php':
        require 'checkout.php';
        break;
    case 'report.php':
        require 'report.php';
        break;
    case 'details.php':
        require 'details.php';
        break;
    case 'benutzer_verwaltung.php':
        require 'benutzer_verwaltung.php';
        break;
    case 'drucker_verwaltung.php':
        require 'drucker_verwaltung.php';
        break;
    case 'dashboard.php':
        require 'dashboard.php';
        break;
    case 'login.php':
        require 'login.php';
        break;
    case 'logout.php':
        require 'logout.php';
        break;
    default:
        http_response_code(404);
        echo "Seite nicht gefunden.";
        break;
}
