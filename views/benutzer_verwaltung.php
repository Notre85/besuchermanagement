<?php
// views/benutzer_verwaltung.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Einbinden der notwendigen Dateien
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/logger.php';
require_once __DIR__ . '/../config/csrf.php';

use App\Controllers\BenutzerController;

// Start der Sitzung, falls noch nicht gestartet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logger initialisieren
$logger = get_logger();

// Instanziieren des BenutzerControllers
$benutzerController = new BenutzerController($pdo, $logger);

// Verarbeiten der POST-Anfragen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Überprüfen, ob 'action' gesetzt ist
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        
        switch ($action) {
            case 'createUser':
                $benutzerController->createUser();
                break;
            
            case 'updateUser':
                $benutzerController->updateUser();
                break;
            
            case 'deleteUser':
                $benutzerController->deleteUser();
                break;
            
            default:
                // Unbekannte Aktion
                $logger->warning("Unbekannte Aktion: $action");
                header("Location: benutzer_verwaltung.php?error=unknown_action");
                exit();
        }
    }
}

// Anzeigen der Benutzerverwaltung (GET-Anfrage oder nach der Verarbeitung)
$benutzerController->showUserManagement();
?>

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

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?php
            switch ($_GET['error']) {
                case 'csrf_invalid':
                    echo 'Ungültiges CSRF-Token.';
                    break;
                case 'required_fields_missing':
                    echo 'Alle erforderlichen Felder müssen ausgefüllt sein.';
                    break;
                case 'user_creation_failed':
                    echo 'Fehler beim Erstellen des Benutzers.';
                    break;
                case 'user_update_failed':
                    echo 'Fehler beim Aktualisieren des Benutzers.';
                    break;
                case 'user_deletion_failed':
                    echo 'Fehler beim Löschen des Benutzers.';
                    break;
                case 'unknown_action':
                    echo 'Unbekannte Aktion.';
                    break;
                case 'invalid_user_id':
                    echo 'Ungültige Benutzer-ID.';
                    break;
                default:
                    echo 'Ein unbekannter Fehler ist aufgetreten.';
            }
        ?>
    </div>
<?php endif; ?>

<h3>Neuen Benutzer hinzufügen</h3>
<form method="POST" action="benutzer_verwaltung.php?action=createUser">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    
    <div class="form-group mb-3">
        <label for="username">Benutzername</label>
        <input type="text" id="username" name="username" class="form-control" required>
    </div>
    
    <div class="form-group mb-3">
        <label for="password">Passwort</label>
        <input type="password" id="password" name="password" class="form-control" required>
    </div>
    
    <div class="form-group mb-3">
        <label for="first_name">Vorname</label>
        <input type="text" id="first_name" name="first_name" class="form-control" required>
    </div>
    
    <div class="form-group mb-3">
        <label for="last_name">Nachname</label>
        <input type="text" id="last_name" name="last_name" class="form-control" required>
    </div>
    
    <div class="form-group mb-3">
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

<!-- Abstand hinzufügen -->
<div class="my-5"></div>

<h3>Bestehende Benutzer</h3>
<div class="table-responsive">
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
                    <td><?php echo $user['id']; ?></td>
                    <td>
                        <form method="POST" action="benutzer_verwaltung.php?action=updateUser">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
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
                            <input type="password" name="password" class="form-control mb-2" placeholder="Neues Passwort (optional)">
                            <button type="submit" class="btn btn-primary btn-sm mb-1">Aktualisieren</button>
                        </form>
                        
                        <!-- Delete-Formular -->
                        <form method="POST" action="benutzer_verwaltung.php?action=deleteUser" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Sind Sie sicher, dass Sie diesen Benutzer löschen möchten?');">Löschen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
