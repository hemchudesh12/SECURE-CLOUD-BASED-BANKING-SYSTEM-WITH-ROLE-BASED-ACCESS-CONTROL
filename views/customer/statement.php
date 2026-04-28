<?php /** @var array $account */ ?>
<style>
.stmt-page-hdr{margin-bottom:1.5rem}
.stmt-page-title{font-size:20px;font-weight:700;color:var(--color-text-primary)}
.stmt-page-sub{font-size:12px;color:var(--color-text-muted);margin-top:2px}
.stmt-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem}
@media(max-width:760px){.stmt-grid{grid-template-columns:1fr}}
.stmt-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden}
.stmt-card-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem}
.stmt-card-body{padding:1.25rem}
.stmt-date-row{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:1.1rem}
.sd-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-secondary);margin-bottom:.35rem}
.sd-input{width:100%;padding:.58rem .8rem;border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-family:var(--font-sans);background:var(--color-background-primary);color:var(--color-text-primary);outline:none;transition:border-color .15s}
.sd-input:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.stmt-divider{height:.5px;background:var(--color-border-tertiary);margin:1rem 0}
.stmt-btn-row{display:flex;gap:.75rem}
.stmt-pdf-btn{flex:1;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:9px 14px;background:#C0392B;color:#fff;border:none;border-radius:var(--border-radius-md);font-size:13px;font-weight:700;cursor:pointer;transition:background .15s}
.stmt-pdf-btn:hover{background:#a93226}
.stmt-csv-btn{flex:1;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:9px 14px;background:#2E7D52;color:#fff;border:none;border-radius:var(--border-radius-md);font-size:13px;font-weight:700;cursor:pointer;transition:background .15s}
.stmt-csv-btn:hover{background:#1e5c3b}
.stmt-info-card{background:var(--color-background-secondary);border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-lg);padding:1.1rem 1.25rem}
.stmt-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1.5rem}
.stmt-info-item-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--color-text-muted);margin-bottom:2px}
.stmt-info-item-val{font-size:13.5px;font-weight:700;color:var(--color-text-primary)}
.stmt-balance-big{font-size:1.8rem;font-weight:700;color:#c9a84c}
</style>

<div class="stmt-page-hdr">
  <h1 class="stmt-page-title">Account Statement</h1>
  <p class="stmt-page-sub">Download your account statement as PDF or CSV</p>
</div>

<div class="stmt-grid">
  <!-- Download Card -->
  <div class="stmt-card">
    <div class="stmt-card-hdr">📥 Download Statement</div>
    <div class="stmt-card-body">
      <form id="statementForm">
        <div class="stmt-date-row">
          <div>
            <label class="sd-label" for="from_date">From Date</label>
            <input type="date" class="sd-input" id="from_date" value="<?= date('Y-m-01') ?>" max="<?= date('Y-m-d') ?>">
          </div>
          <div>
            <label class="sd-label" for="to_date">To Date</label>
            <input type="date" class="sd-input" id="to_date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="stmt-divider"></div>
        <div class="stmt-btn-row">
          <button type="button" class="stmt-pdf-btn" onclick="downloadStatement('pdf')">
            📄 PDF Statement
          </button>
          <button type="button" class="stmt-csv-btn" onclick="downloadStatement('csv')">
            📊 CSV Export
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Account Info Card -->
  <?php if ($account): ?>
  <div class="stmt-card">
    <div class="stmt-card-hdr">🏦 Account Information</div>
    <div class="stmt-card-body">
      <div class="stmt-info-card">
        <div style="margin-bottom:.85rem">
          <div class="stmt-info-item-label">Current Balance</div>
          <div class="stmt-balance-big">₹<?= number_format((float)$account['balance'], 2) ?></div>
        </div>
        <div class="stmt-divider"></div>
        <div class="stmt-info-grid">
          <div>
            <div class="stmt-info-item-label">Account Number</div>
            <div class="stmt-info-item-val" style="font-family:'JetBrains Mono',monospace;font-size:12px"><?= htmlspecialchars($account['account_number']) ?></div>
          </div>
          <div>
            <div class="stmt-info-item-label">Account Type</div>
            <div class="stmt-info-item-val"><?= ucfirst($account['account_type'] ?? 'savings') ?></div>
          </div>
          <div>
            <div class="stmt-info-item-label">Account Holder</div>
            <div class="stmt-info-item-val"><?= htmlspecialchars($account['full_name'] ?? '') ?></div>
          </div>
          <div>
            <div class="stmt-info-item-label">Member Since</div>
            <div class="stmt-info-item-val"><?= date('d M Y', strtotime($account['created_at'] ?? 'now')) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
function downloadStatement(format) {
    const from = document.getElementById('from_date').value;
    const to   = document.getElementById('to_date').value;
    if (!from || !to) { alert('Please select date range.'); return; }
    if (from > to)    { alert('From date must be before To date.'); return; }
    const url = `/banking-system/public/customer/statement/${format}?from_date=${from}&to_date=${to}`;
    window.location.href = url;
}
</script>
