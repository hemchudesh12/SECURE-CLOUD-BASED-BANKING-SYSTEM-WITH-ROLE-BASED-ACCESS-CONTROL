<?php
$role     = Session::get('role', 'customer');
$username = Session::get('username', '');
$fullName = Session::get('full_name', $username);
// Strip any parenthetical role suffix already baked into full_name (e.g. "Hem (Administrator)")
$fullName = preg_replace('/\s*\([^)]+\)\s*$/', '', trim($fullName));
$userId   = (int)Session::get('user_id');
$flash    = Session::getFlash();
$view     = $view ?? 'dashboard';
$secsLeft = Session::secondsRemaining();
$wsToken  = Session::get('ws_token', '');
if (empty($wsToken)) { $wsToken = bin2hex(random_bytes(16)); Session::set('ws_token', $wsToken); }

$fraudCount = 0;
$pendingCount = 0;
try {
    $db = Database::getInstance();
    $fraudCount   = (int)$db->query("SELECT COUNT(*) FROM fraud_flags WHERE review_status='pending'")->fetchColumn();
    $pendingCount = (int)$db->query("SELECT COUNT(*) FROM transactions WHERE status='pending'")->fetchColumn();
} catch (Throwable $e) {}

$navLabels = ['dashboard'=>'Dashboard','audit_log'=>'Audit Logs','users'=>'Users','create_user'=>'Create User','edit_user'=>'Edit User','roles'=>'Roles & Perms','edit_permissions'=>'Edit Permissions','live_monitor'=>'Live Monitor','fraud_dashboard'=>'Fraud','system_health'=>'System Health','reports'=>'Reports','support_tickets'=>'Support','scheduled_payments'=>'Scheduled','integrity'=>'Integrity','transfer'=>'Fund Transfer','history'=>'Transaction History','profile'=>'My Profile','beneficiaries'=>'Beneficiaries','analytics'=>'Analytics','statement'=>'Account Statement','scheduled'=>'Scheduled Payments','support'=>'Support','support_ticket'=>'Ticket','loans'=>'Loans'];
$pageTitle = $navLabels[$view] ?? ucfirst(str_replace('_',' ',$view));
$prefix = ($role === 'administrator') ? 'admin' : $role;

