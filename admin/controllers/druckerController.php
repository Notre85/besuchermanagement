<?php
// admin/controllers/DruckerController.php

require_once 'BaseController.php';

class DruckerController extends BaseController {
    public function index() {
        $title = "Druckerverwaltung";
        $content = '../../views/admin/drucker/index.php';
        include '../../template/layout.php';
    }

    // Implementieren Sie die Methoden configure, save
}
?>
