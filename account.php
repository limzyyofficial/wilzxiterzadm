<?php
$configFile = __DIR__ . '/data/config.json';
$config = [
    'title'            => 'WilzXiterz',
    'whatsapp'         => '6285173360622',
    'telegram'         => 'WilzXiterzVN',
    'channel'          => '',
    'discord'          => '',
    'banner'           => 'assets/img/logops.png',
    'google_client_id' => '',
    'vpn_block'        => false,
    'vpn_api_key'      => '',
];
if (file_exists($configFile)) {
    $saved = json_decode(file_get_contents($configFile), true);
    if (is_array($saved)) $config = array_merge($config, $saved);
}

$fullTitle   = htmlspecialchars($config['title']);
$parts = explode(' ', trim($fullTitle));
$namePart2 = count($parts) > 1 ? array_pop($parts) : '';
$namePart1 = implode(' ', $parts) ?: $fullTitle;
if (!$namePart2) { $namePart1 = substr($fullTitle,0,4); $namePart2 = substr($fullTitle,4); }

$waLink    = "https://wa.me/" . preg_replace('/[^0-9]/', '', $config['whatsapp']);
$tgLink    = "https://t.me/" . ltrim(htmlspecialchars($config['telegram']), '@');
$bannerImg = htmlspecialchars($config['banner'] ?: 'assets/img/logops.png');
$googleClientId = htmlspecialchars($config['google_client_id'] ?? '');
$vpnEnabled = !empty($config['vpn_block']) ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Akun Saya - <?= $fullTitle ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php if ($googleClientId): ?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<?php endif; ?>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
:root{
  --bg:#0A0812;--bg2:#100D1A;--bg3:#150F22;
  --border:#2A2040;--border2:#3A2D5A;
  --purple:#9B59F5;--purple2:#7C3AED;--purple3:#A855F7;
  --purple-glow:rgba(124,58,237,0.25);--purple-light:#C084FC;
  --purple-dim:rgba(124,58,237,0.15);--purple-border:rgba(124,58,237,0.35);
  --green:#22C55E;--green-dim:rgba(34,197,94,0.15);
  --yellow:#EAB308;--red:#EF4444;
  --text:#ffffff;--text-muted:#9B8EC4;--text-dimmer:#5C4F8A;
  --card-bg:#120D20;--card-bg2:#1A1230;--card-border:rgba(124,58,237,0.2);
  --font:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
}
html{scroll-behavior:smooth;background:var(--bg)}
body{background:var(--bg);color:var(--text);font-family:var(--font);-webkit-font-smoothing:antialiased;min-height:100vh;overflow-x:hidden;padding-bottom:60px}
a{text-decoration:none;color:inherit}

body::before{content:'';position:fixed;top:-200px;left:-200px;width:700px;height:700px;background:radial-gradient(circle,rgba(124,58,237,0.12) 0%,transparent 70%);pointer-events:none;z-index:0}

