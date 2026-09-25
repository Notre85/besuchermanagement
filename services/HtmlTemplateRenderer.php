<?php

namespace App\Services;

final class HtmlTemplateRenderer implements TemplateRendererInterface
{
    public function render(array $templateVersion, array $data): array
    {
        $allowed = json_decode($templateVersion['variables_json'] ?? '[]', true);
        $allowed = is_array($allowed) ? $allowed : [];
        $source = (string) $templateVersion['source'];
        preg_match_all('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', $source, $matches);
        foreach (array_unique($matches[1] ?? []) as $variable) {
            if (!in_array($variable, $allowed, true)) {
                throw new \InvalidArgumentException('Nicht erlaubte Vorlagenvariable.');
            }
            $value = self::value($data, $variable);
            $source = str_replace(['{{' . $variable . '}}', '{{ ' . $variable . ' }}'], htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $source);
        }
        return ['format' => 'html', 'content' => $source, 'mime' => 'text/html; charset=UTF-8'];
    }

    private static function value(array $data, string $path): string
    {
        $value = $data;
        foreach (explode('.', $path) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return '';
            }
            $value = $value[$part];
        }
        return is_scalar($value) ? (string) $value : '';
    }
}
