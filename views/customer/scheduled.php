<?php /** @var array $scheduledList, $account */ ?>
<style>
.sched-page-hdr{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem}
.sched-title{font-size:20px;font-weight:700;color:var(--color-text-primary)}
.sched-sub{font-size:12px;color:var(--color-text-muted);margin-top:2px}
.sched-new-btn{display:inline-flex;align-items:center;gap:.4rem;padding:7px 16px;border-radius:var(--border-radius-md);background:#c9a84c;color:#0f1623;font-size:13px;font-weight:700;border:none;cursor:pointer;transition:background .15s}
.sched-new-btn:hover{background:#b8932f}
.sched-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden;margin-bottom:1rem}
.sched-card-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem}
.sched-empty{text-align:center;padding:3.5rem 2rem}
.sched-empty-icon{font-size:3rem;margin-bottom:.75rem}
.sched-empty-title{font-size:16px;font-weight:700;color:var(--color-text-primary);margin-bottom:.35rem}
.sched-empty-sub{font-size:13px;color:var(--color-text-muted);margin-bottom:1.25rem}
.sched-cta-btn{display:inline-flex;align-items:center;gap:.4rem;padding:10px 24px;border-radius:var(--border-radius-md);background:#0f1623;color:#c9a84c;font-size:14px;font-weight:700;border:none;cursor:pointer;width:100%;max-width:320px;justify-content:center;transition:background .15s}
.sched-cta-btn:hover{background:#1a2540}
.sched-tbl-scroll{overflow-x:auto}
table.sched-tbl{width:100%;border-collapse:collapse;min-width:600px}
table.sched-tbl thead th{padding:9px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-muted);background:var(--color-background-secondary);text-align:left;white-space:nowrap;border-bottom:.5px solid var(--color-border-tertiary)}
table.sched-tbl tbody tr{border-bottom:.5px solid var(--color-border-tertiary);transition:background .1s}
table.sched-tbl tbody tr:nth-child(even){background:var(--color-background-secondary)}
table.sched-tbl tbody tr:hover{background:#edf2f9}
table.sched-tbl tbody tr:last-child{border-bottom:none}
table.sched-tbl tbody td{padding:10px 12px;font-size:13px;vertical-align:middle}
.s-pill{display:inline-flex;align-items:center;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap}
.sp-active{background:#eaf3de;color:#1a7a3e}
.sp-paused{background:#fff8e7;color:#8a6200;border:.5px solid #f0c040}
.sp-cancelled{background:var(--color-background-secondary);color:var(--color-text-muted);border:.5px solid var(--color-border-tertiary)}
.sp-freq{background:#eef6ff;color:#1a5dad}
.sched-cancel-btn{display:inline-flex;align-items:center;gap:.2rem;padding:5px 10px;border-radius:var(--border-radius-sm);background:#fcebeb;color:#a32d2d;border:.5px solid #f5b7b7;font-size:11px;font-weight:700;cursor:pointer;transition:all .15s}
.sched-cancel-btn:hover{background:#f5b7b7}
.sched-form-panel{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden;display:none}
.sched-form-panel.show{display:block}
.sched-form-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);background:var(--color-background-secondary)}
.sched-form-body{padding:1.25rem}
.sf-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-secondary);margin-bottom:.35rem}
.sf-input,.sf-select{width:100%;padding:.58rem .8rem;border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-family:var(--font-sans);background:var(--color-background-primary);color:var(--color-text-primary);outline:none;transition:border-color .15s}
.sf-input:focus,.sf-select:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.sf-group{margin-bottom:.9rem}
.sf-row{display:grid;grid-template-columns:1fr 1fr;gap:.85rem}
@media(max-width:580px){.sf-row{grid-template-columns:1fr}}
.sf-actions{display:flex;justify-content:flex-end;gap:.6rem;margin-top:1rem}
.sf-btn-primary{padding:8px 18px;background:#0f1623;color:#c9a84c;border:none;border-radius:var(--border-radius-md);font-size:13px;font-weight:700;cursor:pointer;transition:background .15s}
.sf-btn-primary:hover{background:#1a2540}
.sf-btn-cancel{padding:8px 14px;background:var(--color-background-secondary);color:var(--color-text-secondary);border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:12px;font-weight:600;cursor:pointer;transition:all .15s}
.sf-btn-cancel:hover{border-color:#C0392B;color:#C0392B}
</style>

<div class="sched-page-hdr">
  <div>
    <h1 class="sched-title">Scheduled Payments</h1>
    <p class="sched-sub">Set up recurring and one-time future payments</p>
  </div>
  <button class="sched-new-btn" onclick="toggleSchedForm()">＋ New Scheduled Payment</button>
</div>

<?php if (empty($scheduledList)): ?>
<div class="sched-card">
  <div class="sched-empty">
    <div class="sched-empty-icon">📅</div>
    <div class="sched-empty-title">No scheduled payments</div>
    <p class="sched-empty-sub">Set up recurring transfers to automate your payments.</p>
    <button class="sched-cta-btn" onclick="toggleSchedForm()">＋ Create First Scheduled Payment</button>
  </div>
</div>
<?php else: ?>
<div class="sched-card">
  <div class="sched-card-hdr">📋 Scheduled Payments</div>
  <div class="sched-tbl-scroll">
    <table class="sched-tbl">
      <thead>
        <tr><th>To Account</th><th>Amount</th><th>Frequency</th><th>Next Run</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach ($scheduledList as $s): ?>
      <tr>
        <td>
          <div style="font-weight:700;font-size:13px"><?= htmlspecialchars($s['to_acc']) ?></div>
          <div style="font-size:11px;color:var(--color-text-muted)"><?= htmlspecialchars($s['description'] ?? '') ?></div>
        </td>
        <td style="font-weight:700">₹<?= number_format((float)$s['amount'], 2) ?></td>
        <td><span class="s-pill sp-freq"><?= ucfirst($s['frequency']) ?></span></td>
        <td style="font-size:12px;color:var(--color-text-muted)"><?= date('d M Y H:i', strtotime($s['next_run_at'])) ?></td>
        <td>
          <span class="s-pill <?= $s['status']==='active'?'sp-active':($s['status']==='paused'?'sp-paused':'sp-cancelled') ?>">
            <?= ucfirst($s['status']) ?>
          </span>
        </td>
        <td>
          <?php if ($s['status'] === 'active'): ?>
          <form method="POST" action="/banking-system/public/customer/scheduled/cancel/<?= $s['id'] ?>" style="display:inline" onsubmit="return confirm('Cancel this scheduled payment?')">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button class="sched-cancel-btn">✕ Cancel</button>
          </form>
          <?php else: ?>
          <span style="font-size:11px;color:var(--color-text-muted)"><?= ucfirst($s['status']) ?></span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- New Scheduled Payment Form Panel -->
<div class="sched-form-panel" id="schedFormPanel">
  <div class="sched-form-hdr">📅 New Scheduled Payment</div>
  <div class="sched-form-body">
    <form method="POST" action="/banking-system/public/customer/scheduled/add" class="needs-validation" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <div class="sf-row">
        <div class="sf-group">
          <label class="sf-label">To Account Number <span style="color:#C0392B">*</span></label>
          <input type="text" class="sf-input" name="to_account" required placeholder="ACC-XXXXXXX">
        </div>
        <div class="sf-group">
          <label class="sf-label">Amount (₹) <span style="color:#C0392B">*</span></label>
          <input type="number" class="sf-input" name="amount" required min="1" step="0.01" placeholder="Enter amount">
        </div>
      </div>
      <div class="sf-group">
        <label class="sf-label">Description</label>
        <input type="text" class="sf-input" name="description" placeholder="e.g. Monthly rent">
      </div>
      <div class="sf-group">
        <label class="sf-label">Frequency <span style="color:#C0392B">*</span></label>
        <select class="sf-select" name="frequency" required>
          <option value="once">Once</option>
          <option value="daily">Daily</option>
          <option value="weekly">Weekly</option>
          <option value="monthly" selected>Monthly</option>
        </select>
      </div>
      <div class="sf-row">
        <div class="sf-group">
          <label class="sf-label">First Run Date &amp; Time <span style="color:#C0392B">*</span></label>
          <input type="datetime-local" class="sf-input" name="next_run_at" required min="<?= date('Y-m-d\TH:i') ?>">
        </div>
        <div class="sf-group">
          <label class="sf-label">End Date (optional)</label>
          <input type="date" class="sf-input" name="end_date">
        </div>
      </div>
      <div class="sf-actions">
        <button type="button" class="sf-btn-cancel" onclick="toggleSchedForm()">Cancel</button>
        <button type="submit" class="sf-btn-primary">📅 Schedule Payment</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleSchedForm(){
  const p=document.getElementById('schedFormPanel');
  p.classList.toggle('show');
  if(p.classList.contains('show')) p.scrollIntoView({behavior:'smooth',block:'start'});
}
</script>
