<?php
// admin/controllers/BesucherController.php

require_once 'BaseController.php';
require_once '../../models/Visitor.php';

class BesucherController extends BaseController {
    public function index() {
        $visitorModel = new Visitor($this->db);
        $visitors = $visitorModel->getAllVisitors();
        $title = "Besucherverwaltung";
        $content = '../../views/admin/besucher/index.php';
        include '../../template/layout.php';
    }

    // Implementieren Sie die Methoden edit, update, delete
}
?>
