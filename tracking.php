<?php

$dataDir = __DIR__ . '/data';
$configFile = $dataDir . '/config.json';
$productsFile = $dataDir . '/products.json';
$ordersFile = $dataDir . '/orders.json';

$config = [
    'title' => 'WilzXiterz',
    'whatsapp' => '6285173360622',
    'telegram' => 'WilzXiterzVN',
    'channel' => 'https://t.me/limzyyofficial',
    'banner' => 'assets/img/logops.png',
    'vpn_block' => false,
    'vpn_api_key' => '',
    'discord' => '',
];

if (file_exists($configFile)) {
    $savedConfig = json_decode(file_get_contents($configFile), true);
    if (is_array($savedConfig)) {
        $config = array_merge($config, $savedConfig);
    }
}

$fullTitle = htmlspecialchars($config['title']);
$firstPart = $fullTitle;
$secondPart = '';
if (strlen($fullTitle) > 5) {
    $firstPart = substr($fullTitle, 0, -3);
    $secondPart = substr($fullTitle, -3);
}

$waLink = "https://wa.me/" . preg_replace('/[^0-9]/', '', $config['whatsapp']);
$tgUsername = ltrim($config['telegram'], '@'); 
$tgLink = "https://t.me/" . htmlspecialchars($tgUsername);
$channelLink = htmlspecialchars($config['channel']);
$vpnEnabled  = !empty($config['vpn_block']) ? 'true' : 'false';

$orderData = null;
$errorMsg = null;
$searchQuery = $_GET['order_id'] ?? '';

