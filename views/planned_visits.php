<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Geplante Besuche</h2>

<?php if (!empty($notificationFailures)): ?>
    <div class="alert alert-warning">
        <strong>Benachrichtigungen konnten nicht versendet werden:</strong>
        <ul class="mb-0">
            <?php foreach ($notificationFailures as $failure): ?>
                <li><?= htmlspecialchars($failure['first_name'] . ' ' . $failure['last_name'] . ' (' . $failure['email'] . ')', ENT_QUOTES, 'UTF-8') ?> – <?= htmlspecialchars($failure['failed_at'], ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'created'): ?>
    <?php require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf_barcodes_2d.php'; $qr = new \TCPDF2DBarcode('index.php?access_code=' . rawurlencode($accessCode ?? ''), 'QRCODE,M'); ?>
    <div class="alert alert-success">Besuch angelegt. Einmaliger Zugangscode: <strong><?= htmlspecialchars($accessCode ?? '', ENT_QUOTES, 'UTF-8') ?></strong><br><img alt="QR-Code für den Besuch" width="160" height="160" src="data:image/png;base64,<?= base64_encode($qr->getBarcodePngData(5, 5)) ?>"></div>
<?php elseif (isset($_GET['error']) && $_GET['error'] === 'invalid_input'): ?>
    <div class="alert alert-danger">Bitte prüfen Sie Besucher, Zeitraum und Besuchsgrund.</div>
<?php endif; ?>

<form method="POST" action="planned_visits.php?action=create" class="mb-4">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="visitor_id">Besucher</label><select class="form-select" id="visitor_id" name="visitor_id" required><option value="">Bitte wählen</option><?php foreach ($visitors as $visitor): ?><option value="<?= (int) $visitor['id'] ?>"><?= htmlspecialchars($visitor['first_name'] . ' ' . $visitor['last_name'] . ($visitor['company'] ? ' (' . $visitor['company'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><label class="form-label" for="host_id">Gastgeber</label><select class="form-select" id="host_id" name="host_id"><option value="">Kein Gastgeber</option><?php foreach ($hosts as $host): ?><option value="<?= (int) $host['id'] ?>"><?= htmlspecialchars($host['first_name'] . ' ' . $host['last_name'] . ' (' . $host['email'] . ')', ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><label class="form-label" for="location_id">Standort</label><select class="form-select" id="location_id" name="location_id"><option value="">Kein Standort</option><?php foreach ($locations as $location): ?><option value="<?= (int) $location['id'] ?>"><?= htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label" for="starts_at">Von</label><input class="form-control" type="datetime-local" id="starts_at" name="starts_at" required></div>
        <div class="col-md-6"><label class="form-label" for="ends_at">Bis</label><input class="form-control" type="datetime-local" id="ends_at" name="ends_at" required></div>
        <div class="col-12"><label class="form-label" for="visit_reason">Besuchsgrund</label><textarea class="form-control" id="visit_reason" name="visit_reason" maxlength="500" required></textarea></div>
    </div>
    <button class="btn btn-primary mt-3" type="submit">Besuch anlegen</button>
</form>

<h3>Kommende Besuche</h3>
<table class="table table-striped"><thead><tr><th>Besucher</th><th>Zeitraum</th><th>Gastgeber</th><th>Standort</th><th>Status</th></tr></thead><tbody><?php foreach ($plannedVisits as $visit): ?><tr><td><?= htmlspecialchars($visit['first_name'] . ' ' . $visit['last_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($visit['starts_at'] . ' – ' . $visit['ends_at'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($visit['host_email'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($visit['location_name'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($visit['status'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table>

<?php include __DIR__ . '/../template/footer.php'; ?>
