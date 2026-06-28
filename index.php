<?php
$configFile = __DIR__ . '/data/config.json';
$config = [
    'title'            => 'WilzXiterz',
    'whatsapp'         => '6285173360622',
    'telegram'         => 'WilzXiterzVN',
    'channel'          => '',
    'discord'          => '',
    'banner'           => 'assets/img/logops.png',
    'download_url'     => '',
    'maintenance'      => false,
    'vpn_block'        => false,
    'vpn_api_key'      => '',
    'google_client_id' => '',
];
if (file_exists($configFile)) {
    $saved = json_decode(file_get_contents($configFile), true);
    if (is_array($saved)) $config = array_merge($config, $saved);
}

$fullTitle   = htmlspecialchars($config['title']);
// Split brand name: last word = white, rest = purple gradient
$parts = explode(' ', trim($fullTitle));
$namePart2 = count($parts) > 1 ? array_pop($parts) : '';
$namePart1 = implode(' ', $parts) ?: $fullTitle;
if (!$namePart2) { $namePart1 = substr($fullTitle,0,4); $namePart2 = substr($fullTitle,4); }

$waLink      = "https://wa.me/" . preg_replace('/[^0-9]/', '', $config['whatsapp']);
$tgLink      = "https://t.me/" . ltrim(htmlspecialchars($config['telegram']), '@');
$discordLink    = htmlspecialchars($config['discord']);
$channelLink    = htmlspecialchars($config['channel']);
$googleClientId = htmlspecialchars($config['google_client_id'] ?? '');
$bannerImg   = htmlspecialchars($config['banner'] ?: 'assets/img/logops.png');
$isMaintenance = !empty($config['maintenance']);
$vpnEnabled    = !empty($config['vpn_block']) ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title><?= $fullTitle ?> - Premium Digital Licenses</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

/* BG glow */
body::before {
  content: '';
  position: fixed;
  top: -200px; left: -200px;
  width: 700px; height: 700px;
  background: radial-gradient(circle, rgba(124,58,237,0.12) 0%, transparent 70%);
  pointer-events: none;
  z-index: 0;
}

