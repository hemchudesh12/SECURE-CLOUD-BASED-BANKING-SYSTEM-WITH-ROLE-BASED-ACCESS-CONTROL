<?php /** @var array $account, $recentTxns, $unreadNotifs */ ?>
<?php
$bal      = number_format((float)($account['balance']??0),2);
$accNum   = htmlspecialchars($account['account_number']??'N/A');
$accType  = ucfirst($account['account_type']??'savings');
$isFrozen = !empty($account['is_frozen']);
?>
<style>
.dash-quick-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:.75rem;margin-bottom:1.5rem}
@media(max-width:900px){.dash-quick-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:500px){.dash-quick-grid{grid-template-columns:repeat(2,1fr)}}
.qa-card{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.45rem;padding:.9rem .5rem;background:var(--color-background-primary);border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-lg);cursor:pointer;transition:transform .18s,box-shadow .18s,border-color .18s;text-decoration:none;color:var(--color-text-primary)}
.qa-card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(0,0,0,.09);border-color:var(--color-gold);color:var(--color-text-primary)}
.qa-card .qa-icon-wrap{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.35rem;background:var(--color-background-secondary);transition:background .18s}
.qa-card:hover .qa-icon-wrap{background:#fff8e7}
.qa-card .qa-label{font-size:12px;font-weight:600;color:var(--color-text-secondary);text-align:center}
.balance-kpi-row{display:grid;grid-template-columns:1.7fr 1fr 1fr 1fr;gap:1rem;margin-bottom:1.5rem;align-items:stretch}
@media(max-width:960px){.balance-kpi-row{grid-template-columns:1fr 1fr}.balance-hero-wrap{grid-column:1/-1}}
@media(max-width:500px){.balance-kpi-row{grid-template-columns:1fr}.balance-hero-wrap{grid-column:1}}
.balance-hero-wrap{display:flex;flex-direction:column}
.balance-hero{background:linear-gradient(135deg,#0f1623 70%,#1a2540);border-radius:var(--border-radius-lg);padding:1.5rem 1.5rem 1.25rem;color:#fff;position:relative;overflow:hidden;border:.5px solid #c9a84c44;flex:1}
.balance-hero::before{content:'';position:absolute;top:-30px;right:-30px;width:120px;height:120px;border-radius:50%;background:rgba(201,168,76,.07)}
.balance-label{font-size:10px;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.45);margin-bottom:.3rem}
.balance-amount{font-size:2.4rem;font-weight:700;color:#c9a84c;line-height:1}
.balance-acc{font-size:12px;color:rgba(255,255,255,.4);margin-top:.4rem}
.frozen-overlay{position:absolute;inset:0;background:rgba(15,22,35,.88);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#c9a84c;border-radius:var(--border-radius-lg);z-index:2}
.balance-actions{margin-top:1.1rem;display:flex;gap:.5rem;flex-wrap:wrap}
.bal-btn-p{display:inline-flex;align-items:center;gap:.3rem;padding:6px 14px;border-radius:20px;background:#c9a84c;color:#0f1623;font-size:12px;font-weight:700;border:none;cursor:pointer;text-decoration:none;transition:background .15s}
.bal-btn-p:hover{background:#b8932f;color:#0f1623}
.bal-btn-s{display:inline-flex;align-items:center;gap:.3rem;padding:6px 14px;border-radius:20px;background:rgba(255,255,255,.1);color:#fff;font-size:12px;font-weight:600;border:.5px solid rgba(255,255,255,.2);cursor:pointer;text-decoration:none;transition:background .15s}
.bal-btn-s:hover{background:rgba(255,255,255,.18)}
.kpi-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);border-top-width:3px;padding:1rem 1.1rem;display:flex;flex-direction:column;justify-content:space-between}
.kpi-card.green{border-top-color:#2E7D52}.kpi-card.red{border-top-color:#C0392B}.kpi-card.purple{border-top-color:#7f77dd}
.kpi-top{display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem}
.kpi-value{font-size:1.55rem;font-weight:700;line-height:1.1}
.kpi-label{font-size:11px;font-weight:600;color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.04em;margin-top:2px}
.kpi-icon-wrap{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.kpi-icon-wrap.green{background:#eaf3de}.kpi-icon-wrap.red{background:#fcebeb}.kpi-icon-wrap.purple{background:#f0eeff}
.kpi-sub{font-size:11px;color:var(--color-text-muted);margin-top:.6rem}
.dash-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden}
.dash-card-hdr{display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);gap:.5rem;flex-wrap:wrap}
.dash-card-title{font-size:14px;font-weight:700;color:var(--color-text-primary)}
.dash-card-sub{font-size:12px;color:var(--color-text-muted);margin-top:1px}
.tbl-wrap{overflow-x:auto}
table.tbl-dash{width:100%;border-collapse:collapse}
table.tbl-dash thead th{padding:9px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-muted);background:var(--color-background-secondary);text-align:left;white-space:nowrap;border-bottom:.5px solid var(--color-border-tertiary)}
table.tbl-dash tbody tr{border-bottom:.5px solid var(--color-border-tertiary);transition:background .1s}
table.tbl-dash tbody tr:last-child{border-bottom:none}
table.tbl-dash tbody tr:nth-child(even){background:var(--color-background-secondary)}
table.tbl-dash tbody tr:hover{background:#f0f4f9}
table.tbl-dash tbody tr.flagged{background:#fff8e7}
table.tbl-dash tbody td{padding:10px 14px;font-size:13px;vertical-align:middle;color:var(--color-text-primary)}
table.tbl-dash th:nth-child(1),table.tbl-dash td:nth-child(1){width:155px}
table.tbl-dash th:nth-child(2),table.tbl-dash td:nth-child(2){width:100px}
table.tbl-dash th:nth-child(3),table.tbl-dash td:nth-child(3){width:120px}
table.tbl-dash th:nth-child(4),table.tbl-dash td:nth-child(4){width:110px}
table.tbl-dash td:nth-child(5){max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
table.tbl-dash th:nth-child(6),table.tbl-dash td:nth-child(6){width:110px;white-space:nowrap}
.pill{display:inline-flex;align-items:center;gap:3px;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap}
.pill-credit{background:#eaf3de;color:#1a7a3e}.pill-debit{background:#fcebeb;color:#a32d2d}
.pill-completed{background:#eaf3de;color:#1a7a3e}.pill-pending{background:#fff8e7;color:#8a6200;border:.5px solid #f0c040}
.pill-failed{background:#fcebeb;color:#a32d2d}.pill-reversed{background:#f0eeff;color:#5b4fcf}
.pill-gray{background:var(--color-background-secondary);color:var(--color-text-secondary);border:.5px solid var(--color-border-tertiary)}
.act-btn{display:inline-flex;align-items:center;gap:.25rem;padding:4px 10px;border-radius:var(--border-radius-sm);font-size:11px;font-weight:600;border:.5px solid var(--color-border-tertiary);background:var(--color-background-secondary);color:var(--color-text-secondary);text-decoration:none;transition:all .15s}
.act-btn:hover{border-color:#c9a84c;color:#8a6200}
.act-btn.primary{background:#c9a84c;border-color:#c9a84c;color:#0f1623}
.act-btn.primary:hover{background:#b8932f;border-color:#b8932f;color:#0f1623}
</style>

<!-- Quick Actions -->
<div class="dash-quick-grid fade-up">
  <a href="/banking-system/public/customer/transfer" class="qa-card" aria-label="Fund Transfer">
    <div class="qa-icon-wrap">↔️</div><div class="qa-label">Transfer</div>
  </a>
  <a href="/banking-system/public/customer/beneficiaries" class="qa-card" aria-label="Beneficiaries">
    <div class="qa-icon-wrap">👥</div><div class="qa-label">Beneficiaries</div>
  </a>
  <a href="/banking-system/public/customer/scheduled" class="qa-card" aria-label="Scheduled Payments">
    <div class="qa-icon-wrap">📅</div><div class="qa-label">Schedule</div>
  </a>
  <a href="/banking-system/public/customer/statement" class="qa-card" aria-label="Account Statement">
    <div class="qa-icon-wrap">📄</div><div class="qa-label">Statement</div>
  </a>
  <a href="/banking-system/public/customer/analytics" class="qa-card" aria-label="Analytics">
    <div class="qa-icon-wrap">📈</div><div class="qa-label">Analytics</div>
  </a>
  <a href="/banking-system/public/customer/support" class="qa-card" aria-label="Support">
    <div class="qa-icon-wrap">🎧</div><div class="qa-label">Support</div>
  </a>
</div>

<!-- Balance + KPI Row -->
<div class="balance-kpi-row">
  <div class="balance-hero-wrap">
    <div class="balance-hero fade-up" id="balance-card">
      <?php if($isFrozen):?>
      <div class="frozen-overlay" role="alert" aria-label="Account frozen">🔒 Account Frozen — Contact Support</div>
      <?php endif;?>
      <div class="balance-label">Available Balance</div>
      <div class="balance-amount" id="balance-display">₹<?= $bal ?></div>
      <div class="balance-acc"><?= $accNum ?> &nbsp;·&nbsp; <?= $accType ?></div>
      <div class="balance-actions">
        <a href="/banking-system/public/customer/transfer" class="bal-btn-p">↔️ Transfer</a>
        <a href="/banking-system/public/customer/statement" class="bal-btn-s">📄 Statement</a>
      </div>
    </div>
  </div>
  <div class="kpi-card green fade-up">
    <div class="kpi-top">
      <div><div class="kpi-value" style="color:#2E7D52" id="today-credit">₹0</div><div class="kpi-label">Today Credits</div></div>
      <div class="kpi-icon-wrap green" aria-hidden="true">⬇️</div>
    </div>
    <div class="kpi-sub">Incoming today</div>
  </div>
  <div class="kpi-card red fade-up">
    <div class="kpi-top">
      <div><div class="kpi-value" style="color:#C0392B" id="today-debit">₹0</div><div class="kpi-label">Today Debits</div></div>
      <div class="kpi-icon-wrap red" aria-hidden="true">⬆️</div>
    </div>
    <div class="kpi-sub">Outgoing today</div>
  </div>
  <div class="kpi-card purple fade-up">
    <div class="kpi-top">
      <div><div class="kpi-value" style="color:#7f77dd"><?= count($recentTxns) ?></div><div class="kpi-label">Recent Txns</div></div>
      <div class="kpi-icon-wrap purple" aria-hidden="true">💳</div>
    </div>
    <div class="kpi-sub">Last transactions</div>
  </div>
</div>

<!-- Transaction Feed -->
<div class="dash-card fade-up">
  <div class="dash-card-hdr">
    <div>
      <div class="dash-card-title">⚡ Recent Transactions <span class="ws-dot" style="vertical-align:middle;margin-left:.35rem" aria-label="Live updates"></span></div>
      <div class="dash-card-sub">Your latest account activity</div>
    </div>
    <div style="display:flex;gap:.5rem">
      <a href="/banking-system/public/customer/export" class="act-btn" aria-label="Export CSV">📥 Export</a>
      <a href="/banking-system/public/customer/history" class="act-btn primary" aria-label="View all">View All →</a>
    </div>
  </div>
  <div class="tbl-wrap">
    <table class="tbl-dash" aria-label="Recent transactions">
      <thead>
        <tr><th>Reference</th><th>Type</th><th>Amount</th><th>Status</th><th>Description</th><th>Date</th></tr>
      </thead>
      <tbody id="live-feed">
      <?php if(empty($recentTxns)):?>
      <tr><td colspan="6" style="text-align:center;padding:2.5rem;color:var(--color-text-muted)">
        📭 No transactions yet. <a href="/banking-system/public/customer/transfer" style="color:#c9a84c;font-weight:600">Make your first transfer →</a>
      </td></tr>
      <?php else: foreach($recentTxns as $txn):
        $isDebit=!empty($txn['from_account_id'])&&$txn['from_account_id']==($account['id']??0);
        $amt='₹'.number_format((float)$txn['amount'],2);
        $spmap=['completed'=>'pill-completed','pending'=>'pill-pending','failed'=>'pill-failed','rejected'=>'pill-failed','reversed'=>'pill-reversed'];
        $spill=$spmap[$txn['status']]??'pill-gray';
      ?>
      <tr class="<?= $txn['is_flagged']?'flagged':'' ?>" aria-label="Transaction <?= htmlspecialchars($txn['reference_number']) ?>">
        <td><code style="font-size:.72rem;color:var(--color-text-muted)"><?= htmlspecialchars($txn['reference_number']) ?></code></td>
        <td>
          <?php if($isDebit):?><span class="pill pill-debit">⬆️ Debit</span>
          <?php else:?><span class="pill pill-credit">⬇️ Credit</span><?php endif;?>
        </td>
        <td style="font-weight:700;color:<?=$isDebit?'#C0392B':'#2E7D52'?>"><?= $amt ?></td>
        <td><span class="pill <?= $spill ?>"><?= ucfirst($txn['status']) ?></span></td>
        <td style="color:var(--color-text-muted)"><?= htmlspecialchars($txn['description']??'—') ?></td>
        <td style="color:var(--color-text-muted);font-size:.72rem"><?= date('d M, H:i',strtotime($txn['created_at'])) ?></td>
      </tr>
      <?php endforeach;endif;?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded',()=>{
  const rows=<?= json_encode($recentTxns) ?>;
  const accountId=<?= (int)($account['id']??0) ?>;
  let cr=0,dr=0;
  const today=new Date().toDateString();
  rows.forEach(r=>{
    if(new Date(r.created_at).toDateString()!==today||r.status!=='completed')return;
    if(r.from_account_id==accountId)dr+=parseFloat(r.amount);
    else cr+=parseFloat(r.amount);
  });
  const fmt=v=>'₹'+v.toLocaleString('en-IN',{minimumFractionDigits:2});
  const ce=document.getElementById('today-credit');
  const de=document.getElementById('today-debit');
  if(ce)ce.textContent=fmt(cr);
  if(de)de.textContent=fmt(dr);
});
</script>
