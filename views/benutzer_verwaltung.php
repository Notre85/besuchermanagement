<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Benutzerverwaltung</h2>

<?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] === 'create'): ?>
        <div class="alert alert-success">Benutzer erfolgreich erstellt!</div>
    <?php elseif ($_GET['success'] === 'update'): ?>
        <div class="alert alert-success">Benutzer erfolgreich aktualisiert!</div>
    <?php elseif ($_GET['success'] === 'delete'): ?>
        <div class="alert alert-success">Benutzer erfolgreich gelöscht!</div>
    <?php endif; ?>
<?php endif; ?>

<h3>Neuen Benutzer hinzufügen</h3>
<form method="POST" action="benutzer_verwaltung.php?action=create">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    
    <div class="form-group">
        <label for="username">Benutzername</label>
        <input type="text" id="username" name="username" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label for="password">Passwort</label>
        <input type="password" id="password" name="password" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label for="first_name">Vorname</label>
        <input type="text" id="first_name" name="first_name" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label for="last_name">Nachname</label>
        <input type="text" id="last_name" name="last_name" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label for="role">Rolle</label>
        <select id="role" name="role" class="form-control">
            <option value="Berichtersteller">Berichtersteller</option>
            <option value="Manager">Manager</option>
            <option value="Admin">Admin</option>
            <option value="Superadmin">Superadmin</option>
        </select>
    </div>
    
    <button type="submit" class="btn btn-success">Benutzer hinzufügen</button>
</form>

<h3>Bestehende Benutzer</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Benutzername</th>
            <th>Vorname</th>
            <th>Nachname</th>
            <th>Rolle</th>
            <th>Aktionen</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <form method="POST" action="benutzer_verwaltung.php?action=update">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                    <td><?php echo $user['id']; ?></td>
                    <td>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="form-control" required>
                    </td>
                    <td>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="form-control" required>
                    </td>
                    <td>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="form-control" required>
                    </td>
                    <td>
                        <select name="role" class="form-control">
                            <option value="Berichtersteller" <?php echo $user['role'] === 'Berichtersteller' ? 'selected' : ''; ?>>Berichtersteller</option>
                            <option value="Manager" <?php echo $user['role'] === 'Manager' ? 'selected' : ''; ?>>Manager</option>
                            <option value="Admin" <?php echo $user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="Superadmin" <?php echo $user['role'] === 'Superadmin' ? 'selected' : ''; ?>>Superadmin</option>
                        </select>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-primary btn-sm">Aktualisieren</button>
                </form>
                        <form method="POST" action="benutzer_verwaltung.php?action=delete" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Löschen</button>
                        </form>
                    </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../template/footer.php'; ?>
