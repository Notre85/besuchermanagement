<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Druckerverwaltung</h2>

<?php if (isset($_GET['success'])): ?><div class="alert alert-success">Änderung gespeichert.</div><?php elseif (isset($_GET['error'])): ?><div class="alert alert-danger">Die Eingaben konnten nicht verarbeitet werden.</div><?php endif; ?>

<form method="POST" action="drucker_verwaltung.php?action=create" class="mb-4">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="row g-2"><div class="col-md-4"><label class="visually-hidden" for="printer-name">Name</label><input class="form-control" id="printer-name" name="name" placeholder="Druckername" maxlength="100" required></div><div class="col-md-5"><label class="visually-hidden" for="printer-uri">Adresse</label><input class="form-control" id="printer-uri" name="printer_uri" placeholder="ipp://drucker.local/queue" maxlength="255" required></div><div class="col-md-3"><select class="form-select" name="location_id"><option value="">Kein Standort</option><?php foreach ($locations as $location): ?><option value="<?= (int) $location['id'] ?>"><?= htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div></div>
    <button class="btn btn-primary mt-2" type="submit">Drucker speichern</button>
</form>

<table class="table table-striped"><thead><tr><th>Name</th><th>Adresse</th><th>Standort</th><th>Aktion</th></tr></thead><tbody><?php foreach ($printers as $printer): ?><tr><td><?= htmlspecialchars($printer['name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($printer['printer_uri'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($printer['location_name'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td><td><form method="POST" action="drucker_verwaltung.php?action=delete"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int) $printer['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Deaktivieren</button></form></td></tr><?php endforeach; ?></tbody></table>

<?php include __DIR__ . '/../template/footer.php'; ?>
