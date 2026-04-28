<?php /** @var array $account, $savedBeneficiaries */ ?>
<style>
.tf-page-hdr{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem}
.tf-page-title{font-size:20px;font-weight:700;color:var(--color-text-primary)}
.tf-page-sub{font-size:12px;color:var(--color-text-muted);margin-top:2px}
.tf-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:1.25rem}
@media(max-width:860px){.tf-grid{grid-template-columns:1fr}}
.tf-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden}
.tf-card-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem}
.tf-card-body{padding:1.25rem}
.tf-form-group{margin-bottom:1.1rem}
.tf-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-secondary);margin-bottom:.4rem}
.tf-input,.tf-select{width:100%;padding:.6rem .85rem;border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13.5px;font-family:var(--font-sans);background:var(--color-background-primary);color:var(--color-text-primary);outline:none;transition:border-color .15s,box-shadow .15s}
.tf-input:focus,.tf-select:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.12)}
.tf-divider{display:flex;align-items:center;gap:.75rem;margin:.9rem 0}
.tf-divider::before,.tf-divider::after{content:'';flex:1;height:.5px;background:var(--color-border-tertiary)}
.tf-divider span{font-size:11px;font-weight:600;color:var(--color-text-muted);white-space:nowrap;padding:0 .25rem}
.tf-info-bar{display:flex;align-items:center;gap:1.5rem;background:var(--color-background-secondary);border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);padding:.7rem 1rem;margin:.9rem 0;font-size:12.5px;flex-wrap:wrap}
.tf-info-item{display:flex;align-items:center;gap:.4rem;color:var(--color-text-secondary)}
.tf-info-item strong{color:var(--color-text-primary)}
.tf-send-btn{width:100%;padding:.75rem;background:#1a3c6e;color:#fff;border:none;border-radius:var(--border-radius-md);font-size:15px;font-weight:700;cursor:pointer;letter-spacing:.02em;display:flex;align-items:center;justify-content:center;gap:.5rem;transition:background .18s}
    .tf-send-btn:hover{background:#0f2a52}
.tf-bene-card{display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem;border-bottom:.5px solid var(--color-border-tertiary)}
.tf-bene-card:last-of-type{border-bottom:none}
.tf-bene-avatar{width:36px;height:36px;border-radius:50%;background:#0f1623;color:#c9a84c;font-size:.9rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.tf-bene-name{font-size:13px;font-weight:600;color:var(--color-text-primary)}
.tf-bene-acc{font-size:11px;color:var(--color-text-muted);margin-top:1px}
.tf-bene-send{padding:5px 12px;border-radius:var(--border-radius-sm);background:#c9a84c;color:#0f1623;font-size:11px;font-weight:700;border:none;cursor:pointer;transition:background .15s}
.tf-bene-send:hover{background:#b8932f}
.tf-manage-link{display:block;text-align:center;padding:.6rem;font-size:12px;font-weight:600;color:#c9a84c;border-top:.5px solid var(--color-border-tertiary)}
.tf-manage-link:hover{color:#8a6200}
</style>

<div class="tf-page-hdr">
  <div>
    <h1 class="tf-page-title">Fund Transfer</h1>
    <p class="tf-page-sub">Send money to any account instantly</p>
  </div>
</div>

<div class="tf-grid">
  <!-- LEFT: Transfer Form -->
  <div class="tf-card">
    <div class="tf-card-hdr">↔️ New Transfer</div>
    <div class="tf-card-body">
      <form method="POST" action="/banking-system/public/customer/transfer" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <?php if (!empty($savedBeneficiaries)): ?>
        <div class="tf-form-group">
          <label class="tf-label" for="beneficiary-select">Quick Select Beneficiary</label>
          <select class="tf-select" id="beneficiary-select" onchange="fillBeneficiary(this)">
            <option value="">— Select saved beneficiary —</option>
            <?php foreach ($savedBeneficiaries as $b): ?>
            <option value="<?= htmlspecialchars($b['account_number']) ?>" data-nick="<?= htmlspecialchars($b['nickname']) ?>">
              <?= htmlspecialchars($b['nickname']) ?> — <?= htmlspecialchars($b['account_number']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="tf-divider"><span>— or enter manually —</span></div>
        <?php endif; ?>

        <div class="tf-form-group">
          <label for="to_account" class="tf-label">Destination Account Number <span style="color:#C0392B">*</span></label>
          <input type="text" class="tf-input" id="to_account" name="to_account" required placeholder="e.g. ACC-20240002" autocomplete="off">
          <div id="account-hint" style="font-size:11px;color:var(--color-text-muted);margin-top:.3rem"></div>
        </div>

        <div class="tf-form-group">
          <label for="transfer-amount" class="tf-label">Amount (₹) <span style="color:#C0392B">*</span></label>
          <input type="number" class="tf-input" id="transfer-amount" name="amount" required min="1" step="0.01" placeholder="Enter amount">
          <div id="amount-preview" style="font-size:11px;font-weight:600;color:var(--color-text-muted);margin-top:.3rem"></div>
        </div>

        <div class="tf-form-group">
          <label for="description" class="tf-label">Description / Note</label>
          <input type="text" class="tf-input" id="description" name="description" maxlength="255" placeholder="e.g. Rent payment">
        </div>

        <?php if ($account): ?>
        <div class="tf-info-bar">
          <div class="tf-info-item">💰 Available: <strong>₹<?= number_format((float)$account['balance'], 2) ?></strong></div>
          <div class="tf-info-item">🏦 Account: <strong><?= htmlspecialchars($account['account_number']) ?></strong></div>
        </div>
        <?php endif; ?>

        <button type="submit" class="tf-send-btn">
          ➤ Send Money
        </button>
      </form>
    </div>
  </div>

  <!-- RIGHT: Info + Beneficiaries -->
  <div>

    <!-- Saved Beneficiaries -->
    <?php if (!empty($savedBeneficiaries)): ?>
    <div class="tf-card">
      <div class="tf-card-hdr">👥 Saved Beneficiaries</div>
      <?php foreach (array_slice($savedBeneficiaries, 0, 5) as $b): ?>
      <div class="tf-bene-card">
        <div style="display:flex;align-items:center;gap:.65rem">
          <div class="tf-bene-avatar"><?= strtoupper(substr($b['nickname'],0,1)) ?></div>
          <div>
            <div class="tf-bene-name"><?= htmlspecialchars($b['nickname']) ?></div>
            <div class="tf-bene-acc"><?= htmlspecialchars($b['account_number']) ?></div>
          </div>
        </div>
        <button class="tf-bene-send" onclick="quickTransfer('<?= htmlspecialchars($b['account_number']) ?>')">Send</button>
      </div>
      <?php endforeach; ?>
      <a href="/banking-system/public/customer/beneficiaries" class="tf-manage-link">Manage All →</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function fillBeneficiary(sel) {
    const val = sel.value;
    if (val) document.getElementById('to_account').value = val;
}
function quickTransfer(accNum) {
    document.getElementById('to_account').value = accNum;
    document.getElementById('to_account').dispatchEvent(new Event('input'));
    document.getElementById('to_account').scrollIntoView({behavior:'smooth'});
}
</script>
