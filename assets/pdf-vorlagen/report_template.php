<?php
// assets/pdf-vorlagen/report_template.php

require_once __DIR__ . '/../../vendor/autoload.php'; // Autoload für TCPDF
use TCPDF;

class ReportTemplate extends TCPDF
{
    protected $logger;

    public function __construct($logger)
    {
        parent::__construct('L', 'mm', 'A4'); // Querformat ('L') für A4-Seite
        $this->logger = $logger; // Logger speichern
    }

    // Kopfzeile
    function Header()
    {
        try {
            if (file_exists(__DIR__ . '/logo.png')) {
                $this->Image(__DIR__ . '/logo.png', 10, 6, 30); // Logo einbinden
                $this->logger->info('Header wurde erfolgreich in das PDF eingebunden.');
            } else {
                $this->logger->error('Logo nicht gefunden: ' . __DIR__ . '/logo.png');
            }

            $this->SetFont('dejavusans', 'B', 14);
            $this->Cell(80);
            $this->Cell(30, 10, 'Besucherverwaltung - Bericht', 0, 0, 'C');
            $this->Ln(20);

        } catch (Exception $e) {
            $this->logger->error('Fehler beim Einfügen des Headers: ' . $e->getMessage());
        }
    }

    // Fußzeile
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('dejavusans', 'I', 8);
        $this->Cell(0, 10, 'Seite ' . $this->PageNo(), 0, 0, 'C');
        $this->logger->info('Fußzeile für Seite ' . $this->PageNo() . ' wurde erfolgreich hinzugefügt.');
    }

    // Dynamische Berechnung der Höhe der Zelle
    function calculateHeightForText($width, $text)
    {
        $this->SetFont('dejavusans', '', 12);
        $nbLines = $this->getNumLines($text, $width); // Anzahl der benötigten Zeilen
        $lineHeight = 6; // Höhe einer Zeile
        return $nbLines * $lineHeight;
    }

    // Berichtsinhalte
    function ReportContent($visits, $start_date, $end_date)
    {
        try {
            $this->SetFont('dejavusans', '', 12);
            $this->Cell(0, 10, 'Zeitraum: ' . date('d.m.Y', strtotime($start_date)) . ' bis ' . date('d.m.Y', strtotime($end_date)), 0, 1, 'C');
            $this->Ln(10);

            // Tabellenkopf erstellen
            $this->SetFont('dejavusans', 'B', 12);
            $this->Cell(15, 10, 'Nr.', 1);
            $this->Cell(50, 10, 'Name', 1);
            $this->Cell(50, 10, 'Firma', 1);
            $this->Cell(80, 10, 'Besuchsgrund', 1);
            $this->Cell(40, 10, 'Check-In', 1);
            $this->Cell(40, 10, 'Check-Out', 1);
            $this->Ln();
            $this->logger->info('Tabellenkopf für den Bericht wurde erfolgreich hinzugefügt.');

            // Tabelleninhalt und dynamische Höhe berechnen
            $this->SetFont('dejavusans', '', 12);
            usort($visits, function($a, $b) {
                return $a['id'] <=> $b['id'];
            });

            foreach ($visits as $visit) {
                $visit_reason = $visit['visit_reason'] ?? '';

                // Dynamische Höhe basierend auf dem Textinhalt berechnen
                $cellHeight = max(
                    $this->calculateHeightForText(50, $visit['first_name'] . ' ' . $visit['last_name']),
                    $this->calculateHeightForText(50, $visit['company'] ?? 'N/A'),
                    $this->calculateHeightForText(80, $visit_reason)
                );

                // Besuch Nr.
                $this->Cell(15, $cellHeight, $visit['id'], 1);

                // Name
                $x = $this->GetX(); 
                $y = $this->GetY();
                $this->MultiCell(50, $cellHeight, $visit['first_name'] . ' ' . $visit['last_name'], 1, 'L', 0);
                $this->SetXY($x + 50, $y);

                // Firma
                $x = $this->GetX();
                $this->MultiCell(50, $cellHeight, $visit['company'] ?? 'N/A', 1, 'L', 0);
                $this->SetXY($x + 50, $y);

                // Besuchsgrund
                $x = $this->GetX();
                $this->MultiCell(80, $cellHeight, $visit_reason, 1, 'L', 0);
                $this->SetXY($x + 80, $y);

                // Check-In und Check-Out
                $this->Cell(40, $cellHeight, date('d.m.Y H:i', strtotime($visit['checkin_time'])), 1);
                $this->Cell(40, $cellHeight, $visit['checkout_time'] ? date('d.m.Y H:i', strtotime($visit['checkout_time'])) : 'noch anwesend', 1);
                $this->Ln();
            }

            $this->logger->info('Berichtsinhalte erfolgreich ausgegeben.');

        } catch (Exception $e) {
            $this->logger->error('Fehler beim Generieren des Berichts: ' . $e->getMessage());
        }
    }
}
