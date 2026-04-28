<?php /** @var array $payments */ ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Scheduled Payments</h1>
    <p class="page-subtitle">View all customer scheduled payments</p>
  </div>
</div>
<div class="card">
  <div class="card-header"><div class="card-title"><i class="bi bi-calendar-range me-2"></i>All Scheduled Payments</div></div>
  <?php if (empty($payments)): ?>
  <div class="card-body text-center py-5 text-muted"><i class="bi bi-calendar display-4 mb-3 d-block"></i>No scheduled payments.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr><th>Customer</th><th>From</th><th>To</th><th>Amount</th><th>Frequency</th><th>Next Run</th><th>Status</th></tr>
      </thead>
      <tbody>
      <?php foreach ($payments as $p): ?>
      <tr>
        <td><div class="fw-semibold"><?= htmlspecialchars($p['full_name']) ?></div><div class="text-muted" style="font-size:0.75rem"><?= htmlspecialchars($p['username']) ?></div></td>
        <td><?= htmlspecialchars($p['from_acc']) ?></td>
        <td><?= htmlspecialchars($p['to_acc']) ?></td>
        <td class="fw-semibold">₹<?= number_format((float)$p['amount'],2) ?></td>
        <td><span class="badge bg-primary"><?= ucfirst($p['frequency']) ?></span></td>
        <td><?= date('d M Y H:i', strtotime($p['next_run_at'])) ?></td>
        <td><span class="badge bg-<?= $p['status']==='active'?'success':($p['status']==='paused'?'warning':'secondary') ?>"><?= ucfirst($p['status']) ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
