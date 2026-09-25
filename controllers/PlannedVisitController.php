<?php

namespace App\Controllers;

use App\Host;
use App\Location;
use App\Notification;
use App\PlannedVisit;
use App\Visitor;
use DateTime;
use DateTimeZone;

class PlannedVisitController extends BaseController
{
    private $plannedVisitModel;
    private $visitorModel;
    private $hostModel;
    private $locationModel;
    private $notificationModel;

    public function __construct($pdo, $logger)
    {
        parent::__construct($pdo, $logger);
        $this->plannedVisitModel = new PlannedVisit($pdo);
        $this->visitorModel = new Visitor($pdo);
        $this->hostModel = new Host($pdo);
        $this->locationModel = new Location($pdo);
        $this->notificationModel = new Notification($pdo);
    }

    public function show()
    {
        $this->requireRole(['Manager', 'Admin', 'Superadmin']);
        $this->plannedVisitModel->expire();
        $this->render('planned_visits', [
            'visitors' => $this->visitorModel->getAllVisitors(),
            'hosts' => $this->hostModel->getAllActive(),
            'locations' => $this->locationModel->getAllActive(),
            'plannedVisits' => $this->plannedVisitModel->findUpcoming(),
            'notificationFailures' => $this->notificationModel->getRecentFailures(),
            'accessCode' => $_SESSION['planned_visit_access_code'] ?? null,
        ]);
        unset($_SESSION['planned_visit_access_code']);
    }

    public function create()
    {
        $this->requirePost();
        $this->requireRole(['Manager', 'Admin', 'Superadmin']);
        $this->requireCsrf();

        $visitorId = filter_input(INPUT_POST, 'visitor_id', FILTER_VALIDATE_INT);
        $hostId = filter_input(INPUT_POST, 'host_id', FILTER_VALIDATE_INT) ?: null;
        $locationId = filter_input(INPUT_POST, 'location_id', FILTER_VALIDATE_INT) ?: null;
        $reason = trim($_POST['visit_reason'] ?? '');
        $startsAt = trim($_POST['starts_at'] ?? '');
        $endsAt = trim($_POST['ends_at'] ?? '');
        $timezone = new DateTimeZone('Europe/Berlin');
        $start = DateTime::createFromFormat('Y-m-d\TH:i', $startsAt, $timezone);
        $startErrors = DateTime::getLastErrors();
        $end = DateTime::createFromFormat('Y-m-d\TH:i', $endsAt, $timezone);
        $endErrors = DateTime::getLastErrors();

        if (!$visitorId || $reason === '' || mb_strlen($reason) > 500 || !$start || !$end || ($startErrors !== false && ($startErrors['warning_count'] || $startErrors['error_count'])) || ($endErrors !== false && ($endErrors['warning_count'] || $endErrors['error_count'])) || $start >= $end || $end->getTimestamp() < time()) {
            $this->redirect('planned_visits.php?error=invalid_input');
        }

        $code = strtoupper(bin2hex(random_bytes(8)));
        $plannedVisitId = $this->plannedVisitModel->create([
            'visitor_id' => $visitorId,
            'host_id' => $hostId,
            'location_id' => $locationId,
            'visit_reason' => $reason,
            'starts_at' => $start->format('Y-m-d H:i:s'),
            'ends_at' => $end->format('Y-m-d H:i:s'),
            'access_code_hash' => hash('sha256', $code),
            'access_code_expires_at' => $end->format('Y-m-d H:i:s'),
            'created_by' => $_SESSION['user']['id'],
        ]);
        $this->audit('planned_visit.create', 'planned_visit', $plannedVisitId);

        if ($hostId) {
            $host = $this->hostModel->findById($hostId);
            if ($host) {
                $notificationId = $this->notificationModel->create($hostId, $plannedVisitId, 'planned_visit.created');
                $from = $_ENV['MAIL_FROM'] ?? '';
                if ($from !== '' && filter_var($host['email'], FILTER_VALIDATE_EMAIL)) {
                    $subject = 'Neuer geplanter Besuch';
                    $message = 'Für Sie wurde ein Besuch am ' . $start->format('d.m.Y H:i') . ' Uhr angelegt.';
                    $headers = 'From: ' . str_replace(["\r", "\n"], '', $from);
                    if (mail($host['email'], $subject, $message, $headers)) {
                        $this->notificationModel->markSent($notificationId);
                    } else {
                        $this->notificationModel->markFailed($notificationId);
                    }
                } else {
                    $this->notificationModel->markFailed($notificationId);
                }
            }
        }
        $_SESSION['planned_visit_access_code'] = $code;
        $this->redirect('planned_visits.php?success=created');
    }
}
