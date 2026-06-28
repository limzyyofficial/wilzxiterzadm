<?php
session_start();

$dataDir    = __DIR__ . '/data';
$configFile = $dataDir . '/config.json';
$productsFile = $dataDir . '/products.json';
$ordersFile = $dataDir . '/orders.json';

if (!is_dir($dataDir)) mkdir($dataDir, 0755, true);

// Default config
$defaultConfig = [
    'title'               => 'WilzXiterz',
    'whatsapp'            => '6285173360622',
    'telegram'            => 'WilzXiterzVN',
    'channel'             => '',
    'discord'             => '',
    'banner'              => 'assets/img/logops.png',
    'maintenance'         => false,
    'download_url'        => '',
    'admin_password'      => 'admin123',
    // Pakasir QRIS
    'pakasir_slug'        => '',
    'pakasir_apikey'      => '',
    'pakasir_merchant_id' => '',
    'pakasir_api_key'     => '',
    // Binance Pay
    'binance_api_key'     => '',
    'binance_secret_key'  => '',
    'binance_merchant_id' => '',
];
$config = $defaultConfig;
if (file_exists($configFile)) {
    $saved = json_decode(file_get_contents($configFile), true);
    if (is_array($saved)) $config = array_merge($config, $saved);
}

$bannerImg = htmlspecialchars($config['banner'] ?? 'assets/img/logops.png');

// Auth
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    if ($_POST['admin_password'] === $config['admin_password']) {
        $_SESSION['admin_logged_in'] = true; $isLoggedIn = true;
    } else { $loginError = 'Password salah!'; }
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }

// Upload logo
$logoUploaded = false;
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo_upload']) && $_FILES['logo_upload']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/assets/img/';
    $allowed   = ['image/jpeg','image/png','image/gif','image/webp'];
    $mime      = mime_content_type($_FILES['logo_upload']['tmp_name']);
    if (in_array($mime, $allowed)) {
        $ext      = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'][$mime];
        $filename = 'logo_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['logo_upload']['tmp_name'], $uploadDir . $filename)) {
            $config['banner'] = 'assets/img/' . $filename;
            $bannerImg = htmlspecialchars($config['banner']);
            $logoUploaded = true;
        }
    }
}

// Save config
$successMsg = '';
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    $newConfig = $config;
    $textFields = ['title','whatsapp','telegram','channel','discord','banner','download_url',
                   'pakasir_slug','pakasir_apikey','pakasir_merchant_id','pakasir_api_key',
                   'binance_api_key','binance_secret_key','binance_merchant_id',
                   'google_client_id','vpn_api_key'];
    foreach ($textFields as $f) {
        if ($f === 'banner') {
            // Kalau ada upload sukses, pakai dari upload. Kalau ada URL manual non-kosong, pakai itu.
            if ($logoUploaded) {
                $newConfig['banner'] = $config['banner']; // sudah diset dari upload
            } elseif (isset($_POST['banner']) && trim($_POST['banner']) !== '') {
                $newConfig['banner'] = trim($_POST['banner']);
            }
            // Kalau kosong dan tidak ada upload → biarkan nilai lama
        } elseif (isset($_POST[$f])) {
            $newConfig[$f] = trim($_POST[$f]);
        }
    }
    if (!empty($_POST['admin_password_new'])) $newConfig['admin_password'] = trim($_POST['admin_password_new']);
    $newConfig['maintenance'] = isset($_POST['maintenance']) && $_POST['maintenance'] === '1';
    $newConfig['vpn_block']   = isset($_POST['vpn_block']) && $_POST['vpn_block'] === '1';
    file_put_contents($configFile, json_encode($newConfig, JSON_PRETTY_PRINT));
    $config = $newConfig;
    $bannerImg = htmlspecialchars($config['banner']);
    $successMsg = 'Konfigurasi berhasil disimpan!';
}

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES); }
function loadOrders() {
    $orderDir = __DIR__ . '/data/orders';
    if (!is_dir($orderDir)) return [];
    $orders = [];
    foreach (glob("{$orderDir}/*.json") as $f) {
        $d = json_decode(file_get_contents($f), true);
        if ($d && isset($d['order_id'])) $orders[$d['order_id']] = $d;
    }
    // Sort descending by created_at
    uasort($orders, fn($a,$b) => ($b['created_at']??0) <=> ($a['created_at']??0));
    return $orders;
}
function loadProducts() { global $productsFile; if (!file_exists($productsFile)) return []; return json_decode(file_get_contents($productsFile), true) ?: []; }

$orders   = loadOrders();
$products = loadProducts();
$recentOrders = array_slice(array_reverse(array_values($orders)), 0, 50);

