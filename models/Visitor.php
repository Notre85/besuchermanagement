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

    public function search($term, $limit, $offset, $sort = 'last_name', $direction = 'asc') {
        $sortColumns = [
            'id' => 'id', 'first_name' => 'first_name', 'last_name' => 'last_name',
            'company' => 'company', 'created_at' => 'created_at',
        ];
        $sort = $sortColumns[$sort] ?? $sortColumns['last_name'];
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
        $stmt = $this->pdo->prepare(
            "SELECT * FROM visitors
             WHERE first_name LIKE :term OR last_name LIKE :term OR company LIKE :term OR id = :id
             ORDER BY {$sort} {$direction}, id ASC
             LIMIT :limit OFFSET :offset"
        );
        $numericId = ctype_digit((string) $term) ? (int) $term : -1;
        $stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
        $stmt->bindValue(':id', $numericId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countSearch($term) {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM visitors
             WHERE first_name LIKE :term OR last_name LIKE :term OR company LIKE :term OR id = :id'
        );
        $numericId = ctype_digit((string) $term) ? (int) $term : -1;
        $stmt->execute(['term' => '%' . $term . '%', 'id' => $numericId]);
        return (int) $stmt->fetchColumn();
    }

    public function update($id, $first_name, $last_name, $company) {
        $stmt = $this->pdo->prepare("UPDATE visitors SET first_name = :first_name, last_name = :last_name, company = :company, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'company'    => $company,
            'id'         => $id
        ]);
        return $stmt->rowCount() === 1;
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM visitors WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function anonymize(int $id): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE visitors
             SET first_name = 'Anonymisiert',
                 last_name = CONCAT('Besucher-', id),
                 company = NULL,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }
}
