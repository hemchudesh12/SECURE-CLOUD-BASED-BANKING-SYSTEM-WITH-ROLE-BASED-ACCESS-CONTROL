<!-- Integrity Check -->
<div class="page-header">
  <h1><i class="bi bi-shield-check me-2 text-gold"></i>Audit Log Integrity Check</h1>
</div>

<div class="card fade-in-up" style="max-width:600px">
  <div class="card-header"><i class="bi bi-shield-lock"></i> Cryptographic Chain Verification</div>
  <div class="card-body text-center py-5">
    <?php if ($result['valid']): ?>
      <div style="font-size:5rem">✅</div>
      <h3 class="mt-3 text-success">Chain Intact</h3>
      <p class="text-muted">All audit log entries have been verified.<br>
        No tampering or deletion detected.</p>
      <span class="badge status-completed" style="font-size:0.9rem;padding:0.5rem 1rem">
        SHA-256 chain verified
      </span>
    <?php else: ?>
      <div style="font-size:5rem">🚨</div>
      <h3 class="mt-3 text-danger">Chain BROKEN</h3>
      <p class="text-muted">Integrity violation detected at entry ID:
        <strong class="text-danger">#<?= $result['broken_at'] ?></strong>
      </p>
      <div class="alert alert-danger mt-3 text-start">
        <strong>Action Required:</strong> The audit log has been tampered with or entries have been deleted.
        Investigate immediately and restore from backup.
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="card mt-4 fade-in-up" style="max-width:600px">
  <div class="card-header"><i class="bi bi-info-circle"></i> How Chain Integrity Works</div>
  <div class="card-body" style="font-size:0.875rem">
    <p>Each audit log entry contains:</p>
    <ul>
      <li><strong>entry_hash</strong>: SHA-256 of this entry's content including the previous hash</li>
      <li><strong>previous_hash</strong>: SHA-256 of the preceding entry</li>
    </ul>
    <p class="mb-0">If any entry is modified or deleted, all subsequent hashes become invalid, making tampering detectable.</p>
  </div>
</div>
