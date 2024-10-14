<?php
// controllers/BaseController.php

namespace App\Controllers;

use PDO;

// Einbinden der notwendigen Dateien
require_once __DIR__ . '/../config/csrf.php'; // CSRF-Schutz einbinden

class BaseController {
    protected $pdo;
    protected $logger;

    public function __construct(PDO $pdo, $logger) {
        $this->pdo    = $pdo;
        $this->logger = $logger;
    }

    /**
     * Rendert eine View-Datei und übergibt Daten.
     *
     * @param string $view
     * @param array $data
     */
    protected function render($view, $data = []) {
        extract($data);
        include __DIR__ . "/../views/$view.php";
    }

    /**
     * Leitet zu einer anderen URL weiter.
     *
     * @param string $url
     */
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }

    /**
     * Überprüft, ob der Benutzer eingeloggt ist.
     *
     * @return bool
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    /**
     * Erzwingt die Anmeldung des Benutzers.
     */
    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
        }
    }

    /**
     * Holt den aktuellen Benutzer aus der Session.
     *
     * @return array|null
     */
    protected function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return $_SESSION['user'];
        }
        return null;
    }
}
?>
