<?php
// admin/drucker.php
require_once '../config/config.php';
require_once 'controllers/DruckerController.php';

$controller = new DruckerController();

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'configure':
        $controller->configure();
        break;
    case 'save':
        $controller->save();
        break;
    default:
        $controller->index();
        break;
}
?>