$totalRevenue = 0; $totalOrders = count($orders); $paidOrders = 0;
foreach ($orders as $o) {
    if ($o['status'] === 'completed') { $totalRevenue += $o['amount'] ?? 0; $paidOrders++; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Admin Panel - <?= h($config['title']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
  --bg:#0d0d12;--bg2:#13131a;--bg3:#1a1a24;--card:#16161f;
  --purple:#7C3AED;--purple2:#9D5FF5;--purple-dim:rgba(124,58,237,.15);--purple-border:rgba(124,58,237,.35);
  --green:#22C55E;--green-dim:rgba(34,197,94,.15);
  --yellow:#EAB308;--red:#EF4444;
  --text:#fff;--text2:rgba(255,255,255,.55);--text3:rgba(255,255,255,.3);
  --sep:rgba(255,255,255,.07);--radius:16px;--radius-sm:10px;
  --font:'Inter',-apple-system,sans-serif;
  --binance:#F0B90B;--binance-dim:rgba(240,185,11,.12);--binance-border:rgba(240,185,11,.3);
}
*{margin:0;padding:0;box-sizing:border-box}
body{background:var(--bg);color:var(--text);font-family:var(--font);min-height:100vh}
a{text-decoration:none;color:inherit}

/* LOGIN */
.login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.login-card{background:var(--card);border:1px solid var(--sep);border-radius:20px;padding:32px 28px;width:100%;max-width:380px}
.login-logo{text-align:center;margin-bottom:24px}
.login-logo-icon{width:60px;height:60px;border-radius:16px;overflow:hidden;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;background:var(--purple-dim);border:1px solid var(--purple-border)}
.login-logo-icon img{width:100%;height:100%;object-fit:contain}
.login-logo h2{font-size:1.2rem;font-weight:900}
.login-logo p{font-size:.8rem;color:var(--text2);margin-top:4px}

/* LAYOUT */
.layout{display:flex;min-height:100vh}
.sidebar{width:240px;background:var(--bg2);border-right:1px solid var(--sep);position:fixed;top:0;bottom:0;left:0;padding:20px 0;overflow-y:auto;z-index:100}
.sidebar-brand{padding:0 20px 20px;border-bottom:1px solid var(--sep);margin-bottom:12px;display:flex;align-items:center;gap:10px}
.brand-icon{width:36px;height:36px;border-radius:10px;overflow:hidden;flex-shrink:0}
.brand-icon img{width:100%;height:100%;object-fit:contain}
.brand-name{font-size:.72rem;font-weight:900;color:var(--purple);letter-spacing:.5px;text-transform:uppercase;line-height:1}
.brand-name-bot{font-size:.95rem;font-weight:800;color:var(--text);line-height:1.1}
.brand-sub{font-size:.7rem;color:var(--text2)}
.nav-item{display:flex;align-items:center;gap:12px;padding:11px 20px;color:var(--text2);font-size:.88rem;font-weight:600;cursor:pointer;transition:.15s;border-left:3px solid transparent}
.nav-item:hover{background:var(--bg3);color:var(--text)}
.nav-item.active{background:var(--purple-dim);color:var(--purple2);border-left-color:var(--purple)}
.nav-item i{width:18px;text-align:center}
.nav-section{font-size:.6rem;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:1.5px;padding:16px 20px 6px}
.main{margin-left:240px;padding:24px;flex:1}

/* TOPBAR */
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;gap:12px}
.topbar h1{font-size:1.3rem;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.topbar-actions{display:flex;gap:10px;align-items:center;flex-shrink:0}
.btn{padding:10px 18px;border-radius:var(--radius-sm);font-weight:700;font-size:.82rem;cursor:pointer;border:none;font-family:var(--font);display:inline-flex;align-items:center;gap:7px;transition:.15s}
.btn-purple{background:var(--purple);color:#fff}
.btn-purple:hover{background:var(--purple2)}
.btn-ghost{background:rgba(255,255,255,.07);color:var(--text);border:1px solid var(--sep)}
.btn-ghost:hover{background:rgba(255,255,255,.12)}
.btn-danger{background:rgba(239,68,68,.15);color:var(--red);border:1px solid rgba(239,68,68,.3)}

/* CARDS */
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px}
.stat-card{background:var(--card);border:1px solid var(--sep);border-radius:var(--radius);padding:18px}
.stat-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;margin-bottom:12px}
.stat-val{font-size:1.4rem;font-weight:900;margin-bottom:3px}
.stat-lbl{font-size:.72rem;color:var(--text2);font-weight:600}

/* SECTION TABS */
.tabs{display:flex;gap:4px;background:var(--bg2);border-radius:var(--radius-sm);padding:4px;margin-bottom:20px;width:fit-content}
.tab{padding:8px 16px;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;color:var(--text2);transition:.15s}
.tab.active{background:var(--purple);color:#fff}

/* FORMS */
.card{background:var(--card);border:1px solid var(--sep);border-radius:var(--radius);padding:20px;margin-bottom:16px}
.card-header{display:flex;align-items:center;gap:10px;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--sep)}
.card-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0}
.card-title{font-size:1rem;font-weight:800}
.card-desc{font-size:.78rem;color:var(--text2);margin-top:2px}
.form-group{margin-bottom:14px}
.form-label{display:block;font-size:.75rem;font-weight:700;color:var(--text2);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px}
.form-input{width:100%;background:var(--bg3);border:1px solid var(--sep);padding:11px 14px;border-radius:var(--radius-sm);color:var(--text);outline:none;font-size:.88rem;font-family:var(--font);transition:.15s}
.form-input:focus{border-color:var(--purple)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-hint{font-size:.72rem;color:var(--text3);margin-top:5px;line-height:1.4}
.toggle-wrap{display:flex;align-items:center;gap:12px}
.toggle{position:relative;width:44px;height:24px;cursor:pointer}
.toggle input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;inset:0;background:rgba(255,255,255,.1);border-radius:12px;transition:.2s}
.toggle-slider::before{content:'';position:absolute;width:18px;height:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s}
.toggle input:checked + .toggle-slider{background:var(--green)}
.toggle input:checked + .toggle-slider::before{transform:translateX(20px)}
.toggle-lbl{font-size:.88rem;font-weight:600}

/* PAYMENT GATEWAY CARDS */
.gw-card{border-radius:var(--radius);padding:20px;margin-bottom:16px}
.gw-qris{background:var(--purple-dim);border:1px solid var(--purple-border)}
.gw-binance{background:var(--binance-dim);border:1px solid var(--binance-border)}
.gw-header{display:flex;align-items:center;gap:12px;margin-bottom:16px}
.gw-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}
.gw-qris .gw-icon{background:rgba(124,58,237,.2);color:var(--purple2)}
.gw-binance .gw-icon{background:rgba(240,185,11,.2);color:var(--binance)}
.gw-name{font-size:1rem;font-weight:800}
.gw-sub{font-size:.75rem;color:var(--text2)}
.status-dot{display:inline-flex;align-items:center;gap:5px;font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:99px}
.status-dot.ok{background:var(--green-dim);color:var(--green)}
.status-dot.missing{background:rgba(239,68,68,.12);color:var(--red)}

/* ORDERS TABLE */
.table-wrap{overflow-x:auto;border-radius:var(--radius);border:1px solid var(--sep);-webkit-overflow-scrolling:touch}
table{width:100%;border-collapse:collapse;font-size:.82rem;min-width:700px}
th{background:var(--bg2);padding:12px 14px;text-align:left;font-size:.65rem;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--sep);white-space:nowrap}
td{padding:12px 14px;border-bottom:1px solid var(--sep);color:var(--text2);vertical-align:top}
td:first-child{color:var(--text);font-weight:700}
tr:last-child td{border-bottom:none}
.pill{display:inline-block;padding:3px 9px;border-radius:99px;font-size:.65rem;font-weight:700;white-space:nowrap}
.pill-green{background:var(--green-dim);color:var(--green)}
.pill-yellow{background:rgba(234,179,8,.15);color:var(--yellow)}
.pill-red{background:rgba(239,68,68,.12);color:var(--red)}
.pill-blue{background:rgba(59,130,246,.12);color:#60a5fa}
.method-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;font-size:.65rem;font-weight:700;white-space:nowrap}
.mb-qris{background:var(--purple-dim);color:var(--purple2)}
.mb-binance{background:var(--binance-dim);color:var(--binance)}

/* ALERT */
.alert{padding:13px 16px;border-radius:var(--radius-sm);margin-bottom:16px;font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:10px}
.alert-success{background:var(--green-dim);border:1px solid rgba(34,197,94,.3);color:var(--green)}
.alert-error{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:var(--red)}
.alert-info{background:var(--binance-dim);border:1px solid var(--binance-border);color:var(--binance)}

/* SECTION DISPLAY */
.section{display:none}.section.active{display:block}

/* ══════════════════════════════════════════════════════
   MOBILE BOTTOM NAV
══════════════════════════════════════════════════════ */
.mob-nav{
  display:none;position:fixed;bottom:0;left:0;right:0;
  background:rgba(19,19,26,.96);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border-top:1px solid var(--sep);
  padding:6px 0 calc(6px + env(safe-area-inset-bottom));
  z-index:300;
}
.mob-nav-items{display:flex;justify-content:space-around;align-items:flex-end}
.mob-nav-item{
  display:flex;flex-direction:column;align-items:center;gap:2px;
  font-size:.58rem;font-weight:700;color:var(--text2);
  padding:5px 6px;cursor:pointer;flex:1;min-width:0;
  border-radius:12px;transition:.15s;
  -webkit-tap-highlight-color:transparent;
}
.mob-nav-item i{font-size:1.15rem;transition:.15s}
.mob-nav-item span{
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  width:100%;text-align:center;line-height:1;
}
.mob-nav-item.active{color:var(--purple2)}
.mob-nav-item.active i{
  background:var(--purple-dim);
  padding:5px 14px;border-radius:99px;
  color:var(--purple2);
}

