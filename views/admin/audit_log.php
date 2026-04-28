<?php /** @var array $logs, $total, $page, $pages */ ?>

<div class="page-header">
  <div class="page-title">Audit Logs</div>
  <div class="page-sub">SecureBank / Audit Logs</div>
</div>

<!-- Toolbar -->
<div class="toolbar">
  <form method="GET" action="/banking-system/public/admin/audit" style="display:contents">
    <input type="text" name="action" class="form-input" placeholder="Action (e.g. LOGIN)" value="<?= htmlspecialchars($_GET['action']??'') ?>" style="flex:none;width:160px">
    <input type="text" name="username" class="form-input" placeholder="Username" value="<?= htmlspecialchars($_GET['username']??'') ?>" style="flex:none;width:130px">
    <select name="outcome" class="form-select" style="flex:none;width:110px">
      <option value="">All outcomes</option>
      <option value="success"  <?= ($_GET['outcome']??'')==='success' ?'selected':'' ?>>Success</option>
      <option value="failure"  <?= ($_GET['outcome']??'')==='failure' ?'selected':'' ?>>Failure</option>
    </select>
    <input type="date" name="from_date" class="form-input" value="<?= htmlspecialchars($_GET['from_date']??'') ?>" style="flex:none">
    <input type="date" name="to_date"   class="form-input" value="<?= htmlspecialchars($_GET['to_date']??'') ?>"   style="flex:none">
    <button type="submit" class="btn-pill gold">Filter</button>
    <a href="/banking-system/public/admin/audit" class="btn-pill">Clear</a>
  </form>
  <span class="count-badge"><?= (int)$total ?> entries</span>
  <a href="/banking-system/public/admin/audit/export" class="btn-pill" style="margin-left:auto">↓ Export CSV</a>
</div>

<!-- Table -->
<div class="tbl-card">
  <?php if (empty($logs)): ?>
    <div style="padding:2.5rem;text-align:center;font-size:13px;color:var(--color-text-muted)">No audit entries match the filter.</div>
  <?php else: ?>
  <table class="tbl" aria-label="Audit logs">
    <thead>
      <tr>
        <th>ID</th>
        <th>Action</th>
        <th>User</th>
        <th>Outcome</th>
        <th>Entity</th>
        <th>IP</th>
        <th>Timestamp</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($logs as $log):
      $isFailure = $log['outcome'] === 'failure';
      $isSusp    = in_array($log['action'], ['UNAUTHORIZED_ACCESS','CSRF_FAILURE','SESSION_HIJACK_DETECTED','LOGIN_FAILURE']);
      $entity    = ($log['entity_type'] ?? '—') . ($log['entity_id'] ? ' #'.$log['entity_id'] : '');
    ?>
    <tr class="<?= $isFailure ? 'row-failure' : '' ?>" <?= $isSusp ? 'title="Suspicious activity"' : '' ?>>
      <td class="td-id"><?= $log['id'] ?></td>
      <td class="td-mono"><?= htmlspecialchars($log['action']) ?><?= $isSusp ? ' ⚠️' : '' ?></td>
      <td style="font-size:13px"><?= htmlspecialchars($log['username'] ?? '—') ?></td>
      <td>
        <?php if ($isFailure): ?>
          <span class="badge-failure">failure</span>
        <?php else: ?>
          <span class="badge-success">success</span>
        <?php endif; ?>
      </td>
      <td style="font-size:12px;color:var(--color-text-secondary)"><?= htmlspecialchars($entity) ?></td>
      <td class="td-muted"><?= htmlspecialchars($log['source_ip'] ?? '') ?></td>
      <td class="td-muted"><?= date('d M Y H:i:s', strtotime($log['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Pagination -->
  <?php if ($pages > 1): ?>
  <div style="display:flex;align-items:center;justify-content:center;padding:.6rem 0;gap:.3rem">
    <?php for ($p = max(1, $page - 3); $p <= min($pages, $page + 3); $p++): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
       class="page-btn <?= $p === $page ? 'active' : '' ?>" aria-label="Page <?= $p ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>
