<?php
// models/Visit.php

class Visit {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function checkIn($visitorId, $visitReason) {
        $stmt = $this->db->prepare("INSERT INTO visits (visitor_id, visit_reason) VALUES (:visitor_id, :visit_reason)");
        $stmt->bindParam(':visitor_id', $visitorId);
        $stmt->bindParam(':visit_reason', $visitReason);
        $stmt->execute();
    }

    public function getCheckedInVisitors() {
        $stmt = $this->db->prepare("SELECT v.*, vi.visit_reason, vi.checkin_time FROM visitors v JOIN visits vi ON v.id = vi.visitor_id WHERE vi.checkout_time IS NULL");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVisitsByDateRange($startDate, $endDate) {
        $stmt = $this->db->prepare("SELECT v.*, vi.* FROM visitors v JOIN visits vi ON v.id = vi.visitor_id WHERE vi.checkin_time BETWEEN :start_date AND :end_date");
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Implementieren Sie weitere Methoden für Check-Out und Berichte
}
?>
