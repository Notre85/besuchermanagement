<?php
// controllers/VisitorController.php

namespace App\Controllers;

use App\Visitor;
use PDO;

class VisitorController extends BaseController
{
    protected $visitorModel;

    public function __construct(PDO $pdo, $logger)
    {
        parent::__construct($pdo, $logger);
        $this->visitorModel = new Visitor($pdo);
    }

    // Anzeige der Besucherverwaltung
    public function showVisitorManagement()
    {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if (!in_array($currentUser['role'], ['Manager', 'Admin', 'Superadmin'])) {
            die('Zugriff verweigert.');
        }

        // Alle Besucher abfragen und in der View anzeigen
        $visitors = $this->visitorModel->getAllVisitors();
        $this->render('visitor_form', ['visitors' => $visitors]); // Die View visitor_form wird gerendert
    }

    // Verarbeitet POST-Anfragen (Aktualisieren oder Löschen)
    public function handlePostRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;

            switch ($action) {
                case 'updateVisitor':
                    $this->updateVisitor();
                    break;
                case 'deleteVisitor':
                    $this->deleteVisitor();
                    break;
                default:
                    $this->redirect('visitor_management.php?error=unknown_action');
            }
        }
    }

    // Aktualisieren eines Besuchers
    public function updateVisitor()
    {
        $visitor_id = $_POST['id'] ?? null;
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $company = trim($_POST['company'] ?? '');

        if ($visitor_id && $first_name && $last_name) {
            // Besucher-Daten aktualisieren
            $this->visitorModel->update($visitor_id, $first_name, $last_name, $company);
            $this->redirect('visitor_management.php?success=update');
        } else {
            $this->redirect('visitor_management.php?error=invalid_input');
        }
    }

    // Löschen eines Besuchers
    public function deleteVisitor()
    {
        $visitor_id = $_POST['id'] ?? null;
        if ($visitor_id) {
            // Besucher löschen
            $this->visitorModel->delete($visitor_id);
            $this->redirect('visitor_management.php?success=delete');
        } else {
            $this->redirect('visitor_management.php?error=invalid_input');
        }
    }
}
