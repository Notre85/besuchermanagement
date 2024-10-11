<!-- views/admin/login.php -->
<h1>Admin Login</h1>
<?php if (isset($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form method="POST" action="">
    <label for="username">Benutzername:</label>
    <input type="text" name="username" required>
    <label for="password">Passwort:</label>
    <input type="password" name="password" required>
    <button type="submit">Anmelden</button>
</form>
