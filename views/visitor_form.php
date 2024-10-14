<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Besucherverwaltung</h2>

<?php if (isset($_GET['success']) && $_GET['success'] === 'update'): ?>
    <div class="alert alert-success">Besucher erfolgreich aktualisiert!</div>
<?php elseif (isset($_GET['success']) && $_GET['success'] === 'delete'): ?>
    <div class="alert alert-success">Besucher erfolgreich gelöscht!</div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger">Fehler bei der Verarbeitung.</div>
<?php endif; ?>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Besucher-ID</th>
            <th>Name</th>
            <th>Firma</th>
            <th>Aktionen</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($visitors as $visitor): ?>
            <tr>
                <td><?php echo htmlspecialchars($visitor['id']); ?></td>
                <td><?php echo htmlspecialchars($visitor['first_name'] . ' ' . $visitor['last_name']); ?></td>
                <td><?php echo htmlspecialchars($visitor['company'] ?? 'N/A'); ?></td>
                <td>
                    <!-- Details -->
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#detailsModal" data-visitorid="<?php echo $visitor['id']; ?>">Details</button>
                    
                    <!-- Bearbeiten -->
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editVisitorModal" data-id="<?php echo $visitor['id']; ?>" data-firstname="<?php echo $visitor['first_name']; ?>" data-lastname="<?php echo $visitor['last_name']; ?>" data-company="<?php echo $visitor['company']; ?>">Bearbeiten</button>
                    
                    <!-- Löschen -->
                    <form method="POST" action="visitor_management.php?action=delete" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $visitor['id']; ?>">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Sind Sie sicher, dass Sie diesen Besucher löschen möchten?');">Löschen</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

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
