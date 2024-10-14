<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Bericht erstellen</h2>

<form method="POST" action="report.php">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

    <!-- Berichtstyp auswählen -->
    <div class="row">
        <div class="col-md-6">
            <label for="report_type">Berichtstyp ausw&aumlhlen</label>
            <select id="report_type" name="report_type" class="form-control">
                <option value="time">Zeitraum</option>
                <option value="visitor">Besucherbericht</option>
                <option value="company">Firmenbericht</option>
            </select>
        </div>

        <!-- Filter für Besucher oder Firma -->
        <div class="col-md-6" id="filterField" style="display:none;">
            <label for="filter">Filter (Besuchername, Visitor-ID oder Firma)</label>
            <input type="text" id="filter" name="filter" class="form-control">
        </div>
    </div>

    <!-- Zeitraum auswählen -->
    <div class="row">
        <div class="col-md-6">
            <label for="start_date">Startdatum</label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo date('Y-m-01'); ?>">
        </div>

        <div class="col-md-6">
            <label for="end_date">Enddatum</label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
        </div>
    </div>
    
    <button type="submit" name="generate_report" class="btn btn-primary mt-3">Bericht erstellen</button>
    <button type="submit" name="generate_pdf" class="btn btn-secondary mt-3">PDF generieren</button>
</form>

<script>
document.getElementById('report_type').addEventListener('change', function() {
    var filterField = document.getElementById('filterField');
    if (this.value === 'visitor' || this.value === 'company') {
        filterField.style.display = 'block';
    } else {
        filterField.style.display = 'none';
    }
});
</script>

<!-- Berichtsausgabe -->
<div class="report-output mt-5">
    <h3>Berichtsergebnisse</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Besuch Nr.</th>
                <th>Name</th>
                <th>Firma</th>
                <th>Besuchsgrund</th>
                <th>Check-In</th>
                <th>Check-Out</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($visits) && count($visits) > 0): ?>
                <?php foreach ($visits as $visit): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($visit['id']); ?></td>
                        <td><?php echo htmlspecialchars($visit['first_name'] . ' ' . $visit['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($visit['company']); ?></td>
                        <td><?php echo htmlspecialchars($visit['visit_reason']); ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($visit['checkin_time'])); ?></td>
                        <td><?php echo $visit['checkout_time'] ? date('d.m.Y H:i', strtotime($visit['checkout_time'])) : 'Noch nicht ausgecheckt'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Noch keine Berichte verf%uumlgbar</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
