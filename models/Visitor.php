<?php
// models/Visitor.php

namespace App;

use PDO;

class Visitor extends BaseModel {
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM visitors WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByName($first_name, $last_name) {
        $stmt = $this->pdo->prepare("SELECT * FROM visitors WHERE first_name = :first_name AND last_name = :last_name LIMIT 1");
        $stmt->execute(['first_name' => $first_name, 'last_name' => $last_name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($first_name, $last_name, $company = null) {
        $stmt = $this->pdo->prepare("INSERT INTO visitors (first_name, last_name, company, created_at, updated_at) VALUES (:first_name, :last_name, :company, NOW(), NOW())");
        return $stmt->execute([
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'company'    => $company
        ]);
    }

    public function getAllVisitors() {
        $stmt = $this->pdo->query("SELECT * FROM visitors");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $first_name, $last_name, $company) {
        $stmt = $this->pdo->prepare("UPDATE visitors SET first_name = :first_name, last_name = :last_name, company = :company, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'company'    => $company,
            'id'         => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM visitors WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
