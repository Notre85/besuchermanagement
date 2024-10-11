<?php
// index.php
require_once 'config/config.php';
require_once 'controllers/CheckInController.php';

$controller = new CheckInController();
$controller->index();
?>
