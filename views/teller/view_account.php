<!-- Teller: View Customer Account -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-person-badge me-2 text-gold"></i>
      <?= htmlspecialchars($account['full_name'] ?? $account['username']) ?>
    </h1>
    <nav><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/banking-system/public/teller/search">Search</a></li>
      <li class="breadcrumb-item active">Account Details</li>
    </ol></nav>
  </div>
  <div class="d-flex gap-2">
    <a href="/banking-system/public/teller/deposit?account_id=<?= $account['id'] ?>" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-circle me-1"></i>Deposit
    </a>
    <a href="/banking-system/public/teller/withdrawal?account_id=<?= $account['id'] ?>" class="btn btn-navy btn-sm">
      <i class="bi bi-dash-circle me-1"></i>Withdraw
    </a>
  </div>
</div>

<!-- Account Info Card -->
<div class="balance-card mb-4 fade-in-up">
  <div class="row align-items-center">
    <div class="col-md-8">
      <div class="label mb-1">Account Balance</div>
      <div class="amount">₹<?= number_format((float)$account['balance'], 2) ?></div>
      <div class="mt-2 d-flex gap-2 flex-wrap">
        <span class="account-chip"><?= htmlspecialchars($account['account_number']) ?></span>
        <span class="account-chip"><?= ucfirst($account['account_type']) ?></span>
        <span class="account-chip"><?= htmlspecialchars($account['email']) ?></span>
        <?php if ($account['phone']): ?>
        <span class="account-chip"><?= htmlspecialchars($account['phone']) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-md-4 text-end d-none d-md-block">
      <i class="bi bi-person-circle" style="font-size:5rem;color:rgba(201,168,76,0.2)"></i>
    </div>
  </div>
</div>

<!-- Transaction History -->
<div class="card fade-in-up">
  <div class="card-header">
    <i class="bi bi-clock-history"></i> Transaction History
    <span class="ms-auto badge" style="background:var(--gold);color:var(--navy)"><?= $history['total'] ?> total</span>
  </div>
  <div class="card-body p-0">
    <?php if (empty($history['rows'])): ?>
      <div class="text-center py-4 text-muted">No transactions for this account.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Reference</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($history['rows'] as $txn): ?>
          <tr>
            <td><code style="font-size:0.78rem"><?= htmlspecialchars($txn['reference_number']) ?></code></td>
            <td><?= ucfirst($txn['type']) ?></td>
            <td class="fw-600">₹<?= number_format((float)$txn['amount'], 2) ?></td>
            <td><span class="badge status-<?= $txn['status'] ?>"><?= ucfirst($txn['status']) ?></span></td>
            <td style="font-size:0.78rem"><?= date('d M Y, H:i', strtotime($txn['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
