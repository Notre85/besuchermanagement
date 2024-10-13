<?php
// controllers/BenutzerController.php

namespace App\Controllers;

use App\User;
use PDO;

class BenutzerController extends BaseController {
    protected $userModel;

    public function __construct(PDO $pdo, $logger) {
        parent::__construct($pdo, $logger);
        $this->userModel = new User($pdo);
    }

    /**
     * Zeigt die Benutzerverwaltung an.
     */
    public function showUserManagement() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if (!in_array($currentUser['role'], ['Admin', 'Superadmin'])) {
            die('Zugriff verweigert.');
        }

        $users = $this->userModel->getAllUsers();
        $this->render('benutzer_verwaltung', ['users' => $users]);
    }

    /**
     * Erstellt einen neuen Benutzer.
     */
    public function createUser() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if (!in_array($currentUser['role'], ['Admin', 'Superadmin'])) {
            die('Zugriff verweigert.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'])) {
                $this->logger->error('CSRF-Token-Validierung fehlgeschlagen beim Erstellen eines Benutzers.');
                $this->redirect('benutzer_verwaltung.php?error=csrf_invalid');
            }

            $username   = trim($_POST['username'] ?? '');
            $password   = trim($_POST['password'] ?? '');
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name  = trim($_POST['last_name'] ?? '');
            $role       = $_POST['role'] ?? 'Berichtersteller';

            if (empty($username) || empty($password) || empty($first_name) || empty($last_name)) {
                $this->redirect('benutzer_verwaltung.php?error=required_fields_missing');
            }

            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $result          = $this->userModel->create($username, $hashed_password, $first_name, $last_name, $role);

            if ($result) {
                $this->logger->info("Neuer Benutzer erstellt: $username");
                $this->redirect('benutzer_verwaltung.php?success=create');
            } else {
                $this->logger->error("Fehler beim Erstellen des Benutzers: $username");
                $this->redirect('benutzer_verwaltung.php?error=user_creation_failed');
            }
        } else {
            $this->redirect('benutzer_verwaltung.php');
        }
    }

    /**
     * Aktualisiert einen bestehenden Benutzer.
     */
    public function updateUser() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if (!in_array($currentUser['role'], ['Admin', 'Superadmin'])) {
            die('Zugriff verweigert.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'])) {
                $this->logger->error('CSRF-Token-Validierung fehlgeschlagen beim Aktualisieren eines Benutzers.');
                $this->redirect('benutzer_verwaltung.php?error=csrf_invalid');
            }

            $id         = intval($_POST['id'] ?? 0);
            $username   = trim($_POST['username'] ?? '');
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name  = trim($_POST['last_name'] ?? '');
            $role       = $_POST['role'] ?? 'Berichtersteller';
            $password   = trim($_POST['password'] ?? '');

            if ($id > 0 && !empty($username) && !empty($first_name) && !empty($last_name)) {
                if (!empty($password)) {
                    // Passwort wird aktualisiert
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $result          = $this->userModel->updateWithPassword($id, $username, $hashed_password, $first_name, $last_name, $role);
                } else {
                    // Passwort bleibt unverändert
                    $result = $this->userModel->update($id, $username, $first_name, $last_name, $role);
                }

                if ($result) {
                    $this->logger->info("Benutzer aktualisiert: ID $id");
                    $this->redirect('benutzer_verwaltung.php?success=update');
                } else {
                    $this->logger->error("Fehler beim Aktualisieren des Benutzers: ID $id");
                    $this->redirect('benutzer_verwaltung.php?error=user_update_failed');
                }
            } else {
                $this->logger->warning('Ungültige Daten beim Aktualisieren eines Benutzers.');
                $this->redirect('benutzer_verwaltung.php?error=required_fields_missing');
            }
        } else {
            $this->redirect('benutzer_verwaltung.php');
        }
    }

    /**
     * Löscht einen Benutzer.
     */
    public function deleteUser() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if (!in_array($currentUser['role'], ['Admin', 'Superadmin'])) {
            die('Zugriff verweigert.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'])) {
                $this->logger->error('CSRF-Token-Validierung fehlgeschlagen beim Löschen eines Benutzers.');
                $this->redirect('benutzer_verwaltung.php?error=csrf_invalid');
            }

            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $result = $this->userModel->delete($id);
                if ($result) {
                    $this->logger->info("Benutzer gelöscht: ID $id");
                    $this->redirect('benutzer_verwaltung.php?success=delete');
                } else {
                    $this->logger->error("Fehler beim Löschen des Benutzers: ID $id");
                    $this->redirect('benutzer_verwaltung.php?error=user_deletion_failed');
                }
            } else {
                $this->logger->warning('Ungültige Benutzer-ID beim Löschen.');
                $this->redirect('benutzer_verwaltung.php?error=invalid_user_id');
            }
        } else {
            $this->redirect('benutzer_verwaltung.php');
        }
    }
}
?>
