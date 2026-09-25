<?php

namespace App;

class Location extends BaseModel
{
    public function getAllActive(): array
    {
        return $this->pdo->query(
            'SELECT * FROM locations WHERE active = 1 ORDER BY name'
        )->fetchAll();
    }

    public function create($name, $building = null, $floor = null): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO locations (name, building, floor) VALUES (:name, :building, :floor)');
        $stmt->execute(['name' => $name, 'building' => $building, 'floor' => $floor]);
        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE locations SET active = 0 WHERE id = :id AND active = 1');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }
}