function sbItem(string $href, string $icon, string $label, string $currentView, array $matchViews = [], int $badge = 0): string {
    $active = in_array($currentView, $matchViews) ? ' active' : '';
    $b = $badge > 0 ? '<span class="sb-badge">'.$badge.'</span>' : '';
    return '<a href="'.$href.'" class="sb-item'.$active.'"><span class="sbi">'.$icon.'</span>'.$label.$b.'</a>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>SecureBank — <?= htmlspecialchars($pageTitle) ?></title>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/banking-system/public/css/theme.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>window.WS_TOKEN='<?= htmlspecialchars($wsToken) ?>';window.WS_PORT=8080;window.USER_ID=<?= $userId ?>;window.APP_BASE='/banking-system/public';</script>
</head>
<body data-session-remaining="<?= $secsLeft ?>">

<div id="sys-banner"></div>
<div id="toast-cont"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
  <div class="sb-logo">
    <span class="sb-logo-icon">🏛️</span>
    <span class="sb-logo-text">SecureBank</span>
  </div>

  <?php if ($role === 'customer'): ?>
  <div class="sb-section">Customer Portal</div>
  <nav class="sb-nav">
    <?= sbItem('/banking-system/public/customer/dashboard','📊','Dashboard',$view,['dashboard']) ?>
    <?= sbItem('/banking-system/public/customer/transfer','↔️','Fund Transfer',$view,['transfer']) ?>
    <?= sbItem('/banking-system/public/customer/history','🕐','History',$view,['history']) ?>
    <?= sbItem('/banking-system/public/customer/beneficiaries','👥','Beneficiaries',$view,['beneficiaries']) ?>
    <?= sbItem('/banking-system/public/customer/scheduled','📅','Scheduled',$view,['scheduled']) ?>
    <?= sbItem('/banking-system/public/customer/loans','💳','My Loans',$view,['loans']) ?>
  </nav>
  <div class="sb-section">Reports</div>
  <nav class="sb-nav">
    <?= sbItem('/banking-system/public/customer/statement','📄','Statement',$view,['statement']) ?>
    <?= sbItem('/banking-system/public/customer/analytics','📈','Analytics',$view,['analytics']) ?>
  </nav>
  <div class="sb-section">Account</div>
  <nav class="sb-nav">
    <?= sbItem('/banking-system/public/customer/profile','👤','My Profile',$view,['profile']) ?>
    <?= sbItem('/banking-system/public/customer/support','🎧','Support',$view,['support','support_ticket']) ?>
  </nav>

  <?php elseif ($role === 'administrator'): ?>
  <div class="sb-section">Administration</div>
  <nav class="sb-nav">
    <?= sbItem('/banking-system/public/admin/dashboard','📊','Dashboard',$view,['dashboard']) ?>
    <?= sbItem('/banking-system/public/admin/users','👥','Users',$view,['users','create_user','edit_user']) ?>
    <?= sbItem('/banking-system/public/admin/roles','🔒','Roles &amp; Perms',$view,['roles','edit_permissions']) ?>
  </nav>
  <div class="sb-section">Monitoring</div>
  <nav class="sb-nav">
    <?= sbItem('/banking-system/public/admin/live-monitor','📡','Live Monitor',$view,['live_monitor']) ?>
    <?= sbItem('/banking-system/public/admin/audit','📋','Audit Logs',$view,['audit_log']) ?>
    <?= sbItem('/banking-system/public/admin/fraud','🚨','Fraud',$view,['fraud_dashboard'],$fraudCount) ?>
    <?= sbItem('/banking-system/public/admin/system','⚙️','System Health',$view,['system_health']) ?>
    <?= sbItem('/banking-system/public/admin/reports','📈','Reports',$view,['reports']) ?>
    <?= sbItem('/banking-system/public/admin/loans','💳','Loan Management',$view,['loans']) ?>
    <?= sbItem('/banking-system/public/admin/support','🎧','Support',$view,['support_tickets']) ?>
    <?= sbItem('/banking-system/public/admin/scheduled','⏰','Scheduled',$view,['scheduled_payments']) ?>
    <?= sbItem('/banking-system/public/admin/integrity','🔗','Integrity',$view,['integrity']) ?>
  </nav>
  <?php endif; ?>
</aside>

<!-- TOPBAR -->
<header id="topbar" role="banner">
  <div class="tb-breadcrumb">SecureBank / <strong><?= htmlspecialchars($pageTitle) ?></strong></div>
  <div class="tb-right">
    <span class="ws-dot" id="ws-dot" title="Connection status"></span>
    <?php if ($fraudCount > 0): ?>
    <a href="/banking-system/public/admin/fraud" class="fraud-pill" aria-label="<?= $fraudCount ?> fraud flags pending">
      ⚠️ <?= $fraudCount ?> Fraud Flag<?= $fraudCount > 1 ? 's' : '' ?>
    </a>
    <?php endif; ?>
    <span class="user-pill">👤 <?= htmlspecialchars($fullName) ?> (<?= ucfirst($role) ?>)</span>
    <a href="/banking-system/public/logout" class="logout-btn" aria-label="Sign out">↪ Logout</a>
  </div>
</header>

<!-- MAIN CONTENT -->
<main class="main" id="main-content">
  <?php foreach ($flash as $type => $msgs): foreach ($msgs as $msg):
    $isErr = $type === 'error';
  ?>
  <div style="display:flex;align-items:center;gap:.5rem;padding:.6rem 1rem;border-radius:var(--border-radius-md);margin-bottom:.75rem;background:<?= $isErr?'#fcebeb':'#eaf3de' ?>;border:.5px solid <?= $isErr?'#f5b7b7':'#b8d89a' ?>;font-size:13px;color:<?= $isErr?'#a32d2d':'#1a7a3e' ?>">
    <?= $isErr ? '⚠️' : '✓' ?> <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
    <button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;font-size:1rem;line-height:1" aria-label="Dismiss">×</button>
  </div>
  <?php endforeach; endforeach; ?>

  <?php
  $viewFolder = ($role === 'administrator') ? 'admin' : $role;
  $viewFile   = BASE_PATH . '/views/' . $viewFolder . '/' . $view . '.php';
  if (file_exists($viewFile)) {
      include $viewFile;
  } else {
      echo '<div style="padding:1.25rem;background:#fcebeb;border-radius:var(--border-radius-md);border:.5px solid #f5b7b7;color:#a32d2d;font-size:13px">⚠️ View not found: <strong>' . htmlspecialchars($view) . '</strong></div>';
  }
  ?>

  <div style="margin-top:2rem;padding-top:.75rem;border-top:.5px solid var(--color-border-tertiary);text-align:center;font-size:11px;color:var(--color-text-muted)">
    &copy; <?= date('Y') ?> SecureBank — Secure Cloud-Based Banking &nbsp;·&nbsp; All transactions encrypted &amp; audited
  </div>
</main>

<!-- SESSION WARNING -->
<div id="session-warning" role="alertdialog" aria-label="Session expiring soon">
  <div class="sw-title">⏱️ Session Expiring</div>
  <div class="sw-cd">Expires in <strong id="sw-countdown">5:00</strong></div>
  <button onclick="keepAlive()" style="width:100%;padding:.45rem;border-radius:var(--border-radius-sm);border:none;background:#c9a84c;color:#0f1623;font-weight:700;cursor:pointer;font-size:13px;font-family:var(--font-sans)">Stay Logged In</button>
</div>

<script src="/banking-system/public/js/realtime.js"></script>
<script src="/banking-system/public/js/notifications.js"></script>
<script src="/banking-system/public/js/session-timeout.js"></script>
<script src="/banking-system/public/js/validation.js"></script>
<script>
function keepAlive(){fetch(window.APP_BASE+'/api/balance').then(()=>{document.getElementById('session-warning').style.display='none';}).catch(()=>{});}
function showToast(title,msg,type='success'){const c=document.getElementById('toast-cont');if(!c)return;const d=document.createElement('div');d.className='toast-item'+(type==='error'?' error':(type==='warning'?' warning':''));d.innerHTML=`<div><div class="toast-title">${title}</div><div class="toast-msg">${msg}</div></div><button class="toast-close" onclick="this.parentElement.remove()">×</button>`;c.appendChild(d);setTimeout(()=>d.remove(),5000);}
window.showToast=showToast;
</script>
</body>
</html>