/* NAV */
nav{position:sticky;top:0;left:0;right:0;z-index:200;background:rgba(10,8,18,0.92);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid var(--border)}
.nav-inner{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;max-width:1200px;margin:0 auto}
.nav-logo{display:flex;align-items:center;gap:10px}
.logo-icon{width:40px;height:40px;background:linear-gradient(135deg,#7C3AED,#A855F7);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;color:white;box-shadow:0 0 16px rgba(124,58,237,0.5);flex-shrink:0;position:relative;overflow:hidden}
.logo-icon::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,0.15),transparent)}
.logo-icon img{width:100%;height:100%;object-fit:contain;border-radius:10px}
.logo-text{display:flex;flex-direction:column;line-height:1.1}
.logo-part1{font-size:15px;font-weight:800;letter-spacing:0.02em;background:linear-gradient(90deg,#C084FC,#A855F7);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.logo-part2{font-size:15px;font-weight:800;letter-spacing:0.02em;color:white}
.nav-menu{display:flex;align-items:center;gap:28px}
@media(max-width:900px){.nav-menu{display:none}}
.nav-link{display:flex;align-items:center;gap:6px;font-size:14px;color:var(--text-muted);transition:color 0.15s}
.nav-link:hover,.nav-link.active{color:white}
.nav-actions{display:flex;align-items:center;gap:10px}
@media(max-width:900px){.nav-actions{display:none}}
.hamburger-btn{display:none;align-items:center;gap:8px;background:rgba(255,255,255,.07);border:1px solid var(--border);color:white;font-size:14px;cursor:pointer;padding:7px 12px;border-radius:10px;transition:.15s}
@media(max-width:900px){.hamburger-btn{display:flex}}
.hamburger-btn i{color:var(--purple-light);font-size:1rem}

/* SIDEBAR */
.sidebar-overlay{position:fixed;inset:0;z-index:300;background:rgba(0,0,0,0.65);backdrop-filter:blur(4px);opacity:0;pointer-events:none;transition:opacity 0.3s}
.sidebar-overlay.open{opacity:1;pointer-events:all}
.sidebar{position:fixed;top:0;left:0;bottom:0;width:288px;z-index:310;background:linear-gradient(180deg,var(--bg3) 0%,var(--bg2) 100%);border-right:1px solid var(--border2);transform:translateX(-100%);transition:transform 0.32s cubic-bezier(0.4,0,0.2,1);display:flex;flex-direction:column;overflow-y:auto}
.sidebar.open{transform:translateX(0)}
.sidebar-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}
.sidebar-close{display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);border:1px solid var(--border);color:var(--text-muted);cursor:pointer;font-size:13px;padding:6px 11px;border-radius:8px;transition:color 0.15s;font-family:var(--font)}
.sidebar-close:hover{color:white}
.sidebar-nav{padding:16px 12px;display:flex;flex-direction:column;gap:0;flex:1}
.sidebar-link{display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:12px;font-size:14px;font-weight:500;color:var(--text-muted);background:rgba(124,58,237,0.06);border:1px solid var(--border);transition:all 0.15s;margin-bottom:4px}
.sidebar-link:hover{background:rgba(124,58,237,0.15);color:white}
.sidebar-link i{width:18px;text-align:center;font-size:15px}

/* PAGE */
.page-shell{max-width:860px;margin:0 auto;padding:0 20px;position:relative;z-index:1}

