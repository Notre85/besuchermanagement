<?php
// models/Visit.php

namespace App;

use PDO;

class Visit extends BaseModel {

    public function create($visitor_id, $visit_reason, $planned_visit_id = null) {
        $this->pdo->beginTransaction();
        try {
            $check = $this->pdo->prepare("SELECT id FROM visits WHERE visitor_id = :visitor_id AND checkout_time IS NULL FOR UPDATE");
            $check->execute(['visitor_id' => $visitor_id]);
            if ($check->fetchColumn() !== false) {
                $this->pdo->rollBack();
                return false;
            }

            $stmt = $this->pdo->prepare("INSERT INTO visits (visitor_id, planned_visit_id, visit_reason, checkin_time) VALUES (:visitor_id, :planned_visit_id, :visit_reason, NOW())");
            $result = $stmt->execute([
                'visitor_id'   => $visitor_id,
                'planned_visit_id' => $planned_visit_id,
                'visit_reason' => $visit_reason
            ]);
            $visitId = (int) $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $result ? $visitId : 0;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function checkout($visit_id) {
        $stmt = $this->pdo->prepare("UPDATE visits SET checkout_time = NOW() WHERE id = :id AND checkout_time IS NULL");
        return $stmt->execute(['id' => $visit_id]);
    }

    public function checkoutByVisitorId($visitor_id) {
        $stmt = $this->pdo->prepare("UPDATE visits SET checkout_time = NOW() WHERE visitor_id = :visitor_id AND checkout_time IS NULL");
        return $stmt->execute(['visitor_id' => $visitor_id]);
    }

    public function getActiveByVisitorId(int $visitorId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM visits WHERE visitor_id = :visitor_id AND checkout_time IS NULL ORDER BY id');
        $stmt->execute(['visitor_id' => $visitorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($visit_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM visits WHERE id = :id");
        $stmt->execute(['id' => $visit_id]);
        return $stmt->fetch();
    }

    public function findBadgeData(int $visitId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT visits.id, visits.checkin_time, visits.checkout_time,
                    visitors.first_name, visitors.last_name, visitors.company,
                    visits.visit_reason, planned_visits.location_id, planned_visits.starts_at, planned_visits.ends_at,
                    planned_visits.status AS planned_status,
                    hosts.first_name AS host_first_name, hosts.last_name AS host_last_name,
                    locations.name AS location_name
             FROM visits
             JOIN visitors ON visitors.id = visits.visitor_id
             LEFT JOIN planned_visits ON planned_visits.id = visits.planned_visit_id
             LEFT JOIN hosts ON hosts.id = planned_visits.host_id
             LEFT JOIN locations ON locations.id = planned_visits.location_id
             WHERE visits.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $visitId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getCurrentVisits() {
        $stmt = $this->pdo->query("
          SELECT visits.id AS visit_id, visits.*, visitors.id AS visitor_id, visitors.first_name, visitors.last_name, visitors.company,
                 planned_visits.host_id, planned_visits.location_id, planned_visits.status AS planned_status
          FROM visits 
          JOIN visitors ON visits.visitor_id = visitors.id 
          LEFT JOIN planned_visits ON planned_visits.id = visits.planned_visit_id
          WHERE visits.checkout_time IS NULL 
          ORDER BY visits.id ASC 
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isVisitorCheckedIn($visitor_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM visits WHERE visitor_id = :visitor_id AND checkout_time IS NULL");
        $stmt->execute(['visitor_id' => $visitor_id]);
        return $stmt->fetchColumn() > 0;
    }

// Berichte nach Datum filtern
public function getVisitsByDateRange($start_date, $end_date) {
    $stmt = $this->pdo->prepare("
        SELECT visits.*, visitors.first_name, visitors.last_name, visitors.company,
               planned_visits.host_id, planned_visits.location_id,
               TIMESTAMPDIFF(SECOND, visits.checkin_time, COALESCE(visits.checkout_time, NOW())) AS duration_seconds
        FROM visits
        JOIN visitors ON visits.visitor_id = visitors.id
        LEFT JOIN planned_visits ON planned_visits.id = visits.planned_visit_id
        WHERE checkin_time BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Berichte nach Besucher filtern
public function getVisitsByVisitor($visitor, $start_date, $end_date) {
    $stmt = $this->pdo->prepare("
        SELECT visits.*, visitors.first_name, visitors.last_name, visitors.company,
               planned_visits.host_id, planned_visits.location_id,
               TIMESTAMPDIFF(SECOND, visits.checkin_time, COALESCE(visits.checkout_time, NOW())) AS duration_seconds
        FROM visits
        JOIN visitors ON visits.visitor_id = visitors.id
        LEFT JOIN planned_visits ON planned_visits.id = visits.planned_visit_id
        WHERE (visitors.first_name LIKE ? OR visitors.last_name LIKE ? OR visitors.id = ?)
        AND visits.checkin_time BETWEEN ? AND ?
    ");
    $visitor_param = "%$visitor%";
    $stmt->execute([$visitor_param, $visitor_param, $visitor, $start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Berichte nach Firma filtern
    public function getVisitsByCompany($company, $start_date, $end_date) {
    $stmt = $this->pdo->prepare("
        SELECT visits.*, visitors.first_name, visitors.last_name, visitors.company,
               planned_visits.host_id, planned_visits.location_id,
               TIMESTAMPDIFF(SECOND, visits.checkin_time, COALESCE(visits.checkout_time, NOW())) AS duration_seconds
        FROM visits
        JOIN visitors ON visits.visitor_id = visitors.id
        LEFT JOIN planned_visits ON planned_visits.id = visits.planned_visit_id
        WHERE visitors.company LIKE ? 
        AND visits.checkin_time BETWEEN ? AND ?
    ");
    $company_param = "%$company%";
    $stmt->execute([$company_param, $start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVisitsByCriteria($reportType, $filter, $startDate, $endDate, $hostId = null, $locationId = null, $status = null) {
        $where = ['visits.checkin_time BETWEEN :start_date AND :end_date'];
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        if ($reportType === 'visitor') {
            $where[] = '(visitors.first_name LIKE :visitor_filter OR visitors.last_name LIKE :visitor_filter OR visitors.id = :visitor_id)';
            $params['visitor_filter'] = '%' . $filter . '%';
            $params['visitor_id'] = ctype_digit((string) $filter) ? (int) $filter : -1;
        } elseif ($reportType === 'company') {
            $where[] = 'visitors.company LIKE :company_filter';
            $params['company_filter'] = '%' . $filter . '%';
        }
        if ($hostId !== null) {
            $where[] = 'planned_visits.host_id = :host_id';
            $params['host_id'] = $hostId;
        }
        if ($locationId !== null) {
            $where[] = 'planned_visits.location_id = :location_id';
            $params['location_id'] = $locationId;
        }
        if ($status !== null) {
            $where[] = "COALESCE(planned_visits.status, CASE WHEN visits.checkout_time IS NULL THEN 'checked_in' ELSE 'checked_out' END) = :visit_status";
            $params['visit_status'] = $status;
        }

        $stmt = $this->pdo->prepare(
            'SELECT visits.*, visitors.first_name, visitors.last_name, visitors.company,
                    planned_visits.host_id, planned_visits.location_id,
                    COALESCE(planned_visits.status, CASE WHEN visits.checkout_time IS NULL THEN \'checked_in\' ELSE \'checked_out\' END) AS visit_status,
                    TIMESTAMPDIFF(SECOND, visits.checkin_time, COALESCE(visits.checkout_time, NOW())) AS duration_seconds
             FROM visits
             JOIN visitors ON visits.visitor_id = visitors.id
             LEFT JOIN planned_visits ON planned_visits.id = visits.planned_visit_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY visits.checkin_time DESC
             LIMIT 5001'
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
