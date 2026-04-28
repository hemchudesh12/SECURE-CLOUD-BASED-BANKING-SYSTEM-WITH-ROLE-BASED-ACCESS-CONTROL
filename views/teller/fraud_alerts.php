<?php /** @var array $flags */ ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Fraud Alerts</h1>
    <p class="page-subtitle">Pending flagged transactions requiring review</p>
  </div>
  <span class="badge bg-danger fs-6"><?= count($flags) ?> Pending</span>
</div>

<?php if (empty($flags)): ?>
<div class="card">
  <div class="card-body text-center py-5">
    <i class="bi bi-shield-check display-4 text-success mb-3 d-block"></i>
    <h5>No fraud alerts</h5>
    <p class="text-muted">All transactions are clean. Great!</p>
  </div>
</div>
<?php else: ?>
<div class="card">
  <div class="card-header"><div class="card-title"><i class="bi bi-shield-exclamation me-2"></i>Flagged Transactions</div></div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr><th>Reference</th><th>Amount</th><th>Rules Triggered</th><th>Risk Score</th><th>From → To</th><th>Date</th></tr>
      </thead>
      <tbody>
      <?php foreach ($flags as $f): ?>
      <tr class="flagged-row">
        <td><code><?= htmlspecialchars($f['reference_number']) ?></code></td>
        <td class="fw-semibold">₹<?= number_format((float)$f['amount'], 2) ?></td>
        <td>
          <?php foreach (explode(',', $f['rule_triggered']) as $rule): ?>
          <span class="badge bg-danger me-1" style="font-size:0.7rem"><?= htmlspecialchars(trim($rule)) ?></span>
          <?php endforeach; ?>
        </td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <div class="risk-bar" style="width:80px">
              <div class="risk-bar-fill <?= $f['risk_score']>=70?'risk-high':($f['risk_score']>=40?'risk-medium':'risk-low') ?>"
                   style="width:<?= $f['risk_score'] ?>%"></div>
            </div>
            <span class="fw-semibold"><?= $f['risk_score'] ?>/100</span>
          </div>
        </td>
        <td>
          <span class="text-muted"><?= htmlspecialchars($f['from_acc'] ?? 'N/A') ?></span>
          <i class="bi bi-arrow-right mx-1"></i>
          <span class="text-muted"><?= htmlspecialchars($f['to_acc'] ?? 'N/A') ?></span>
        </td>
        <td class="text-muted" style="font-size:0.78rem"><?= date('d M Y H:i', strtotime($f['txn_date'])) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
