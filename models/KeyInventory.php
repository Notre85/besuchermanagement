<?php

namespace App;

use PDO;

class KeyInventory extends BaseModel
{
    public function getAllActive(): array
    {
        return $this->pdo->query(
            'SELECT keys_inventory.*, locations.name AS location_name
             FROM keys_inventory
             LEFT JOIN locations ON locations.id = keys_inventory.location_id
             WHERE keys_inventory.active = 1
             ORDER BY keys_inventory.key_number'
        )->fetchAll();
    }

    public function getAvailable(): array
    {
        return $this->pdo->query(
            "SELECT keys_inventory.*, locations.name AS location_name
             FROM keys_inventory
             LEFT JOIN locations ON locations.id = keys_inventory.location_id
             WHERE keys_inventory.active = 1 AND keys_inventory.status = 'available'
             ORDER BY keys_inventory.key_number"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignSpecific(int $keyId, int $visitId, ?int $locationId, ?int $userId): ?array
    {
        $this->pdo->beginTransaction();
        try {
            $visitStmt = $this->pdo->prepare('SELECT id FROM visits WHERE id = :visit_id AND checkout_time IS NULL FOR UPDATE');
            $visitStmt->execute(['visit_id' => $visitId]);
            if (!$visitStmt->fetchColumn()) {
                $this->pdo->rollBack();
                return null;
            }
            $assignmentStmt = $this->pdo->prepare("SELECT id FROM key_assignments WHERE visit_id = :visit_id AND status = 'issued' FOR UPDATE");
            $assignmentStmt->execute(['visit_id' => $visitId]);
            if ($assignmentStmt->fetchColumn()) {
                $this->pdo->rollBack();
                return null;
            }
            $stmt = $this->pdo->prepare(
                "SELECT * FROM keys_inventory
                 WHERE id = :id AND active = 1 AND status = 'available'
                 FOR UPDATE"
            );
            $stmt->execute(['id' => $keyId]);
            $key = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$key || ($locationId !== null && (int) $key['location_id'] !== $locationId)) {
                $this->pdo->rollBack();
                return null;
            }

            $update = $this->pdo->prepare("UPDATE keys_inventory SET status = 'issued' WHERE id = :id AND status = 'available'");
            $update->execute(['id' => $keyId]);
            if ($update->rowCount() !== 1) {
                throw new \RuntimeException('Schlüssel konnte nicht reserviert werden.');
            }
            $assignment = $this->pdo->prepare(
                'INSERT INTO key_assignments (key_id, visit_id, issued_by)
                 VALUES (:key_id, :visit_id, :issued_by)'
            );
            $assignment->execute(['key_id' => $keyId, 'visit_id' => $visitId, 'issued_by' => $userId]);
            $key['assignment_id'] = (int) $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $key;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function create(string $number, string $label, string $type, ?int $locationId): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO keys_inventory (key_number, label, key_type, location_id)
             VALUES (:number, :label, :type, :location_id)'
        );
        $stmt->execute(['number' => $number, 'label' => $label, 'type' => $type, 'location_id' => $locationId]);
        return (int) $this->pdo->lastInsertId();
    }

    public function deactivate(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE keys_inventory SET active = 0, status = 'blocked' WHERE id = :id AND status = 'available' AND active = 1");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function assignAvailable(int $visitId, ?int $locationId, ?int $userId, ?string $notes = null): ?array
    {
        $this->pdo->beginTransaction();
        try {
            $sql = "SELECT * FROM keys_inventory
                    WHERE active = 1 AND status = 'available'";
            if ($locationId !== null) {
                $sql .= ' AND location_id = :location_id';
            }
            $sql .= ' ORDER BY key_number LIMIT 1 FOR UPDATE';
            $stmt = $this->pdo->prepare($sql);
            if ($locationId !== null) {
                $stmt->bindValue(':location_id', $locationId, PDO::PARAM_INT);
            }
            $stmt->execute();
            $key = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$key) {
                $this->pdo->rollBack();
                return null;
            }
            $update = $this->pdo->prepare("UPDATE keys_inventory SET status = 'issued' WHERE id = :id AND status = 'available'");
            $update->execute(['id' => $key['id']]);
            if ($update->rowCount() !== 1) {
                throw new \RuntimeException('Schlüssel konnte nicht reserviert werden.');
            }
            $assignment = $this->pdo->prepare(
                'INSERT INTO key_assignments (key_id, visit_id, issued_by, notes)
                 VALUES (:key_id, :visit_id, :issued_by, :notes)'
            );
            $assignment->execute(['key_id' => $key['id'], 'visit_id' => $visitId, 'issued_by' => $userId, 'notes' => $notes]);
            $key['assignment_id'] = (int) $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $key;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function returnForVisit(int $visitId, ?int $userId, ?string $notes = null): bool
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "SELECT key_id FROM key_assignments WHERE visit_id = :visit_id AND status = 'issued' FOR UPDATE"
            );
            $stmt->execute(['visit_id' => $visitId]);
            $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$assignment) {
                $this->pdo->rollBack();
                return false;
            }
            $updateAssignment = $this->pdo->prepare(
                "UPDATE key_assignments SET status = 'returned', returned_at = NOW(), returned_by = :user_id, notes = COALESCE(:notes, notes) WHERE visit_id = :visit_id AND status = 'issued'"
            );
            $updateAssignment->execute(['user_id' => $userId, 'notes' => $notes, 'visit_id' => $visitId]);
            $updateKey = $this->pdo->prepare("UPDATE keys_inventory SET status = 'available' WHERE id = :id AND status = 'issued'");
            $updateKey->execute(['id' => $assignment['key_id']]);
            if ($updateAssignment->rowCount() !== 1 || $updateKey->rowCount() !== 1) {
                throw new \RuntimeException('Schlüsselrückgabe konnte nicht gespeichert werden.');
            }
            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function findAssignmentForVisit(int $visitId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT key_assignments.*, keys_inventory.key_number, keys_inventory.label
             FROM key_assignments JOIN keys_inventory ON keys_inventory.id = key_assignments.key_id
             WHERE key_assignments.visit_id = :visit_id ORDER BY key_assignments.id DESC LIMIT 1"
        );
        $stmt->execute(['visit_id' => $visitId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAssignmentHistory(int $limit = 500): array
    {
        $limit = max(1, min($limit, 1000));
        $stmt = $this->pdo->query(
            "SELECT key_assignments.*, keys_inventory.key_number, keys_inventory.label,
                    visits.id AS visit_id, visits.checkin_time, visits.checkout_time,
                    visitors.id AS visitor_id, visitors.first_name, visitors.last_name,
                    issuer.username AS issued_by_username, returner.username AS returned_by_username
             FROM key_assignments
             JOIN keys_inventory ON keys_inventory.id = key_assignments.key_id
             JOIN visits ON visits.id = key_assignments.visit_id
             JOIN visitors ON visitors.id = visits.visitor_id
             LEFT JOIN users issuer ON issuer.id = key_assignments.issued_by
             LEFT JOIN users returner ON returner.id = key_assignments.returned_by
             ORDER BY key_assignments.issued_at DESC
             LIMIT {$limit}"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
