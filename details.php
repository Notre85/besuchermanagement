<?php
// details.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/logger.php';
require_once __DIR__ . '/config/csrf.php';

use App\Visitor;
use App\Visit;

session_start();

$logger = get_logger();

if (!isset($_GET['id'])) {
    die('Keine Besucher-ID angegeben.');
}

$visitor_id = intval($_GET['id']);
$visitorModel = new Visitor($pdo);
$visitModel = new Visit($pdo);

$visitor = $visitorModel->findById($visitor_id);
$visits = $visitModel->getVisitsByVisitor($visitor_id);

include __DIR__ . '/views/visitor_details.php';
