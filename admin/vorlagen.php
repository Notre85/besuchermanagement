<?php
// admin/vorlagen.php
require_once '../config/config.php';
require_once 'controllers/VorlagenController.php';

$controller = new VorlagenController();

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'edit':
        $controller->edit($_GET['id']);
        break;
    case 'update':
        $controller->update($_GET['id']);
        break;
    default:
        $controller->index();
        break;
}
?>
