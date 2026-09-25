<?php

namespace App\Controllers;

use App\KeyInventory;
use App\Location;

class KeyController extends BaseController
{
    private KeyInventory $keys;
    private Location $locations;

    public function __construct($pdo, $logger)
    {
        parent::__construct($pdo, $logger);
        $this->keys = new KeyInventory($pdo);
        $this->locations = new Location($pdo);
    }

    public function show(): void
    {
        $this->requireRole(['Admin', 'Superadmin']);
        $this->render('key_management', [
            'keys' => $this->keys->getAllActive(),
            'locations' => $this->locations->getAllActive(),
            'assignments' => $this->keys->getAssignmentHistory(),
        ]);
    }

    public function create(): void
    {
        $this->requirePost();
        $this->requireRole(['Admin', 'Superadmin']);
        $this->requireCsrf();
        $number = trim($_POST['key_number'] ?? '');
        $label = trim($_POST['label'] ?? '');
        $type = trim($_POST['key_type'] ?? 'standard');
        $locationId = filter_var($_POST['location_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($number === '' || mb_strlen($number) > 50 || $label === '' || mb_strlen($label) > 100 || $type === '' || mb_strlen($type) > 50) {
            $this->redirect('key_management.php?error=invalid_input');
        }
        try {
            $id = $this->keys->create($number, $label, $type, $locationId === false ? null : $locationId);
        } catch (\Throwable $e) {
            $this->logger->warning('Schlüssel konnte nicht angelegt werden.');
            $this->redirect('key_management.php?error=duplicate');
        }
        $this->audit('key.create', 'key', $id);
        $this->redirect('key_management.php?success=created');
    }

    public function deactivate(): void
    {
        $this->requirePost();
        $this->requireRole(['Admin', 'Superadmin']);
        $this->requireCsrf();
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$id || !$this->keys->deactivate($id)) {
            $this->redirect('key_management.php?error=not_available');
        }
        $this->audit('key.deactivate', 'key', $id);
        $this->redirect('key_management.php?success=deactivated');
    }
}
