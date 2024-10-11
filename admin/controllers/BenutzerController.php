<?php
// admin/controllers/BenutzerController.php

require_once 'BaseController.php';
require_once '../../models/User.php';
require_once '../../models/Role.php';

class BenutzerController extends BaseController {
    public function index() {
        $userModel = new User($this->db);
        $users = $userModel->getAllUsers();
        $title = "Benutzerverwaltung";
        $content = '../../views/admin/benutzer/index.php';
        include '../../template/layout.php';
    }

    // Implementieren Sie die Methoden create, store, edit, update, delete
}
?>
