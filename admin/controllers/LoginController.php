<?php
// admin/controllers/LoginController.php

class LoginController {
    protected $db;

    public function __construct() {
        require_once '../config/db.php';
        $this->db = Database::getInstance();
        session_start();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validierung und Authentifizierung
            $username = $_POST['username'];
            $password = $_POST['password'];

            $userModel = new User($this->db);
            $user = $userModel->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Ungültige Anmeldedaten.";
            }
        }

        $title = "Admin Login";
        $content = '../views/admin/login.php';
        include '../template/layout.php';
    }

    public function logout() {
        session_destroy();
        header('Location: login.php');
    }
}
?>
