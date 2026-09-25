<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Login</h2>

<?php if (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
    <div class="alert alert-danger">Ungültiger Benutzername oder Passwort.</div>
<?php endif; ?>

<form method="POST" action="login.php">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    
    <div class="form-group">
        <label for="username">Benutzername</label>
        <input type="text" id="username" name="username" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label for="password">Passwort</label>
        <input type="password" id="password" name="password" class="form-control" required>
    </div>
    
    <button type="submit" class="btn btn-primary">Login</button>
</form>

<?php include __DIR__ . '/../template/footer.php'; ?>
