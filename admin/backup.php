<?php
// admin/backup.php
require_once '../config/config.php';
require_once 'controllers/BackupController.php';

$controller = new BackupController();

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'restore':
        $controller->restore();
        break;
    default:
        $controller->index();
        break;
}
?>
