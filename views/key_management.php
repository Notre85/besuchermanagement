<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Schlüsselverwaltung</h2>
<?php if (isset($_GET['success'])): ?><div class="alert alert-success">Änderung gespeichert.</div><?php elseif (isset($_GET['error'])): ?><div class="alert alert-danger">Die Schlüsseländerung konnte nicht verarbeitet werden.</div><?php endif; ?>

<form method="POST" action="key_management.php?action=create" class="row g-2 mb-4">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="col-md-3"><label class="form-label" for="key_number">Schlüsselnummer</label><input class="form-control" id="key_number" name="key_number" maxlength="50" required></div>
    <div class="col-md-3"><label class="form-label" for="label">Bezeichnung</label><input class="form-control" id="label" name="label" maxlength="100" required></div>
    <div class="col-md-2"><label class="form-label" for="key_type">Typ</label><input class="form-control" id="key_type" name="key_type" value="standard" maxlength="50" required></div>
    <div class="col-md-3"><label class="form-label" for="location_id">Standort</label><select class="form-select" id="location_id" name="location_id"><option value="">Alle/zentral</option><?php foreach ($locations as $location): ?><option value="<?= (int) $location['id'] ?>"><?= htmlspecialchars($location['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
    <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary" type="submit">Anlegen</button></div>
</form>

<table class="table table-striped"><thead><tr><th>Nummer</th><th>Bezeichnung</th><th>Typ</th><th>Standort</th><th>Status</th><th>Aktion</th></tr></thead><tbody>
<?php foreach ($keys as $key): ?><tr><td><?= htmlspecialchars($key['key_number'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($key['label'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($key['key_type'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($key['location_name'] ?? 'Zentral', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($key['status'], ENT_QUOTES, 'UTF-8') ?></td><td><?php if ($key['status'] === 'available'): ?><form method="POST" action="key_management.php?action=deactivate"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int) $key['id'] ?>"><button type="submit" class="btn btn-sm btn-outline-danger">Sperren</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table>

<h3 class="mt-5">Ausgabehistorie</h3>
<p class="text-muted">Die Historie zeigt, welcher Besucher welchen Schlüssel wann erhalten und zurückgegeben hat.</p>
<div class="table-responsive"><table class="table table-sm table-striped"><thead><tr><th>Schlüssel</th><th>Besucher</th><th>Ausgabe</th><th>Rückgabe</th><th>Status</th><th>Ausgegeben durch</th><th>Zurückgenommen durch</th></tr></thead><tbody>
<?php foreach ($assignments as $assignment): ?><tr><td><?= htmlspecialchars($assignment['key_number'] . ' – ' . $assignment['label'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($assignment['first_name'] . ' ' . $assignment['last_name'] . ' (ID ' . $assignment['visitor_id'] . ')', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($assignment['issued_at'])), ENT_QUOTES, 'UTF-8') ?></td><td><?= $assignment['returned_at'] ? htmlspecialchars(date('d.m.Y H:i', strtotime($assignment['returned_at'])), ENT_QUOTES, 'UTF-8') : '—' ?></td><td><?= htmlspecialchars($assignment['status'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($assignment['issued_by_username'] ?? 'Kiosk/System', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($assignment['returned_by_username'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?>
<?php if (empty($assignments)): ?><tr><td colspan="7" class="text-center">Noch keine Schlüssel ausgegeben.</td></tr><?php endif; ?>
</tbody></table></div>

<?php include __DIR__ . '/../template/footer.php'; ?>
