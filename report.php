<?php
// report.php

// Fehlerberichterstattung aktivieren (nur für Entwicklungszwecke)
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php'; // Datenbankverbindung
require_once __DIR__ . '/config/logger.php'; // Logger

// Namespace sicherstellen und den Controller laden
use App\Controllers\ReportController;

$logger = get_logger(); // Logger instanziieren
$controller = new ReportController($pdo, $logger); // ReportController instanziieren

// POST-Anfrage verarbeiten oder Formular anzeigen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_pdf'])) {
        $controller->generatePdf(); // PDF generieren
    } else {
        $controller->generateReport(); // Bericht generieren
    }
} else {
    $controller->showReportForm(); // Formular anzeigen
}
