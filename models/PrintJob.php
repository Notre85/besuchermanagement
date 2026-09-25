<?php

namespace App;

class PrintJob extends BaseModel
{
    public function claimNext(string $deviceId): ?array
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "SELECT print_jobs.*, printers.printer_uri, print_template_versions.format, print_template_versions.source,
                        print_template_versions.variables_json, print_template_versions.page_width,
                        print_template_versions.page_height
                 FROM print_jobs
                 JOIN printers ON printers.id = print_jobs.printer_id
                 JOIN print_template_versions ON print_template_versions.id = print_jobs.template_version_id
                 WHERE print_jobs.status = 'queued' AND (print_jobs.device_id = :device_id OR print_jobs.device_id IS NULL)
                 ORDER BY print_jobs.created_at LIMIT 1 FOR UPDATE"
            );
            $stmt->execute(['device_id' => $deviceId]);
            $job = $stmt->fetch();
            if (!$job) { $this->pdo->rollBack(); return null; }
            $update = $this->pdo->prepare("UPDATE print_jobs SET status = 'sent', attempts = attempts + 1, sent_at = NOW(), device_id = :device_id WHERE id = :id AND status = 'queued'");
            $update->execute(['device_id' => $deviceId, 'id' => $job['id']]);
            if ($update->rowCount() !== 1) throw new \RuntimeException('Druckjob konnte nicht reserviert werden.');
            $this->pdo->commit();
            return $job;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $id, string $status, ?string $error = null): bool
    {
        if (!in_array($status, ['printed', 'failed', 'cancelled'], true)) return false;
        $stmt = $this->pdo->prepare('UPDATE print_jobs SET status = :status, error_message = :error, completed_at = NOW() WHERE id = :id AND status = \'sent\'');
        $stmt->execute(['id' => $id, 'status' => $status, 'error' => $error]);
        return $stmt->rowCount() === 1;
    }

    public function retry(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE print_jobs SET status = 'queued', error_message = NULL, completed_at = NULL WHERE id = :id AND status = 'failed'");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO print_jobs (printer_id, template_version_id, profile_id, output_type, entity_type, entity_id, payload_json, rendered_format, requested_by, device_id)
             VALUES (:printer_id, :template_version_id, :profile_id, :output_type, :entity_type, :entity_id, :payload_json, :rendered_format, :requested_by, :device_id)'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function getRecent(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        return $this->pdo->query(
            "SELECT print_jobs.*, printers.name AS printer_name
             FROM print_jobs JOIN printers ON printers.id = print_jobs.printer_id
             ORDER BY print_jobs.created_at DESC LIMIT {$limit}"
        )->fetchAll();
    }
}
