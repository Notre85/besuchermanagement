<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Druckvorlagen</h2>
<?php if (isset($_GET['success'])): ?><div class="alert alert-success">Änderung gespeichert.</div><?php elseif (isset($_GET['error'])): ?><div class="alert alert-danger">Die Vorlagenänderung konnte nicht verarbeitet werden.</div><?php endif; ?>

<form method="POST" action="template_management.php?action=create" class="row g-2 mb-4">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="col-md-5"><label class="form-label" for="template-name">Name</label><input class="form-control" id="template-name" name="name" maxlength="100" required></div>
    <div class="col-md-5"><label class="form-label" for="template-purpose">Zweck</label><select class="form-select" id="template-purpose" name="purpose" required><option value="visitor_badge">Besucherausweis</option><option value="key_receipt">Schlüssel-BON</option><option value="key_label">Schlüssel-Label</option></select></div>
    <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary" type="submit">Vorlage anlegen</button></div>
</form>

<?php foreach ($templates as $template): ?>
<section class="card mb-4"><div class="card-body"><h3 class="h5"><?= htmlspecialchars($template['name'], ENT_QUOTES, 'UTF-8') ?> <small class="text-muted">(<?= htmlspecialchars($template['purpose'], ENT_QUOTES, 'UTF-8') ?>)</small></h3>
<form method="POST" action="template_management.php?action=version" class="row g-2">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="template_id" value="<?= (int) $template['id'] ?>">
    <div class="col-md-2"><label class="form-label">Format</label><select class="form-select" name="format"><option value="html">HTML</option><option value="pdf">PDF/TCPDF</option><option value="jasper">JasperReports</option></select></div>
    <div class="col-md-6"><label class="form-label">Quelle / Jasper-Report</label><textarea class="form-control" name="source" rows="3" maxlength="100000" required></textarea></div>
    <div class="col-md-3"><label class="form-label">Variablen, kommasepariert</label><input class="form-control" name="variables" value="visitor.first_name,visitor.last_name,visit.id,key.number,qr.code"></div>
    <div class="col-md-1 d-flex align-items-end"><button class="btn btn-outline-primary" type="submit">Version</button></div>
</form>
<table class="table table-sm mt-3"><thead><tr><th>Version</th><th>Format</th><th>Status</th><th>Aktionen</th></tr></thead><tbody>
<?php foreach ($template['versions'] as $version): ?><tr><td><?= (int) $version['version_no'] ?></td><td><?= htmlspecialchars($version['format'], ENT_QUOTES, 'UTF-8') ?></td><td><?= $version['active'] ? 'Aktiv' : 'Archiviert' ?></td><td><form class="d-inline" method="POST" action="template_management.php?action=activate"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="template_id" value="<?= (int) $template['id'] ?>"><input type="hidden" name="version_id" value="<?= (int) $version['id'] ?>"><button class="btn btn-sm btn-outline-success" type="submit">Aktivieren</button></form> <form class="d-inline" method="POST" action="template_management.php?action=preview" target="_blank"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="version_id" value="<?= (int) $version['id'] ?>"><button class="btn btn-sm btn-outline-secondary" type="submit">Testausgabe</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<?php endforeach; ?>

<?php include __DIR__ . '/../template/footer.php'; ?>
