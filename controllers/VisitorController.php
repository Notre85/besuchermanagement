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
        $this->requireRole(['Manager', 'Admin', 'Superadmin']);

        $term = trim($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 50;
        $sortOptions = ['id', 'first_name', 'last_name', 'company', 'created_at'];
        $sort = in_array($_GET['sort'] ?? '', $sortOptions, true) ? $_GET['sort'] : 'last_name';
        $direction = strtolower($_GET['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $total = $term === '' ? count($this->visitorModel->getAllVisitors()) : $this->visitorModel->countSearch($term);
        $visitors = $term === ''
            ? $this->visitorModel->search('', $perPage, ($page - 1) * $perPage, $sort, $direction)
            : $this->visitorModel->search($term, $perPage, ($page - 1) * $perPage, $sort, $direction);
        $this->render('visitor_form', ['visitors' => $visitors, 'term' => $term, 'page' => $page, 'perPage' => $perPage, 'total' => $total, 'sort' => $sort, 'direction' => $direction]);
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
        $this->requirePost();
        $this->requireRole(['Manager', 'Admin', 'Superadmin']);
        $this->requireCsrf();
        $visitor_id = $_POST['id'] ?? null;
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $company = trim($_POST['company'] ?? '');

        if (filter_var($visitor_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) && $first_name && $last_name && mb_strlen($first_name) <= 50 && mb_strlen($last_name) <= 50 && mb_strlen($company) <= 100) {
            // Besucher-Daten aktualisieren
            if (!$this->visitorModel->update($visitor_id, $first_name, $last_name, $company)) {
                $this->redirect('visitor_management.php?error=update_failed');
            }
            $this->audit('visitor.update', 'visitor', (int) $visitor_id);
            $this->redirect('visitor_management.php?success=update');
        } else {
            $this->redirect('visitor_management.php?error=invalid_input');
        }
    }

    // Löschen eines Besuchers
    public function deleteVisitor()
    {
        $this->requirePost();
        $this->requireRole(['Manager', 'Admin', 'Superadmin']);
        $this->requireCsrf();
        $visitor_id = $_POST['id'] ?? null;
        if (filter_var($visitor_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            // Besucher löschen
            if (!$this->visitorModel->anonymize((int) $visitor_id)) {
                $this->redirect('visitor_management.php?error=delete_failed');
            }
            $this->audit('visitor.anonymize', 'visitor', (int) $visitor_id);
            $this->redirect('visitor_management.php?success=delete');
        } else {
            $this->redirect('visitor_management.php?error=invalid_input');
        }
    }
}
