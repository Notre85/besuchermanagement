<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Besuchermanagement-System</title>
    <link rel="stylesheet" href="template/css/styles.css">
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
    <script src="assets/bootstrap/bootstrap.bundle.min.js" defer></script>
    <!-- Haupt-JavaScript-Datei -->
    <script src="js/main.js" defer></script>
</head>
<body>

    <!-- Header-Bereich für Logo und Firmennamen -->
    <header class="text-center py-0">
        <div class="container d-flex justify-content-center align-items-center">
            <?php if (file_exists(__DIR__ . '/../assets/images/logo.svg')): ?>
                 <img src="assets/images/logo.svg" alt="Firmenlogo" style="max-width: 50px; margin-right: 15px;">
            <?php endif; ?>
            <h1 class="mb-0">Integrierte Leitstelle Mittelfranken S&uumld</h1>
        </div>
    </header>

    <!-- Navigation -->
    <?php include __DIR__ . '/navigation.php'; ?>

    <!-- Hauptinhalt -->
    <div class="container mt-4">