/* ============================================================
   LOADING SPLASH
============================================================ */
#ls {
  position: fixed; inset: 0; z-index: 9999;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  background: linear-gradient(180deg, #0A0812 0%, #060410 100%);
  transition: opacity .55s ease, visibility .55s ease;
  overflow: hidden;
}
#ls::before {
  content: '';
  position: absolute; inset: 0;
  pointer-events: none;
  background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(124,58,237,0.5) 0%, transparent 65%);
}
#ls.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
.ls-core { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; gap: 20px; }
.ls-glowline {
  width: 200px; height: 2px; border-radius: 99px;
  background: linear-gradient(90deg, transparent, var(--purple3), #c084fc, var(--purple3), transparent);
  box-shadow: 0 0 18px 3px rgba(157,95,245,0.7), 0 0 50px 8px rgba(124,58,237,0.3);
  animation: lsGlowPulse 2.4s ease-in-out infinite;
}
@keyframes lsGlowPulse { 0%,100%{width:100px;opacity:.5} 50%{width:260px;opacity:1} }
.ls-brand {
  font-weight: 800; font-size: 28px; letter-spacing: .08em; text-transform: uppercase;
  color: var(--text);
  text-shadow: 0 0 30px rgba(192,132,252,0.8), 0 0 60px rgba(124,58,237,0.4);
  white-space: nowrap;
}
.ls-letter {
  display: inline-block; opacity: 0; filter: blur(8px);
  transform: translateY(14px) scale(.85);
  animation: lsLetterReveal .65s cubic-bezier(.2,.8,.2,1) forwards;
}
@keyframes lsLetterReveal {
  0%{opacity:0;filter:blur(8px);transform:translateY(14px) scale(.85)}
  55%{opacity:1;filter:blur(0);transform:translateY(-2px) scale(1.04)}
  100%{opacity:1;filter:blur(0);transform:translateY(0) scale(1)}
}
.ls-dots { display: flex; gap: 8px; margin-top: 4px; }
.ls-dots span {
  width: 7px; height: 7px; border-radius: 50%;
  background: rgba(255,255,255,.12);
  animation: lsDotCycle 1.6s ease-in-out infinite;
}
.ls-dots span:nth-child(1){animation-delay:0s}
.ls-dots span:nth-child(2){animation-delay:.22s}
.ls-dots span:nth-child(3){animation-delay:.44s}
@keyframes lsDotCycle {
  0%,100%{background:rgba(255,255,255,.12);transform:scale(1)}
  50%{background:var(--purple3);transform:scale(1.35);box-shadow:0 0 10px rgba(168,85,247,.7)}
}
.ls-label { font-size: 10px; letter-spacing: .36em; text-transform: uppercase; color: var(--text-dimmer); }

/* ============================================================
   NAVBAR
============================================================ */
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

/* Logo */
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

/* Desktop nav links */
.nav-menu { display: flex; align-items: center; gap: 28px; }
@media (max-width: 900px) { .nav-menu { display: none; } }
.nav-link {
  display: flex; align-items: center; gap: 6px;
  font-size: 14px; color: var(--text-muted);
  transition: color 0.15s;
}
.nav-link:hover, .nav-link.active { color: white; }
.nav-link i { font-size: 14px; }

/* Desktop right actions */
.nav-actions { display: flex; align-items: center; gap: 10px; }
@media (max-width: 900px) { .nav-actions { display: none; } }

.btn-discord-nav {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 8px 14px;
  background: rgba(88,101,242,0.15); border: 1px solid rgba(88,101,242,0.3);
  border-radius: 8px; font-size: 13px; color: #818CF8;
  transition: all 0.15s;
}
.btn-discord-nav:hover { background: rgba(88,101,242,0.28); color: white; }
.discord-sup-badge {
  background: rgba(255,255,255,0.1); border-radius: 5px;
  padding: 2px 7px; font-size: 10px; font-weight: 600; color: rgba(255,255,255,0.8);
}
.btn-admin-nav {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 13px; color: var(--text-muted); transition: color 0.15s;
}
.btn-admin-nav:hover { color: white; }

/* ── GOOGLE LOGIN BUTTON ─────────────────────────────────────────── */
.btn-google-nav {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 7px 13px;
  background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12);
  border-radius: 8px; font-size: 13px; color: var(--text-muted);
  cursor: pointer; transition: all 0.15s; font-family: var(--font);
}
.btn-google-nav:hover { background: rgba(255,255,255,0.12); color: white; border-color: rgba(255,255,255,0.22); }
.btn-google-nav svg { width:16px; height:16px; flex-shrink:0; }
.btn-google-user {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 5px 10px;
  background: rgba(124,58,237,0.12); border: 1px solid rgba(124,58,237,0.25);
  border-radius: 8px; font-size: 13px; color: var(--purple-light);
  cursor: pointer; transition: all 0.15s;
}
.btn-google-user:hover { background: rgba(124,58,237,0.22); }
.btn-google-user img { width: 24px; height: 24px; border-radius: 50%; object-fit:cover; }
.btn-google-user-init {
  width:24px; height:24px; border-radius:50%; background:linear-gradient(135deg,#7C3AED,#9D5CF7);
  display:inline-flex; align-items:center; justify-content:center;
  font-size:11px; font-weight:700; color:#fff; flex-shrink:0;
}

/* ── USER DASHBOARD PANEL ────────────────────────────────────────── */
.user-panel-overlay {
  position: fixed; inset:0; z-index:1200;
  background: rgba(0,0,0,0.65); backdrop-filter:blur(6px);
  display:none; align-items:flex-start; justify-content:flex-end;
  padding: 72px 16px 0;
}
.user-panel-overlay.open { display:flex; }
.user-panel {
  width: 420px; max-width: 100%;
  background: #160E27; border: 1px solid rgba(124,58,237,0.25);
  border-radius: 18px; overflow:hidden;
  max-height: calc(100vh - 90px); display:flex; flex-direction:column;
  box-shadow: 0 24px 80px rgba(0,0,0,0.6);
  animation: panelIn 0.22s cubic-bezier(.22,1,.36,1);
}
@keyframes panelIn { from { opacity:0; transform:translateY(-12px) scale(.97); } to { opacity:1; transform:none; } }
.up-header {
  padding: 18px 20px 14px;
  border-bottom: 1px solid rgba(255,255,255,0.07);
  display:flex; align-items:center; gap:14px;
}
.up-avatar { width:46px; height:46px; border-radius:50%; object-fit:cover; border:2px solid rgba(124,58,237,0.4); flex-shrink:0; }
.up-avatar-init {
  width:46px; height:46px; border-radius:50%; background: linear-gradient(135deg,#7C3AED,#9D5CF7);
  display:flex; align-items:center; justify-content:center;
  font-size:18px; font-weight:700; color:#fff; flex-shrink:0;
}
.up-name { font-weight:700; font-size:15px; color:#fff; }
.up-email { font-size:12px; color:var(--text-muted); margin-top:2px; }
.up-close { margin-left:auto; background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:18px; padding:4px; }
.up-close:hover { color:#fff; }
.up-tabs { display:flex; border-bottom:1px solid rgba(255,255,255,0.07); }
.up-tab {
  flex:1; padding:11px 0; font-size:13px; font-weight:600; color:var(--text-muted);
  background:none; border:none; cursor:pointer; border-bottom:2px solid transparent;
  transition: all 0.15s;
}
.up-tab.active { color: var(--purple-light); border-bottom-color: var(--purple-light); }
.up-tab:hover:not(.active) { color:#fff; }
.up-body { flex:1; overflow-y:auto; padding:16px; }
.up-body::-webkit-scrollbar { width:4px; }
.up-body::-webkit-scrollbar-track { background:transparent; }
.up-body::-webkit-scrollbar-thumb { background: rgba(124,58,237,0.4); border-radius:4px; }
.up-loading { text-align:center; padding:40px 0; color:var(--text-dimmer); font-size:13px; }
.up-empty { text-align:center; padding:40px 0; }
.up-empty i { font-size:32px; color:var(--text-dimmer); margin-bottom:10px; display:block; }
.up-empty p { color:var(--text-muted); font-size:13px; }
.order-card {
  background: #1A1230; border:1px solid rgba(124,58,237,0.18);
  border-radius:12px; padding:14px; margin-bottom:10px;
}
.order-card:last-child { margin-bottom:0; }
.oc-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
.oc-id { font-size:11px; color:var(--text-dimmer); font-family:monospace; }
.oc-status {
  font-size:10px; font-weight:700; padding:3px 8px; border-radius:5px;
  text-transform:uppercase; letter-spacing:.04em;
}
.oc-status.completed { background:rgba(34,197,94,.15); color:#22C55E; }
.oc-status.pending   { background:rgba(234,179,8,.13); color:#EAB308; }
.oc-status.failed    { background:rgba(239,68,68,.13); color:#EF4444; }
.oc-status.cancelled { background:rgba(100,100,120,.18); color:#aaa; }
.oc-product { font-weight:700; font-size:14px; color:#fff; margin-bottom:3px; }
.oc-item    { font-size:12px; color:var(--text-muted); margin-bottom:8px; }
.oc-meta    { display:flex; align-items:center; justify-content:space-between; font-size:12px; color:var(--text-dimmer); }
.oc-amount  { font-weight:700; color:var(--purple-light); }
.oc-license {
  margin-top:10px; background:rgba(124,58,237,0.08); border:1px solid rgba(124,58,237,0.2);
  border-radius:8px; padding:10px 12px;
}
.oc-license-label { font-size:10px; font-weight:700; color:var(--purple-light); letter-spacing:.08em; text-transform:uppercase; margin-bottom:6px; display:flex; align-items:center; gap:5px; }
.oc-license-key {
  font-family:monospace; font-size:12px; color:#fff;
  word-break:break-all; line-height:1.6;
  display:flex; align-items:flex-start; justify-content:space-between; gap:8px;
}
.oc-copy-btn {
  background: rgba(124,58,237,0.2); border:1px solid rgba(124,58,237,0.3);
  border-radius:5px; padding:3px 8px; font-size:11px; color:var(--purple-light);
  cursor:pointer; white-space:nowrap; flex-shrink:0; transition:all .15s;
}
.oc-copy-btn:hover { background:rgba(124,58,237,0.4); color:#fff; }
.up-signout {
  padding:12px 16px; border-top:1px solid rgba(255,255,255,0.07);
  display:flex; justify-content:space-between; align-items:center;
}
.up-refresh-btn {
  display:inline-flex; align-items:center; gap:6px;
  font-size:12px; color:var(--text-muted); background:none; border:none;
  cursor:pointer; padding:6px 10px; border-radius:7px; transition:all .15s;
}
.up-refresh-btn:hover { color:var(--purple-light); background:rgba(124,58,237,.08); }
.up-signout-btn {
  display:inline-flex; align-items:center; gap:7px;
  font-size:12px; color:var(--text-muted); background:none; border:none;
  cursor:pointer; padding:6px 10px; border-radius:7px; transition:all .15s;
}
.up-signout-btn:hover { color:#EF4444; background:rgba(239,68,68,.08); }
/* Sidebar google button */
.sb-google-btn {
  display:flex; align-items:center; gap:10px; padding:12px 16px;
  background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
  border-radius:10px; cursor:pointer; transition:all .15s; width:100%;
}
.sb-google-btn:hover { background:rgba(255,255,255,.1); }
.sb-google-btn svg { width:18px; height:18px; flex-shrink:0; }
.sb-google-label { font-size:13px; font-weight:600; color:var(--text-muted); }
.sb-google-user { font-size:12px; color:var(--purple-light); }
@media (max-width:480px) {
  .user-panel { width:100%; }
  .user-panel-overlay { padding:66px 0 0; align-items:flex-end; }
  .user-panel { border-radius:18px 18px 0 0; max-height:85vh; }
}

/* Hamburger */
.hamburger-btn {
  display: none; align-items: center; gap: 8px;
  background: rgba(255,255,255,.07); border: 1px solid var(--border);
  color: white; font-size: 14px; cursor: pointer;
  padding: 7px 12px; border-radius: 10px; transition: .15s;
}
@media (max-width: 900px) { .hamburger-btn { display: flex; } }
.hamburger-btn i { color: var(--purple-light); font-size: 1rem; }

/* ============================================================
   SIDEBAR OVERLAY
============================================================ */
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

.sidebar-footer { margin: 0 12px 20px; }
.sb-discord-btn {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 16px;
  background: rgba(88,101,242,0.12); border: 1px solid rgba(88,101,242,0.25);
  border-radius: 12px; color: #818CF8; font-size: 14px;
  transition: all 0.15s;
}
.sb-discord-btn:hover { background: rgba(88,101,242,0.22); color: white; }
.sb-discord-left { display: flex; align-items: center; gap: 10px; font-weight: 600; }
.sb-discord-badge { font-size: 12px; font-weight: 600; color: var(--purple-light); }

/* ============================================================
   MAIN CONTENT
============================================================ */
.spacer { height: 0; }
.page-shell { max-width: 1200px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 1; }

/* ============================================================
   HERO
============================================================ */
.hero-wrap { padding: 32px 0 20px; }
.hero-card {
  background: linear-gradient(135deg, #1A1030 0%, #120D24 60%, #0E0A1C 100%);
  border: 1px solid var(--border2); border-radius: 20px;
  padding: 28px 24px; position: relative; overflow: hidden;
}
.hero-card::before {
  content: ''; position: absolute; top: -60px; right: -60px;
  width: 220px; height: 220px;
  background: radial-gradient(circle, rgba(168,85,247,0.15), transparent 70%);
  pointer-events: none;
}
.hero-eyebrow {
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.1em; color: var(--purple-light); margin-bottom: 10px;
}
.hero-title { font-size: clamp(22px, 5vw, 40px); font-weight: 800; line-height: 1.15; letter-spacing: -0.02em; }
.hero-sub { margin-top: 12px; font-size: 14px; line-height: 1.7; color: var(--text-muted); max-width: 520px; }
.stats-grid {
  display: grid; gap: 10px; margin-top: 22px;
  grid-template-columns: 1fr;
}
@media (min-width: 560px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
.stat-item {
  background: rgba(124,58,237,0.08); border: 1px solid rgba(124,58,237,0.2);
  border-radius: 14px; padding: 14px 18px;
}
.stat-num { font-size: 16px; font-weight: 700; color: white; }
.stat-desc { margin-top: 4px; font-size: 12px; color: var(--text-muted); line-height: 1.4; }

/* ============================================================
   MAINTENANCE BANNER
============================================================ */
.maintenance-banner {
  background: rgba(234,179,8,0.1); border: 1px solid rgba(234,179,8,0.3);
  border-radius: 14px; padding: 16px 20px; margin-bottom: 20px;
  display: flex; align-items: center; gap: 12px;
  font-size: 14px; color: var(--yellow);
}
.maintenance-banner i { font-size: 1.1rem; }

/* ============================================================
   HOW IT WORKS
============================================================ */
.flow-wrap { padding: 20px 0 24px; }
.flow-outer-card {
  background: linear-gradient(135deg, #150F28, #0F0B1C);
  border: 1px solid var(--border2); border-radius: 18px; padding: 24px;
}
.section-eyebrow {
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.1em; color: var(--purple-light); margin-bottom: 6px;
}
.section-title { font-size: 20px; font-weight: 700; color: white; margin-bottom: 0; }
.flow-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 14px; margin-top: 20px;
}
.flow-step {
  background: rgba(124,58,237,0.07); border: 1px solid rgba(124,58,237,0.18);
  border-radius: 14px; padding: 18px 16px;
  display: flex; flex-direction: column; gap: 10px;
}
.flow-num { font-size: 11px; font-weight: 700; color: #ffffff; letter-spacing: 0.06em; }
.flow-icon {
  width: 34px; height: 34px;
  background: rgba(168,85,247,0.12); border: 1px solid rgba(168,85,247,0.25);
  border-radius: 9px; display: flex; align-items: center; justify-content: center;
  color: var(--purple-light); font-size: 14px;
}
.flow-step h3 { font-size: 14px; font-weight: 600; }
.flow-step p { font-size: 12px; color: var(--text-muted); line-height: 1.5; }

/* ============================================================
   SEARCH + FILTER
============================================================ */
.toolbar-wrap { padding: 20px 0 16px; }
.search-bar {
  width: 100%;
  background: var(--card-bg2); border: 1px solid var(--border2);
  border-radius: 10px; padding: 12px 16px;
  font-size: 14px; color: white; outline: none;
  font-family: var(--font);
  transition: border-color 0.15s, box-shadow 0.15s;
}
.search-bar::placeholder { color: var(--text-dimmer); }
.search-bar:focus { border-color: var(--purple2); box-shadow: 0 0 0 3px rgba(124,58,237,0.15); }

.chip-row { display: flex; flex-wrap: nowrap; gap: 8px; margin-top: 14px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
.chip-row::-webkit-scrollbar { display: none; }
.chip {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 7px 14px; white-space: nowrap;
  background: rgba(255,255,255,0.04); border: 1px solid var(--border);
  border-radius: 999px; font-size: 12px; color: var(--text-muted);
  cursor: pointer; transition: all 0.15s; font-family: var(--font);
}
.chip:hover { border-color: var(--purple2); color: var(--purple-light); }
.chip.active {
  background: rgba(124,58,237,0.18); border-color: rgba(168,85,247,0.5);
  color: var(--purple-light);
}

/* ============================================================
   PRODUCT CARDS
============================================================ */
.products-wrap { padding: 0 0 40px; }
.products-grid {
  display: grid; gap: 14px; margin-top: 20px;
  grid-template-columns: 1fr;
}
@media (min-width: 560px) { .products-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .products-grid { grid-template-columns: repeat(3, 1fr); gap: 18px; } }

.pcard {
  display: flex; flex-direction: column; gap: 14px;
  padding: 18px;
  background: linear-gradient(145deg, #1A1230, #120D22);
  border: 1px solid rgba(124,58,237,0.2);
  border-radius: 16px; min-height: 230px;
  transition: all 0.22s; cursor: pointer;
  position: relative; overflow: hidden;
  text-decoration: none; color: inherit;
}
.pcard::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
  background: linear-gradient(90deg, transparent, rgba(168,85,247,0.4), transparent);
}
.pcard:hover {
  border-color: rgba(168,85,247,0.45);
  background: linear-gradient(145deg, #1E1535, #160F28);
  transform: translateY(-3px);
  box-shadow: 0 12px 32px rgba(0,0,0,0.4), 0 0 20px rgba(124,58,237,0.1);
}

.pcard-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.cat-pill {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 10px;
  background: rgba(255,255,255,0.05); border: 1px solid var(--border);
  border-radius: 999px; font-size: 11px; color: var(--text-muted);
}
.cat-pill i { font-size: 11px; }

.badge-ready {
  padding: 3px 10px;
  background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.3);
  border-radius: 999px; font-size: 11px; font-weight: 600; color: #4ADE80; white-space: nowrap;
}
.badge-updating {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px;
  background: rgba(234,179,8,0.12); border: 1px solid rgba(234,179,8,0.3);
  border-radius: 999px; font-size: 11px; font-weight: 600; color: #FCD34D; white-space: nowrap;
}
.pcard-name-row { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin-top: 2px; }
.pcard-name { font-size: 17px; font-weight: 700; color: white; }
.badge-bestseller {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 9px;
  background: rgba(250,204,21,0.1); border: 1px solid rgba(250,204,21,0.25);
  border-radius: 999px; font-size: 10px; font-weight: 600; color: #FDE047;
}
.badge-bestseller i { font-size: 10px; }

.pcard-desc {
  font-size: 13px; color: var(--text-muted); line-height: 1.5;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.pcard-facts { display: flex; flex-wrap: wrap; gap: 12px; }
.pcard-fact { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; color: var(--text-dimmer); }
.pcard-fact i { font-size: 12px; }

.pcard-footer { margin-top: auto; display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; }
.price-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-dimmer); margin-bottom: 3px; }
.price-amount {
  font-size: 15px; font-weight: 700;
  background: linear-gradient(90deg, #C084FC, #818CF8);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.price-usd { font-size: 12px; color: var(--text-dimmer); margin-top: 2px; }
.view-btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 9px 16px;
  background: linear-gradient(135deg, rgba(124,58,237,0.25), rgba(124,58,237,0.1));
  border: 1px solid rgba(168,85,247,0.3);
  border-radius: 9px; font-size: 12px; font-weight: 600; color: var(--purple-light);
  white-space: nowrap; transition: all 0.15s; cursor: pointer;
}
.view-btn:hover { background: linear-gradient(135deg, rgba(124,58,237,0.45), rgba(124,58,237,0.25)); border-color: rgba(168,85,247,0.6); }

/* Empty */
.empty-state { text-align: center; padding: 48px 20px; color: var(--text-dimmer); }
.empty-state i { font-size: 2.2rem; display: block; margin-bottom: 12px; color: var(--text-dimmer); }
.empty-state p { font-size: 14px; }

/* ============================================================
   FOOTER
============================================================ */
footer {
  border-top: 1px solid var(--border);
  background: linear-gradient(180deg, #0D0A1A 0%, #080611 100%);
  position: relative; z-index: 1; margin-top: 20px;
}
.footer-inner { max-width: 1200px; margin: 0 auto; padding: 36px 20px; }
.footer-grid { display: grid; grid-template-columns: 1fr; gap: 28px; }
@media (min-width: 768px) { .footer-grid { grid-template-columns: 1.4fr 0.8fr 0.9fr 0.9fr; } }
.footer-brand {
  font-size: 18px; font-weight: 800;
  background: linear-gradient(90deg, #C084FC, #818CF8);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.footer-desc { margin-top: 10px; font-size: 13px; line-height: 1.65; color: var(--text-muted); max-width: 280px; }
.footer-head { font-size: 13px; font-weight: 600; color: white; margin-bottom: 12px; }
.footer-links { display: flex; flex-direction: column; gap: 8px; }
.footer-link { font-size: 13px; color: var(--text-muted); transition: color 0.15s; }
.footer-link:hover { color: white; }
.footer-support-txt { font-size: 13px; color: var(--text-muted); line-height: 1.6; }
.footer-discord-btn {
  display: inline-flex; align-items: center; gap: 8px; margin-top: 12px;
  padding: 9px 14px;
  background: rgba(88,101,242,0.12); border: 1px solid rgba(88,101,242,0.25);
  border-radius: 9px; font-size: 13px; color: #818CF8; transition: all 0.15s;
}
.footer-discord-btn:hover { background: rgba(88,101,242,0.22); }
.footer-bottom { margin-top: 28px; padding-top: 18px; border-top: 1px solid var(--border); text-align: center; font-size: 12px; color: var(--text-dimmer); }

/* ============================================================
   FLOATING CS (WhatsApp)
============================================================ */
.floating-cs {
  position: fixed; bottom: 22px; right: 18px;
  background: #25D366; color: #fff;
  padding: 11px 18px; border-radius: 99px;
  font-size: 14px; font-weight: 700;
  display: flex; align-items: center; gap: 8px;
  box-shadow: 0 6px 24px rgba(37,211,102,0.35);
  z-index: 400; transition: .2s;
}
.floating-cs:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(37,211,102,0.45); }
.floating-cs i { font-size: 1rem; }

/* ============================================================
   DAYS POPUP (bottom sheet pilih paket)
============================================================ */
#daysPopupOverlay {
  position: fixed; inset: 0; z-index: 800;
  background: rgba(0,0,0,.65); backdrop-filter: blur(6px);
  display: none; align-items: flex-end; justify-content: center;
}
#daysPopupOverlay.open { display: flex; }
.days-popup {
  background: var(--bg2); border: 1px solid var(--border2);
  border-radius: 24px 24px 0 0;
  padding: 20px 16px calc(20px + env(safe-area-inset-bottom));
  width: 100%; max-width: 540px; max-height: 90vh; overflow-y: auto;
  animation: dpSlideUp .32s cubic-bezier(.4,0,.2,1);
}
@keyframes dpSlideUp { from{transform:translateY(100%);opacity:0} to{transform:translateY(0);opacity:1} }
.days-popup-handle { width: 38px; height: 4px; background: rgba(255,255,255,.12); border-radius: 99px; margin: 0 auto 18px; }
.days-popup-head {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 18px; padding-bottom: 16px; border-bottom: 1px solid var(--border);
}
.dp-prod-icon {
  width: 46px; height: 46px; border-radius: 12px;
  background: var(--purple-dim); border: 1px solid var(--purple-border);
  display: flex; align-items: center; justify-content: center;
  color: var(--purple-light); font-size: 1.1rem; flex-shrink: 0;
}
.dp-prod-title { font-size: 1rem; font-weight: 800; letter-spacing: -.2px; line-height: 1.2; }
.dp-prod-plat { font-size: .72rem; color: var(--text-muted); margin-top: 3px; }
.dp-item {
  display: flex; align-items: center; justify-content: space-between;
  background: var(--bg3); border: 1.5px solid var(--border);
  border-radius: 14px; padding: 14px 16px; margin-bottom: 10px;
  cursor: pointer; transition: all .18s; text-decoration: none; gap: 10px; color: inherit;
}
.dp-item:hover { border-color: var(--purple); background: var(--purple-dim); }
.dp-item-left { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
.dp-day-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: var(--purple-dim); border: 1px solid var(--purple-border);
  display: flex; align-items: center; justify-content: center;
  font-size: .82rem; color: var(--purple-light); flex-shrink: 0;
}
.dp-label { font-size: .9rem; font-weight: 700; color: var(--text); }
.dp-right { text-align: right; flex-shrink: 0; }
.dp-price-lbl { font-size: .58rem; font-weight: 700; color: var(--text-dimmer); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
.dp-price { font-size: 1rem; font-weight: 900; color: var(--purple-light); }
.dp-orig { font-size: .7rem; color: var(--text-dimmer); text-decoration: line-through; margin-top: 1px; }
.dp-promo-tag { display: inline-block; font-size: .6rem; font-weight: 800; background: var(--purple2); color: #fff; padding: 2px 7px; border-radius: 99px; margin-top: 3px; }
.dp-arrow { color: var(--purple-light); font-size: .85rem; flex-shrink: 0; }

/* ============================================================
   RECENT PURCHASE TOAST
============================================================ */
#recentToast {
  position: fixed; bottom: 70px; left: 16px; right: 16px;
  z-index: 800; pointer-events: none;
}
.rt-card {
  background: rgba(18,13,32,0.97); border: 1px solid var(--border2);
  border-radius: 16px; padding: 13px 16px;
  display: flex; align-items: center; gap: 13px;
  box-shadow: 0 8px 32px rgba(0,0,0,.5);
  backdrop-filter: blur(12px);
  animation: rtSlideIn .4s cubic-bezier(.34,1.56,.64,1) forwards;
  pointer-events: all; position: relative; max-width: 440px;
}
@keyframes rtSlideIn { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
@keyframes rtSlideOut { to{transform:translateY(20px);opacity:0} }
.rt-card.hiding { animation: rtSlideOut .3s ease forwards; }
.rt-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: var(--purple-dim); border: 1px solid var(--purple-border);
  display: flex; align-items: center; justify-content: center;
  color: var(--purple-light); font-size: .9rem; flex-shrink: 0;
}
.rt-body { flex: 1; min-width: 0; }
.rt-label { font-size: .6rem; font-weight: 700; color: var(--purple-light); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 2px; }
.rt-name { font-size: .82rem; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rt-product { font-size: .76rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rt-meta { font-size: .7rem; color: var(--text-dimmer); margin-top: 2px; }
.rt-close { background: none; border: none; color: var(--text-dimmer); cursor: pointer; padding: 4px; font-size: .85rem; flex-shrink: 0; }

/* Animations */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(18px); }
  to   { opacity: 1; transform: translateY(0); }
}
.fade-up { animation: fadeUp 0.45s ease both; }
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


<!-- LOADING SPLASH -->
<div id="ls">
  <div class="ls-core">
    <div class="ls-glowline"></div>
    <div class="ls-brand"><?= strtoupper(htmlspecialchars($fullTitle)) ?></div>
    <div class="ls-dots"><span></span><span></span><span></span></div>
    <div class="ls-label">Premium Digital Licenses</div>
  </div>
</div>

<!-- NAVBAR -->
<nav>
  <div class="nav-inner">
    <!-- Logo -->
    <a href="index.php" class="nav-logo">
      <div class="logo-icon">
        <img src="<?= $bannerImg ?>" alt="logo" onerror="this.outerHTML='<i class=\'fa-solid fa-layer-group\' style=\'color:#fff;font-size:16px\'></i>'">
      </div>
      <div class="logo-text">
        <span class="logo-part1"><?= $namePart1 ?></span>
        <span class="logo-part2"><?= $namePart2 ?></span>
      </div>
    </a>

    <!-- Desktop links -->
    <div class="nav-menu">
      <a class="nav-link active" href="index.php"><i class="fa-solid fa-boxes-stacked"></i> Products</a>
      <a class="nav-link" href="tracking.php"><i class="fa-solid fa-magnifying-glass"></i> Tracking</a>
      <a class="nav-link" href="download.php"><i class="fa-solid fa-cloud-arrow-down"></i> Downloads</a>
    </div>

    <div class="nav-actions">
      <?php if (!empty($config['discord'])): ?>
      <a class="btn-discord-nav" href="<?= $discordLink ?>" target="_blank">
        <i class="fa-brands fa-discord"></i> Discord
        <span class="discord-sup-badge">Support</span>
      </a>
      <?php endif; ?>
      <?php if (!empty($config['google_client_id'])): ?>
      <!-- Google Sign-In: shown when logged out -->
      <button class="btn-google-nav" id="navGoogleLoginBtn" onclick="triggerGoogleLogin()" style="display:none">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
        Login
      </button>
      <!-- User avatar: shown when logged in -->
      <button class="btn-google-user" id="navGoogleUserBtn" onclick="toggleUserPanel()" style="display:none">
        <span class="btn-google-user-init" id="navUserInit"></span>
        <img id="navUserAvatar" src="" alt="" style="display:none">
        <span id="navUserName" style="max-width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
        <i class="fa-solid fa-chevron-down" style="font-size:10px;opacity:.7"></i>
      </button>
      <?php endif; ?>
      <a class="btn-admin-nav" href="admin.php">
        <i class="fa-solid fa-right-to-bracket" style="color:var(--purple-light)"></i> Admin
      </a>
    </div>

    <!-- Hamburger -->
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
    <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;color:var(--text-dimmer);text-transform:uppercase;padding:12px 4px 6px;display:flex;align-items:center;gap:6px;"><i class="fa-regular fa-comment-dots" style="font-size:12px"></i> Bantuan</div>
    <a class="sidebar-link" href="<?= $waLink ?>" target="_blank"><i class="fa-brands fa-whatsapp" style="color:#25D366"></i> WhatsApp Admin</a>
    <?php if (!empty($config['telegram'])): ?>
    <a class="sidebar-link" href="<?= $tgLink ?>" target="_blank"><i class="fa-brands fa-telegram" style="color:#2CA5E0"></i> Telegram Admin</a>
    <?php endif; ?>
    <div style="font-size:10px;font-weight:700;letter-spacing:0.12em;color:var(--text-dimmer);text-transform:uppercase;padding:12px 4px 6px;display:flex;align-items:center;gap:6px;"><i class="fa-solid fa-gear" style="font-size:12px"></i> Lainnya</div>
    <a class="sidebar-link" href="download.php"><i class="fa-solid fa-cloud-arrow-down" style="color:var(--purple-light)"></i> Downloads</a>
    <a class="sidebar-link" href="admin.php"><i class="fa-solid fa-right-to-bracket" style="color:var(--purple-light)"></i> Login Admin</a>
    <?php if (!empty($config['channel'])): ?>
    <a class="sidebar-link" href="<?= $channelLink ?>" target="_blank"><i class="fa-solid fa-satellite-dish" style="color:var(--purple-light)"></i> Channel</a>
    <?php endif; ?>
  </nav>

  <?php if (!empty($config['google_client_id'])): ?>
  <div style="padding:12px 16px 0;">
    <button class="sb-google-btn" id="sbGoogleBtn" onclick="triggerGoogleLogin()">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
      <div>
        <div class="sb-google-label" id="sbGoogleLabel">Login dengan Google</div>
        <div class="sb-google-user" id="sbGoogleUser" style="display:none"></div>
      </div>
    </button>
  </div>
  <?php endif; ?>

  <?php if (!empty($config['discord'])): ?>
  <div class="sidebar-footer">
    <a class="sb-discord-btn" href="<?= $discordLink ?>" target="_blank">
      <div class="sb-discord-left"><i class="fa-brands fa-discord"></i> Discord</div>
      <span class="sb-discord-badge">Support</span>
    </a>
  </div>
  <?php endif; ?>
</aside>

<!-- MAIN -->
<main>
  <!-- HERO -->
  <section class="hero-wrap">
    <div class="page-shell">
      <div class="hero-card fade-up">
        <p class="hero-eyebrow">Digital License Platform</p>
        <h1 class="hero-title">Premium tools and<br>instant digital licenses.</h1>
        <p class="hero-sub">Browse trusted digital tools, pay securely, and get quick access to license keys, setup guides, and customer support.</p>
        <div class="stats-grid">
          <div class="stat-item"><div class="stat-num">5000+ Licenses</div><div class="stat-desc">Delivered to customers with support.</div></div>
          <div class="stat-item"><div class="stat-num">2000+ Members</div><div class="stat-desc">Active community with instant support.</div></div>
          <div class="stat-item"><div class="stat-num">Since 2024</div><div class="stat-desc">Licenses ready for auto delivery.</div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="flow-wrap">
    <div class="page-shell">
      <div class="flow-outer-card">
        <p class="section-eyebrow">How It Works</p>
        <h2 class="section-title">Checkout in three steps</h2>
        <div class="flow-grid">
          <div class="flow-step">
            <div class="flow-icon"><i class="fa-solid fa-cart-shopping"></i></div>
            <h3>Choose package</h3>
            <p>Select the product and duration that fits your setup.</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon"><i class="fa-solid fa-qrcode"></i></div>
            <h3>Complete payment</h3>
            <p>Pay via QRIS, e-wallet, or crypto in seconds.</p>
          </div>
          <div class="flow-step">
            <div class="flow-icon"><i class="fa-solid fa-key"></i></div>
            <h3>Get your license</h3>
            <p>License key delivered automatically after payment confirmed.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- SEARCH + FILTER -->
  <section class="toolbar-wrap" id="products">
    <div class="page-shell">
      <?php if ($isMaintenance): ?>
      <div class="maintenance-banner">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>Toko sedang dalam mode maintenance. Pembelian sementara ditutup.</span>
      </div>
      <?php endif; ?>
      <p class="section-eyebrow">Products</p>
      <h2 class="section-title">Find your tool</h2>
      <p style="margin-top:4px;font-size:13px;color:var(--text-muted)">Search by product name or filter by platform.</p>
      <div style="margin-top:14px">
        <input class="search-bar" id="searchInput" placeholder="Search Products..." type="text">
      </div>
      <div class="chip-row" id="chipRow">
        <button class="chip active" data-cat="ALL"><i class="fa-solid fa-layer-group"></i> All</button>
      </div>
    </div>
  </section>

  <!-- PRODUCTS GRID -->
  <section class="products-wrap">
    <div class="page-shell">
      <p class="section-eyebrow">Storefront</p>
      <h2 class="section-title">Available products</h2>
      <div class="products-grid" id="productContainer">
        <div class="empty-state" style="grid-column:1/-1"><i class="fa-solid fa-spinner fa-spin"></i><p>Memuat produk...</p></div>
      </div>
    </div>
  </section>
</main>

<!-- FOOTER -->
<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div>
        <span class="footer-brand"><?= $fullTitle ?></span>
        <p class="footer-desc">Digital licenses, setup resources, secure checkout, and customer support in one place.</p>
      </div>
      <div>
        <h2 class="footer-head">Quick Links</h2>
        <div class="footer-links">
          <a class="footer-link" href="index.php">Products</a>
          <a class="footer-link" href="tracking.php">Tracking</a>
          <a class="footer-link" href="download.php">Downloads</a>
        </div>
      </div>
      <div>
        <h2 class="footer-head">Support</h2>
        <p class="footer-support-txt">Setup help, license delivery, reset requests, and payment support.</p>
        <?php if (!empty($config['discord'])): ?>
        <a class="footer-discord-btn" href="<?= $discordLink ?>" target="_blank">
          <i class="fa-brands fa-discord"></i> Discord
        </a>
        <?php endif; ?>
      </div>
      <div>
        <h2 class="footer-head">Contact</h2>
        <div class="footer-links">
          <a class="footer-link" href="<?= $waLink ?>" target="_blank"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
          <?php if (!empty($config['telegram'])): ?>
          <a class="footer-link" href="<?= $tgLink ?>" target="_blank"><i class="fa-brands fa-telegram"></i> Telegram</a>
          <?php endif; ?>
          <?php if (!empty($config['discord'])): ?>
          <a class="footer-link" href="<?= $discordLink ?>" target="_blank"><i class="fa-brands fa-discord"></i> Discord</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="footer-bottom">© 2026 <?= $fullTitle ?>. All rights reserved.</div>
  </div>
</footer>

<!-- USER DASHBOARD PANEL -->
<?php if (!empty($config['google_client_id'])): ?>
<div class="user-panel-overlay" id="userPanelOverlay" onclick="handleOverlayClick(event)">
  <div class="user-panel" id="userPanel">
    <!-- Header -->
    <div class="up-header">
      <div class="up-avatar-init" id="upAvatarInit"></div>
      <img class="up-avatar" id="upAvatar" src="" alt="" style="display:none">
      <div>
        <div class="up-name" id="upName">-</div>
        <div class="up-email" id="upEmail">-</div>
      </div>
      <button class="up-close" onclick="closeUserPanel()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <!-- Tabs -->
    <div class="up-tabs">
      <button class="up-tab active" id="tabTrx" onclick="switchTab('trx')">
        <i class="fa-solid fa-receipt" style="margin-right:5px"></i> Transaksi
      </button>
      <button class="up-tab" id="tabLic" onclick="switchTab('lic')">
        <i class="fa-solid fa-key" style="margin-right:5px"></i> License
      </button>
    </div>
    <!-- Body -->
    <div class="up-body" id="upBody">
      <div class="up-loading"><i class="fa-solid fa-spinner fa-spin" style="margin-right:6px"></i> Memuat data...</div>
    </div>
    <!-- Footer -->
    <div class="up-signout">
      <button class="up-refresh-btn" onclick="reloadOrders()">
        <i class="fa-solid fa-rotate-right"></i> Refresh
      </button>
      <button class="up-signout-btn" onclick="signOutGoogle()">
        <i class="fa-solid fa-right-from-bracket"></i> Keluar
      </button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Google GSI -->
<?php if (!empty($config['google_client_id'])): ?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<div id="g_id_onload"
  data-client_id="<?= $googleClientId ?>"
  data-callback="handleGoogleLogin"
  data-auto_prompt="false">
</div>
<div class="g_id_signin" id="gsiWidget"
  data-type="standard"
  data-size="medium"
  data-theme="outline"
  style="display:none">
</div>
<?php endif; ?>

<!-- FLOATING WA -->
<a href="<?= $waLink ?>" target="_blank" class="floating-cs">
  <i class="fa-brands fa-whatsapp"></i> CS Bantuan
</a>

<!-- RECENT TOAST -->
<div id="recentToast"></div>

<!-- DAYS POPUP -->
<div id="daysPopupOverlay" onclick="closeDaysPopup(event)">
  <div class="days-popup" id="daysPopup">
    <div class="days-popup-handle"></div>
    <div class="days-popup-head">
      <div class="dp-prod-icon" id="dpIcon"><i class="fa-solid fa-box"></i></div>
      <div>
        <div class="dp-prod-title" id="dpTitle">Produk</div>
        <div class="dp-prod-plat" id="dpPlat"></div>
      </div>
    </div>
    <div id="dpItems"></div>
  </div>
</div>

<script>
// ── LOADING SPLASH ─────────────────────────────────────────────────────────────
(function(){
  var ls = document.getElementById('ls');
  if (!ls) return;
  if (sessionStorage.getItem('ls_shown')) { ls.style.display='none'; return; }
  sessionStorage.setItem('ls_shown','1');
  var brand = ls.querySelector('.ls-brand');
  var LETTER_DELAY=0.1, LETTER_DUR=0.65;
  var letterCount = 0;
  if (brand && !brand.dataset.split) {
    var text = brand.textContent;
    brand.textContent = ''; brand.dataset.split = '1';
    [...text].forEach(function(ch,i){
      var span = document.createElement('span');
      span.className='ls-letter'; span.textContent = ch===' '?'\u00A0':ch;
      span.style.animationDelay=(i*LETTER_DELAY)+'s';
      brand.appendChild(span); letterCount++;
    });
  }
  var dur = Math.min(4500, Math.max(2800, Math.round((((letterCount-1)*LETTER_DELAY)+LETTER_DUR+1.4)*1000)));
  window.addEventListener('load',function(){ setTimeout(function(){ ls.classList.add('hidden'); }, dur); });
})();

// ── SIDEBAR ────────────────────────────────────────────────────────────────────
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');
const menuBtn = document.getElementById('menuBtn');
const menuLabel = document.getElementById('menuLabel');
const sidebarClose = document.getElementById('sidebarClose');

function openSidebar(){sidebar.classList.add('open');overlay.classList.add('open');menuLabel.textContent='Close';document.body.style.overflow='hidden';}
function closeSidebar(){sidebar.classList.remove('open');overlay.classList.remove('open');menuLabel.textContent='Menu';document.body.style.overflow='';}
menuBtn.addEventListener('click',()=>sidebar.classList.contains('open')?closeSidebar():openSidebar());
sidebarClose.addEventListener('click',closeSidebar);
overlay.addEventListener('click',closeSidebar);

// ── PRODUCTS ───────────────────────────────────────────────────────────────────
let allProducts = [], activeCategory = 'ALL';
const searchInput = document.getElementById('searchInput');
const chipRow = document.getElementById('chipRow');
const productContainer = document.getElementById('productContainer');
const prodDataMap = {};

const PLAT_ICON = { android:'fa-brands fa-android', pc:'fa-solid fa-desktop', ios:'fa-brands fa-apple', default:'fa-solid fa-cube' };
function platIcon(p){ const k=(p||'').toLowerCase(); return PLAT_ICON[k]||PLAT_ICON.default; }
function parseRp(v){ return parseInt(String(v||'').replace(/[^0-9]/g,''))||0; }
function effectivePrice(i){ return (i.promo&&i.promo_price)?Number(i.promo_price):parseRp(i.price); }

function buildChips(){
  const cats=['ALL'];
  allProducts.forEach(p=>{ const c=(p.platform||'OTHER').toUpperCase(); if(!cats.includes(c)) cats.push(c); });
  chipRow.innerHTML='';
  cats.forEach(c=>{
    const btn=document.createElement('button');
    btn.className='chip'+(c===activeCategory?' active':'');
    btn.dataset.cat=c;
    let icon='fa-solid fa-layer-group';
    if(c==='PC') icon='fa-solid fa-desktop';
    else if(c==='ANDROID') icon='fa-brands fa-android';
    else if(c==='IOS') icon='fa-brands fa-apple';
    btn.innerHTML=`<i class="${icon}"></i> ${c}`;
    btn.addEventListener('click',function(e){
      e.preventDefault();
      activeCategory=c;
      chipRow.querySelectorAll('.chip').forEach(x=>x.classList.remove('active'));
      btn.classList.add('active');
      filterProducts();
    });
    chipRow.appendChild(btn);
  });
}

function displayProducts(products){
  productContainer.innerHTML='';
  if(!products.length){
    productContainer.innerHTML='<div class="empty-state" style="grid-column:1/-1"><i class="fa-solid fa-box-open"></i><p>Produk tidak ditemukan</p></div>';
    return;
  }
  products.forEach((p,idx)=>{
    const plat=(p.platform||'?').toUpperCase();
    const icon=platIcon(p.platform);
    const isReady = p.manual_status==='ready' ? true : p.manual_status==='updating' ? false : (p.stock||0)>0;
    const totalStock=(p.prices||[]).reduce((s,pr)=>s+(pr.stock||(pr.licenses||[]).length||0),0);
    const minPrice=p.prices&&p.prices.length ? Math.min(...p.prices.map(effectivePrice)) : 0;
    const minPriceUsd=(minPrice/18000).toFixed(2);

    // From days label
    const firstPkg=(p.prices&&p.prices[0])?p.prices[0]:null;
    let fromDaysTxt='From 1 day';
    if(firstPkg){
      let d=firstPkg.days;
      if(!d&&firstPkg.label){ const m=String(firstPkg.label).match(/\d+/); d=m?parseInt(m[0]):1; }
      d=d||1; fromDaysTxt='From '+d+' day'+(d>1?'s':'');
    }

    const stockColor=totalStock===0?'var(--red)':totalStock<=3?'var(--yellow)':'var(--green)';

    const mapKey='prod_'+idx;
    prodDataMap[mapKey]={id:p.id,name:p.name,platform:plat,prices:p.prices||[]};

    const card=document.createElement('div');
    card.className='pcard fade-up';

    card.innerHTML=
      '<div class="pcard-top">'+
        '<span class="cat-pill"><i class="'+icon+'"></i> '+plat+'</span>'+
        (isReady?'<span class="badge-ready">Ready</span>':'<span class="badge-updating"><i class="fa-solid fa-rotate fa-spin" style="font-size:10px"></i> Updating</span>')+
      '</div>'+
      '<div>'+
        '<div class="pcard-name-row">'+
          '<span class="pcard-name">'+(p.name||'-')+'</span>'+
          (p.best_seller?'<span class="badge-bestseller"><i class="fa-solid fa-star"></i> Best Seller</span>':'')+
        '</div>'+
        '<p class="pcard-desc">'+(p.desc||'Panel untuk '+plat)+'</p>'+
      '</div>'+
      '<div class="pcard-facts">'+
        '<span class="pcard-fact days-btn" data-key="'+mapKey+'" style="cursor:pointer;color:var(--text-muted)"><i class="fa-regular fa-calendar"></i> '+fromDaysTxt+' <i class="fa-solid fa-chevron-right" style="font-size:9px;margin-left:2px"></i></span>'+
        '<span class="pcard-fact"><i class="fa-solid fa-key"></i> <span style="color:'+stockColor+';font-weight:700">'+totalStock+' ready</span></span>'+
      '</div>'+
      '<div class="pcard-footer">'+
        '<div>'+
          '<div class="price-label">Start from</div>'+
          '<div class="price-amount">Rp '+minPrice.toLocaleString('id-ID')+'</div>'+
          '<div class="price-usd">/ $'+minPriceUsd+'</div>'+
        '</div>'+
        '<span class="view-btn" data-id="'+p.id+'"><i class="fa-solid fa-cart-shopping"></i> View</span>'+
      '</div>';

    // Events
    card.querySelector('.days-btn').addEventListener('click',function(e){
      e.stopPropagation(); openDaysPopup(prodDataMap[this.dataset.key]);
    });
    card.querySelector('.view-btn').addEventListener('click',function(e){
      e.stopPropagation(); window.location.href='detail.php?id='+this.dataset.id;
    });
    card.addEventListener('click',function(){
      window.location.href='detail.php?id='+p.id;
    });

    productContainer.appendChild(card);
  });
}

function filterProducts(){
  const q=searchInput.value.toLowerCase();
  const filtered=allProducts.filter(p=>{
    const matchQ=p.name.toLowerCase().includes(q)||(p.desc||'').toLowerCase().includes(q);
    const matchCat=(activeCategory==='ALL')||((p.platform||'OTHER').toUpperCase()===activeCategory);
    return matchQ && matchCat;
  });
  displayProducts(filtered);
}

fetch('api.php?action=get_products&t='+Date.now(),{cache:'no-store'})
  .then(r=>{ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
  .then(data=>{ if(!Array.isArray(data)) throw new Error('Invalid data'); allProducts=data; buildChips(); filterProducts(); })
  .catch(()=>{ productContainer.innerHTML='<div class="empty-state" style="grid-column:1/-1"><i class="fa-solid fa-triangle-exclamation"></i><p>Gagal memuat produk</p></div>'; });

searchInput.addEventListener('input',filterProducts);

// ── DAYS POPUP ─────────────────────────────────────────────────────────────────
function openDaysPopup(p){
  document.getElementById('dpTitle').textContent=p.name;
  document.getElementById('dpPlat').textContent=p.platform+' · '+(p.prices||[]).length+' paket';
  const items=document.getElementById('dpItems');
  items.innerHTML='';
  if(!p.prices||!p.prices.length){
    items.innerHTML='<div style="text-align:center;padding:24px;color:var(--text-dimmer)">Belum ada paket</div>';
  } else {
    p.prices.forEach(pr=>{
      const orig=parseRp(pr.price);
      const price=(pr.promo&&pr.promo_price)?Number(pr.promo_price):orig;
      const hasPromo=pr.promo&&pr.promo_price&&price<orig;
      const a=document.createElement('a');
      a.href='detail.php?id='+p.id; a.className='dp-item';
      a.innerHTML=
        '<div class="dp-item-left">'+
          '<div class="dp-day-icon"><i class="fa-regular fa-calendar"></i></div>'+
          '<div class="dp-label">'+(pr.label||'Paket')+'</div>'+
        '</div>'+
        '<div class="dp-right">'+
          '<div class="dp-price-lbl">HARGA</div>'+
          '<div class="dp-price">Rp '+price.toLocaleString('id-ID')+'</div>'+
          (hasPromo?'<div class="dp-orig">Rp '+orig.toLocaleString('id-ID')+'</div><div class="dp-promo-tag">PROMO</div>':'')+
        '</div>'+
        '<i class="fa-solid fa-chevron-right dp-arrow"></i>';
      items.appendChild(a);
    });
  }
  document.getElementById('daysPopupOverlay').classList.add('open');
  document.body.style.overflow='hidden';
}
function closeDaysPopup(e){
  const popup=document.getElementById('daysPopup');
  if(e&&popup.contains(e.target)) return;
  document.getElementById('daysPopupOverlay').classList.remove('open');
  document.body.style.overflow='';
}
// Swipe down to close
(function(){
  let startY=0;
  const popup=document.getElementById('daysPopup');
  popup.addEventListener('touchstart',e=>{startY=e.touches[0].clientY;},{passive:true});
  popup.addEventListener('touchend',e=>{if(e.changedTouches[0].clientY-startY>60)closeDaysPopup(null);},{passive:true});
})();

// ── RECENT PURCHASE TOAST ──────────────────────────────────────────────────────
const toastContainer=document.getElementById('recentToast');
let recentOrders=[],toastIndex=0,toastTimer=null;

function timeAgo(ts){
  const d=Math.floor(Date.now()/1000)-ts;
  if(d<60) return d+'s ago';
  if(d<3600) return Math.floor(d/60)+'m ago';
  if(d<86400) return Math.floor(d/3600)+'h ago';
  return Math.floor(d/86400)+'d ago';
}
function maskName(n){ if(!n||n.length<2) return n||'Guest'; return n[0]+'***'+n[n.length-1]; }

function showToast(order){
  if(!order) return;
  const card=document.createElement('div');
  card.className='rt-card';
  card.innerHTML=
    '<div class="rt-icon"><i class="fa-solid fa-cart-shopping"></i></div>'+
    '<div class="rt-body">'+
      '<div class="rt-label">RECENT PURCHASE</div>'+
      '<div class="rt-name">'+maskName(order.name)+' bought</div>'+
      '<div class="rt-product">'+order.product+(order.item?' · '+order.item.trim():'')+'</div>'+
      '<div class="rt-meta">'+timeAgo(order.created_at)+'</div>'+
    '</div>'+
    '<button class="rt-close" id="rtCloseBtn"><i class="fa-solid fa-xmark"></i></button>';
  toastContainer.innerHTML='';
  toastContainer.appendChild(card);
  card.querySelector('#rtCloseBtn').addEventListener('click',()=>dismissToast(card));
  clearTimeout(toastTimer);
  toastTimer=setTimeout(()=>dismissToast(card),4500);
}
function dismissToast(card){
  if(!card||!card.parentNode) return;
  card.classList.add('hiding');
  setTimeout(()=>{ card.remove(); clearTimeout(toastTimer); toastTimer=setTimeout(cycleToast,6000); },320);
}
function cycleToast(){ if(!recentOrders.length) return; toastIndex=(toastIndex+1)%recentOrders.length; showToast(recentOrders[toastIndex]); }

window.addEventListener('load',function(){
  fetch('api.php?action=get_recent_orders&t='+Date.now(),{cache:'no-store'})
    .then(r=>r.json())
    .then(data=>{ if(!Array.isArray(data)||!data.length) return; recentOrders=data; setTimeout(()=>showToast(recentOrders[0]),3000); })
    .catch(()=>{});
});

// ── GOOGLE LOGIN & USER DASHBOARD ─────────────────────────────────────────────
let gCurrentUser = null; // { name, email, picture, credential }
let gUserOrders  = [];
let gActiveTab   = 'trx';

// Restore session from localStorage
(function(){
  try {
    const saved = localStorage.getItem('gUser');
    if (saved) {
      gCurrentUser = JSON.parse(saved);
      applyUserState();
    } else {
      showLoggedOutState();
    }
  } catch(e) { showLoggedOutState(); }
})();

function triggerGoogleLogin(){
  // Click the hidden GSI widget button
  const widget = document.querySelector('#gsiWidget div[role="button"]');
  if (widget) { widget.click(); return; }
  // Fallback: prompt via google.accounts.id
  if (window.google && google.accounts && google.accounts.id) {
    google.accounts.id.prompt();
  }
}

function handleGoogleLogin(response){
  if (!response || !response.credential) return;
  // Decode JWT payload (no verification needed client-side, server verifies)
  try {
    const parts = response.credential.split('.');
    const payload = JSON.parse(atob(parts[1].replace(/-/g,'+').replace(/_/g,'/')));
    gCurrentUser = {
      name: payload.name || payload.email.split('@')[0],
      email: payload.email,
      picture: payload.picture || '',
      credential: response.credential,
      sub: payload.sub
    };
    localStorage.setItem('gUser', JSON.stringify(gCurrentUser));
    applyUserState();
    // Auto-open panel after login
    openUserPanel();
    loadUserOrders();
  } catch(e) { console.error('Google login parse error', e); }
}

function applyUserState(){
  if (!gCurrentUser) { showLoggedOutState(); return; }
  const name = gCurrentUser.name || gCurrentUser.email;

  // Navbar: hide login btn, show user btn
  const loginBtn = document.getElementById('navGoogleLoginBtn');
  const userBtn  = document.getElementById('navGoogleUserBtn');
  const init     = document.getElementById('navUserInit');
  const avatar   = document.getElementById('navUserAvatar');
  const nameEl   = document.getElementById('navUserName');
  if (loginBtn) loginBtn.style.display = 'none';
  if (userBtn)  userBtn.style.display  = 'inline-flex';
  if (nameEl)   nameEl.textContent = name.split(' ')[0];
  if (init && avatar) {
    init.textContent = name.charAt(0).toUpperCase();
    if (gCurrentUser.picture) {
      avatar.src = gCurrentUser.picture;
      avatar.style.display = 'inline-block';
      init.style.display = 'none';
    }
  }

  // Sidebar
  const sbBtn   = document.getElementById('sbGoogleBtn');
  const sbLabel = document.getElementById('sbGoogleLabel');
  const sbUser  = document.getElementById('sbGoogleUser');
  if (sbBtn) sbBtn.onclick = () => { closeSidebar(); openUserPanel(); };
  if (sbLabel) sbLabel.textContent = name.split(' ')[0];
  if (sbUser) { sbUser.style.display = 'block'; sbUser.textContent = 'Lihat riwayat'; }
}

function showLoggedOutState(){
  const loginBtn = document.getElementById('navGoogleLoginBtn');
  const userBtn  = document.getElementById('navGoogleUserBtn');
  if (loginBtn) loginBtn.style.display = 'inline-flex';
  if (userBtn)  userBtn.style.display  = 'none';

  const sbBtn   = document.getElementById('sbGoogleBtn');
  const sbLabel = document.getElementById('sbGoogleLabel');
  const sbUser  = document.getElementById('sbGoogleUser');
  if (sbBtn) sbBtn.onclick = triggerGoogleLogin;
  if (sbLabel) sbLabel.textContent = 'Login dengan Google';
  if (sbUser) sbUser.style.display = 'none';
}

function toggleUserPanel(){
  const overlay = document.getElementById('userPanelOverlay');
  if (!overlay) return;
  if (overlay.classList.contains('open')) { closeUserPanel(); }
  else { openUserPanel(); }
}
function openUserPanel(){
  const overlay = document.getElementById('userPanelOverlay');
  if (!overlay || !gCurrentUser) return;
  // Fill header
  const name = gCurrentUser.name || gCurrentUser.email;
  document.getElementById('upName').textContent  = name;
  document.getElementById('upEmail').textContent = gCurrentUser.email;
  const initEl   = document.getElementById('upAvatarInit');
  const avatarEl = document.getElementById('upAvatar');
  initEl.textContent = name.charAt(0).toUpperCase();
  if (gCurrentUser.picture) {
    avatarEl.src = gCurrentUser.picture;
    avatarEl.style.display = 'block';
    initEl.style.display   = 'none';
  } else {
    avatarEl.style.display = 'none';
    initEl.style.display   = 'flex';
  }
  overlay.classList.add('open');
  document.body.style.overflow = 'hidden';
  if (!gUserOrders.length) loadUserOrders();
  else renderTab(gActiveTab);
}
function closeUserPanel(){
  const overlay = document.getElementById('userPanelOverlay');
  if (overlay) overlay.classList.remove('open');
  document.body.style.overflow = '';
}
function handleOverlayClick(e){
  if (e.target === document.getElementById('userPanelOverlay')) closeUserPanel();
}
function switchTab(tab){
  gActiveTab = tab;
  document.getElementById('tabTrx').classList.toggle('active', tab==='trx');
  document.getElementById('tabLic').classList.toggle('active', tab==='lic');
  renderTab(tab);
}

function reloadOrders(){
  gUserOrders = [];
  loadUserOrders();
}

function loadUserOrders(){
  if (!gCurrentUser) return;
  const body = document.getElementById('upBody');
  if (body) body.innerHTML = '<div class="up-loading"><i class="fa-solid fa-spinner fa-spin" style="margin-right:6px"></i> Memuat...</div>';

  fetch('api.php?action=get_user_orders', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      credential: gCurrentUser.credential || '',
      email: gCurrentUser.email,
      sub:   gCurrentUser.sub || ''
    })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      gUserOrders = data.orders || [];
      renderTab(gActiveTab);
    } else {
      if (body) body.innerHTML = '<div class="up-empty"><i class="fa-solid fa-triangle-exclamation"></i><p>' + (data.message||'Gagal memuat') + '</p></div>';
    }
  })
  .catch(() => {
    if (body) body.innerHTML = '<div class="up-empty"><i class="fa-solid fa-wifi"></i><p>Tidak bisa terhubung ke server</p></div>';
  });
}

function renderTab(tab){
  const body = document.getElementById('upBody');
  if (!body) return;

  if (!gUserOrders.length) {
    body.innerHTML = '<div class="up-empty"><i class="fa-solid fa-box-open"></i><p>Belum ada riwayat transaksi.<br>Beli produk & login dengan email yang sama.</p></div>';
    return;
  }

  if (tab === 'trx') {
    // Transaction history - all orders
    body.innerHTML = '';
    gUserOrders.forEach(o => {
      const d = new Date(o.created_at * 1000);
      const dateStr = d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});
      const statusClass = {'completed':'completed','pending':'pending','failed':'failed','cancelled':'cancelled'}[o.status] || 'pending';
      const statusLabel = {'completed':'Selesai','pending':'Menunggu','failed':'Gagal','cancelled':'Batal'}[o.status] || o.status;
      const card = document.createElement('div');
      card.className = 'order-card';
      card.innerHTML =
        '<div class="oc-top">' +
          '<span class="oc-id">' + o.order_id + '</span>' +
          '<span class="oc-status ' + statusClass + '">' + statusLabel + '</span>' +
        '</div>' +
        '<div class="oc-product">' + (o.product||'-') + '</div>' +
        '<div class="oc-item"><i class="fa-regular fa-calendar" style="margin-right:4px;opacity:.6"></i>' + (o.item||'-') + '</div>' +
        '<div class="oc-meta">' +
          '<span><i class="fa-regular fa-clock" style="margin-right:3px"></i>' + dateStr + '</span>' +
          '<span class="oc-amount">Rp ' + Number(o.amount||0).toLocaleString('id-ID') + '</span>' +
        '</div>';
      body.appendChild(card);
    });

  } else {
    // License tab - only completed orders with license key
    const completed = gUserOrders.filter(o => o.status === 'completed' && o.product_content && o.product_content !== 'Menunggu Pembayaran...');
    if (!completed.length) {
      body.innerHTML = '<div class="up-empty"><i class="fa-solid fa-key"></i><p>Belum ada license aktif.<br>License muncul setelah pembayaran selesai.</p></div>';
      return;
    }
    body.innerHTML = '';
    completed.forEach(o => {
      const card = document.createElement('div');
      card.className = 'order-card';
      const licId = 'lic_' + Math.random().toString(36).slice(2);
      card.innerHTML =
        '<div class="oc-top">' +
          '<span class="oc-id">' + o.order_id + '</span>' +
          '<span class="oc-status completed">Aktif</span>' +
        '</div>' +
        '<div class="oc-product">' + (o.product||'-') + '</div>' +
        '<div class="oc-item"><i class="fa-regular fa-calendar" style="margin-right:4px;opacity:.6"></i>' + (o.item||'-') + '</div>' +
        '<div class="oc-license">' +
          '<div class="oc-license-label"><i class="fa-solid fa-key"></i> License Key</div>' +
          '<div class="oc-license-key">' +
            '<span id="' + licId + '">' + escHtml(o.product_content) + '</span>' +
            '<button class="oc-copy-btn" onclick="copyLicense('' + licId + '', this)"><i class="fa-regular fa-copy"></i> Copy</button>' +
          '</div>' +
        '</div>';
      body.appendChild(card);
    });
  }
}

function escHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function copyLicense(id, btn){
  const el = document.getElementById(id);
  if (!el) return;
  navigator.clipboard.writeText(el.textContent.trim()).then(()=>{
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
    btn.style.color = '#22C55E';
    setTimeout(()=>{ btn.innerHTML = '<i class="fa-regular fa-copy"></i> Copy'; btn.style.color = ''; }, 2000);
  }).catch(()=>{
    const t = document.createElement('textarea');
    t.value = el.textContent.trim(); document.body.appendChild(t); t.select(); document.execCommand('copy'); document.body.removeChild(t);
    btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
    setTimeout(()=>{ btn.innerHTML = '<i class="fa-regular fa-copy"></i> Copy'; }, 2000);
  });
}

function signOutGoogle(){
  gCurrentUser = null;
  gUserOrders  = [];
  localStorage.removeItem('gUser');
  if (window.google && google.accounts && google.accounts.id) {
    google.accounts.id.disableAutoSelect();
  }
  closeUserPanel();
  showLoggedOutState();
}
</script>
</body>
</html>
