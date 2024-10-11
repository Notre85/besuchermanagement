<?php
// admin/logout.php
require_once '../config/config.php';
require_once 'controllers/LoginController.php';

$controller = new LoginController();
$controller->logout();
?>
