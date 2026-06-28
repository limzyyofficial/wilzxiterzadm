<?php
$configFile = __DIR__ . '/data/config.json';
$config = [
    'title'       => 'WilzXiterz',
    'whatsapp'    => '6285173360622',
    'telegram'    => 'WilzXiterzVN',
    'channel'     => 'https://whatsapp.com/channel/0029VaYaVJc0gcfPo4GDws0e',
    'discord'     => '',
    'banner'      => 'assets/img/logops.png',
    'download_url'=> '',
    'vpn_block'   => false,
    'vpn_api_key' => '',
];
if (file_exists($configFile)) {
    $savedConfig = json_decode(file_get_contents($configFile), true);
    if (is_array($savedConfig)) $config = array_merge($config, $savedConfig);
}
$fullTitle  = htmlspecialchars($config['title']);
$firstPart  = $fullTitle; $secondPart = '';
if (strlen($fullTitle) > 5) { $firstPart = substr($fullTitle, 0, -3); $secondPart = substr($fullTitle, -3); }
$waLink      = "https://wa.me/" . preg_replace('/[^0-9]/', '', $config['whatsapp']);
$tgUsername  = ltrim($config['telegram'], '@');
$tgLink      = "https://t.me/" . htmlspecialchars($tgUsername);
$channelLink = htmlspecialchars($config['channel']);
$discordLink = htmlspecialchars($config['discord']);
$bannerImg   = htmlspecialchars($config['banner']);
$vpnEnabled  = !empty($config['vpn_block']) ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= $fullTitle ?> - Downloads</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --bg:       #0d0d12;
      --bg2:      #13131a;
      --bg3:      #1a1a24;
      --card:     #16161f;
      --card2:    #1e1e2a;
      --purple:   #7C3AED;
      --purple2:  #9D5FF5;
      --purple-dim: rgba(124,58,237,0.15);
      --purple-border: rgba(124,58,237,0.35);
      --green:    #22C55E;
      --green-dim: rgba(34,197,94,0.15);
      --yellow:   #EAB308;
      --yellow-dim: rgba(234,179,8,0.15);
      --red:      #EF4444;
      --text:     #ffffff;
      --text2:    rgba(255,255,255,0.55);
      --text3:    rgba(255,255,255,0.3);
      --sep:      rgba(255,255,255,0.07);
      --radius:   16px;
      --radius-sm:10px;
      --font: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    *{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
    html{background:var(--bg)}
    body{background:var(--bg);color:var(--text);font-family:var(--font);-webkit-font-smoothing:antialiased;overflow-x:hidden;padding-bottom:40px}

    /* NAVBAR */
    .nav{position:sticky;top:0;z-index:500;background:rgba(13,13,18,.88);backdrop-filter:blur(20px) saturate(1.8);-webkit-backdrop-filter:blur(20px) saturate(1.8);border-bottom:1px solid var(--sep);padding:14px 20px;display:flex;justify-content:space-between;align-items:center}
    .nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none}
    .nav-logo-icon{width:34px;height:34px;border-radius:9px;overflow:hidden;display:flex;align-items:center;justify-content:center}
    .nav-logo-icon img{width:34px;height:34px;object-fit:contain;border-radius:9px}
    .nav-logo-text{font-size:1.1rem;font-weight:800;letter-spacing:-.3px;color:var(--text)}
    .nav-logo-text span{color:var(--purple2)}
    .nav-menu-btn{background:rgba(255,255,255,.07);border:1px solid var(--sep);color:var(--text);width:38px;height:38px;border-radius:10px;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;transition:.15s;padding:0}
    .nav-menu-btn:active{background:rgba(255,255,255,.15)}

    /* OVERLAY + SIDEBAR */
    .overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:600;opacity:0;pointer-events:none;transition:opacity .3s;backdrop-filter:blur(6px)}
    .overlay.active{opacity:1;pointer-events:all}
    .sidebar{position:fixed;top:0;left:-320px;width:290px;height:100%;background:var(--bg2);z-index:700;transition:left .35s cubic-bezier(.4,0,.2,1);border-right:1px solid var(--sep);display:flex;flex-direction:column}
    .sidebar.active{left:0}
    .sb-head{padding:24px 20px 20px;border-bottom:1px solid var(--sep);display:flex;align-items:center;justify-content:space-between}
    .sb-logo{display:flex;align-items:center;gap:12px;text-decoration:none}
    .sb-logo-img{width:42px;height:42px;border-radius:12px;overflow:hidden;background:var(--bg3);display:flex;align-items:center;justify-content:center;border:1px solid var(--purple-border)}
    .sb-logo-img img{width:42px;height:42px;object-fit:contain}
    .sb-logo-name{font-size:1.15rem;font-weight:800;letter-spacing:-.3px;color:var(--text);line-height:1.1}
    .sb-logo-name span{color:var(--purple2)}
    .sb-close{background:rgba(255,255,255,.08);border:1px solid var(--sep);color:var(--text2);width:34px;height:34px;border-radius:50%;cursor:pointer;font-size:.95rem;display:flex;align-items:center;justify-content:center;transition:.15s}
    .sb-close:active{background:rgba(255,255,255,.15)}
    .sb-nav{flex:1;padding:10px 12px;overflow-y:auto}
    .sb-nav-item{display:flex;align-items:center;gap:14px;padding:13px 14px;border-radius:12px;color:var(--text);text-decoration:none;font-size:.92rem;font-weight:500;transition:background .15s;margin-bottom:2px}
    .sb-nav-item:active{background:rgba(255,255,255,.06)}
    .sb-nav-item .sb-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.88rem;flex-shrink:0}
    .sb-nav-item .sb-label{flex:1}
    .sb-nav-item .sb-arrow{color:var(--text3);font-size:.75rem}
    .sb-discord{margin:0 12px 20px;background:rgba(88,101,242,.15);border:1px solid rgba(88,101,242,.35);border-radius:14px;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;text-decoration:none}
    .sb-discord-left{display:flex;align-items:center;gap:10px}
    .sb-discord-icon{width:34px;height:34px;background:rgba(88,101,242,.25);border-radius:9px;display:flex;align-items:center;justify-content:center;color:#7289da;font-size:.95rem}
    .sb-discord-text{font-size:.85rem;font-weight:700;color:var(--text)}
    .sb-discord-support{font-size:.82rem;font-weight:700;color:#7289da}

    /* PAGE HEADER */
    .page-header{padding:24px 16px 8px}
    .page-header-tag{font-size:.65rem;font-weight:700;color:var(--purple2);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:6px}
    .page-header-title{font-size:1.6rem;font-weight:900;letter-spacing:-.4px;margin-bottom:6px}
    .page-header-sub{font-size:.83rem;color:var(--text2);line-height:1.5}

    /* SEARCH + FILTER */
    .dl-controls{padding:12px 16px 0}
    .search-wrap{background:var(--card);border:1px solid var(--sep);border-radius:14px;padding:12px 16px;display:flex;align-items:center;gap:10px;margin-bottom:12px}
    .search-wrap i{color:var(--text3);font-size:.9rem}
    .search-wrap input{flex:1;background:none;border:none;outline:none;color:var(--text);font-size:.9rem;font-family:var(--font)}
    .search-wrap input::placeholder{color:var(--text3)}
    .filter-row{display:flex;gap:8px;overflow-x:auto;padding-bottom:4px;-ms-overflow-style:none;scrollbar-width:none}
    .filter-row::-webkit-scrollbar{display:none}
    .filter-pill{background:var(--card);border:1px solid var(--sep);color:var(--text2);padding:7px 14px;border-radius:99px;font-size:.78rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:.15s;font-family:var(--font);white-space:nowrap;flex-shrink:0}
    .filter-pill.active{background:var(--purple);border-color:var(--purple);color:#fff;box-shadow:0 4px 14px rgba(124,58,237,.35)}
    .filter-pill:active{transform:scale(.95)}

    /* DOWNLOAD CARDS */
    .dl-list{padding:16px}
    .dl-card{background:var(--card);border:1px solid var(--sep);border-radius:18px;padding:16px;margin-bottom:12px;transition:border-color .2s}
    .dl-card:active{border-color:var(--purple-border)}

    .dl-card-inner{display:flex;align-items:center;gap:14px;margin-bottom:14px}
    .dl-img{width:52px;height:52px;border-radius:12px;background:var(--bg3);border:1px solid var(--sep);overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center}
    .dl-img .dl-img-fallback{font-size:1.4rem;color:var(--purple2)}
    .dl-info{flex:1;min-width:0}
    .dl-name{font-size:.97rem;font-weight:800;letter-spacing:-.2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:4px}
    .dl-desc{font-size:.76rem;color:var(--text2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .dl-badges{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
    .dl-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:99px;font-size:.7rem;font-weight:700}
    .dl-badge.android{background:rgba(61,220,132,.1);color:#3DDC84;border:1px solid rgba(61,220,132,.25)}
    .dl-badge.ios{background:rgba(255,255,255,.07);color:var(--text2);border:1px solid var(--sep)}
    .dl-badge.pc{background:rgba(0,161,255,.1);color:#38bdf8;border:1px solid rgba(56,189,248,.25)}
    .dl-badge.freefire{background:rgba(234,179,8,.1);color:var(--yellow);border:1px solid rgba(234,179,8,.25)}
    .dl-badge.pubg{background:rgba(251,146,60,.1);color:#fb923c;border:1px solid rgba(251,146,60,.25)}
    .dl-badge.stream{background:rgba(139,92,246,.12);color:var(--purple2);border:1px solid var(--purple-border)}

    .dl-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;background:linear-gradient(135deg,var(--purple),var(--purple2));color:#fff;text-decoration:none;padding:13px 16px;border-radius:12px;font-weight:800;font-size:.88rem;box-shadow:0 4px 20px rgba(124,58,237,.3);transition:.15s;border:none;cursor:pointer;font-family:var(--font)}
    .dl-btn:active{transform:scale(.97);opacity:.9}
    .dl-btn.disabled{background:var(--bg3);color:var(--text3);border:1px solid var(--sep);box-shadow:none;pointer-events:none}
    .dl-btn i{font-size:.85rem}

    /* EMPTY */
    .empty-state{text-align:center;padding:50px 20px;color:var(--text3)}
    .empty-state i{font-size:2.2rem;display:block;margin-bottom:12px}
    .empty-state p{font-size:.88rem}

    /* FLOATING CS */
    .floating-cs{position:fixed;bottom:20px;right:16px;background:var(--purple);color:#fff;text-decoration:none;padding:11px 18px;border-radius:99px;font-size:.8rem;font-weight:700;display:flex;align-items:center;gap:8px;box-shadow:0 8px 30px rgba(124,58,237,.45);z-index:400;transition:.2s}
    .floating-cs:active{transform:scale(.95)}

    footer{text-align:center;padding:20px;font-size:.75rem;color:var(--text3)}
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
<header class="nav">
  <a href="index.php" class="nav-logo">
    <div class="nav-logo-icon">
      <img src="<?= $bannerImg ?>" alt="logo" onerror="this.outerHTML='<span style=\'font-size:.85rem;font-weight:900;color:#fff\'>AX</span>'">
    </div>
    <div class="nav-logo-text"><?= $firstPart ?><span><?= $secondPart ?></span></div>
  </a>
  <button class="nav-menu-btn" id="menuToggle"><i class="fa-solid fa-bars"></i></button>
</header>

<div class="overlay" id="overlay"></div>
<div class="sidebar" id="sidebar">
  <div class="sb-head">
    <a href="index.php" class="sb-logo">
      <div class="sb-logo-img">
        <img src="<?= $bannerImg ?>" alt="logo" onerror="this.outerHTML='<i class=\'fa-solid fa-bolt-lightning\' style=\'color:var(--purple2)\'></i>'">
      </div>
      <div class="sb-logo-name"><?= $firstPart ?><span><?= $secondPart ?></span></div>
    </a>
    <button class="sb-close" id="closeMenu"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <nav class="sb-nav">
    <a href="index.php" class="sb-nav-item">
      <span class="sb-icon" style="background:rgba(255,255,255,.07)"><i class="fa-solid fa-box-open" style="color:var(--text2)"></i></span>
      <span class="sb-label">Products</span>
      <i class="fa-solid fa-chevron-right sb-arrow"></i>
    </a>
    <a href="tracking.php" class="sb-nav-item">
      <span class="sb-icon" style="background:rgba(255,255,255,.07)"><i class="fa-solid fa-magnifying-glass" style="color:var(--text2)"></i></span>
      <span class="sb-label">Guides</span>
      <i class="fa-solid fa-chevron-right sb-arrow"></i>
    </a>
    <a href="download.php" class="sb-nav-item">
      <span class="sb-icon" style="background:var(--purple-dim);border:1px solid var(--purple-border)"><i class="fa-solid fa-cloud-arrow-down" style="color:var(--purple2)"></i></span>
      <span class="sb-label" style="color:var(--purple2);font-weight:700">Downloads</span>
      <i class="fa-solid fa-chevron-right sb-arrow"></i>
    </a>
    <a href="admin.php" class="sb-nav-item">
      <span class="sb-icon" style="background:rgba(255,255,255,.07)"><i class="fa-solid fa-arrow-right-to-bracket" style="color:var(--text2)"></i></span>
      <span class="sb-label">Login</span>
      <i class="fa-solid fa-chevron-right sb-arrow"></i>
    </a>
    <a href="<?= $waLink ?>" target="_blank" class="sb-nav-item">
      <span class="sb-icon" style="background:rgba(37,211,102,.12)"><i class="fa-brands fa-whatsapp" style="color:#25D366"></i></span>
      <span class="sb-label">WhatsApp</span>
      <i class="fa-solid fa-chevron-right sb-arrow"></i>
    </a>
    <a href="<?= $tgLink ?>" target="_blank" class="sb-nav-item">
      <span class="sb-icon" style="background:rgba(42,165,224,.12)"><i class="fa-brands fa-telegram" style="color:#2CA5E0"></i></span>
      <span class="sb-label">Telegram</span>
      <i class="fa-solid fa-chevron-right sb-arrow"></i>
    </a>
  </nav>
  <?php if (!empty($config['discord'])): ?>
  <a href="<?= $discordLink ?>" target="_blank" class="sb-discord">
    <div class="sb-discord-left">
      <div class="sb-discord-icon"><i class="fa-brands fa-discord"></i></div>
      <div class="sb-discord-text">Discord</div>
    </div>
    <div class="sb-discord-support">Support</div>
  </a>
  <?php endif; ?>
</div>

<!-- PAGE HEADER -->
<div class="page-header">
  <div class="page-header-tag">FILE CENTER</div>
  <div class="page-header-title">Download Files</div>
  <div class="page-header-sub">Semua file installer tersedia di sini. Pilih produk dan klik download.</div>
</div>

<!-- CONTROLS -->
<div class="dl-controls">
  <div class="search-wrap">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" id="searchInput" placeholder="Cari produk...">
  </div>
  <div class="filter-row" id="filterRow">
    <button class="filter-pill active" data-cat="ALL"><i class="fa-solid fa-layer-group" style="font-size:.7rem"></i> ALL</button>
    <button class="filter-pill" data-cat="ANDROID"><i class="fa-brands fa-android" style="font-size:.7rem"></i> ANDROID</button>
    <button class="filter-pill" data-cat="PC"><i class="fa-solid fa-desktop" style="font-size:.7rem"></i> PC</button>
    <button class="filter-pill" data-cat="IOS"><i class="fa-brands fa-apple" style="font-size:.7rem"></i> IOS</button>
  </div>
</div>

<!-- DOWNLOAD LIST -->
<div class="dl-list" id="dlList">
  <div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Memuat produk...</p></div>
</div>

<footer><p>© 2026 <?= $firstPart ?><span style="color:var(--purple2)"><?= $secondPart ?></span> · All Rights Reserved</p></footer>

<a href="<?= $waLink ?>" target="_blank" class="floating-cs">
  <i class="fa-brands fa-whatsapp"></i> CS Bantuan
</a>

<script>
  // SIDEBAR
  const menuToggle = document.getElementById('menuToggle');
  const closeMenu  = document.getElementById('closeMenu');
  const sidebar    = document.getElementById('sidebar');
  const overlay    = document.getElementById('overlay');
  const toggleSidebar = (show) => { sidebar.classList.toggle('active',show); overlay.classList.toggle('active',show); };
  menuToggle.onclick = () => toggleSidebar(true);
  closeMenu.onclick = overlay.onclick = () => toggleSidebar(false);

  const globalDownloadUrl = '<?= addslashes($config['download_url'] ?? '') ?>';
  let allProducts = [];
  let activeCategory = 'ALL';

  const PLAT_ICONS = { android:'fa-android', pc:'fa-desktop', ios:'fa-apple', default:'fa-cube' };
  const PLAT_BRANDS = ['fa-android','fa-apple'];

  function getPlatIcon(p) {
    const k = (p||'').toLowerCase();
    return PLAT_ICONS[k] || PLAT_ICONS.default;
  }

  function getPlatBadgeClass(p) {
    const k = (p||'').toLowerCase();
    if (k === 'android') return 'android';
    if (k === 'ios') return 'ios';
    if (k === 'pc') return 'pc';
    return 'android';
  }

  function getCatBadgeClass(c) {
    const k = (c||'').toLowerCase();
    if (k === 'freefire' || k === 'ff') return 'freefire';
    if (k === 'pubg' || k === 'bgmi') return 'pubg';
    if (k === 'stream') return 'stream';
    return 'freefire';
  }

  function renderCards(products) {
    const dlList = document.getElementById('dlList');
    dlList.innerHTML = '';
    if (!products.length) {
      dlList.innerHTML = '<div class="empty-state"><i class="fa-solid fa-box-open"></i><p>Produk tidak ditemukan</p></div>';
      return;
    }
    products.forEach(p => {
      const downloadUrl = p.download_url || globalDownloadUrl || '';
      const platKey = (p.platform||'').toLowerCase();
      const icon = getPlatIcon(p.platform);
      const iconClass = PLAT_BRANDS.includes(icon) ? 'fa-brands ' + icon : 'fa-solid ' + icon;
      const imgHtml = '<i class="fa-solid fa-cube dl-img-fallback"></i>';

      const catBadge = p.category
        ? `<span class="dl-badge ${getCatBadgeClass(p.category)}">${p.category.toUpperCase()}</span>`
        : '';

      const card = document.createElement('div');
      card.className = 'dl-card';
      card.dataset.name = (p.name||'').toLowerCase();
      card.dataset.desc = (p.desc||'').toLowerCase();
      card.dataset.platform = (p.platform||'').toUpperCase();

      card.innerHTML =
        `<div class="dl-card-inner">
          <div class="dl-img">${imgHtml}</div>
          <div class="dl-info">
            <div class="dl-name">${p.name}</div>
            <div class="dl-desc">${p.desc || p.platform || ''}</div>
          </div>
        </div>
        <div class="dl-badges">
          <span class="dl-badge ${getPlatBadgeClass(p.platform)}">
            <i class="${iconClass}"></i> ${(p.platform||'').toUpperCase()}
          </span>
          ${catBadge}
        </div>` +
        (downloadUrl
          ? `<a href="${downloadUrl}" target="_blank" class="dl-btn"><i class="fa-solid fa-cloud-arrow-down"></i> Download Files</a>`
          : `<button class="dl-btn disabled"><i class="fa-solid fa-clock"></i> Coming Soon</button>`);

      dlList.appendChild(card);
    });
  }

  function filterProducts() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const filtered = allProducts.filter(p => {
      const matchQ = (p.name||'').toLowerCase().includes(q) || (p.desc||'').toLowerCase().includes(q);
      const matchCat = activeCategory === 'ALL' || (p.platform||'').toUpperCase() === activeCategory;
      return matchQ && matchCat;
    });
    renderCards(filtered);
  }

  // Filter pills
  document.getElementById('filterRow').addEventListener('click', e => {
    const pill = e.target.closest('.filter-pill');
    if (!pill) return;
    document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
    pill.classList.add('active');
    activeCategory = pill.dataset.cat;
    filterProducts();
  });

  document.getElementById('searchInput').addEventListener('input', filterProducts);

  // Load products
  fetch('api.php?action=get_products&t=' + Date.now(), { cache: 'no-store' })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(data => {
      if (!Array.isArray(data)) throw new Error('Invalid response');
      allProducts = data;
      filterProducts();
    })
    .catch(() => {
      document.getElementById('dlList').innerHTML =
        '<div class="empty-state"><i class="fa-solid fa-triangle-exclamation"></i><p>Gagal memuat produk</p></div>';
    });
</script>
</body>
</html>
