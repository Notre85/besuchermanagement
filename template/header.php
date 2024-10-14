<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Besuchermanagement-System</title>
    <link rel="stylesheet" href="template/css/styles.css">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap 5 JS Bundle (enthält Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <!-- Haupt-JavaScript-Datei -->
    <script src="js/main.js" defer></script>
</head>
<body>

    <!-- Header-Bereich für Logo und Firmennamen -->
    <header class="text-center py-3">
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
