<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Dashboard</h2>

<?php if (isset($_GET['success']) && $_GET['success'] === 'backup'): ?>
    <div class="alert alert-success">Backup erfolgreich erstellt!</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">Gesamtanzahl Besucher</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $totalVisitors; ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Aktuell eingecheckte Besucher</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo count($currentVisits); ?></h5>
            </div>
        </div>
    </div>
</div>


<!-- Erfolgsmeldungen -->
<?php if (isset($_GET['success']) && $_GET['success'] === 'checkin'): ?>
    <div class="alert alert-success">Check-In erfolgreich!</div>
<?php elseif (isset($_GET['success']) && $_GET['success'] === 'checkout'): ?>
    <div class="alert alert-success">Check-Out erfolgreich!</div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php
            switch ($_GET['error']) {
                case 'visitor_not_found':
                    echo 'Besucher mit dieser ID wurde nicht gefunden.';
                    break;
                case 'required_fields_missing':
                    echo 'Vorname, Nachname und Besuchsgrund sind erforderlich, wenn keine Besucher-ID angegeben ist.';
                    break;
                case 'visit_reason_required':
                    echo 'Besuchsgrund ist erforderlich.';
                    break;
                case 'visitor_creation_failed':
                    echo 'Besucher konnte nicht erstellt werden.';
                    break;
                case 'checkout_failed':
                    echo 'Fehler beim Auschecken.';
                    break;
                case 'invalid_visit_id':
                    echo 'Ung¸ltige Visit ID.';
                    break;
                case 'invalid_visitor_id':
                    echo 'Ung¸ltige Besucher ID.';
                    break;
                case 'already_checked_in':
                    echo 'Der Besucher ist bereits eingecheckt.';
                    break;
                case 'not_checked_in':
                    echo 'Der Besucher ist nicht eingecheckt.';
                    break;
                case 'missing_parameters':
                    echo 'Weder Visit ID noch Visitor ID f¸r Check-Out angegeben.';
                    break;
                default:
                    echo 'Ein unbekannter Fehler ist aufgetreten.';
            }
        ?>
    </div>
<?php endif; ?>

<!-- Dashboard-Tabelle -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Visit ID</th>
            <th>Visitor ID</th>
            <th>Name</th>
            <th>Firma</th>
            <th>Grund</th>
            <th>Check-In</th>
            <th>Aktionen</th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($currentVisits) && count($currentVisits) > 0): ?>
            <?php foreach ($currentVisits as $visit): ?>
                <tr>
                    <td><?php echo htmlspecialchars($visit['visit_id']); ?></td>
                    <td><?php echo htmlspecialchars($visit['visitor_id']); ?></td>
                    <td><?php echo htmlspecialchars($visit['first_name'] . ' ' . $visit['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($visit['company'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($visit['visit_reason']); ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($visit['checkin_time'])); ?></td>
                    <td>
                        <!-- Details-Button -->
                        <button 
                            type="button" 
                            class="btn btn-info btn-sm details-button" 
                            data-bs-toggle="modal" 
                            data-bs-target="#detailsModal" 
                            data-visitor-id="<?php echo htmlspecialchars($visit['visitor_id']); ?>"
                            data-visit-id="<?php echo htmlspecialchars($visit['visit_id']); ?>"
                        >
                            Details
                        </button>

                        <!-- Check-Out via Visit ID -->
                        <a href="index.php?action=checkout&visit_id=<?php echo htmlspecialchars($visit['visit_id']); ?>" class="btn btn-success btn-sm">Check-Out</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">Keine aktuell eingecheckten Besucher.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="detailsModalLabel">Besucher Details</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Inhalte werden via AJAX geladen -->
        <div id="modalContent">
            <p>Details werden geladen...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schlieﬂen</button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript zur Handhabung der Details-Buttons -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const detailsButtons = document.querySelectorAll('.details-button');
    const modal = document.getElementById('detailsModal');
    const modalContent = document.getElementById('modalContent');

    detailsButtons.forEach(button => {
        button.addEventListener('click', function () {
            const visitorId = this.getAttribute('data-visitor-id');
            const visitId = this.getAttribute('data-visit-id');

            // Optional: Zeigen Sie eine Ladeanimation oder einen Spinner
            modalContent.innerHTML = '<p>Details werden geladen...</p>';

            // AJAX-Anfrage senden, um die Details zu erhalten
            fetch(`details.php?visitor_id=${encodeURIComponent(visitorId)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Netzwerkantwort war nicht ok');
                    }
                    return response.text();
                })
                .then(data => {
                    modalContent.innerHTML = data;
                })
                .catch(error => {
                    modalContent.innerHTML = `<p class="text-danger">Fehler beim Laden der Details: ${error.message}</p>`;
                });
        });
    });
});
</script>

<?php include __DIR__ . '/../template/footer.php'; ?>
