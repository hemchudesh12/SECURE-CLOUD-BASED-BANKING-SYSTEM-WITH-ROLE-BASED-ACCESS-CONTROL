<?php
/**
 * Auth layout — serves login, register, reset pages.
 * For the login view, it directly includes the self-contained login.php.
 * Register/reset pages fall back to a simple centered card.
 */

$view  = $view ?? 'login';
$flash = Session::getFlash();

if ($view === 'login') {
    // Login has its own full-page design
    include BASE_PATH . '/views/auth/login.php';
    return;
}

// ── Other auth pages (register, reset) use a simple centered layout ──
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>SecureBank — <?= ucfirst(str_replace('_',' ',$view)) ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/banking-system/public/css/theme.css">
<style>
body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg);padding:1rem}
.auth-card{width:100%;max-width:420px;background:#fff;border-radius:20px;padding:2rem;box-shadow:0 4px 30px rgba(0,0,0,.1)}
.auth-logo{text-align:center;margin-bottom:1.5rem}
.auth-logo-icon{font-size:2.5rem;display:block;margin-bottom:.35rem}
.auth-logo-name{font-family:'Playfair Display',serif;font-size:1.4rem;color:var(--navy);font-weight:700}
.auth-logo-sub{font-size:.75rem;color:var(--muted);margin-top:.15rem}
.auth-heading{font-size:1.2rem;font-weight:700;color:var(--navy);margin-bottom:1.25rem;text-align:center}
</style>
</head>
<body>
<main class="auth-card" id="main-content">
  <div class="auth-logo">
    <span class="auth-logo-icon">🏛️</span>
    <div class="auth-logo-name">SecureBank</div>
    <div class="auth-logo-sub">Secure Cloud-Based Banking</div>
  </div>

  <?php foreach($flash as $type => $msgs): foreach($msgs as $msg):
    $isErr = $type==='error';
  ?>
  <div style="padding:.7rem 1rem;border-radius:10px;margin-bottom:.85rem;font-size:.82rem;font-weight:500;background:<?=$isErr?'#FEF2F2':'#ECFDF5'?>;border-left:4px solid <?=$isErr?'var(--red)':'var(--green)'?>;color:<?=$isErr?'#991B1B':'#065F46'?>">
    <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
  </div>
  <?php endforeach;endforeach;?>

  <?php
  $viewFile = BASE_PATH . '/views/auth/' . $view . '.php';
  if (file_exists($viewFile)) {
      include $viewFile;
  } else {
      echo '<p style="text-align:center;color:var(--muted)">View not found: ' . htmlspecialchars($view) . '</p>';
  }
  ?>

  <div style="text-align:center;margin-top:1.25rem;font-size:.78rem;color:var(--muted)">
    <a href="/banking-system/public/login" style="color:var(--gold-dk);font-weight:600;text-decoration:none">← Back to Login</a>
  </div>
</main>
</body>
</html>
