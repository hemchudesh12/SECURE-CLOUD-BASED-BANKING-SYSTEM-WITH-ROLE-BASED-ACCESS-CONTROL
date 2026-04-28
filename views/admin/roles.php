<?php /** @var array $roles, $permissions */ ?>

<div class="page-header" style="margin-bottom:1rem">
  <div class="page-title">Roles &amp; Permissions</div>
  <div class="page-sub">SecureBank / Roles &amp; Perms</div>
</div>

<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:16px;align-items:start">

  <!-- LEFT: Roles table + Add role -->
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="tbl-card">
      <div style="padding:.75rem 1rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:13px;font-weight:600;color:var(--color-text-primary)">All Roles</div>
      <table class="tbl">
        <thead><tr><th>Role</th><th>Description</th><th>Perms</th></tr></thead>
        <tbody>
        <?php
        $roleColors = ['administrator'=>'badge-gold','customer'=>'badge-green'];
        foreach ($roles as $r):
          $rc = $roleColors[$r['name']] ?? 'badge-gray';
        ?>
        <tr>
          <td><span class="<?= $rc ?>"><?= ucfirst($r['name']) ?></span></td>
          <td style="font-size:12px;color:var(--color-text-secondary)"><?= htmlspecialchars($r['description']) ?></td>
          <td>
            <a href="/banking-system/public/admin/roles/<?= $r['id'] ?>/permissions"
               class="btn-pill" style="padding:3px 10px;font-size:11px" title="Edit permissions">✏️ Edit</a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Add Role -->
    <div class="card-box">
      <div class="card-box-title" style="margin-bottom:.65rem">➕ Add New Role</div>
      <form method="POST" action="/banking-system/public/admin/roles">
        <?= CsrfMiddleware::field() ?>
        <div style="display:flex;flex-direction:column;gap:.5rem">
          <input type="text" name="name" class="form-input" placeholder="Role name (e.g. auditor)" required>
          <input type="text" name="description" class="form-input" placeholder="Description">
          <button type="submit" class="btn-pill gold" style="width:100%;justify-content:center;padding:.5rem">Add Role</button>
        </div>
      </form>
    </div>
  </div>

  <!-- RIGHT: Permissions grouped by module -->
  <div class="tbl-card">
    <div style="padding:.75rem 1rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:13px;font-weight:600;color:var(--color-text-primary)">
      All Permissions <span class="count-badge" style="margin-left:.5rem"><?= count($permissions) ?></span>
    </div>

    <?php
    $byModule = [];
    foreach ($permissions as $p) {
        $byModule[$p['module']][] = $p;
    }
    $moduleIcons = ['admin'=>'⚙️','auth'=>'🔐','customer'=>'👤'];
    foreach ($byModule as $module => $perms):
      $icon = $moduleIcons[$module] ?? '📌';
      $gid  = 'perm-group-' . $module;
    ?>
    <div class="perm-group">
      <button class="perm-group-header" onclick="toggleGroup('<?= $gid ?>')" aria-expanded="true"
              style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:.6rem 1rem;background:var(--color-background-secondary);border:none;border-bottom:.5px solid var(--color-border-tertiary);cursor:pointer;font-family:var(--font-sans)">
        <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-secondary)">
          <?= $icon ?> <?= $module ?> <span class="count-badge" style="margin-left:.4rem"><?= count($perms) ?></span>
        </span>
        <span id="<?= $gid ?>-chevron" style="font-size:11px;color:var(--color-text-muted)">▲</span>
      </button>
      <div id="<?= $gid ?>" style="display:block">
        <?php foreach ($perms as $perm): ?>
        <div style="display:flex;align-items:baseline;gap:.75rem;padding:.55rem 1rem;border-bottom:.5px solid var(--color-border-tertiary)">
          <code style="font-size:11.5px;font-family:var(--font-mono);color:var(--color-text-primary);white-space:nowrap;flex-shrink:0"><?= htmlspecialchars($perm['action_key']) ?></code>
          <span style="font-size:12px;color:var(--color-text-muted)"><?= htmlspecialchars($perm['description']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<script>
function toggleGroup(id) {
    const el  = document.getElementById(id);
    const chv = document.getElementById(id + '-chevron');
    if (!el) return;
    const open = el.style.display !== 'none';
    el.style.display   = open ? 'none' : 'block';
    chv.textContent    = open ? '▼' : '▲';
    el.closest('.perm-group').querySelector('.perm-group-header').setAttribute('aria-expanded', !open);
}
</script>
