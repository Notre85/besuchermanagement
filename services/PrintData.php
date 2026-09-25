<?php

namespace App\Services;

final class PrintData
{
    public static function forVisit(array $visit, ?array $key = null): array
    {
        return [
            'visitor' => [
                'first_name' => (string) ($visit['first_name'] ?? ''),
                'last_name' => (string) ($visit['last_name'] ?? ''),
                'company' => (string) ($visit['company'] ?? ''),
            ],
            'visit' => [
                'id' => (int) ($visit['id'] ?? 0),
                'checkin_time' => $visit['checkin_time'] ?? null,
                'checkout_time' => $visit['checkout_time'] ?? null,
                'starts_at' => $visit['starts_at'] ?? null,
                'ends_at' => $visit['ends_at'] ?? null,
            ],
            'host' => ['name' => trim(($visit['host_first_name'] ?? '') . ' ' . ($visit['host_last_name'] ?? ''))],
            'location' => ['name' => (string) ($visit['location_name'] ?? '')],
            'key' => [
                'number' => (string) ($key['key_number'] ?? ''),
                'label' => (string) ($key['label'] ?? ''),
                'return_due' => $visit['ends_at'] ?? null,
            ],
            'qr' => ['code' => 'visit:' . (int) ($visit['id'] ?? 0)],
        ];
    }
}
