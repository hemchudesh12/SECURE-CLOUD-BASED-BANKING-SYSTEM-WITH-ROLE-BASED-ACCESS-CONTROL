<?php /** @var array $users, $roles */ ?>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
  <div>
    <div class="page-title">User Management</div>
    <div class="page-sub">SecureBank / Users</div>
  </div>
  <a href="/banking-system/public/admin/users/create" class="btn-pill gold">+ Create User</a>
</div>

<!-- Filters -->
<div class="tbl-card" style="padding:.75rem 1rem;margin-bottom:1rem">
  <form method="GET" action="/banking-system/public/admin/users" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
    <input type="text" name="search" class="form-input" placeholder="Search username or email…"
           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="flex:1;min-width:180px">
    <select name="role" class="form-select" style="width:140px">
      <option value="">All Roles</option>
      <?php foreach ($roles as $r): ?>
      <option value="<?= $r['id'] ?>" <?= ($_GET['role']??'')==$r['id']?'selected':'' ?>><?= ucfirst($r['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="status" class="form-select" style="width:120px">
      <option value="">All Status</option>
      <option value="1" <?= ($_GET['status']??'')==='1'?'selected':'' ?>>Active</option>
      <option value="0" <?= ($_GET['status']??'')==='0'?'selected':'' ?>>Inactive</option>
    </select>
    <button type="submit" class="btn-pill gold">Filter</button>
    <a href="/banking-system/public/admin/users" class="btn-pill">Clear</a>
  </form>
</div>

<!-- Table -->
<div class="tbl-card">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem;border-bottom:.5px solid var(--color-border-tertiary)">
    <span style="font-size:13px;font-weight:600;color:var(--color-text-primary)">All Users</span>
    <span class="count-badge"><?= count($users) ?> users</span>
  </div>
  <table class="tbl" aria-label="User management table">
    <thead>
      <tr>
        <th>ID</th><th>Username</th><th>Full Name</th><th>Email</th>
        <th>Account Number</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php
    // Fetch account numbers for all users
    $pdo = Database::getInstance();
    $accStmt = $pdo->query("SELECT user_id, account_number, balance FROM accounts WHERE is_active=1");
    $accMap = [];
    foreach ($accStmt->fetchAll() as $a) { $accMap[$a['user_id']] = $a; }

    // Apply client-side-safe PHP filter
    $filtered = $users;
    if (!empty($_GET['search'])) {
        $s = strtolower($_GET['search']);
        $filtered = array_filter($filtered, fn($u) =>
            str_contains(strtolower($u['username']), $s) ||
            str_contains(strtolower($u['email']), $s) ||
            str_contains(strtolower($u['full_name'] ?? ''), $s)
        );
    }
    if (isset($_GET['role']) && $_GET['role'] !== '') {
        $filtered = array_filter($filtered, fn($u) => $u['role_id'] == $_GET['role']);
    }
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $filtered = array_filter($filtered, fn($u) => (int)$u['is_active'] === (int)$_GET['status']);
    }
    $roleColors = ['administrator'=>'badge-gold','customer'=>'badge-green'];
    foreach ($filtered as $u):
      $isSelf = (int)$u['id'] === (int)Session::get('user_id');
      $rc = $roleColors[$u['role_name']] ?? 'badge-gray';
      $cleanName = preg_replace('/\s*\([^)]+\)\s*$/', '', $u['full_name'] ?? '');
      $accInfo = $accMap[$u['id']] ?? null;
    ?>
    <tr style="<?= !$u['is_active'] ? 'opacity:.55' : '' ?>">
      <td class="td-id"><?= $u['id'] ?></td>
      <td style="font-weight:600;font-size:13px"><?= htmlspecialchars($u['username']) ?></td>
      <td style="font-size:13px"><?= htmlspecialchars($cleanName) ?></td>
      <td style="font-size:12px;color:var(--color-text-muted)"><?= htmlspecialchars($u['email']) ?></td>
      <td>
        <?php if ($accInfo): ?>
          <div style="font-family:'JetBrains Mono',monospace;font-size:11.5px;font-weight:600;color:var(--color-text-primary)">
            <?= htmlspecialchars($accInfo['account_number']) ?>
          </div>
          <div title="Format: ACC-{YEAR}{6-digit User ID}" style="font-size:10px;color:var(--color-text-muted);margin-top:1px;cursor:help">
            ACC-YEAR-ID &nbsp;·&nbsp; ₹<?= number_format((float)$accInfo['balance'], 2) ?>
          </div>
        <?php else: ?>
          <span style="font-size:11px;color:var(--color-text-muted)">— No account</span>
        <?php endif; ?>
      </td>
      <td><span class="<?= $rc ?>"><?= ucfirst($u['role_name']) ?></span></td>
      <td>
        <span class="<?= $u['is_active'] ? 'badge-success' : 'badge-failure' ?>">
          <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
        </span>
        <?php if (($u['login_failures'] ?? 0) >= 3): ?>
        <span class="badge-gold" title="<?= $u['login_failures'] ?> failed logins" style="margin-left:4px">⚠️</span>
        <?php endif; ?>
      </td>
      <td class="td-muted"><?= $u['last_login_at'] ? date('d M, H:i', strtotime($u['last_login_at'])) : '—' ?></td>
      <td>
        <div style="display:flex;gap:.3rem;align-items:center">
          <!-- Edit -->
          <a href="/banking-system/public/admin/users/<?= $u['id'] ?>/edit"
             class="btn-pill" style="padding:3px 10px;font-size:11px" title="Edit user" aria-label="Edit <?= htmlspecialchars($u['username']) ?>">✏️ Edit</a>

          <?php if (!$isSelf): ?>
          <!-- Toggle Active -->
          <form method="POST" action="/banking-system/public/admin/users/<?= $u['id'] ?>/toggle" style="display:inline">
            <?= CsrfMiddleware::field() ?>
            <button type="submit"
                    class="btn-pill" style="padding:3px 10px;font-size:11px;color:<?= $u['is_active']?'#a32d2d':'#1a7a3e' ?>;border-color:<?= $u['is_active']?'#f5b7b7':'#b8d89a' ?>"
                    onclick="return confirm('<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?> this user?')"
                    aria-label="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?> <?= htmlspecialchars($u['username']) ?>">
              <?= $u['is_active'] ? '🚫 Deactivate' : '✅ Activate' ?>
            </button>
          </form>

          <!-- Reset Password -->
          <form method="POST" action="/banking-system/public/admin/users/<?= $u['id'] ?>/reset-password" style="display:inline">
            <?= CsrfMiddleware::field() ?>
            <button type="submit"
                    class="btn-pill" style="padding:3px 10px;font-size:11px"
                    onclick="return confirm('Send password reset for <?= htmlspecialchars($u['username']) ?>?')"
                    aria-label="Reset password for <?= htmlspecialchars($u['username']) ?>">🔑 Reset</button>
          </form>

          <!-- Delete -->
          <form method="POST" action="/banking-system/public/admin/users/<?= $u['id'] ?>/delete" style="display:inline">
            <?= CsrfMiddleware::field() ?>
            <button type="submit"
                    class="btn-pill" style="padding:3px 10px;font-size:11px;color:#a32d2d;border-color:#f5b7b7"
                    onclick="return confirm('⚠️ Permanently delete user «<?= htmlspecialchars($u['username']) ?>»? This cannot be undone.')"
                    aria-label="Delete <?= htmlspecialchars($u['username']) ?>">🗑️ Delete</button>
          </form>
          <?php else: ?>
          <span style="font-size:11px;color:var(--color-text-muted);padding:3px 6px">(you)</span>
          <?php endif; ?>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($filtered)): ?>
    <tr><td colspan="9" style="text-align:center;padding:2rem;font-size:13px;color:var(--color-text-muted)">No users match the current filter.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
