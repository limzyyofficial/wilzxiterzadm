<?php
// Simple session-based auth
session_start();
$DATA_DIR = __DIR__ . '/data/';
if (!is_dir($DATA_DIR)) mkdir($DATA_DIR, 0755, true);

function readJson($file) {
    $path = $GLOBALS['DATA_DIR'] . $file;
    if (!file_exists($path)) return [];
    return json_decode(file_get_contents($path), true) ?? [];
}
function writeJson($file, $data) {
    file_put_contents($GLOBALS['DATA_DIR'] . $file, json_encode($data, JSON_PRETTY_PRINT));
}

// Default credentials
$creds = readJson('creds.json');
if (empty($creds)) { $creds = ['user' => 'Admin', 'pass' => 'admin123']; writeJson('creds.json', $creds); }

// Login handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_login'])) {
    if ($_POST['username'] === $creds['user'] && $_POST['password'] === $creds['pass']) {
        $_SESSION['wxvn_admin'] = true;
    } else {
        $loginError = 'Username atau password salah!';
    }
}
if (isset($_POST['do_logout'])) { session_destroy(); header('Location: admin.php'); exit; }

$isLoggedIn = !empty($_SESSION['wxvn_admin']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Admin Panel · WXVN Store</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#07080f;--bg2:#0d0f1c;--card:#0f1221;--card2:#141728;
  --border:rgba(255,255,255,.07);--border2:rgba(255,255,255,.14);
  --text:#e8eaf6;--muted:#5a6080;--dim:#2e3350;
  --sp:#1ed760;--sp2:#17b34e;
  --gold:#f5c842;--err:#ff4757;--warn:#ffa502;--wa:#25d366;
  --r:12px;--pill:99px;--t:all .28s cubic-bezier(.4,0,.2,1);
  --nav-h:64px;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
::-webkit-scrollbar{width:3px}::-webkit-scrollbar-track{background:transparent}::-webkit-scrollbar-thumb{background:var(--dim);border-radius:99px}
body{background:var(--bg);font-family:'DM Sans',sans-serif;color:var(--text);min-height:100vh}
.mesh{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 500px 350px at 20% 10%,rgba(30,215,96,.03),transparent 60%),radial-gradient(ellipse 400px 350px at 80% 90%,rgba(245,200,66,.03),transparent 60%)}

