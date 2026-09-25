<?php

namespace App\Controllers;

use App\PrintJob;

class PrintJobController extends BaseController
{
    private PrintJob $jobs;

    public function __construct($pdo, $logger)
    {
        parent::__construct($pdo, $logger);
        $this->jobs = new PrintJob($pdo);
    }

    public function show(): void
    {
        $this->requireRole(['Admin', 'Superadmin']);
        $this->render('print_jobs', ['jobs' => $this->jobs->getRecent()]);
    }

    public function retry(): void
    {
        $this->requirePost(); $this->requireRole(['Admin', 'Superadmin']); $this->requireCsrf();
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$id || !$this->jobs->retry($id)) $this->redirect('print_jobs.php?error=retry_failed');
        $this->audit('print.retry', 'print_job', $id); $this->redirect('print_jobs.php?success=retried');
    }
}
