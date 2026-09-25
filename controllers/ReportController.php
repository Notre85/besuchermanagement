<?php
// controllers/ReportController.php

namespace App\Controllers;

use App\Visit;
use App\Host;
use App\Location;

class ReportController extends BaseController
{
    protected $visitModel;
    protected $hostModel;
    protected $locationModel;

    public function __construct($pdo, $logger)
    {
        parent::__construct($pdo, $logger);
        $this->visitModel = new Visit($pdo);
        $this->hostModel = new Host($pdo);
        $this->locationModel = new Location($pdo);
    }

    // Zeigt das Formular an
    public function showReportForm()
    {
        $this->requireRole(['Berichtersteller', 'Manager', 'Admin', 'Superadmin']);
        $this->render('report_form', ['hosts' => $this->hostModel->getAllActive(), 'locations' => $this->locationModel->getAllActive()]);
    }

    // Generiert den Bericht als HTML
    public function generateReport()
    {
        $this->requirePost();
        $this->requireRole(['Berichtersteller', 'Manager', 'Admin', 'Superadmin']);
        $this->requireCsrf();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reportType = $_POST['report_type'] ?? 'time';
            $filter = trim($_POST['filter'] ?? '');
            $hostId = $this->optionalId($_POST['host_id'] ?? null);
            $locationId = $this->optionalId($_POST['location_id'] ?? null);
            $status = $this->validatedStatus($_POST['visit_status'] ?? null);
            $startValue = $this->validatedDate($_POST['start_date'] ?? '');
            $endValue = $this->validatedDate($_POST['end_date'] ?? '');
            if (!$startValue || !$endValue || $startValue > $endValue || mb_strlen($filter) > 100) {
                $this->redirect('report.php?error=invalid_date');
            }
            $start_date = $startValue . ' 00:00:00';
            $end_date = $endValue . ' 23:59:59';
            $visits = [];

            $visits = $this->visitModel->getVisitsByCriteria($reportType, $filter, $start_date, $end_date, $hostId, $locationId, $status);

            $this->render('report_form', ['visits' => $visits, 'hosts' => $this->hostModel->getAllActive(), 'locations' => $this->locationModel->getAllActive()]);
            $this->audit('report.view', 'visit', null);
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

    public function generateCsv()
    {
        $this->requirePost();
        $this->requireRole(['Berichtersteller', 'Manager', 'Admin', 'Superadmin']);
        $this->requireCsrf();
        $reportType = $_POST['report_type'] ?? 'time';
        $filter = trim($_POST['filter'] ?? '');
        $hostId = $this->optionalId($_POST['host_id'] ?? null);
        $locationId = $this->optionalId($_POST['location_id'] ?? null);
        $status = $this->validatedStatus($_POST['visit_status'] ?? null);
        $startDate = $this->validatedDate($_POST['start_date'] ?? '');
        $endDate = $this->validatedDate($_POST['end_date'] ?? '');
        if (!$startDate || !$endDate || $startDate > $endDate || mb_strlen($filter) > 100) {
            $this->redirect('report.php?error=invalid_date');
        }
        $visits = $this->visitModel->getVisitsByCriteria($reportType, $filter, $startDate . ' 00:00:00', $endDate . ' 23:59:59', $hostId, $locationId, $status);
        if (count($visits) > 5000) {
            $this->redirect('report.php?error=report_too_large');
        }
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="Besuchsbericht.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Besuch Nr.', 'Vorname', 'Nachname', 'Firma', 'Besuchsgrund', 'Check-In', 'Check-Out'], ';');
        foreach ($visits as $visit) {
            fputcsv($out, [$visit['id'], $visit['first_name'], $visit['last_name'], $visit['company'], $visit['visit_reason'], $visit['checkin_time'], $visit['checkout_time']], ';');
        }
        fclose($out);
        $this->audit('report.csv', 'visit', null);
        exit();
    }

    private function validatedDate($value)
    {
        $date = \DateTime::createFromFormat('!Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value ? $value : null;
    }

    private function optionalId($value)
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $id === false ? null : $id;
    }

    private function validatedStatus($value)
    {
        return in_array($value, ['registered', 'checked_in', 'checked_out', 'expired', 'cancelled'], true) ? $value : null;
    }

    private function loadVisits($reportType, $filter, $startDate, $endDate)
    {
        switch ($reportType) {
            case 'visitor':
                return $this->visitModel->getVisitsByVisitor($filter, $startDate, $endDate);
            case 'company':
                return $this->visitModel->getVisitsByCompany($filter, $startDate, $endDate);
            default:
                return $this->visitModel->getVisitsByDateRange($startDate, $endDate);
        }
    }

    // Generiert das PDF und loggt die Aktion
    public function generatePdf()
    {
        $this->requirePost();
        $this->requireRole(['Berichtersteller', 'Manager', 'Admin', 'Superadmin']);
        $this->requireCsrf();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reportType = $_POST['report_type'] ?? 'time';
            $filter = trim($_POST['filter'] ?? '');
            $hostId = $this->optionalId($_POST['host_id'] ?? null);
            $locationId = $this->optionalId($_POST['location_id'] ?? null);
            $status = $this->validatedStatus($_POST['visit_status'] ?? null);
            $startValue = $this->validatedDate($_POST['start_date'] ?? '');
            $endValue = $this->validatedDate($_POST['end_date'] ?? '');
            if (!$startValue || !$endValue || $startValue > $endValue || mb_strlen($filter) > 100) {
                $this->redirect('report.php?error=invalid_date');
            }
            $start_date = $startValue . ' 00:00:00';
            $end_date = $endValue . ' 23:59:59';
            $visits = [];

            $visits = $this->visitModel->getVisitsByCriteria($reportType, $filter, $start_date, $end_date, $hostId, $locationId, $status);

            // Logging der PDF-Erstellung
            $this->logger->info('PDF-Bericht wird erstellt.', [
                'Berichtsart' => $reportType,
                'Startdatum' => $start_date,
                'Enddatum' => $end_date,
                'Anzahl Besuche' => count($visits)
            ]);
            $this->audit('report.pdf', 'visit', null);

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
