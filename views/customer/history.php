<!-- Transaction History -->
<style>
.hist-page-hdr{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem}
.hist-title{font-size:20px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem}
.hist-export-btn{display:inline-flex;align-items:center;gap:.4rem;padding:6px 14px;border-radius:var(--border-radius-md);background:#2E7D52;color:#fff;font-size:12px;font-weight:700;border:none;cursor:pointer;text-decoration:none;transition:background .15s}
.hist-export-btn:hover{background:#1e5c3b;color:#fff}
.hist-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden;margin-bottom:1rem}
.hist-card-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem}
.hist-filter-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.85rem;padding:1.1rem 1.25rem}
@media(max-width:760px){.hist-filter-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:480px){.hist-filter-grid{grid-template-columns:1fr}}
.hist-filter-actions{display:flex;justify-content:flex-end;align-items:center;gap:.5rem;padding:0 1.25rem 1rem}
.hf-group{display:flex;flex-direction:column;gap:.35rem}
.hf-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-secondary)}
.hf-input,.hf-select{width:100%;padding:.55rem .8rem;border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-family:var(--font-sans);background:var(--color-background-primary);color:var(--color-text-primary);outline:none;transition:border-color .15s}
.hf-input:focus,.hf-select:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.hf-filter-btn{padding:7px 18px;background:#0f1623;color:#c9a84c;border:none;border-radius:var(--border-radius-md);font-size:13px;font-weight:700;cursor:pointer;transition:background .15s}
.hf-filter-btn:hover{background:#1a2540}
.hf-clear-btn{padding:7px 12px;background:var(--color-background-secondary);color:var(--color-text-secondary);border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .15s}
.hf-clear-btn:hover{border-color:#C0392B;color:#C0392B}
.hist-tbl-hdr{display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);flex-wrap:wrap;gap:.5rem}
.hist-tbl-title{font-size:14px;font-weight:700;color:var(--color-text-primary)}
.hist-count-badge{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:#fff8e7;color:#8a6200;border:.5px solid #f0c040}
.tbl-scroll{overflow-x:auto}
table.hist-tbl{width:100%;border-collapse:collapse;min-width:700px}
table.hist-tbl thead th{padding:9px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--color-text-muted);background:var(--color-background-secondary);text-align:left;white-space:nowrap;border-bottom:.5px solid var(--color-border-tertiary)}
table.hist-tbl tbody tr{border-bottom:.5px solid var(--color-border-tertiary);transition:background .1s}
table.hist-tbl tbody tr:nth-child(even){background:var(--color-background-secondary)}
table.hist-tbl tbody tr:hover{background:#edf2f9}
table.hist-tbl tbody tr:last-child{border-bottom:none}
table.hist-tbl tbody td{padding:10px 12px;font-size:13px;vertical-align:middle;color:var(--color-text-primary)}
table.hist-tbl th:nth-child(1),table.hist-tbl td:nth-child(1){width:145px}
table.hist-tbl th:nth-child(2),table.hist-tbl td:nth-child(2){width:110px}
table.hist-tbl th:nth-child(3),table.hist-tbl td:nth-child(3){width:115px}
table.hist-tbl th:nth-child(4),table.hist-tbl td:nth-child(4){width:105px}
table.hist-tbl th:nth-child(5),table.hist-tbl td:nth-child(5){width:115px}
table.hist-tbl th:nth-child(6),table.hist-tbl td:nth-child(6){width:105px}
table.hist-tbl td:nth-child(7){max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.h-pill{display:inline-flex;align-items:center;gap:3px;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap}
.hp-credit{background:#eaf3de;color:#1a7a3e}
.hp-debit{background:#fcebeb;color:#a32d2d}
.hp-completed{background:#eaf3de;color:#1a7a3e}
.hp-pending{background:#fff8e7;color:#8a6200;border:.5px solid #f0c040}
.hp-failed{background:#fcebeb;color:#a32d2d}
.hp-gray{background:var(--color-background-secondary);color:var(--color-text-secondary);border:.5px solid var(--color-border-tertiary)}
.hp-dir-sent{background:#fcebeb;color:#a32d2d}
.hp-dir-recv{background:#eaf3de;color:#1a7a3e}
.hist-pagination{display:flex;justify-content:center;padding:.85rem}
.h-page-item{list-style:none}
.h-page-link{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:var(--border-radius-sm);border:.5px solid var(--color-border-tertiary);background:var(--color-background-primary);font-size:12px;font-weight:600;color:var(--color-text-secondary);text-decoration:none;transition:all .12s;margin:0 1px}
.h-page-link:hover{border-color:#c9a84c;color:#8a6200}
.h-page-item.active .h-page-link{background:#c9a84c;border-color:#c9a84c;color:#0f1623}
</style>

<div class="hist-page-hdr">
  <h1 class="hist-title">🕐 Transaction History</h1>
  <a href="/banking-system/public/customer/export?<?= http_build_query($_GET) ?>" class="hist-export-btn">
    📥 Export CSV
  </a>
</div>

<!-- Filters -->
<div class="hist-card fade-in-up">
  <div class="hist-card-hdr">🔍 Filter Transactions</div>
  <form method="GET" action="/banking-system/public/customer/history">
    <div class="hist-filter-grid">
      <div class="hf-group">
        <label class="hf-label">Type</label>
        <select name="type" class="hf-select">
          <option value="">All Types</option>
          <option value="transfer"   <?= ($_GET['type']??'')==='transfer'   ?'selected':'' ?>>↔️ Transfer</option>
          <option value="deposit"    <?= ($_GET['type']??'')==='deposit'    ?'selected':'' ?>>⬇️ Deposit</option>
          <option value="withdrawal" <?= ($_GET['type']??'')==='withdrawal' ?'selected':'' ?>>⬆️ Withdrawal</option>
        </select>
      </div>
      <div class="hf-group">
        <label class="hf-label">From Date</label>
        <input type="date" name="from_date" class="hf-input" value="<?= htmlspecialchars($_GET['from_date']??'') ?>">
      </div>
      <div class="hf-group">
        <label class="hf-label">To Date</label>
        <input type="date" name="to_date" class="hf-input" value="<?= htmlspecialchars($_GET['to_date']??'') ?>">
      </div>
      <div class="hf-group">
        <label class="hf-label">Min Amount</label>
        <input type="number" name="min_amount" class="hf-input" placeholder="₹0" step="0.01" value="<?= htmlspecialchars($_GET['min_amount']??'') ?>">
      </div>
      <div class="hf-group">
        <label class="hf-label">Max Amount</label>
        <input type="number" name="max_amount" class="hf-input" placeholder="₹∞" step="0.01" value="<?= htmlspecialchars($_GET['max_amount']??'') ?>">
      </div>
    </div>
    <div class="hist-filter-actions">
      <a href="/banking-system/public/customer/history" class="hf-clear-btn">✕ Clear</a>
      <button type="submit" class="hf-filter-btn">🔍 Filter</button>
    </div>
  </form>
</div>

<!-- Results -->
<div class="hist-card fade-in-up">
  <div class="hist-tbl-hdr">
    <div class="hist-tbl-title">📋 Transactions</div>
    <span class="hist-count-badge"><?= $history['total'] ?> total</span>
  </div>
  <div>
    <?php if (empty($history['rows'])): ?>
      <div style="text-align:center;padding:3rem;color:var(--color-text-muted)">
        📭 No transactions found for the selected filters.
      </div>
    <?php else: ?>
    <div class="tbl-scroll">
      <table class="hist-tbl">
        <thead>
          <tr>
            <th>Reference</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Direction</th>
            <th>Status</th>
            <th>Date</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($history['rows'] as $txn):
          $isCredit = (int)$txn['to_account_id'] === (int)$account['id'];
          $tIcon = match($txn['type']) { 'deposit'=>'⬇️', 'withdrawal'=>'⬆️', default=>'↔️' };
          $statusPillMap = ['completed'=>'hp-completed','pending'=>'hp-pending','failed'=>'hp-failed','rejected'=>'hp-failed'];
          $sp = $statusPillMap[$txn['status']] ?? 'hp-gray';
        ?>
          <tr>
            <td><code style="font-size:.72rem;color:var(--color-text-muted)"><?= htmlspecialchars($txn['reference_number']) ?></code></td>
            <td><?= $tIcon ?> <?= ucfirst($txn['type']) ?></td>
            <td style="font-weight:700;color:<?= $isCredit?'#2E7D52':'#C0392B' ?>">
              <?= $isCredit ? '+' : '-' ?>₹<?= number_format((float)$txn['amount'], 2) ?>
            </td>
            <td>
              <?php if($isCredit):?>
              <span class="h-pill hp-dir-recv">← Received</span>
              <?php else:?>
              <span class="h-pill hp-dir-sent">→ Sent</span>
              <?php endif;?>
            </td>
            <td><span class="h-pill <?= $sp ?>"><?= ucfirst($txn['status']) ?></span></td>
            <td style="font-size:0.8rem;white-space:nowrap;color:var(--color-text-muted)"><?= date('d M Y', strtotime($txn['created_at'])) ?><br><span style="font-size:.72rem"><?= date('h:i A', strtotime($txn['created_at'])) ?></span></td>
            <td style="font-size:0.8rem;color:var(--color-text-muted)"><?= htmlspecialchars($txn['description'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($history['pages'] > 1): ?>
    <div class="hist-pagination">
      <ul style="display:flex;list-style:none;gap:2px;padding:0;margin:0">
        <?php for ($p = 1; $p <= $history['pages']; $p++): ?>
        <li class="h-page-item <?= $p === $history['page'] ? 'active' : '' ?>">
          <a class="h-page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
      </ul>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
