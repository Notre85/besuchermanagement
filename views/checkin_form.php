<!-- views/checkin_form.php -->
<h1>Besucher-Check-In</h1>
<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <label for="first_name">Vorname:</label>
    <input type="text" name="first_name" required>
    <label for="last_name">Nachname:</label>
    <input type="text" name="last_name" required>
    <label for="company">Firma:</label>
    <input type="text" name="company">
    <label for="visit_reason">Besuchsgrund:</label>
    <textarea name="visit_reason" required></textarea>
    <button type="submit">Einchecken</button>
</form>

<h2>Eingecheckte Besucher</h2>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Firma</th>
            <th>Besuchsgrund</th>
            <th>Check-In Zeit</th>
            <th>Aktionen</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($checkedInVisitors as $visitor): ?>
            <tr>
                <td><?= htmlspecialchars($visitor['first_name'] . ' ' . $visitor['last_name']) ?></td>
                <td><?= htmlspecialchars($visitor['company']) ?></td>
                <td><?= htmlspecialchars($visitor['visit_reason']) ?></td>
                <td><?= htmlspecialchars($visitor['checkin_time']) ?></td>
                <td>
                    <a href="print_badge.php?id=<?= $visitor['id'] ?>">Ausweis drucken</a>
                    <!-- Weitere Aktionen -->
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
