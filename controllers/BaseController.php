<?php
// controllers/BaseController.php

class BaseController {
    protected $db;

    public function __construct() {
        require_once 'config/db.php';
        $this->db = Database::getInstance();
        session_start();
    }
}
?>
