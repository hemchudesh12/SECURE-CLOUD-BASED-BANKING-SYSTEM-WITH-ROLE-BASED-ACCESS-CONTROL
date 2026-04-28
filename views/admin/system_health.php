<!-- System Health -->
<div class="page-header">
  <h1><i class="bi bi-activity me-2 text-gold"></i>System Health</h1>
  <p class="text-muted mb-0">Last refreshed: <?= date('H:i:s') ?></p>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-card fade-in-up">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon icon-green"><i class="bi bi-people"></i></div>
        <div>
          <div class="stat-value"><?= $stats['active_users'] ?></div>
          <div class="stat-label">Active Users</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card fade-in-up">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon icon-navy"><i class="bi bi-bank2"></i></div>
        <div>
          <div class="stat-value"><?= $stats['active_accounts'] ?></div>
          <div class="stat-label">Active Accounts</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card fade-in-up">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon icon-gold <?= $stats['pending_txns'] > 0 ? 'pulse' : '' ?>">
          <i class="bi bi-hourglass-split"></i>
        </div>
        <div>
          <div class="stat-value"><?= $stats['pending_txns'] ?></div>
          <div class="stat-label">Pending Txns</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card fade-in-up">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon <?= $stats['recent_failures'] > 5 ? 'icon-red' : 'icon-blue' ?>">
          <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div>
          <div class="stat-value <?= $stats['recent_failures'] > 5 ? 'text-danger' : '' ?>">
            <?= $stats['recent_failures'] ?>
          </div>
          <div class="stat-label">Auth Failures (1h)</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card fade-in-up">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon icon-navy"><i class="bi bi-journal"></i></div>
        <div>
          <div class="stat-value"><?= number_format($stats['total_audit_entries']) ?></div>
          <div class="stat-label">Audit Entries</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card fade-in-up">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon icon-green"><i class="bi bi-shield-check"></i></div>
        <div>
          <div class="stat-value text-success">Online</div>
          <div class="stat-label">DB Connection</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card fade-in-up">
  <div class="card-header"><i class="bi bi-shield-check"></i> Security Status</div>
  <div class="card-body">
    <ul class="list-unstyled mb-0" style="font-size:0.9rem">
      <li class="py-2 border-bottom d-flex justify-content-between">
        <span><i class="bi bi-shield-lock text-navy me-2"></i>Session Security (IP+UA binding)</span>
        <span class="badge status-completed">Active</span>
      </li>
      <li class="py-2 border-bottom d-flex justify-content-between">
        <span><i class="bi bi-lock text-navy me-2"></i>CSRF Protection (per-session token)</span>
        <span class="badge status-completed">Active</span>
      </li>
      <li class="py-2 border-bottom d-flex justify-content-between">
        <span><i class="bi bi-database-lock text-navy me-2"></i>PDO Prepared Statements</span>
        <span class="badge status-completed">Active</span>
      </li>
      <li class="py-2 border-bottom d-flex justify-content-between">
        <span><i class="bi bi-key text-navy me-2"></i>Bcrypt Password Hashing (cost=12)</span>
        <span class="badge status-completed">Active</span>
      </li>
      <li class="py-2 border-bottom d-flex justify-content-between">
        <span><i class="bi bi-chain text-navy me-2"></i>Audit Log SHA-256 Chain</span>
        <a href="/banking-system/public/admin/integrity" class="badge status-completed text-decoration-none">Verify Now</a>
      </li>
      <li class="py-2 d-flex justify-content-between">
        <span><i class="bi bi-clock text-navy me-2"></i>Session Timeout (30 min)</span>
        <span class="badge status-completed">Active</span>
      </li>
    </ul>
  </div>
</div>
