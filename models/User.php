<?php
// models/User.php

class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    } 

    public function getAllUsers() {
        $stmt = $this->db->prepare("SELECT users.*, roles.role_name FROM users LEFT JOIN roles ON users.role_id = roles.id");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Implementieren Sie weitere Methoden für CRUD-Operationen
}
?>
