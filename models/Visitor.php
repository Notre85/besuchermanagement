<?php
// models/Visitor.php

class Visitor {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function findOrCreate($firstName, $lastName, $company) {
        $stmt = $this->db->prepare("SELECT * FROM visitors WHERE first_name = :first_name AND last_name = :last_name");
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->execute();
        $visitor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($visitor) {
            return $visitor;
        } else {
            $stmt = $this->db->prepare("INSERT INTO visitors (first_name, last_name, company) VALUES (:first_name, :last_name, :company)");
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':company', $company);
            $stmt->execute();
            return $this->findById($this->db->lastInsertId());
        }
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM visitors WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Implementieren Sie weitere Methoden für CRUD-Operationen
}
?>
