<?php
// visitor_management.php

// Fehlerberichterstattung aktivieren (nur für Entwicklungszwecke)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php'; // Datenbankverbindung
require_once __DIR__ . '/config/logger.php'; // Logger

// Namespace sicherstellen und den Controller laden
use App\Controllers\VisitorController;

$logger = get_logger(); // Logger instanziieren
$controller = new VisitorController($pdo, $logger); // VisitorController instanziieren

$action = $_GET['action'] ?? null; // Aktionsparameter abfragen

// POST-Anfrage verarbeiten oder Verwaltung anzeigen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update') {
        $controller->updateVisitor(); // Besucher aktualisieren
    } elseif ($action === 'delete') {
        $controller->deleteVisitor(); // Besucher löschen
    }
} else {
    $controller->showVisitorManagement(); // Besucherverwaltung anzeigen
}