if (!empty($searchQuery)) {
    $searchQuery = trim($searchQuery);

    // Semua order disimpan dalam satu file data/orders.json (key = order_id),
    // sesuai format yang dipakai oleh api.php & admin.php.
    $allOrders = [];
    if (file_exists($ordersFile)) {
        $allOrders = json_decode(file_get_contents($ordersFile), true) ?: [];
    }

    $rawOrder = null;

    // 1. Coba cari langsung berdasarkan ID Transaksi (key array)
    if (isset($allOrders[$searchQuery])) {
        $rawOrder = $allOrders[$searchQuery];
    } else {
        // 2. Jika tidak ketemu, cari berdasarkan kecocokan Nama atau Nomor HP
        foreach ($allOrders as $o) {
            $matchName  = (isset($o['customer_name']) && stripos($o['customer_name'], $searchQuery) !== false);
            $matchPhone = (isset($o['customer_phone']) && $o['customer_phone'] !== '' && $o['customer_phone'] === $searchQuery);
            if ($matchName || $matchPhone) { $rawOrder = $o; break; }
        }
    }

    if ($rawOrder) {
        // Normalisasi field agar sesuai dengan yang dipakai tampilan di bawah
        $orderData = [
            'order_id'        => $rawOrder['id'] ?? $searchQuery,
            'name'             => $rawOrder['customer_name'] ?? 'Guest',
            'phone'            => $rawOrder['customer_phone'] ?? '',
            'product'          => $rawOrder['product_name'] ?? '-',
            'item'             => $rawOrder['item_label'] ?? '',
            'status'           => $rawOrder['status'] ?? 'pending',
            'created_at'       => isset($rawOrder['created_at']) ? strtotime($rawOrder['created_at']) : time(),
            'product_content'  => $rawOrder['license_key'] ?? '',
        ];

        $productImage = 'assets/img/qris.png';
        $realProductId = $rawOrder['product_id'] ?? '';

        if (file_exists($productsFile)) {
            $productsList = json_decode(file_get_contents($productsFile), true);
            if (is_array($productsList)) {
                foreach ($productsList as $p) {
                    if (isset($p['id']) && $p['id'] === $realProductId) {
                        $productImage = $p['img'] ?? 'assets/img/qris.png';
                        break;
                    }
                }
            }
        }

        $orderData['product_image'] = $productImage;
        $orderData['real_product_id'] = $realProductId;

    } else {
        $errorMsg = "Data <b>" . htmlspecialchars($searchQuery) . "</b> tidak ditemukan. Gunakan ID, Nama, atau No HP.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lacak Pesanan - <?= $fullTitle ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }

    :root {
      --bg: #0A0812;
      --bg2: #100D1A;
      --bg3: #150F22;
      --border: #2A2040;
      --border2: #3A2D5A;
      --purple: #9B59F5;
      --purple2: #7C3AED;
      --purple3: #A855F7;
      --purple-glow: rgba(124,58,237,0.25);
      --purple-light: #C084FC;
      --purple-dim: rgba(124,58,237,0.15);
      --purple-border: rgba(124,58,237,0.35);
      --green: #22C55E;
      --green-dim: rgba(34,197,94,0.15);
      --yellow: #EAB308;
      --yellow-dim: rgba(234,179,8,0.15);
      --red: #EF4444;
      --text: #ffffff;
      --text-muted: #9B8EC4;
      --text-dimmer: #5C4F8A;
      --card-bg: #120D20;
      --card-bg2: #1A1230;
      --card-border: rgba(124,58,237,0.2);
      --font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    html { scroll-behavior: smooth; background: var(--bg); }
    body {
      background: var(--bg);
      color: var(--text);
      font-family: var(--font);
      -webkit-font-smoothing: antialiased;
      min-height: 100vh;
      overflow-x: hidden;
      padding-bottom: 60px;
    }
    a { text-decoration: none; color: inherit; }

    body::before {
      content: '';
      position: fixed;
      top: -200px; left: -200px;
      width: 700px; height: 700px;
      background: radial-gradient(circle, rgba(124,58,237,0.12) 0%, transparent 70%);
      pointer-events: none;
      z-index: 0;
    }

    /* NAVBAR */
    nav {
      position: sticky; top: 0; left: 0; right: 0; z-index: 200;
      background: rgba(10,8,18,0.92);
      backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--border);
    }
    .nav-inner {
      display: flex; align-items: center; justify-content: space-between;
      padding: 14px 20px;
      max-width: 1200px; margin: 0 auto;
    }
    .nav-logo { display: flex; align-items: center; gap: 10px; }
    .logo-icon {
      width: 40px; height: 40px;
      background: linear-gradient(135deg, #7C3AED, #A855F7);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 16px; color: white;
      box-shadow: 0 0 16px rgba(124,58,237,0.5);
      flex-shrink: 0; position: relative; overflow: hidden;
    }
    .logo-icon::before {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent);
    }
    .logo-icon img { width: 100%; height: 100%; object-fit: contain; border-radius: 10px; }
    .logo-text { display: flex; flex-direction: column; line-height: 1.1; }
    .logo-part1 {
      font-size: 15px; font-weight: 800; letter-spacing: 0.02em;
      background: linear-gradient(90deg, #C084FC, #A855F7);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .logo-part2 { font-size: 15px; font-weight: 800; letter-spacing: 0.02em; color: white; }

    .hamburger-btn {
      display: flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.07); border: 1px solid var(--border);
      color: white; font-size: 14px; cursor: pointer;
      padding: 7px 12px; border-radius: 10px; transition: .15s;
    }
    .hamburger-btn i { color: var(--purple-light); font-size: 1rem; }

    /* SIDEBAR OVERLAY */
    .sidebar-overlay {
      position: fixed; inset: 0; z-index: 300;
      background: rgba(0,0,0,0.65); backdrop-filter: blur(4px);
      opacity: 0; pointer-events: none; transition: opacity 0.3s;
    }
    .sidebar-overlay.open { opacity: 1; pointer-events: all; }

    .sidebar {
      position: fixed; top: 0; left: 0; bottom: 0;
      width: 288px; z-index: 310;
      background: linear-gradient(180deg, var(--bg3) 0%, var(--bg2) 100%);
      border-right: 1px solid var(--border2);
      transform: translateX(-100%);
      transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex; flex-direction: column;
      overflow-y: auto;
    }
    .sidebar.open { transform: translateX(0); }

    .sidebar-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 16px 20px; border-bottom: 1px solid var(--border);
    }
    .sidebar-close {
      display: flex; align-items: center; gap: 6px;
      background: rgba(255,255,255,.08); border: 1px solid var(--border);
      color: var(--text-muted); cursor: pointer; font-size: 13px;
      padding: 6px 11px; border-radius: 8px; transition: color 0.15s; font-family: var(--font);
    }
    .sidebar-close:hover { color: white; }

    .sidebar-nav { padding: 16px 12px; display: flex; flex-direction: column; gap: 0; flex: 1; }
    .sidebar-link {
      display: flex; align-items: center; gap: 12px;
      padding: 14px 16px; border-radius: 12px;
      font-size: 14px; font-weight: 500; color: var(--text-muted);
      background: rgba(124,58,237,0.06); border: 1px solid var(--border);
      transition: all 0.15s;
      margin-bottom: 4px;
    }
    .sidebar-link:hover { background: rgba(124,58,237,0.15); color: white; }
    .sidebar-link i { width: 18px; text-align: center; font-size: 15px; }
    .sidebar-divider {
      font-size: 10px; font-weight: 700; letter-spacing: 0.12em;
      color: var(--text-dimmer); text-transform: uppercase;
      padding: 12px 4px 6px; display: flex; align-items: center; gap: 6px;
    }

    /* MAIN */
    .page-shell { max-width: 700px; margin: 0 auto; padding: 32px 20px; position: relative; z-index: 1; }

    .back-btn {
      display: inline-flex; align-items: center; gap: 8px;
      color: var(--purple-light); font-weight: 600; font-size: 14px;
      margin-bottom: 24px; transition: 0.2s;
    }
    .back-btn:hover { color: white; }

    .track-card {
      background: linear-gradient(135deg, #1A1030, #120D24);
      border: 1px solid var(--border2); border-radius: 20px;
      padding: 28px 24px; position: relative; overflow: hidden;
    }
    .track-card::before {
      content: ''; position: absolute; top: -60px; right: -60px;
      width: 220px; height: 220px;
      background: radial-gradient(circle, rgba(168,85,247,0.12), transparent 70%);
      pointer-events: none;
    }

    .track-header { text-align: center; margin-bottom: 28px; }
    .track-header-icon {
      width: 56px; height: 56px; border-radius: 14px;
      background: var(--purple-dim); border: 1px solid var(--purple-border);
      display: flex; align-items: center; justify-content: center;
      color: var(--purple-light); font-size: 1.4rem;
      margin: 0 auto 14px;
    }
    .track-header h1 { font-size: 1.4rem; font-weight: 800; }
    .track-header p { color: var(--text-muted); font-size: 0.875rem; margin-top: 6px; }

    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; margin-bottom: 8px; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.06em; }
    .form-group input {
      width: 100%; padding: 12px 16px;
      background: var(--card-bg2); border: 1px solid var(--border2);
      border-radius: 10px; color: white; font-size: 14px; outline: none;
      font-family: var(--font); transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-group input::placeholder { color: var(--text-dimmer); }
    .form-group input:focus { border-color: var(--purple2); box-shadow: 0 0 0 3px rgba(124,58,237,0.15); }

    .btn-track {
      width: 100%; padding: 13px;
      background: linear-gradient(135deg, var(--purple2), var(--purple3));
      color: white; border: none; border-radius: 10px;
      font-size: 14px; font-weight: 700; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: 8px;
      font-family: var(--font); transition: 0.2s;
      box-shadow: 0 4px 16px rgba(124,58,237,0.3);
    }
    .btn-track:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(124,58,237,0.4); }

    /* RESULT */
    .result-card {
      background: rgba(124,58,237,0.06); border: 1px solid var(--card-border);
      border-radius: 14px; padding: 20px; margin-top: 24px;
      animation: fadeUp 0.4s ease both;
    }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

    .product-header {
      display: flex; align-items: center; gap: 14px;
      margin-bottom: 18px; padding-bottom: 18px; border-bottom: 1px solid var(--border);
    }
    .product-logo {
      width: 52px; height: 52px; border-radius: 12px;
      background: var(--card-bg2); border: 1px solid var(--border2);
      display: flex; align-items: center; justify-content: center;
      overflow: hidden; flex-shrink: 0;
    }
    .product-logo img { width: 100%; height: 100%; object-fit: cover; }
    .product-name { font-size: 1rem; font-weight: 700; color: white; margin-bottom: 3px; }
    .product-buyer { font-size: 0.78rem; color: var(--text-muted); }
    .product-buyer strong { color: var(--text); }

    .result-row { display: flex; justify-content: space-between; align-items: center; padding: 11px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
    .result-row:last-child { border-bottom: none; }
    .result-label { color: var(--text-muted); font-size: 0.82rem; }
    .result-value { font-weight: 600; text-align: right; font-size: 0.82rem; word-break: break-all; }

    .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-pending { background: var(--yellow-dim); color: var(--yellow); border: 1px solid rgba(234,179,8,0.3); }
    .status-success { background: var(--green-dim); color: var(--green); border: 1px solid rgba(34,197,94,0.3); }

    .license-box {
      background: var(--purple-dim); border: 1px dashed var(--purple-border);
      border-radius: 10px; padding: 16px; margin-top: 18px; text-align: center;
    }
    .license-box-label { font-size: 0.72rem; font-weight: 700; color: var(--text-dimmer); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
    .license-key {
      font-family: monospace; font-size: 1rem; color: var(--purple-light);
      font-weight: 800; word-break: break-all;
      padding: 10px 12px; background: rgba(124,58,237,0.08); border-radius: 7px; margin: 6px 0;
    }

    .action-buttons { display: grid; grid-template-columns: 1fr; gap: 10px; margin-top: 16px; }
    @media (min-width: 480px) { .action-buttons { grid-template-columns: 1fr 1fr; } }
    .btn-action {
      padding: 12px; border: none; border-radius: 9px;
      font-size: 0.875rem; font-weight: 700; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: 8px;
      text-decoration: none; transition: 0.2s; font-family: var(--font);
    }
    .btn-copy { background: var(--green-dim); color: var(--green); border: 1px solid rgba(34,197,94,0.3); }
    .btn-copy:hover { background: rgba(34,197,94,0.25); }
    .btn-wa { background: rgba(37,211,102,0.12); color: #25D366; border: 1px solid rgba(37,211,102,0.3); }
    .btn-wa:hover { background: rgba(37,211,102,0.22); }
    .btn-yellow { background: var(--yellow-dim); color: var(--yellow); border: 1px solid rgba(234,179,8,0.3); }
    .btn-yellow:hover { background: rgba(234,179,8,0.25); }
    .btn-purple { background: var(--purple-dim); color: var(--purple-light); border: 1px solid var(--purple-border); }
    .btn-purple:hover { background: rgba(124,58,237,0.25); }

    .info-box {
      border-radius: 10px; padding: 14px 16px; margin-top: 18px;
      display: flex; align-items: flex-start; gap: 10px; font-size: 0.82rem;
    }
    .info-box i { flex-shrink: 0; margin-top: 1px; }
    .info-box-yellow { background: var(--yellow-dim); border: 1px solid rgba(234,179,8,0.25); color: var(--yellow); }
    .info-box-red { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); color: var(--red); }
  </style>

  <script>
    const swalDark = Swal.mixin({
      background: '#120D20', color: '#fff',
      confirmButtonColor: '#7C3AED', cancelButtonColor: '#EF4444'
    });
  </script>
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
        <img src="<?= $config['banner'] ?? 'assets/img/logops.png' ?>" alt="logo" onerror="this.outerHTML='<i class=\'fa-solid fa-layer-group\' style=\'color:#fff;font-size:16px\'></i>'">
      </div>
      <div class="logo-text">
        <span class="logo-part1"><?= $firstPart ?></span>
        <span class="logo-part2"><?= $secondPart ?></span>
      </div>
    </a>
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
      <div class="logo-icon" style="width:34px;height:34px;">
        <img src="<?= $config['banner'] ?? 'assets/img/logops.png' ?>" alt="logo" style="width:100%;height:100%;object-fit:contain" onerror="this.outerHTML='<i class=\'fa-solid fa-layer-group\' style=\'color:#fff;font-size:14px\'></i>'">
      </div>
      <div class="logo-text">
        <span class="logo-part1" style="font-size:13px"><?= $firstPart ?></span>
        <span class="logo-part2" style="font-size:13px"><?= $secondPart ?></span>
      </div>
    </a>
    <button class="sidebar-close" id="sidebarClose"><i class="fa-solid fa-xmark"></i> Close</button>
  </div>
  <nav class="sidebar-nav">
    <a class="sidebar-link" href="index.php"><i class="fa-solid fa-house" style="color:var(--purple-light)"></i> Beranda</a>
    <a class="sidebar-link" href="tracking.php"><i class="fa-solid fa-magnifying-glass" style="color:var(--purple-light)"></i> Lacak Pesanan</a>
    <div class="sidebar-divider"><i class="fa-regular fa-comment-dots"></i> Bantuan</div>
    <a class="sidebar-link" href="<?= $waLink ?>" target="_blank"><i class="fa-brands fa-whatsapp" style="color:#25D366"></i> WhatsApp Admin</a>
    <?php if (!empty($config['telegram'])): ?>
    <a class="sidebar-link" href="<?= $tgLink ?>" target="_blank"><i class="fa-brands fa-telegram" style="color:#2CA5E0"></i> Telegram Admin</a>
    <?php endif; ?>
    <div class="sidebar-divider"><i class="fa-solid fa-gear"></i> Lainnya</div>
    <a class="sidebar-link" href="download.php"><i class="fa-solid fa-cloud-arrow-down" style="color:var(--purple-light)"></i> Downloads</a>
    <a class="sidebar-link" href="admin.php"><i class="fa-solid fa-right-to-bracket" style="color:var(--purple-light)"></i> Login Admin</a>
  </nav>
</aside>

<!-- MAIN -->
<div class="page-shell">
  <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Kembali</a>

  <div class="track-card">
    <div class="track-header">
      <div class="track-header-icon"><i class="fa-solid fa-receipt"></i></div>
      <h1>Lacak Pesanan</h1>
      <p>Masukkan ID Transaksi, Nama, atau No HP Anda</p>
    </div>

    <form action="" method="GET">
      <div class="form-group">
        <label>Cari Pesanan</label>
        <input type="text" name="order_id" id="orderIdInput" placeholder="ID Transaksi / Nama / No HP..." value="<?= htmlspecialchars($searchQuery) ?>" required>
      </div>
      <button type="submit" class="btn-track" id="btnTrack">
        <i class="fa-solid fa-magnifying-glass"></i> Lacak Sekarang
      </button>
    </form>

    <?php if ($orderData):
        $isCompleted = strtolower($orderData['status']) === 'completed';
        $statusClass = $isCompleted ? 'status-success' : 'status-pending';
        $statusText  = $isCompleted ? 'BERHASIL' : 'PENDING';
        $key = $orderData['product_content'] ?? '';
        $isStockEmpty = str_contains($key, 'KOSONG') || str_contains($key, 'HABIS') || str_contains($key, 'HUBUNGI');
        $hasValidKey  = $key && !$isStockEmpty && $key !== 'Menunggu Pembayaran...';
    ?>
      <div class="result-card" id="resultCardArea">
        <div class="product-header">
          <div class="product-logo">
            <img src="<?= htmlspecialchars($orderData['product_image']) ?>" onerror="this.src='assets/img/qris.png'" alt="Produk">
          </div>
          <div>
            <div class="product-name"><?= htmlspecialchars($orderData['product'] ?? 'Produk') ?></div>
            <div class="product-buyer"><strong>Pembeli:</strong> <?= htmlspecialchars($orderData['name'] ?? '-') ?></div>
            <div class="product-buyer"><strong>Varian:</strong> <?= htmlspecialchars($orderData['item'] ?? '-') ?></div>
          </div>
        </div>

        <div class="result-row">
          <span class="result-label">ID Transaksi</span>
          <span class="result-value" style="color:var(--purple-light)"><?= htmlspecialchars($orderData['order_id']) ?></span>
        </div>
        <div class="result-row">
          <span class="result-label">Status</span>
          <span class="result-value"><span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span></span>
        </div>
        <div class="result-row">
          <span class="result-label">Tanggal Pesan</span>
          <span class="result-value"><?= date('d M Y, H:i', $orderData['created_at']) ?> WIB</span>
        </div>

        <?php if ($isCompleted): ?>
          <?php if ($hasValidKey): ?>
            <div class="license-box">
              <div class="license-box-label">Lisensi Key Anda</div>
              <div class="license-key" id="licenseKeyText"><?= htmlspecialchars($key) ?></div>
            </div>
            <div class="action-buttons">
              <button class="btn-action btn-copy" onclick="copyKey()">
                <i class="fa-solid fa-copy"></i> Copy Key
              </button>
              <a href="<?= $channelLink ?>" target="_blank" class="btn-action btn-purple">
                <i class="fa-solid fa-bullhorn"></i> Download File
              </a>
            </div>
          <?php elseif ($isStockEmpty): ?>
            <div class="info-box info-box-red">
              <i class="fa-solid fa-circle-exclamation"></i>
              <div><strong>Stok Habis / Kosong</strong><br>Silakan klaim stok secara manual ke Admin.</div>
            </div>
            <div class="action-buttons" style="grid-template-columns:1fr; margin-top:12px;">
              <a href="<?= $waLink ?>?text=Halo%20Admin,%20saya%20sudah%20bayar%20tapi%20stok%20kosong.%20Order%20ID:%20<?= urlencode($orderData['order_id']) ?>" target="_blank" class="btn-action btn-wa">
                <i class="fa-brands fa-whatsapp"></i> Klaim Manual ke Admin
              </a>
            </div>
          <?php else: ?>
            <div class="info-box info-box-yellow">
              <i class="fa-solid fa-clock"></i>
              <div><strong>Diproses Admin</strong><br>Pesanan sedang disiapkan, silakan tunggu atau chat admin.</div>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="info-box info-box-yellow">
            <i class="fa-solid fa-clock"></i>
            <div><strong>Menunggu Pembayaran</strong><br>Selesaikan pembayaran agar Lisensi Key dapat dikeluarkan.</div>
          </div>
          <div class="action-buttons" style="grid-template-columns:1fr; margin-top:12px;">
            <a href="detail.php?id=<?= htmlspecialchars($orderData['real_product_id']) ?>" class="btn-action btn-yellow">
              <i class="fa-solid fa-credit-card"></i> Ke Halaman Pembayaran
            </a>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  <?php if ($errorMsg): ?>
    document.addEventListener('DOMContentLoaded', () => {
      swalDark.fire({ icon: 'error', title: 'Oops...', html: '<?= $errorMsg ?>' });
    });
  <?php endif; ?>

  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const menuBtn = document.getElementById('menuBtn');
  const menuLabel = document.getElementById('menuLabel');
  const sidebarClose = document.getElementById('sidebarClose');

  function openSidebar(){ sidebar.classList.add('open'); overlay.classList.add('open'); menuLabel.textContent='Close'; document.body.style.overflow='hidden'; }
  function closeSidebar(){ sidebar.classList.remove('open'); overlay.classList.remove('open'); menuLabel.textContent='Menu'; document.body.style.overflow=''; }
  menuBtn.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
  sidebarClose.addEventListener('click', closeSidebar);
  overlay.addEventListener('click', closeSidebar);

  function copyKey() {
    const keyText = document.getElementById('licenseKeyText').innerText;
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(keyText).then(() => {
        swalDark.fire({ title: 'Tersalin!', text: 'Lisensi Key berhasil disalin.', icon: 'success', timer: 2000, showConfirmButton: false });
      }).catch(() => fallbackCopy(keyText));
    } else { fallbackCopy(keyText); }
  }
  function fallbackCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text; ta.style.position = 'fixed'; ta.style.left = '-9999px';
    document.body.appendChild(ta); ta.select();
    try {
      document.execCommand('copy');
      swalDark.fire({ title: 'Tersalin!', text: 'Lisensi Key berhasil disalin.', icon: 'success', timer: 2000, showConfirmButton: false });
    } catch(err) {
      swalDark.fire('Gagal', 'Silakan salin teks secara manual.', 'error');
    }
    document.body.removeChild(ta);
  }

  <?php if ($orderData && !$isCompleted): ?>
    let trackOrderId = "<?= htmlspecialchars($orderData['order_id']) ?>";
    let trackInterval = setInterval(async () => {
      try {
        const res = await fetch(`api.php?action=check_status&order_id=${trackOrderId}&t=${Date.now()}`, { cache: 'no-store' });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        if (data.status === 'completed' || data.status === 'paid' || data.status === 'success') {
          clearInterval(trackInterval);
          swalDark.fire({ title: 'Pembayaran Diterima!', text: 'Pesanan lunas. Halaman akan dimuat ulang...', icon: 'success', timer: 2000, showConfirmButton: false, allowOutsideClick: false })
            .then(() => window.location.reload());
        }
      } catch(e) {}
    }, 3000);
  <?php endif; ?>
</script>
</body>
</html>