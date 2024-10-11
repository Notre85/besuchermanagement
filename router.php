<?php
// router.php

require_once 'config/config.php';

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($request) {
    case '/':
    case '/index.php':
        require 'index.php';
        break;
    // Fügen Sie weitere Routen hinzu
    default:
        http_response_code(404);
        echo "Seite nicht gefunden.";
        break;
}
?>
