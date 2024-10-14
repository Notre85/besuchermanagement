<?php
// assets/pdf-vorlagen/report_template.php

// FPDF wird über Composer geladen
require_once __DIR__ . '/../../vendor/autoload.php';

class ReportTemplate extends FPDF
{
    protected $logger;

    // Konstruktor, der den Logger empfängt
    public function __construct($logger)
    {
        parent::__construct();
        $this->logger = $logger; // Logger speichern
    }

    // Kopfzeile
    function Header()
    {
        try {
            // Überprüfen, ob das Logo existiert
            if (file_exists(__DIR__ . '/logo.png')) {
                $this->Image(__DIR__ . '/logo.png', 10, 6, 30); // Logo einbinden
                $this->logger->info('Header wurde erfolgreich in das PDF eingebunden.');
            } else {
                // Loggen, wenn das Logo fehlt
                $this->logger->error('Logo nicht gefunden: ' . __DIR__ . '/logo.png');
            }

            $this->SetFont('Arial', 'B', 14);
            $this->Cell(80); // Verschieben nach rechts
            $this->Cell(30, 10, 'Besucherverwaltung - Bericht', 0, 0, 'C');
            $this->Ln(20);

        } catch (Exception $e) {
            // Loggen, falls ein Fehler auftritt
            $this->logger->error('Fehler beim Einfügen des Headers: ' . $e->getMessage());
        }
    }

    // Fußzeile
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Seite ' . $this->PageNo(), 0, 0, 'C');
        $this->logger->info('Fußzeile für Seite ' . $this->PageNo() . ' wurde erfolgreich hinzugefügt.');
    }

    // Berichtsinhalte
    function ReportContent($visits, $start_date, $end_date)
    {
        try {
            // Zeitraum anzeigen
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 10, 'Zeitraum: ' . date('d.m.Y', strtotime($start_date)) . ' bis ' . date('d.m.Y', strtotime($end_date)), 0, 1, 'C');
            $this->Ln(10);

            // Tabellenkopf erstellen
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(20, 10, 'Besuch Nr.', 1);
            $this->Cell(40, 10, 'Name', 1);
            $this->Cell(40, 10, 'Firma', 1);
            $this->Cell(60, 10, 'Besuchsgrund', 1);
            $this->Cell(30, 10, 'Check-In', 1);
            $this->Cell(30, 10, 'Check-Out', 1);
            $this->Ln();
            $this->logger->info('Tabellenkopf für den Bericht wurde erfolgreich hinzugefügt.');

            // Tabelleninhalt erstellen
            $this->SetFont('Arial', '', 12);
            foreach ($visits as $visit) {
                $this->Cell(20, 10, $visit['id'], 1);
                $this->Cell(40, 10, $visit['first_name'] . ' ' . $visit['last_name'], 1);
                $this->Cell(40, 10, $visit['company'] ?? 'N/A', 1);
                $this->Cell(60, 10, substr($visit['visit_reason'], 0, 30), 1);
                $this->Cell(30, 10, date('d.m.Y H:i', strtotime($visit['checkin_time'])), 1);
                $this->Cell(30, 10, $visit['checkout_time'] ? date('d.m.Y H:i', strtotime($visit['checkout_time'])) : 'Noch nicht ausgecheckt', 1);
                $this->Ln();
            }

            // Loggen, wenn alle Inhalte erfolgreich hinzugefügt wurden
            $this->logger->info('Berichtsinhalte erfolgreich ausgegeben.');

        } catch (Exception $e) {
            // Loggen, wenn ein Fehler beim Erstellen des Berichts auftritt
            $this->logger->error('Fehler beim Generieren des Berichts: ' . $e->getMessage());
        }
    }
}
