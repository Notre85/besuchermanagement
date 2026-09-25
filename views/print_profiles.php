<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Druckprofile</h2>
<?php if (isset($_GET['success'])): ?><div class="alert alert-success">Druckprofil gespeichert.</div><?php elseif (isset($_GET['error'])): ?><div class="alert alert-danger">Das Druckprofil konnte nicht gespeichert werden.</div><?php endif; ?>

<form method="POST" action="print_profiles.php?action=create" class="row g-2 mb-4">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="col-md-3"><label class="form-label" for="profile-name">Name</label><input class="form-control" id="profile-name" name="name" maxlength="100" required></div>
    <div class="col-md-2"><label class="form-label" for="output-type">Ausgabe</label><select class="form-select" id="output-type" name="output_type"><option value="visitor_badge">Besucherausweis</option><option value="key_receipt">Schlüssel-BON</option><option value="key_label">Schlüssel-Label</option></select></div>
    <div class="col-md-2"><label class="form-label" for="printer-id">Drucker</label><select class="form-select" id="printer-id" name="printer_id" required><?php foreach ($printers as $printer): ?><option value="<?= (int) $printer['id'] ?>"><?= htmlspecialchars($printer['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label" for="template-version-id">Aktive Vorlage</label><select class="form-select" id="template-version-id" name="template_version_id" required><?php foreach ($versions as $version): ?><option value="<?= (int) $version['id'] ?>"><?= htmlspecialchars($version['name'] . ' v' . $version['version_no'] . ' (' . $version['format'] . ')', ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label" for="profile-location">Standort</label><select class="form-select" id="profile-location" name="location_id"><option value="">Alle</option><?php foreach ($locations as $location): ?><option value="<?= (int) $location['id'] ?>"><?= htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
    <div class="col-12"><button class="btn btn-primary" type="submit">Profil speichern</button></div>
</form>

<table class="table table-striped"><thead><tr><th>Name</th><th>Ausgabe</th><th>Drucker</th><th>Vorlage</th><th>Standort</th></tr></thead><tbody><?php foreach ($profiles as $profile): ?><tr><td><?= htmlspecialchars($profile['name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($profile['output_type'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($profile['printer_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($profile['template_name'] . ' v' . $profile['version_no'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($profile['location_id'] ? (string) $profile['location_id'] : 'Alle', ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table>

<?php include __DIR__ . '/../template/footer.php'; ?>
