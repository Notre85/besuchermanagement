<?php

namespace App\Controllers;

use App\Visit;
use App\Services\PrintData;
use App\Services\PrintJobService;
use TCPDF;

class BadgeController extends BaseController
{
    private Visit $visitModel;
    private PrintJobService $printJobs;

    public function __construct($pdo, $logger)
    {
        parent::__construct($pdo, $logger);
        $this->visitModel = new Visit($pdo);
        $this->printJobs = new PrintJobService($pdo);
    }

    public function queue(): void
    {
        $this->requirePost(); $this->requireRole(['Berichtersteller', 'Manager', 'Admin', 'Superadmin']); $this->requireCsrf();
        $visitId = filter_var($_POST['visit_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $visit = $visitId ? $this->visitModel->findBadgeData($visitId) : null;
        if (!$visit || $visit['checkout_time'] !== null || in_array($visit['planned_status'], ['expired', 'cancelled'], true)) {
            $this->redirect('dashboard.php?error=badge_unavailable');
        }
        $data = PrintData::forVisit($visit);
        $jobId = $this->printJobs->queue('visitor_badge', 'visit', $visitId, $data, $visit['location_id'] ? (int) $visit['location_id'] : null, $_SESSION['user']['id'] ?? null);
        if (!$jobId) $this->redirect('dashboard.php?error=no_badge_profile');
        $this->audit('badge.queue', 'print_job', $jobId);
        $this->redirect('dashboard.php?success=badge_queued');
    }

    public function generate(): void
    {
        $this->requireRole(['Berichtersteller', 'Manager', 'Admin', 'Superadmin']);
        $visitId = filter_var($_GET['visit_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$visitId) {
            http_response_code(400);
            exit('Ungültige Besuchsnummer.');
        }
        $visit = $this->visitModel->findBadgeData($visitId);
        if (!$visit || $visit['checkout_time'] !== null || $visit['planned_status'] === 'expired' || $visit['planned_status'] === 'cancelled') {
            http_response_code(404);
            exit('Für diesen Besuch ist kein aktiver Ausweis verfügbar.');
        }

        $pdf = new TCPDF('P', 'mm', [85, 54], true, 'UTF-8', false);
        $pdf->SetCreator('Besuchermanagement');
        $pdf->SetTitle('Besucherausweis');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(5, 5, 5);
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', 'B', 13);
        $pdf->Cell(0, 8, 'Besucherausweis', 0, 1, 'C');
        $pdf->SetFont('dejavusans', 'B', 11);
        $pdf->Cell(0, 7, htmlspecialchars($visit['first_name'] . ' ' . $visit['last_name'], ENT_QUOTES, 'UTF-8'), 0, 1, 'C');
        $pdf->SetFont('dejavusans', '', 8);
        if (!empty($visit['company'])) {
            $pdf->Cell(0, 5, htmlspecialchars($visit['company'], ENT_QUOTES, 'UTF-8'), 0, 1, 'C');
        }
        $host = trim(($visit['host_first_name'] ?? '') . ' ' . ($visit['host_last_name'] ?? ''));
        $pdf->Cell(0, 5, 'Gastgeber: ' . htmlspecialchars($host !== '' ? $host : '–', ENT_QUOTES, 'UTF-8'), 0, 1);
        $validFrom = $visit['starts_at'] ?: $visit['checkin_time'];
        $validTo = $visit['ends_at'] ?: date('Y-m-d H:i:s', strtotime($visit['checkin_time'] . ' +1 day'));
        $pdf->Cell(0, 5, 'Gültig: ' . date('d.m.Y H:i', strtotime($validFrom)) . ' – ' . date('d.m.Y H:i', strtotime($validTo)), 0, 1);
        if (!empty($visit['location_name'])) {
            $pdf->Cell(0, 5, 'Standort: ' . htmlspecialchars($visit['location_name'], ENT_QUOTES, 'UTF-8'), 0, 1);
        }
        $pdf->write2DBarcode('visit:' . (int) $visit['id'], 'QRCODE,M', 28, 35, 28, 28, [], 'N');
        $this->audit('badge.generate', 'visit', $visitId);
        $pdf->Output('Besucherausweis-' . $visitId . '.pdf', 'I');
        exit();
    }
}
