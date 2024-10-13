<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Bericht erstellen</h2>

<?php if (isset($_GET['success']) && $_GET['success'] === 'backup'): ?>
    <div class="alert alert-success">Backup erfolgreich erstellt!</div>
<?php endif; ?>

<form method="POST" action="report.php">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    
    <div class="form-group">
        <label for="timeframe">Zeitraum auswählen</label>
        <select id="timeframe" name="timeframe" class="form-control">
            <option value="today">Heute</option>
            <option value="week">Diese Woche</option>
            <option value="month" selected>Dieser Monat</option>
            <option value="year">Dieses Jahr</option>
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary">Bericht erstellen</button>
</form>

<?php include __DIR__ . '/../template/footer.php'; ?>
