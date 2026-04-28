<!-- Edit User -->
<style>
.eu-page-hdr{margin-bottom:1.5rem}
.eu-page-title{font-size:20px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem}
.eu-breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:12px;color:var(--color-text-muted);margin-top:.4rem}
.eu-breadcrumb a{color:var(--color-text-muted);text-decoration:none;transition:color .15s}
.eu-breadcrumb a:hover{color:#c9a84c}
.eu-breadcrumb .sep{color:var(--color-border-tertiary)}
.eu-breadcrumb .cur{color:var(--color-text-primary);font-weight:600}
.eu-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:1.25rem}
@media(max-width:860px){.eu-grid{grid-template-columns:1fr}}
.eu-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden}
.eu-card-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem;background:var(--color-background-secondary)}
.eu-card-body{padding:1.25rem}
.eu-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-secondary);margin-bottom:.35rem}
.eu-input,.eu-select{width:100%;padding:.6rem .85rem;border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-family:var(--font-sans);background:var(--color-background-primary);color:var(--color-text-primary);outline:none;transition:border-color .15s,box-shadow .15s;margin-bottom:.9rem}
.eu-input:focus,.eu-select:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.eu-input:disabled{background:var(--color-background-secondary);color:var(--color-text-muted);cursor:not-allowed}
.eu-hint{font-size:11px;color:#b8860b;background:#fff8e7;border:.5px solid #f0c040;border-radius:var(--border-radius-sm);padding:4px 8px;margin-top:-.6rem;margin-bottom:.9rem}
.eu-actions{display:flex;align-items:center;justify-content:flex-end;gap:.6rem;margin-top:.5rem;padding-top:.75rem;border-top:.5px solid var(--color-border-tertiary)}
.eu-btn-primary{padding:9px 20px;background:#c9a84c;color:#0f1623;border:none;border-radius:var(--border-radius-md);font-size:13px;font-weight:700;cursor:pointer;transition:background .15s;display:inline-flex;align-items:center;gap:.4rem}
.eu-btn-primary:hover{background:#b8932f}
.eu-btn-cancel{padding:9px 16px;background:none;color:var(--color-text-secondary);border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;text-decoration:none;display:inline-flex;align-items:center}
.eu-btn-cancel:hover{border-color:#C0392B;color:#C0392B}
.eu-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1.25rem}
.eu-info-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--color-text-muted);margin-bottom:2px}
.eu-info-val{font-size:13px;font-weight:600;color:var(--color-text-primary)}
.eu-pill{display:inline-flex;align-items:center;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px}
.eu-pill-active{background:#eaf3de;color:#1a7a3e}
.eu-pill-inactive{background:#fcebeb;color:#a32d2d}
.eu-pill-warn{background:#fcebeb;color:#a32d2d}
</style>

<div class="eu-page-hdr">
  <h1 class="eu-page-title">✏️ Edit User</h1>
  <nav class="eu-breadcrumb" aria-label="Breadcrumb">
    <a href="/banking-system/public/admin/users">Users</a>
    <span class="sep">›</span>
    <span class="cur">Edit: <?= htmlspecialchars($editUser['username'] ?? '') ?></span>
  </nav>
</div>

<?php if (!$editUser): ?>
<div style="padding:1.1rem;background:#fcebeb;border:.5px solid #f5b7b7;border-radius:var(--border-radius-md);color:#a32d2d;font-size:13px;font-weight:600">
  ⚠️ User not found.
</div>
<?php else: ?>
<div class="eu-grid">
  <!-- Left: Edit Form -->
  <div class="eu-card">
    <div class="eu-card-hdr">⚙️ Edit: <?= htmlspecialchars($editUser['username']) ?></div>
    <div class="eu-card-body">
      <form method="POST" action="/banking-system/public/admin/users/<?= $editUser['id'] ?>/edit" class="needs-validation" novalidate>
        <?= CsrfMiddleware::field() ?>

        <div>
          <label class="eu-label">Username (cannot change)</label>
          <input type="text" class="eu-input" value="<?= htmlspecialchars($editUser['username']) ?>" disabled>
        </div>

        <div>
          <label for="eu_full_name" class="eu-label">Full Name</label>
          <input type="text" id="eu_full_name" name="full_name" class="eu-input"
                 value="<?= htmlspecialchars($editUser['full_name'] ?? '') ?>">
        </div>

        <div>
          <label for="eu_email" class="eu-label">Email Address <span style="color:#C0392B">*</span></label>
          <input type="email" id="eu_email" name="email" class="eu-input" required
                 value="<?= htmlspecialchars($editUser['email']) ?>">
        </div>

        <div>
          <label for="eu_role" class="eu-label">Role</label>
          <select id="eu_role" name="role_id" class="eu-select"
                  <?= $editUser['id'] === (int)Session::get('user_id') ? 'disabled' : '' ?>>
            <?php foreach ($roles as $role): ?>
              <option value="<?= $role['id'] ?>" <?= $editUser['role_id']==$role['id']?'selected':'' ?>>
                <?= ucfirst($role['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if ($editUser['id'] === (int)Session::get('user_id')): ?>
            <div class="eu-hint">⚠️ You cannot change your own role.</div>
            <input type="hidden" name="role_id" value="<?= $editUser['role_id'] ?>">
          <?php endif; ?>
        </div>

        <div class="eu-actions">
          <a href="/banking-system/public/admin/users" class="eu-btn-cancel">Cancel</a>
          <button type="submit" class="eu-btn-primary">💾 Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Right: Account Info -->
  <div class="eu-card">
    <div class="eu-card-hdr">ℹ️ Account Info</div>
    <div class="eu-card-body">
      <div class="eu-info-grid">
        <div>
          <div class="eu-info-label">User ID</div>
          <div class="eu-info-val">#<?= $editUser['id'] ?></div>
        </div>
        <div>
          <div class="eu-info-label">Status</div>
          <div>
            <span class="eu-pill <?= $editUser['is_active'] ? 'eu-pill-active' : 'eu-pill-inactive' ?>">
              <?= $editUser['is_active'] ? '● Active' : '● Inactive' ?>
            </span>
          </div>
        </div>
        <div>
          <div class="eu-info-label">Login Failures</div>
          <div class="eu-info-val <?= $editUser['login_failures'] > 0 ? '' : '' ?>">
            <?php if ($editUser['login_failures'] > 0): ?>
            <span class="eu-pill eu-pill-warn"><?= $editUser['login_failures'] ?> failures</span>
            <?php else: ?>
            <span style="color:#2E7D52;font-weight:700">0 — Clean</span>
            <?php endif; ?>
          </div>
        </div>
        <div>
          <div class="eu-info-label">Member Since</div>
          <div class="eu-info-val"><?= date('d M Y', strtotime($editUser['created_at'])) ?></div>
        </div>
        <div style="grid-column:1/-1">
          <div class="eu-info-label">Last Login</div>
          <div class="eu-info-val"><?= $editUser['last_login_at'] ? date('d M Y, H:i', strtotime($editUser['last_login_at'])) : '—' ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
