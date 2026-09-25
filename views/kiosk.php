<?php $assetBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/'); ?>
<?php $kioskFlash = $_SESSION['kiosk_flash'] ?? null; unset($_SESSION['kiosk_flash']); ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Besucher anmelden</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(($assetBase ?: '') . '/template/css/kiosk.css', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="kiosk-page">
<main class="kiosk-container">
    <div class="kiosk-brand">
        <?php if (file_exists(__DIR__ . '/../assets/images/logo.svg')): ?><img src="<?= htmlspecialchars(($assetBase ?: '') . '/assets/images/logo.svg', ENT_QUOTES, 'UTF-8') ?>" alt="Firmenlogo"><?php endif; ?>
        <span>Besuchermanagement</span>
    </div>
    <section class="kiosk-card" aria-label="Besucher Check-in"><div class="kiosk-card-body">
        <?php if (($kioskFlash['type'] ?? '') === 'success' && ($kioskFlash['code'] ?? '') === 'checkin'): ?><div class="kiosk-alert kiosk-alert-success">Check-in erfolgreich.</div><?php elseif (($kioskFlash['type'] ?? '') === 'success' && ($kioskFlash['code'] ?? '') === 'checkout'): ?><div class="kiosk-alert kiosk-alert-success">Check-out erfolgreich.</div><?php elseif (($kioskFlash['type'] ?? '') === 'success' && ($kioskFlash['code'] ?? '') === 'key_issued'): ?><div class="kiosk-alert kiosk-alert-success">Schlüssel wurde ausgegeben.</div><?php elseif (($kioskFlash['type'] ?? '') === 'error'): ?><div class="kiosk-alert kiosk-alert-error">Die Anmeldung, Abmeldung oder Schlüsselausgabe konnte nicht verarbeitet werden.</div><?php endif; ?>
        <div class="kiosk-layout"><div class="kiosk-main-column">
        <form method="POST" action="kiosk.php" class="kiosk-form">
            <input type="hidden" name="kiosk" value="1"><input type="hidden" name="action" value="checkin"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="kiosk-field"><label for="visitor_id">Besucher-ID</label><input class="kiosk-input kiosk-input-large" type="number" min="1" id="visitor_id" name="visitor_id" inputmode="numeric"></div>
            <div class="kiosk-divider"><span>oder neu erfassen</span></div>
            <div class="kiosk-grid"><div class="kiosk-field"><label for="first_name">Vorname</label><input class="kiosk-input kiosk-input-large" id="first_name" name="first_name" maxlength="50"></div><div class="kiosk-field"><label for="last_name">Nachname</label><input class="kiosk-input kiosk-input-large" id="last_name" name="last_name" maxlength="50"></div></div>
            <div class="kiosk-field"><label for="company">Firma</label><input class="kiosk-input kiosk-input-large" id="company" name="company" maxlength="100"></div>
            <div class="kiosk-field"><label for="visit_reason">Besuchsgrund</label><textarea class="kiosk-input" id="visit_reason" name="visit_reason" maxlength="500" rows="3"></textarea></div>
            <button class="kiosk-button kiosk-button-primary" type="submit">Check-in starten</button>
        </form>
        </div><div class="kiosk-side-column">
        <div class="kiosk-divider"><span>Aktuell eingecheckte Besucher</span></div>
        <?php if (empty($currentVisits ?? [])): ?>
            <p class="kiosk-empty">Derzeit sind keine Besucher eingecheckt.</p>
        <?php else: ?>
            <div class="kiosk-visitor-list">
                <?php foreach ($currentVisits as $currentVisit): ?>
                    <div class="kiosk-visitor-row">
                        <div><strong><?= htmlspecialchars($currentVisit['first_name'] . ' ' . $currentVisit['last_name'], ENT_QUOTES, 'UTF-8') ?></strong><span>Besuchsnummer <?= (int) $currentVisit['visit_id'] ?> · Besucher-ID <?= (int) $currentVisit['visitor_id'] ?></span></div>
                        <form method="POST" action="kiosk.php">
                            <input type="hidden" name="kiosk" value="1"><input type="hidden" name="action" value="checkout"><input type="hidden" name="visit_id" value="<?= (int) $currentVisit['visit_id'] ?>"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button class="kiosk-button kiosk-button-secondary kiosk-button-small" type="submit">Check-out</button>
                        </form>
                        <?php if (isset($_SESSION['user']) && !empty($availableKeys ?? [])): ?><button class="kiosk-button kiosk-button-primary kiosk-button-small kiosk-issue-key-button" type="button" data-visit-id="<?= (int) $currentVisit['visit_id'] ?>">Schlüssel</button><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['user']) && !empty($availableKeys ?? [])): ?>
        <div class="kiosk-modal" id="issueKeyModal" hidden><div class="kiosk-modal-backdrop" data-close-issue-key></div><div class="kiosk-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="issueKeyModalTitle"><button type="button" class="kiosk-modal-close" data-close-issue-key aria-label="Schließen">×</button><h2 id="issueKeyModalTitle">Schlüssel ausgeben</h2><form method="POST" action="kiosk.php"><input type="hidden" name="kiosk" value="1"><input type="hidden" name="action" value="issue_key"><input type="hidden" name="visit_id" id="kioskIssueKeyVisitId"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><label class="kiosk-modal-label" for="kioskIssueKeySelect">Schlüssel auswählen</label><select class="kiosk-input kiosk-input-large" id="kioskIssueKeySelect" name="key_id" required><option value="">Bitte auswählen</option><?php foreach ($availableKeys as $key): ?><option value="<?= (int) $key['id'] ?>"><?= htmlspecialchars($key['key_number'] . ' – ' . $key['label'] . (!empty($key['location_name']) ? ' (' . $key['location_name'] . ')' : ' (zentral)'), ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select><button class="kiosk-button kiosk-button-primary kiosk-modal-submit" type="submit">Ausgabe bestätigen</button></form></div></div>
        <?php endif; ?>
        </div></div>
    </div></section>
    <p class="kiosk-footer">Bitte wenden Sie sich bei Fragen an den Empfang.</p>
</main>
<script>
document.querySelectorAll('.kiosk-alert').forEach(function (message) {
    window.setTimeout(function () {
        message.classList.add('kiosk-alert-hidden');
        window.setTimeout(function () { message.remove(); }, 350);
    }, 5000);
});
var issueKeyModal = document.getElementById('issueKeyModal');
document.querySelectorAll('.kiosk-issue-key-button').forEach(function (button) {
    button.addEventListener('click', function () { document.getElementById('kioskIssueKeyVisitId').value = button.dataset.visitId; issueKeyModal.hidden = false; document.getElementById('kioskIssueKeySelect').focus(); });
});
document.querySelectorAll('[data-close-issue-key]').forEach(function (element) { element.addEventListener('click', function () { issueKeyModal.hidden = true; }); });
</script>
</body>
</html>
