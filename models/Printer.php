<?php

namespace App;

class Printer extends BaseModel
{
    public function getAllActive(): array
    {
        return $this->pdo->query(
            'SELECT printers.*, locations.name AS location_name
             FROM printers LEFT JOIN locations ON locations.id = printers.location_id
             WHERE printers.active = 1 ORDER BY printers.name'
        )->fetchAll();
    }

    public function create($name, $uri, $locationId = null): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO printers (name, printer_uri, location_id) VALUES (:name, :printer_uri, :location_id)');
        $stmt->execute(['name' => $name, 'printer_uri' => $uri, 'location_id' => $locationId]);
        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE printers SET active = 0 WHERE id = :id AND active = 1');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }
}
