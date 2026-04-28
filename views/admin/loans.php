<?php /** @var array $loans */ ?>
<style>
.adml-hdr{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem}
.adml-title{font-size:20px;font-weight:700;color:var(--color-text-primary)}
.adml-sub{font-size:12px;color:var(--color-text-muted);margin-top:2px}
.adml-pending-badge{display:inline-flex;align-items:center;padding:4px 12px;background:#fff8e7;color:#8a6200;font-size:13px;font-weight:700;border-radius:20px;border:.5px solid #f0c040;margin-left:.5rem}
.adml-filter-bar{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.85rem}
.adml-filter-btn{padding:5px 14px;border-radius:20px;border:.5px solid var(--color-border-tertiary);background:var(--color-background-primary);color:var(--color-text-secondary);font-size:12px;font-weight:600;cursor:pointer;transition:all .15s}
.adml-filter-btn:hover{border-color:#c9a84c;color:#8a6200}
.adml-filter-btn.active{background:#0f1623;border-color:#0f1623;color:#c9a84c}
.adml-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden}
.adml-card-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem;background:var(--color-background-secondary)}
.adml-tbl-scroll{overflow-x:auto}
table.adml-tbl{width:100%;border-collapse:collapse;min-width:820px}
table.adml-tbl thead th{padding:9px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-muted);background:var(--color-background-secondary);text-align:left;border-bottom:.5px solid var(--color-border-tertiary);white-space:nowrap}
table.adml-tbl tbody tr{border-bottom:.5px solid var(--color-border-tertiary);transition:background .1s}
table.adml-tbl tbody tr:nth-child(even){background:var(--color-background-secondary)}
table.adml-tbl tbody tr:hover{background:#edf2f9}
table.adml-tbl tbody tr:last-child{border-bottom:none}
table.adml-tbl tbody td{padding:10px 12px;font-size:13px;vertical-align:middle}
.alp-pill{display:inline-flex;align-items:center;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap}
.alp-pending{background:#fff8e7;color:#8a6200;border:.5px solid #f0c040}
.alp-approved{background:#eaf3de;color:#1a7a3e}
.alp-rejected{background:#fcebeb;color:#a32d2d}
.loan-approve-btn{padding:5px 12px;background:#eaf3de;color:#1a7a3e;border:.5px solid #b8d89a;border-radius:var(--border-radius-sm);font-size:11px;font-weight:700;cursor:pointer;transition:all .15s}
.loan-approve-btn:hover{background:#b8d89a}
.loan-reject-btn{padding:5px 10px;background:#fcebeb;color:#a32d2d;border:.5px solid #f5b7b7;border-radius:var(--border-radius-sm);font-size:11px;font-weight:700;cursor:pointer;transition:all .15s;margin-left:.35rem}
.loan-reject-btn:hover{background:#f5b7b7}
.adml-user-name{font-weight:700;font-size:13px;color:var(--color-text-primary)}
.adml-user-sub{font-size:11px;color:var(--color-text-muted)}
</style>

<?php $pendingCount = count(array_filter($loans, fn($l) => $l['status'] === 'pending')); ?>
<div class="adml-hdr">
  <div>
    <h1 class="adml-title" style="display:inline-flex;align-items:center;gap:.5rem">
      💳 Loan Management
      <?php if ($pendingCount > 0): ?>
      <span class="adml-pending-badge"><?= $pendingCount ?> Pending</span>
      <?php endif; ?>
    </h1>
    <p class="adml-sub">Review and process customer loan applications</p>
  </div>
</div>

<!-- Filter -->
<div class="adml-filter-bar">
  <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--color-text-muted)">Filter:</span>
  <a href="?status=" class="adml-filter-btn <?= empty($_GET['status']) ? 'active' : '' ?>">All</a>
  <a href="?status=pending" class="adml-filter-btn <?= ($_GET['status']??'')==='pending' ? 'active' : '' ?>">⏳ Pending</a>
  <a href="?status=approved" class="adml-filter-btn <?= ($_GET['status']??'')==='approved' ? 'active' : '' ?>">✓ Approved</a>
  <a href="?status=rejected" class="adml-filter-btn <?= ($_GET['status']??'')==='rejected' ? 'active' : '' ?>">✕ Rejected</a>
</div>

<div class="adml-card">
  <div class="adml-card-hdr">📋 All Loan Applications</div>
  <?php if (empty($loans)): ?>
  <div style="text-align:center;padding:3rem;color:var(--color-text-muted)">
    📭 No loan applications found<?= !empty($_GET['status']) ? ' for this filter' : '' ?>.
  </div>
  <?php else: ?>
  <div class="adml-tbl-scroll">
    <table class="adml-tbl" id="loansTable">
      <thead>
        <tr>
          <th>#</th>
          <th>Customer</th>
          <th>Account</th>
          <th>Amount</th>
          <th>Purpose</th>
          <th>Period</th>
          <th>Applied</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($loans as $l): ?>
      <tr data-status="<?= htmlspecialchars($l['status']) ?>">
        <td><code style="font-size:.72rem;color:var(--color-text-muted)">#<?= $l['id'] ?></code></td>
        <td>
          <div class="adml-user-name"><?= htmlspecialchars($l['full_name']) ?></div>
          <div class="adml-user-sub">@<?= htmlspecialchars($l['username']) ?> · <?= htmlspecialchars($l['email']) ?></div>
        </td>
        <td style="font-family:'JetBrains Mono',monospace;font-size:11.5px"><?= htmlspecialchars($l['account_number']) ?></td>
        <td style="font-weight:700;color:var(--color-text-primary)">₹<?= number_format((float)$l['amount'], 2) ?></td>
        <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($l['purpose']) ?>">
          <?= htmlspecialchars($l['purpose']) ?>
        </td>
        <td style="text-align:center"><?= $l['repayment_months'] ?> mo</td>
        <td style="font-size:11px;color:var(--color-text-muted);white-space:nowrap"><?= date('d M Y', strtotime($l['requested_at'])) ?></td>
        <td>
          <span class="alp-pill alp-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span>
          <?php if ($l['status'] === 'rejected' && $l['rejection_note']): ?>
          <div style="font-size:10px;color:var(--color-text-muted);margin-top:2px" title="<?= htmlspecialchars($l['rejection_note']) ?>">
            <?= htmlspecialchars(mb_strimwidth($l['rejection_note'], 0, 30, '…')) ?>
          </div>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($l['status'] === 'pending'): ?>
          <div style="display:flex;align-items:center;gap:.25rem">
            <form method="POST" action="/banking-system/public/admin/loans/<?= $l['id'] ?>/approve" style="display:inline"
                  onsubmit="return confirm('Approve loan of ₹<?= number_format((float)$l['amount'],2) ?> for <?= htmlspecialchars($l['full_name']) ?>?')">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <button class="loan-approve-btn">✓ Approve</button>
            </form>
            <form method="POST" action="/banking-system/public/admin/loans/<?= $l['id'] ?>/reject" style="display:inline"
                  onsubmit="return promptReject(event, this)">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <input type="hidden" name="rejection_note" id="rn<?= $l['id'] ?>" value="Application rejected by admin.">
              <button class="loan-reject-btn">✕ Reject</button>
            </form>
          </div>
          <?php elseif ($l['status'] === 'approved'): ?>
          <div style="font-size:11px;color:#1a7a3e">✓ <?= date('d M Y', strtotime($l['approved_at'])) ?></div>
          <div style="font-size:10px;color:var(--color-text-muted)">by <?= htmlspecialchars($l['approved_by_name'] ?? 'Admin') ?></div>
          <?php else: ?>
          <span style="font-size:11px;color:var(--color-text-muted)">—</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<script>
function promptReject(e, form) {
  e.preventDefault();
  const reason = prompt('Enter rejection reason (optional):', 'Application does not meet eligibility criteria.');
  if (reason === null) return false; // cancelled
  form.querySelector('[name="rejection_note"]').value = reason || 'Application rejected by admin.';
  form.submit();
  return false;
}
</script>
