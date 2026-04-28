<?php
/**
 * Customer Profile — view and edit personal info + change password.
 * @var array $account
 */
$userId = Session::get('user_id');
$pdo    = Database::getInstance();
$user   = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$userId]);
$user = $user->fetch();
?>
<style>
.prof-page-hdr{margin-bottom:1.5rem}
.prof-page-title{font-size:20px;font-weight:700;color:var(--color-text-primary)}
.prof-page-sub{font-size:12px;color:var(--color-text-muted);margin-top:2px}
.prof-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem}
@media(max-width:860px){.prof-grid{grid-template-columns:1fr}}
.prof-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden;margin-bottom:1.1rem}
.prof-card:last-child{margin-bottom:0}
.prof-card-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem}
.prof-card-body{padding:1.25rem}
.prof-avatar-wrap{text-align:center;margin-bottom:1.25rem}
.prof-avatar{width:74px;height:74px;border-radius:50%;background:#0f1623;color:#c9a84c;font-size:1.9rem;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto;border:2.5px solid #c9a84c44}
.prof-avatar-name{font-size:14px;font-weight:700;color:var(--color-text-primary);margin-top:.5rem}
.prof-avatar-user{font-size:12px;color:var(--color-text-muted)}
.pf-divider{height:.5px;background:var(--color-border-tertiary);margin:.85rem 0}
.pf-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-secondary);margin-bottom:.35rem}
.pf-input{width:100%;padding:.58rem .8rem;border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-family:var(--font-sans);background:var(--color-background-primary);color:var(--color-text-primary);outline:none;transition:border-color .15s;margin-bottom:.85rem}
.pf-input:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.pf-btn-primary{display:flex;align-items:center;justify-content:flex-end}
.pf-submit-btn{padding:8px 20px;background:#c9a84c;color:#0f1623;border:none;border-radius:var(--border-radius-md);font-size:13px;font-weight:700;cursor:pointer;transition:background .15s}
.pf-submit-btn:hover{background:#b8932f}
.acc-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1.5rem}
.acc-info-item-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--color-text-muted);margin-bottom:2px}
.acc-info-item-val{font-size:13px;font-weight:700;color:var(--color-text-primary)}
.acc-balance-big{font-size:1.9rem;font-weight:700;color:#c9a84c;margin-bottom:.75rem}
.acc-info-bg{background:linear-gradient(135deg,#0f1623,#1a2540);border-radius:var(--border-radius-md);padding:1.1rem 1.25rem;color:#fff}
.acc-info-bg .acc-info-item-val{color:#fff}
.acc-info-bg .acc-info-item-label{color:rgba(255,255,255,.5)}
.last-login-bar{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);padding:.65rem 1.25rem;display:flex;flex-wrap:wrap;gap:1.5rem;font-size:11px;color:var(--color-text-muted);margin-top:1rem}
</style>

<div class="prof-page-hdr">
  <h1 class="prof-page-title">My Profile</h1>
  <p class="prof-page-sub">Manage your personal information and account settings</p>
</div>

<div class="prof-grid">
  <!-- Left: Personal Info + Password -->
  <div>
    <div class="prof-card">
      <div class="prof-card-hdr">👤 Personal Information</div>
      <div class="prof-card-body">
        <div class="prof-avatar-wrap">
          <div class="prof-avatar"><?= strtoupper(substr($user['full_name']??'U',0,1)) ?></div>
          <div class="prof-avatar-name"><?= htmlspecialchars($user['full_name']??'') ?></div>
          <div class="prof-avatar-user">@<?= htmlspecialchars($user['username']??'') ?></div>
        </div>
        <div class="pf-divider"></div>
        <form method="POST" action="/banking-system/public/customer/profile" class="needs-validation" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']??'') ?>">
          <div>
            <label class="pf-label" for="full_name">Full Name</label>
            <input type="text" class="pf-input" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']??'') ?>" required>
          </div>
          <div>
            <label class="pf-label" for="email">Email Address</label>
            <input type="email" class="pf-input" id="email" name="email" value="<?= htmlspecialchars($user['email']??'') ?>" required>
          </div>
          <div>
            <label class="pf-label" for="phone">Phone Number</label>
            <input type="tel" class="pf-input" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>">
          </div>
          <div class="pf-btn-primary">
            <button type="submit" class="pf-submit-btn">💾 Update Profile</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Change Password -->
    <div class="prof-card">
      <div class="prof-card-hdr">🔑 Change Password</div>
      <div class="prof-card-body">
        <form method="POST" action="/banking-system/public/customer/profile/password" class="needs-validation" novalidate>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']??'') ?>">
          <div>
            <label class="pf-label" for="current_password">Current Password</label>
            <input type="password" class="pf-input" id="current_password" name="current_password" required autocomplete="current-password">
          </div>
          <div>
            <label class="pf-label" for="password-new">New Password</label>
            <input type="password" class="pf-input" id="password-new" name="new_password" required autocomplete="new-password" minlength="8" placeholder="Min. 8 chars, upper+lower+number">
            <div id="password-strength" style="font-size:11px;color:var(--color-text-muted);margin-top:-0.6rem;margin-bottom:.85rem"></div>
          </div>
          <div>
            <label class="pf-label" for="confirm_password">Confirm New Password</label>
            <input type="password" class="pf-input" id="confirm_password" name="confirm_password" required autocomplete="new-password">
          </div>
          <div class="pf-btn-primary">
            <button type="submit" class="pf-submit-btn" style="background:#0f1623;color:#c9a84c">🔒 Change Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Right: Account Details -->
  <div>
    <?php if ($account): ?>
    <div class="prof-card">
      <div class="prof-card-hdr">🏦 Account Details</div>
      <div class="prof-card-body">
        <div class="acc-info-bg">
          <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:rgba(255,255,255,.45);margin-bottom:.3rem">Current Balance</div>
          <div class="acc-balance-big">₹<?= number_format((float)$account['balance'],2) ?></div>
          <div class="pf-divider" style="border-color:rgba(255,255,255,.12)"></div>
          <div class="acc-info-grid">
            <div>
              <div class="acc-info-item-label">Account Number</div>
              <div class="acc-info-item-val" style="font-family:'JetBrains Mono',monospace;font-size:11.5px"><?= htmlspecialchars($account['account_number']) ?></div>
            </div>
            <div>
              <div class="acc-info-item-label">Account Type</div>
              <div class="acc-info-item-val"><?= ucfirst($account['account_type']??'savings') ?></div>
            </div>
            <div>
              <div class="acc-info-item-label">Interest Rate</div>
              <div class="acc-info-item-val"><?= $account['interest_rate'] ?>% p.a.</div>
            </div>
            <div>
              <div class="acc-info-item-label">Member Since</div>
              <div class="acc-info-item-val"><?= date('d M Y', strtotime($account['created_at'])) ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Last Login Info -->
<div class="last-login-bar">
  <span>🕐 Last login: <?= $user['last_login_at'] ? date('d M Y H:i', strtotime($user['last_login_at'])) : 'N/A' ?></span>
  <span>📍 Last IP: <?= htmlspecialchars($user['last_login_ip']??'N/A') ?></span>
  <span>📅 Member since: <?= date('d M Y', strtotime($user['created_at'])) ?></span>
</div>
