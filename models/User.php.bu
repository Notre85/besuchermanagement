<?php
// models/User.php

namespace App;

use PDO;

class User extends BaseModel {
    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    public function create($username, $password, $first_name, $last_name, $role) {
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password, first_name, last_name, role, created_at) VALUES (:username, :password, :first_name, :last_name, :role, NOW())");
        return $stmt->execute([
            'username'   => $username,
            'password'   => $password,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => $role
        ]);
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT * FROM users");
        return $stmt->fetchAll();
    }

    public function update($id, $username, $first_name, $last_name, $role) {
        $stmt = $this->pdo->prepare("UPDATE users SET username = :username, first_name = :first_name, last_name = :last_name, role = :role, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([
            'username'   => $username,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => $role,
            'id'         => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
