<?php $assetBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/'); ?>
<?php $kioskFlash = $_SESSION['kiosk_flash'] ?? null; unset($_SESSION['kiosk_flash']); ?>
<?php
$kioskErrorMessages = [
    'choose_one' => 'Bitte entweder eine Besucher-ID oder eine neue Besuchererfassung verwenden.',
    'invalid_identifier' => 'Bitte eine Besucher-ID (z. B. 123) oder Termin-ID mit T (z. B. T123) eingeben.',
    'planned_visit_not_found' => 'Die Termin-ID wurde nicht gefunden, ist abgelaufen oder bereits verwendet.',
    'invalid_access_code' => 'Der angegebene Zugangscode ist ungültig.',
    'invalid_input' => 'Bitte prüfen Sie die Eingaben.',
    'visit_reason_required' => 'Bitte geben Sie einen Besuchsgrund an.',
    'required_fields_missing' => 'Bitte Vorname, Nachname und Besuchsgrund ausfüllen.',
    'visitor_not_found' => 'Die Besucher-ID wurde nicht gefunden.',
    'visitor_creation_failed' => 'Der Besucher konnte nicht angelegt werden.',
    'already_checked_in' => 'Dieser Besucher ist bereits eingecheckt.',
    'invalid_visit_id' => 'Der Besuch ist nicht mehr aktiv oder wurde bereits beendet.',
    'not_checked_in' => 'Dieser Besucher ist derzeit nicht eingecheckt.',
    'missing_parameters' => 'Für die Abmeldung fehlen Angaben.',
    'checkout_failed' => 'Der Check-out konnte nicht gespeichert werden.',
    'key_issue_invalid' => 'Die Schlüsselausgabe konnte nicht verarbeitet werden.',
    'key_already_issued' => 'Für diesen Besuch wurde bereits ein Schlüssel ausgegeben.',
    'key_unavailable' => 'Der ausgewählte Schlüssel ist nicht mehr verfügbar.',
];
$kioskErrorCode = (string) ($kioskFlash['code'] ?? '');
$kioskErrorMessage = $kioskErrorMessages[$kioskErrorCode] ?? 'Die Aktion konnte nicht verarbeitet werden.';
?>
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
        <?php if (($kioskFlash['type'] ?? '') === 'success' && ($kioskFlash['code'] ?? '') === 'checkin'): ?><div class="kiosk-alert kiosk-alert-success">Check-in erfolgreich.</div><?php elseif (($kioskFlash['type'] ?? '') === 'success' && ($kioskFlash['code'] ?? '') === 'checkout'): ?><div class="kiosk-alert kiosk-alert-success">Check-out erfolgreich.</div><?php elseif (($kioskFlash['type'] ?? '') === 'success' && ($kioskFlash['code'] ?? '') === 'key_issued'): ?><div class="kiosk-alert kiosk-alert-success">Schlüssel wurde ausgegeben.</div><?php elseif (($kioskFlash['type'] ?? '') === 'error'): ?><div class="kiosk-alert kiosk-alert-error"><?= htmlspecialchars($kioskErrorMessage, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <div class="kiosk-layout"><div class="kiosk-main-column">
        <form method="POST" action="kiosk.php" class="kiosk-form">
            <input type="hidden" name="kiosk" value="1"><input type="hidden" name="action" value="checkin"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="kiosk-field"><label for="checkin_identifier">Besucher-ID oder Termin-ID</label><input class="kiosk-input kiosk-input-large" type="text" id="checkin_identifier" name="checkin_identifier" inputmode="text" autocomplete="off" placeholder="z. B. 123 oder T123"></div>
            <div class="kiosk-divider"><span>oder neu erfassen</span></div>
            <div class="kiosk-grid"><div class="kiosk-field"><label for="first_name">Vorname</label><input class="kiosk-input kiosk-input-large" id="first_name" name="first_name" maxlength="50"></div><div class="kiosk-field"><label for="last_name">Nachname</label><input class="kiosk-input kiosk-input-large" id="last_name" name="last_name" maxlength="50"></div></div>
            <div class="kiosk-field"><label for="company">Firma</label><input class="kiosk-input kiosk-input-large" id="company" name="company" maxlength="100"></div>
            <div class="kiosk-field"><label for="visit_reason">Besuchsgrund</label><textarea class="kiosk-input" id="visit_reason" name="visit_reason" maxlength="500" rows="3" required></textarea></div>
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
document.querySelectorAll('form[method="POST"]').forEach(function (form) {
    form.addEventListener('submit', async function (event) {
        if (form.dataset.csrfReady === '1') {
            delete form.dataset.csrfReady;
            return;
        }
        event.preventDefault();
        var submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) submitButton.disabled = true;
        try {
            var response = await fetch('kiosk.php?action=csrf_token', {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Token konnte nicht abgerufen werden.');
            var tokenData = await response.json();
            var tokenInput = form.querySelector('input[name="csrf_token"]');
            if (!tokenInput || !tokenData.csrf_token) throw new Error('Ungültige Tokenantwort.');
            tokenInput.value = tokenData.csrf_token;
            form.dataset.csrfReady = '1';
            HTMLFormElement.prototype.submit.call(form);
        } catch (error) {
            var errorMessage = document.querySelector('.kiosk-alert-error');
            if (!errorMessage) {
                errorMessage = document.createElement('div');
                errorMessage.className = 'kiosk-alert kiosk-alert-error';
                form.prepend(errorMessage);
            }
            errorMessage.textContent = 'Die Sitzung konnte nicht aktualisiert werden. Bitte versuchen Sie es erneut.';
        } finally {
            if (submitButton && form.dataset.csrfReady !== '1') submitButton.disabled = false;
        }
    });
});
</script>
</body>
</html>
