<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

$createdBy = 1;
$definitions = [
    [
        'Besucherausweis', 'visitor_badge', 85, 54,
        '<div style="font-family:Arial,sans-serif;color:#123f69;"><div style="font-size:14px;font-weight:bold;border-bottom:1px solid #123f69;padding-bottom:3px;">BESUCHERAUSWEIS</div><div style="font-size:18px;font-weight:bold;margin-top:8px;">{{ visitor.first_name }} {{ visitor.last_name }}</div><div style="font-size:10px;margin-top:4px;">{{ visitor.company }}</div><div style="font-size:10px;margin-top:8px;">Besuch: {{ visit.id }}<br>Gültig ab: {{ visit.checkin_time }}<br>Gültig bis: {{ visit.ends_at }}</div><div style="font-size:9px;margin-top:7px;">Gastgeber: {{ host.name }}<br>Ort: {{ location.name }}</div><div style="font-size:9px;margin-top:6px;border-top:1px solid #ccd6df;padding-top:3px;">QR: {{ qr.code }}</div></div>',
        ['visitor.first_name','visitor.last_name','visitor.company','visit.id','visit.checkin_time','visit.ends_at','host.name','location.name','qr.code'],
    ],
    [
        'Schlüssel-BON', 'key_receipt', 80, 50,
        '<div style="font-family:Arial,sans-serif;color:#1d2a3a;"><div style="font-size:14px;font-weight:bold;border-bottom:1px solid #1d2a3a;padding-bottom:4px;">SCHLÜSSEL-BON</div><div style="font-size:13px;font-weight:bold;margin-top:8px;">{{ key.number }} – {{ key.label }}</div><div style="font-size:10px;margin-top:8px;">Besucher: {{ visitor.first_name }} {{ visitor.last_name }}<br>Besuch: {{ visit.id }}<br>Ausgabe: {{ visit.checkin_time }}<br>Rückgabe bis: {{ key.return_due }}</div><div style="font-size:9px;margin-top:9px;">Bitte Schlüssel beim Check-out zurückgeben.</div></div>',
        ['key.number','key.label','visitor.first_name','visitor.last_name','visit.id','visit.checkin_time','key.return_due'],
    ],
    [
        'Schlüssel-Label', 'key_label', 62, 29,
        '<div style="font-family:Arial,sans-serif;color:#1d2a3a;text-align:center;"><div style="font-size:12px;font-weight:bold;">{{ key.number }}</div><div style="font-size:9px;margin-top:4px;">{{ key.label }}</div><div style="font-size:8px;margin-top:5px;">Besuch {{ visit.id }}<br>{{ visitor.last_name }}</div></div>',
        ['key.number','key.label','visit.id','visitor.last_name'],
    ],
];

foreach ($definitions as [$name, $purpose, $width, $height, $source, $variables]) {
    $find = $pdo->prepare('SELECT id FROM print_templates WHERE name = :name AND purpose = :purpose LIMIT 1');
    $find->execute(['name' => $name, 'purpose' => $purpose]);
    $templateId = (int) $find->fetchColumn();
    if (!$templateId) {
        $insert = $pdo->prepare('INSERT INTO print_templates (name, purpose, created_by) VALUES (:name, :purpose, :created_by)');
        $insert->execute(['name' => $name, 'purpose' => $purpose, 'created_by' => $createdBy]);
        $templateId = (int) $pdo->lastInsertId();
    }

    $active = $pdo->prepare('SELECT id FROM print_template_versions WHERE template_id = :template_id AND active = 1 LIMIT 1');
    $active->execute(['template_id' => $templateId]);
    $versionId = (int) $active->fetchColumn();
    if (!$versionId) {
        $next = $pdo->prepare('SELECT COALESCE(MAX(version_no), 0) + 1 FROM print_template_versions WHERE template_id = :template_id');
        $next->execute(['template_id' => $templateId]);
        $versionNo = (int) $next->fetchColumn();
        $insertVersion = $pdo->prepare('INSERT INTO print_template_versions (template_id, version_no, format, source, page_width, page_height, variables_json, active, created_by) VALUES (:template_id, :version_no, "pdf", :source, :page_width, :page_height, :variables_json, 1, :created_by)');
        $insertVersion->execute([
            'template_id' => $templateId, 'version_no' => $versionNo, 'source' => $source,
            'page_width' => $width, 'page_height' => $height,
            'variables_json' => json_encode($variables, JSON_THROW_ON_ERROR), 'created_by' => $createdBy,
        ]);
        $versionId = (int) $pdo->lastInsertId();
    }
    echo $name, ': template=', $templateId, ', active_version=', $versionId, PHP_EOL;
}
