<?php

namespace App\Controllers;

use App\AuditLog;

class AuditLogController extends BaseController
{
    private AuditLog $auditLog;

    public function __construct($pdo, $logger)
    {
        parent::__construct($pdo, $logger);
        $this->auditLog = new AuditLog($pdo);
    }

    public function show(): void
    {
        $this->requireRole(['Admin', 'Superadmin']);
        $filters = $this->filters();
        $this->render('audit_log', ['entries' => $this->auditLog->search($filters), 'filters' => $filters]);
    }

    public function exportCsv(): void
    {
        $this->requirePost(); $this->requireRole(['Admin', 'Superadmin']); $this->requireCsrf();
        $entries = $this->auditLog->search($this->filters(), 500);
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="auditlog.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Zeitpunkt', 'Benutzer', 'Aktion', 'Ergebnis', 'Objekt', 'Objekt-ID', 'IP', 'Gerät', 'Request-ID'], ';');
        foreach ($entries as $entry) fputcsv($out, [$entry['created_at'], $entry['username'] ?? 'kiosk', $entry['action'], $entry['result'], $entry['entity_type'], $entry['entity_id'], $entry['ip_address'], $entry['device_id'], $entry['request_id']], ';');
        fclose($out); $this->audit('audit.export', 'audit_log'); exit();
    }

    private function filters(): array
    {
        $from = $this->date($_GET['from'] ?? $_POST['from'] ?? ''); $to = $this->date($_GET['to'] ?? $_POST['to'] ?? '');
        return ['from' => $from, 'to' => $to, 'action' => mb_substr(trim($_GET['action_filter'] ?? $_POST['action_filter'] ?? ''), 0, 100), 'result' => in_array($_GET['result'] ?? $_POST['result'] ?? '', ['success', 'failed'], true) ? ($_GET['result'] ?? $_POST['result']) : '', 'user_id' => filter_var($_GET['user_id'] ?? $_POST['user_id'] ?? null, FILTER_VALIDATE_INT) ?: null];
    }

    private function date(string $value): ?string
    {
        $date = \DateTime::createFromFormat('!Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value ? $value : null;
    }
}
