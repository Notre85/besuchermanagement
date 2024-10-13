<!-- views/visitor_details.php -->

<div class="modal" tabindex="-1" role="dialog" id="visitorDetailsModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Besucherdetails</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Schließen">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php if (isset($visitor)): ?>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($visitor['first_name'] . ' ' . $visitor['last_name']); ?></p>
            <p><strong>Firma:</strong> <?php echo htmlspecialchars($visitor['company'] ?? 'N/A'); ?></p>
            <h5>Besuchshistorie:</h5>
            <ul>
                <?php foreach ($visits as $visit): ?>
                    <li>
                        <?php echo date('d.m.Y H:i', strtotime($visit['checkin_time'])); ?> - 
                        <?php echo $visit['checkout_time'] ? date('d.m.Y H:i', strtotime($visit['checkout_time'])) : 'Noch nicht ausgecheckt'; ?>: 
                        <?php echo htmlspecialchars($visit['visit_reason']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Keine Besucherdaten verfügbar.</p>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Schließen</button>
      </div>
    </div>
  </div>
</div>