/* ══════════════════════════════════════════════════════
   MOBILE (≤768px) — Layar HP
══════════════════════════════════════════════════════ */
@media(max-width:768px){
  /* Layout */
  .sidebar{display:none}
  .mob-nav{display:block}
  .main{
    margin-left:0;
    padding:12px 14px;
    padding-top:calc(env(safe-area-inset-top) + 12px);
    padding-bottom:calc(72px + env(safe-area-inset-bottom));
  }

  /* Topbar */
  .topbar{
    position:sticky;top:0;z-index:100;
    background:rgba(13,13,18,.9);
    backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
    margin:-12px -14px 14px;
    padding:12px 14px;
    border-bottom:1px solid var(--sep);
  }
  .topbar h1{font-size:1rem;font-weight:900}
  .topbar-actions .btn span{display:none}
  .topbar-actions .btn{padding:9px 11px;border-radius:10px}
  .topbar-actions{gap:7px}

  /* Stats — 2 kolom */
  .stat-grid{grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
  .stat-card{padding:14px 12px;border-radius:14px}
  .stat-icon{width:34px;height:34px;border-radius:9px;font-size:.85rem;margin-bottom:10px}
  .stat-val{font-size:1.2rem}
  .stat-lbl{font-size:.68rem}

  /* Cards */
  .card{padding:14px;border-radius:14px;margin-bottom:12px}
  .card-header{margin-bottom:14px;padding-bottom:12px}
  .card-icon{width:32px;height:32px;border-radius:8px;font-size:.82rem}
  .card-title{font-size:.92rem}
  .card-desc{font-size:.72rem}

  /* Forms */
  .form-row{grid-template-columns:1fr}
  .form-input{padding:12px 13px;font-size:.9rem;border-radius:10px}
  .form-label{font-size:.72rem}
  .form-group{margin-bottom:12px}

  /* Payment gateway cards */
  .gw-card{padding:16px;border-radius:14px}
  .gw-header{flex-wrap:wrap;gap:8px}
  .gw-icon{width:40px;height:40px;border-radius:10px;font-size:1.1rem}

  /* Login */
  .login-card{padding:24px 18px;border-radius:18px}
  .login-logo-icon{width:54px;height:54px}

  /* Table — horizontal scroll */
  .table-wrap{border-radius:12px}
  table{font-size:.78rem;min-width:600px}
  th{padding:10px 12px;font-size:.6rem}
  td{padding:10px 12px}

  /* Alert */
  .alert{padding:11px 14px;font-size:.82rem}
}

/* ══════════════════════════════════════════════════════
   EMULATOR / VERY SMALL (≤430px) — Mode emulator/HP kecil
══════════════════════════════════════════════════════ */
@media(max-width:430px){
  .main{padding:10px 12px;padding-bottom:calc(70px + env(safe-area-inset-bottom))}

  /* Topbar lebih compact */
  .topbar{padding:10px 12px;margin:-10px -12px 12px}
  .topbar h1{font-size:.92rem}

  /* Stats — 2 kolom compact */
  .stat-grid{gap:8px;margin-bottom:14px}
  .stat-card{padding:12px 10px;border-radius:12px}
  .stat-icon{width:30px;height:30px;border-radius:8px;font-size:.75rem;margin-bottom:8px}
  .stat-val{font-size:1.05rem;margin-bottom:1px}
  .stat-lbl{font-size:.62rem;letter-spacing:-.1px}

  /* Cards */
  .card{padding:12px;border-radius:12px;margin-bottom:10px}
  .card-header{margin-bottom:12px;padding-bottom:10px;gap:8px}
  .card-icon{width:30px;height:30px;border-radius:8px;font-size:.75rem;flex-shrink:0}
  .card-title{font-size:.85rem}
  .card-desc{font-size:.68rem;margin-top:1px}

  /* Forms */
  .form-input{padding:11px 12px;font-size:.85rem;border-radius:9px}
  .form-label{font-size:.68rem;margin-bottom:5px}
  .form-group{margin-bottom:10px}
  .form-hint{font-size:.65rem}
  .btn{padding:11px 13px;font-size:.8rem;border-radius:9px}

  /* Payment gateway */
  .gw-card{padding:12px;border-radius:12px;margin-bottom:10px}
  .gw-icon{width:36px;height:36px;border-radius:9px;font-size:1rem}
  .gw-name{font-size:.9rem}
  .gw-sub{font-size:.7rem}

  /* Nav item bottom — lebih lebar tap area */
  .mob-nav-item{padding:4px 2px;font-size:.54rem}
  .mob-nav-item i{font-size:1.1rem}
  .mob-nav-item.active i{padding:4px 12px}

  /* Table */
  table{font-size:.72rem;min-width:520px}
  th{padding:8px 10px;font-size:.56rem}
  td{padding:9px 10px}

  /* Alert */
  .alert{padding:10px 12px;font-size:.78rem;border-radius:9px}

  /* Toggle */
  .toggle-lbl{font-size:.82rem}

  /* Tabs */
  .tab{padding:7px 12px;font-size:.75rem}

  /* Login */
  .login-card{padding:20px 16px;border-radius:16px}
  .login-logo-icon{width:48px;height:48px;border-radius:13px}
  .login-logo h2{font-size:1.05rem}
}
</style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
<!-- ═══════════════════════════════════════════ LOGIN ════════════════════════ -->
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">
      <div class="login-logo-icon"><img src="<?= $bannerImg ?>" alt="Logo"></div>
      <h2><?= h($config['title']) ?></h2>
      <p>Admin Panel</p>
    </div>
    <?php if ($loginError): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><?= h($loginError) ?></div><?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Password Admin</label>
        <input type="password" name="admin_password" class="form-input" placeholder="Masukkan password..." autofocus>
      </div>
      <button type="submit" name="admin_login" value="1" class="btn btn-purple" style="width:100%;justify-content:center;padding:13px">
        <i class="fas fa-sign-in-alt"></i> Masuk
      </button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ═══════════════════════════════════════════ DASHBOARD ════════════════════ -->
<div class="layout">

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><img src="<?= $bannerImg ?>" alt="Logo"></div>
    <div><div class="brand-name">Wilz</div><div class="brand-name-bot">Xiterz</div><div class="brand-sub">Admin Panel</div></div>
  </div>
  <div class="nav-section">Menu</div>
  <a class="nav-item active" onclick="showSection('dashboard',this)"><i class="fas fa-chart-bar"></i> Dashboard</a>
  <a class="nav-item" onclick="showSection('orders',this)"><i class="fas fa-receipt"></i> Orders</a>
  <div class="nav-section">Pengaturan</div>
  <a class="nav-item" onclick="showSection('general',this)"><i class="fas fa-gear"></i> General</a>
  <a class="nav-item" onclick="showSection('products',this)"><i class="fas fa-box-open"></i> Produk</a>
  <a class="nav-item" onclick="showSection('payment',this)"><i class="fas fa-credit-card"></i> Payment Gateway</a>
  <div class="nav-section">Lainnya</div>
  <a class="nav-item" href="index.php" target="_blank"><i class="fas fa-store"></i> Lihat Toko</a>
  <a class="nav-item btn-danger" href="admin.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
</aside>

<!-- MAIN -->
<main class="main">
  <div class="topbar">
    <h1 id="pageTitle">Dashboard</h1>
    <div class="topbar-actions">
      <a href="index.php" target="_blank" class="btn btn-ghost"><i class="fas fa-external-link-alt"></i><span> Lihat Toko</span></a>
      <a href="admin.php?logout=1" class="btn btn-ghost"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
    </div>
  </div>

  <?php if ($successMsg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><?= h($successMsg) ?></div><?php endif; ?>

  <!-- ── DASHBOARD ── -->
  <div id="sec-dashboard" class="section active">
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background:var(--purple-dim);color:var(--purple2)"><i class="fas fa-receipt"></i></div>
        <div class="stat-val"><?= $totalOrders ?></div>
        <div class="stat-lbl">Total Orders</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:var(--green-dim);color:var(--green)"><i class="fas fa-check-circle"></i></div>
        <div class="stat-val"><?= $paidOrders ?></div>
        <div class="stat-lbl">Paid Orders</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(234,179,8,.15);color:var(--yellow)"><i class="fas fa-coins"></i></div>
        <div class="stat-val" style="font-size:1rem">Rp <?= number_format($totalRevenue,0,',','.') ?></div>
        <div class="stat-lbl">Total Revenue</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:var(--binance-dim);color:var(--binance)"><i class="fas fa-bitcoin-sign"></i></div>
        <div class="stat-val"><?= count(array_filter($orders, fn($o)=>($o['payment_method']??'')=='binance' && $o['status']=='completed')) ?></div>
        <div class="stat-lbl">Binance Paid</div>
      </div>
    </div>

    <!-- Payment method config status -->
    <div class="card">
      <div class="card-header">
        <div class="card-icon" style="background:var(--purple-dim);color:var(--purple2)"><i class="fas fa-credit-card"></i></div>
        <div><div class="card-title">Status Payment Gateway</div><div class="card-desc">Konfigurasi metode pembayaran aktif</div></div>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:10px">
        <div style="background:var(--bg3);border:1px solid var(--sep);border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:10px;flex:1;min-width:200px">
          <div style="width:36px;height:36px;background:var(--purple-dim);border-radius:9px;display:flex;align-items:center;justify-content:center;color:var(--purple2)"><i class="fas fa-qrcode"></i></div>
          <div>
            <div style="font-weight:800;font-size:.9rem">QRIS (Pakasir)</div>
            <?php if (($config['pakasir_slug'] || $config['pakasir_merchant_id']) && ($config['pakasir_apikey'] || $config['pakasir_api_key'])): ?>
              <span class="status-dot ok"><i class="fas fa-circle" style="font-size:.4rem"></i> Terkonfigurasi</span>
            <?php else: ?>
              <span class="status-dot missing"><i class="fas fa-circle" style="font-size:.4rem"></i> Belum diisi</span>
            <?php endif; ?>
          </div>
        </div>
        <div style="background:var(--bg3);border:1px solid var(--sep);border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:10px;flex:1;min-width:200px">
          <div style="width:36px;height:36px;background:var(--binance-dim);border-radius:9px;display:flex;align-items:center;justify-content:center;color:var(--binance)"><i class="fas fa-bitcoin-sign"></i></div>
          <div>
            <div style="font-weight:800;font-size:.9rem">Binance Pay</div>
            <?php if ($config['binance_api_key'] && $config['binance_secret_key']): ?>
              <span class="status-dot ok"><i class="fas fa-circle" style="font-size:.4rem"></i> Terkonfigurasi</span>
            <?php else: ?>
              <span class="status-dot missing"><i class="fas fa-circle" style="font-size:.4rem"></i> Belum diisi</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent orders quick view -->
    <div class="card">
      <div class="card-header">
        <div class="card-icon" style="background:var(--purple-dim);color:var(--purple2)"><i class="fas fa-clock"></i></div>
        <div><div class="card-title">Order Terbaru</div><div class="card-desc">5 transaksi terakhir</div></div>
      </div>
      <div class="table-wrap">
        <table>
          <tr><th>Order ID</th><th>Produk</th><th>Metode</th><th>Jumlah</th><th>Status</th></tr>
          <?php foreach(array_slice($recentOrders,0,5) as $o): ?>
          <tr>
            <td style="font-size:.72rem"><?= h($o['order_id']) ?></td>
            <td><?= h($o['product'] ?? $o['product_name'] ?? '-') ?></td>
            <td>
              <?php if(($o['payment_method']??'') === 'binance'): ?>
                <span class="method-badge mb-binance"><i class="fas fa-bitcoin-sign"></i> Binance</span>
              <?php else: ?>
                <span class="method-badge mb-qris"><i class="fas fa-qrcode"></i> QRIS</span>
              <?php endif; ?>
            </td>
            <td>Rp <?= number_format($o['amount']??0,0,',','.') ?></td>
            <td>
              <?php $s=$o['status']??'pending'; ?>
              <span class="pill <?= $s==='completed'?'pill-green':($s==='pending'?'pill-yellow':'pill-red') ?>"><?= strtoupper($s) ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($recentOrders)): ?><tr><td colspan="5" style="text-align:center;padding:20px;color:var(--text3)">Belum ada order</td></tr><?php endif; ?>
        </table>
      </div>
    </div>
  </div>

  <!-- ── ORDERS ── -->
  <div id="sec-orders" class="section">
    <div class="card">
      <div class="card-header">
        <div class="card-icon" style="background:var(--purple-dim);color:var(--purple2)"><i class="fas fa-list"></i></div>
        <div><div class="card-title">Semua Orders</div><div class="card-desc">Total <?= $totalOrders ?> transaksi</div></div>
      </div>
      <div class="table-wrap">
        <table>
          <tr><th>Order ID</th><th>Customer</th><th>Produk</th><th>Paket</th><th>Metode</th><th>Jumlah</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
          <?php foreach($recentOrders as $o): ?>
          <?php $s=$o['status']??'pending'; $oid=h($o['order_id'] ?? $o['id'] ?? ''); ?>
          <tr id="row-<?= $oid ?>">
            <td style="font-size:.68rem;font-family:monospace"><?= $oid ?></td>
            <td><?= h($o['name'] ?? $o['customer_name'] ?? '-') ?><?php if(!empty($o['phone'] ?? $o['customer_phone'] ?? '')): ?><br><span style="font-size:.7rem;color:var(--text3)"><?= h($o['phone'] ?? $o['customer_phone']) ?></span><?php endif; ?></td>
            <td><?= h($o['product'] ?? $o['product_name'] ?? '-') ?></td>
            <td><?= h($o['item'] ?? $o['item_label'] ?? '-') ?>
              <?php $lic = $o['product_content'] ?? $o['license_key'] ?? ''; if(!empty($lic) && $lic !== 'Menunggu Pembayaran...'): ?>
                <br><span style="font-size:.65rem;font-family:monospace;color:var(--purple2);word-break:break-all"><?= h($lic) ?></span>
              <?php endif; ?>
            </td>
            <td>
              <?php if(($o['payment_method']??'') === 'binance'): ?>
                <span class="method-badge mb-binance"><i class="fas fa-bitcoin-sign"></i> Binance</span>
                <?php if(!empty($o['usdt_amount'])): ?><br><span style="font-size:.68rem;color:var(--binance)"><?= h($o['usdt_amount']) ?> USDT</span><?php endif; ?>
              <?php else: ?>
                <span class="method-badge mb-qris"><i class="fas fa-qrcode"></i> QRIS</span>
              <?php endif; ?>
            </td>
            <td>Rp <?= number_format($o['amount']??0,0,',','.') ?></td>
            <td id="status-<?= $oid ?>"><span class="pill <?= $s==='completed'?'pill-green':($s==='pending'?'pill-yellow':'pill-red') ?>"><?= strtoupper($s) ?></span></td>
            <?php $cat=$o['created_at']??''; $catStr=is_numeric($cat)?date('d/m/Y H:i',$cat):$cat; ?><td style="font-size:.72rem"><?= h($catStr) ?></td>
            <td>
              <?php if($s !== 'completed'): ?>
                <button onclick="markComplete('<?= $oid ?>')" class="btn btn-ghost" style="padding:6px 10px;font-size:.72rem;color:var(--green);border-color:rgba(34,197,94,.3)" title="Tandai Selesai">
                  <i class="fas fa-check"></i> Selesai
                </button>
              <?php else: ?>
                <span style="font-size:.72rem;color:var(--text3)">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($recentOrders)): ?><tr><td colspan="9" style="text-align:center;padding:24px;color:var(--text3)">Belum ada order</td></tr><?php endif; ?>
        </table>
      </div>
    </div>
  </div>

  <!-- ── GENERAL SETTINGS ── -->
  <div id="sec-general" class="section">
    <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="save_config" value="1">
    <div class="card">
      <div class="card-header">
        <div class="card-icon" style="background:var(--purple-dim);color:var(--purple2)"><i class="fas fa-store"></i></div>
        <div><div class="card-title">Informasi Toko</div><div class="card-desc">Pengaturan umum website</div></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nama Toko</label>
          <input type="text" name="title" class="form-input" value="<?= h($config['title']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">WhatsApp CS</label>
          <input type="text" name="whatsapp" class="form-input" value="<?= h($config['whatsapp']) ?>" placeholder="628xxx">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Telegram Admin</label>
          <input type="text" name="telegram" class="form-input" value="<?= h($config['telegram']) ?>" placeholder="username">
        </div>
        <div class="form-group">
          <label class="form-label">Channel / Grup</label>
          <input type="text" name="channel" class="form-input" value="<?= h($config['channel']) ?>" placeholder="https://...">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" style="display:flex;align-items:center;gap:8px">
          <i class="fa-brands fa-discord" style="color:#818CF8"></i> URL Discord
        </label>
        <input type="text" name="discord" class="form-input" value="<?= h($config['discord']) ?>" placeholder="https://discord.gg/...">
        <div class="form-hint">Link invite Discord server. Muncul sebagai tombol di navbar, sidebar, dan footer. Kosongkan jika tidak menggunakan Discord.</div>
      </div>
      <div class="form-group">
        <label class="form-label">Logo Website</label>
        <!-- Preview -->
        <div id="logoPreviewWrap" style="margin-bottom:10px;display:flex;align-items:center;gap:14px">
          <div style="width:64px;height:64px;border-radius:14px;overflow:hidden;background:var(--bg3);border:1px solid var(--sep);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <img id="logoPreviewImg" src="<?= $bannerImg ?>" alt="Logo" style="width:100%;height:100%;object-fit:contain" onerror="this.style.opacity='.2'">
          </div>
          <div style="font-size:.75rem;color:var(--text2);line-height:1.5">
            Logo saat ini.<br>Upload gambar baru atau isi URL di bawah.
          </div>
        </div>
        <!-- Upload file -->
        <label id="logoUploadLabel" style="display:flex;align-items:center;gap:10px;cursor:pointer;background:var(--bg3);border:1.5px dashed var(--sep);border-radius:10px;padding:12px 16px;transition:.15s;margin-bottom:8px" onmouseenter="this.style.borderColor='var(--purple)'" onmouseleave="this.style.borderColor='var(--sep)'">
          <i class="fas fa-image" style="color:var(--purple2);font-size:1.1rem"></i>
          <span id="logoFileName" style="font-size:.82rem;color:var(--text2)">Pilih gambar (JPG, PNG, WebP, GIF)</span>
          <input type="file" name="logo_upload" id="logoUploadInput" accept="image/*" style="display:none" onchange="previewLogo(this)">
        </label>
        <!-- URL fallback -->
        <input type="text" name="banner" id="bannerUrlInput" class="form-input" value="<?= h($config['banner']) ?>" placeholder="Atau masukkan URL gambar..." style="margin-top:4px">
        <div class="form-hint">Upload gambar akan otomatis tersimpan. URL manual sebagai alternatif jika tidak upload.</div>
      </div>
      <div class="form-group">
        <label class="form-label">Download URL Global</label>
        <input type="text" name="download_url" class="form-input" value="<?= h($config['download_url']) ?>" placeholder="https://...">
        <div class="form-hint">Link download APK/file default jika produk tidak punya link sendiri</div>
      </div>
      <div class="form-group">
        <label class="form-label">Mode Maintenance</label>
        <label class="toggle-wrap">
          <label class="toggle"><input type="checkbox" name="maintenance" value="1" <?= $config['maintenance'] ? 'checked' : '' ?>>
          <span class="toggle-slider"></span></label>
          <span class="toggle-lbl">Aktifkan maintenance (pembelian ditutup)</span>
        </label>
      </div>
      <!-- VPN / Proxy Block -->
      <div class="form-group">
        <label class="form-label" style="display:flex;align-items:center;gap:8px">
          <i class="fa-solid fa-shield-halved" style="color:#EF4444"></i> VPN / Proxy Block
        </label>
        <label class="toggle-wrap">
          <label class="toggle"><input type="checkbox" name="vpn_block" value="1" <?= !empty($config['vpn_block']) ? 'checked' : '' ?>>
          <span class="toggle-slider" style="<?= !empty($config['vpn_block']) ? '' : '' ?>"></span></label>
          <span class="toggle-lbl">Blokir pengunjung yang menggunakan VPN/Proxy</span>
        </label>
        <div class="form-hint" style="margin-top:8px">Menggunakan <a href="https://proxycheck.io" target="_blank" style="color:var(--purple-light)">proxycheck.io</a> (gratis 1000 cek/hari). Untuk limit lebih tinggi, daftarkan API key di bawah.</div>
      </div>
      <div class="form-group" id="vpnApiKeyGroup" style="<?= !empty($config['vpn_block']) ? '' : 'display:none' ?>">
        <label class="form-label">proxycheck.io API Key <span style="font-weight:400;color:var(--text3)">(opsional — untuk kapasitas lebih tinggi)</span></label>
        <input type="text" name="vpn_api_key" class="form-input" value="<?= h($config['vpn_api_key'] ?? '') ?>" placeholder="xxxxxx-xxxxxx-xxxxxx-xxxxxx">
        <div class="form-hint">Daftar gratis di <a href="https://proxycheck.io/dashboard" target="_blank" style="color:var(--purple-light)">proxycheck.io/dashboard</a> → ambil API key → paste di sini. Tanpa API key, limit 1000 cek/hari.</div>
      </div>
      <!-- Google OAuth -->
      <div class="form-group">
        <label class="form-label" style="display:flex;align-items:center;gap:8px">
          <i class="fa-brands fa-google" style="color:#4285F4"></i> Google Client ID
        </label>
        <input type="text" name="google_client_id" class="form-input" value="<?= h($config['google_client_id'] ?? '') ?>" placeholder="xxxxxx.apps.googleusercontent.com">
        <div class="form-hint">Untuk fitur Login Google di halaman Akun. Buat di <a href="https://console.cloud.google.com/apis/credentials" target="_blank" style="color:var(--purple-light)">Google Cloud Console</a> → OAuth 2.0 Client IDs.</div>
      </div>
    </div>
    <div class="card">
      <div class="card-header">
        <div class="card-icon" style="background:rgba(239,68,68,.12);color:var(--red)"><i class="fas fa-lock"></i></div>
        <div><div class="card-title">Keamanan</div><div class="card-desc">Ganti password admin</div></div>
      </div>
      <div class="form-group">
        <label class="form-label">Password Baru (kosongkan jika tidak ingin ganti)</label>
        <input type="password" name="admin_password_new" class="form-input" placeholder="Password baru...">
      </div>
    </div>
    <button type="submit" class="btn btn-purple"><i class="fas fa-save"></i> Simpan Pengaturan</button>
    </form>
  </div>

  <!-- ── PRODUCTS ── -->
  <div id="sec-products" class="section">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
      <div></div>
      <button class="btn btn-purple" onclick="openProductModal()"><i class="fas fa-plus"></i> Tambah Produk</button>
    </div>
    <div id="productCards" class="stat-grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
      <div style="text-align:center;padding:40px;color:var(--text3);grid-column:1/-1"><i class="fas fa-spinner fa-spin"></i> Memuat produk...</div>
    </div>
  </div>

  <!-- ── PAYMENT GATEWAY ── -->
  <div id="sec-payment" class="section">
    <form method="POST">
    <input type="hidden" name="save_config" value="1">

    <!-- QRIS Pakasir -->
    <div class="gw-card gw-qris">
      <div class="gw-header">
        <div class="gw-icon"><i class="fas fa-qrcode"></i></div>
        <div>
          <div class="gw-name">QRIS — Pakasir</div>
          <div class="gw-sub">Pembayaran QRIS untuk e-wallet & m-banking Indonesia</div>
        </div>
        <?php if (($config['pakasir_slug'] || $config['pakasir_merchant_id']) && ($config['pakasir_apikey'] || $config['pakasir_api_key'])): ?>
          <span class="status-dot ok" style="margin-left:auto"><i class="fas fa-circle" style="font-size:.4rem"></i> Aktif</span>
        <?php else: ?>
          <span class="status-dot missing" style="margin-left:auto"><i class="fas fa-circle" style="font-size:.4rem"></i> Belum diisi</span>
        <?php endif; ?>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Merchant ID</label>
          <input type="text" name="pakasir_slug" class="form-input" value="<?= h($config['pakasir_slug'] ?: $config['pakasir_merchant_id']) ?>" placeholder="Slug Proyek Pakasir (pakasir_slug)">
        </div>
        <div class="form-group">
          <label class="form-label">API Key</label>
          <input type="text" name="pakasir_apikey" class="form-input" value="<?= h($config['pakasir_apikey'] ?: $config['pakasir_api_key']) ?>" placeholder="API Key Pakasir">
        </div>
      </div>
      <div class="alert alert-info" style="margin-top:4px;margin-bottom:0;font-size:.78rem">
        <i class="fas fa-info-circle"></i>
        Dapatkan Merchant ID & API Key dari dashboard <strong>pakasir.com</strong> → Menu Integrasi
      </div>
    </div>

    <!-- Binance Pay -->
    <div class="gw-card gw-binance">
      <div class="gw-header">
        <div class="gw-icon"><i class="fas fa-bitcoin-sign"></i></div>
        <div>
          <div class="gw-name">Binance Pay</div>
          <div class="gw-sub">Pembayaran crypto USDT via Binance — global & tanpa batas</div>
        </div>
        <?php if ($config['binance_api_key'] && $config['binance_secret_key']): ?>
          <span class="status-dot ok" style="margin-left:auto"><i class="fas fa-circle" style="font-size:.4rem"></i> Aktif</span>
        <?php else: ?>
          <span class="status-dot missing" style="margin-left:auto"><i class="fas fa-circle" style="font-size:.4rem"></i> Belum diisi</span>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label class="form-label">Merchant ID</label>
        <input type="text" name="binance_merchant_id" class="form-input" value="<?= h($config['binance_merchant_id']) ?>" placeholder="Binance Pay Merchant ID">
        <div class="form-hint">Ditemukan di Binance → Merchant → Profile</div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">API Key (Certificate SN)</label>
          <input type="text" name="binance_api_key" class="form-input" value="<?= h($config['binance_api_key']) ?>" placeholder="API Key">
        </div>
        <div class="form-group">
          <label class="form-label">Secret Key</label>
          <input type="password" name="binance_secret_key" class="form-input" value="<?= h($config['binance_secret_key']) ?>" placeholder="Secret Key">
        </div>
      </div>
      <div class="alert alert-info" style="margin-top:4px;margin-bottom:12px;font-size:.78rem">
        <i class="fas fa-info-circle"></i>
        Buka <strong>Binance → Merchant Center → API Management</strong> untuk membuat API Key & Secret Key. 
        Konversi IDR→USDT otomatis (~1 USD = Rp 16.000, sesuaikan di <code>api.php</code> baris konversi jika perlu).
      </div>
      <div style="background:rgba(0,0,0,.2);border-radius:var(--radius-sm);padding:14px">
        <div style="font-size:.72rem;font-weight:700;color:var(--binance);margin-bottom:8px"><i class="fas fa-webhook"></i> Webhook URL (opsional)</div>
        <div style="font-size:.75rem;color:var(--text2);word-break:break-all;font-family:monospace;background:rgba(0,0,0,.3);padding:10px;border-radius:8px">
          <?= (isset($_SERVER['HTTPS']) ? 'https' : 'http') ?>://<?= $_SERVER['HTTP_HOST'] ?><?= dirname($_SERVER['REQUEST_URI']) ?>/api.php?action=binance_webhook
        </div>
        <div style="font-size:.7rem;color:var(--text3);margin-top:6px">Masukkan URL ini di Binance Merchant → Webhook untuk notifikasi pembayaran otomatis</div>
      </div>
    </div>

    <button type="submit" class="btn btn-purple"><i class="fas fa-save"></i> Simpan Payment Gateway</button>
    </form>
  </div>

