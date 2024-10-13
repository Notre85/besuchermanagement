<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Besucher Check-In</h2>

<?php if (isset($_GET['success']) && $_GET['success'] === 'checkin'): ?>
    <div class="alert alert-success">Check-In erfolgreich!</div>
<?php endif; ?>

<form method="POST" action="checkin.php">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    
    <div class="form-group">
        <label for="visitor_id">Besucher-ID (optional)</label>
        <input type="number" id="visitor_id" name="visitor_id" class="form-control" placeholder="Besucher-ID">
    </div>
    
    <p>Oder</p>
    
    <div class="form-group">
        <label for="first_name">Vorname</label>
        <input type="text" id="first_name" name="first_name" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label for="last_name">Nachname</label>
        <input type="text" id="last_name" name="last_name" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label for="company">Firma (optional)</label>
        <input type="text" id="company" name="company" class="form-control">
    </div>
    
    <div class="form-group">
        <label for="visit_reason">Grund des Besuchs</label>
        <textarea id="visit_reason" name="visit_reason" class="form-control" required></textarea>
    </div>
    
    <button type="submit" class="btn btn-primary">Check-In</button>
</form>

<h3>Aktuell eingecheckte Besucher</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Firma</th>
            <th>Grund</th>
            <th>Check-In</th>
            <th>Aktionen</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Holen der aktuell eingecheckten Besucher
        $currentVisits = (new App\Visit($pdo))->getCurrentVisits();
        foreach ($currentVisits as $visit):
        ?>
            <tr>
                <td><?php echo htmlspecialchars($visit['first_name'] . ' ' . $visit['last_name']); ?></td>
                <td><?php echo htmlspecialchars($visit['company'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($visit['visit_reason']); ?></td>
                <td><?php echo date('d.m.Y H:i', strtotime($visit['checkin_time'])); ?></td>
                <td>
                    <form method="POST" action="checkout.php" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="visit_id" value="<?php echo $visit['id']; ?>">
                        <button type="submit" class="btn btn-success btn-sm">Check-Out</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../template/footer.php'; ?>
