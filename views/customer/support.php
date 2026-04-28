<?php /** @var array $tickets */ ?>
<style>
.sup-page-hdr{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem}
.sup-title{font-size:20px;font-weight:700;color:var(--color-text-primary)}
.sup-sub{font-size:12px;color:var(--color-text-muted);margin-top:2px}
.sup-new-btn{display:inline-flex;align-items:center;gap:.4rem;padding:7px 16px;border-radius:var(--border-radius-md);background:#c9a84c;color:#0f1623;font-size:13px;font-weight:700;border:none;cursor:pointer;transition:background .15s}
.sup-new-btn:hover{background:#b8932f}
.sup-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden;margin-bottom:1rem}
.sup-card-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem}
.sup-empty{text-align:center;padding:3rem}
.sup-tbl-scroll{overflow-x:auto}
table.sup-tbl{width:100%;border-collapse:collapse;min-width:680px}
table.sup-tbl thead th{padding:9px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-muted);background:var(--color-background-secondary);text-align:left;white-space:nowrap;border-bottom:.5px solid var(--color-border-tertiary)}
table.sup-tbl tbody tr{border-bottom:.5px solid var(--color-border-tertiary);transition:background .1s}
table.sup-tbl tbody tr:nth-child(even){background:var(--color-background-secondary)}
table.sup-tbl tbody tr:hover{background:#edf2f9}
table.sup-tbl tbody tr:last-child{border-bottom:none}
table.sup-tbl tbody td{padding:10px 12px;font-size:13px;vertical-align:middle}
table.sup-tbl th:nth-child(1),table.sup-tbl td:nth-child(1){width:55px}
table.sup-tbl th:nth-child(3),table.sup-tbl td:nth-child(3){width:100px}
table.sup-tbl th:nth-child(4),table.sup-tbl td:nth-child(4){width:100px}
table.sup-tbl th:nth-child(5),table.sup-tbl td:nth-child(5){width:120px}
table.sup-tbl th:nth-child(6),table.sup-tbl td:nth-child(6){width:100px}
table.sup-tbl th:nth-child(7),table.sup-tbl td:nth-child(7){width:80px}
.sp-pill{display:inline-flex;align-items:center;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap}
.spp-high{background:#fcebeb;color:#a32d2d}
.spp-medium{background:#fff8e7;color:#8a6200;border:.5px solid #f0c040}
.spp-low{background:#eaf3de;color:#1a7a3e}
.spp-urgent{background:#f0eeff;color:#5b4fcf}
.spp-open{background:#eef6ff;color:#1a5dad}
.spp-in_progress{background:#fff8e7;color:#8a6200;border:.5px solid #f0c040}
.spp-resolved{background:#eaf3de;color:#1a7a3e}
.spp-closed{background:var(--color-background-secondary);color:var(--color-text-muted);border:.5px solid var(--color-border-tertiary)}
.sup-view-btn{display:inline-flex;align-items:center;gap:.25rem;padding:5px 10px;border-radius:var(--border-radius-sm);background:var(--color-background-secondary);color:var(--color-text-secondary);font-size:11px;font-weight:600;border:.5px solid var(--color-border-tertiary);text-decoration:none;transition:all .15s}
.sup-view-btn:hover{border-color:#c9a84c;color:#8a6200}
.sup-form-panel{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden;display:none}
.sup-form-panel.show{display:block}
.sup-form-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);background:var(--color-background-secondary)}
.sup-form-body{padding:1.25rem}
.sf-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-secondary);margin-bottom:.35rem}
.sf-input,.sf-select{width:100%;padding:.58rem .8rem;border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-family:var(--font-sans);background:var(--color-background-primary);color:var(--color-text-primary);outline:none;transition:border-color .15s;margin-bottom:.85rem}
.sf-input:focus,.sf-select:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.sf-textarea{width:100%;padding:.65rem .8rem;border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-family:var(--font-sans);background:var(--color-background-primary);color:var(--color-text-primary);outline:none;transition:border-color .15s;min-height:120px;resize:vertical;margin-bottom:.85rem}
.sf-textarea:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.sf-actions{display:flex;justify-content:flex-end;gap:.6rem}
.sf-submit-btn{padding:8px 18px;background:#c9a84c;color:#0f1623;border:none;border-radius:var(--border-radius-md);font-size:13px;font-weight:700;cursor:pointer;transition:background .15s}
.sf-submit-btn:hover{background:#b8932f}
.sf-cancel-btn{padding:8px 14px;background:var(--color-background-secondary);color:var(--color-text-secondary);border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:12px;font-weight:600;cursor:pointer;transition:all .15s}
.sf-cancel-btn:hover{border-color:#C0392B;color:#C0392B}
</style>

<div class="sup-page-hdr">
  <div>
    <h1 class="sup-title">Support Tickets</h1>
    <p class="sup-sub">Raise and track your support requests</p>
  </div>
  <button class="sup-new-btn" onclick="toggleSupForm()">＋ New Ticket</button>
</div>

<?php if (empty($tickets)): ?>
<div class="sup-card">
  <div class="sup-empty">
    <div style="font-size:2.5rem;margin-bottom:.75rem">🎧</div>
    <div style="font-size:16px;font-weight:700;color:var(--color-text-primary);margin-bottom:.35rem">No support tickets</div>
    <p style="font-size:13px;color:var(--color-text-muted);margin-bottom:1rem">Need help? Create a new ticket and we'll get back to you.</p>
    <button class="sup-new-btn" onclick="toggleSupForm()">＋ Create Ticket</button>
  </div>
</div>
<?php else: ?>
<div class="sup-card">
  <div class="sup-card-hdr">🎫 My Tickets</div>
  <div class="sup-tbl-scroll">
    <table class="sup-tbl">
      <thead>
        <tr><th>#</th><th>Subject</th><th>Priority</th><th>Status</th><th>Assigned To</th><th>Date</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach ($tickets as $t): ?>
      <tr>
        <td><code style="font-size:.72rem;color:var(--color-text-muted)">#<?= $t['id'] ?></code></td>
        <td><div style="font-weight:600;font-size:13px"><?= htmlspecialchars($t['subject']) ?></div></td>
        <td><span class="sp-pill spp-<?= strtolower($t['priority']) ?>"><?= ucfirst($t['priority']) ?></span></td>
        <td><span class="sp-pill spp-<?= $t['status'] ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span></td>
        <td style="font-size:12px;color:var(--color-text-muted)"><?= htmlspecialchars($t['assigned_name'] ?? 'Unassigned') ?></td>
        <td style="font-size:11px;color:var(--color-text-muted);white-space:nowrap"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
        <td>
          <a href="/banking-system/public/support/ticket/<?= $t['id'] ?>" class="sup-view-btn">👁 View</a>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Create Ticket Form Panel -->
<div class="sup-form-panel" id="supFormPanel">
  <div class="sup-form-hdr">🎧 Create Support Ticket</div>
  <div class="sup-form-body">
    <form method="POST" action="/banking-system/public/customer/support/create" class="needs-validation" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <div>
        <label class="sf-label" for="subject">Subject <span style="color:#C0392B">*</span></label>
        <input type="text" class="sf-input" id="subject" name="subject" required maxlength="200" placeholder="Brief description of your issue">
      </div>
      <div>
        <label class="sf-label" for="priority">Priority</label>
        <select class="sf-select" id="priority" name="priority">
          <option value="low">Low</option>
          <option value="medium" selected>Medium</option>
          <option value="high">High</option>
          <option value="urgent">Urgent</option>
        </select>
      </div>
      <div>
        <label class="sf-label" for="message">Message <span style="color:#C0392B">*</span></label>
        <textarea class="sf-textarea" id="message" name="message" required placeholder="Describe your issue in detail..."></textarea>
      </div>
      <div class="sf-actions">
        <button type="button" class="sf-cancel-btn" onclick="toggleSupForm()">Cancel</button>
        <button type="submit" class="sf-submit-btn">✉️ Submit Ticket</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleSupForm(){
  const p=document.getElementById('supFormPanel');
  p.classList.toggle('show');
  if(p.classList.contains('show')) p.scrollIntoView({behavior:'smooth',block:'start'});
}
</script>
