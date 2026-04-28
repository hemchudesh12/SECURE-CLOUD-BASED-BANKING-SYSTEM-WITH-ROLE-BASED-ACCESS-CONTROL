<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SecureBank — Sign In</title>
<meta name="description" content="Sign in to your SecureBank secure banking portal">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<style>
:root{--navy:#0D1B2A;--navy-mid:#1a2f45;--gold:#C9972B;--gold-lt:#e6b84a;--gold-dk:#A87820;--green:#2E7D52;--red:#C0392B;--bg:#F4F6F9;--border:#E2E8F0;--muted:#64748B}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;background:var(--bg);overflow:hidden}

/* ── LEFT PANEL ─────────────────────────────── */
.left-panel{width:42%;background:var(--navy);display:flex;flex-direction:column;justify-content:center;align-items:center;padding:3rem 2.5rem;position:relative;overflow:hidden;flex-shrink:0}

/* floating shapes */
.shape{position:absolute;border-radius:50%;opacity:.06;animation:float 6s ease-in-out infinite}
.shape-1{width:300px;height:300px;background:var(--gold);top:-80px;left:-80px;animation-delay:0s}
.shape-2{width:200px;height:200px;background:var(--gold);bottom:-60px;right:-60px;animation-delay:2s}
.shape-3{width:150px;height:150px;background:#fff;top:50%;left:60%;animation-delay:4s}
.shape-4{width:80px;height:80px;background:var(--gold);top:20%;left:10%;animation-delay:1s}
@keyframes float{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-20px) scale(1.05)}}

.brand-block{position:relative;z-index:1;text-align:center;max-width:320px}
.brand-icon-lg{font-size:3.5rem;margin-bottom:.75rem;display:block}
.brand-name-lg{font-family:'Playfair Display',serif;font-size:2rem;color:var(--gold);font-weight:700;letter-spacing:.5px;margin-bottom:.35rem}
.brand-tagline{font-size:.85rem;color:rgba(255,255,255,.55);letter-spacing:.05em;margin-bottom:2.5rem}

.trust-badges{display:flex;flex-direction:column;gap:.75rem;width:100%;position:relative;z-index:1}
.trust-badge{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border:1px solid rgba(201,151,43,.2);border-radius:12px;background:rgba(201,151,43,.06)}
.trust-badge-icon{font-size:1.25rem;width:36px;flex-shrink:0;text-align:center}
.trust-badge-text{font-size:.8rem;font-weight:600;color:rgba(255,255,255,.85)}
.trust-badge-sub{font-size:.7rem;color:rgba(255,255,255,.4);margin-top:.1rem}

/* ── RIGHT PANEL ─────────────────────────────── */
.right-panel{flex:1;display:flex;align-items:center;justify-content:center;padding:2rem;overflow-y:auto}
.form-card{width:100%;max-width:420px}

.form-heading{font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--navy);margin-bottom:.35rem}
.form-subtext{font-size:.85rem;color:var(--muted);margin-bottom:2rem}

.alert-msg{padding:.75rem 1rem;border-radius:10px;font-size:.82rem;font-weight:500;margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;animation:slideIn .3s ease-out}
.alert-msg.error{background:#FEF2F2;color:#991B1B;border-left:4px solid var(--red)}
.alert-msg.success{background:#ECFDF5;color:#065F46;border-left:4px solid var(--green)}
@keyframes slideIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
@keyframes shake{0%,100%{transform:translateX(0)}20%{transform:translateX(-8px)}40%{transform:translateX(8px)}60%{transform:translateX(-5px)}80%{transform:translateX(5px)}}
.shake{animation:shake .4s ease-out}

.form-group{margin-bottom:1rem}
.form-label{display:block;font-size:.78rem;font-weight:600;color:var(--navy);margin-bottom:.35rem}
.input-wrap{position:relative}
.input-icon{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.9rem;pointer-events:none;z-index:1}
.input-icon-r{position:absolute;right:.85rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:.85rem;padding:.2rem}
input{width:100%;padding:.7rem .85rem .7rem 2.4rem;border:1.5px solid var(--border);border-radius:10px;font-size:.875rem;font-family:'DM Sans',sans-serif;color:var(--navy);background:#fff;outline:none;transition:border-color .2s,box-shadow .2s}
input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(201,151,43,.15)}
input.no-icon{padding-left:.85rem}
input.has-right{padding-right:2.5rem}

.btn-signin{width:100%;padding:.85rem;border-radius:12px;border:none;background:linear-gradient(135deg,var(--gold),var(--gold-dk));color:var(--navy);font-size:.95rem;font-weight:700;font-family:'DM Sans',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.5rem;transition:all .2s;box-shadow:0 4px 14px rgba(201,151,43,.35);margin-top:1.25rem}
.btn-signin:hover{filter:brightness(1.08);transform:translateY(-1px);box-shadow:0 6px 20px rgba(201,151,43,.45)}
.btn-signin:active{transform:translateY(0)}
.btn-signin:disabled{opacity:.6;cursor:not-allowed;transform:none}

.form-links{display:flex;justify-content:space-between;align-items:center;margin-top:.85rem;font-size:.78rem}
.link-gold{color:var(--gold-dk);font-weight:600;text-decoration:none;transition:color .15s}
.link-gold:hover{color:var(--gold)}
.link-navy{color:var(--navy);font-weight:600;text-decoration:underline;text-underline-offset:2px;transition:color .15s}
.link-navy:hover{color:var(--gold-dk)}

.divider{display:flex;align-items:center;gap:.75rem;margin:1.25rem 0;color:var(--muted);font-size:.75rem}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border)}

