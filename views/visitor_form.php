<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Besucherverwaltung</h2>

<?php
$currentSort = $sort ?? 'last_name';
$currentDirection = $direction ?? 'asc';
$sortLink = static function (string $field) use ($term, $currentSort, $currentDirection): string {
    $nextDirection = ($field === $currentSort && $currentDirection === 'asc') ? 'desc' : 'asc';
    return 'visitor_management.php?' . http_build_query(['q' => $term, 'page' => 1, 'sort' => $field, 'direction' => $nextDirection]);
};
$sortIndicator = static function (string $field) use ($currentSort, $currentDirection): string {
    return $field === $currentSort ? ($currentDirection === 'asc' ? ' ▲' : ' ▼') : '';
};
?>

<form method="GET" action="visitor_management.php" class="row g-2 mb-3">
    <div class="col-md-8"><label class="visually-hidden" for="visitor-search">Suche</label><input id="visitor-search" class="form-control" name="q" value="<?php echo htmlspecialchars($term ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Name, Firma oder Besucher-ID"></div>
    <div class="col-auto"><button class="btn btn-outline-primary" type="submit">Suchen</button></div>
</form>

<?php if (isset($_GET['success']) && $_GET['success'] === 'update'): ?>
    <div class="alert alert-success">Besucher erfolgreich aktualisiert!</div>
<?php elseif (isset($_GET['success']) && $_GET['success'] === 'delete'): ?>
    <div class="alert alert-success">Besucherdaten erfolgreich anonymisiert.</div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger">Fehler bei der Verarbeitung.</div>
<?php endif; ?>

<table class="table table-bordered">
    <thead>
        <tr>
            <th><a href="<?= htmlspecialchars($sortLink('id'), ENT_QUOTES, 'UTF-8') ?>">Besucher-ID<?= $sortIndicator('id') ?></a></th>
            <th><a href="<?= htmlspecialchars($sortLink('last_name'), ENT_QUOTES, 'UTF-8') ?>">Name<?= $sortIndicator('last_name') ?></a></th>
            <th><a href="<?= htmlspecialchars($sortLink('company'), ENT_QUOTES, 'UTF-8') ?>">Firma<?= $sortIndicator('company') ?></a></th>
            <th><a href="<?= htmlspecialchars($sortLink('created_at'), ENT_QUOTES, 'UTF-8') ?>">Angelegt<?= $sortIndicator('created_at') ?></a></th>
            <th>Aktionen</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($visitors as $visitor): ?>
            <tr>
                <td><?php echo htmlspecialchars($visitor['id']); ?></td>
                <td><?php echo htmlspecialchars($visitor['first_name'] . ' ' . $visitor['last_name']); ?></td>
                <td><?php echo htmlspecialchars($visitor['company'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($visitor['created_at'] ?? ''); ?></td>
                <td>
                    <!-- Details -->
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#detailsModal" data-visitorid="<?php echo $visitor['id']; ?>">Details</button>
                    
                    <!-- Bearbeiten -->
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editVisitorModal" data-id="<?php echo htmlspecialchars($visitor['id'], ENT_QUOTES, 'UTF-8'); ?>" data-firstname="<?php echo htmlspecialchars($visitor['first_name'], ENT_QUOTES, 'UTF-8'); ?>" data-lastname="<?php echo htmlspecialchars($visitor['last_name'], ENT_QUOTES, 'UTF-8'); ?>" data-company="<?php echo htmlspecialchars($visitor['company'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">Bearbeiten</button>
                    
                    <!-- Löschen -->
                    <form method="POST" action="visitor_management.php?action=delete" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($visitor['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Personenbezogene Daten dieses Besuchers anonymisieren?');">Anonymisieren</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if (($total ?? 0) > ($perPage ?? 50)): ?><nav aria-label="Besucherseiten"><ul class="pagination"><?php for ($p = 1; $p <= (int) ceil($total / $perPage); $p++): ?><li class="page-item<?= $p === $page ? ' active' : '' ?>"><a class="page-link" href="visitor_management.php?<?= htmlspecialchars(http_build_query(['q' => $term, 'page' => $p, 'sort' => $currentSort, 'direction' => $currentDirection]), ENT_QUOTES, 'UTF-8') ?>"><?= $p ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>

<!-- Modal zum Bearbeiten von Besuchern -->
<div class="modal fade" id="editVisitorModal" tabindex="-1" aria-labelledby="editVisitorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editVisitorModalLabel">Besucher bearbeiten</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="visitor_management.php?action=update">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="id" id="visitorId">
            <div class="mb-3">
                <label for="first_name" class="form-label">Vorname</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Nachname</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="mb-3">
                <label for="company" class="form-label">Firma</label>
                <input type="text" class="form-control" id="company" name="company">
            </div>
            <button type="submit" class="btn btn-primary">Speichern</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal für die Besucherdetails -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailsModalLabel">Besucherdetails</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="visitorDetailsContent">
        <!-- Die Inhalte der details werden dynamisch geladen -->
      </div>
    </div>
  </div>
</div>

<script>
// Modal mit den aktuellen Besucherdaten füllen (Bearbeiten)
document.getElementById('editVisitorModal').addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var id = button.getAttribute('data-id');
    var first_name = button.getAttribute('data-firstname');
    var last_name = button.getAttribute('data-lastname');
    var company = button.getAttribute('data-company');

    var modal = this;
    modal.querySelector('#visitorId').value = id;
    modal.querySelector('#first_name').value = first_name;
    modal.querySelector('#last_name').value = last_name;
    modal.querySelector('#company').value = company;
});

// Modal für die Details füllen und laden
document.getElementById('detailsModal').addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var visitorId = button.getAttribute('data-visitorid');
    var modal = this;
    
    // Lade die Details des Besuchers mit Ajax
    fetch('details.php?visitor_id=' + visitorId)
        .then(response => response.text())
        .then(data => {
            modal.querySelector('#visitorDetailsContent').innerHTML = data;
        })
        .catch(error => {
            modal.querySelector('#visitorDetailsContent').innerHTML = '<p>Fehler beim Laden der Besucherdetails.</p>';
        });
});
</script>

<?php include __DIR__ . '/../template/footer.php'; ?>