/* HERO / PROFILE CARD */
.profile-wrap{padding:32px 0 20px}
.profile-card{background:linear-gradient(135deg,#1A1030 0%,#120D24 60%,#0E0A1C 100%);border:1px solid var(--border2);border-radius:20px;padding:28px 24px;position:relative;overflow:hidden}
.profile-card::before{content:'';position:absolute;top:-60px;right:-60px;width:220px;height:220px;background:radial-gradient(circle,rgba(168,85,247,0.15),transparent 70%);pointer-events:none}
.profile-avatar{width:64px;height:64px;border-radius:50%;border:3px solid var(--purple-border);object-fit:cover;background:var(--purple-dim)}
.profile-avatar-placeholder{width:64px;height:64px;border-radius:50%;border:3px solid var(--purple-border);background:var(--purple-dim);display:flex;align-items:center;justify-content:center;font-size:26px;color:var(--purple-light)}
.profile-info{display:flex;align-items:center;gap:16px}
.profile-name{font-size:20px;font-weight:800;color:white}
.profile-email{font-size:13px;color:var(--text-muted);margin-top:2px}
.profile-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.3);border-radius:999px;font-size:11px;font-weight:600;color:#4ADE80;margin-top:6px}
.profile-actions{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap}
.btn-logout{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:9px;font-size:13px;font-weight:600;color:#f87171;cursor:pointer;transition:all 0.15s;font-family:var(--font)}
.btn-logout:hover{background:rgba(239,68,68,0.2)}

/* LOGIN CARD */
.login-wrap{padding:60px 0}
.login-card{background:linear-gradient(135deg,#1A1030 0%,#120D24 100%);border:1px solid var(--border2);border-radius:20px;padding:40px 32px;text-align:center;max-width:400px;margin:0 auto}
.login-icon{width:72px;height:72px;background:linear-gradient(135deg,#7C3AED,#A855F7);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:28px;color:white;margin:0 auto 20px;box-shadow:0 0 30px rgba(124,58,237,0.4)}
.login-title{font-size:22px;font-weight:800;margin-bottom:8px}
.login-sub{font-size:14px;color:var(--text-muted);line-height:1.6;margin-bottom:28px}
.btn-google{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:13px 24px;background:white;border:none;border-radius:12px;font-size:15px;font-weight:700;color:#1a1a1a;cursor:pointer;transition:all 0.15s;font-family:var(--font);width:100%;max-width:300px}
.btn-google:hover{background:#f1f3f4;transform:translateY(-1px);box-shadow:0 8px 24px rgba(0,0,0,0.3)}
.btn-google img{width:20px;height:20px}
.no-client-note{font-size:12px;color:var(--text-dimmer);margin-top:16px;line-height:1.5;padding:12px;background:rgba(255,255,255,0.03);border-radius:8px;border:1px solid var(--border)}

/* TABS */
.tabs-wrap{margin-top:24px}
.tab-btns{display:flex;gap:0;background:var(--card-bg2);border:1px solid var(--border);border-radius:12px;padding:4px;margin-bottom:20px}
.tab-btn{flex:1;padding:9px 12px;border:none;background:none;color:var(--text-muted);font-size:13px;font-weight:600;cursor:pointer;border-radius:9px;transition:all 0.15s;font-family:var(--font);display:flex;align-items:center;justify-content:center;gap:6px}
.tab-btn.active{background:rgba(124,58,237,0.25);color:var(--purple-light)}
.tab-pane{display:none}
.tab-pane.active{display:block}

/* SECTION LABEL */
.sec-eyebrow{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--purple-light);margin-bottom:6px}
.sec-title{font-size:18px;font-weight:700;margin-bottom:16px}

/* ORDER CARDS */
.order-card{background:var(--card-bg);border:1px solid var(--card-border);border-radius:14px;padding:16px;margin-bottom:10px;transition:border-color 0.15s}
.order-card:hover{border-color:rgba(168,85,247,0.4)}
.order-top{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.order-id{font-size:12px;font-weight:700;color:var(--text-muted);font-family:monospace}
.order-status{padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
.status-completed{background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.3);color:#4ADE80}
.status-pending{background:rgba(234,179,8,0.12);border:1px solid rgba(234,179,8,0.3);color:#FCD34D}
.order-product{font-size:15px;font-weight:700;color:white;margin-top:8px}
.order-item{font-size:12px;color:var(--text-muted);margin-top:2px}
.order-meta{display:flex;flex-wrap:wrap;gap:12px;margin-top:10px}
.order-meta-item{display:flex;align-items:center;gap:5px;font-size:12px;color:var(--text-dimmer)}
.order-meta-item i{font-size:11px}
.order-key-box{margin-top:12px;background:rgba(124,58,237,0.08);border:1px solid var(--purple-border);border-radius:10px;padding:12px}
.order-key-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--purple-light);margin-bottom:6px}
.order-key-value{font-family:monospace;font-size:13px;color:white;word-break:break-all;line-height:1.5}
.copy-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:rgba(124,58,237,0.15);border:1px solid var(--purple-border);border-radius:7px;font-size:11px;font-weight:600;color:var(--purple-light);cursor:pointer;transition:all 0.15s;margin-top:8px;font-family:var(--font)}
.copy-btn:hover{background:rgba(124,58,237,0.3)}
.copy-btn.copied{background:rgba(34,197,94,0.15);border-color:rgba(34,197,94,0.3);color:#4ADE80}

/* EMPTY */
.empty-block{text-align:center;padding:48px 20px;color:var(--text-dimmer)}
.empty-block i{font-size:2.2rem;display:block;margin-bottom:12px}
.empty-block p{font-size:14px}

/* LOADING */
.loading-block{text-align:center;padding:40px;color:var(--text-dimmer)}

/* FOOTER */
footer{border-top:1px solid var(--border);background:linear-gradient(180deg,#0D0A1A 0%,#080611 100%);position:relative;z-index:1;margin-top:40px}
.footer-inner{max-width:1200px;margin:0 auto;padding:24px 20px;text-align:center}
.footer-bottom{font-size:12px;color:var(--text-dimmer)}
</style>
</head>
<body>
<!-- VPN/PROXY BLOCK OVERLAY -->
<style>
#vpn-block-overlay{display:none;position:fixed;inset:0;z-index:99999;background:#0A0812;align-items:center;justify-content:center;flex-direction:column;padding:24px}
#vpn-block-overlay.show{display:flex}
.vpn-card{background:linear-gradient(135deg,#15101E 0%,#1C1230 100%);border:1.5px solid rgba(239,68,68,0.4);border-radius:24px;padding:40px 32px;text-align:center;max-width:380px;width:100%;box-shadow:0 0 60px rgba(239,68,68,0.15),0 24px 64px rgba(0,0,0,0.6);animation:vpnFadeIn .4s ease}
@keyframes vpnFadeIn{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
.vpn-shield-wrap{width:80px;height:80px;margin:0 auto 20px}
.vpn-shield{width:80px;height:80px;filter:drop-shadow(0 0 18px rgba(239,68,68,0.6))}
.vpn-title{font-size:22px;font-weight:800;color:#fff;margin-bottom:8px;font-family:'Inter',sans-serif}
.vpn-sub{font-size:14px;color:#9B8EC4;line-height:1.6;margin-bottom:24px;font-family:'Inter',sans-serif}
.vpn-blocked-badge{display:inline-flex;align-items:center;gap:8px;padding:11px 24px;background:linear-gradient(135deg,#7F1D1D,#991B1B);border:1px solid rgba(239,68,68,0.5);border-radius:12px;font-size:14px;font-weight:700;color:#FCA5A5;margin-bottom:20px;font-family:'Inter',sans-serif;box-shadow:0 0 20px rgba(239,68,68,0.3)}
.vpn-blocked-badge::before{content:'';width:8px;height:8px;background:#EF4444;border-radius:50%;display:inline-block;animation:vpnBlink 1s infinite}
@keyframes vpnBlink{0%,100%{opacity:1}50%{opacity:.3}}
.vpn-info-box{background:rgba(124,58,237,0.1);border:1px solid rgba(124,58,237,0.3);border-radius:12px;padding:14px 16px;font-size:13px;color:#C084FC;line-height:1.6;font-family:'Inter',sans-serif}
.vpn-info-box i{color:#A855F7;margin-right:6px}
</style>
<div id="vpn-block-overlay">
  <div class="vpn-card">
    <div class="vpn-shield-wrap">
      <svg class="vpn-shield" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M40 8L14 18V38C14 52.5 25.5 66 40 70C54.5 66 66 52.5 66 38V18L40 8Z" fill="#7F1D1D" stroke="#EF4444" stroke-width="2"/>
        <path d="M40 8L40 70C54.5 66 66 52.5 66 38V18L40 8Z" fill="#991B1B"/>
        <circle cx="40" cy="34" r="8" fill="none" stroke="#FCA5A5" stroke-width="2.5"/>
        <path d="M40 42V50" stroke="#FCA5A5" stroke-width="2.5" stroke-linecap="round"/>
        <path d="M36 50H44" stroke="#FCA5A5" stroke-width="2.5" stroke-linecap="round"/>
        <path d="M34 34C34 30.686 36.686 28 40 28" stroke="#FCA5A5" stroke-width="2" stroke-linecap="round" opacity="0.5"/>
      </svg>
    </div>
    <div class="vpn-title">VPN / Proxy Detected</div>
    <div class="vpn-sub">This website does not allow VPN or proxy connections.</div>
    <div class="vpn-blocked-badge">Connection Blocked</div>
    <div class="vpn-info-box">
      <i class="fa-solid fa-circle-info"></i>
      Please disconnect your VPN or proxy and try again with your real IP address.
    </div>
  </div>
</div>
<script>
(function(){
  var VPN_ENABLED = <?= $vpnEnabled ?>;
  if (!VPN_ENABLED) return;
  fetch('vpn_check.php?t='+Date.now(),{cache:'no-store'})
    .then(function(r){return r.json()})
    .then(function(d){if(d&&d.blocked){var el=document.getElementById('vpn-block-overlay');if(el)el.classList.add('show');document.body.style.overflow='hidden';}})
    .catch(function(){});
})();
</script>


<!-- NAVBAR -->
<nav>
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">
      <div class="logo-icon">
        <img src="<?= $bannerImg ?>" alt="logo" onerror="this.outerHTML='<i class=\'fa-solid fa-layer-group\' style=\'color:#fff;font-size:16px\'></i>'">
      </div>
      <div class="logo-text">
        <span class="logo-part1"><?= $namePart1 ?></span>
        <span class="logo-part2"><?= $namePart2 ?></span>
      </div>
    </a>
    <div class="nav-menu">
      <a class="nav-link" href="index.php"><i class="fa-solid fa-boxes-stacked"></i> Products</a>
      <a class="nav-link" href="tracking.php"><i class="fa-solid fa-magnifying-glass"></i> Tracking</a>
      <a class="nav-link" href="download.php"><i class="fa-solid fa-cloud-arrow-down"></i> Downloads</a>
      <a class="nav-link active" href="account.php"><i class="fa-solid fa-circle-user"></i> Akun</a>
    </div>
    <div class="nav-actions">
      <a class="nav-link" href="admin.php" style="font-size:13px;color:var(--text-muted)">
        <i class="fa-solid fa-right-to-bracket" style="color:var(--purple-light)"></i> Login Admin
      </a>
    </div>
    <button class="hamburger-btn" id="menuBtn">
      <i class="fa-solid fa-bars"></i>
      <span id="menuLabel">Menu</span>
    </button>
  </div>
</nav>

<!-- SIDEBAR -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <a href="index.php" class="nav-logo">
      <div class="logo-icon" style="width:34px;height:34px">
        <img src="<?= $bannerImg ?>" alt="logo" style="width:100%;height:100%;object-fit:contain" onerror="this.outerHTML='<i class=\'fa-solid fa-layer-group\' style=\'color:#fff;font-size:14px\'></i>'">
      </div>
      <div class="logo-text">
        <span class="logo-part1" style="font-size:13px"><?= $namePart1 ?></span>
        <span class="logo-part2" style="font-size:13px"><?= $namePart2 ?></span>
      </div>
    </a>
    <button class="sidebar-close" id="sidebarClose"><i class="fa-solid fa-xmark"></i> Close</button>
  </div>
  <nav class="sidebar-nav">
    <a class="sidebar-link" href="index.php"><i class="fa-solid fa-house" style="color:var(--purple-light)"></i> Beranda</a>
    <a class="sidebar-link" href="tracking.php"><i class="fa-solid fa-magnifying-glass" style="color:var(--purple-light)"></i> Lacak Pesanan</a>
    <a class="sidebar-link" href="account.php"><i class="fa-solid fa-circle-user" style="color:var(--purple-light)"></i> Akun Saya</a>
    <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;color:var(--text-dimmer);text-transform:uppercase;padding:12px 4px 6px">Bantuan</div>
    <a class="sidebar-link" href="<?= $waLink ?>" target="_blank"><i class="fa-brands fa-whatsapp" style="color:#25D366"></i> WhatsApp Admin</a>
    <?php if (!empty($config['telegram'])): ?>
    <a class="sidebar-link" href="<?= $tgLink ?>" target="_blank"><i class="fa-brands fa-telegram" style="color:#2CA5E0"></i> Telegram Admin</a>
    <?php endif; ?>
    <a class="sidebar-link" href="download.php"><i class="fa-solid fa-cloud-arrow-down" style="color:var(--purple-light)"></i> Downloads</a>
    <a class="sidebar-link" href="admin.php"><i class="fa-solid fa-right-to-bracket" style="color:var(--purple-light)"></i> Login Admin</a>
  </nav>
</aside>

<main>
  <div class="page-shell">

    <!-- TIDAK ADA GOOGLE CLIENT ID -->
    <?php if (!$googleClientId): ?>
    <div class="login-wrap">
      <div class="login-card">
        <div class="login-icon"><i class="fa-solid fa-circle-user"></i></div>
        <div class="login-title">Login dengan Google</div>
        <div class="login-sub">Masuk untuk melihat riwayat order dan lisensi kamu.</div>
        <div class="no-client-note">
          <i class="fa-solid fa-triangle-exclamation" style="color:var(--yellow)"></i>
          Google Client ID belum dikonfigurasi.<br>
          Masuk ke <a href="admin.php" style="color:var(--purple-light)">Admin Panel → Settings → Google Client ID</a> untuk mengaktifkan fitur ini.
        </div>
      </div>
    </div>

    <!-- ADA GOOGLE CLIENT ID -->
    <?php else: ?>

    <!-- STATE: BELUM LOGIN -->
    <div id="state-login" class="login-wrap" style="display:none">
      <div class="login-card">
        <div class="login-icon"><i class="fa-solid fa-circle-user"></i></div>
        <div class="login-title">Login dengan Google</div>
        <div class="login-sub">Masuk untuk melihat semua riwayat order dan license key kamu secara otomatis.</div>
        <div id="g_signin_btn" style="display:flex;justify-content:center"></div>
        <p style="margin-top:16px;font-size:11px;color:var(--text-dimmer)">Data kamu aman. Kami hanya menyimpan email untuk menghubungkan riwayat order.</p>
      </div>
    </div>

    <!-- STATE: SUDAH LOGIN -->
    <div id="state-account" style="display:none">
      <!-- Profile -->
      <section class="profile-wrap">
        <div class="profile-card">
          <div class="profile-info">
            <div id="acc-avatar-wrap">
              <div class="profile-avatar-placeholder"><i class="fa-solid fa-user"></i></div>
            </div>
            <div>
              <div class="profile-name" id="acc-name">—</div>
              <div class="profile-email" id="acc-email">—</div>
              <div class="profile-badge"><i class="fa-brands fa-google"></i> Google Account</div>
            </div>
          </div>
          <div class="profile-actions">
            <button class="btn-logout" onclick="doLogout()"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
          </div>
        </div>
      </section>

      <!-- Tabs -->
      <div class="tabs-wrap">
        <div class="tab-btns">
          <button class="tab-btn active" onclick="switchTab('orders',this)"><i class="fa-solid fa-receipt"></i> Riwayat Order</button>
          <button class="tab-btn" onclick="switchTab('licenses',this)"><i class="fa-solid fa-key"></i> License Keys</button>
        </div>

        <!-- TAB ORDERS -->
        <div class="tab-pane active" id="tab-orders">
          <p class="sec-eyebrow">Riwayat Pembelian</p>
          <h2 class="sec-title">Semua Order Kamu</h2>
          <div id="orders-list">
            <div class="loading-block"><i class="fa-solid fa-spinner fa-spin" style="font-size:1.5rem;margin-bottom:10px;display:block"></i>Memuat order...</div>
          </div>
        </div>

        <!-- TAB LICENSES -->
        <div class="tab-pane" id="tab-licenses">
          <p class="sec-eyebrow">License Keys</p>
          <h2 class="sec-title">Keys yang Sudah Kamu Dapatkan</h2>
          <div id="licenses-list">
            <div class="loading-block"><i class="fa-solid fa-spinner fa-spin" style="font-size:1.5rem;margin-bottom:10px;display:block"></i>Memuat lisensi...</div>
          </div>
        </div>
      </div>
    </div>

    <?php endif; ?>
  </div>
</main>

<footer>
  <div class="footer-inner">
    <div class="footer-bottom">© 2026 <?= $fullTitle ?>. All rights reserved.</div>
  </div>
</footer>

<script>
// SIDEBAR
const sidebar=document.getElementById('sidebar');
const overlay=document.getElementById('sidebarOverlay');
const menuBtn=document.getElementById('menuBtn');
const menuLabel=document.getElementById('menuLabel');
const sidebarClose=document.getElementById('sidebarClose');
function openSidebar(){sidebar.classList.add('open');overlay.classList.add('open');menuLabel.textContent='Close';document.body.style.overflow='hidden'}
function closeSidebar(){sidebar.classList.remove('open');overlay.classList.remove('open');menuLabel.textContent='Menu';document.body.style.overflow=''}
if(menuBtn) menuBtn.addEventListener('click',()=>sidebar.classList.contains('open')?closeSidebar():openSidebar());
if(sidebarClose) sidebarClose.addEventListener('click',closeSidebar);
if(overlay) overlay.addEventListener('click',closeSidebar);

<?php if ($googleClientId): ?>
// ── GOOGLE ONE TAP & SIGN IN ──────────────────────────────────────────────
const GC_ID = '<?= $googleClientId ?>';

let currentUser = null;

function initGoogleAuth() {
  google.accounts.id.initialize({
    client_id: GC_ID,
    callback: handleCredential,
    auto_select: true,
    cancel_on_tap_outside: false,
  });

  // Render tombol sign in
  google.accounts.id.renderButton(
    document.getElementById('g_signin_btn'),
    { theme:'filled_black', size:'large', text:'signin_with', shape:'rounded', width:280 }
  );

  // Cek session storage dulu (token Google terakhir)
  const saved = sessionStorage.getItem('g_user');
  if (saved) {
    try {
      currentUser = JSON.parse(saved);
      showAccountState(currentUser);
      loadOrders();
    } catch(e) {
      sessionStorage.removeItem('g_user');
      showLoginState();
    }
  } else {
    // Coba One Tap
    google.accounts.id.prompt(notification => {
      if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
        showLoginState();
      }
    });
  }
}

async function handleCredential(response) {
  // Decode JWT payload (tidak perlu verify signature di sisi client)
  try {
    const payload = JSON.parse(atob(response.credential.split('.')[1]));
    currentUser = {
      sub: payload.sub,
      name: payload.name,
      email: payload.email,
      picture: payload.picture,
      credential: response.credential,
    };
    sessionStorage.setItem('g_user', JSON.stringify(currentUser));
    showAccountState(currentUser);
    loadOrders();
  } catch(e) {
    alert('Gagal login. Coba lagi.');
    showLoginState();
  }
}

function showLoginState() {
  document.getElementById('state-login').style.display='flex';
  document.getElementById('state-account').style.display='none';
}
function showAccountState(user) {
  document.getElementById('state-login').style.display='none';
  document.getElementById('state-account').style.display='block';
  document.getElementById('acc-name').textContent = user.name || '—';
  document.getElementById('acc-email').textContent = user.email || '—';
  const wrap = document.getElementById('acc-avatar-wrap');
  if (user.picture) {
    wrap.innerHTML = `<img class="profile-avatar" src="${user.picture}" alt="avatar" onerror="this.outerHTML='<div class=profile-avatar-placeholder><i class=fa-solid fa-user></i></div>'">`;
  }
}

function doLogout() {
  sessionStorage.removeItem('g_user');
  currentUser = null;
  google.accounts.id.disableAutoSelect();
  showLoginState();
  document.getElementById('orders-list').innerHTML = '';
  document.getElementById('licenses-list').innerHTML = '';
}

// ── TAB SWITCH ────────────────────────────────────────────────────────────
function switchTab(name, btn) {
  document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  btn.classList.add('active');
}

// ── LOAD ORDERS ───────────────────────────────────────────────────────────
async function loadOrders() {
  if (!currentUser) return;
  const oList = document.getElementById('orders-list');
  const lList = document.getElementById('licenses-list');
  oList.innerHTML = '<div class="loading-block"><i class="fa-solid fa-spinner fa-spin" style="font-size:1.5rem;margin-bottom:10px;display:block"></i>Memuat order...</div>';
  lList.innerHTML = '<div class="loading-block"><i class="fa-solid fa-spinner fa-spin" style="font-size:1.5rem;margin-bottom:10px;display:block"></i>Memuat lisensi...</div>';

  try {
    const r = await fetch('api.php?action=get_user_orders&t='+Date.now(), {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ credential: currentUser.credential, email: currentUser.email, sub: currentUser.sub }),
      cache: 'no-store',
    });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    const data = await r.json();
    if (!data.success) { oList.innerHTML = errBlock(data.message||'Gagal memuat order'); lList.innerHTML = oList.innerHTML; return; }

    renderOrders(data.orders || []);
  } catch(e) {
    oList.innerHTML = errBlock('Gagal menghubungi server');
    lList.innerHTML = errBlock('Gagal menghubungi server');
  }
}

function renderOrders(orders) {
  const oList = document.getElementById('orders-list');
  const lList = document.getElementById('licenses-list');

  if (!orders.length) {
    oList.innerHTML = '<div class="empty-block"><i class="fa-solid fa-receipt"></i><p>Belum ada order dengan akun ini.</p></div>';
    lList.innerHTML = '<div class="empty-block"><i class="fa-solid fa-key"></i><p>Belum ada license key yang didapat.</p></div>';
    return;
  }

  // All orders
  oList.innerHTML = orders.map(o => orderCard(o, false)).join('');

  // Only completed with license
  const completed = orders.filter(o => o.status === 'completed' && o.product_content && o.product_content !== 'Menunggu Pembayaran...' && o.product_content !== 'STOK_KOSONG_HUBUNGI_ADMIN');
  if (!completed.length) {
    lList.innerHTML = '<div class="empty-block"><i class="fa-solid fa-key"></i><p>Belum ada license key yang didapat.</p></div>';
  } else {
    lList.innerHTML = completed.map(o => orderCard(o, true)).join('');
  }

  // Copy buttons
  document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const key = this.dataset.key;
      navigator.clipboard.writeText(key).then(() => {
        this.classList.add('copied');
        this.innerHTML = '<i class="fa-solid fa-check"></i> Tersalin!';
        setTimeout(() => { this.classList.remove('copied'); this.innerHTML = '<i class="fa-solid fa-copy"></i> Copy Key'; }, 2000);
      });
    });
  });
}

function orderCard(o, licenseOnly) {
  const isCompleted = o.status === 'completed';
  const statusHtml = isCompleted
    ? '<span class="order-status status-completed">SELESAI</span>'
    : '<span class="order-status status-pending">PENDING</span>';

  const d = new Date((o.created_at||0)*1000);
  const dateStr = d.toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric', hour:'2-digit',minute:'2-digit'});
  const amount = (o.amount||0).toLocaleString('id-ID');

  let keyBox = '';
  if (isCompleted && o.product_content && o.product_content !== 'Menunggu Pembayaran...') {
    const isBad = o.product_content === 'STOK_KOSONG_HUBUNGI_ADMIN';
    keyBox = `<div class="order-key-box">
      <div class="order-key-label"><i class="fa-solid fa-key"></i> License Key</div>
      <div class="order-key-value">${isBad ? '<span style="color:var(--yellow)">⚠ Stok kosong — hubungi admin</span>' : escHtml(o.product_content)}</div>
      ${!isBad ? `<button class="copy-btn" data-key="${escHtml(o.product_content)}"><i class="fa-solid fa-copy"></i> Copy Key</button>` : ''}
    </div>`;
  }

  return `<div class="order-card">
    <div class="order-top">
      <span class="order-id">${escHtml(o.order_id)}</span>
      ${statusHtml}
    </div>
    <div class="order-product">${escHtml(o.product||'—')}</div>
    <div class="order-item">${escHtml(o.item||'')}</div>
    <div class="order-meta">
      <span class="order-meta-item"><i class="fa-solid fa-clock"></i> ${dateStr}</span>
      <span class="order-meta-item"><i class="fa-solid fa-money-bill"></i> Rp ${amount}</span>
      ${o.payment_method ? `<span class="order-meta-item"><i class="fa-solid fa-credit-card"></i> ${escHtml(o.payment_method.toUpperCase())}</span>` : ''}
    </div>
    ${keyBox}
  </div>`;
}

function errBlock(msg) {
  return `<div class="empty-block"><i class="fa-solid fa-triangle-exclamation" style="color:var(--yellow)"></i><p>${escHtml(msg)}</p></div>`;
}
function escHtml(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Init setelah Google SDK load
window.addEventListener('load', function() {
  if (typeof google !== 'undefined' && google.accounts) {
    initGoogleAuth();
  } else {
    // Tunggu SDK load
    const check = setInterval(() => {
      if (typeof google !== 'undefined' && google.accounts) {
        clearInterval(check);
        initGoogleAuth();
      }
    }, 200);
    setTimeout(() => clearInterval(check), 10000);
  }
});
<?php endif; ?>
</script>
</body>
</html>
