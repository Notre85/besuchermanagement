<?php
// admin/controllers/BackupController.php

require_once 'BaseController.php';

class BackupController extends BaseController {
    public function index() {
        $title = "Backup-Verwaltung";
        $content = '../../views/admin/backup/index.php';
        include '../../template/layout.php';
    }

    // Implementieren Sie die Methoden create, restore
}
?>
