<!-- Teller Deposit -->
<div class="page-header">
  <h1><i class="bi bi-plus-circle me-2 text-gold"></i>Cash Deposit</h1>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card fade-in-up">
      <div class="card-header"><i class="bi bi-cash-stack"></i> Process Deposit</div>
      <div class="card-body">

        <?php if ($account): ?>
        <!-- Selected account info -->
        <div class="p-3 rounded-3 mb-4" style="background:var(--gold-pale);border:1px solid var(--gold)">
          <div class="row">
            <div class="col">
              <small class="text-muted d-block">Account Holder</small>
              <strong><?= htmlspecialchars($account['full_name'] ?? '') ?></strong>
            </div>
            <div class="col">
              <small class="text-muted d-block">Account Number</small>
              <strong><?= htmlspecialchars($account['account_number']) ?></strong>
            </div>
            <div class="col">
              <small class="text-muted d-block">Current Balance</small>
              <strong class="text-success">₹<?= number_format((float)$account['balance'], 2) ?></strong>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <form method="POST" action="/banking-system/public/teller/deposit" class="needs-validation" novalidate>
          <?= CsrfMiddleware::field() ?>

          <div class="mb-3">
            <label for="account_id" class="form-label">Select Account</label>
            <select id="account_id" name="account_id" class="form-select" required>
              <option value="">-- Select customer account --</option>
              <?php foreach ($accounts as $acc): ?>
                <option value="<?= $acc['id'] ?>" <?= ($account && $account['id']==$acc['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($acc['account_number'] . ' — ' . ($acc['full_name'] ?? '')) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Please select an account.</div>
          </div>

          <div class="mb-3">
            <label for="amount" class="form-label">Deposit Amount (₹)</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-currency-rupee"></i></span>
              <input type="number" id="amount" name="amount" class="form-control form-control-lg"
                     placeholder="0.00" required min="1" step="0.01">
              <div class="invalid-feedback">Enter a valid amount.</div>
            </div>
          </div>

          <div class="mb-4">
            <label for="description" class="form-label">Description</label>
            <input type="text" id="description" name="description" class="form-control"
                   placeholder="e.g. Cash deposit at counter">
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-plus-circle me-2"></i>Process Deposit
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card fade-in-up">
      <div class="card-header"><i class="bi bi-info-circle"></i> Deposit Guidelines</div>
      <div class="card-body" style="font-size:0.875rem">
        <ul class="list-unstyled">
          <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Verify customer identity before depositing</li>
          <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Count cash twice before processing</li>
          <li class="mb-2"><i class="bi bi-exclamation-triangle text-warning me-2"></i>All deposits are audit-logged with your name</li>
          <li class="mb-2"><i class="bi bi-lock text-navy me-2"></i>Deposits are immediately reflected in balance</li>
        </ul>
      </div>
    </div>
  </div>
</div>