</main>
</div>

<!-- MOBILE BOTTOM NAV -->
<nav class="mob-nav">
  <div class="mob-nav-items">
    <div class="mob-nav-item active" onclick="showSection('dashboard',this)"><i class="fas fa-chart-bar"></i><span>Dashboard</span></div>
    <div class="mob-nav-item" onclick="showSection('orders',this)"><i class="fas fa-receipt"></i><span>Orders</span></div>
    <div class="mob-nav-item" onclick="showSection('products',this)"><i class="fas fa-box-open"></i><span>Produk</span></div>
    <div class="mob-nav-item" onclick="showSection('general',this)"><i class="fas fa-gear"></i><span>General</span></div>
    <div class="mob-nav-item" onclick="showSection('payment',this)"><i class="fas fa-credit-card"></i><span>Payment</span></div>
  </div>
</nav>

<script>
function showSection(id, el) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.getElementById('sec-' + id).classList.add('active');
  // Sync sidebar nav-item
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  // Sync mob-nav-item
  document.querySelectorAll('.mob-nav-item').forEach(n => n.classList.remove('active'));
  if (el) el.classList.add('active');
  // Also sync the counterpart (sidebar<->mobnav)
  const isMob = el && el.classList.contains('mob-nav-item');
  document.querySelectorAll(isMob ? '.nav-item' : '.mob-nav-item').forEach(n => {
    if (n.getAttribute('onclick') && n.getAttribute('onclick').includes("'"+id+"'")) n.classList.add('active');
  });
  const titles = {dashboard:'Dashboard', orders:'Orders', products:'Manajemen Produk', general:'General Settings', payment:'Payment Gateway'};
  const t = document.getElementById('pageTitle');
  if (t) t.textContent = titles[id] || id;
  if (id === 'products') loadAdminProducts();
}
</script>
<?php endif; ?>
<!-- PRODUCT MODAL -->
<div id="prodModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:500;justify-content:center;align-items:flex-end;overflow:auto">
  <div style="background:var(--bg2);border:1px solid var(--sep);border-radius:20px 20px 0 0;padding:20px 20px calc(20px + env(safe-area-inset-bottom));width:100%;max-width:540px;max-height:92vh;overflow-y:auto;-webkit-overflow-scrolling:touch">
    <div style="width:40px;height:4px;background:rgba(255,255,255,.12);border-radius:99px;margin:0 auto 18px"></div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
      <h3 id="prodModalTitle" style="font-size:1rem;font-weight:900">Tambah Produk</h3>
      <button onclick="closeProdModal()" style="background:rgba(255,255,255,.08);border:none;color:var(--text2);width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:1rem"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Nama Produk</label><input id="pName" class="form-input" placeholder="e.g. DRIP CLIENT APKMOD"></div>
      <div class="form-group"><label class="form-label">Platform</label>
        <select id="pPlatform" class="form-input"><option>ANDROID</option><option>PC</option><option>IOS</option><option>ALL</option></select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Kategori</label><input id="pCategory" class="form-input" placeholder="e.g. TOOLS, GAME"></div>
      <div class="form-group"><label class="form-label">Status</label>
        <select id="pStatus" class="form-input"><option value="ready">Ready</option><option value="updating">Updating</option></select>
      </div>
    </div>
    <div class="form-group" style="margin-bottom:14px">
      <label style="display:flex;align-items:center;gap:8px;font-size:.85rem;font-weight:600;cursor:pointer">
        <input type="checkbox" id="pBestSeller" style="width:16px;height:16px;accent-color:var(--purple)"> Tandai sebagai Best Seller
      </label>
    </div>
    <div class="form-group"><label class="form-label">Deskripsi</label><input id="pDesc" class="form-input" placeholder="Deskripsi singkat produk"></div>
    <div class="form-group"><label class="form-label">Download URL</label><input id="pDownload" class="form-input" placeholder="https://..."></div>

    <div style="margin-bottom:14px">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
        <label class="form-label" style="margin:0">Paket Harga &amp; License Keys</label>
        <button onclick="addPriceRow()" class="btn btn-ghost" style="padding:5px 10px;font-size:.75rem"><i class="fas fa-plus"></i> Tambah Paket</button>
      </div>
      <div id="priceRows"></div>
    </div>

    <div id="prodModalError" class="alert alert-error" style="display:none;margin-bottom:12px"></div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <button onclick="saveProd()" class="btn btn-purple"><i class="fas fa-save"></i> Simpan</button>
      <button id="delProdBtn" onclick="deleteProd()" class="btn btn-danger" style="display:none"><i class="fas fa-trash"></i> Hapus</button>
      <button onclick="closeProdModal()" class="btn btn-ghost">Batal</button>
    </div>
  </div>
