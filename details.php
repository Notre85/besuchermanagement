<?php
// Datei: details.php

// Autoload von Composer einbinden
require_once __DIR__ . '/vendor/autoload.php';

// Einbinden von db.php, um die Datenbankverbindung und Umgebungsvariablen zu laden
require_once __DIR__ . '/config/db.php';

// Start der Sitzung, falls noch nicht gestartet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Überprüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user'])) {
    die("<p>Zugriff verweigert. Bitte <a href='login.php'>melden</a> Sie sich an.</p>");
}

// Zugriff auf den aktuellen Benutzer (optional, falls benötigt)
$currentUser = $_SESSION['user'];

// Sicherstellen, dass `visitor_id` vorhanden und gültig ist
if (isset($_GET['visitor_id']) && is_numeric($_GET['visitor_id'])) {
    $visitor_id = (int) $_GET['visitor_id'];
} else {
    die("<p>Ungültige oder fehlende Besucher-ID.</p>");
}

try {
    // Besucher-Details abfragen
    $visitor_query = $pdo->prepare("SELECT * FROM visitors WHERE id = ?");
    $visitor_query->execute([$visitor_id]);
    $visitor = $visitor_query->fetch(PDO::FETCH_ASSOC);

    if (!$visitor) {
        die("<p>Besucher nicht gefunden.</p>");
    }

    // Besuchshistorie des Besuchers abfragen
    $visits_query = $pdo->prepare("SELECT * FROM visits WHERE visitor_id = ? ORDER BY checkin_time DESC");
    $visits_query->execute([$visitor_id]);
    $visits = $visits_query->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Fehlerprotokollierung (optional)
    // $logger->error("Datenbankfehler: " . $e->getMessage());

    // Generische Fehlermeldung für den Benutzer
    die("<p>Datenbankfehler: Bitte versuchen Sie es später erneut.</p>");
}
?>

<center>
<h2>Details von <strong><?= htmlspecialchars($visitor['first_name']) . " " . htmlspecialchars($visitor['last_name']) ?></strong></h2>
</center>
<p><strong>Firma:</strong> <?= htmlspecialchars($visitor['company'] ?: 'Keine Firma') ?><br><strong>Visitor ID:</strong> <?= htmlspecialchars($visitor_id) ?></p>
<p><strong>Besuchshistorie:</strong></p>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Grund des Besuchs</th>
            <th>Check-In Zeit</th>
            <th>Check-Out Zeit</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($visits)): ?>
            <tr>
                <td colspan="3">Keine Besuche gefunden.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($visits as $visit): ?>
                <tr>
                    <td><?= htmlspecialchars($visit['visit_reason']) ?></td>
                    <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($visit['checkin_time']))) ?></td>
                    <td><?= htmlspecialchars($visit['checkout_time'] ? date('d.m.Y H:i', strtotime($visit['checkout_time'])) : 'Noch nicht ausgecheckt') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
