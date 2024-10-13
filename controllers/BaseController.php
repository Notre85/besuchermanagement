<?php
// controllers/BaseController.php

namespace App\Controllers;

use PDO;

class BaseController {
    protected $pdo;
    protected $logger;

    public function __construct(PDO $pdo, $logger) {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    protected function render($view, $data = []) {
        extract($data);
        include __DIR__ . "/../views/$view.php";
    }

    protected function redirect($url) {
        header("Location: $url");
        exit();
    }

    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
        }
    }

    protected function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return $_SESSION['user'];
        }
        return null;
    }
}
