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

<h3>Aktuell eingecheckte Besucher</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Firma</th>
            <th>Grund</th>
            <th>Check-In</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($currentVisits as $visit): ?>
            <tr>
                <td><?php echo htmlspecialchars($visit['first_name'] . ' ' . $visit['last_name']); ?></td>
                <td><?php echo htmlspecialchars($visit['company'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($visit['visit_reason']); ?></td>
                <td><?php echo date('d.m.Y H:i', strtotime($visit['checkin_time'])); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../template/footer.php'; ?>
