<?php
// admin/controllers/VorlagenController.php

require_once 'BaseController.php';

class VorlagenController extends BaseController {
    public function index() {
        $title = "Vorlagenverwaltung";
        $content = '../../views/admin/vorlagen/index.php';
        include '../../template/layout.php';
    }

    // Implementieren Sie die Methoden edit, update
}
?>
