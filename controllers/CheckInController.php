<?php
// controllers/CheckInController.php

namespace App\Controllers;

use App\Visitor;
use App\Visit;
use App\PlannedVisit;
use App\Notification;
use App\KeyInventory;
use App\Services\PrintData;
use App\Services\PrintJobService;

class CheckInController extends BaseController {
    protected $visitorModel;
    protected $visitModel;
    protected $plannedVisitModel;
    protected $notificationModel;
    protected $keyModel;
    protected $printJobs;

    public function __construct($pdo, $logger) {
        parent::__construct($pdo, $logger);
        $this->visitorModel = new Visitor($pdo);
        $this->visitModel = new Visit($pdo);
        $this->plannedVisitModel = new PlannedVisit($pdo);
        $this->notificationModel = new Notification($pdo);
        $this->keyModel = new KeyInventory($pdo);
        $this->printJobs = new PrintJobService($pdo);
    }

    public function showCheckInForm() {
        $this->requireRole(['Berichtersteller', 'Manager', 'Admin', 'Superadmin']);
        // Im anonymen Kiosk keine vollständige Anwesenheitsliste ausgeben.
        $currentVisits = $this->visitModel->getCurrentVisits();
        $this->render('checkin_form', ['currentVisits' => $currentVisits, 'availableKeys' => $this->keyModel->getAvailable()]);
    }

    public function showKiosk() {
        $this->render('kiosk', ['currentVisits' => $this->visitModel->getCurrentVisits(), 'availableKeys' => $this->keyModel->getAvailable()]);
    }

