<?php

namespace App;

class Host extends BaseModel
{
    public function getAllActive(): array
    {
        return $this->pdo->query(
            'SELECT * FROM hosts WHERE active = 1 ORDER BY last_name, first_name'
        )->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM hosts WHERE id = :id AND active = 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($firstName, $lastName, $email, $department = null): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO hosts (first_name, last_name, email, department) VALUES (:first_name, :last_name, :email, :department)');
        $stmt->execute(['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'department' => $department]);
        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE hosts SET active = 0 WHERE id = :id AND active = 1');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }
}
