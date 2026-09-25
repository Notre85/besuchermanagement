<?php

namespace App;

class PlannedVisit extends BaseModel
{
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO planned_visits
                (visitor_id, host_id, location_id, visit_reason, starts_at, ends_at,
                 access_code_hash, access_code_expires_at, created_by)
             VALUES (:visitor_id, :host_id, :location_id, :visit_reason, :starts_at,
                     :ends_at, :access_code_hash, :access_code_expires_at, :created_by)'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function findUpcoming(int $limit = 100): array
    {
        $limit = max(1, min($limit, 500));
        $stmt = $this->pdo->query(
            "SELECT planned_visits.*, visitors.first_name, visitors.last_name,
                    visitors.company, hosts.email AS host_email,
                    locations.name AS location_name
             FROM planned_visits
             JOIN visitors ON visitors.id = planned_visits.visitor_id
             LEFT JOIN hosts ON hosts.id = planned_visits.host_id
             LEFT JOIN locations ON locations.id = planned_visits.location_id
             WHERE planned_visits.ends_at >= NOW()
               AND planned_visits.status IN ('registered', 'checked_in')
             ORDER BY planned_visits.starts_at ASC
             LIMIT {$limit}"
        );
        return $stmt->fetchAll();
    }

    public function findByAccessCode(string $code)
    {
        $hash = hash('sha256', $code);
        $stmt = $this->pdo->prepare(
            "SELECT * FROM planned_visits
             WHERE access_code_hash = :access_code_hash
               AND access_code_expires_at >= NOW()
               AND status = 'registered' LIMIT 1"
        );
        $stmt->execute(['access_code_hash' => $hash]);
        return $stmt->fetch();
    }

    public function markCheckedIn(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE planned_visits SET status = 'checked_in'
             WHERE id = :id AND status = 'registered'"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function markCheckedOutByVisitor(int $visitorId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE planned_visits SET status = 'checked_out'
             WHERE visitor_id = :visitor_id AND status = 'checked_in'"
        );
        $stmt->execute(['visitor_id' => $visitorId]);
    }

    public function findHostForVisit(int $id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT hosts.id, hosts.email, visitors.first_name, visitors.last_name,
                    planned_visits.starts_at, planned_visits.ends_at
             FROM planned_visits
             JOIN hosts ON hosts.id = planned_visits.host_id AND hosts.active = 1
             JOIN visitors ON visitors.id = planned_visits.visitor_id
             WHERE planned_visits.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function expire(): int
    {
        $stmt = $this->pdo->prepare(
            "UPDATE planned_visits SET status = 'expired'
             WHERE status = 'registered' AND ends_at < NOW()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }
}
