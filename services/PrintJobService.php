<?php

namespace App\Services;

use App\PrintJob;
use App\PrintProfile;

final class PrintJobService
{
    private PrintProfile $profiles;
    private PrintJob $jobs;

    public function __construct($pdo)
    {
        $this->profiles = new PrintProfile($pdo);
        $this->jobs = new PrintJob($pdo);
    }

    public function queue(string $outputType, string $entityType, int $entityId, array $data, ?int $locationId, ?int $userId, ?string $deviceId = null): ?int
    {
        $profile = $this->profiles->findForOutput($outputType, $locationId);
        if (!$profile) {
            return null;
        }
        return $this->jobs->create([
            'printer_id' => $profile['printer_id'],
            'template_version_id' => $profile['template_version_id'],
            'profile_id' => $profile['id'],
            'output_type' => $outputType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'payload_json' => json_encode($data, JSON_THROW_ON_ERROR),
            'rendered_format' => $profile['format'],
            'requested_by' => $userId,
            'device_id' => $deviceId,
        ]);
    }
}
