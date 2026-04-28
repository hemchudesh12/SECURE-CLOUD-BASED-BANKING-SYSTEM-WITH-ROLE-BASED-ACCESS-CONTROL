<!-- Create User -->
<style>
.cu-page-hdr{margin-bottom:1.5rem}
.cu-page-title{font-size:20px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem}
.cu-breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:12px;color:var(--color-text-muted);margin-top:.4rem}
.cu-breadcrumb a{color:var(--color-text-muted);text-decoration:none;transition:color .15s}
.cu-breadcrumb a:hover{color:#c9a84c}
.cu-breadcrumb .sep{color:var(--color-border-tertiary);font-size:10px}
.cu-breadcrumb .cur{color:var(--color-text-primary);font-weight:600}
.cu-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:1.25rem}
@media(max-width:860px){.cu-grid{grid-template-columns:1fr}}
.cu-card{background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:.5px solid var(--color-border-tertiary);overflow:hidden}
.cu-card-hdr{padding:.85rem 1.25rem;border-bottom:.5px solid var(--color-border-tertiary);font-size:14px;font-weight:700;color:var(--color-text-primary);display:flex;align-items:center;gap:.5rem;background:var(--color-background-secondary)}
.cu-card-body{padding:1.25rem}
.cu-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-secondary);margin-bottom:.35rem}
.cu-input,.cu-select{width:100%;padding:.6rem .85rem;border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-family:var(--font-sans);background:var(--color-background-primary);color:var(--color-text-primary);outline:none;transition:border-color .15s,box-shadow .15s;margin-bottom:.9rem}
.cu-input:focus,.cu-select:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.1)}
.cu-pw-wrap{position:relative;margin-bottom:.9rem}
.cu-pw-wrap .cu-input{margin-bottom:0;padding-right:2.5rem}
.cu-pw-toggle{position:absolute;right:.6rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--color-text-muted);font-size:14px;padding:2px;transition:color .15s}
.cu-pw-toggle:hover{color:#c9a84c}
.cu-actions{display:flex;align-items:center;justify-content:flex-end;gap:.6rem;margin-top:.5rem}
.cu-btn-primary{padding:9px 20px;background:#c9a84c;color:#0f1623;border:none;border-radius:var(--border-radius-md);font-size:13px;font-weight:700;cursor:pointer;transition:background .15s;display:inline-flex;align-items:center;gap:.4rem}
.cu-btn-primary:hover{background:#b8932f}
.cu-btn-cancel{padding:9px 16px;background:none;color:var(--color-text-secondary);border:.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);font-size:13px;font-weight:600;cursor:pointer;transition:all .15s;text-decoration:none;display:inline-flex;align-items:center}
.cu-btn-cancel:hover{border-color:#C0392B;color:#C0392B}
.cu-notes-box{background:#eef6ff;border:.5px solid #b3d1f5;border-radius:var(--border-radius-md);padding:1rem 1.1rem}
.cu-notes-title{display:flex;align-items:center;gap:.4rem;font-size:13px;font-weight:700;color:#1a5dad;margin-bottom:.65rem}
.cu-notes-list{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.45rem}
.cu-notes-list li{font-size:12.5px;color:#1a4080;display:flex;align-items:flex-start;gap:.5rem}
.cu-notes-list li::before{content:'';flex-shrink:0;margin-top:.3rem}
</style>

<div class="cu-page-hdr">
  <h1 class="cu-page-title">👤 Create New User</h1>
  <nav class="cu-breadcrumb" aria-label="Breadcrumb">
    <a href="/banking-system/public/admin/users">Users</a>
    <span class="sep">›</span>
    <span class="cur">Create</span>
  </nav>
</div>

<div class="cu-grid">
  <div class="cu-card">
    <div class="cu-card-hdr">🪪 New User Details</div>
    <div class="cu-card-body">
      <form method="POST" action="/banking-system/public/admin/users/create" class="needs-validation" novalidate>
        <?= CsrfMiddleware::field() ?>
        <div>
          <label for="full_name" class="cu-label">Full Name <span style="color:#C0392B">*</span></label>
          <input type="text" id="full_name" name="full_name" class="cu-input" required value="<?= htmlspecialchars($_POST['full_name']??'', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <label for="username" class="cu-label">Username <span style="color:#C0392B">*</span></label>
          <input type="text" id="username" name="username" class="cu-input" required pattern="[A-Za-z0-9_]{3,50}" value="<?= htmlspecialchars($_POST['username']??'', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <label for="email" class="cu-label">Email Address <span style="color:#C0392B">*</span></label>
          <input type="email" id="email" name="email" class="cu-input" required value="<?= htmlspecialchars($_POST['email']??'', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <label for="role_id" class="cu-label">Role <span style="color:#C0392B">*</span></label>
          <select id="role_id" name="role_id" class="cu-select" required>
            <?php foreach ($roles as $role): ?>
              <option value="<?= $role['id'] ?>" <?= ($_POST['role_id']??3)==$role['id']?'selected':'' ?>>
                <?= ucfirst($role['name']) ?> — <?= htmlspecialchars($role['description']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="password" class="cu-label">Password <span style="color:#C0392B">*</span></label>
          <div class="cu-pw-wrap">
            <input type="password" id="password" name="password" class="cu-input" required minlength="8" placeholder="Min 8 chars, uppercase, number, symbol">
            <button type="button" class="cu-pw-toggle" onclick="togglePw()" title="Show/hide password" aria-label="Toggle password visibility">👁</button>
          </div>
        </div>

        <!-- Initial Deposit (Customer only) -->
        <div id="deposit-field" style="display:none">
          <div style="height:.5px;background:var(--color-border-tertiary);margin:.2rem 0 1rem"></div>
          <label for="initial_deposit" class="cu-label">Initial Deposit (₹) <span style="color:#C0392B">*</span></label>
          <input type="number" id="initial_deposit" name="initial_deposit" class="cu-input"
                 min="1000" step="0.01" placeholder="Minimum ₹1,000 required">
          <div style="font-size:11px;background:#fff8e7;border:.5px solid #f0c040;border-radius:var(--border-radius-sm);padding:5px 9px;margin-top:-.6rem;margin-bottom:.9rem;color:#8a6200">
            ⚠️ Customer accounts require a minimum opening deposit of <strong>₹1,000</strong>.
            Account number will be auto-generated as <strong>ACC-<?= date('Y') ?>XXXXXX</strong> (based on Year + User ID).
          </div>
        </div>
        <div class="cu-actions">
          <a href="/banking-system/public/admin/users" class="cu-btn-cancel">Cancel</a>
          <button type="submit" class="cu-btn-primary">✓ Create User</button>
        </div>
      </form>
    </div>
  </div>

  <div>
    <div class="cu-card">
      <div class="cu-card-hdr">ℹ️ Notes</div>
      <div class="cu-card-body">
        <div class="cu-notes-box">
          <div class="cu-notes-title">ℹ️ Important Information</div>
          <ul class="cu-notes-list">
            <li>✅ Customer accounts get a savings account automatically</li>
            <li>💰 Minimum opening deposit of <strong>₹1,000</strong> is required for customers</li>
            <li>🔢 Account number format: <strong>ACC-<?= date('Y') ?>XXXXXX</strong> (Year + zero-padded User ID)</li>
            <li>✅ New users can log in immediately after creation</li>
            <li>⚠️ Account creation is audit-logged</li>
            <li>🔒 Password stored as bcrypt hash (cost=12)</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function togglePw(){
  const inp=document.getElementById('password');
  inp.type = inp.type==='password' ? 'text' : 'password';
}
// Show deposit field only when Customer role is selected
const roleSelect = document.getElementById('role_id');
const depositField = document.getElementById('deposit-field');
const depositInput = document.getElementById('initial_deposit');
function checkRole() {
  // Role ID 3 = Customer (standard setup)
  const selectedText = roleSelect.options[roleSelect.selectedIndex]?.text?.toLowerCase() ?? '';
  const isCustomer = selectedText.includes('customer');
  depositField.style.display = isCustomer ? '' : 'none';
  depositInput.required = isCustomer;
  if (!isCustomer) depositInput.value = '';
}
if (roleSelect) { roleSelect.addEventListener('change', checkRole); checkRole(); }
</script>
