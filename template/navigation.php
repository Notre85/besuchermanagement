<?php
// template/navigation.php
session_start();
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../config/logger.php';
require_once __DIR__ . '/../config/db.php';

use App\User;

// Benutzerinformationen laden, wenn eingeloggt
$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">
        <?php if (file_exists(__DIR__ . '/../assets/images/logo.png')): ?>
            <img src="assets/images/logo.png" width="30" height="30" class="d-inline-block align-top" alt="">
        <?php endif; ?>
        Besuchermanagement
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <?php if ($currentUser): ?>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <?php if (in_array($currentUser['role'], ['Berichtersteller', 'Manager', 'Admin', 'Superadmin'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                <?php endif; ?>

                <?php if (in_array($currentUser['role'], ['Admin', 'Superadmin'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="benutzer_verwaltung.php">Benutzerverwaltung</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="drucker_verwaltung.php">Druckerverwaltung</a>
                    </li>
                <?php endif; ?>

                <?php if (in_array($currentUser['role'], ['Berichtersteller', 'Manager', 'Admin', 'Superadmin'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="report.php">Berichte</a>
                    </li>
                <?php endif; ?>
            </ul>
            <span class="navbar-text mr-3">
                <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?> (<?php echo $currentUser['role']; ?>)
            </span>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    <?php else: ?>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <!-- Weitere Links für nicht eingeloggte Benutzer -->
            </ul>
            <a href="login.php" class="btn btn-outline-primary">Login</a>
        </div>
    <?php endif; ?>
</nav>
