<?php
// controllers/DruckerController.php

namespace App\Controllers;

use App\Location;
use App\Printer;

class DruckerController extends BaseController {
    private $printerModel;
    private $locationModel;

    public function __construct($pdo, $logger) {
        parent::__construct($pdo, $logger);
        $this->printerModel = new Printer($pdo);
        $this->locationModel = new Location($pdo);
    }
    // Implementierung der Druckerverwaltung
    // Diese kann je nach Anforderungen erweitert werden
    // Beispiel: Hinzufügen, Bearbeiten, Löschen von Druckern

    public function showDruckerVerwaltung() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if (!in_array($currentUser['role'], ['Admin', 'Superadmin'])) {
            die('Zugriff verweigert.');
        }

        $this->render('drucker_verwaltung', [
            'printers' => $this->printerModel->getAllActive(),
            'locations' => $this->locationModel->getAllActive(),
        ]);
    }

    public function createPrinter() {
        $this->requirePost();
        $this->requireRole(['Admin', 'Superadmin']);
        $this->requireCsrf();
        $name = trim($_POST['name'] ?? '');
        $uri = trim($_POST['printer_uri'] ?? '');
        $locationId = filter_input(INPUT_POST, 'location_id', FILTER_VALIDATE_INT) ?: null;
        if ($name === '' || mb_strlen($name) > 100 || mb_strlen($uri) > 255 || !preg_match('/^[a-z][a-z0-9+.-]*:\/\//i', $uri)) {
            $this->redirect('drucker_verwaltung.php?error=invalid_input');
        }
        $id = $this->printerModel->create($name, $uri, $locationId);
        $this->audit('printer.create', 'printer', $id);
        $this->redirect('drucker_verwaltung.php?success=created');
    }

    public function deletePrinter() {
        $this->requirePost();
        $this->requireRole(['Admin', 'Superadmin']);
        $this->requireCsrf();
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id || !$this->printerModel->delete($id)) {
            $this->redirect('drucker_verwaltung.php?error=delete_failed');
        }
        $this->audit('printer.delete', 'printer', $id);
        $this->redirect('drucker_verwaltung.php?success=deleted');
    }

    // Weitere Methoden für das Hinzufügen, Bearbeiten und Löschen von Druckern können hier hinzugefügt werden
}
