<?php

namespace App\Controllers;

use App\Host;
use App\Location;

class MasterDataController extends BaseController
{
    private $hostModel;
    private $locationModel;

    public function __construct($pdo, $logger)
    {
        parent::__construct($pdo, $logger);
        $this->hostModel = new Host($pdo);
        $this->locationModel = new Location($pdo);
    }

    public function show()
    {
        $this->requireRole(['Admin', 'Superadmin']);
        $this->render('master_data', [
            'hosts' => $this->hostModel->getAllActive(),
            'locations' => $this->locationModel->getAllActive(),
        ]);
    }

    public function createHost()
    {
        $this->requirePost();
        $this->requireRole(['Admin', 'Superadmin']);
        $this->requireCsrf();
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $department = trim($_POST['department'] ?? '');
        if ($firstName === '' || $lastName === '' || mb_strlen($firstName) > 50 || mb_strlen($lastName) > 50 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($department) > 100) {
            $this->redirect('master_data.php?error=invalid_host');
        }
        $id = $this->hostModel->create($firstName, $lastName, $email, $department ?: null);
        $this->audit('host.create', 'host', $id);
        $this->redirect('master_data.php?success=created');
    }

    public function createLocation()
    {
        $this->requirePost();
        $this->requireRole(['Admin', 'Superadmin']);
        $this->requireCsrf();
        $name = trim($_POST['name'] ?? '');
        $building = trim($_POST['building'] ?? '');
        $floor = trim($_POST['floor'] ?? '');
        if ($name === '' || mb_strlen($name) > 100 || mb_strlen($building) > 100 || mb_strlen($floor) > 50) {
            $this->redirect('master_data.php?error=invalid_location');
        }
        $id = $this->locationModel->create($name, $building ?: null, $floor ?: null);
        $this->audit('location.create', 'location', $id);
        $this->redirect('master_data.php?success=created');
    }

    public function deleteHost()
    {
        $this->delete('host');
    }

    public function deleteLocation()
    {
        $this->delete('location');
    }

    private function delete($type)
    {
        $this->requirePost();
        $this->requireRole(['Admin', 'Superadmin']);
        $this->requireCsrf();
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id || ($type === 'host' ? !$this->hostModel->delete($id) : !$this->locationModel->delete($id))) {
            $this->redirect('master_data.php?error=delete_failed');
        }
        $this->audit($type . '.delete', $type, $id);
        $this->redirect('master_data.php?success=deleted');
    }
}
