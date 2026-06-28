<?php
$configFile = __DIR__ . '/data/config.json';
$config = [
    'title'       => 'WilzXiterz',
    'whatsapp'    => '6285173360622',
    'telegram'    => 'WilzXiterzVN',
    'channel'     => '',
    'discord'     => '',
    'banner'      => 'assets/img/logops.png',
    'maintenance' => false,
    'download_url'=> '',
    'vpn_block'   => false,
    'google_client_id' => '',
];
if (file_exists($configFile)) {
    $savedConfig = json_decode(file_get_contents($configFile), true);
    if (is_array($savedConfig)) $config = array_merge($config, $savedConfig);
}
$fullTitle        = htmlspecialchars($config['title']);
$parts            = explode(' ', trim($fullTitle));
$namePart2        = count($parts) > 1 ? array_pop($parts) : '';
$namePart1        = implode(' ', $parts) ?: $fullTitle;
if (!$namePart2) { $namePart1 = substr($fullTitle,0,4); $namePart2 = substr($fullTitle,4); }
$bannerImg        = htmlspecialchars($config['banner'] ?? 'assets/img/logops.png');
$waLink           = "https://wa.me/" . preg_replace('/[^0-9]/', '', $config['whatsapp']);
$tgLink           = "https://t.me/" . ltrim(htmlspecialchars($config['telegram']), '@');
$discordLink      = htmlspecialchars($config['discord']);
$globalDownloadUrl= htmlspecialchars($config['download_url'] ?? '');
$isMaintenance    = !empty($config['maintenance']);
$hasBinance       = !empty($config['binance_api_key']) && !empty($config['binance_secret_key']) && !empty($config['binance_merchant_id']);
$vpnEnabled       = !empty($config['vpn_block']) ? 'true' : 'false';
$discordBanner    = !empty($config['discord']) ? htmlspecialchars($config['discord']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Detail Produk - <?= $fullTitle ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
    :root {
      --bg-deep:      #09090C;
      --bg-card:      #111115;
      --bg-card2:     #18181B;
      --border:       #27272A;
      --border2:      #3f3f46;
      --purple:       #C084FC;
      --purple-light: #D8B4FE;
      --purple-dim:   #9333EA;
      --purple-glow:  rgba(147,51,234,0.25);
      --text:         #F4F4F5;
      --text-muted:   #A1A1AA;
      --text-dim:     #71717A;
      --green:        #22C55E;
      --green-dim:    rgba(34,197,94,0.12);
      --yellow:       #EAB308;
      --red:          #EF4444;
      --discord:      #5865F2;
      --font: 'Inter', system-ui, -apple-system, sans-serif;
    }
    html { scroll-behavior: smooth; background: var(--bg-deep); }
    body { font-family: var(--font); background: var(--bg-deep); color: var(--text); min-height: 100vh; -webkit-font-smoothing: antialiased; padding-bottom: 120px; overflow-x: hidden; }
    a { color: inherit; text-decoration: none; }

    /* ── LOADING SCREEN ── */
    #ls{position:fixed;inset:0;z-index:9999;background:#030308;display:flex;flex-direction:column;align-items:center;justify-content:center;transition:opacity .6s,visibility .6s;overflow:hidden}
    #ls.hidden{opacity:0;visibility:hidden;pointer-events:none}
    .ls-nebula{position:absolute;border-radius:50%;filter:blur(60px);animation:ls-nebula-pulse 4s ease-in-out infinite alternate}
    .ls-nebula-1{width:300px;height:300px;top:-60px;left:-80px;background:radial-gradient(circle,rgba(147,51,234,.35),transparent 70%)}
    .ls-nebula-2{width:260px;height:260px;bottom:-40px;right:-60px;background:radial-gradient(circle,rgba(192,132,252,.25),transparent 70%)}
    @keyframes ls-nebula-pulse{0%{transform:scale(1);opacity:.6}100%{transform:scale(1.2);opacity:1}}
    .ls-center{position:relative;display:flex;flex-direction:column;align-items:center;gap:18px}
    .ls-planet{width:80px;height:80px;position:relative;animation:ls-float 3s ease-in-out infinite}
    @keyframes ls-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
    .ls-planet-core{width:80px;height:80px;border-radius:50%;background:radial-gradient(circle at 35% 35%,#d8b4fe,#9333ea 50%,#3b0764);box-shadow:0 0 40px rgba(147,51,234,.7),inset -8px -8px 20px rgba(0,0,0,.5)}
    .ls-planet-ring{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) rotateX(70deg);width:120px;height:120px;border:2px solid rgba(216,180,254,.4);border-radius:50%}
    .ls-orbit-wrap{position:absolute;top:50%;left:50%;width:110px;height:110px;margin:-55px 0 0 -55px;animation:ls-orbit 2s linear infinite}
    @keyframes ls-orbit{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
    .ls-orbit-dot{position:absolute;top:-5px;left:50%;margin-left:-5px;width:9px;height:9px;border-radius:50%;background:#d8b4fe;box-shadow:0 0 10px #c084fc}
    .ls-app-name{font-size:1.4rem;font-weight:900;background:linear-gradient(135deg,#fff 30%,#d8b4fe);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .ls-progress{width:150px;height:2px;background:rgba(255,255,255,.06);border-radius:99px;overflow:hidden;margin-top:6px}
    .ls-progress-bar{height:100%;width:0;background:linear-gradient(to right,#9333ea,#c084fc);border-radius:99px;animation:ls-load 1.6s cubic-bezier(.4,0,.2,1) forwards}
    @keyframes ls-load{0%{width:0}55%{width:70%}100%{width:100%}}

    /* ── NAVBAR ── */
    .site-nav {
      position: sticky; top: 0; left: 0; right: 0; z-index: 200;
      background: rgba(9,9,12,0.9); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--border);
    }
    .nav-inner { max-width: 1280px; margin: 0 auto; padding: 0 20px; height: 60px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .nav-logo { display: flex; align-items: center; gap: 10px; }
    .nav-logo-icon { width: 34px; height: 34px; border-radius: 8px; overflow: hidden; background: rgba(147,51,234,.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .nav-logo-icon img { width: 100%; height: 100%; object-fit: contain; }
    .nav-logo-text { display: flex; flex-direction: column; line-height: 1.1; }
    .logo-p1 { font-size: 13px; font-weight: 800; color: var(--purple); }
    .logo-p2 { font-size: 13px; font-weight: 800; color: var(--text); }
    .nav-links { display: none; align-items: center; gap: 28px; }
    @media(min-width:900px) { .nav-links { display: flex; } }
    .nav-link { display: flex; align-items: center; gap: 6px; font-size: 13.5px; color: var(--text-muted); transition: color .15s; }
    .nav-link:hover, .nav-link.active { color: var(--text); }
    .nav-link svg { width: 15px; height: 15px; }
    .nav-actions { display: flex; align-items: center; gap: 10px; }
    .btn-discord-nav {
      display: inline-flex; align-items: center; gap: 7px;
      background: var(--discord); color: #fff; font-size: 13px; font-weight: 600;
      padding: 7px 14px; border-radius: 8px; transition: opacity .15s;
    }
    .btn-discord-nav:hover { opacity: .85; }
    .btn-discord-nav svg { width: 15px; height: 15px; }
    .hamburger-btn { display: flex; align-items: center; gap: 6px; background: rgba(255,255,255,.07); border: 1px solid var(--border); color: var(--text); font-size: 13px; cursor: pointer; padding: 7px 12px; border-radius: 9px; font-family: var(--font); }
    @media(min-width:900px) { .hamburger-btn { display: none; } }

    /* ── MOBILE SIDEBAR ── */
    .sidebar-overlay { position: fixed; inset: 0; z-index: 300; background: rgba(0,0,0,.65); backdrop-filter: blur(4px); opacity: 0; pointer-events: none; transition: opacity .3s; }
    .sidebar-overlay.open { opacity: 1; pointer-events: all; }
    .sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: 280px; z-index: 310; background: var(--bg-card); border-right: 1px solid var(--border); transform: translateX(-100%); transition: transform .32s cubic-bezier(.4,0,.2,1); display: flex; flex-direction: column; overflow-y: auto; }
    .sidebar.open { transform: translateX(0); }
    .sidebar-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 18px; border-bottom: 1px solid var(--border); }
    .sidebar-close { background: rgba(255,255,255,.08); border: 1px solid var(--border); color: var(--text-muted); cursor: pointer; font-size: 13px; padding: 6px 11px; border-radius: 8px; font-family: var(--font); }
    .sidebar-nav { padding: 14px 10px; display: flex; flex-direction: column; gap: 3px; flex: 1; }
    .sb-link { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 10px; font-size: 14px; font-weight: 500; color: var(--text-muted); background: rgba(255,255,255,.03); border: 1px solid transparent; transition: all .15s; }
    .sb-link:hover { background: rgba(147,51,234,.12); color: var(--text); border-color: rgba(147,51,234,.2); }
    .sb-link svg { width: 16px; height: 16px; flex-shrink: 0; }
    .discord-sb { display: flex; align-items: center; gap: 10px; margin: 0 10px 16px; padding: 12px 14px; background: rgba(88,101,242,.12); border: 1px solid rgba(88,101,242,.3); border-radius: 12px; font-size: 13px; font-weight: 600; color: #818CF8; }
    .discord-sb svg { width: 16px; height: 16px; }

    /* ── PAGE ── */
    .shell { max-width: 1280px; margin: 0 auto; padding: 0 20px; }
    main { padding-top: 28px; }

    /* BREADCRUMB */
    .breadcrumb { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted); margin-bottom: 24px; transition: color .15s; }
    .breadcrumb:hover { color: var(--text); }
    .breadcrumb svg { width: 14px; height: 14px; }

    /* MAINTENANCE */
    .maintenance-bar { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.35); border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; text-align: center; font-weight: 700; color: #fca5a5; display: <?= $isMaintenance ? 'block' : 'none' ?>; }

    /* HERO GRID */
    .hero-grid { display: grid; grid-template-columns: 1fr auto; gap: 24px; align-items: start; margin-bottom: 32px; }
    @media(max-width:640px) { .hero-grid { grid-template-columns: 1fr; } }

    /* PILLS */
    .pills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
    .pill { display: inline-flex; align-items: center; gap: 5px; border: 1px solid var(--border); border-radius: 99px; padding: 4px 12px; font-size: 12px; font-weight: 600; color: var(--text-muted); background: var(--bg-card); }
    .pill svg { width: 13px; height: 13px; }
    .pill-ready { background: rgba(34,197,94,.1); border-color: rgba(34,197,94,.25); color: #4ade80; }
    .pill-bestseller { background: rgba(192,132,252,.1); border-color: rgba(192,132,252,.3); color: var(--purple); }
    .pill-updating { background: rgba(234,179,8,.1); border-color: rgba(234,179,8,.25); color: var(--yellow); }
    .pill-plat { background: rgba(56,189,248,.08); border-color: rgba(56,189,248,.2); color: #38bdf8; }

    h1.product-title { font-size: clamp(1.8rem,5vw,3rem); font-weight: 800; letter-spacing: -.02em; line-height: 1.1; background: linear-gradient(135deg,#fff 40%,var(--purple-light)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .product-subtitle { margin-top: 8px; font-size: 15px; color: var(--text-muted); }

    /* STAT CARDS */
    .stat-cards { display: grid; gap: 12px; min-width: 210px; }
    @media(max-width:640px) { .stat-cards { grid-template-columns: 1fr 1fr; min-width: unset; } }
    .stat-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 18px; }
    .stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-dim); }
    .stat-value { font-size: 1.6rem; font-weight: 800; color: var(--purple-light); margin-top: 6px; line-height: 1; }
    .stat-sub { font-size: 12.5px; color: var(--text-muted); margin-top: 5px; }
    .stat-avail { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; }
    .stat-avail .stat-value { color: var(--purple); }
    .stat-avail-note { font-size: 11px; color: var(--text-dim); text-align: right; line-height: 1.4; }

    /* DISCORD BANNER */
    .discord-banner { background: linear-gradient(135deg,rgba(88,101,242,.15),rgba(88,101,242,.05)); border: 1px solid rgba(88,101,242,.3); border-radius: 16px; padding: 18px 22px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 36px; }
    .discord-banner h2 { font-size: 15px; font-weight: 600; }
    .discord-banner p { margin-top: 4px; font-size: 13px; color: var(--text-muted); max-width: 500px; }
    .banner-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .btn-outline { display: inline-flex; align-items: center; gap: 6px; border: 1px solid var(--border); border-radius: 8px; padding: 8px 14px; font-size: 13px; font-weight: 600; color: var(--text-muted); transition: color .15s, border-color .15s; }
    .btn-outline:hover { color: var(--text); border-color: var(--border2); }
    .btn-outline svg { width: 15px; height: 15px; }
    .btn-discord-sm { display: inline-flex; align-items: center; gap: 7px; background: var(--discord); color: #fff; font-size: 13px; font-weight: 600; padding: 8px 14px; border-radius: 8px; transition: opacity .15s; }
    .btn-discord-sm:hover { opacity: .85; }
    .btn-discord-sm svg { width: 15px; height: 15px; }

    /* SECTION */
    .section { margin-bottom: 36px; }
    .section-eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--purple); margin-bottom: 4px; }
    .section-title { font-size: 20px; font-weight: 700; }
    .section-desc { margin-top: 4px; font-size: 13.5px; color: var(--text-muted); }

    /* PAYMENT METHOD */
    .payment-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px,1fr)); gap: 12px; margin-top: 18px; }
    .payment-card { background: var(--bg-card); border: 2px solid var(--border); border-radius: 14px; padding: 18px; cursor: pointer; transition: border-color .15s, background .15s; display: flex; flex-direction: column; gap: 4px; }
    .payment-card:hover { border-color: var(--border2); background: var(--bg-card2); }
    .payment-card.selected { border-color: var(--purple); background: rgba(192,132,252,.06); }
    .payment-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; font-size: 20px; }
    .payment-name { font-size: 15px; font-weight: 700; }
    .payment-desc { font-size: 12px; color: var(--text-muted); }

    /* PACKAGES */
    .packages-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 12px; margin-top: 18px; }
    @media(min-width:768px) { .packages-grid { grid-template-columns: repeat(4,1fr); } }
    @media(max-width:360px) { .packages-grid { grid-template-columns: 1fr; } }

    .pkg-card { background: var(--bg-card); border: 2px solid var(--border); border-radius: 14px; padding: 16px; cursor: pointer; transition: border-color .2s, background .2s; position: relative; }
    .pkg-card:hover { border-color: var(--border2); background: var(--bg-card2); }
    .pkg-card.selected { border-color: var(--purple); background: rgba(192,132,252,.06); }
    .pkg-badge { position: absolute; top: -1px; right: 14px; background: var(--purple-dim); color: #fff; font-size: 10px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; padding: 3px 8px; border-radius: 0 0 6px 6px; }
    .pkg-duration { font-size: 18px; font-weight: 800; }
    .pkg-access { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
    .pkg-price-label { font-size: 11px; color: var(--text-dim); margin-top: 12px; text-transform: uppercase; letter-spacing: .05em; }
    .pkg-price { font-size: 18px; font-weight: 800; margin-top: 2px; }
    .pkg-price-orig { font-size: 12px; color: var(--text-dim); text-decoration: line-through; margin-top: 1px; }
    .pkg-per-day { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
    .pkg-stock { display: inline-flex; align-items: center; gap: 5px; margin-top: 10px; font-size: 11px; }
    .pkg-stock-dot { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; }
    .pkg-stock-dot.empty { background: var(--yellow); }
    .pkg-stock.ok { color: #4ade80; }
    .pkg-stock.low { color: var(--yellow); }
    .pkg-stock.out { color: var(--red); }

    /* CUSTOMER FIELDS */
    .customer-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 18px; }
    @media(max-width:500px) { .customer-fields { grid-template-columns: 1fr; } }
    .field-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-dim); margin-bottom: 8px; }
    .field-input { width: 100%; background: var(--bg-card2); border: 1px solid var(--border); border-radius: 10px; padding: 12px 14px; font-size: 14px; color: var(--text); font-family: var(--font); outline: none; transition: border-color .15s; }
    .field-input:focus { border-color: rgba(192,132,252,.5); }
    .field-input::placeholder { color: var(--text-dim); }

    /* SUMMARY CARD */
    .summary-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 22px; margin-top: 28px; }
    .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 9px 0; border-bottom: 1px solid var(--border); font-size: 14px; }
    .summary-row:last-of-type { border-bottom: none; }
    .summary-label { color: var(--text-muted); }
    .summary-val { font-weight: 600; }
    .summary-total .summary-val { font-size: 20px; font-weight: 800; color: var(--purple-light); }

    .btn-checkout { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-top: 18px; background: linear-gradient(135deg,#9333EA,#7C3AED); color: #fff; font-size: 15px; font-weight: 700; padding: 14px 24px; border: none; border-radius: 12px; cursor: pointer; transition: opacity .15s, transform .1s; font-family: var(--font); box-shadow: 0 8px 24px rgba(147,51,234,.35); }
    .btn-checkout:hover { opacity: .9; transform: translateY(-1px); }
    .btn-checkout:active { transform: translateY(0); }
    .btn-checkout:disabled { opacity: .5; cursor: not-allowed; transform: none; }
    .btn-checkout svg { width: 18px; height: 18px; }

    /* PAYMENT MODAL (Bottom Sheet) */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.88); display: none; justify-content: center; align-items: flex-end; z-index: 1000; }
    .modal-content { background: var(--bg-card); padding: 20px 18px 32px; border-radius: 24px 24px 0 0; max-width: 500px; width: 100%; text-align: center; border: 1px solid var(--border); max-height: 94vh; overflow-y: auto; position: relative; padding-bottom: calc(20px + env(safe-area-inset-bottom)); animation: slideUp .35s cubic-bezier(.4,0,.2,1); }
    @keyframes slideUp { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .modal-handle { width: 40px; height: 4px; background: rgba(255,255,255,.1); border-radius: 99px; margin: 0 auto 18px; }
    .close-modal { position: absolute; top: 16px; right: 16px; color: var(--text-dim); cursor: pointer; font-size: 18px; background: rgba(255,255,255,.07); border: none; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: var(--font); }

    .modal-status-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 99px; font-size: 11px; font-weight: 700; margin-bottom: 12px; }
    .qr-wrap { background: #fff; border-radius: 14px; padding: 14px; display: inline-block; margin: 14px 0; }
    .qr-wrap img { width: 200px; height: 200px; display: block; border-radius: 8px; }
    .qr-loading-txt { font-size: 13px; color: var(--text-muted); padding: 40px 0; }
    .timer-row { display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 13px; color: var(--text-muted); margin-top: 8px; }
    .timer-val { font-weight: 700; color: var(--text); font-family: monospace; font-size: 15px; }

    .modal-detail-box { text-align: left; margin: 16px 0; background: var(--bg-card2); padding: 14px; border-radius: 12px; border: 1px solid var(--border); }
    .modal-detail-title { font-size: 10px; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }
    .modal-detail-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; }
    .modal-detail-row:last-child { margin-bottom: 0; border-top: 1px solid var(--border); padding-top: 6px; font-weight: 700; }
    .modal-detail-lbl { color: var(--text-muted); }
    .modal-detail-val { font-weight: 600; }
    .modal-total-val { color: var(--purple-light); }

    .license-box { background: #ecfdf5; border: 1px solid #6ee7b7; border-radius: 12px; padding: 16px; margin: 16px 0; font-family: monospace; font-size: 14px; color: #065f46; font-weight: 900; word-break: break-all; line-height: 1.5; }

    .modal-btn { display: flex; align-items: center; justify-content: center; gap: 7px; width: 100%; padding: 13px; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; border: none; font-family: var(--font); transition: opacity .15s; margin-top: 10px; }
    .modal-btn-purple { background: linear-gradient(135deg,#9333EA,#7C3AED); color: #fff; box-shadow: 0 4px 16px rgba(147,51,234,.3); }
    .modal-btn-purple:hover { opacity: .9; }
    .modal-btn-outline { background: none; border: 1px solid var(--border); color: var(--text-muted); }
    .modal-btn-outline:hover { border-color: var(--border2); color: var(--text); }
    .modal-btn-green { background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.3); color: #4ade80; }

    .dl-link { display: flex; align-items: center; justify-content: center; gap: 7px; width: 100%; padding: 13px; border-radius: 12px; font-size: 14px; font-weight: 700; background: linear-gradient(135deg,#9333EA,#7C3AED); color: #fff; box-shadow: 0 4px 16px rgba(147,51,234,.3); margin-top: 10px; text-decoration: none; }
    .dl-link:hover { opacity: .9; }

    /* BINANCE MODAL specifics */
    .bnb-qr-area { background: rgba(234,179,8,.06); border: 1px solid rgba(234,179,8,.25); border-radius: 14px; padding: 18px; margin: 14px 0; }
    .bnb-amount { font-size: 28px; font-weight: 900; color: var(--yellow); margin: 4px 0; }
    .bnb-idr { font-size: 13px; color: var(--text-muted); }

    /* FOOTER */
    footer { border-top: 1px solid var(--border); margin-top: 48px; }
    .footer-inner { max-width: 1280px; margin: 0 auto; padding: 36px 20px 24px; display: grid; gap: 36px; grid-template-columns: 1.4fr .8fr .9fr; }
    @media(max-width:768px) { .footer-inner { grid-template-columns: 1fr 1fr; } }
    @media(max-width:480px) { .footer-inner { grid-template-columns: 1fr; } }
    .footer-tagline { margin-top: 10px; font-size: 13.5px; color: var(--text-muted); line-height: 1.6; }
    .footer-heading { font-size: 13.5px; font-weight: 700; margin-bottom: 12px; }
    .footer-links { display: grid; gap: 9px; }
    .footer-links a { font-size: 13.5px; color: var(--text-muted); transition: color .15s; }
    .footer-links a:hover { color: var(--text); }
    .footer-support-desc { font-size: 13.5px; color: var(--text-muted); line-height: 1.6; margin-bottom: 12px; }
    .footer-discord-link { display: inline-flex; align-items: center; gap: 8px; background: rgba(88,101,242,.15); border: 1px solid rgba(88,101,242,.35); color: #818CF8; font-size: 13px; font-weight: 600; padding: 8px 14px; border-radius: 8px; transition: background .15s; }
    .footer-discord-link:hover { background: rgba(88,101,242,.25); }
    .footer-discord-link svg { width: 15px; height: 15px; }
    .footer-bottom { max-width: 1280px; margin: 0 auto; padding: 16px 20px; border-top: 1px solid var(--border); display: flex; flex-wrap: wrap; gap: 10px; justify-content: space-between; align-items: center; font-size: 12.5px; color: var(--text-dim); }
    .footer-bottom a { color: var(--text-dim); transition: color .15s; }
    .footer-bottom a:hover { color: var(--text-muted); }
  </style>
</head>
<body>

<!-- LOADING SCREEN -->
<div id="ls">
  <div class="ls-nebula ls-nebula-1"></div>
  <div class="ls-nebula ls-nebula-2"></div>
  <div class="ls-center">
    <div class="ls-planet">
      <div class="ls-planet-core"></div>
      <div class="ls-planet-ring"></div>
      <div class="ls-orbit-wrap"><div class="ls-orbit-dot"></div></div>
    </div>
    <div class="ls-app-name"><?= $fullTitle ?></div>
    <div class="ls-progress"><div class="ls-progress-bar"></div></div>
  </div>
</div>

<!-- VPN BLOCK OVERLAY -->
<style>
#vpn-block-overlay{display:none;position:fixed;inset:0;z-index:99999;background:#09090C;align-items:center;justify-content:center;flex-direction:column;padding:24px}
#vpn-block-overlay.show{display:flex}
.vpn-card{background:linear-gradient(135deg,#111115,#18181B);border:1.5px solid rgba(239,68,68,.4);border-radius:24px;padding:40px 32px;text-align:center;max-width:380px;width:100%;box-shadow:0 0 60px rgba(239,68,68,.12),0 24px 64px rgba(0,0,0,.6);animation:vpnFadeIn .4s ease}
@keyframes vpnFadeIn{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
.vpn-shield-wrap{width:80px;height:80px;margin:0 auto 20px}
.vpn-shield{width:80px;height:80px;filter:drop-shadow(0 0 18px rgba(239,68,68,.6))}
.vpn-title{font-size:22px;font-weight:800;color:#fff;margin-bottom:8px}
.vpn-sub{font-size:14px;color:#A1A1AA;line-height:1.6;margin-bottom:24px}
.vpn-blocked-badge{display:inline-flex;align-items:center;gap:8px;padding:11px 24px;background:linear-gradient(135deg,#7F1D1D,#991B1B);border:1px solid rgba(239,68,68,.5);border-radius:12px;font-size:14px;font-weight:700;color:#FCA5A5;margin-bottom:20px;box-shadow:0 0 20px rgba(239,68,68,.3)}
.vpn-blocked-badge::before{content:'';width:8px;height:8px;background:#EF4444;border-radius:50%;display:inline-block;animation:vpnBlink 1s infinite}
@keyframes vpnBlink{0%,100%{opacity:1}50%{opacity:.3}}
.vpn-info-box{background:rgba(147,51,234,.1);border:1px solid rgba(147,51,234,.3);border-radius:12px;padding:14px 16px;font-size:13px;color:#C084FC;line-height:1.6}
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
    <div class="vpn-info-box">Please disconnect your VPN or proxy and try again with your real IP address.</div>
  </div>
</div>
<script>
(function(){
  var E=<?= $vpnEnabled ?>;if(!E)return;
  fetch('vpn_check.php?t='+Date.now(),{cache:'no-store'}).then(function(r){return r.json()}).then(function(d){if(d&&d.blocked){var el=document.getElementById('vpn-block-overlay');if(el)el.classList.add('show');document.body.style.overflow='hidden';}}).catch(function(){});
})();
</script>

<!-- NAVBAR -->
<nav class="site-nav">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">
      <div class="nav-logo-icon">
        <img src="<?= $bannerImg ?>" alt="logo" onerror="this.outerHTML='<i class=\'fa-solid fa-layer-group\' style=\'color:var(--purple);font-size:14px\'></i>'">
      </div>
      <div class="nav-logo-text"><span class="logo-p1"><?= $namePart1 ?></span><span class="logo-p2"><?= $namePart2 ?></span></div>
    </a>
    <div class="nav-links">
      <a class="nav-link active" href="index.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
        Products
      </a>
      <a class="nav-link" href="tracking.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        Tracking
      </a>
      <a class="nav-link" href="download.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
        Downloads
      </a>
      <a class="nav-link" href="account.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        Akun
      </a>
    </div>
    <div class="nav-actions">
      <?php if ($discordBanner): ?>
      <a href="<?= $discordBanner ?>" target="_blank" class="btn-discord-nav">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.32 4.37A19.8 19.8 0 0 0 16.56 3c-.16.29-.35.68-.49 1a18.3 18.3 0 0 0-4.14 0c-.14-.32-.33-.71-.49-1a19.8 19.8 0 0 0-3.76 1.37C5.3 7.92 4.66 11.38 4.98 14.79a20 20 0 0 0 4.6 2.33c.37-.5.7-1.04.98-1.6-.54-.2-1.06-.45-1.54-.75l.37-.3a10.83 10.83 0 0 0 9.16 0l.38.3c-.49.3-1.01.55-1.55.75.28.56.61 1.1.98 1.6a20 20 0 0 0 4.61-2.33c.38-3.96-.64-7.39-2.65-10.42ZM9.68 12.71c-.9 0-1.63-.82-1.63-1.84s.72-1.83 1.63-1.83c.92 0 1.65.83 1.63 1.83 0 1.02-.72 1.84-1.63 1.84Zm4.65 0c-.9 0-1.63-.82-1.63-1.84s.72-1.83 1.63-1.83c.92 0 1.65.83 1.63 1.83 0 1.02-.71 1.84-1.63 1.84Z"/></svg>
        Discord
      </a>
      <?php endif; ?>
      <a href="account.php" class="btn-outline" style="font-size:13px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        Akun
      </a>
    </div>
    <button class="hamburger-btn" id="menuBtn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      <span id="menuBtnLabel">Menu</span>
    </button>
  </div>
</nav>

<!-- SIDEBAR -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <a href="index.php" class="nav-logo">
      <div class="nav-logo-icon"><img src="<?= $bannerImg ?>" alt="logo" style="width:100%;height:100%;object-fit:contain"></div>
      <div class="nav-logo-text"><span class="logo-p1"><?= $namePart1 ?></span><span class="logo-p2"><?= $namePart2 ?></span></div>
    </a>
    <button class="sidebar-close" id="sidebarClose">✕ Close</button>
  </div>
  <nav class="sidebar-nav">
    <a class="sb-link" href="index.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m21 8-9-5-9 5 9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
      Products
    </a>
    <a class="sb-link" href="tracking.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      Tracking
    </a>
    <a class="sb-link" href="download.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
      Downloads
    </a>
    <a class="sb-link" href="account.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Akun
    </a>
    <a class="sb-link" href="<?= $waLink ?>" target="_blank">
      <svg viewBox="0 0 24 24" fill="currentColor" style="color:#25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
      WhatsApp
    </a>
    <?php if ($tgLink): ?>
    <a class="sb-link" href="<?= $tgLink ?>" target="_blank">
      <svg viewBox="0 0 24 24" fill="currentColor" style="color:#2CA5E0"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.96 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
      Telegram
    </a>
    <?php endif; ?>
  </nav>
  <?php if ($discordBanner): ?>
  <a href="<?= $discordBanner ?>" target="_blank" class="discord-sb">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.32 4.37A19.8 19.8 0 0 0 16.56 3c-.16.29-.35.68-.49 1a18.3 18.3 0 0 0-4.14 0c-.14-.32-.33-.71-.49-1a19.8 19.8 0 0 0-3.76 1.37C5.3 7.92 4.66 11.38 4.98 14.79a20 20 0 0 0 4.6 2.33c.37-.5.7-1.04.98-1.6-.54-.2-1.06-.45-1.54-.75l.37-.3a10.83 10.83 0 0 0 9.16 0l.38.3c-.49.3-1.01.55-1.55.75.28.56.61 1.1.98 1.6a20 20 0 0 0 4.61-2.33c.38-3.96-.64-7.39-2.65-10.42ZM9.68 12.71c-.9 0-1.63-.82-1.63-1.84s.72-1.83 1.63-1.83c.92 0 1.65.83 1.63 1.83 0 1.02-.72 1.84-1.63 1.84Zm4.65 0c-.9 0-1.63-.82-1.63-1.84s.72-1.83 1.63-1.83c.92 0 1.65.83 1.63 1.83 0 1.02-.71 1.84-1.63 1.84Z"/></svg>
    Discord — Support
  </a>
  <?php endif; ?>
</aside>

<!-- MAIN -->
<main>
  <div class="shell">
    <!-- Breadcrumb -->
    <a href="index.php" class="breadcrumb">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
      Back to products
    </a>

    <!-- MAINTENANCE -->
    <?php if ($isMaintenance): ?>
    <div class="maintenance-bar">
      <i class="fa-solid fa-triangle-exclamation"></i> Maintenance — Pembelian ditutup sementara. Silahkan hubungi admin.
    </div>
    <?php endif; ?>

    <!-- HERO GRID -->
    <div class="hero-grid" id="heroGrid">
      <!-- Kiri: info produk (diisi JS) -->
      <div>
        <div class="pills" id="prodPills">
          <span class="pill" id="prodPlatBadge"><i class="fa-solid fa-cube"></i> —</span>
          <span class="pill pill-ready" id="prodStatusBadge">Ready</span>
          <span class="pill pill-bestseller" id="prodBestBadge" style="display:none">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>
            Best Seller
          </span>
        </div>
        <h1 class="product-title" id="prodTitle">Memuat...</h1>
        <p class="product-subtitle" id="prodSubtitle">Panel</p>
      </div>
      <!-- Kanan: stat cards -->
      <div class="stat-cards">
        <div class="stat-card">
          <div class="stat-label">Starts from</div>
          <div class="stat-value" id="prodMinPrice">—</div>
          <div class="stat-sub" id="prodMinInfo">—</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Availability</div>
          <div class="stat-avail">
            <div>
              <div class="stat-value" id="prodTotalStock">—</div>
              <div class="stat-sub">license ready</div>
            </div>
            <div class="stat-avail-note">Auto delivery<br>after paid</div>
          </div>
        </div>
      </div>
    </div>

    <!-- DISCORD / SUPPORT BANNER -->
    <div class="discord-banner">
      <div>
        <h2>Need help before checkout?</h2>
        <p><?= $discordBanner ? 'Join Discord for member vouchers, restock alerts, setup guidance, license resets, and checkout help.' : 'Contact us on WhatsApp for manual orders, setup guidance, license resets, and checkout help.' ?></p>
      </div>
      <div class="banner-actions">
        <a href="download.php" class="btn-outline">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
          Downloads
        </a>
        <?php if ($discordBanner): ?>
        <a href="<?= $discordBanner ?>" target="_blank" class="btn-discord-sm">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.32 4.37A19.8 19.8 0 0 0 16.56 3c-.16.29-.35.68-.49 1a18.3 18.3 0 0 0-4.14 0c-.14-.32-.33-.71-.49-1a19.8 19.8 0 0 0-3.76 1.37C5.3 7.92 4.66 11.38 4.98 14.79a20 20 0 0 0 4.6 2.33c.37-.5.7-1.04.98-1.6-.54-.2-1.06-.45-1.54-.75l.37-.3a10.83 10.83 0 0 0 9.16 0l.38.3c-.49.3-1.01.55-1.55.75.28.56.61 1.1.98 1.6a20 20 0 0 0 4.61-2.33c.38-3.96-.64-7.39-2.65-10.42ZM9.68 12.71c-.9 0-1.63-.82-1.63-1.84s.72-1.83 1.63-1.83c.92 0 1.65.83 1.63 1.83 0 1.02-.72 1.84-1.63 1.84Zm4.65 0c-.9 0-1.63-.82-1.63-1.84s.72-1.83 1.63-1.83c.92 0 1.65.83 1.63 1.83 0 1.02-.71 1.84-1.63 1.84Z"/></svg>
          Join Discord
        </a>
        <?php else: ?>
        <a href="<?= $waLink ?>" target="_blank" class="btn-discord-sm" style="background:#25D366">
          <i class="fa-brands fa-whatsapp" style="font-size:15px"></i>
          WhatsApp Admin
        </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- PAYMENT METHOD SECTION -->
    <div class="section" id="<?= $isMaintenance ? '' : 'checkoutSection' ?>">
      <p class="section-eyebrow">Checkout</p>
      <h2 class="section-title">Payment Method</h2>
      <p class="section-desc">Choose a payment method before selecting your package.</p>

      <div class="payment-grid">
        <div class="payment-card selected" onclick="selectPayment('qris',this)" id="pay-qris">
          <div class="payment-icon" style="background:rgba(34,197,94,.1)">🏧</div>
          <div class="payment-name">QRIS</div>
          <div class="payment-desc">QRIS — e-wallet &amp; m-banking Indonesia</div>
        </div>
        <?php if ($hasBinance): ?>
        <div class="payment-card" onclick="selectPayment('binance',this)" id="pay-binance">
          <div class="payment-icon" style="background:rgba(234,179,8,.1)">⬡</div>
          <div class="payment-name">Binance Pay</div>
          <div class="payment-desc">Binance Pay ID &amp; QR</div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- PACKAGES SECTION -->
    <div class="section">
      <p class="section-eyebrow">Packages</p>
      <h2 class="section-title">Select Package</h2>
      <p class="section-desc">Pick the duration that matches what you need.</p>
      <div class="packages-grid" id="item-list">
        <div style="grid-column:1/-1;padding:32px;text-align:center;color:var(--text-dim)">
          <i class="fa-solid fa-spinner fa-spin"></i> Memuat paket...
        </div>
      </div>

      <!-- Customer fields -->
      <div class="customer-fields" id="<?= $isMaintenance ? '' : 'customerFields' ?>">
        <div>
          <div class="field-label">Nama / Username</div>
          <input type="text" class="field-input" id="customer-name" placeholder="Masukkan nama kamu" <?= $isMaintenance ? 'disabled' : '' ?>>
        </div>
        <div>
          <div class="field-label">No. WhatsApp <span style="color:var(--text-dim);font-weight:400">(opsional)</span></div>
          <input type="tel" class="field-input" id="customer-phone" placeholder="08xxxxxxxx" <?= $isMaintenance ? 'disabled' : '' ?>>
        </div>
      </div>

      <!-- Summary -->
      <div class="summary-card">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:14px">Order Summary</h3>
        <div class="summary-row">
          <span class="summary-label">Product</span>
          <span class="summary-val" id="sum-prod">—</span>
        </div>
        <div class="summary-row">
          <span class="summary-label">Package</span>
          <span class="summary-val" id="sum-pkg">—</span>
        </div>
        <div class="summary-row">
          <span class="summary-label">Payment</span>
          <span class="summary-val" id="sum-pay">QRIS</span>
        </div>
        <div class="summary-row">
          <span class="summary-label">Delivery</span>
          <span class="summary-val" style="color:#4ade80">Auto · Instant</span>
        </div>
        <div class="summary-row summary-total">
          <span class="summary-label">Total</span>
          <span class="summary-val" id="sum-price">—</span>
        </div>
        <button class="btn-checkout" id="buyNowBtn" <?= $isMaintenance ? 'disabled' : '' ?>>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          <?= $isMaintenance ? 'Maintenance — Ditutup' : 'Continue to Checkout' ?>
        </button>
        <p style="margin-top:10px;font-size:11.5px;color:var(--text-dim);text-align:center">License delivered automatically after payment is confirmed</p>
      </div>
    </div>
  </div><!-- /shell -->
</main>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div>
      <a href="index.php" class="nav-logo" style="display:inline-flex">
        <div class="nav-logo-icon"><img src="<?= $bannerImg ?>" alt="logo"></div>
        <div class="nav-logo-text" style="margin-left:10px"><span class="logo-p1"><?= $namePart1 ?></span><span class="logo-p2"><?= $namePart2 ?></span></div>
      </a>
      <p class="footer-tagline">Digital licenses, setup resources, secure checkout, and customer support in one place.</p>
    </div>
    <div>
      <h2 class="footer-heading">Quick Links</h2>
      <nav class="footer-links">
        <a href="index.php">Products</a>
        <a href="download.php">Downloads</a>
        <a href="tracking.php">Tracking</a>
        <a href="account.php">Akun Saya</a>
      </nav>
    </div>
    <div>
      <h2 class="footer-heading">Support</h2>
      <p class="footer-support-desc">Setup help, license delivery checks, reset requests, and payment support.</p>
      <?php if ($discordBanner): ?>
      <a href="<?= $discordBanner ?>" target="_blank" class="footer-discord-link">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.32 4.37A19.8 19.8 0 0 0 16.56 3c-.16.29-.35.68-.49 1a18.3 18.3 0 0 0-4.14 0c-.14-.32-.33-.71-.49-1a19.8 19.8 0 0 0-3.76 1.37C5.3 7.92 4.66 11.38 4.98 14.79a20 20 0 0 0 4.6 2.33c.37-.5.7-1.04.98-1.6-.54-.2-1.06-.45-1.54-.75l.37-.3a10.83 10.83 0 0 0 9.16 0l.38.3c-.49.3-1.01.55-1.55.75.28.56.61 1.1.98 1.6a20 20 0 0 0 4.61-2.33c.38-3.96-.64-7.39-2.65-10.42ZM9.68 12.71c-.9 0-1.63-.82-1.63-1.84s.72-1.83 1.63-1.83c.92 0 1.65.83 1.63 1.83 0 1.02-.72 1.84-1.63 1.84Zm4.65 0c-.9 0-1.63-.82-1.63-1.84s.72-1.83 1.63-1.83c.92 0 1.65.83 1.63 1.83 0 1.02-.71 1.84-1.63 1.84Z"/></svg>
        Discord — Support
      </a>
      <?php else: ?>
      <a href="<?= $waLink ?>" target="_blank" class="footer-discord-link" style="background:rgba(37,211,102,.12);border-color:rgba(37,211,102,.3);color:#4ade80">
        <i class="fa-brands fa-whatsapp"></i> WhatsApp Admin
      </a>
      <?php endif; ?>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© 2026 <?= $fullTitle ?>. All rights reserved.</span>
    <div style="display:flex;gap:14px">
      <a href="tracking.php">Tracking</a>
      <a href="account.php">Akun</a>
      <a href="admin.php">Admin</a>
    </div>
  </div>
</footer>

<!-- PAYMENT MODAL (QRIS) -->
<div class="modal-overlay" id="paymentModal">
  <div class="modal-content">
    <div class="modal-handle"></div>
    <button class="close-modal" onclick="cancelPayment()">✕</button>
    <div id="paymentHeaderStatus"></div>
    <div class="modal-status-tag" id="statusBadgeElement" style="background:rgba(234,179,8,.2);color:#EAB308;border:1px solid #EAB308">PENDING</div>
    <div class="qr-wrap">
      <img id="qrImage" src="" alt="QR Code" style="display:none">
      <div class="qr-loading-txt" id="qrLoadingText">Menunggu QR Code...</div>
    </div>
    <div class="timer-row">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
      Expires in <span class="timer-val" id="paymentTimer">60:00</span>
    </div>
    <div class="modal-detail-box">
      <div class="modal-detail-title">DETAIL ORDER</div>
      <div class="modal-detail-row"><span class="modal-detail-lbl">Produk</span><span class="modal-detail-val" id="modalProductName">—</span></div>
      <div class="modal-detail-row"><span class="modal-detail-lbl">Paket</span><span class="modal-detail-val" id="modalItemLabel">—</span></div>
      <div class="modal-detail-row"><span class="modal-detail-lbl">Order ID</span><span class="modal-detail-val" id="transactionId" style="font-family:monospace;font-size:11px">—</span></div>
      <div class="modal-detail-row"><span class="modal-detail-lbl">Tanggal</span><span class="modal-detail-val" id="transactionDate">—</span></div>
      <div class="modal-detail-row"><span class="modal-detail-lbl">Total Bayar</span><span class="modal-detail-val modal-total-val" id="paymentAmountModal">—</span></div>
    </div>
    <div class="license-box" id="licenseKeyInput" style="display:none">—</div>
    <div id="apkDownloadContainer" style="display:none">
      <a id="apkDownloadLink" href="#" target="_blank" class="dl-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
        Download Installer
      </a>
    </div>
    <button class="modal-btn modal-btn-purple" id="copyLicenseBtn" onclick="copyLicenseKey()" style="display:none">
      <i class="fa-solid fa-copy"></i> Copy License Key
    </button>
    <button class="modal-btn modal-btn-outline" id="cancelPaymentBtn" onclick="cancelPayment()">
      <i class="fa-solid fa-xmark"></i> Batalkan Pesanan
    </button>
    <button class="modal-btn modal-btn-green" id="closePaymentBtn" onclick="closeSuccessModal()" style="display:none">
      <i class="fa-solid fa-check"></i> Selesai
    </button>
  </div>
</div>

<!-- BINANCE MODAL -->
<div class="modal-overlay" id="binanceModal">
  <div class="modal-content">
    <div class="modal-handle"></div>
    <button class="close-modal" onclick="cancelPayment()">✕</button>
    <div style="font-size:13px;font-weight:700;color:var(--text-dim);margin-bottom:8px">BINANCE PAY</div>
    <div class="modal-status-tag" id="bnbStatusBadge" style="background:rgba(234,179,8,.2);color:#EAB308;border:1px solid #EAB308">PENDING</div>
    <div class="bnb-qr-area">
      <div style="font-size:12px;color:var(--text-dim);margin-bottom:6px">Order ID</div>
      <div style="font-family:monospace;font-size:13px;color:var(--text);margin-bottom:12px" id="bnbOrderId">—</div>
      <div style="font-size:12px;color:var(--text-dim)">Total (IDR)</div>
      <div class="bnb-amount" id="bnbIdrAmount">—</div>
      <div class="bnb-idr" id="bnbUsdtAmount">— USDT</div>
      <div style="font-size:11px;color:var(--text-dim);margin-top:8px" id="bnbDate">—</div>
    </div>
    <a href="#" target="_blank" id="bnbPayBtn" class="modal-btn modal-btn-purple" style="display:none;text-decoration:none">
      <i class="fa-solid fa-arrow-up-right-from-square"></i> Bayar di Binance
    </a>
    <div class="timer-row" style="margin-top:8px">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
      <span class="timer-val" id="paymentTimer2">60:00</span>
    </div>
    <div id="bnbLicenseSection" style="display:none">
      <div class="license-box" id="bnbLicenseKey">—</div>
    </div>
    <button class="modal-btn modal-btn-purple" id="bnbCopyBtn" onclick="copyBnbLicense()" style="display:none">
      <i class="fa-solid fa-copy"></i> Copy License Key
    </button>
    <button class="modal-btn modal-btn-outline" id="bnbCancelBtn" onclick="cancelPayment()">
      <i class="fa-solid fa-xmark"></i> Batalkan
    </button>
    <button class="modal-btn modal-btn-green" id="bnbCloseBtn" onclick="closeSuccessModal()" style="display:none">
      <i class="fa-solid fa-check"></i> Selesai
    </button>
  </div>
</div>

<script>
  // ── CONSTANTS ──
  const GLOBAL_DOWNLOAD_URL = '<?= addslashes($globalDownloadUrl) ?>';
  const WA_URL              = '<?= addslashes($waLink) ?>';
  const CHANNEL_URL         = '<?= addslashes($config['channel'] ?? '') ?>';
  const BINANCE_ACTIVE      = <?= $hasBinance ? 'true' : 'false' ?>;
  const IS_MAINTENANCE      = <?= $isMaintenance ? 'true' : 'false' ?>;

  // ── STATE ──
  let currentProduct = null, selectedItem = null, selectedItemIndex = -1;
  let paymentTimerInterval = null, statusCheckInterval = null, isPaymentCompleted = false;
  let selectedPaymentMethod = 'qris';

  // ── LOADING SCREEN ──
  if (!sessionStorage.getItem('ls_shown')) {
    sessionStorage.setItem('ls_shown', '1');
    window.addEventListener('load', () => setTimeout(() => document.getElementById('ls').classList.add('hidden'), 1400));
  } else {
    document.getElementById('ls').classList.add('hidden');
  }

  // ── SIDEBAR ──
  const sidebar = document.getElementById('sidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  const menuBtn = document.getElementById('menuBtn');
  const menuBtnLabel = document.getElementById('menuBtnLabel');
  const sidebarClose = document.getElementById('sidebarClose');
  function openSidebar() { sidebar.classList.add('open'); sidebarOverlay.classList.add('open'); menuBtnLabel.textContent='Close'; document.body.style.overflow='hidden'; }
  function closeSidebar() { sidebar.classList.remove('open'); sidebarOverlay.classList.remove('open'); menuBtnLabel.textContent='Menu'; document.body.style.overflow=''; }
  menuBtn.onclick = () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
  sidebarClose.onclick = sidebarOverlay.onclick = closeSidebar;

  // Prevent modal close on escape if not desired
  window.addEventListener('beforeunload', e => {
    if (document.getElementById('paymentModal').style.display === 'flex' && !isPaymentCompleted) {
      e.preventDefault(); e.returnValue = '';
    }
  });

  // ── PAYMENT METHOD SELECT ──
  function selectPayment(method, el) {
    if (method === 'binance' && !BINANCE_ACTIVE) return;
    document.querySelectorAll('.payment-card').forEach(m => m.classList.remove('selected'));
    el.classList.add('selected');
    selectedPaymentMethod = method;
    document.getElementById('sum-pay').textContent = method === 'binance' ? 'Binance Pay' : 'QRIS';
  }

  // ── HELPERS ──
  function formatRp(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }
  function parseRpVal(val) { return parseInt(String(val||'').replace(/[^0-9]/g,''))||0; }
  function getEffPrice(i) { return (i.promo && i.promo_price) ? Number(i.promo_price) : parseRpVal(i.price); }
  function getItemDays(item) {
    if (item.days) return Number(item.days);
    if (item.label) { const m = String(item.label).match(/\d+/); if (m) return parseInt(m[0]); }
    return 1;
  }
  function getProductDownloadUrl() {
    if (currentProduct && currentProduct.download_url) return currentProduct.download_url;
    if (GLOBAL_DOWNLOAD_URL) return GLOBAL_DOWNLOAD_URL;
    return CHANNEL_URL || WA_URL;
  }

  // ── RENDER PACKAGES ──
  function renderItems(items) {
    const list = document.getElementById('item-list');
    list.innerHTML = '';
    if (!items || !items.length) {
      list.innerHTML = '<div style="grid-column:1/-1;padding:32px;text-align:center;color:var(--text-dim)">Tidak ada paket tersedia.</div>';
      return;
    }
    const minPrice = Math.min(...items.map(i => getEffPrice(i)));
    items.forEach((item, idx) => {
      const card = document.createElement('div');
      card.className = 'pkg-card' + (idx === 0 ? ' selected' : '');
      card.onclick = () => {
        document.querySelectorAll('.pkg-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        selectedItem = item; selectedItemIndex = idx;
        document.getElementById('sum-pkg').textContent = item.label || (getItemDays(item) + ' Days');
        document.getElementById('sum-price').textContent = formatRp(getEffPrice(item));
      };
      const itemDays  = getItemDays(item);
      const baseAmt   = parseRpVal(item.price);
      const effAmt    = getEffPrice(item);
      const perDay    = itemDays ? Math.round(effAmt / itemDays) : effAmt;
      const stock     = item.stock || 0;
      const stockOut  = stock === 0;
      const stockLow  = stock > 0 && stock < 10;
      const isBest    = idx === items.length - 1 && items.length > 2;

      let stockClass  = 'ok', stockTxt = stock + ' licenses ready';
      if (stockOut)  { stockClass = 'out'; stockTxt = 'Pre-order'; }
      else if (stockLow) { stockClass = 'low'; }

      card.innerHTML = `
        ${isBest ? '<div class="pkg-badge">Best Value</div>' : ''}
        <div class="pkg-duration">${item.label || itemDays + ' Day'}</div>
        <div class="pkg-access">${itemDays} ${itemDays===1?'day':'days'} access</div>
        <div class="pkg-price-label">PRICE</div>
        ${item.promo && item.promo_price ? `<div class="pkg-price-orig">${formatRp(baseAmt)}</div>` : ''}
        <div class="pkg-price" style="${item.promo?'color:#4ade80':''}">${formatRp(effAmt)}${item.promo?'<span style="font-size:10px;color:#4ade80;margin-left:4px">PROMO</span>':''}</div>
        ${itemDays > 1 ? `<div class="pkg-per-day">${formatRp(perDay)} / day</div>` : ''}
        <div class="pkg-stock ${stockClass}">
          <div class="pkg-stock-dot ${stockOut?'empty':stockLow?'empty':''}"></div>
          ${stockTxt}
        </div>`;
      list.appendChild(card);
    });
    // Select first
    if (items.length) {
      selectedItem = items[0]; selectedItemIndex = 0;
      document.getElementById('sum-pkg').textContent = items[0].label || (getItemDays(items[0]) + ' Days');
      document.getElementById('sum-price').textContent = formatRp(getEffPrice(items[0]));
    }
  }

  // ── LOAD PRODUCT ──
  const urlParams = new URLSearchParams(window.location.search);
  const productId = urlParams.get('id');
  if (!productId) window.location.href = 'index.php';

  fetch('api.php?action=get_products&t=' + Date.now(), { cache: 'no-store' })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(data => {
      if (!Array.isArray(data)) throw new Error('Invalid response');
      currentProduct = data.find(p => p.id == productId);
      if (!currentProduct) { window.location.href = 'index.php'; return; }

      // Pills & title
      const plat = (currentProduct.platform || '—').toUpperCase();
      const platIconMap = { ANDROID:'fa-brands fa-android', PC:'fa-solid fa-desktop', IOS:'fa-brands fa-apple' };
      const platIcon = platIconMap[plat] || 'fa-solid fa-cube';
      document.getElementById('prodPlatBadge').innerHTML = `<i class="${platIcon}"></i> ${plat}`;
      document.getElementById('prodPlatBadge').className = 'pill pill-plat';

      let isReady;
      if (currentProduct.manual_status === 'ready') isReady = true;
      else if (currentProduct.manual_status === 'updating') isReady = false;
      else isReady = (currentProduct.prices||[]).some(pr => (pr.stock||0) > 0);

      const statusBadge = document.getElementById('prodStatusBadge');
      statusBadge.textContent = isReady ? 'Ready' : 'Updating';
      statusBadge.className = 'pill ' + (isReady ? 'pill-ready' : 'pill-updating');

      if (currentProduct.best_seller) document.getElementById('prodBestBadge').style.display = 'inline-flex';

      document.getElementById('prodTitle').textContent = currentProduct.name;
      document.getElementById('prodSubtitle').textContent = currentProduct.desc || 'Panel untuk ' + plat;
      document.getElementById('sum-prod').textContent = currentProduct.name;
      document.title = currentProduct.name + ' - <?= $fullTitle ?>';

      const totalStock = (currentProduct.prices||[]).reduce((s,pr)=>s+(pr.stock||0),0);
      document.getElementById('prodTotalStock').textContent = totalStock;

      if (currentProduct.prices && currentProduct.prices.length) {
        const minItem = currentProduct.prices.reduce((a,b) => getEffPrice(a)<=getEffPrice(b)?a:b);
        const minAmt  = getEffPrice(minItem);
        const minUsd  = (minAmt / 18000).toFixed(2);
        document.getElementById('prodMinPrice').textContent = formatRp(minAmt);
        document.getElementById('prodMinInfo').textContent = '$' + minUsd + ' · ' + (minItem.label||'1 Day') + ' access';
      }

      renderItems(currentProduct.prices || []);
    })
    .catch(() => { window.location.href = 'index.php'; });

  // ── BUY BUTTON ──
  document.getElementById('buyNowBtn').onclick = async function() {
    if (IS_MAINTENANCE) return;
    if (!selectedItem) { alert('Pilih paket terlebih dahulu!'); return; }
    const name  = document.getElementById('customer-name').value.trim() || 'Guest';
    const phone = document.getElementById('customer-phone').value.trim() || '';
    this.disabled = true;
    this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
    try {
      const r = await fetch('api.php?action=checkout', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          product_id: currentProduct.id, item_index: selectedItemIndex,
          name, phone, customer_name: name, customer_phone: phone,
          amount: getEffPrice(selectedItem),
          payment_method: selectedPaymentMethod,
          ...(function(){ try { const u=JSON.parse(sessionStorage.getItem('g_user')||'null'); return u?{google_sub:u.sub,google_email:u.email,google_credential:u.credential}:{}; } catch(e){return{};} })()
        })
      });
      const d = await r.json();
      if (d.success) {
        localStorage.setItem('active_order_id', d.order_id);
        localStorage.setItem('active_order_method', selectedPaymentMethod);
        localStorage.setItem('active_order_amount', getEffPrice(selectedItem));
        if (selectedPaymentMethod === 'binance') {
          showBinanceModal(d.order_id, d.checkout_url, d.usdt_amount, getEffPrice(selectedItem));
        } else {
          showPaymentModal(d.order_id, d.qr_image_url || d.qr_url, getEffPrice(selectedItem));
        }
        pollStatus(d.order_id);
      } else { alert(d.message || 'Gagal membuat order'); }
    } catch(e) { alert('Terjadi kesalahan. Coba lagi.'); }
    this.disabled = false;
    this.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg> Continue to Checkout';
  };

  // ── MODAL HELPERS ──
  function showPaymentModal(orderId, qrUrl, amount) {
    document.getElementById('paymentModal').style.display = 'flex';
    document.getElementById('binanceModal').style.display = 'none';
    updateModalInfo(orderId, amount);
    startTimer(3600, 'paymentTimer');
    const qr = document.getElementById('qrImage');
    const qrTxt = document.getElementById('qrLoadingText');
    if (qrUrl) {
      qr.src = qrUrl; qr.style.display = 'block';
      qr.onload = () => { if (qrTxt) qrTxt.style.display = 'none'; };
    }
  }

  function showBinanceModal(orderId, checkoutUrl, usdtAmount, idrAmount) {
    document.getElementById('paymentModal').style.display = 'none';
    document.getElementById('binanceModal').style.display = 'flex';
    document.getElementById('bnbOrderId').textContent = orderId;
    document.getElementById('bnbIdrAmount').textContent = formatRp(idrAmount);
    document.getElementById('bnbUsdtAmount').textContent = usdtAmount ? usdtAmount + ' USDT' : '...';
    document.getElementById('bnbDate').textContent = new Date().toLocaleString('id-ID');
    const bnbBadge = document.getElementById('bnbStatusBadge');
    bnbBadge.textContent = 'PENDING'; bnbBadge.style.cssText = 'background:rgba(234,179,8,.2);color:#EAB308;border:1px solid #EAB308';
    document.getElementById('bnbLicenseSection').style.display = 'none';
    document.getElementById('bnbCancelBtn').style.display = 'flex';
    document.getElementById('bnbCloseBtn').style.display = 'none';
    document.getElementById('bnbCopyBtn').style.display = 'none';
    if (checkoutUrl) { document.getElementById('bnbPayBtn').href = checkoutUrl; document.getElementById('bnbPayBtn').style.display = 'flex'; }
    startTimer(3600, 'paymentTimer2');
  }

  function updateModalInfo(orderId, amount) {
    document.getElementById('modalProductName').textContent = currentProduct ? currentProduct.name : '—';
    document.getElementById('modalItemLabel').textContent = selectedItem ? selectedItem.label : '—';
    document.getElementById('paymentAmountModal').textContent = formatRp(amount);
    document.getElementById('transactionId').textContent = orderId;
    document.getElementById('transactionDate').textContent = new Date().toLocaleString('id-ID');
    const badge = document.getElementById('statusBadgeElement');
    badge.textContent = 'PENDING'; badge.style.cssText = 'background:rgba(234,179,8,.2);color:#EAB308;border:1px solid #EAB308';
  }

  function startTimer(duration, elId) {
    if (paymentTimerInterval) clearInterval(paymentTimerInterval);
    let t = duration;
    const el = document.getElementById(elId || 'paymentTimer');
    paymentTimerInterval = setInterval(() => {
      let m=parseInt(t/60), s=parseInt(t%60);
      if (el) el.textContent = `${m<10?'0'+m:m}:${s<10?'0'+s:s}`;
      if (--t < 0) { clearInterval(paymentTimerInterval); alert('Waktu pembayaran habis!'); cancelPayment(true); }
    }, 1000);
  }

  function pollStatus(orderId) {
    if (statusCheckInterval) clearInterval(statusCheckInterval);
    statusCheckInterval = setInterval(async () => {
      try {
        const r = await fetch(`api.php?action=check_status&order_id=${orderId}&t=${Date.now()}`, { cache: 'no-store' });
        const d = await r.json();
        if (['completed','paid','success'].includes(d.status)) {
          clearInterval(statusCheckInterval); clearInterval(paymentTimerInterval);
          showCompletedUI(d.product_content);
        }
      } catch(e) {}
    }, 3000);
  }

  function showCompletedUI(licenseKey) {
    isPaymentCompleted = true;
    const isBinance = localStorage.getItem('active_order_method') === 'binance';
    if (isBinance) {
      const bnbBadge = document.getElementById('bnbStatusBadge');
      if (bnbBadge) { bnbBadge.textContent='COMPLETED'; bnbBadge.style.cssText='background:rgba(34,197,94,.15);color:#22C55E;border:1px solid #22C55E'; }
      document.getElementById('bnbPayBtn').style.display = 'none';
      document.getElementById('bnbCancelBtn').style.display = 'none';
      document.getElementById('bnbCloseBtn').style.display = 'flex';
      document.getElementById('bnbLicenseSection').style.display = 'block';
      const bnbLic = document.getElementById('bnbLicenseKey');
      bnbLic.textContent = licenseKey;
      const bnbCopy = document.getElementById('bnbCopyBtn');
      if (licenseKey.includes('KOSONG')||licenseKey.includes('HUBUNGI')||licenseKey.includes('HABIS')) {
        bnbLic.style.color = '#ef4444'; bnbCopy.style.display = 'none';
      } else { bnbCopy.style.display = 'flex'; }
    } else {
      document.getElementById('paymentHeaderStatus').innerHTML = `
        <div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.4);color:#fff;padding:18px;border-radius:14px;margin-bottom:14px;display:flex;flex-direction:column;align-items:center;gap:8px">
          <i class="fas fa-check-circle" style="font-size:2.2rem;color:#22C55E"></i>
          <h3 style="font-size:16px;margin:0;font-weight:800;color:#22C55E">PEMBAYARAN BERHASIL</h3>
        </div>`;
      document.getElementById('qrImage').style.display = 'none';
      const lt = document.getElementById('qrLoadingText'); if(lt) lt.style.display='none';
      const badge = document.getElementById('statusBadgeElement');
      if (badge) { badge.textContent='COMPLETED'; badge.style.cssText='background:rgba(34,197,94,.15);color:#22C55E;border:1px solid #22C55E'; }
      const licBox = document.getElementById('licenseKeyInput');
      licBox.textContent = licenseKey; licBox.style.display = 'block';
      const copyBtn = document.getElementById('copyLicenseBtn');
      const dlContainer = document.getElementById('apkDownloadContainer');
      const dlLink = document.getElementById('apkDownloadLink');
      const finalDlUrl = getProductDownloadUrl();
      if (dlLink) dlLink.href = finalDlUrl;
      if (licenseKey.includes('KOSONG')||licenseKey.includes('HUBUNGI')||licenseKey.includes('HABIS')) {
        licBox.style.color = '#ef4444'; copyBtn.style.display='none'; if(dlContainer) dlContainer.style.display='none';
      } else {
        copyBtn.style.display = 'flex';
        if (finalDlUrl && dlContainer) dlContainer.style.display = 'block';
      }
      document.getElementById('cancelPaymentBtn').style.display = 'none';
      document.getElementById('closePaymentBtn').style.display = 'flex';
    }
    if (window.navigator.vibrate) window.navigator.vibrate([100,50,100]);
  }

  function cancelPayment(force=false) {
    if (!isPaymentCompleted) {
      if (force || confirm('Yakin ingin membatalkan?')) {
        clearInterval(paymentTimerInterval); clearInterval(statusCheckInterval);
        document.getElementById('paymentModal').style.display = 'none';
        document.getElementById('binanceModal').style.display = 'none';
        localStorage.removeItem('active_order_id'); localStorage.removeItem('active_order_amount'); localStorage.removeItem('active_order_method');
      }
    } else closeSuccessModal();
  }

  function closeSuccessModal() {
    localStorage.removeItem('active_order_id'); localStorage.removeItem('active_order_amount'); localStorage.removeItem('active_order_method');
    document.getElementById('paymentModal').style.display = 'none';
    document.getElementById('binanceModal').style.display = 'none';
    window.location.href = 'index.php';
  }

  // ── COPY ──
  function copyLicenseKey() {
    const text = document.getElementById('licenseKeyInput').innerText;
    const btn  = document.getElementById('copyLicenseBtn');
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(()=>showCopied(btn)).catch(()=>fallbackCopy(text,btn));
    } else fallbackCopy(text, btn);
  }
  function copyBnbLicense() {
    const text = document.getElementById('bnbLicenseKey').innerText;
    const btn  = document.getElementById('bnbCopyBtn');
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(()=>showCopied(btn)).catch(()=>fallbackCopy(text,btn));
    } else fallbackCopy(text, btn);
  }
  function fallbackCopy(text, btn) {
    const ta = document.createElement('textarea'); ta.value=text; ta.style.cssText='position:fixed;left:-9999px';
    document.body.appendChild(ta); ta.focus(); ta.select();
    try { document.execCommand('copy'); showCopied(btn); } catch(e){}
    document.body.removeChild(ta);
  }
  function showCopied(btn) {
    const orig = btn.innerHTML; btn.innerHTML = '<i class="fas fa-check"></i> Tersalin!'; btn.style.background='var(--green)';
    setTimeout(() => { btn.innerHTML=orig; btn.style.background=''; }, 2000);
  }

  // ── CHECK SAVED SESSION ──
  function checkSavedSession() {
    const savedId     = localStorage.getItem('active_order_id');
    const savedAmt    = localStorage.getItem('active_order_amount');
    const savedMethod = localStorage.getItem('active_order_method') || 'qris';
    if (!savedId) return;
    fetch(`api.php?action=check_status&order_id=${savedId}&t=${Date.now()}`, { cache:'no-store' })
      .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(d => {
        if (['completed','paid','success'].includes(d.status)) {
          if (savedMethod === 'binance') {
            document.getElementById('binanceModal').style.display='flex';
            document.getElementById('bnbOrderId').textContent = savedId;
            document.getElementById('bnbDate').textContent = new Date().toLocaleString('id-ID');
            if (savedAmt) document.getElementById('bnbIdrAmount').textContent = formatRp(savedAmt);
          } else {
            document.getElementById('paymentModal').style.display='flex';
            if (savedAmt) updateModalInfo(savedId, savedAmt);
          }
          showCompletedUI(d.product_content);
        } else if (d.status==='pending') {
          if (savedMethod === 'binance') {
            document.getElementById('binanceModal').style.display='flex';
            document.getElementById('bnbOrderId').textContent = savedId;
            document.getElementById('bnbDate').textContent = new Date().toLocaleString('id-ID');
            if (savedAmt) document.getElementById('bnbIdrAmount').textContent = formatRp(savedAmt);
            const bnbBadge = document.getElementById('bnbStatusBadge');
            if (bnbBadge) { bnbBadge.textContent='PENDING'; bnbBadge.style.cssText='background:rgba(234,179,8,.2);color:#EAB308;border:1px solid #EAB308'; }
          } else {
            document.getElementById('paymentModal').style.display='flex';
            const lt = document.getElementById('qrLoadingText');
            if (lt) lt.textContent = 'Lanjutkan pembayaran...';
            if (savedAmt) updateModalInfo(savedId, savedAmt);
          }
          pollStatus(savedId);
        }
      }).catch(()=>{});
  }
  setTimeout(checkSavedSession, 2000);
</script>
</body>
</html>