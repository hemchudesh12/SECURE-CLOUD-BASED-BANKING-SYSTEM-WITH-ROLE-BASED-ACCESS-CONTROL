<!-- Teller Dashboard -->
<div class="page-header">
  <h1><i class="bi bi-speedometer2 me-2 text-gold"></i>Teller Dashboard</h1>
  <p class="text-muted mb-0"><?= date('l, d F Y') ?></p>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-card fade-in-up">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon icon-green"><i class="bi bi-arrow-down-circle"></i></div>
        <div>
          <div class="stat-value">₹<?= number_format((float)$stats['today_deposits'], 2) ?></div>
          <div class="stat-label">Today's Deposits</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card fade-in-up">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon icon-red"><i class="bi bi-arrow-up-circle"></i></div>
        <div>
          <div class="stat-value">₹<?= number_format((float)$stats['today_withdrawals'], 2) ?></div>
          <div class="stat-label">Today's Withdrawals</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card fade-in-up">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon icon-gold" style="<?= $stats['pending_approvals'] > 0 ? 'animation:pulse-gold 1.5s infinite' : '' ?>">
          <i class="bi bi-hourglass-split"></i>
        </div>
        <div>
          <div class="stat-value"><?= $stats['pending_approvals'] ?></div>
          <div class="stat-label">Pending Approvals</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Account Search Bar -->
<div class="card mb-4 fade-in-up" style="border:2px solid var(--gold)">
  <div class="card-header"><i class="bi bi-search"></i> Quick Account Search</div>
  <div class="card-body">
    <form method="POST" action="/banking-system/public/teller/search">
      <?= CsrfMiddleware::field() ?>
      <div class="input-group input-group-lg">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Search by account number or customer name..." required>
        <button type="submit" class="btn btn-primary">Search</button>
      </div>
    </form>
  </div>
</div>

<!-- Quick Actions -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <a href="/banking-system/public/teller/deposit" class="quick-action fade-in-up">
      <span class="qa-icon">💰</span><span class="qa-label">Deposit</span>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="/banking-system/public/teller/withdrawal" class="quick-action fade-in-up">
      <span class="qa-icon">🏧</span><span class="qa-label">Withdrawal</span>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="/banking-system/public/teller/approvals" class="quick-action fade-in-up position-relative">
      <span class="qa-icon">✅</span><span class="qa-label">Approvals</span>
      <?php if ($stats['pending_approvals'] > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?= $stats['pending_approvals'] ?>
        </span>
      <?php endif; ?>
    </a>
  </div>
  <div class="col-6 col-md-3">
    <a href="/banking-system/public/teller/search" class="quick-action fade-in-up">
      <span class="qa-icon">🔍</span><span class="qa-label">Search</span>
    </a>
  </div>
</div>

<!-- Recent Transactions -->
<div class="card fade-in-up">
  <div class="card-header"><i class="bi bi-clock-history"></i> Today's Transactions</div>
  <div class="card-body p-0">
    <?php if (empty($recentTxns)): ?>
      <div class="text-center py-4 text-muted">No transactions today yet.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr>
          <th>Reference</th><th>Type</th><th>Amount</th><th>Account</th><th>By</th><th>Status</th><th>Time</th>
        </tr></thead>
        <tbody>
        <?php foreach ($recentTxns as $txn): ?>
          <tr>
            <td><code style="font-size:0.78rem"><?= htmlspecialchars($txn['reference_number']) ?></code></td>
            <td><?= ucfirst($txn['type']) ?></td>
            <td class="fw-600">₹<?= number_format((float)$txn['amount'], 2) ?></td>
            <td><?= htmlspecialchars($txn['to_acc'] ?? $txn['from_acc'] ?? '—') ?></td>
            <td><?= htmlspecialchars($txn['initiated_username'] ?? '—') ?></td>
            <td><span class="badge status-<?= $txn['status'] ?>"><?= ucfirst($txn['status']) ?></span></td>
            <td style="font-size:0.78rem"><?= date('h:i A', strtotime($txn['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
