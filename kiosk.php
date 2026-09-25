<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Controllers\CheckInController;

$allowedNetworks = $_ENV['KIOSK_ALLOWED_NETWORKS'] ?? '127.0.0.0/8,10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,::1/128,fc00::/7';
if (!client_ip_in_networks($_SERVER['REMOTE_ADDR'] ?? '', $allowedNetworks)) {
    http_response_code(403);
    exit('Kiosk-Zugriff aus diesem Netz nicht erlaubt.');
}

$configuredDeviceToken = trim((string) ($_ENV['KIOSK_DEVICE_TOKEN'] ?? ''));
if ($configuredDeviceToken !== '') {
    $providedDeviceToken = (string) ($_SERVER['HTTP_X_KIOSK_DEVICE_TOKEN'] ?? '');
    if ($providedDeviceToken === '' || !hash_equals($configuredDeviceToken, $providedDeviceToken)) {
        http_response_code(403);
        exit('Kiosk-Gerät nicht authentisiert.');
    }
}

$controller = new CheckInController($pdo, get_logger());
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remoteAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $sessionIdentity = $remoteAddress . '|' . session_id();
    $deviceIdentity = (string) ($_ENV['KIOSK_DEVICE_ID'] ?? 'kiosk');
    if (request_rate_limited('kiosk-ip', $remoteAddress, 60, 60)
        || request_rate_limited('kiosk-session', $sessionIdentity, 30, 60)
        || request_rate_limited('kiosk-device', $deviceIdentity, 120, 60)) {
        http_response_code(429);
        exit('Zu viele Anfragen. Bitte später erneut versuchen.');
    }
    if (($_POST['action'] ?? '') === 'issue_key') {
        $controller->handleIssueKey();
    }
    if (($_POST['action'] ?? '') === 'checkout') {
        $controller->handleCheckOut();
    }
    $controller->handleCheckIn();
} else {
    $controller->showKiosk();
}