</div>

<script>
function showSection(id, el) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.getElementById('sec-' + id).classList.add('active');
  document.querySelectorAll('.nav-item, .mob-nav-item').forEach(n => n.classList.remove('active'));
  if (el) el.classList.add('active');
  const titles = {dashboard:'Dashboard', orders:'Orders', products:'Manajemen Produk', general:'General Settings', payment:'Payment Gateway'};
  const t = document.getElementById('pageTitle');
  if (t) t.textContent = titles[id] || id;
  if (id === 'products') loadAdminProducts();
}

// ── PRODUCTS ───────────────────────────────────────────────────────────────
let adminProducts = [];
let editingProdId = null;

async function loadAdminProducts() {
  const box = document.getElementById('productCards');
  box.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text3);grid-column:1/-1"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';
  try {
    const r = await fetch('api.php?action=get_products_admin&t=' + Date.now(), {cache:'no-store'});
    if (!r.ok) throw new Error('HTTP ' + r.status);
    const data = await r.json();
    if (!Array.isArray(data)) throw new Error('Invalid response');
    adminProducts = data;
    renderAdminProducts();
  } catch(e) {
    box.innerHTML = '<div style="text-align:center;padding:40px;color:var(--red);grid-column:1/-1">Gagal memuat produk</div>';
  }
}

