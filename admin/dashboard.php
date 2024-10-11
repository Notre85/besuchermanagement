<?php
// admin/dashboard.php
require_once '../config/config.php';
require_once 'controllers/DashboardController.php';

$controller = new DashboardController();
$controller->index();
?>