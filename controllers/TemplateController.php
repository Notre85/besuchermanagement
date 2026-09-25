<?php

namespace App\Controllers;

use App\PrintTemplate;
use App\Services\HtmlTemplateRenderer;
use App\Services\JasperTemplateRenderer;
use App\Services\PdfTemplateRenderer;

class TemplateController extends BaseController
{
    private PrintTemplate $templates;
    private const PURPOSES = ['visitor_badge', 'key_receipt', 'key_label'];
    private const FORMATS = ['html', 'pdf', 'jasper'];
    private const VARIABLES = [
        'visitor.first_name', 'visitor.last_name', 'visitor.company', 'visit.id',
        'visit.checkin_time', 'visit.checkout_time', 'visit.starts_at', 'visit.ends_at',
        'host.name', 'location.name', 'key.number', 'key.label', 'key.return_due', 'qr.code',
    ];

    public function __construct($pdo, $logger)
    {
        parent::__construct($pdo, $logger);
        $this->templates = new PrintTemplate($pdo);
    }

    public function show(): void
    {
        $this->requireRole(['Admin', 'Superadmin']);
        $templates = $this->templates->getAll();
        foreach ($templates as &$template) $template['versions'] = $this->templates->getVersions((int) $template['id']);
        $this->render('template_management', ['templates' => $templates, 'purposes' => self::PURPOSES, 'formats' => self::FORMATS, 'variables' => self::VARIABLES]);
    }

    public function create(): void
    {
        $this->requirePost(); $this->requireRole(['Admin', 'Superadmin']); $this->requireCsrf();
        $name = trim($_POST['name'] ?? ''); $purpose = $_POST['purpose'] ?? '';
        if ($name === '' || mb_strlen($name) > 100 || !in_array($purpose, self::PURPOSES, true)) $this->redirect('template_management.php?error=invalid_input');
        try { $id = $this->templates->create($name, $purpose, (int) $_SESSION['user']['id']); }
        catch (\Throwable $e) { $this->redirect('template_management.php?error=duplicate'); }
        $this->audit('template.create', 'print_template', $id); $this->redirect('template_management.php?success=created');
    }

    public function createVersion(): void
    {
        $this->requirePost(); $this->requireRole(['Admin', 'Superadmin']); $this->requireCsrf();
        $templateId = filter_var($_POST['template_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $format = $_POST['format'] ?? ''; $source = trim($_POST['source'] ?? '');
        $variables = array_values(array_intersect(self::VARIABLES, array_filter(array_map('trim', explode(',', $_POST['variables'] ?? '')))));
        if (!$templateId || !in_array($format, self::FORMATS, true) || $source === '' || mb_strlen($source) > 100000 || ($format === 'html' || $format === 'pdf') && preg_match('/<\/?(?:script|iframe|object|embed)\b/i', $source)) {
            $this->redirect('template_management.php?error=invalid_input');
        }
        $width = filter_var($_POST['page_width'] ?? null, FILTER_VALIDATE_FLOAT); $height = filter_var($_POST['page_height'] ?? null, FILTER_VALIDATE_FLOAT);
        $id = $this->templates->createVersion((int) $templateId, $format, $source, $variables, $width === false ? null : $width, $height === false ? null : $height, (int) $_SESSION['user']['id']);
        $this->audit('template.version.create', 'print_template_version', $id); $this->redirect('template_management.php?success=version_created');
    }

    public function activate(): void
    {
        $this->requirePost(); $this->requireRole(['Admin', 'Superadmin']); $this->requireCsrf();
        $templateId = filter_var($_POST['template_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $versionId = filter_var($_POST['version_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$templateId || !$versionId || !$this->templates->activateVersion($templateId, $versionId)) $this->redirect('template_management.php?error=activation_failed');
        $this->audit('template.version.activate', 'print_template_version', $versionId); $this->redirect('template_management.php?success=activated');
    }

    public function preview(): void
    {
        $this->requirePost(); $this->requireRole(['Admin', 'Superadmin']); $this->requireCsrf();
        $versionId = filter_var($_POST['version_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $version = $versionId ? $this->templates->findVersion($versionId) : null;
        if (!$version) { http_response_code(404); exit('Vorlage nicht gefunden.'); }
        $data = ['visitor' => ['first_name' => 'Max', 'last_name' => 'Mustermann', 'company' => 'Beispiel GmbH'], 'visit' => ['id' => 123, 'checkin_time' => date('Y-m-d H:i:s'), 'starts_at' => date('Y-m-d H:i:s'), 'ends_at' => date('Y-m-d H:i:s', time() + 3600)], 'host' => ['name' => 'Erika Beispiel'], 'location' => ['name' => 'Empfang'], 'key' => ['number' => 'K-001', 'label' => 'Hauptschlüssel', 'return_due' => date('Y-m-d H:i:s', time() + 3600)], 'qr' => ['code' => 'visit:123']];
        switch ($version['format']) {
            case 'html': $renderer = new HtmlTemplateRenderer(); break;
            case 'pdf': $renderer = new PdfTemplateRenderer(); break;
            case 'jasper': $renderer = new JasperTemplateRenderer(); break;
            default: http_response_code(400); exit('Unbekanntes Vorlagenformat.');
        }
        $result = $renderer->render($version, $data); header('Content-Type: ' . $result['mime']); echo $result['content']; $this->audit('template.preview', 'print_template_version', $versionId); exit();
    }
}