function renderAdminProducts() {
  const box = document.getElementById('productCards');
  if (!adminProducts.length) {
    box.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text3);grid-column:1/-1"><i class="fas fa-box-open" style="font-size:2rem;display:block;margin-bottom:10px"></i>Belum ada produk. Klik "+ Tambah Produk"</div>';
    return;
  }
  box.innerHTML = adminProducts.map(p => {
    const totalStock = (p.prices||[]).reduce((s,pr)=>s+(pr.stock||0),0);
    const keyCount = totalStock; // Stok real dari file stok (pr.stock sudah diisi API)
    const minPrice = (p.prices||[]).reduce((min,pr) => {
      const v = pr.promo && pr.promo_price ? Number(pr.promo_price) : parseInt(String(pr.price||'0').replace(/[^0-9]/g,''));
      return Math.min(min, v);
    }, Infinity);
    return `<div style="background:var(--card);border:1px solid var(--sep);border-radius:var(--radius);padding:16px">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
        <div style="width:40px;height:40px;border-radius:8px;background:var(--purple-dim);display:flex;align-items:center;justify-content:center"><i class="fas fa-box" style="color:var(--purple2)"></i></div>
        <div style="flex:1;min-width:0">
          <div style="font-weight:800;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${p.name||'-'}</div>
          <div style="font-size:.72rem;color:var(--text2)">${p.platform||''} · ${p.category||''}</div>
        </div>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">
        <span class="pill ${(p.manual_status||'ready')==='ready'?'pill-green':'pill-yellow'}">${(p.manual_status||'ready').toUpperCase()}</span>
        ${p.best_seller ? '<span class="pill" style="background:rgba(234,179,8,.15);color:var(--yellow)"><i class="fas fa-star"></i> Best Seller</span>' : ''}
        <span class="pill pill-blue">${(p.prices||[]).length} paket</span>
        <span class="pill" style="background:rgba(124,58,237,.12);color:var(--purple2)">${keyCount} keys</span>
      </div>
      <div style="font-size:.8rem;color:var(--text2);margin-bottom:12px">
        Mulai dari <strong style="color:var(--text)">Rp ${minPrice === Infinity ? '0' : minPrice.toLocaleString('id-ID')}</strong>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:6px">
        <button onclick='quickStatus(${JSON.stringify(p.id)},"ready")' class="btn" style="justify-content:center;font-size:.78rem;padding:8px 0;background:${(p.manual_status||'ready')==='ready'?'var(--green)':'rgba(34,197,94,.12)'};color:${(p.manual_status||'ready')==='ready'?'#fff':'var(--green)'};border:1px solid rgba(34,197,94,.35);font-weight:800">
          <i class="fas fa-circle-check"></i> Ready
        </button>
        <button onclick='quickStatus(${JSON.stringify(p.id)},"updating")' class="btn" style="justify-content:center;font-size:.78rem;padding:8px 0;background:${(p.manual_status||'ready')==='updating'?'var(--yellow)':'rgba(234,179,8,.12)'};color:${(p.manual_status||'ready')==='updating'?'#000':'var(--yellow)'};border:1px solid rgba(234,179,8,.35);font-weight:800">
          <i class="fas fa-rotate"></i> Updating
        </button>
      </div>
      <button onclick='openProductModal(${JSON.stringify(p)})' class="btn btn-ghost" style="width:100%;justify-content:center;font-size:.8rem"><i class="fas fa-edit"></i> Edit Produk</button>
    </div>`;
  }).join('');
}

