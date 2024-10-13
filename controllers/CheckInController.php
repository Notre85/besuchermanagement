<?php
// controllers/CheckInController.php

namespace App\Controllers;

use App\Visitor;
use App\Visit;

class CheckInController extends BaseController {
    protected $visitorModel;
    protected $visitModel;

    public function __construct($pdo, $logger) {
        parent::__construct($pdo, $logger);
        $this->visitorModel = new Visitor($pdo);
        $this->visitModel = new Visit($pdo);
    }

    public function showCheckInForm() {
        $this->render('checkin_form');
    }

    public function handleCheckIn() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'])) {
                $this->logger->error('CSRF-Token-Validierung fehlgeschlagen.');
                die('Ungültiges CSRF-Token.');
            }

            $visitor_id = isset($_POST['visitor_id']) ? intval($_POST['visitor_id']) : null;
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $visit_reason = trim($_POST['visit_reason'] ?? '');
            $company = trim($_POST['company'] ?? '');

            if ($visitor_id) {
                $visitor = $this->visitorModel->findById($visitor_id);
            } else {
                if (empty($first_name) || empty($last_name)) {
                    $this->logger->warning('Vor- oder Nachname fehlt beim Check-In.');
                    die('Vor- und Nachname sind erforderlich.');
                }
                $visitor = $this->visitorModel->findByName($first_name, $last_name);
                if (!$visitor) {
                    $this->visitorModel->create($first_name, $last_name, $company);
                    $visitor = $this->visitorModel->findByName($first_name, $last_name);
                }
            }

            if (!$visitor) {
                $this->logger->error('Besucher konnte nicht gefunden oder erstellt werden.');
                die('Besucher konnte nicht gefunden oder erstellt werden.');
            }

            $this->visitModel->create($visitor['id'], $visit_reason);
            $this->logger->info("Besucher eingegangen: ID {$visitor['id']}");

            $this->redirect('index.php?success=checkin');
        } else {
            $this->redirect('index.php');
        }
    }

    public function handleCheckOut() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'])) {
                $this->logger->error('CSRF-Token-Validierung fehlgeschlagen beim Check-Out.');
                die('Ungültiges CSRF-Token.');
            }

            $visit_id = intval($_POST['visit_id'] ?? 0);
            if ($visit_id > 0) {
                $result = $this->visitModel->checkout($visit_id);
                if ($result) {
                    $this->logger->info("Besucher ausgecheckt: Visit ID {$visit_id}");
                    $this->redirect('index.php?success=checkout');
                } else {
                    $this->logger->warning("Fehler beim Auschecken von Visit ID {$visit_id}");
                    die('Fehler beim Auschecken.');
                }
            } else {
                $this->logger->warning('Ungültige Visit ID beim Check-Out.');
                die('Ungültige Visit ID.');
            }
        } else {
            $this->redirect('index.php');
        }
    }
}
