#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';

$days = (int) ($_ENV['RETENTION_DAYS'] ?? 365);
if ($days < 30) {
    fwrite(STDERR, "RETENTION_DAYS muss mindestens 30 sein.\n");
    exit(1);
}

$cutoff = (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))
    ->modify('-' . $days . ' days')
    ->format('Y-m-d H:i:s');

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare(
        "UPDATE visitors v
         SET v.first_name = 'Anonymisiert', v.last_name = 'Anonymisiert', v.company = NULL
         WHERE v.updated_at < :cutoff
           AND NOT EXISTS (
               SELECT 1 FROM visits recent
               WHERE recent.visitor_id = v.id AND recent.checkin_time >= :cutoff_recent
           )"
    );
    $stmt->execute(['cutoff' => $cutoff, 'cutoff_recent' => $cutoff]);
    $auditStmt = $pdo->prepare('DELETE FROM audit_log WHERE created_at < :cutoff');
    $auditStmt->execute(['cutoff' => $cutoff]);
    $pdo->commit();
    echo $stmt->rowCount() . " Besucher anonymisiert, " . $auditStmt->rowCount() . " Audit-Einträge gelöscht.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "Aufbewahrungslauf fehlgeschlagen.\n");
    exit(1);
}
