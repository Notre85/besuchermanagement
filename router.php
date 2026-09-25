<?php
// router.php

// Einfache Routing-Logik basierend auf dem URL-Pfad

$request = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/router.php';
$script = basename($scriptName) === 'router.php'
    ? rtrim(dirname($scriptName), '/')
    : '';
$path = $script === '' ? ltrim($request, '/') : ltrim(str_replace($script, '', $request), '/');

// Statische Dateien (CSS, Bilder, JavaScript) direkt ausliefern.
// PHP-Endpunkte werden weiterhin ausschließlich über die Whitelist unten geroutet.
if ($path !== '' && is_file(__DIR__ . '/' . $path)) {
    $staticTypes = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'ico' => 'image/x-icon',
        'txt' => 'text/plain; charset=UTF-8',
    ];
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (isset($staticTypes[$extension])) {
        header('Content-Type: ' . $staticTypes[$extension]);
        readfile(__DIR__ . '/' . $path);
        exit;
    }
}

switch ($path) {
    case '':
    case 'index.php':
        require 'index.php';
        break;
    case 'checkin.php':
        require 'index.php';
        break;
    case 'kiosk.php':
        require 'kiosk.php';
        break;
    case 'visitor_management.php':
        require 'visitor_management.php';
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
    case 'badge.php':
        require 'badge.php';
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
    case 'backup.php':
        require 'backup.php';
        break;
    case 'planned_visits.php':
        require 'planned_visits.php';
        break;
    case 'master_data.php':
        require 'master_data.php';
        break;
    case 'key_management.php':
        require 'key_management.php';
        break;
    case 'template_management.php':
        require 'template_management.php';
        break;
    case 'print_profiles.php':
        require 'print_profiles.php';
        break;
    case 'print_agent.php':
        require 'print_agent.php';
        break;
    case 'audit_log.php':
        require 'audit_log.php';
        break;
    case 'print_jobs.php':
        require 'print_jobs.php';
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
