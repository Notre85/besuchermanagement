<?php

namespace App\Controllers;

use App\PrintJob;
use App\AuditLog;
use App\Services\HtmlTemplateRenderer;
use App\Services\JasperTemplateRenderer;
use App\Services\PdfTemplateRenderer;

class PrintAgentController
{
    private $pdo;
    private PrintJob $jobs;
    private AuditLog $audit;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->jobs = new PrintJob($pdo);
        $this->audit = new AuditLog($pdo);
    }

    public function handle(): void
    {
        $configured = (string) ($_ENV['PRINT_AGENT_TOKEN'] ?? '');
        $provided = (string) ($_SERVER['HTTP_X_PRINT_AGENT_TOKEN'] ?? '');
        if ($configured === '' || $provided === '' || !hash_equals($configured, $provided)) {
            http_response_code(401); exit('Nicht autorisierter Druckagent.');
        }
        $action = $_GET['action'] ?? '';
        if ($action === 'next' && $_SERVER['REQUEST_METHOD'] === 'GET') { $this->next(); return; }
        if ($action === 'status' && $_SERVER['REQUEST_METHOD'] === 'POST') { $this->status(); return; }
        http_response_code(405); header('Allow: GET, POST'); exit('Methode nicht erlaubt.');
    }

    private function next(): void
    {
        $deviceId = trim($_GET['device_id'] ?? '');
        if ($deviceId === '' || mb_strlen($deviceId) > 100) { http_response_code(400); exit('Ungültige Gerätekennung.'); }
        $job = $this->jobs->claimNext($deviceId);
        if ($job) $this->audit->record('print.agent.claim', 'print_job', (int) $job['id'], 'success', $deviceId);
        header('Content-Type: application/json; charset=UTF-8');
        if (!$job) { echo json_encode(['job' => null]); return; }
        $data = json_decode($job['payload_json'], true, 512, JSON_THROW_ON_ERROR);
        switch ($job['format']) {
            case 'html': $renderer = new HtmlTemplateRenderer(); break;
            case 'pdf': $renderer = new PdfTemplateRenderer(); break;
            case 'jasper': $renderer = new JasperTemplateRenderer(); break;
            default: throw new \RuntimeException('Unbekanntes Vorlagenformat.');
        }
        try {
            $rendered = $renderer->render($job, $data);
        } catch (\Throwable $e) {
            $this->jobs->updateStatus((int) $job['id'], 'failed', 'Vorlage konnte nicht gerendert werden.');
            http_response_code(500); exit('Druckjob konnte nicht gerendert werden.');
        }
        echo json_encode(['job' => ['id' => (int) $job['id'], 'printer_uri' => $job['printer_uri'] ?? null, 'format' => $rendered['format'], 'mime' => $rendered['mime'], 'content_base64' => base64_encode($rendered['content'])]], JSON_THROW_ON_ERROR);
    }

    private function status(): void
    {
        $body = json_decode(file_get_contents('php://input'), true);
        $id = filter_var($body['job_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $status = $body['status'] ?? '';
        $error = isset($body['error']) ? trim((string) $body['error']) : null;
        if (!$id || !in_array($status, ['printed', 'failed', 'cancelled'], true) || ($error !== null && mb_strlen($error) > 500)) { http_response_code(400); exit('Ungültiger Druckstatus.'); }
        if (!$this->jobs->updateStatus($id, $status, $error)) { http_response_code(409); exit('Druckjobstatus konnte nicht aktualisiert werden.'); }
        $this->audit->record('print.agent.status', 'print_job', $id, $status === 'failed' ? 'failed' : 'success', $_GET['device_id'] ?? null, $error ? ['error' => $error] : []);
        echo json_encode(['ok' => true]);
    }
}
