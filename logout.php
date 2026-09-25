<?php
// logout.php

require_once __DIR__ . '/config/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(405);
    header('Allow: POST');
    exit('Methode nicht erlaubt.');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
}
session_destroy();

header('Location: login.php');
exit();
