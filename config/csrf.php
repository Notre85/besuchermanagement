<?php
// config/csrf.php

function generateToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function validateToken($token) {
    return hash_equals($_SESSION['csrf_token'], $token);
}
?>
