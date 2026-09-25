<?php include __DIR__ . '/../template/header.php'; ?>

<h2>Druckjobs</h2>
<table class="table table-striped"><thead><tr><th>ID</th><th>Zeit</th><th>Ausgabe</th><th>Drucker</th><th>Format</th><th>Status</th><th>Fehler</th><th>Aktion</th></tr></thead><tbody>
<?php foreach ($jobs as $job): ?><tr><td><?= (int) $job['id'] ?></td><td><?= htmlspecialchars($job['created_at'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($job['output_type'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($job['printer_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($job['rendered_format'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($job['status'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($job['error_message'] ?? '', ENT_QUOTES, 'UTF-8') ?></td><td><?php if ($job['status'] === 'failed'): ?><form method="POST" action="print_jobs.php?action=retry"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="id" value="<?= (int) $job['id'] ?>"><button class="btn btn-sm btn-outline-primary" type="submit">Erneut senden</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table>

<?php include __DIR__ . '/../template/footer.php'; ?>
