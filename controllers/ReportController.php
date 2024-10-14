<?php
// controllers/ReportController.php

namespace App\Controllers;

use App\Visit;
use FPDF;

class ReportController extends BaseController
{
    protected $visitModel;

    public function __construct($pdo, $logger)
    {
        parent::__construct($pdo, $logger);
        $this->visitModel = new Visit($pdo);
    }

    // Zeigt das Formular an
    public function showReportForm()
    {
        $this->render('report_form');
    }

    // Generiert den Bericht als HTML
    public function generateReport()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'])) {
                die('Ungültiges CSRF-Token.');
            }

            $reportType = $_POST['report_type'] ?? 'time';
            $filter = $_POST['filter'] ?? null;
            $start_date = $_POST['start_date'] . ' 00:00:00';
            $end_date = $_POST['end_date'] . ' 23:59:59';
            $visits = [];

            switch ($reportType) {
                case 'visitor':
                    $visits = $this->visitModel->getVisitsByVisitor($filter, $start_date, $end_date);
                    break;
                case 'company':
                    $visits = $this->visitModel->getVisitsByCompany($filter, $start_date, $end_date);
                    break;
                case 'time':
                default:
                    $visits = $this->visitModel->getVisitsByDateRange($start_date, $end_date);
                    break;
            }

            $this->render('report_form', ['visits' => $visits]);
        } else {
            $this->redirect('report.php');
        }

        // Logging der HTML-Berichtserstellung
        $this->logger->info('HTML-Bericht im Backend angezeigt.', [
            'Berichtsart' => $reportType,
            'Startdatum' => $start_date,
            'Enddatum' => $end_date,
            'Anzahl Besuche' => count($visits)
        ]);
    }

    // Generiert das PDF und loggt die Aktion
    public function generatePdf()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'])) {
                die('Ungültiges CSRF-Token.');
            }

            $reportType = $_POST['report_type'] ?? 'time';
            $filter = $_POST['filter'] ?? null;
            $start_date = $_POST['start_date'] . ' 00:00:00';
            $end_date = $_POST['end_date'] . ' 23:59:59';
            $visits = [];

            switch ($reportType) {
                case 'visitor':
                    $visits = $this->visitModel->getVisitsByVisitor($filter, $start_date, $end_date);
                    break;
                case 'company':
                    $visits = $this->visitModel->getVisitsByCompany($filter, $start_date, $end_date);
                    break;
                case 'time':
                default:
                    $visits = $this->visitModel->getVisitsByDateRange($start_date, $end_date);
                    break;
            }

            // Logging der PDF-Erstellung
            $this->logger->info('PDF-Bericht wird erstellt.', [
                'Berichtsart' => $reportType,
                'Startdatum' => $start_date,
                'Enddatum' => $end_date,
                'Anzahl Besuche' => count($visits)
            ]);

            // PDF mit Template generieren
            usort($visits, function($a, $b) {
              return $a['id'] <=> $b['id'];
            });
            require_once __DIR__ . '/../assets/pdf-vorlagen/report_template.php';
            $pdf = new \ReportTemplate($this->logger);
            $pdf->AddPage();
            $pdf->ReportContent($visits, $start_date, $end_date);
            $pdf->Output('Besuchsbericht.pdf', 'I'); // PDF als Download (D) mit korrektem Dateinamen
            exit();
        } else {
            $this->redirect('report.php');
        }
    }
}
