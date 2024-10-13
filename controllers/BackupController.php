<?php
// controllers/BackupController.php

namespace App\Controllers;

class BackupController extends BaseController {
    public function performBackup() {
        $this->requireLogin();
        $currentUser = $this->getCurrentUser();
        if (!in_array($currentUser['role'], ['Admin', 'Superadmin'])) {
            die('Zugriff verweigert.');
        }

        // Datenbank-Backup durchführen
        $dbHost = $_ENV['DB_HOST'];
        $dbName = $_ENV['DB_NAME'];
        $dbUser = $_ENV['DB_USER'];
        $dbPass = $_ENV['DB_PASS'];
        $backupDir = __DIR__ . "/../backup/";
        $backupFile = $backupDir . "db-backup_" . date('Ymd_His') . ".sql";

        $command = "mysqldump -h {$dbHost} -u {$dbUser} -p'{$dbPass}' {$dbName} > {$backupFile}";
        system($command, $output);

        if (file_exists($backupFile)) {
            $this->logger->info("Datenbank-Backup erstellt: {$backupFile}");
            $this->redirect('dashboard.php?success=backup');
        } else {
            $this->logger->error("Fehler beim Erstellen des Datenbank-Backups.");
            die('Fehler beim Erstellen des Datenbank-Backups.');
        }
    }
}
