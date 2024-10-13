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

    public function getVisitsByVisitor($visitor_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM visits WHERE visitor_id = :visitor_id ORDER BY checkin_time DESC");
        $stmt->execute(['visitor_id' => $visitor_id]);
        return $stmt->fetchAll();
    }

    public function getCurrentVisits() {
        $stmt = $this->pdo->query("SELECT visits.*, visitors.first_name, visitors.last_name FROM visits JOIN visitors ON visits.visitor_id = visitors.id WHERE visits.checkout_time IS NULL");
        return $stmt->fetchAll();
    }

    public function getVisitsByDateRange($start_date, $end_date) {
        $stmt = $this->pdo->prepare("SELECT visits.*, visitors.first_name, visitors.last_name, visitors.company FROM visits JOIN visitors ON visits.visitor_id = visitors.id WHERE visits.checkin_time BETWEEN :start_date AND :end_date ORDER BY visits.checkin_time DESC");
        $stmt->execute([
            'start_date' => $start_date,
            'end_date'   => $end_date
        ]);
        return $stmt->fetchAll();
    }
}
