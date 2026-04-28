<!-- Edit Role Permissions -->
<div class="page-header">
  <h1><i class="bi bi-shield-plus me-2 text-gold"></i>Edit Permissions:
    <span style="color:var(--gold)"><?= htmlspecialchars(ucfirst($editRole['name'] ?? '')) ?></span>
  </h1>
  <nav><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="/banking-system/public/admin/roles">Roles</a></li>
    <li class="breadcrumb-item active">Edit Permissions</li>
  </ol></nav>
</div>

<?php if (!$editRole): ?>
<div class="alert alert-danger">Role not found.</div>
<?php else: ?>
<div class="card fade-in-up">
  <div class="card-header">
    <i class="bi bi-key"></i> Assign Permissions to "<?= htmlspecialchars(ucfirst($editRole['name'])) ?>"
  </div>
  <div class="card-body">
    <div class="alert alert-info mb-4">
      <i class="bi bi-info-circle me-2"></i>
      Changes take effect on the user's <strong>next login</strong> (permission cache is refreshed at login).
    </div>
    <form method="POST" action="/banking-system/public/admin/roles/<?= $editRole['id'] ?>/permissions">
      <?= CsrfMiddleware::field() ?>
      <?php
      $byModule = [];
      foreach ($permissions as $p) {
          $byModule[$p['module']][] = $p;
      }
      ?>
      <?php foreach ($byModule as $module => $perms): ?>
      <div class="mb-4">
        <h6 class="fw-700 text-uppercase mb-2" style="color:var(--navy);letter-spacing:1px;font-size:0.8rem">
          <i class="bi bi-folder2-open me-2"></i><?= $module ?>
        </h6>
        <div class="row g-2">
        <?php foreach ($perms as $perm): ?>
          <div class="col-md-6">
            <div class="form-check p-3 rounded-2 border" style="background:<?= in_array($perm['id'], $assigned) ? 'var(--gold-pale)' : '' ?>">
              <input class="form-check-input" type="checkbox"
                     name="permissions[]" value="<?= $perm['id'] ?>"
                     id="perm_<?= $perm['id'] ?>"
                     <?= in_array($perm['id'], $assigned) ? 'checked' : '' ?>>
              <label class="form-check-label ms-2" for="perm_<?= $perm['id'] ?>">
                <code style="font-size:0.78rem"><?= htmlspecialchars($perm['action_key']) ?></code>
                <small class="d-block text-muted"><?= htmlspecialchars($perm['description']) ?></small>
              </label>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save me-2"></i>Save Permissions
        </button>
        <a href="/banking-system/public/admin/roles" class="btn btn-outline-secondary">Cancel</a>
        <button type="button" class="btn btn-outline-gold ms-auto"
                onclick="document.querySelectorAll('input[type=checkbox]').forEach(c=>c.checked=true)">
          Select All
        </button>
        <button type="button" class="btn btn-outline-secondary"
                onclick="document.querySelectorAll('input[type=checkbox]').forEach(c=>c.checked=false)">
          Clear All
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
