<!-- Password Reset Request -->
<h2 class="text-center mb-1" style="font-size:1.25rem;font-weight:700;color:var(--navy)">Reset Password</h2>
<p class="text-center text-muted mb-4" style="font-size:0.85rem">Enter your email to receive a reset link</p>

<form method="POST" action="/banking-system/public/reset-password" class="needs-validation" novalidate>
  <?= CsrfMiddleware::field() ?>
  <div class="mb-4">
    <label for="email" class="form-label">Email Address</label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-envelope"></i></span>
      <input type="email" id="email" name="email" class="form-control" placeholder="your@email.com" required>
      <div class="invalid-feedback">Enter a valid email address.</div>
    </div>
  </div>
  <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
    <i class="bi bi-envelope-open me-2"></i>Send Reset Link
  </button>
  <div class="text-center">
    <a href="/banking-system/public/login" class="text-decoration-none" style="color:var(--navy);font-size:0.85rem">
      <i class="bi bi-arrow-left me-1"></i>Back to Login
    </a>
  </div>
</form>