/* ── LOGIN ── */
#loginWrap{position:fixed;inset:0;z-index:999;display:flex;align-items:center;justify-content:center;padding:1.2rem;background:var(--bg)}
.login-card{width:100%;max-width:360px;background:var(--card);border:1px solid var(--border2);border-radius:18px;padding:1.8rem 1.6rem;position:relative;overflow:hidden}
.login-stripe{position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--sp),var(--gold))}
.login-brand{text-align:center;margin-bottom:1.6rem}
.login-ico{width:56px;height:56px;border-radius:16px;margin:0 auto .65rem;background:rgba(30,215,96,.1);border:1px solid rgba(30,215,96,.2);display:grid;place-items:center;font-size:1.5rem}
.login-title{font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800}
.login-sub{font-size:.72rem;color:var(--muted);margin-top:.2rem}
.field{margin-bottom:.8rem}
.flabel{font-family:'JetBrains Mono',monospace;font-size:.6rem;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);margin-bottom:.35rem;display:block}
.fwrap{position:relative}
.fico{position:absolute;left:.82rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.82rem;pointer-events:none}
.finp{width:100%;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;padding:.65rem .85rem .65rem 2.3rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.9rem;outline:none;transition:var(--t)}
.finp:focus{border-color:var(--sp);box-shadow:0 0 0 3px rgba(30,215,96,.09)}
.finp::placeholder{color:var(--dim)}
.pw-eye{position:absolute;right:.8rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;font-size:.85rem}
.err-alert{background:rgba(255,71,87,.08);border:1px solid rgba(255,71,87,.2);border-radius:9px;padding:.5rem .75rem;font-size:.76rem;color:var(--err);margin-bottom:.8rem;display:<?= !empty($loginError)?'block':'none' ?>}
.submit-btn{width:100%;padding:.8rem;border-radius:var(--pill);background:linear-gradient(135deg,var(--sp),#38f9a0);border:none;font-family:'Syne',sans-serif;font-weight:800;font-size:.92rem;color:#000;cursor:pointer;transition:var(--t)}
.submit-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(30,215,96,.3)}
.login-hint{text-align:center;font-size:.65rem;color:var(--dim);font-family:'JetBrains Mono',monospace;margin-top:.8rem}

/* ── ADMIN LAYOUT ── */
#adminWrap{display:<?= $isLoggedIn?'flex':'none' ?>;flex-direction:column;min-height:100vh;position:relative;z-index:1}
.topbar{position:sticky;top:0;z-index:100;background:rgba(7,8,15,.95);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);padding:.75rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.6rem}
.tb-brand{font-family:'Syne',sans-serif;font-size:1rem;font-weight:800}
.tb-brand em{color:var(--sp);font-style:normal}
.tb-pill{display:inline-flex;align-items:center;gap:.35rem;background:rgba(30,215,96,.08);border:1px solid rgba(30,215,96,.18);padding:.22rem .65rem;border-radius:99px;font-family:'JetBrains Mono',monospace;font-size:.58rem;color:var(--sp)}
.ldot{width:5px;height:5px;background:var(--sp);border-radius:50%;animation:blink 1.4s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.15}}
.tb-icon-btn{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.04);border:1px solid var(--border);display:grid;place-items:center;cursor:pointer;color:var(--muted);font-size:.82rem;transition:var(--t)}
.tb-icon-btn:hover{background:rgba(255,255,255,.1);color:var(--text)}
.tb-icon-btn.danger:hover{background:rgba(255,71,87,.15);color:var(--err);border-color:rgba(255,71,87,.3)}
.page-scroll{flex:1;overflow-y:auto;padding:1rem 1rem calc(var(--nav-h) + 1.5rem)}
.bottom-nav{position:fixed;bottom:0;left:0;right:0;z-index:200;height:var(--nav-h);background:rgba(7,8,15,.97);backdrop-filter:blur(24px);border-top:1px solid var(--border);display:flex;align-items:stretch}
.bn-item{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.22rem;cursor:pointer;transition:var(--t);position:relative;border:none;background:none;color:var(--muted);padding:.3rem 0}
.bn-item::before{content:'';position:absolute;top:0;left:20%;right:20%;height:2px;border-radius:99px;background:var(--sp);opacity:0;transition:opacity .2s}
.bn-item.active{color:var(--sp)}.bn-item.active::before{opacity:1}
.bn-lbl{font-size:.58rem;font-weight:600;letter-spacing:.5px;text-transform:uppercase;font-family:'JetBrains Mono',monospace}
.bn-badge{position:absolute;top:.22rem;right:calc(50% - 14px);min-width:16px;height:16px;padding:0 4px;background:var(--err);border-radius:99px;font-size:.55rem;font-weight:700;color:#fff;display:none;place-items:center;border:2px solid var(--bg);font-family:'JetBrains Mono',monospace}
.bn-badge.on{display:grid}

/* ── CARDS & SECTIONS ── */
.stats-row{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;margin-bottom:1rem}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1rem;position:relative;overflow:hidden}
.stat-glow{position:absolute;top:-20px;right:-20px;width:80px;height:80px;border-radius:50%;opacity:.12;filter:blur(20px)}
.stat-ico{width:34px;height:34px;border-radius:9px;display:grid;place-items:center;font-size:.88rem;margin-bottom:.6rem}
.stat-num{font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;line-height:1}
.stat-lbl{font-size:.68rem;color:var(--muted);margin-top:.22rem}
.rev-hero{background:linear-gradient(135deg,rgba(30,215,96,.08),rgba(245,200,66,.06));border:1px solid rgba(30,215,96,.15);border-radius:14px;padding:1.2rem;margin-bottom:.85rem;text-align:center}
.rev-label{font-family:'JetBrains Mono',monospace;font-size:.58rem;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:.4rem}
.rev-amount{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:var(--sp)}
.sec{background:var(--card);border:1px solid var(--border);border-radius:14px;margin-bottom:.85rem;overflow:hidden}
.sec-top{display:flex;align-items:center;justify-content:space-between;padding:.85rem 1rem;border-bottom:1px solid var(--border)}
.sec-title{font-family:'Syne',sans-serif;font-size:.88rem;font-weight:800;display:flex;align-items:center;gap:.45rem}
.sec-body{padding:.85rem 1rem}
.sec-action{display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;font-weight:600;color:var(--muted);background:rgba(255,255,255,.04);border:1px solid var(--border);padding:.28rem .72rem;border-radius:99px;cursor:pointer;transition:var(--t)}
.sec-action:hover{color:var(--text);background:rgba(255,255,255,.09)}

/* ── ORDER CARDS ── */
.order-list{display:flex;flex-direction:column;gap:.6rem}
.o-card{background:rgba(255,255,255,.025);border:1px solid var(--border);border-radius:var(--r);padding:.82rem .9rem}
.o-id{font-family:'JetBrains Mono',monospace;font-size:.7rem;color:var(--sp)}
.o-name{font-family:'Syne',sans-serif;font-weight:700;font-size:.88rem;margin:.12rem 0}
.o-contact{font-size:.72rem;color:var(--muted)}
.o-gameid{font-size:.72rem;color:var(--gold);margin-top:.1rem}
.o-meta{display:flex;align-items:center;justify-content:space-between;margin:.5rem 0}
.o-items{font-size:.72rem;color:var(--muted);flex:1}
.o-total{font-family:'Syne',sans-serif;font-weight:800;font-size:.92rem;color:var(--sp)}
.o-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.4rem}
.o-date{font-family:'JetBrains Mono',monospace;font-size:.62rem;color:var(--dim)}
.o-actions{display:flex;gap:.35rem;flex-wrap:wrap}
.badge{display:inline-flex;align-items:center;gap:.25rem;font-family:'JetBrains Mono',monospace;font-size:.6rem;padding:.2rem .58rem;border-radius:99px}
.b-pending{background:rgba(255,165,2,.12);color:var(--warn);border:1px solid rgba(255,165,2,.25)}
.b-confirmed{background:rgba(30,215,96,.12);color:var(--sp);border:1px solid rgba(30,215,96,.25)}
.b-rejected{background:rgba(255,71,87,.12);color:var(--err);border:1px solid rgba(255,71,87,.25)}
.b-delivered{background:rgba(245,200,66,.12);color:var(--gold);border:1px solid rgba(245,200,66,.25)}
.abtn{display:inline-flex;align-items:center;gap:.28rem;padding:.3rem .72rem;border-radius:99px;border:1px solid var(--border);background:rgba(255,255,255,.04);color:var(--muted);font-size:.7rem;font-weight:600;cursor:pointer;transition:var(--t);font-family:'DM Sans',sans-serif}
.abtn.confirm{background:rgba(30,215,96,.1);border-color:rgba(30,215,96,.25);color:var(--sp)}
.abtn.deliver{background:rgba(245,200,66,.1);border-color:rgba(245,200,66,.25);color:var(--gold)}
.abtn.reject{background:rgba(255,71,87,.08);border-color:rgba(255,71,87,.2);color:var(--err)}
.abtn.wa{background:rgba(37,211,102,.1);border-color:rgba(37,211,102,.3);color:var(--wa)}
.abtn.del{color:var(--err)}
.abtn:active{transform:scale(.96)}

