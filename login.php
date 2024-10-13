<?php
// login.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\User;

session_start();

$logger = get_logger();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $logger->error('CSRF-Token-Validierung fehlgeschlagen beim Login.');
        die('Ungültiges CSRF-Token.');
    }

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        header('Location: login.php?error=invalid');
        exit();
    }

    $userModel = new User($pdo);
    $user = $userModel->findByUsername($username);

    if ($user && password_verify($password, $user['password'])) {
        // Benutzer ist authentifiziert
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
        $logger->info("Benutzer eingeloggt: $username");
        header('Location: dashboard.php');
        exit();
    } else {
        $logger->warning("Ungültiger Login-Versuch für Benutzer: $username");
        header('Location: login.php?error=invalid');
        exit();
    }
} else {
    include __DIR__ . '/views/login_form.php';
}
