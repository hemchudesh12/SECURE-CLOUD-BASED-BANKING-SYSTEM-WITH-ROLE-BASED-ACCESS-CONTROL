<?php /** @var array $beneficiaries, $account */ ?>
<style>
.ben-page-hdr{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem}
.ben-title{font-size:20px;font-weight:700;color:var(--color-text-primary)}
.ben-sub{font-size:12px;color:var(--color-text-muted);margin-top:2px}
.ben-add-btn{display:inline-flex;align-items:center;gap:.4rem;padding:7px 16px;border-radius:var(--border-radius-md);background:#c9a84c;color:#0f1623;font-size:13px;font-weight:700;border:none;cursor:pointer;transition:background .15s}
.ben-add-btn:hover{background:#b8932f}
.ben-alpha-divider{display:flex;align-items:center;gap:.75rem;margin:1.1rem 0 .5rem}
.ben-alpha-badge{width:32px;height:32px;border-radius:50%;background:#0f1623;color:#c9a84c;font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ben-alpha-divider::after{content:'';flex:1;height:.5px;background:var(--color-border-tertiary)}
.ben-card{display:flex;align-items:center;justify-content:space-between;background:var(--color-background-primary);border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-lg);padding:.9rem 1.1rem;margin-bottom:.6rem;transition:box-shadow .18s,border-color .18s}
.ben-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.08);border-color:#c9a84c55}
.ben-avatar{width:42px;height:42px;border-radius:50%;background:#0f1623;color:#c9a84c;font-size:1rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ben-name{font-size:13.5px;font-weight:700;color:var(--color-text-primary)}
.ben-acc{font-size:11px;color:var(--color-text-muted);margin-top:1px}
.ben-bank{font-size:11px;color:var(--color-text-muted);margin-top:2px}
.ben-send-btn{display:inline-flex;align-items:center;gap:.3rem;padding:6px 14px;border-radius:var(--border-radius-md);background:#0f1623;color:#c9a84c;font-size:12px;font-weight:700;text-decoration:none;border:none;cursor:pointer;transition:background .15s}
.ben-send-btn:hover{background:#1a2540;color:#c9a84c}
.ben-del-btn{display:inline-flex;align-items:center;gap:.2rem;padding:6px 10px;border-radius:var(--border-radius-md);background:var(--color-background-secondary);color:#C0392B;font-size:12px;font-weight:600;border:.5px solid #f5b7b7;cursor:pointer;transition:all .15s;margin-left:.5rem}
.ben-del-btn:hover{background:#fcebeb}
.ben-empty{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);padding:3rem;text-align:center}
.ben-form-panel{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);margin-top:1.25rem;overflow:hidden;display:none}
.ben-form-panel.show{display:block}
.ben-form-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);background:var(--color-background-secondary)}
.ben-form-body{padding:1.25rem}
.ben-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:.85rem}
@media(max-width:580px){.ben-form-grid{grid-template-columns:1fr}}
.bfg-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-secondary);margin-bottom:.35rem}
.bfg-input{width:100%;padding:.58rem .8rem;border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-family:var(--font-sans);background:var(--color-background-primary);color:var(--color-text-primary);outline:none;transition:border-color .15s}
.bfg-input:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.ben-form-actions{display:flex;justify-content:flex-end;gap:.6rem;margin-top:1rem}
.btn-primary-sm{padding:7px 16px;background:#c9a84c;color:#0f1623;border:none;border-radius:var(--border-radius-md);font-size:12px;font-weight:700;cursor:pointer;transition:background .15s}
.btn-primary-sm:hover{background:#b8932f}
.btn-cancel-sm{padding:7px 14px;background:var(--color-background-secondary);color:var(--color-text-secondary);border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:12px;font-weight:600;cursor:pointer;transition:all .15s}
.btn-cancel-sm:hover{border-color:#C0392B;color:#C0392B}
</style>

<div class="ben-page-hdr">
  <div>
    <h1 class="ben-title">Saved Beneficiaries</h1>
    <p class="ben-sub">Manage your saved payees for quick transfers</p>
  </div>
  <button class="ben-add-btn" onclick="toggleBenForm()">＋ Add Beneficiary</button>
</div>

<?php if (empty($beneficiaries)): ?>
<div class="ben-empty">
  <div style="font-size:2.5rem;margin-bottom:.75rem">👥</div>
  <h5 style="font-weight:700;color:var(--color-text-primary);margin-bottom:.35rem">No saved beneficiaries</h5>
  <p style="font-size:13px;color:var(--color-text-muted);margin-bottom:1rem">Add payees for quick and easy transfers.</p>
  <button class="ben-add-btn" onclick="toggleBenForm()">＋ Add First Beneficiary</button>
</div>
<?php else: ?>
  <?php
  $grouped = [];
  foreach ($beneficiaries as $b) {
    $letter = strtoupper(substr($b['nickname'], 0, 1));
    $grouped[$letter][] = $b;
  }
  ksort($grouped);
  foreach ($grouped as $letter => $items):
  ?>
  <div class="ben-alpha-divider">
    <div class="ben-alpha-badge"><?= htmlspecialchars($letter) ?></div>
  </div>
  <?php foreach ($items as $b): ?>
  <div class="ben-card">
    <div style="display:flex;align-items:center;gap:.75rem;flex:1;min-width:0">
      <div class="ben-avatar"><?= strtoupper(substr($b['nickname'],0,1)) ?></div>
      <div style="min-width:0">
        <div class="ben-name"><?= htmlspecialchars($b['nickname']) ?></div>
        <div class="ben-acc"><?= htmlspecialchars($b['account_number']) ?></div>
        <div class="ben-bank">🏦 <?= htmlspecialchars($b['bank_name'] ?? 'SecureBank') ?></div>
      </div>
    </div>
    <div style="display:flex;align-items:center;flex-shrink:0">
      <a href="/banking-system/public/customer/transfer?to=<?= urlencode($b['account_number']) ?>" class="ben-send-btn">↗ Send Money</a>
      <form method="POST" action="/banking-system/public/customer/beneficiaries/remove/<?= $b['id'] ?>" onsubmit="return confirm('Remove this beneficiary?')" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <button class="ben-del-btn" title="Remove">🗑</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endforeach; ?>
<?php endif; ?>

<!-- Add Beneficiary Form Panel -->
<div class="ben-form-panel" id="benFormPanel">
  <div class="ben-form-hdr">➕ Add New Beneficiary</div>
  <div class="ben-form-body">
    <form method="POST" action="/banking-system/public/customer/beneficiaries/add" class="needs-validation" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <div class="ben-form-grid">
        <div>
          <label class="bfg-label" for="bfg-nick">Nickname <span style="color:#C0392B">*</span></label>
          <input type="text" class="bfg-input" id="bfg-nick" name="nickname" required placeholder="e.g. Jane - Personal">
        </div>
        <div>
          <label class="bfg-label" for="to_account">Account Number <span style="color:#C0392B">*</span></label>
          <input type="text" class="bfg-input" id="to_account" name="account_number" required placeholder="ACC-XXXXXXX">
          <div id="account-hint" style="font-size:11px;color:var(--color-text-muted);margin-top:.25rem"></div>
        </div>
        <div style="grid-column:1/-1">
          <label class="bfg-label" for="bfg-bank">Bank Name</label>
          <input type="text" class="bfg-input" id="bfg-bank" name="bank_name" value="SecureBank" placeholder="SecureBank">
        </div>
      </div>
      <div class="ben-form-actions">
        <button type="button" class="btn-cancel-sm" onclick="toggleBenForm()">Cancel</button>
        <button type="submit" class="btn-primary-sm">＋ Add Beneficiary</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleBenForm(){
  const p=document.getElementById('benFormPanel');
  p.classList.toggle('show');
  if(p.classList.contains('show')) p.scrollIntoView({behavior:'smooth',block:'start'});
}
// Auto-fill from URL param
const urlParams = new URLSearchParams(window.location.search);
const toAcc = urlParams.get('to');
if (toAcc) {
    document.addEventListener('DOMContentLoaded', () => {
        toggleBenForm();
        const inp = document.querySelector('[name="account_number"]');
        if(inp) inp.value = toAcc;
    });
}
</script>