/* ── FILTERS & SEARCH ── */
.filter-scroll{display:flex;gap:.4rem;overflow-x:auto;padding-bottom:.3rem;margin-bottom:.85rem;scrollbar-width:none}
.filter-scroll::-webkit-scrollbar{display:none}
.fpill{flex-shrink:0;padding:.35rem .85rem;border-radius:99px;border:1px solid var(--border);background:rgba(255,255,255,.03);color:var(--muted);font-size:.74rem;font-weight:600;cursor:pointer;transition:var(--t)}
.fpill.on{background:var(--text);color:#000;border-color:var(--text)}
.search-bar{width:100%;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;padding:.58rem .82rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.88rem;outline:none;transition:var(--t);margin-bottom:.85rem}
.search-bar:focus{border-color:var(--sp)}
.search-bar::placeholder{color:var(--dim)}

/* ── SETTINGS ── */
.setting-block{background:rgba(255,255,255,.025);border:1px solid var(--border);border-radius:var(--r);padding:.9rem;margin-bottom:.7rem}
.setting-title{font-family:'Syne',sans-serif;font-weight:800;font-size:.9rem;margin-bottom:.18rem}
.setting-sub{font-size:.72rem;color:var(--muted);margin-bottom:.9rem}
.danger-btn{width:100%;padding:.65rem;border-radius:10px;background:rgba(255,71,87,.07);border:1px solid rgba(255,71,87,.18);color:var(--err);font-family:'Syne',sans-serif;font-weight:700;font-size:.82rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.38rem;margin-bottom:.45rem;transition:var(--t)}
.danger-btn:active{background:rgba(255,71,87,.15)}
.green-btn{width:100%;padding:.72rem;border-radius:var(--pill);background:var(--sp);border:none;font-family:'Syne',sans-serif;font-weight:800;font-size:.88rem;color:#000;cursor:pointer;transition:var(--t);display:flex;align-items:center;justify-content:center;gap:.4rem;margin-top:.65rem}
.green-btn:hover{background:var(--sp2);transform:translateY(-1px)}
.info-row{display:flex;justify-content:space-between;align-items:center;padding:.35rem 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:.78rem}
.info-row:last-child{border-bottom:none}
.info-key{color:var(--muted);font-family:'JetBrains Mono',monospace;font-size:.7rem}

/* ── MODAL ── */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);backdrop-filter:blur(18px);z-index:3000;align-items:flex-end;justify-content:center}
.modal-bg.on{display:flex;animation:fadeIn .2s ease}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.modal-sheet{width:100%;max-width:520px;max-height:92vh;overflow-y:auto;background:var(--card);border:1px solid var(--border2);border-radius:22px 22px 0 0;padding:1.5rem 1.3rem;position:relative;animation:sheetUp .32s cubic-bezier(.34,1.2,.64,1)}
@keyframes sheetUp{from{transform:translateY(60px);opacity:0}to{transform:translateY(0);opacity:1}}
.sheet-handle{width:36px;height:3.5px;background:var(--dim);border-radius:99px;margin:0 auto 1.2rem}
.modal-stripe{position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--sp),var(--gold));border-radius:22px 22px 0 0}
.modal-close{position:absolute;top:.9rem;right:.9rem;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.06);border:1px solid var(--border);display:grid;place-items:center;cursor:pointer;color:var(--muted);font-size:.75rem;transition:var(--t)}
.modal-close:hover{background:var(--err);color:#fff}
.modal-title{font-family:'Syne',sans-serif;font-size:1.05rem;font-weight:800;margin-bottom:.2rem}
.modal-sub{font-size:.74rem;color:var(--muted);margin-bottom:1.1rem}
.modal-btn{width:100%;padding:.78rem;border-radius:var(--pill);border:none;font-family:'Syne',sans-serif;font-weight:800;font-size:.9rem;cursor:pointer;transition:var(--t);display:flex;align-items:center;justify-content:center;gap:.4rem;margin-top:.45rem}
.modal-btn.primary{background:var(--sp);color:#000}
.modal-btn.danger{background:rgba(255,71,87,.12);color:var(--err);border:1px solid rgba(255,71,87,.22)}
.modal-btn.secondary{background:rgba(255,255,255,.06);color:var(--muted);border:1px solid var(--border)}
.modal-btn.gold{background:var(--gold);color:#000}
.modal-btn.wa{background:var(--wa);color:#000}
.osum-box{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:.8rem .95rem;margin-bottom:.9rem}
.osum-row{display:flex;justify-content:space-between;font-size:.8rem;margin:.22rem 0}
.osum-row .k{color:var(--muted)}.osum-row .v{font-weight:600}
.osum-total{border-top:1px solid var(--border);padding-top:.45rem;margin-top:.35rem}
.osum-total .v{color:var(--sp);font-family:'JetBrains Mono',monospace}
.finp-modal{width:100%;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:9px;padding:.58rem .75rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:.85rem;outline:none;transition:var(--t);margin-bottom:.55rem}
.finp-modal:focus{border-color:var(--sp)}
.finp-modal::placeholder{color:var(--dim)}
textarea.finp-modal{resize:vertical;min-height:80px;font-family:'JetBrains Mono',monospace;font-size:.75rem}
.flabel-modal{font-family:'JetBrains Mono',monospace;font-size:.6rem;letter-spacing:1px;text-transform:uppercase;color:var(--muted);margin-bottom:.3rem;display:block}
.delivered-item{background:rgba(30,215,96,.05);border:1px solid rgba(30,215,96,.2);border-radius:8px;padding:.5rem .7rem;margin-bottom:.4rem}
.d-label{font-size:.62rem;color:var(--muted);margin-bottom:.2rem}
.d-val{font-family:'JetBrains Mono',monospace;font-size:.76rem;color:var(--sp);word-break:break-all}
.empty{text-align:center;padding:2.2rem 1rem}
.empty-ico{font-size:2rem;opacity:.3;margin-bottom:.55rem}
.empty-t{font-family:'Syne',sans-serif;font-weight:700;font-size:.88rem;margin-bottom:.25rem}
.empty-s{font-size:.76rem;color:var(--muted)}
#toasts{position:fixed;bottom:calc(var(--nav-h) + .7rem);left:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:.35rem;align-items:center;pointer-events:none}
.toast{background:var(--card);border:1px solid var(--border);border-left:3px solid var(--sp);padding:.55rem .9rem;border-radius:10px;font-size:.76rem;font-family:'JetBrains Mono',monospace;display:flex;align-items:center;gap:.45rem;max-width:340px;width:100%;color:var(--text);animation:toastIn .3s cubic-bezier(.34,1.4,.64,1);pointer-events:all}
.toast.err{border-left-color:var(--err)}.toast.warn{border-left-color:var(--warn)}.toast.wa{border-left-color:var(--wa)}
@keyframes toastIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>
<div class="mesh"></div>

<?php if (!$isLoggedIn): ?>
<!-- ── LOGIN PAGE ── -->
<div id="loginWrap">
  <div class="login-card">
    <div class="login-stripe"></div>
    <div class="login-brand">
      <div class="login-ico">🎮</div>
      <div class="login-title">WXVN Store</div>
      <div class="login-sub">Admin Panel · Restricted Access</div>
    </div>
    <?php if (!empty($loginError)): ?>
    <div class="err-alert"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($loginError) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="field">
        <label class="flabel">Username</label>
        <div class="fwrap">
          <i class="fas fa-user fico"></i>
          <input class="finp" type="text" name="username" placeholder="Masukkan username..." required autocomplete="username">
        </div>
      </div>
      <div class="field">
        <label class="flabel">Password</label>
        <div class="fwrap">
          <i class="fas fa-lock fico"></i>
          <input class="finp" type="password" name="password" id="pwInp" placeholder="Masukkan password..." required autocomplete="current-password" style="padding-right:2.5rem">
          <button type="button" class="pw-eye" onclick="var p=document.getElementById('pwInp');p.type=p.type==='password'?'text':'password'"><i class="fas fa-eye"></i></button>
        </div>
      </div>
      <button type="submit" name="do_login" class="submit-btn"><i class="fas fa-sign-in-alt"></i>&nbsp;Masuk ke Panel</button>
    </form>
    <div class="login-hint">Default: Admin / admin123</div>
  </div>
</div>
<?php else: ?>
<!-- ── ADMIN PANEL ── -->
<div id="adminWrap">
  <div class="topbar">
    <div class="tb-brand">WXVN<em>ADM</em></div>
    <div id="tbPageTitle" style="font-family:'Syne',sans-serif;font-size:.88rem;font-weight:700">Dashboard</div>
    <div style="display:flex;align-items:center;gap:.45rem">
      <div class="tb-pill"><div class="ldot"></div>LIVE</div>
      <div class="tb-icon-btn" onclick="window.open('index.php','_blank')" title="Buka Toko"><i class="fas fa-store"></i></div>
      <form method="POST" style="display:inline">
        <button type="submit" name="do_logout" class="tb-icon-btn danger" title="Logout"><i class="fas fa-sign-out-alt"></i></button>
      </form>
    </div>
  </div>

  <div class="page-scroll" id="pageScroll"></div>

  <nav class="bottom-nav">
    <button class="bn-item active" data-p="dashboard" onclick="goPage('dashboard')">
      <span style="font-size:1.1rem">📊</span><span class="bn-lbl">Dashboard</span>
    </button>
    <button class="bn-item" data-p="orders" onclick="goPage('orders')">
      <span style="font-size:1.1rem">🛍️</span><span class="bn-lbl">Order</span>
      <span class="bn-badge" id="pendBadge"></span>
    </button>
    <button class="bn-item" data-p="settings" onclick="goPage('settings')">
      <span style="font-size:1.1rem">⚙️</span><span class="bn-lbl">Settings</span>
    </button>
  </nav>
</div>

<!-- MODAL -->
<div class="modal-bg" id="modal" onclick="if(event.target===this)closeModal()">
  <div class="modal-sheet">
    <div class="modal-stripe"></div>
    <div class="sheet-handle"></div>
    <div class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></div>
    <div id="modalBody"></div>
  </div>
</div>
<div id="toasts"></div>

<script>
var API = 'api.php';
async function api(action, body) {
  var r = await fetch(API + '?action=' + action, body ? { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body) } : {});
  return r.json();
}

var curPage = 'dashboard', ordFilter = 'all', ordSearch = '';

function fmt(n) { return 'Rp' + Number(n).toLocaleString('id-ID'); }
function esc(s) { var d=document.createElement('div');d.textContent=String(s);return d.innerHTML; }

/* ── NAV ── */
function goPage(p) {
  curPage = p;
  document.querySelectorAll('.bn-item').forEach(function(b){ b.classList.toggle('active', b.dataset.p === p); });
  document.getElementById('tbPageTitle').textContent = { dashboard:'Dashboard', orders:'Manajemen Order', settings:'Pengaturan' }[p];
  document.getElementById('pageScroll').scrollTop = 0;
  if (p === 'dashboard') renderDashboard();
  else if (p === 'orders') renderOrders();
  else if (p === 'settings') renderSettings();
}

async function updateBadge() {
  var orders = await api('get_orders');
  var n = orders.filter(function(o){ return o.status === 'pending'; }).length;
  var b = document.getElementById('pendBadge');
  if (n > 0) { b.classList.add('on'); b.textContent = n; } else { b.classList.remove('on'); }
}

/* ── DASHBOARD ── */
async function renderDashboard() {
  var orders = await api('get_orders');
  var pending = orders.filter(function(o){ return o.status === 'pending'; }).length;
  var delivered = orders.filter(function(o){ return o.status === 'delivered'; }).length;
  var revenue = orders.filter(function(o){ return o.status==='confirmed'||o.status==='delivered'; }).reduce(function(s,o){ return s+o.total; }, 0);

  var html = '<div class="stats-row">'
    + statCard('🛍️', orders.length, 'Total Order', 'rgba(30,215,96,.1)', 'var(--sp)', 'var(--sp)')
    + statCard('⏳', pending, 'Pending', 'rgba(255,165,2,.1)', 'var(--warn)', 'var(--warn)', pending>0?'⚠️ Perlu konfirmasi':'')
    + statCard('🎉', delivered, 'Terkirim', 'rgba(245,200,66,.1)', 'var(--gold)', 'var(--text)')
    + statCard('❌', orders.filter(function(o){return o.status==='rejected';}).length, 'Ditolak', 'rgba(255,71,87,.1)', 'var(--err)', 'var(--text)')
    + '</div>';

  html += '<div class="rev-hero"><div class="rev-label"><i class="fas fa-coins"></i>&nbsp;Revenue Terkonfirmasi</div>'
    + '<div class="rev-amount">'+fmt(revenue)+'</div>'
    + '<div style="font-size:.7rem;color:var(--muted);margin-top:.3rem">'+orders.filter(function(o){return o.status==='confirmed'||o.status==='delivered';}).length+' transaksi</div></div>';

  var recentPending = orders.filter(function(o){ return o.status==='pending'; }).slice(0,5);
  if (recentPending.length) {
    html += '<div class="sec"><div class="sec-top"><div class="sec-title">⚠️ Perlu Konfirmasi</div>'
      + '<span class="sec-action" onclick="goPage(\'orders\')">Semua <i class="fas fa-arrow-right"></i></span></div>'
      + '<div class="sec-body"><div class="order-list">';
    recentPending.forEach(function(o){ html += renderOrderCard(o); });
    html += '</div></div></div>';
  }

  var recent = orders.slice(0, 8);
  html += '<div class="sec"><div class="sec-top"><div class="sec-title">📋 Order Terbaru</div>'
    + '<span class="sec-action" onclick="goPage(\'orders\')">Semua <i class="fas fa-arrow-right"></i></span></div>'
    + '<div class="sec-body">';
  if (!recent.length) html += '<div class="empty"><div class="empty-ico">📋</div><div class="empty-t">Belum ada order</div><div class="empty-s">Order dari toko tampil di sini otomatis</div></div>';
  else html += '<div class="order-list">' + recent.map(renderOrderCard).join('') + '</div>';
  html += '</div></div>';

  document.getElementById('pageScroll').innerHTML = html;
}

function statCard(ico, num, lbl, bg, col, numCol, note) {
  return '<div class="stat-card"><div class="stat-glow" style="background:'+col+'"></div>'
    + '<div class="stat-ico" style="background:'+bg+';color:'+col+'">'+ico+'</div>'
    + '<div class="stat-num" style="color:'+numCol+'">'+num+'</div>'
    + '<div class="stat-lbl">'+lbl+'</div>'
    + (note?'<div style="font-size:.62rem;font-family:\'JetBrains Mono\',monospace;color:'+col+';margin-top:.22rem">'+note+'</div>':'')
    + '</div>';
}

/* ── ORDERS ── */
async function renderOrders() {
  var orders = await api('get_orders');
  var pend = orders.filter(function(o){return o.status==='pending';}).length;
  var conf = orders.filter(function(o){return o.status==='confirmed';}).length;
  var delv = orders.filter(function(o){return o.status==='delivered';}).length;
  var rej  = orders.filter(function(o){return o.status==='rejected';}).length;

  var html = '<div class="filter-scroll">'
    + fpill('all','Semua ('+orders.length+')')
    + fpill('pending','Pending ('+pend+')')
    + fpill('confirmed','Konfirmasi ('+conf+')')
    + fpill('delivered','Terkirim ('+delv+')')
    + fpill('rejected','Ditolak ('+rej+')')
    + '</div>';
  html += '<input class="search-bar" placeholder="🔍  Cari nama / Order ID / email..." value="'+esc(ordSearch)+'" oninput="ordSearch=this.value;renderOrders()">';

  var list = orders.filter(function(o) {
    var mf = ordFilter === 'all' || o.status === ordFilter;
    var q = ordSearch.toLowerCase();
    var ms = !q || o.id.toLowerCase().includes(q) || (o.name||'').toLowerCase().includes(q)
          || (o.contact||'').toLowerCase().includes(q) || (o.userEmail||'').toLowerCase().includes(q)
          || (o.gameId||'').toLowerCase().includes(q);
    return mf && ms;
  });

  if (ordFilter === 'pending' && list.length > 0) {
    html += '<button style="width:100%;padding:.72rem;border-radius:99px;background:var(--sp);border:none;font-family:\'Syne\',sans-serif;font-weight:800;font-size:.88rem;color:#000;cursor:pointer;margin-bottom:.85rem;display:flex;align-items:center;justify-content:center;gap:.4rem" onclick="confirmAllPending()"><i class="fas fa-check-double"></i>&nbsp;Konfirmasi Semua Pending ('+list.length+')</button>';
  }

  html += '<div class="order-list">';
  if (!list.length) html += '<div class="empty"><div class="empty-ico">📋</div><div class="empty-t">Tidak ada order</div><div class="empty-s">Coba filter lain</div></div>';
  else list.forEach(function(o){ html += renderOrderCard(o); });
  html += '</div>';
  document.getElementById('pageScroll').innerHTML = html;
}

function fpill(val, lbl) { return '<button class="fpill'+(ordFilter===val?' on':'')+'" onclick="setFilt(\''+val+'\')">'+lbl+'</button>'; }
function setFilt(v) { ordFilter = v; renderOrders(); }

var stInfo = {
  pending:   { cls:'b-pending',   ico:'⏳', lbl:'Pending' },
  confirmed: { cls:'b-confirmed', ico:'✅', lbl:'Dikonfirmasi' },
  delivered: { cls:'b-delivered', ico:'🎉', lbl:'Terkirim' },
  rejected:  { cls:'b-rejected',  ico:'❌', lbl:'Ditolak' }
};

function renderOrderCard(o) {
  var st = stInfo[o.status] || stInfo.pending;
  var acts = '';
  if (o.status === 'pending') {
    acts += '<button class="abtn confirm" onclick="confirmOrder(\''+o.id+'\')"><i class="fas fa-check"></i> Konfirmasi</button>';
    acts += '<button class="abtn reject" onclick="openReject(\''+o.id+'\')"><i class="fas fa-times"></i></button>';
  }
  if (o.status === 'confirmed') {
    acts += '<button class="abtn deliver" onclick="openDeliver(\''+o.id+'\')"><i class="fas fa-paper-plane"></i> Kirim Kode</button>';
  }
  if (o.contact) {
    var phone = o.contact.replace(/\D/g,'');
    if (phone.startsWith('0')) phone = '62' + phone.slice(1);
    var waMsg = 'Halo '+esc(o.name||'')+', update order '+o.id;
    acts += '<button class="abtn wa" onclick="window.open(\'https://wa.me/'+phone+'?text='+encodeURIComponent('Halo '+o.name+', update order '+o.id)+'\',\'_blank\')" title="WA Customer"><i class="fab fa-whatsapp"></i></button>';
  }
  acts += '<button class="abtn" onclick="viewOrder(\''+o.id+'\')"><i class="fas fa-eye"></i></button>';
  acts += '<button class="abtn del" onclick="deleteOrder(\''+o.id+'\')"><i class="fas fa-trash"></i></button>';

  return '<div class="o-card">'
    + '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem">'
    + '<div><div class="o-id">'+esc(o.id)+'</div><div class="o-name">'+esc(o.name||o.userName||'—')+'</div>'
    + '<div class="o-contact">'+esc(o.contact||'—')+(o.userEmail?' · '+esc(o.userEmail):'')+'</div>'
    + (o.gameId?'<div class="o-gameid">🎮 ID: '+esc(o.gameId)+'</div>':'')
    + '</div><span class="badge '+st.cls+'">'+st.ico+' '+st.lbl+'</span></div>'
    + '<div class="o-meta"><div class="o-items">'+esc(o.prodName||'—')+'</div><div class="o-total">'+fmt(o.total||0)+'</div></div>'
    + '<div class="o-footer"><div class="o-date">'+esc(o.date||'—')+'</div><div class="o-actions">'+acts+'</div></div>'
    + '</div>';
}

async function confirmOrder(id) {
  await api('update_order', { id:id, status:'confirmed' });
  updateBadge(); toast('✅ Order '+id+' dikonfirmasi!');
  if (curPage === 'orders') renderOrders(); else renderDashboard();
}

async function confirmAllPending() {
  var orders = await api('get_orders');
  var pendings = orders.filter(function(o){ return o.status === 'pending'; });
  for (var o of pendings) await api('update_order', { id:o.id, status:'confirmed' });
  updateBadge(); toast('✅ '+pendings.length+' order dikonfirmasi!');
  renderOrders();
}

async function deleteOrder(id) {
  if (!confirm('Hapus order '+id+'?')) return;
  await api('delete_order', { id:id });
  updateBadge(); toast('Order dihapus','warn');
  if (curPage === 'orders') renderOrders(); else renderDashboard();
}

function openReject(id) {
  openModal('<div class="modal-title">❌ Tolak Order</div><div class="modal-sub">'+esc(id)+'</div>'
    + '<label class="flabel-modal">Alasan Penolakan</label>'
    + '<input class="finp-modal" id="rejReason" placeholder="Contoh: Bukti transfer tidak valid...">'
    + '<button class="modal-btn danger" onclick="doReject(\''+id+'\')"><i class="fas fa-times"></i>&nbsp;Tolak Order</button>'
    + '<button class="modal-btn secondary" onclick="closeModal()">Batal</button>');
}
async function doReject(id) {
  var r = (document.getElementById('rejReason').value || '').trim() || 'Pembayaran tidak valid';
  await api('update_order', { id:id, status:'rejected', statusNote:r });
  closeModal(); updateBadge(); toast('Order ditolak','warn');
  if (curPage === 'orders') renderOrders(); else renderDashboard();
}

async function openDeliver(id) {
  var orders = await api('get_orders');
  var o = orders.find(function(x){ return x.id === id; }); if (!o) return;
  openModal('<div class="modal-title">📦 Kirim Kode/Voucher</div>'
    + '<div class="modal-sub">'+esc(o.name||o.userName||'—')+' · '+esc(o.prodName)+'</div>'
    + (o.gameId?'<div style="background:rgba(245,200,66,.07);border:1px solid rgba(245,200,66,.2);border-radius:8px;padding:.5rem .75rem;font-size:.8rem;color:var(--gold);margin-bottom:.7rem">🎮 ID Game: <strong>'+esc(o.gameId)+'</strong></div>':'')
    + '<label class="flabel-modal">Kode Voucher / License Key</label>'
    + '<textarea class="finp-modal" id="deliverKey" placeholder="Masukkan kode voucher atau license..."></textarea>'
    + '<label class="flabel-modal">Catatan (opsional)</label>'
    + '<input class="finp-modal" id="deliverNote" placeholder="Instruksi cara pakai...">'
    + '<button class="modal-btn gold" onclick="doDeliver(\''+id+'\')"><i class="fas fa-paper-plane"></i>&nbsp;Kirim & Selesaikan</button>'
    + '<button class="modal-btn secondary" onclick="closeModal()">Batal</button>');
}

async function doDeliver(id) {
  var key = (document.getElementById('deliverKey').value || '').trim();
  var note = (document.getElementById('deliverNote').value || '').trim();
  if (!key) { toast('Masukkan kode voucher!','err'); return; }
  var orders = await api('get_orders');
  var o = orders.find(function(x){ return x.id === id; });
  var delivered = [{ name: o ? o.prodName : id, plan: '', key: key }];
  await api('update_order', { id:id, status:'delivered', delivered:delivered, activationKey:key, statusNote:note });
  closeModal(); updateBadge(); toast('🎉 Kode terkirim!');
  if (curPage === 'orders') renderOrders(); else renderDashboard();
}

async function viewOrder(id) {
  var orders = await api('get_orders');
  var o = orders.find(function(x){ return x.id === id; }); if (!o) return;
  var st = stInfo[o.status] || stInfo.pending;
  var deliveredHtml = '';
  if (o.delivered && o.delivered.length) {
    deliveredHtml = '<div style="margin-top:.7rem">';
    o.delivered.forEach(function(d) {
      deliveredHtml += '<div class="delivered-item"><div class="d-label">'+(d.name||o.prodName)+'</div><div class="d-val">'+esc(d.key)+'</div></div>';
    });
    deliveredHtml += '</div>';
  }
  var phone = (o.contact||'').replace(/\D/g,'');
  if (phone.startsWith('0')) phone = '62' + phone.slice(1);
  openModal('<div class="modal-title">Detail Order</div>'
    + '<div class="modal-sub" style="font-family:\'JetBrains Mono\',monospace;color:var(--sp);font-size:.75rem">'+esc(o.id)+'</div>'
    + '<div class="osum-box">'
    + '<div class="osum-row"><span class="k">Produk</span><span class="v">'+esc(o.prodName||'—')+'</span></div>'
    + (o.gameId?'<div class="osum-row"><span class="k">ID Game</span><span class="v" style="color:var(--gold)">'+esc(o.gameId)+'</span></div>':'')
    + '<div class="osum-row"><span class="k">Nama</span><span class="v">'+esc(o.name||o.userName||'—')+'</span></div>'
    + '<div class="osum-row"><span class="k">Kontak</span><span class="v">'+esc(o.contact||'—')+'</span></div>'
    + '<div class="osum-row"><span class="k">Email</span><span class="v">'+esc(o.userEmail||'—')+'</span></div>'
    + '<div class="osum-row"><span class="k">Metode</span><span class="v">'+esc((o.method||'').toUpperCase())+'</span></div>'
    + '<div class="osum-row"><span class="k">Tanggal</span><span class="v" style="font-size:.75rem">'+esc(o.date||'—')+'</span></div>'
    + '<div class="osum-row osum-total"><span class="k">Total</span><span class="v">'+fmt(o.total||0)+'</span></div>'
    + '</div>'
    + '<div style="text-align:center;margin-bottom:.7rem"><span class="badge '+st.cls+'">'+st.ico+' '+st.lbl+'</span></div>'
    + (o.statusNote&&o.status==='rejected'?'<div style="background:rgba(255,71,87,.06);border:1px solid rgba(255,71,87,.15);border-radius:8px;padding:.5rem .75rem;font-size:.74rem;color:var(--err);margin-bottom:.6rem">Alasan: '+esc(o.statusNote)+'</div>':'')
    + deliveredHtml
    + (phone?'<button class="modal-btn wa" onclick="window.open(\'https://wa.me/'+phone+'\',\'_blank\')" style="margin-top:.8rem"><i class="fab fa-whatsapp"></i>&nbsp;WhatsApp Customer</button>':'')
    + '<button class="modal-btn secondary" onclick="closeModal()">Tutup</button>');
}

/* ── SETTINGS ── */
async function renderSettings() {
  var orders = await api('get_orders');
  var html = '<div class="setting-block">'
    + '<div class="setting-title">🔐 Kredensial Admin</div>'
    + '<div class="setting-sub">Ubah username dan password login admin</div>'
    + '<label class="flabel-modal">Username Baru</label>'
    + '<input class="finp-modal" id="newUser" placeholder="Username...">'
    + '<label class="flabel-modal">Password Baru</label>'
    + '<input class="finp-modal" type="password" id="newPass" placeholder="Kosongkan jika tidak diubah">'
    + '<label class="flabel-modal">Konfirmasi Password</label>'
    + '<input class="finp-modal" type="password" id="confPass" placeholder="Ulangi password baru">'
    + '<button class="green-btn" onclick="saveCreds()"><i class="fas fa-save"></i>&nbsp;Simpan Kredensial</button>'
    + '</div>';

  html += '<div class="setting-block"><div class="setting-title">🗑️ Manajemen Data</div>'
    + '<div class="setting-sub">Hapus atau bersihkan data order</div>'
    + '<button class="danger-btn" onclick="clearDone()"><i class="fas fa-broom"></i>&nbsp;Hapus Order Selesai & Ditolak</button>'
    + '<button class="danger-btn" onclick="clearAll()"><i class="fas fa-times-circle"></i>&nbsp;Hapus SEMUA Order</button>'
    + '</div>';

  html += '<div class="setting-block"><div class="setting-title">ℹ️ Info Sistem</div>'
    + '<div class="info-row"><span class="info-key">Total Order</span><span>'+orders.length+'</span></div>'
    + '<div class="info-row"><span class="info-key">Pending</span><span style="color:var(--warn)">'+orders.filter(function(o){return o.status==='pending';}).length+'</span></div>'
    + '<div class="info-row"><span class="info-key">Terkirim</span><span style="color:var(--sp)">'+orders.filter(function(o){return o.status==='delivered';}).length+'</span></div>'
    + '<div class="info-row"><span class="info-key">Versi Panel</span><span style="color:var(--gold)">v1.0.0</span></div>'
    + '<div class="info-row"><span class="info-key">Link Toko</span><a href="index.php" target="_blank" style="color:var(--sp)">Buka Toko <i class="fas fa-external-link-alt"></i></a></div>'
    + '</div>';

  document.getElementById('pageScroll').innerHTML = html;
}

async function saveCreds() {
  var u = document.getElementById('newUser').value.trim();
  var p = document.getElementById('newPass').value;
  var c = document.getElementById('confPass').value;
  if (!u) { toast('Username tidak boleh kosong!','err'); return; }
  if (p && p !== c) { toast('Konfirmasi password tidak cocok!','err'); return; }
  var creds = {};
  var existing = await api('get_creds');
  creds.user = u; creds.pass = p || existing.pass;
  await api('save_creds', creds);
  toast('✅ Kredensial disimpan! Login ulang diperlukan.');
}

async function clearDone() {
  if (!confirm('Hapus order selesai & ditolak?')) return;
  var orders = await api('get_orders');
  var keep = orders.filter(function(o){ return o.status === 'pending' || o.status === 'confirmed'; });
  await api('save_order', keep); // hack: save_order handles upsert; use direct approach
  // Use raw fetch to overwrite
  await fetch(API + '?action=save_all_orders', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(keep) });
  toast('Selesai','warn'); renderSettings();
}

async function clearAll() {
  if (!confirm('HAPUS SEMUA ORDER? Tidak bisa dibatalkan!')) return;
  await fetch(API + '?action=clear_orders', { method:'POST' });
  toast('Semua order dihapus','err'); renderSettings();
}

/* ── MODAL ── */
function openModal(html) { document.getElementById('modalBody').innerHTML = html; document.getElementById('modal').classList.add('on'); }
function closeModal() { document.getElementById('modal').classList.remove('on'); }
document.addEventListener('keydown', function(e){ if (e.key==='Escape') closeModal(); });

/* ── TOAST ── */
function toast(msg, type) {
  var t = document.createElement('div');
  t.className = 'toast' + (type==='err'?' err':type==='warn'?' warn':type==='wa'?' wa':'');
  t.innerHTML = '<span>'+(type==='err'?'❌':type==='warn'?'⚠️':type==='wa'?'📲':'✅')+'</span><span>'+msg+'</span>';
  document.getElementById('toasts').appendChild(t);
  setTimeout(function(){ t.style.opacity='0'; t.style.transform='translateY(8px)'; t.style.transition='all .3s'; setTimeout(function(){ t.remove(); },320); }, 3500);
}

/* ── POLLING ── */
setInterval(function(){ updateBadge(); if(curPage==='orders')renderOrders(); else if(curPage==='dashboard')renderDashboard(); }, 15000);

/* ── INIT ── */
goPage('dashboard'); updateBadge();
</script>
<?php endif; ?>
</body>
</html>
