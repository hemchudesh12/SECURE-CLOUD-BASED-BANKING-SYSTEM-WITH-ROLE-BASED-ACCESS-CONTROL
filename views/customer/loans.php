<?php /** @var array $loans, $account */ ?>
<style>
.loan-hdr{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem}
.loan-title{font-size:20px;font-weight:700;color:var(--color-text-primary)}
.loan-sub{font-size:12px;color:var(--color-text-muted);margin-top:2px}
.loan-apply-btn{display:inline-flex;align-items:center;gap:.4rem;padding:8px 18px;background:#c9a84c;color:#0f1623;border:none;border-radius:var(--border-radius-md);font-size:13px;font-weight:700;cursor:pointer;transition:background .15s}
.loan-apply-btn:hover{background:#b8932f}
.loan-grid{display:grid;grid-template-columns:1.5fr 1fr;gap:1.25rem}
@media(max-width:860px){.loan-grid{grid-template-columns:1fr}}
.loan-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden}
.loan-card-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem;background:var(--color-background-secondary)}
.loan-card-body{padding:1.25rem}
.lf-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-secondary);margin-bottom:.35rem}
.lf-input,.lf-select{width:100%;padding:.6rem .85rem;border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-family:var(--font-sans);background:var(--color-background-primary);color:var(--color-text-primary);outline:none;transition:border-color .15s;margin-bottom:.9rem}
.lf-input:focus,.lf-select:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.lf-actions{display:flex;justify-content:flex-end;gap:.6rem;margin-top:.5rem}
.lf-btn-primary{padding:9px 20px;background:#c9a84c;color:#0f1623;border:none;border-radius:var(--border-radius-md);font-size:13px;font-weight:700;cursor:pointer}
.lf-btn-primary:hover{background:#b8932f}
.loan-tbl-scroll{overflow-x:auto}
table.loan-tbl{width:100%;border-collapse:collapse;min-width:560px}
table.loan-tbl thead th{padding:9px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-muted);background:var(--color-background-secondary);text-align:left;border-bottom:.5px solid var(--color-border-tertiary)}
table.loan-tbl tbody tr{border-bottom:.5px solid var(--color-border-tertiary)}
table.loan-tbl tbody tr:nth-child(even){background:var(--color-background-secondary)}
table.loan-tbl tbody tr:last-child{border-bottom:none}
table.loan-tbl tbody td{padding:10px 12px;font-size:13px;vertical-align:middle}
.lp-pill{display:inline-flex;align-items:center;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap}
.lp-pending{background:#fff8e7;color:#8a6200;border:.5px solid #f0c040}
.lp-approved{background:#eaf3de;color:#1a7a3e}
.lp-rejected{background:#fcebeb;color:#a32d2d}
.loan-empty{text-align:center;padding:3rem}
.loan-info-box{background:#eef6ff;border:.5px solid #b3d1f5;border-radius:var(--border-radius-md);padding:.9rem 1.1rem;margin-bottom:1.1rem}
.loan-info-title{font-size:12px;font-weight:700;color:#1a5dad;margin-bottom:.5rem}
.loan-info-list{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.35rem}
.loan-info-list li{font-size:12px;color:#1a4080}
</style>

<div class="loan-hdr">
  <div>
    <h1 class="loan-title">💳 My Loans</h1>
    <p class="loan-sub">Apply for a loan and track your applications</p>
  </div>
  <button class="loan-apply-btn" onclick="document.getElementById('loanFormPanel').classList.toggle('show');this.textContent=document.getElementById('loanFormPanel').classList.contains('show')?'✕ Close Form':'＋ Apply for Loan'">＋ Apply for Loan</button>
</div>

<!-- Apply Form -->
<div id="loanFormPanel" class="loan-card" style="display:none;margin-bottom:1.25rem">
  <div class="loan-card-hdr">📋 New Loan Application</div>
  <div class="loan-card-body">
    <form method="POST" action="/banking-system/public/customer/loans/apply" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem">
        <div>
          <label class="lf-label">Loan Amount (₹) <span style="color:#C0392B">*</span></label>
          <input type="number" name="amount" class="lf-input" min="1000" step="100" required placeholder="e.g. 50000">
        </div>
        <div>
          <label class="lf-label">Repayment Period (months) <span style="color:#C0392B">*</span></label>
          <select name="repayment_months" class="lf-select" required>
            <option value="6">6 months</option>
            <option value="12" selected>12 months</option>
            <option value="24">24 months</option>
            <option value="36">36 months</option>
            <option value="48">48 months</option>
            <option value="60">60 months</option>
          </select>
        </div>
        <div style="grid-column:1/-1">
          <label class="lf-label">Loan Purpose / Reason <span style="color:#C0392B">*</span></label>
          <input type="text" name="purpose" class="lf-input" required placeholder="e.g. Home renovation, Medical emergency, Education">
        </div>
      </div>
      <div class="lf-actions">
        <button type="submit" class="lf-btn-primary">🚀 Submit Application</button>
      </div>
    </form>
  </div>
</div>

<!-- My Loans Table -->
<div class="loan-card">
  <div class="loan-card-hdr">📑 My Loan Applications</div>
  <?php if (empty($loans)): ?>
  <div class="loan-empty">
    <div style="font-size:2.5rem;margin-bottom:.75rem">💳</div>
    <div style="font-size:15px;font-weight:700;color:var(--color-text-primary);margin-bottom:.35rem">No loan applications yet</div>
    <p style="font-size:13px;color:var(--color-text-muted)">Click "Apply for Loan" above to submit your first application.</p>
  </div>
  <?php else: ?>
  <div class="loan-tbl-scroll">
    <table class="loan-tbl">
      <thead><tr><th>#</th><th>Amount</th><th>Purpose</th><th>Period</th><th>Status</th><th>Applied</th><th>Decision</th></tr></thead>
      <tbody>
      <?php foreach ($loans as $l): ?>
      <tr>
        <td><code style="font-size:.72rem">#<?= $l['id'] ?></code></td>
        <td style="font-weight:700">₹<?= number_format((float)$l['amount'], 2) ?></td>
        <td><?= htmlspecialchars($l['purpose']) ?></td>
        <td><?= $l['repayment_months'] ?> mo</td>
        <td><span class="lp-pill lp-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span></td>
        <td style="font-size:11px;color:var(--color-text-muted)"><?= date('d M Y', strtotime($l['requested_at'])) ?></td>
        <td style="font-size:11px;color:var(--color-text-muted)">
          <?php if ($l['status'] === 'pending'): ?>
            <span style="color:#8a6200">⏳ Awaiting review</span>
          <?php elseif ($l['status'] === 'approved'): ?>
            <span style="color:#1a7a3e">✓ <?= date('d M Y', strtotime($l['approved_at'])) ?></span><br>
            <span style="font-size:10px">by <?= htmlspecialchars($l['approved_by_name'] ?? 'Admin') ?></span>
          <?php else: ?>
            <span style="color:#a32d2d">✕ <?= htmlspecialchars($l['rejection_note'] ?? 'Rejected') ?></span>
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
const fpanel = document.getElementById('loanFormPanel');
document.querySelector('.loan-apply-btn').addEventListener('click', function() {
  const isOpen = fpanel.style.display !== 'none';
  fpanel.style.display = isOpen ? 'none' : 'block';
  this.textContent = isOpen ? '＋ Apply for Loan' : '✕ Close Form';
  if (!isOpen) fpanel.scrollIntoView({behavior:'smooth',block:'start'});
});
</script>
