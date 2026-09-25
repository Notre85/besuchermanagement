<?php
// Datei: details.php

// Autoload von Composer einbinden
require_once __DIR__ . '/vendor/autoload.php';

// Einbinden von db.php, um die Datenbankverbindung und Umgebungsvariablen zu laden
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/csrf.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'] ?? '', ['Berichtersteller', 'Manager', 'Admin', 'Superadmin'], true)) {
    http_response_code(403);
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

    $audit = $pdo->prepare(
        'INSERT INTO audit_log (user_id, action, entity_type, entity_id, request_id)
         VALUES (:user_id, :action, :entity_type, :entity_id, :request_id)'
    );
    $audit->execute([
        'user_id' => $_SESSION['user']['id'] ?? null,
        'action' => 'visitor.history.view',
        'entity_type' => 'visitor',
        'entity_id' => $visitor_id,
        'request_id' => bin2hex(random_bytes(16)),
    ]);

} catch (PDOException $e) {
    // Fehlerprotokollierung (optional)
    // $logger->error("Datenbankfehler: " . $e->getMessage());

    // Generische Fehlermeldung für den Benutzer
    die("<p>Datenbankfehler: Bitte versuchen Sie es später erneut.</p>");
}
?>

<center>
<h2>Details von <strong><?= htmlspecialchars($visitor['first_name'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($visitor['last_name'], ENT_QUOTES, 'UTF-8') ?></strong></h2>
</center>
<p><strong>Firma:</strong> <?= htmlspecialchars($visitor['company'] ?: 'Keine Firma', ENT_QUOTES, 'UTF-8') ?><br><strong>Visitor ID:</strong> <?= (int) $visitor_id ?></p>
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
                    <td><?= htmlspecialchars($visit['visit_reason'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($visit['checkin_time'])), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($visit['checkout_time'] ? date('d.m.Y H:i', strtotime($visit['checkout_time'])) : 'Noch nicht ausgecheckt', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
