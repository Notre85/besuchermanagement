<?php

namespace App;

class Notification extends BaseModel
{
    public function create(int $hostId, int $plannedVisitId, string $type): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO notifications (host_id, planned_visit_id, type)
             VALUES (:host_id, :planned_visit_id, :type)'
        );
        $stmt->execute([
            'host_id' => $hostId,
            'planned_visit_id' => $plannedVisitId,
            'type' => $type,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function markSent(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE notifications SET sent_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function markFailed(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE notifications SET failed_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function getRecentFailures(int $limit = 25): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->pdo->query(
            "SELECT notifications.*, hosts.first_name, hosts.last_name, hosts.email
             FROM notifications
             JOIN hosts ON hosts.id = notifications.host_id
             WHERE notifications.failed_at IS NOT NULL
             ORDER BY notifications.failed_at DESC
             LIMIT {$limit}"
        );
        return $stmt->fetchAll();
    }
}
