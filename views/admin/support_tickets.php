<?php /** @var array $tickets */ ?>
<style>
.admsup-page-hdr{display:flex;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem}
.admsup-title{font-size:20px;font-weight:700;color:var(--color-text-primary)}
.admsup-sub{font-size:12px;color:var(--color-text-muted);margin-top:2px}
.admsup-open-badge{display:inline-flex;align-items:center;padding:4px 12px;background:#fcebeb;color:#a32d2d;font-size:13px;font-weight:700;border-radius:20px;border:.5px solid #f5b7b7;margin-left:.5rem}
.admsup-filter-bar{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:.85rem}
.admsup-filter-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-muted)}
.admsup-filter-btn{padding:5px 14px;border-radius:20px;border:.5px solid var(--color-border-tertiary);background:var(--color-background-primary);color:var(--color-text-secondary);font-size:12px;font-weight:600;cursor:pointer;transition:all .15s}
.admsup-filter-btn:hover{border-color:#c9a84c;color:#8a6200}
.admsup-filter-btn.active{background:#0f1623;border-color:#0f1623;color:#c9a84c}
.admsup-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden}
.admsup-card-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem}
.admsup-tbl-scroll{overflow-x:auto}
table.admsup-tbl{width:100%;border-collapse:collapse;min-width:720px}
table.admsup-tbl thead th{padding:9px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-muted);background:var(--color-background-secondary);text-align:left;white-space:nowrap;border-bottom:.5px solid var(--color-border-tertiary)}
table.admsup-tbl tbody tr{border-bottom:.5px solid var(--color-border-tertiary);transition:background .1s}
table.admsup-tbl tbody tr:nth-child(even){background:var(--color-background-secondary)}
table.admsup-tbl tbody tr:hover{background:#edf2f9}
table.admsup-tbl tbody tr:last-child{border-bottom:none}
table.admsup-tbl tbody td{padding:10px 12px;font-size:13px;vertical-align:middle}
table.admsup-tbl th:nth-child(1),table.admsup-tbl td:nth-child(1){width:55px}
table.admsup-tbl th:nth-child(2),table.admsup-tbl td:nth-child(2){width:165px}
table.admsup-tbl th:nth-child(4),table.admsup-tbl td:nth-child(4){width:105px}
table.admsup-tbl th:nth-child(5),table.admsup-tbl td:nth-child(5){width:110px}
table.admsup-tbl th:nth-child(6),table.admsup-tbl td:nth-child(6){width:105px}
table.admsup-tbl th:nth-child(7),table.admsup-tbl td:nth-child(7){width:80px}
.asp-pill{display:inline-flex;align-items:center;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap}
.aspp-high{background:#fcebeb;color:#a32d2d}
.aspp-medium{background:#fff8e7;color:#8a6200;border:.5px solid #f0c040}
.aspp-low{background:#eaf3de;color:#1a7a3e}
.aspp-urgent{background:#f0eeff;color:#5b4fcf}
.aspp-open{background:#eef6ff;color:#1a5dad}
.aspp-in_progress{background:#fff8e7;color:#8a6200;border:.5px solid #f0c040}
.aspp-resolved{background:#eaf3de;color:#1a7a3e}
.aspp-closed{background:var(--color-background-secondary);color:var(--color-text-muted);border:.5px solid var(--color-border-tertiary)}
.asp-view-btn{display:inline-flex;align-items:center;gap:.25rem;padding:5px 11px;border-radius:var(--border-radius-sm);background:var(--color-background-secondary);color:var(--color-text-secondary);font-size:11px;font-weight:600;border:.5px solid var(--color-border-tertiary);text-decoration:none;transition:all .15s}
.asp-view-btn:hover{border-color:#c9a84c;color:#8a6200}
.asp-cust-name{font-weight:700;font-size:13px;color:var(--color-text-primary)}
.asp-cust-user{font-size:11px;color:var(--color-text-muted)}
</style>

<div class="admsup-page-hdr">
  <div>
    <h1 class="admsup-title" style="display:inline-flex;align-items:center;gap:.5rem">
      Support Tickets
      <span class="admsup-open-badge"><?= count(array_filter($tickets, fn($t) => $t['status']==='open')) ?> Open</span>
    </h1>
    <p class="admsup-sub">Manage all customer support requests</p>
  </div>
</div>

<!-- Filter Bar -->
<div class="admsup-filter-bar">
  <span class="admsup-filter-label">Filter:</span>
  <button class="admsup-filter-btn active" onclick="filterTickets('all',this)">All</button>
  <button class="admsup-filter-btn" onclick="filterTickets('open',this)">🔵 Open</button>
  <button class="admsup-filter-btn" onclick="filterTickets('in_progress',this)">🟠 In Progress</button>
  <button class="admsup-filter-btn" onclick="filterTickets('resolved',this)">🟢 Resolved</button>
  <button class="admsup-filter-btn" onclick="filterTickets('closed',this)">⚫ Closed</button>
</div>

<div class="admsup-card">
  <div class="admsup-card-hdr">🎫 All Support Tickets</div>
  <?php if (empty($tickets)): ?>
  <div style="text-align:center;padding:3rem;color:var(--color-text-muted)">📭 No tickets yet.</div>
  <?php else: ?>
  <div class="admsup-tbl-scroll">
    <table class="admsup-tbl" id="ticketsTable">
      <thead>
        <tr><th>#</th><th>Customer</th><th>Subject</th><th>Priority</th><th>Status</th><th>Created</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach ($tickets as $t): ?>
      <tr data-status="<?= htmlspecialchars($t['status']) ?>">
        <td><code style="font-size:.72rem;color:var(--color-text-muted)">#<?= $t['id'] ?></code></td>
        <td>
          <div class="asp-cust-name"><?= htmlspecialchars($t['customer_name']) ?></div>
          <div class="asp-cust-user">@<?= htmlspecialchars($t['customer_username']) ?></div>
        </td>
        <td style="font-size:13px;font-weight:500"><?= htmlspecialchars($t['subject']) ?></td>
        <td><span class="asp-pill aspp-<?= strtolower($t['priority']) ?>"><?= ucfirst($t['priority']) ?></span></td>
        <td><span class="asp-pill aspp-<?= $t['status'] ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span></td>
        <td style="font-size:11px;color:var(--color-text-muted);white-space:nowrap"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
        <td><a href="/banking-system/public/support/ticket/<?= $t['id'] ?>" class="asp-view-btn">👁 View</a></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<script>
function filterTickets(status, btn) {
  document.querySelectorAll('.admsup-filter-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('#ticketsTable tbody tr').forEach(row=>{
    row.style.display = (status==='all'||row.dataset.status===status) ? '' : 'none';
  });
}
</script>