let priceRowCount = 0;
function addPriceRow(data={}) {
  priceRowCount++;
  const id = 'pr' + priceRowCount;
  const existingStock = data.stock !== undefined ? data.stock : (data.licenses||[]).length;
  const div = document.createElement('div');
  div.id = id;
  div.style.cssText = 'background:var(--bg3);border:1px solid var(--sep);border-radius:10px;padding:12px;margin-bottom:8px';
  div.innerHTML = `
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <span style="font-size:.75rem;font-weight:700;color:var(--text2)">Paket ${priceRowCount}</span>
      <button onclick="document.getElementById('${id}').remove()" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:.85rem"><i class="fas fa-trash"></i></button>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Label</label><input class="form-input pr-label" placeholder="1 Hari, 7 Hari..." value="${data.label||''}"></div>
      <div class="form-group"><label class="form-label">Hari</label><input type="number" class="form-input pr-days" placeholder="1" value="${data.days||1}"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Harga (Rp)</label><input type="number" class="form-input pr-price" placeholder="13000" value="${data.price||''}"></div>
      <div class="form-group">
        <label style="display:flex;align-items:center;gap:6px;font-size:.8rem;cursor:pointer;margin-top:8px">
          <input type="checkbox" class="pr-promo" ${data.promo?'checked':''}> Promo
        </label>
        <input type="number" class="form-input pr-promo-price" placeholder="Harga promo" value="${data.promo_price||''}">
      </div>
    </div>
    <div class="form-group" style="margin-bottom:0">
      <label class="form-label">
        Tambah License Keys (satu per baris) — Stok tersedia: <span class="pr-key-count" style="color:var(--green);font-weight:800">${existingStock}</span>
      </label>
      <textarea class="form-input pr-licenses" rows="3" placeholder="Paste keys baru di sini (tidak menghapus stok lama)&#10;KEY-XXXX-XXXX&#10;KEY-YYYY-YYYY" style="resize:vertical;min-height:70px;font-family:monospace;font-size:.78rem" oninput="this.closest('#${id}').querySelector('.pr-new-count').textContent = this.value.split('\\n').map(s=>s.trim()).filter(Boolean).length + ' baru'"></textarea>
      <div style="font-size:.68rem;color:var(--text3);margin-top:4px">Keys baru akan ditambahkan ke stok yang ada. Duplikat diabaikan otomatis. <span class="pr-new-count" style="color:var(--purple2);font-weight:700"></span></div>
    </div>`;
  document.getElementById('priceRows').appendChild(div);
}

