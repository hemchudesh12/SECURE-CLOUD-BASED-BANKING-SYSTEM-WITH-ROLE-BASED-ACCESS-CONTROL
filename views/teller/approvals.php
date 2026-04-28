<!-- Teller Approvals -->
<div class="page-header">
  <h1><i class="bi bi-check2-square me-2 text-gold"></i>Pending Approvals</h1>
  <?php if (!empty($pendingTxns)): ?>
  <span class="badge bg-danger fs-6"><?= count($pendingTxns) ?> pending</span>
  <?php endif; ?>
</div>

<?php if (empty($pendingTxns)): ?>
<div class="card fade-in-up">
  <div class="card-body text-center py-5">
    <div style="font-size:4rem">✅</div>
    <h4 class="mt-3 text-success">All Clear!</h4>
    <p class="text-muted">No pending transactions require approval.</p>
  </div>
</div>
<?php else: ?>

<div class="approval-banner mb-4 fade-in-up">
  <i class="bi bi-exclamation-triangle me-2"></i>
  <strong>Note:</strong> You cannot approve transactions that you initiated. All approval actions are audit-logged.
</div>

<?php foreach ($pendingTxns as $txn):
  $canApprove = (int)$txn['initiated_by'] !== (int)Session::get('user_id');
?>
<div class="card mb-3 fade-in-up <?= !$canApprove ? 'opacity-75' : '' ?>"
     style="border-left: 4px solid <?= $canApprove ? 'var(--gold)' : 'var(--text-muted)' ?>">
  <div class="card-body">
    <div class="row align-items-center g-3">
      <div class="col-md-8">
        <div class="d-flex align-items-center gap-3 mb-2">
          <span class="badge status-pending fs-6">⏳ Pending</span>
          <code style="font-size:0.85rem"><?= htmlspecialchars($txn['reference_number']) ?></code>
        </div>
        <div class="row g-2" style="font-size:0.875rem">
          <div class="col-sm-3">
            <small class="text-muted d-block">Amount</small>
            <strong style="font-size:1.2rem;color:var(--navy)">₹<?= number_format((float)$txn['amount'], 2) ?></strong>
          </div>
          <div class="col-sm-3">
            <small class="text-muted d-block">From Account</small>
            <span><?= htmlspecialchars($txn['from_acc'] ?? '—') ?></span>
          </div>
          <div class="col-sm-3">
            <small class="text-muted d-block">To Account</small>
            <span><?= htmlspecialchars($txn['to_acc'] ?? '—') ?></span>
          </div>
          <div class="col-sm-3">
            <small class="text-muted d-block">Initiated By</small>
            <span><?= htmlspecialchars($txn['initiator_name'] ?? '—') ?></span>
          </div>
          <div class="col-12">
            <small class="text-muted d-block">Submitted</small>
            <span><?= date('d M Y, h:i A', strtotime($txn['created_at'])) ?></span>
          </div>
        </div>
        <?php if (!$canApprove): ?>
          <div class="mt-2 text-muted" style="font-size:0.8rem">
            <i class="bi bi-lock me-1"></i>You initiated this transaction and cannot approve it.
          </div>
        <?php endif; ?>
      </div>
      <div class="col-md-4 text-end">
        <?php if ($canApprove): ?>
        <div class="d-flex gap-2 justify-content-end flex-wrap">
          <form method="POST" action="/banking-system/public/teller/approve/<?= $txn['id'] ?>" class="d-inline">
            <?= CsrfMiddleware::field() ?>
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="btn btn-success btn-sm"
                    onclick="return confirm('Approve this transfer of ₹<?= number_format((float)$txn['amount'],2) ?>?')">
              <i class="bi bi-check-circle me-1"></i>Approve
            </button>
          </form>
          <form method="POST" action="/banking-system/public/teller/approve/<?= $txn['id'] ?>" class="d-inline">
            <?= CsrfMiddleware::field() ?>
            <input type="hidden" name="action" value="reject">
            <button type="submit" class="btn btn-outline-danger btn-sm"
                    onclick="return confirm('Reject this transaction?')">
              <i class="bi bi-x-circle me-1"></i>Reject
            </button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
