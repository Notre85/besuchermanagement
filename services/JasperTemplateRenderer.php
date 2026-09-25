<?php

namespace App\Services;

final class JasperTemplateRenderer implements TemplateRendererInterface
{
    public function render(array $templateVersion, array $data): array
    {
        $endpoint = rtrim($_ENV['JASPER_REPORTS_URL'] ?? '', '/');
        $token = (string) ($_ENV['JASPER_REPORTS_TOKEN'] ?? '');
        if ($endpoint === '' || $token === '' || !function_exists('curl_init')) {
            throw new \RuntimeException('JasperReports ist nicht konfiguriert.');
        }
        $payload = json_encode(['source' => $templateVersion['source'], 'data' => $data], JSON_THROW_ON_ERROR);
        $ch = curl_init($endpoint . '/render');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/pdf', 'Authorization: Bearer ' . $token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 15,
        ]);
        $content = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($content === false || $status < 200 || $status >= 300) {
            throw new \RuntimeException('JasperReports konnte die Vorlage nicht rendern: ' . ($error ?: 'HTTP ' . $status));
        }
        return ['format' => 'jasper', 'content' => $content, 'mime' => 'application/pdf'];
    }
}