function getPriceRows() {
  return [...document.querySelectorAll('#priceRows > div')].map(div => ({
    label: div.querySelector('.pr-label').value.trim(),
    days: parseInt(div.querySelector('.pr-days').value)||1,
    price: div.querySelector('.pr-price').value,
    // Hanya kirim keys yang ada di textarea (keys baru). Backend akan merge+deduplicate.
    licenses: div.querySelector('.pr-licenses').value.split('\n').map(s=>s.trim()).filter(Boolean),
    promo: div.querySelector('.pr-promo').checked,
    promo_price: div.querySelector('.pr-promo-price').value,
  }));
}

function openProductModal(prod=null) {
  editingProdId = prod ? prod.id : null;
  document.getElementById('prodModalTitle').textContent = prod ? 'Edit Produk' : 'Tambah Produk';
  document.getElementById('pName').value = prod?.name||'';
  document.getElementById('pPlatform').value = prod?.platform||'ANDROID';
  document.getElementById('pCategory').value = prod?.category||'';
  document.getElementById('pStatus').value = prod?.manual_status||'ready';
  document.getElementById('pBestSeller').checked = !!prod?.best_seller;
  document.getElementById('pDesc').value = prod?.desc||'';
  document.getElementById('pDownload').value = prod?.download_url||'';
  document.getElementById('priceRows').innerHTML = '';
  priceRowCount = 0;
  (prod?.prices||[]).forEach(pr => addPriceRow(pr));
  if (!prod?.prices?.length) addPriceRow();
  document.getElementById('delProdBtn').style.display = prod ? 'block' : 'none';
  document.getElementById('prodModalError').style.display = 'none';
  document.getElementById('prodModal').style.display = 'flex';
}

function closeProdModal() { document.getElementById('prodModal').style.display = 'none'; }

async function saveProd() {
  const name = document.getElementById('pName').value.trim();
  if (!name) { showProdError('Nama produk wajib diisi'); return; }
  const prices = getPriceRows();
  if (!prices.length) { showProdError('Minimal 1 paket harga'); return; }

  const data = {
    id: editingProdId,
    name, platform: document.getElementById('pPlatform').value,
    category: document.getElementById('pCategory').value.trim().toUpperCase() || 'LAINNYA',
    manual_status: document.getElementById('pStatus').value,
    best_seller: document.getElementById('pBestSeller').checked,
    desc: document.getElementById('pDesc').value.trim(),
    download_url: document.getElementById('pDownload').value.trim(),
    prices,
  };

  try {
    const r = await fetch('api.php?action=save_product', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)});
    const d = await r.json();
    if (!d.success) { showProdError(d.message||'Gagal'); return; }

    closeProdModal();
    loadAdminProducts();
  } catch(e) { showProdError('Terjadi kesalahan'); }
}

async function deleteProd() {
  if (!editingProdId || !confirm('Yakin hapus produk ini?')) return;
  await fetch('api.php?action=delete_product', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id:editingProdId})});
  closeProdModal(); loadAdminProducts();
}

function showProdError(msg) {
  const el = document.getElementById('prodModalError');
  el.textContent = msg; el.style.display = 'flex';
}

// ── QUICK STATUS TOGGLE (Ready / Updating) ──────────────────────────────────
async function quickStatus(prodId, status) {
  // Temukan produk dari cache lokal
  const prod = adminProducts.find(p => p.id === prodId);
  if (!prod) return;

  // Optimistic UI — langsung update array lokal & re-render
  prod.manual_status = status;
  renderAdminProducts();

  try {
    const payload = Object.assign({}, prod, { manual_status: status });
    const r = await fetch('api.php?action=save_product', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });
    const d = await r.json();
    if (!d.success) {
      // Rollback jika gagal
      prod.manual_status = status === 'ready' ? 'updating' : 'ready';
      renderAdminProducts();
      alert('Gagal update status: ' + (d.message||'error'));
    }
  } catch(e) {
    prod.manual_status = status === 'ready' ? 'updating' : 'ready';
    renderAdminProducts();
    alert('Terjadi kesalahan koneksi');
  }
}

// ── MARK ORDER COMPLETE ─────────────────────────────────────────────────────
async function markComplete(orderId) {
  if (!confirm('Tandai order ' + orderId + ' sebagai SELESAI?')) return;
  try {
    const r = await fetch('api.php?action=complete_order', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({order_id: orderId})
    });
    const d = await r.json();
    if (d.success) {
      // Update status pill langsung tanpa reload
      const statusCell = document.getElementById('status-' + orderId);
      if (statusCell) statusCell.innerHTML = '<span class="pill pill-green">COMPLETED</span>';
      // Hapus tombol aksi
      const row = document.getElementById('row-' + orderId);
      if (row) {
        const lastTd = row.querySelector('td:last-child');
        if (lastTd) lastTd.innerHTML = '<span style="font-size:.72rem;color:var(--text3)">—</span>';
      }
      // Update stat card paid orders
      const paidEl = document.querySelector('.stat-val');
      // Reload page setelah 1 detik untuk refresh semua stats
      setTimeout(() => location.reload(), 1000);
    } else {
      alert('Gagal: ' + (d.message||'Unknown error'));
    }
  } catch(e) {
    alert('Terjadi kesalahan koneksi');
  }
}

function previewLogo(input) {
  if (!input.files || !input.files[0]) return;
  var file = input.files[0];
  var nameEl = document.getElementById('logoFileName');
  var imgEl  = document.getElementById('logoPreviewImg');
  var urlEl  = document.getElementById('bannerUrlInput');
  if (nameEl) nameEl.textContent = file.name;
  var reader = new FileReader();
  reader.onload = function(e) {
    if (imgEl) { imgEl.src = e.target.result; imgEl.style.opacity = '1'; }
  };
  reader.readAsDataURL(file);
  if (urlEl) urlEl.value = '';
}

// VPN toggle show/hide API key field
(function(){
  const checkboxes = document.querySelectorAll('input[name="vpn_block"]');
  checkboxes.forEach(function(cb){
    cb.addEventListener('change', function(){
      const grp = document.getElementById('vpnApiKeyGroup');
      if (grp) grp.style.display = this.checked ? '' : 'none';
    });
  });
})();
</script>
</body>
</html>
