<?php
// admin/besucher.php
require_once '../config/config.php';
require_once 'controllers/BesucherController.php';

$controller = new BesucherController();

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'edit':
        $controller->edit($_GET['id']);
        break;
    case 'update':
        $controller->update($_GET['id']);
        break;
    case 'delete':
        $controller->delete($_GET['id']);
        break;
    default:
        $controller->index();
        break;
}
?>
