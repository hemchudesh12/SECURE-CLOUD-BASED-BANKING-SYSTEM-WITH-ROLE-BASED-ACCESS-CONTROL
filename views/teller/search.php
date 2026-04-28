<!-- Teller Account Search -->
<div class="page-header">
  <h1><i class="bi bi-search me-2 text-gold"></i>Account Search</h1>
</div>

<div class="card mb-4 fade-in-up">
  <div class="card-header"><i class="bi bi-search"></i> Search Accounts</div>
  <div class="card-body">
    <form method="POST" action="/banking-system/public/teller/search">
      <?= CsrfMiddleware::field() ?>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Account number, username, or full name..."
               value="<?= htmlspecialchars($_POST['q'] ?? $_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="/banking-system/public/teller/search" class="btn btn-outline-secondary">Clear</a>
      </div>
    </form>
  </div>
</div>

<?php if (!empty($accounts)): ?>
<div class="card fade-in-up">
  <div class="card-header">
    <i class="bi bi-people"></i> Search Results
    <span class="ms-2 badge" style="background:var(--gold);color:var(--navy)"><?= count($accounts) ?> found</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr><th>Account No.</th><th>Full Name</th><th>Username</th><th>Type</th><th>Balance</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($accounts as $acc): ?>
          <tr>
            <td><code><?= htmlspecialchars($acc['account_number']) ?></code></td>
            <td class="fw-600"><?= htmlspecialchars($acc['full_name'] ?? $acc['username']) ?></td>
            <td class="text-muted"><?= htmlspecialchars($acc['username']) ?></td>
            <td><?= ucfirst($acc['account_type']) ?></td>
            <td class="fw-600">₹<?= number_format((float)$acc['balance'], 2) ?></td>
            <td>
              <span class="badge <?= $acc['is_active'] ? 'status-completed' : 'status-rejected' ?>">
                <?= $acc['is_active'] ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="/banking-system/public/teller/deposit?account_id=<?= $acc['id'] ?>"
                   class="btn btn-sm btn-primary" title="Deposit">
                  <i class="bi bi-plus-circle"></i>
                </a>
                <a href="/banking-system/public/teller/withdrawal?account_id=<?= $acc['id'] ?>"
                   class="btn btn-sm btn-navy" title="Withdraw">
                  <i class="bi bi-dash-circle"></i>
                </a>
                <a href="/banking-system/public/teller/account/<?= $acc['id'] ?>"
                   class="btn btn-sm btn-outline-secondary" title="View">
                  <i class="bi bi-eye"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php elseif (isset($_POST['q']) || isset($_GET['q'])): ?>
<div class="alert alert-info fade-in-up">
  <i class="bi bi-info-circle me-2"></i>No accounts found matching your search.
</div>
<?php endif; ?>
