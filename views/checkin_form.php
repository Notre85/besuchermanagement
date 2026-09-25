<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Besucher Check-In</h2>

<?php if (isset($_GET['success']) && $_GET['success'] === 'checkin'): ?>
    <div class="alert alert-success">Check-In erfolgreich!</div>
<?php elseif (isset($_GET['success']) && $_GET['success'] === 'checkout'): ?>
    <div class="alert alert-success">Check-Out erfolgreich!</div>
<?php elseif (isset($_GET['success']) && $_GET['success'] === 'key_issued'): ?>
    <div class="alert alert-success">Schlüssel wurde ausgegeben und als Druckjob eingereiht.</div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php
            switch ($_GET['error']) {
                case 'visitor_not_found':
                    echo 'Besucher mit dieser ID wurde nicht gefunden.';
                    break;
                case 'invalid_access_code':
                    echo 'Der Zugangscode ist ungültig oder abgelaufen.';
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
                    echo 'Ungültige Visit ID.';
                    break;
                case 'invalid_visitor_id':
                    echo 'Ungültige Besucher ID.';
                    break;
                case 'already_checked_in':
                    echo 'Der Besucher ist bereits eingecheckt.';
                    break;
                case 'key_issue_invalid':
                    echo 'Besuch oder Schlüssel ist ungültig.';
                    break;
                case 'key_already_issued':
                    echo 'Für diesen Besuch wurde bereits ein Schlüssel ausgegeben.';
                    break;
                case 'key_unavailable':
                    echo 'Der ausgewählte Schlüssel ist nicht mehr verfügbar oder gehört zu einem anderen Standort.';
                    break;
                case 'not_checked_in':
                    echo 'Der Besucher ist nicht eingecheckt.';
                    break;
                case 'missing_parameters':
                    echo 'Weder Visit ID noch Visitor ID für Check-Out angegeben.';
                    break;
                default:
                    echo 'Ein unbekannter Fehler ist aufgetreten.';
            }
        ?>
    </div>
<?php endif; ?>

<form method="POST" action="index.php" id="checkinForm">
    <input type="hidden" name="action" value="checkin">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- Optional Visitor-ID -->
    <div class="mb-3">
        <label for="visitor_id" class="form-label">Besucher-ID (optional)</label>
        <input type="number" id="visitor_id" name="visitor_id" class="form-control" placeholder="Besucher-ID">
    </div>
    
    <p>Oder</p>

    <div class="mb-3">
        <label for="access_code" class="form-label">Termin-Code (optional)</label>
        <input type="text" id="access_code" name="access_code" class="form-control" maxlength="16" autocomplete="off" value="<?php echo htmlspecialchars($_GET['access_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    
    <!-- Vorname -->
    <div class="mb-3">
        <label for="first_name" class="form-label">Vorname</label>
        <input type="text" id="first_name" name="first_name" class="form-control">
    </div>
    
    <!-- Nachname -->
    <div class="mb-3">
        <label for="last_name" class="form-label">Nachname</label>
        <input type="text" id="last_name" name="last_name" class="form-control">
    </div>
    
    <!-- Firma -->
    <div class="mb-3">
        <label for="company" class="form-label">Firma (optional)</label>
        <input type="text" id="company" name="company" class="form-control">
    </div>
    
    <!-- Besuchsgrund -->
    <div class="mb-3">
        <label for="visit_reason" class="form-label">Grund des Besuchs</label>
        <textarea id="visit_reason" name="visit_reason" class="form-control" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Check-In</button>
</form>

<!-- Abstand hinzufügen -->
<div class="my-5"></div>

<h3>Aktuell eingecheckte Besucher</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Besuch Nummer</th>
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
                    <td><?php echo htmlspecialchars($visit['first_name'] . ' ' . $visit['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($visit['company'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($visit['visit_reason']); ?></td>
                    <td><?php echo date('d.m.Y H:i', strtotime($visit['checkin_time'])); ?></td>
                    <td>
                        <!-- Check-Out via Visit ID -->
                        <?php if(isset($visit['visit_id'])): ?>
                            <form method="POST" action="index.php" style="display:inline;">
                                <input type="hidden" name="action" value="checkout">
                                <input type="hidden" name="visit_id" value="<?php echo htmlspecialchars($visit['visit_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                            <button type="submit" class="btn btn-success btn-sm">Check-Out</button>
                            <?php if (isset($_SESSION['user']) && !empty($availableKeys)): ?><button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#issueKeyModal" data-visit-id="<?= (int) $visit['visit_id'] ?>">Schlüssel ausgeben</button><?php endif; ?>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">Keine aktuell eingecheckten Besucher.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (isset($_SESSION['user']) && !empty($availableKeys)): ?>
<div class="modal fade" id="issueKeyModal" tabindex="-1" aria-labelledby="issueKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="issueKeyModalLabel">Schlüssel ausgeben</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button></div>
        <div class="modal-body"><form method="POST" action="index.php" id="issueKeyForm">
            <input type="hidden" name="action" value="issue_key"><input type="hidden" name="visit_id" id="issueKeyVisitId"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <label class="form-label" for="issueKeySelect">Schlüssel auswählen</label><select class="form-select" id="issueKeySelect" name="key_id" required><option value="">Bitte auswählen</option><?php foreach ($availableKeys as $key): ?><option value="<?= (int) $key['id'] ?>"><?= htmlspecialchars($key['key_number'] . ' – ' . $key['label'] . (!empty($key['location_name']) ? ' (' . $key['location_name'] . ')' : ' (zentral)'), ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
            <button type="submit" class="btn btn-primary mt-3">Ausgabe bestätigen</button>
        </form></div>
    </div></div>
</div>
<script>document.getElementById('issueKeyModal').addEventListener('show.bs.modal', function (event) { document.getElementById('issueKeyVisitId').value = event.relatedTarget.getAttribute('data-visit-id'); });</script>
<?php endif; ?>

<?php include __DIR__ . '/../template/footer.php'; ?>
