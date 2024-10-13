<?php
// controllers/DashboardController.php

namespace App\Controllers;

use App\Visit;

class DashboardController extends BaseController {
    protected $visitModel;

    public function __construct($pdo, $logger) {
        parent::__construct($pdo, $logger);
        $this->visitModel = new Visit($pdo);
    }

    public function showDashboard() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if (!in_array($currentUser['role'], ['Berichtersteller', 'Manager', 'Admin', 'Superadmin'])) {
            die('Zugriff verweigert.');
        }

        // Beispielstatistiken
        $totalVisitors = $this->pdo->query("SELECT COUNT(*) AS count FROM visitors")->fetch()['count'];
        $currentVisits = $this->visitModel->getCurrentVisits();

        $this->render('dashboard', [
            'totalVisitors'  => $totalVisitors,
            'currentVisits'  => $currentVisits
        ]);
    }
}
