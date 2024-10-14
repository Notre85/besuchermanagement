<?php
// models/Visit.php

namespace App;

use PDO;

class Visit extends BaseModel {

    public function create($visitor_id, $visit_reason) {
        $stmt = $this->pdo->prepare("INSERT INTO visits (visitor_id, visit_reason, checkin_time) VALUES (:visitor_id, :visit_reason, NOW())");
        return $stmt->execute([
            'visitor_id'   => $visitor_id,
            'visit_reason' => $visit_reason
        ]);
    }

    public function checkout($visit_id) {
        $stmt = $this->pdo->prepare("UPDATE visits SET checkout_time = NOW() WHERE id = :id AND checkout_time IS NULL");
        return $stmt->execute(['id' => $visit_id]);
    }

    public function checkoutByVisitorId($visitor_id) {
        $stmt = $this->pdo->prepare("UPDATE visits SET checkout_time = NOW() WHERE visitor_id = :visitor_id AND checkout_time IS NULL");
        return $stmt->execute(['visitor_id' => $visitor_id]);
    }

    public function findById($visit_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM visits WHERE id = :id");
        $stmt->execute(['id' => $visit_id]);
        return $stmt->fetch();
    }

    public function getCurrentVisits() {
        $stmt = $this->pdo->query("
          SELECT visits.id AS visit_id, visits.*, visitors.id AS visitor_id, visitors.first_name, visitors.last_name, visitors.company 
          FROM visits 
          JOIN visitors ON visits.visitor_id = visitors.id 
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
        SELECT visits.*, visitors.first_name, visitors.last_name, visitors.company 
        FROM visits
        JOIN visitors ON visits.visitor_id = visitors.id
        WHERE checkin_time BETWEEN ? AND ?
    ");
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Berichte nach Besucher filtern
public function getVisitsByVisitor($visitor, $start_date, $end_date) {
    $stmt = $this->pdo->prepare("
        SELECT visits.*, visitors.first_name, visitors.last_name, visitors.company 
        FROM visits
        JOIN visitors ON visits.visitor_id = visitors.id
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
        SELECT visits.*, visitors.first_name, visitors.last_name, visitors.company 
        FROM visits
        JOIN visitors ON visits.visitor_id = visitors.id
        WHERE visitors.company LIKE ? 
        AND visits.checkin_time BETWEEN ? AND ?
    ");
    $company_param = "%$company%";
    $stmt->execute([$company_param, $start_date, $end_date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
}
