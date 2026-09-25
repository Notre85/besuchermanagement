<?php

namespace App\Controllers;

use App\Location;
use App\PrintProfile;
use App\Printer;

class PrintProfileController extends BaseController
{
    private PrintProfile $profiles;
    private Printer $printers;
    private Location $locations;

    public function __construct($pdo, $logger)
    {
        parent::__construct($pdo, $logger);
        $this->profiles = new PrintProfile($pdo);
        $this->printers = new Printer($pdo);
        $this->locations = new Location($pdo);
    }

    public function show(): void
    {
        $this->requireRole(['Admin', 'Superadmin']);
        $this->render('print_profiles', ['profiles' => $this->profiles->getAll(), 'printers' => $this->printers->getAllActive(), 'locations' => $this->locations->getAllActive(), 'versions' => $this->profiles->getActiveTemplateVersions()]);
    }

    public function create(): void
    {
        $this->requirePost(); $this->requireRole(['Admin', 'Superadmin']); $this->requireCsrf();
        $name = trim($_POST['name'] ?? ''); $outputType = $_POST['output_type'] ?? '';
        $printerId = filter_var($_POST['printer_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $versionId = filter_var($_POST['template_version_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $locationId = filter_var($_POST['location_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($name === '' || mb_strlen($name) > 100 || !in_array($outputType, ['visitor_badge', 'key_receipt', 'key_label'], true) || !$printerId || !$versionId || !$this->profiles->versionMatchesOutput($versionId, $outputType)) $this->redirect('print_profiles.php?error=invalid_input');
        try { $id = $this->profiles->create($name, $outputType, $printerId, $versionId, $locationId === false ? null : $locationId); }
        catch (\Throwable $e) { $this->redirect('print_profiles.php?error=duplicate'); }
        $this->audit('print_profile.create', 'print_profile', $id); $this->redirect('print_profiles.php?success=created');
    }
}
