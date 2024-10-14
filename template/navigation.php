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
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <?php if (file_exists(__DIR__ . '/../assets/images/logo.png')): ?>
                <img src="assets/images/logo.png" width="30" height="30" class="d-inline-block align-top" alt="Logo">
            <?php endif; ?>
            Besuchermanagement
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if ($currentUser): ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if (in_array($currentUser['role'], ['Berichtersteller', 'Manager', 'Admin', 'Superadmin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                    <?php endif; ?>

                    <?php if (in_array($currentUser['role'], ['Admin', 'Superadmin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="benutzer_verwaltung.php">Benutzerverwaltung</a>
                        </li>
                    <?php if (in_array($currentUser['role'], ['Manager', 'Admin', 'Superadmin'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="visitor_management.php">Besucherverwaltung</a>
                        </li>
                    <?php endif; ?>
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
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?> (<?php echo htmlspecialchars($currentUser['role']); ?>)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
                    </li>
                </ul>
            <?php else: ?>
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a href="login.php" class="btn btn-outline-primary">Login</a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
