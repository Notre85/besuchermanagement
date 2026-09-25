<?php
// controllers/BackupController.php

namespace App\Controllers;

class BackupController extends BaseController {
    public function performBackup() {
        $this->requirePost();
        $this->requireRole(['Admin', 'Superadmin']);
        $this->requireCsrf();

        // Datenbank-Backup durchführen
        $dbHost = $_ENV['DB_HOST'];
        $dbName = $_ENV['DB_NAME'];
        $dbUser = $_ENV['DB_USER'];
        $dbPass = $_ENV['DB_PASS'];
        $backupDir = rtrim($_ENV['BACKUP_DIR'] ?? '/var/backups/besuchermanagement', '/') . '/';
        $backupFile = $backupDir . "db-backup_" . date('Ymd_His') . ".sql";

        if (!is_dir($backupDir) && !mkdir($backupDir, 0700, true)) {
            http_response_code(500);
            exit('Fehler beim Erstellen des Backup-Verzeichnisses.');
        }

        $command = sprintf(
            'mysqldump --single-transaction --skip-lock-tables --no-tablespaces -h %s -u %s --password=%s %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName)
        );
        $output = [];
        $exitCode = 1;
        $previousUmask = umask(0077);
        exec($command . ' > ' . escapeshellarg($backupFile) . ' 2>&1', $output, $exitCode);
        umask($previousUmask);

        if ($exitCode === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
            chmod($backupFile, 0600);
            $retentionDays = max(1, (int) ($_ENV['BACKUP_RETENTION_DAYS'] ?? 30));
            foreach (glob($backupDir . 'db-backup_*.sql') ?: [] as $oldBackup) {
                if ($oldBackup !== $backupFile && is_file($oldBackup) && filemtime($oldBackup) < time() - ($retentionDays * 86400)) {
                    @unlink($oldBackup);
                }
            }
            $this->logger->info("Datenbank-Backup erstellt: {$backupFile}");
            $this->redirect('dashboard.php?success=backup');
        } else {
            $this->logger->error("Fehler beim Erstellen des Datenbank-Backups.");
            if (file_exists($backupFile)) {
                unlink($backupFile);
            }
            http_response_code(500);
            exit('Fehler beim Erstellen des Datenbank-Backups.');
        }
    }
}