    public function handleIssueKey(): void
    {
        $this->requirePost();
        $this->requireCsrf();
        $this->requireRole(['Berichtersteller', 'Manager', 'Admin', 'Superadmin']);
        $visitId = filter_var($_POST['visit_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $keyId = filter_var($_POST['key_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$visitId || !$keyId) {
            $this->redirect('index.php?error=key_issue_invalid');
        }
        $visit = $this->visitModel->findById($visitId);
        $visitData = $this->visitModel->findBadgeData($visitId);
        if (!$visit || $visit['checkout_time'] !== null || !$visitData) {
            $this->redirect('index.php?error=key_issue_invalid');
        }
        if ($this->keyModel->findAssignmentForVisit($visitId)) {
            $this->redirect('index.php?error=key_already_issued');
        }
        $key = $this->keyModel->assignSpecific(
            $keyId,
            $visitId,
            $visitData['location_id'] ? (int) $visitData['location_id'] : null,
            (int) ($_SESSION['user']['id'] ?? 0)
        );
        if (!$key) {
            $this->redirect('index.php?error=key_unavailable');
        }
        $this->audit('key.issue', 'key_assignment', (int) $key['assignment_id']);
        $data = PrintData::forVisit($visitData, $key);
        $device = !empty($_POST['kiosk']) ? ($_ENV['KIOSK_DEVICE_ID'] ?? 'kiosk') : null;
        foreach (['key_receipt', 'key_label'] as $outputType) {
            $jobId = $this->printJobs->queue($outputType, 'visit', $visitId, $data, $visitData['location_id'] ? (int) $visitData['location_id'] : null, (int) ($_SESSION['user']['id'] ?? 0), $device);
            if ($jobId) {
                $this->audit('print.queue', 'print_job', $jobId);
            }
        }
        $this->redirect('index.php?success=key_issued');
    }

    public function handleCheckIn() {
        $this->requirePost();
        $this->requireCsrf();
        $this->requireCheckInAccess();
        $visitorIdInput = trim((string) ($_POST['visitor_id'] ?? ''));
        $visitor_id = $visitorIdInput === '' ? null : filter_var($visitorIdInput, FILTER_VALIDATE_INT);
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $visit_reason = trim($_POST['visit_reason'] ?? '');
        $access_code = strtoupper(trim($_POST['access_code'] ?? ''));

        if ($access_code !== '' && $visitor_id !== null) {
            $this->redirect('index.php?error=choose_one');
        }

        if ($access_code !== '') {
            $plannedVisit = $this->plannedVisitModel->findByAccessCode($access_code);
            if (!$plannedVisit) {
                $this->redirect('index.php?error=invalid_access_code');
            }
            if ($this->visitModel->isVisitorCheckedIn((int) $plannedVisit['visitor_id'])) {
                $this->redirect('index.php?error=already_checked_in');
            }
            $visitId = $this->visitModel->create((int) $plannedVisit['visitor_id'], $plannedVisit['visit_reason'], (int) $plannedVisit['id']);
            if (!$visitId) {
                $this->redirect('index.php?error=already_checked_in');
            }
            $this->plannedVisitModel->markCheckedIn((int) $plannedVisit['id']);
            $this->notifyHost($plannedVisit);
            $this->finalizeCheckIn($visitId, $plannedVisit['location_id'] ? (int) $plannedVisit['location_id'] : null);
            $this->audit('visit.checkin', 'visit', $visitId);
            $this->redirect('index.php?success=checkin');
        }

            // Sicherheitsmaßnahme: Überprüfen, ob der Besucher bereits eingecheckt ist
            if (($visitor_id !== null && $visitor_id < 1) || mb_strlen($first_name) > 50 || mb_strlen($last_name) > 50 || mb_strlen($company) > 100 || mb_strlen($visit_reason) > 500) {
                $this->redirect('index.php?error=invalid_input');
            }

            if ($visitor_id) {
                // Check-In via VisitorID
                if (empty($visit_reason)) {
                    $this->logger->warning('Besuchsgrund fehlt beim Check-In via VisitorID.');
                    $this->redirect('index.php?error=visit_reason_required');
                }

                $visitor = $this->visitorModel->findById($visitor_id);
                if (!$visitor) {
                    $this->logger->warning("Besucher mit ID $visitor_id nicht gefunden.");
                    $this->redirect('index.php?error=visitor_not_found');
                }

                // Überprüfen, ob der Besucher bereits eingecheckt ist
                if ($this->visitModel->isVisitorCheckedIn($visitor_id)) {
                    $this->logger->warning("Besucher ID $visitor_id ist bereits eingecheckt.");
                    $this->redirect('index.php?error=already_checked_in');
                }

                // Check-In durchführen
                $visitId = $this->visitModel->create($visitor['id'], $visit_reason);
                if (!$visitId) {
                    $this->redirect('index.php?error=already_checked_in');
                }
                $this->finalizeCheckIn($visitId, $this->configuredKioskLocation());
                $this->audit('visit.checkin', 'visit', $visitId);
                $this->logger->info("Besucher eingegangen: ID {$visitor['id']} via VisitorID");

            } else {
                // Check-In via Name, Vorname, Firma
                if (empty($first_name) || empty($last_name) || empty($visit_reason)) {
                    $this->logger->warning('Vorname, Nachname oder Besuchsgrund fehlt beim Check-In.');
                    $this->redirect('index.php?error=required_fields_missing');
                }

                // Suchen nach dem Besucher
                $visitor = $this->visitorModel->findByName($first_name, $last_name);
                if (!$visitor) {
                    // Erstellen eines neuen Besuchers
                    $this->visitorModel->create($first_name, $last_name, $company);
                    $visitor = $this->visitorModel->findByName($first_name, $last_name);
                }

                if (!$visitor) {
                    $this->logger->error('Besucher konnte nicht gefunden oder erstellt werden.');
                    $this->redirect('index.php?error=visitor_creation_failed');
                }

                // Überprüfen, ob der Besucher bereits eingecheckt ist
                if ($this->visitModel->isVisitorCheckedIn($visitor['id'])) {
                    $this->logger->warning("Besucher ID {$visitor['id']} ist bereits eingecheckt.");
                    $this->redirect('index.php?error=already_checked_in');
                }

                // Check-In durchführen
                $visitId = $this->visitModel->create($visitor['id'], $visit_reason);
                if (!$visitId) {
                    $this->redirect('index.php?error=already_checked_in');
                }
                $this->finalizeCheckIn($visitId, $this->configuredKioskLocation());
                $this->audit('visit.checkin', 'visit', $visitId);
                $this->logger->info("Besucher eingegangen: ID {$visitor['id']}");
            }

            // Weiterleitung zur Hauptseite mit Erfolgsmeldung
        $this->redirect('index.php?success=checkin');
    }

    public function handleCheckOut() {
        $this->requirePost();
        $this->requireCsrf();
        $this->requireCheckInAccess();
        $visit_id = isset($_POST['visit_id']) ? intval($_POST['visit_id']) : null;
        $visitor_id = isset($_POST['visitor_id']) ? intval($_POST['visitor_id']) : null;

            if ($visit_id) {
                // Check-Out via Visit ID
                $visit = $this->visitModel->findById($visit_id);
                if (!$visit || $visit['checkout_time'] !== null) {
                    $this->logger->warning("Ungültige oder bereits ausgecheckte Visit ID $visit_id.");
                    $this->redirect('index.php?error=invalid_visit_id');
                }

                $result = $this->visitModel->checkout($visit_id);
                if ($result) {
                    $this->keyModel->returnForVisit($visit_id, $_SESSION['user']['id'] ?? null);
                    $this->plannedVisitModel->markCheckedOutByVisitor((int) $visit['visitor_id']);
                    $this->audit('visit.checkout', 'visit', $visit_id);
                    $this->logger->info("Besucher ausgecheckt: Visit ID {$visit_id}");
                    $this->redirect('index.php?success=checkout');
                } else {
                    $this->logger->warning("Fehler beim Auschecken von Visit ID {$visit_id}");
                    $this->redirect('index.php?error=checkout_failed');
                }

            } elseif ($visitor_id) {
                // Check-Out via Visitor ID
                $visitor = $this->visitorModel->findById($visitor_id);
                if (!$visitor) {
                    $this->logger->warning("Besucher mit ID $visitor_id nicht gefunden.");
                    $this->redirect('index.php?error=visitor_not_found');
                }

                // Überprüfen, ob der Besucher eingecheckt ist
                if (!$this->visitModel->isVisitorCheckedIn($visitor_id)) {
                    $this->logger->warning("Besucher ID $visitor_id ist nicht eingecheckt.");
                    $this->redirect('index.php?error=not_checked_in');
                }

                $activeVisits = $this->visitModel->getActiveByVisitorId($visitor_id);
                $result = $this->visitModel->checkoutByVisitorId($visitor_id);
                if ($result) {
                    foreach ($activeVisits as $activeVisit) {
                        $this->keyModel->returnForVisit((int) $activeVisit['id'], $_SESSION['user']['id'] ?? null);
                    }
                    $this->plannedVisitModel->markCheckedOutByVisitor($visitor_id);
                    $this->audit('visit.checkout', 'visitor', $visitor_id);
                    $this->logger->info("Alle Besuche ausgecheckt für Visitor ID {$visitor_id}");
                    $this->redirect('index.php?success=checkout');
                } else {
                    $this->logger->warning("Fehler beim Auschecken für Visitor ID {$visitor_id}");
                    $this->redirect('index.php?error=checkout_failed');
                }

            } else {
                $this->logger->warning('Weder Visit ID noch Visitor ID für Check-Out angegeben.');
                $this->redirect('index.php?error=missing_parameters');
            }
    }

    private function notifyHost(array $plannedVisit) {
        $host = $this->plannedVisitModel->findHostForVisit((int) $plannedVisit['id']);
        $from = $_ENV['MAIL_FROM'] ?? '';
        if (!$host) {
            return;
        }
        $notificationId = $this->notificationModel->create((int) $host['id'], (int) $plannedVisit['id'], 'visit.checked_in');
        if ($from === '' || !filter_var($host['email'], FILTER_VALIDATE_EMAIL)) {
            $this->notificationModel->markFailed($notificationId);
            return;
        }
        $subject = 'Besucher ist eingetroffen';
        $message = 'Der Besuch von ' . $host['first_name'] . ' ' . $host['last_name'] . ' ist eingetroffen.';
        $headers = 'From: ' . str_replace(["\r", "\n"], '', $from);
        if (mail($host['email'], $subject, $message, $headers)) {
            $this->notificationModel->markSent($notificationId);
        } else {
            $this->notificationModel->markFailed($notificationId);
        }
    }

    private function configuredKioskLocation(): ?int
    {
        $value = filter_var($_ENV['KIOSK_LOCATION_ID'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $value === false ? null : $value;
    }

    private function finalizeCheckIn(int $visitId, ?int $locationId): void
    {
        $key = null;
        $visit = $this->visitModel->findBadgeData($visitId);
        if (!$visit) {
            return;
        }
        $data = PrintData::forVisit($visit, $key);
        $device = !empty($_POST['kiosk']) ? ($_ENV['KIOSK_DEVICE_ID'] ?? 'kiosk') : null;
        foreach (['visitor_badge', 'key_receipt', 'key_label'] as $outputType) {
            if ($outputType !== 'visitor_badge' && !$key) {
                continue;
            }
            $jobId = $this->printJobs->queue($outputType, 'visit', $visitId, $data, $locationId, $_SESSION['user']['id'] ?? null, $device);
            if ($jobId) {
                $this->audit('print.queue', 'print_job', $jobId);
            }
        }
    }

    private function requireCheckInAccess(): void
    {
        if (!empty($_POST['kiosk'])) {
            $networks = $_ENV['KIOSK_ALLOWED_NETWORKS'] ?? '127.0.0.0/8,10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,::1/128,fc00::/7';
            if (!client_ip_in_networks($_SERVER['REMOTE_ADDR'] ?? '', $networks)) {
                http_response_code(403);
                exit('Kiosk-Zugriff aus diesem Netz nicht erlaubt.');
            }
            return;
        }
        $this->requireRole(['Berichtersteller', 'Manager', 'Admin', 'Superadmin']);
    }
}
?>
