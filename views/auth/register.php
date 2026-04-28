<!-- Registration Form -->
<h2 class="text-center mb-1" style="font-size:1.25rem;font-weight:700;color:var(--navy)">Open an Account</h2>
<p class="text-center text-muted mb-4" style="font-size:0.85rem">Customer self-registration</p>

<form method="POST" action="/banking-system/public/register" class="needs-validation" novalidate>
  <?= CsrfMiddleware::field() ?>

  <div class="row g-2 mb-3">
    <div class="col-12">
      <label for="full_name" class="form-label">Full Name</label>
      <input type="text" id="full_name" name="full_name" class="form-control"
             placeholder="Your full name" required
             value="<?= htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="invalid-feedback">Full name is required.</div>
    </div>
    <div class="col-12">
      <label for="username" class="form-label">Username</label>
      <input type="text" id="username" name="username" class="form-control"
             placeholder="Choose a username" required pattern="[A-Za-z0-9_]{3,50}"
             value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="invalid-feedback">3–50 alphanumeric characters.</div>
    </div>
    <div class="col-12">
      <label for="email" class="form-label">Email Address</label>
      <input type="email" id="email" name="email" class="form-control"
             placeholder="your@email.com" required
             value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="invalid-feedback">Enter a valid email address.</div>
    </div>
    <div class="col-12">
      <label for="password" class="form-label">Password</label>
      <input type="password" id="password" name="password" class="form-control"
             placeholder="Min 8 chars, uppercase, number, symbol" required minlength="8">
      <!-- Strength meter -->
      <div class="mt-1" style="height:4px;background:#e9ecef;border-radius:2px">
        <div id="password-strength" style="height:100%;width:0;border-radius:2px;transition:all 0.3s"></div>
      </div>
      <div class="d-flex justify-content-between mt-1">
        <small class="text-muted" style="font-size:0.75rem">Password strength</small>
        <small id="strength-label" style="font-size:0.75rem;font-weight:600"></small>
      </div>
      <div class="invalid-feedback">Password must be at least 8 characters.</div>
    </div>
    <div class="col-12">
      <label for="confirm_password" class="form-label">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" class="form-control"
             placeholder="Repeat password" required>
      <div class="invalid-feedback">Passwords must match.</div>
    </div>
  </div>

  <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
    <i class="bi bi-person-plus me-2"></i>Create Account
  </button>

  <div class="text-center" style="font-size:0.85rem">
    Already have an account?
    <a href="/banking-system/public/login" class="text-decoration-none" style="color:var(--gold)">Sign In</a>
  </div>
</form>
