<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Stammdaten</h2>
<?php if (isset($_GET['success'])): ?><div class="alert alert-success">Änderung gespeichert.</div><?php elseif (isset($_GET['error'])): ?><div class="alert alert-danger">Die Eingaben konnten nicht verarbeitet werden.</div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-6">
        <h3>Gastgeber hinzufügen</h3>
        <form method="POST" action="master_data.php?action=create_host">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input class="form-control mb-2" name="first_name" placeholder="Vorname" maxlength="50" required>
            <input class="form-control mb-2" name="last_name" placeholder="Nachname" maxlength="50" required>
            <input class="form-control mb-2" type="email" name="email" placeholder="E-Mail" maxlength="254" required>
            <input class="form-control mb-2" name="department" placeholder="Abteilung" maxlength="100">
            <button class="btn btn-primary" type="submit">Gastgeber speichern</button>
        </form>
        <ul class="list-group mt-3"><?php foreach ($hosts as $host): ?><li class="list-group-item d-flex justify-content-between"><span><?= htmlspecialchars($host['first_name'] . ' ' . $host['last_name'] . ' – ' . $host['email'], ENT_QUOTES, 'UTF-8') ?></span><form method="POST" action="master_data.php?action=delete_host"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int) $host['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Deaktivieren</button></form></li><?php endforeach; ?></ul>
    </div>
    <div class="col-md-6">
        <h3>Standort hinzufügen</h3>
        <form method="POST" action="master_data.php?action=create_location">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input class="form-control mb-2" name="name" placeholder="Name" maxlength="100" required>
            <input class="form-control mb-2" name="building" placeholder="Gebäude" maxlength="100">
            <input class="form-control mb-2" name="floor" placeholder="Etage" maxlength="50">
            <button class="btn btn-primary" type="submit">Standort speichern</button>
        </form>
        <ul class="list-group mt-3"><?php foreach ($locations as $location): ?><li class="list-group-item d-flex justify-content-between"><span><?= htmlspecialchars($location['name'] . ($location['building'] ? ' – ' . $location['building'] : ''), ENT_QUOTES, 'UTF-8') ?></span><form method="POST" action="master_data.php?action=delete_location"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int) $location['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Deaktivieren</button></form></li><?php endforeach; ?></ul>
    </div>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
