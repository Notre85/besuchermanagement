<?php
// template/layout.php
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/template/css/styles.css">
</head>
<body>
    <header>
        <!-- Optionales Header-Bild -->
        <?php if (file_exists('template/assets/header.png')): ?>
            <img src="/template/assets/header.png" alt="Header">
        <?php endif; ?>
        <nav>
            <!-- Navigation -->
            <ul>
                <li><a href="/index.php">Startseite</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/admin/dashboard.php">Dashboard</a></li>
                    <li><a href="/admin/logout.php">Abmelden</a></li>
                <?php else: ?>
                    <li><a href="/admin/login.php">Admin Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <?php include $content; ?>
    </main>

    <footer>
        <!-- Optionales Footer-Bild -->
        <?php if (file_exists('template/assets/footer.png')): ?>
            <img src="/template/assets/footer.png" alt="Footer">
        <?php endif; ?>
        <p>&copy; <?= date('Y') ?> Ihr Unternehmen</p>
    </footer>
</body>
</html>
