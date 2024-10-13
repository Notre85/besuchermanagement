<?php
// controllers/ReportController.php

namespace App\Controllers;

use App\Visit;
use FPDF;

class ReportController extends BaseController {
    protected $visitModel;

    public function __construct($pdo, $logger) {
        parent::__construct($pdo, $logger);
        $this->visitModel = new Visit($pdo);
    }

    public function showReportForm() {
        $this->render('report_form');
    }

    public function generateReport() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'])) {
                $this->logger->error('CSRF-Token-Validierung fehlgeschlagen bei der Berichtserstellung.');
                die('Ungültiges CSRF-Token.');
            }

            $timeframe = $_POST['timeframe'] ?? 'month';
            $start_date = '';
            $end_date = '';

            switch ($timeframe) {
                case 'today':
                    $start_date = date('Y-m-d 00:00:00');
                    $end_date = date('Y-m-d 23:59:59');
                    break;
                case 'week':
                    $start_date = date('Y-m-d 00:00:00', strtotime('monday this week'));
                    $end_date = date('Y-m-d 23:59:59', strtotime('sunday this week'));
                    break;
                case 'month':
                    $start_date = date('Y-m-01 00:00:00');
                    $end_date = date('Y-m-t 23:59:59');
                    break;
                case 'year':
                    $start_date = date('Y-01-01 00:00:00');
                    $end_date = date('Y-12-31 23:59:59');
                    break;
                default:
                    $start_date = date('Y-m-01 00:00:00');
                    $end_date = date('Y-m-t 23:59:59');
            }

            $visits = $this->visitModel->getVisitsByDateRange($start_date, $end_date);

            // PDF-Generierung
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, 'Besuchsbericht', 0, 1, 'C');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, "Zeitraum: " . date('d.m.Y', strtotime($start_date)) . " bis " . date('d.m.Y', strtotime($end_date)), 0, 1, 'C');
            $pdf->Ln(10);

            // Tabellenkopf
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(40, 10, 'Name', 1);
            $pdf->Cell(40, 10, 'Firma', 1);
            $pdf->Cell(60, 10, 'Besuchsgrund', 1);
            $pdf->Cell(30, 10, 'Check-In', 1);
            $pdf->Cell(30, 10, 'Check-Out', 1);
            $pdf->Ln();

            // Tabelleninhalt
            $pdf->SetFont('Arial', '', 12);
            foreach ($visits as $visit) {
                $name = $visit['first_name'] . ' ' . $visit['last_name'];
                $company = $visit['company'] ?? 'N/A';
                $reason = $visit['visit_reason'];
                $checkin = date('d.m.Y H:i', strtotime($visit['checkin_time']));
                $checkout = $visit['checkout_time'] ? date('d.m.Y H:i', strtotime($visit['checkout_time'])) : 'Noch nicht ausgecheckt';

                $pdf->Cell(40, 10, $name, 1);
                $pdf->Cell(40, 10, $company, 1);
                $pdf->Cell(60, 10, substr($reason, 0, 30) . (strlen($reason) > 30 ? '...' : ''), 1);
                $pdf->Cell(30, 10, $checkin, 1);
                $pdf->Cell(30, 10, $checkout, 1);
                $pdf->Ln();
            }

            $pdf->Output('D', 'Besuchsbericht.pdf');
            exit();
        } else {
            $this->redirect('report.php');
        }
    }
}
