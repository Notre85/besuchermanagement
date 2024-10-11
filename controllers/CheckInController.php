<?php
// controllers/CheckInController.php

require_once 'BaseController.php';
require_once 'models/Visitor.php';
require_once 'models/Visit.php';

class CheckInController extends BaseController {
    public function index() {
        $visitorModel = new Visitor($this->db);
        $visitModel = new Visit($this->db);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Validierung und Verarbeitung des Check-In-Formulars
            $firstName = $_POST['first_name'];
            $lastName = $_POST['last_name'];
            $company = $_POST['company'];
            $visitReason = $_POST['visit_reason'];

            $visitor = $visitorModel->findOrCreate($firstName, $lastName, $company);
            $visitModel->checkIn($visitor['id'], $visitReason);

            // Optional: Besucherausweis drucken
        }

        $checkedInVisitors = $visitModel->getCheckedInVisitors();
        $title = "Besucher-Check-In";
        $content = 'views/checkin_form.php';
        include 'template/layout.php';
    }
}
?>
