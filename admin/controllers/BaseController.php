<?php
// admin/controllers/BaseController.php

class BaseController {
    protected $db;

    public function __construct() {
        require_once '../../config/db.php';
        $this->db = Database::getInstance();
        session_start();
        $this->checkAuthentication();
    }

    protected function checkAuthentication() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
    }
}
?>
