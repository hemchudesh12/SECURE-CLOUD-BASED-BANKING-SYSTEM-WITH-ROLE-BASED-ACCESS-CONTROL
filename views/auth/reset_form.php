<?php /* Password Reset Form — shown when token is valid */ ?>
<h2 class="text-center mb-1" style="font-size:1.25rem;font-weight:700;color:var(--navy)">Set New Password</h2>
<p class="text-center text-muted mb-4" style="font-size:0.85rem">Choose a strong new password</p>

<?php if (!$valid): ?>
  <div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle me-2"></i>
    This reset link is invalid or has expired. Please request a new one.
  </div>
  <a href="/banking-system/public/reset-password" class="btn btn-primary w-100">Request New Link</a>
<?php else: ?>
<form method="POST" action="/banking-system/public/reset-password/<?= htmlspecialchars($params['token'], ENT_QUOTES) ?>" class="needs-validation" novalidate>
  <?= CsrfMiddleware::field() ?>
  <div class="mb-3">
    <label for="password" class="form-label">New Password</label>
    <input type="password" id="password" name="password" class="form-control"
           placeholder="Min 8 chars, uppercase, number, symbol" required minlength="8">
  </div>
  <div class="mb-4">
    <label for="confirm_password" class="form-label">Confirm Password</label>
    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
           placeholder="Repeat new password" required>
    <div class="invalid-feedback">Passwords must match.</div>
  </div>
  <button type="submit" class="btn btn-primary w-100 py-2">
    <i class="bi bi-shield-check me-2"></i>Update Password
  </button>
</form>
<?php endif; ?>