/* Demo Accounts accordion */
.demo-wrap{border:1.5px dashed rgba(201,151,43,.4);border-radius:12px;overflow:hidden;background:#FFFBEB}
.demo-toggle{width:100%;display:flex;align-items:center;justify-content:space-between;padding:.7rem 1rem;background:none;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;font-size:.8rem;font-weight:600;color:var(--gold-dk)}
.demo-toggle .chevron{transition:transform .25s;font-size:.7rem}
.demo-toggle.open .chevron{transform:rotate(180deg)}
.demo-body{display:none;padding:.25rem 1rem .75rem}
.demo-body.open{display:block}
.demo-row{display:flex;align-items:center;justify-content:space-between;padding:.45rem 0;border-bottom:1px solid rgba(201,151,43,.15);font-size:.75rem}
.demo-row:last-child{border-bottom:none}
.demo-role{font-weight:700;color:var(--navy)}
.demo-creds{color:var(--muted);font-family:monospace;font-size:.72rem}
.demo-fill{background:none;border:1.5px solid rgba(201,151,43,.4);border-radius:6px;padding:2px 7px;font-size:.68rem;font-weight:600;color:var(--gold-dk);cursor:pointer;transition:all .15s}
.demo-fill:hover{background:var(--gold);color:var(--navy);border-color:var(--gold)}

.security-note{display:flex;align-items:center;gap:.35rem;margin-top:1.25rem;font-size:.7rem;color:var(--muted);justify-content:center}

@media(max-width:768px){
  .left-panel{display:none}
  .right-panel{padding:1.5rem}
  body{overflow:auto}
}
</style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="left-panel" aria-hidden="true">
  <div class="shape shape-1"></div>
  <div class="shape shape-2"></div>
  <div class="shape shape-3"></div>
  <div class="shape shape-4"></div>

  <div class="brand-block">
    <span class="brand-icon-lg">🏛️</span>
    <div class="brand-name-lg">SecureBank</div>
    <div class="brand-tagline">Secure Cloud-Based Banking</div>

    <div class="trust-badges">
      <div class="trust-badge">
        <span class="trust-badge-icon">🔐</span>
        <div>
          <div class="trust-badge-text">256-bit Encryption</div>
          <div class="trust-badge-sub">End-to-end secure sessions</div>
        </div>
      </div>
      <div class="trust-badge">
        <span class="trust-badge-icon">🏛️</span>
        <div>
          <div class="trust-badge-text">RBI Compliant</div>
          <div class="trust-badge-sub">Regulatory &amp; audit ready</div>
        </div>
      </div>
      <div class="trust-badge">
        <span class="trust-badge-icon">🛡️</span>
        <div>
          <div class="trust-badge-text">24/7 Fraud Monitoring</div>
          <div class="trust-badge-sub">AI-powered threat detection</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- RIGHT PANEL -->
<div class="right-panel">
  <main class="form-card" id="main-content">
    <a href="#main-content" class="sr-only" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden">Skip to main content</a>

    <h1 class="form-heading">Welcome Back</h1>
    <p class="form-subtext">Sign in to your secure banking portal</p>

    <?php
    $flash = Session::getFlash();
    foreach (($flash['error'] ?? []) as $err): ?>
    <div class="alert-msg error" role="alert" id="form-alert">
      <span>⚠️</span> <?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endforeach;
    foreach (($flash['success'] ?? []) as $msg): ?>
    <div class="alert-msg success" role="alert">
      <span>✅</span> <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endforeach; ?>

    <form method="POST" action="/banking-system/public/login" id="login-form" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <div class="input-wrap">
          <span class="input-icon" aria-hidden="true">👤</span>
          <input type="text" id="username" name="username" required autocomplete="username"
                 placeholder="Enter your username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                 aria-label="Username" aria-required="true">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-wrap">
          <span class="input-icon" aria-hidden="true">🔒</span>
          <input type="password" id="password" name="password" required autocomplete="current-password"
                 placeholder="Enter your password" class="has-right"
                 aria-label="Password" aria-required="true">
          <button type="button" class="input-icon-r" id="togglePwd" aria-label="Toggle password visibility">👁️</button>
        </div>
      </div>

      <button type="submit" class="btn-signin" id="signin-btn" aria-label="Sign in securely">
        🛡️ Sign In Securely
      </button>
    </form>

    <div class="form-links">
      <a href="/banking-system/public/reset-password" class="link-gold">Forgot Password?</a>
      <a href="/banking-system/public/register" class="link-navy">New Customer? Register</a>
    </div>

    <div class="divider">Demo Accounts</div>

    <div class="demo-wrap">
      <button class="demo-toggle" id="demoToggle" aria-expanded="false" aria-controls="demoBody">
        <span>🔑 View Demo Credentials</span>
        <span class="chevron" aria-hidden="true">▼</span>
      </button>
      <div class="demo-body" id="demoBody" role="region" aria-label="Demo accounts">
        <div class="demo-row">
          <div><div class="demo-role">🔴 Administrator</div><div class="demo-creds">Hem / Hem@2806</div></div>
          <button class="demo-fill" onclick="fillLogin('Hem','Hem@2806')" aria-label="Fill admin credentials">Use</button>
        </div>

        <div class="demo-row">
          <div><div class="demo-role">🟢 Customer 1</div><div class="demo-creds">Mani / Mani@123</div></div>
          <button class="demo-fill" onclick="fillLogin('Mani','Mani@123')" aria-label="Fill customer credentials">Use</button>
        </div>
        <div class="demo-row">
          <div><div class="demo-role">🟢 Customer 2</div><div class="demo-creds">jane_doe / Customer@123</div></div>
          <button class="demo-fill" onclick="fillLogin('jane_doe','Customer@123')" aria-label="Fill customer 2 credentials">Use</button>
        </div>
      </div>
    </div>

    <div class="security-note" aria-label="Security information">
      🔐 <span>Your connection is encrypted and monitored for security</span>
    </div>
  </main>
</div>

<script>
// Password toggle
document.getElementById('togglePwd').addEventListener('click', function(){
  const p = document.getElementById('password');
  const isText = p.type === 'text';
  p.type = isText ? 'password' : 'text';
  this.textContent = isText ? '👁️' : '🙈';
  this.setAttribute('aria-label', isText ? 'Show password' : 'Hide password');
});

// Demo accordion
const demoToggle = document.getElementById('demoToggle');
const demoBody   = document.getElementById('demoBody');
demoToggle.addEventListener('click', () => {
  const open = demoBody.classList.toggle('open');
  demoToggle.classList.toggle('open', open);
  demoToggle.setAttribute('aria-expanded', open);
});

// Fill credentials
function fillLogin(user, pass){
  document.getElementById('username').value = user;
  document.getElementById('password').value = pass;
  document.getElementById('username').dispatchEvent(new Event('input'));
}

// Shake on error
<?php if (!empty($flash['error'])): ?>
document.getElementById('login-form').classList.add('shake');
<?php endif; ?>

// Form submit — disable button to prevent double submit
document.getElementById('login-form').addEventListener('submit', function(){
  const btn = document.getElementById('signin-btn');
  btn.disabled = true;
  btn.innerHTML = '⏳ Signing in…';
});
</script>
</body>
</html>
