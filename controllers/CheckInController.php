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
        // Holen der aktuell eingecheckten Besucher
        $currentVisits = $this->visitModel->getCurrentVisits();
        $this->render('checkin_form', ['currentVisits' => $currentVisits]);
    }

    public function handleCheckIn() {
        // Handle Check-In via Name, Vorname, Firma oder VisitorID (GET-Anfrage)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $visitor_id = isset($_GET['visitor_id']) ? intval($_GET['visitor_id']) : null;
            $first_name = trim($_GET['first_name'] ?? '');
            $last_name = trim($_GET['last_name'] ?? '');
            $company = trim($_GET['company'] ?? '');
            $visit_reason = trim($_GET['visit_reason'] ?? '');

            // Sicherheitsmaßnahme: Überprüfen, ob der Besucher bereits eingecheckt ist
            if ($visitor_id) {
                // Check-In via VisitorID
                if (empty($visit_reason)) {
                    $this->logger->warning('Besuchsgrund fehlt beim Check-In via VisitorID.');
                    $this->redirect('index.php?error=visit_reason_required');
                }

                $visitor = $this->visitorModel->findById($visitor_id);
                if (!$visitor) {
                    $this->logger->warning("Besucher mit ID $visitor_id nicht gefunden.");
                    $this->redirect('index.php?error=visitor_not_found');
                }

                // Überprüfen, ob der Besucher bereits eingecheckt ist
                if ($this->visitModel->isVisitorCheckedIn($visitor_id)) {
                    $this->logger->warning("Besucher ID $visitor_id ist bereits eingecheckt.");
                    $this->redirect('index.php?error=already_checked_in');
                }

                // Check-In durchführen
                $this->visitModel->create($visitor['id'], $visit_reason);
                $this->logger->info("Besucher eingegangen: ID {$visitor['id']} via VisitorID");

            } else {
                // Check-In via Name, Vorname, Firma
                if (empty($first_name) || empty($last_name) || empty($visit_reason)) {
                    $this->logger->warning('Vorname, Nachname oder Besuchsgrund fehlt beim Check-In.');
                    $this->redirect('index.php?error=required_fields_missing');
                }

                // Suchen nach dem Besucher
                $visitor = $this->visitorModel->findByName($first_name, $last_name);
                if (!$visitor) {
                    // Erstellen eines neuen Besuchers
                    $this->visitorModel->create($first_name, $last_name, $company);
                    $visitor = $this->visitorModel->findByName($first_name, $last_name);
                }

                if (!$visitor) {
                    $this->logger->error('Besucher konnte nicht gefunden oder erstellt werden.');
                    $this->redirect('index.php?error=visitor_creation_failed');
                }

                // Überprüfen, ob der Besucher bereits eingecheckt ist
                if ($this->visitModel->isVisitorCheckedIn($visitor['id'])) {
                    $this->logger->warning("Besucher ID {$visitor['id']} ist bereits eingecheckt.");
                    $this->redirect('index.php?error=already_checked_in');
                }

                // Check-In durchführen
                $this->visitModel->create($visitor['id'], $visit_reason);
                $this->logger->info("Besucher eingegangen: ID {$visitor['id']}");
            }

            // Weiterleitung zur Hauptseite mit Erfolgsmeldung
            $this->redirect('index.php?success=checkin');
        } else {
            $this->redirect('index.php');
        }
    }

    public function handleCheckOut() {
        // Handle Check-Out via Visit ID oder Visitor ID (GET-Anfrage)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $visit_id = isset($_GET['visit_id']) ? intval($_GET['visit_id']) : null;
            $visitor_id = isset($_GET['visitor_id']) ? intval($_GET['visitor_id']) : null;

            if ($visit_id) {
                // Check-Out via Visit ID
                $visit = $this->visitModel->findById($visit_id);
                if (!$visit || $visit['checkout_time'] !== null) {
                    $this->logger->warning("Ungültige oder bereits ausgecheckte Visit ID $visit_id.");
                    $this->redirect('index.php?error=invalid_visit_id');
                }

                $result = $this->visitModel->checkout($visit_id);
                if ($result) {
                    $this->logger->info("Besucher ausgecheckt: Visit ID {$visit_id}");
                    $this->redirect('index.php?success=checkout');
                } else {
                    $this->logger->warning("Fehler beim Auschecken von Visit ID {$visit_id}");
                    $this->redirect('index.php?error=checkout_failed');
                }

            } elseif ($visitor_id) {
                // Check-Out via Visitor ID
                $visitor = $this->visitorModel->findById($visitor_id);
                if (!$visitor) {
                    $this->logger->warning("Besucher mit ID $visitor_id nicht gefunden.");
                    $this->redirect('index.php?error=visitor_not_found');
                }

                // Überprüfen, ob der Besucher eingecheckt ist
                if (!$this->visitModel->isVisitorCheckedIn($visitor_id)) {
                    $this->logger->warning("Besucher ID $visitor_id ist nicht eingecheckt.");
                    $this->redirect('index.php?error=not_checked_in');
                }

                $result = $this->visitModel->checkoutByVisitorId($visitor_id);
                if ($result) {
                    $this->logger->info("Alle Besuche ausgecheckt für Visitor ID {$visitor_id}");
                    $this->redirect('index.php?success=checkout');
                } else {
                    $this->logger->warning("Fehler beim Auschecken für Visitor ID {$visitor_id}");
                    $this->redirect('index.php?error=checkout_failed');
                }

            } else {
                $this->logger->warning('Weder Visit ID noch Visitor ID für Check-Out angegeben.');
                $this->redirect('index.php?error=missing_parameters');
            }
        } else {
            $this->redirect('index.php');
        }
    }
}
?>
