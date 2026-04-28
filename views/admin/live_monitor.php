<?php /** @var array $recentTxns, $stats */ ?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
  <div>
    <div class="page-title">Live Transaction Monitor <span class="ws-dot" id="ws-dot" style="vertical-align:middle;margin-left:.35rem"></span></div>
    <div class="page-sub">SecureBank / Live Monitor — Real-time feed</div>
  </div>
  <div style="display:flex;gap:.5rem">
    <button class="btn-pill" id="pauseBtn" onclick="togglePause()">⏸ Pause</button>
    <button class="btn-pill" style="color:#a32d2d;border-color:#f5b7b7" onclick="clearFeed()">🗑 Clear</button>
  </div>
</div>

<!-- KPIs -->
<div class="stat-grid fade-up" style="margin-bottom:1rem">
  <div class="stat-card blue">
    <div class="stat-num" id="live-count"><?= $stats['today_count'] ?? 0 ?></div>
    <div class="stat-label">Today's Transactions</div>
  </div>
  <div class="stat-card green">
    <div class="stat-num">₹<?= number_format((float)($stats['today_vol'] ?? 0), 0) ?></div>
    <div class="stat-label">Today's Volume</div>
  </div>
  <div class="stat-card amber">
    <div class="stat-num" id="pending-live">—</div>
    <div class="stat-label">Pending Approvals</div>
  </div>
  <div class="stat-card" style="border-top-color:#C0392B">
    <div class="stat-num" id="fraud-live" style="color:#C0392B">—</div>
    <div class="stat-label">Fraud Flags Today</div>
  </div>
</div>

<!-- Feed table -->
<div class="tbl-card fade-up">
  <div style="padding:.75rem 1rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:13px;font-weight:600">⚡ Live Transaction Stream</div>
  <div style="max-height:540px;overflow-y:auto" id="feed-container">
    <table class="tbl" aria-label="Live transaction feed">
      <thead style="position:sticky;top:0;z-index:1">
        <tr><th>Reference</th><th>Type</th><th>Amount</th><th>From</th><th>To</th><th>Status</th><th>Initiator</th><th>Time</th></tr>
      </thead>
      <tbody id="live-feed">
      <?php foreach ($recentTxns as $txn):
        $typeColors=['transfer'=>'badge-blue','deposit'=>'badge-green','withdrawal'=>'badge-gold'];
        $statusColors=['completed'=>'badge-success','pending'=>'badge-gold','failed'=>'badge-failure','rejected'=>'badge-failure','reversed'=>'badge-blue'];
        $tc=$typeColors[$txn['type']]??'badge-gray';
        $sc=$statusColors[$txn['status']]??'badge-gray';
      ?>
      <tr class="<?= !empty($txn['is_flagged']) ? 'row-failure' : '' ?>">
        <td class="td-mono"><?= htmlspecialchars($txn['reference_number']) ?></td>
        <td><span class="<?= $tc ?>"><?= $txn['type'] ?></span></td>
        <td style="font-weight:600;font-size:13px">₹<?= number_format((float)$txn['amount'], 2) ?></td>
        <td class="td-muted"><?= htmlspecialchars($txn['from_acc'] ?? '—') ?></td>
        <td class="td-muted"><?= htmlspecialchars($txn['to_acc'] ?? '—') ?></td>
        <td><span class="<?= $sc ?>"><?= $txn['status'] ?></span></td>
        <td style="font-size:12px;color:var(--color-text-muted)"><?= htmlspecialchars($txn['initiator'] ?? '—') ?></td>
        <td class="td-muted"><?= date('H:i:s', strtotime($txn['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($recentTxns)): ?>
      <tr><td colspan="8" style="text-align:center;padding:2rem;font-size:13px;color:var(--color-text-muted)">No transactions yet. Waiting for live events…</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Broadcast -->
<div class="card-box fade-up" style="margin-top:16px">
  <div class="card-box-title" style="margin-bottom:.65rem">📢 Broadcast System Alert</div>
  <form method="POST" action="/banking-system/public/admin/broadcast" style="display:flex;gap:.5rem;flex-wrap:wrap">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <input type="text" class="form-input" name="message" placeholder="Enter system-wide message…" required style="flex:1;min-width:220px">
    <select class="form-select" name="severity" style="width:130px">
      <option value="info">ℹ️ Info</option>
      <option value="warning">⚠️ Warning</option>
      <option value="danger">🚨 Critical</option>
    </select>
    <button type="submit" class="btn-pill gold" style="white-space:nowrap">📢 Broadcast</button>
  </form>
</div>

<script>
let paused = false;
function togglePause() {
    paused = !paused;
    const btn = document.getElementById('pauseBtn');
    btn.textContent = paused ? '▶ Resume' : '⏸ Pause';
    btn.style.color = paused ? '#1a7a3e' : '';
    btn.style.borderColor = paused ? '#b8d89a' : '';
}
function clearFeed() {
    document.getElementById('live-feed').innerHTML =
        '<tr><td colspan="8" style="text-align:center;padding:2rem;font-size:13px;color:var(--color-text-muted)">Feed cleared</td></tr>';
}
function typeBadge(t){const m={transfer:'badge-blue',deposit:'badge-green',withdrawal:'badge-gold'};return m[t]||'badge-gray';}
function statusBadge(s){const m={completed:'badge-success',pending:'badge-gold',failed:'badge-failure'};return m[s]||'badge-gray';}
if (typeof rt !== 'undefined') {
    rt.on('transaction_completed', (d) => {
        if (paused) return;
        const feed = document.getElementById('live-feed');
        const lc = document.getElementById('live-count');
        if (lc) lc.textContent = parseInt(lc.textContent || '0') + 1;
        const row = document.createElement('tr');
        row.className = (d.flagged ? 'row-failure ' : '') + 'row-new';
        row.innerHTML = `
            <td class="td-mono">${d.reference||'—'}</td>
            <td><span class="${typeBadge(d.type)}">${d.type||'transfer'}</span></td>
            <td style="font-weight:600">₹${parseFloat(d.amount||0).toLocaleString('en-IN',{minimumFractionDigits:2})}</td>
            <td class="td-muted">${d.from||'—'}</td>
            <td class="td-muted">${d.to||'—'}</td>
            <td><span class="badge-success">completed</span></td>
            <td style="font-size:12px;color:var(--color-text-muted)">—</td>
            <td class="td-muted">${new Date().toLocaleTimeString()}</td>`;
        feed.prepend(row);
    });
}
</script>
