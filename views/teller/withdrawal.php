<!-- Teller Withdrawal -->
<div class="page-header">
  <h1><i class="bi bi-dash-circle me-2 text-gold"></i>Cash Withdrawal</h1>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card fade-in-up">
      <div class="card-header"><i class="bi bi-cash"></i> Process Withdrawal</div>
      <div class="card-body">

        <?php if ($account): ?>
        <div class="p-3 rounded-3 mb-4" style="background:rgba(220,53,69,0.06);border:1px solid var(--danger)">
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
              <small class="text-muted d-block">Available Balance</small>
              <strong class="text-danger">₹<?= number_format((float)$account['balance'], 2) ?></strong>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <form method="POST" action="/banking-system/public/teller/withdrawal" class="needs-validation" novalidate>
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
          </div>

          <div class="mb-3">
            <label for="amount" class="form-label">Withdrawal Amount (₹)</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-currency-rupee"></i></span>
              <input type="number" id="amount" name="amount" class="form-control form-control-lg"
                     placeholder="0.00" required min="1" step="0.01"
                     max="<?= $account ? $account['balance'] : '' ?>">
            </div>
            <?php if ($account): ?>
            <div class="form-text text-danger">Max available: ₹<?= number_format((float)$account['balance'], 2) ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-4">
            <label for="description" class="form-label">Description</label>
            <input type="text" id="description" name="description" class="form-control"
                   placeholder="e.g. Counter withdrawal">
          </div>

          <div class="approval-banner mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Withdrawals above <strong>₹50,000</strong> require supervisor approval.
          </div>

          <button type="submit" class="btn btn-danger w-100 py-2">
            <i class="bi bi-dash-circle me-2"></i>Process Withdrawal
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
