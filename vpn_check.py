"""
vpn_check.py  —  FastAPI replacement for vpn_check.php
Mirrors endpoint: GET /vpn_check.php?t=<timestamp>
Returns JSON: { "blocked": bool, "reason": str }

This module is mounted into the main api.py app under /vpn_check
OR can be run standalone:
    uvicorn vpn_check:app --host 0.0.0.0 --port 8001

If running standalone, update your PHP/JS from:
    fetch('vpn_check.php?t='+Date.now())
to:
    fetch('/vpn_check/?t='+Date.now())
"""

from __future__ import annotations

import asyncio
import hashlib
import ipaddress
import json
import time
from pathlib import Path

import httpx
from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse

# ─────────────────────────────────────────────────────────────────────────────
# Paths
# ─────────────────────────────────────────────────────────────────────────────
BASE_DIR    = Path(__file__).parent
DATA_DIR    = BASE_DIR / "data"
CONFIG_FILE = DATA_DIR / "config.json"
CACHE_DIR   = DATA_DIR / "vpn_cache"

CACHE_DIR.mkdir(parents=True, exist_ok=True)
CACHE_TTL   = 7200   # 2 hours


# ─────────────────────────────────────────────────────────────────────────────
# Config
# ─────────────────────────────────────────────────────────────────────────────
def load_config() -> dict:
    defaults = {"vpn_block": False, "vpn_api_key": ""}
    if CONFIG_FILE.exists():
        try:
            saved = json.loads(CONFIG_FILE.read_text())
            if isinstance(saved, dict):
                defaults.update(saved)
        except Exception:
            pass
    return defaults


# ─────────────────────────────────────────────────────────────────────────────
# IP helpers
# ─────────────────────────────────────────────────────────────────────────────
def get_real_ip(request: Request) -> str:
    for header in ("cf-connecting-ip", "x-real-ip", "x-forwarded-for"):
        val = request.headers.get(header)
        if val:
            ip = val.split(",")[0].strip()
            if ip:
                return ip
    return request.client.host or "0.0.0.0"


def is_private_ip(ip_str: str) -> bool:
    """Return True for loopback / private / link-local ranges."""
    try:
        addr = ipaddress.ip_address(ip_str)
        return (
            addr.is_loopback
            or addr.is_private
            or addr.is_link_local
            or addr.is_reserved
        )
    except ValueError:
        return False


# ─────────────────────────────────────────────────────────────────────────────
# Cache helpers
# ─────────────────────────────────────────────────────────────────────────────
def _cache_key_path(ip: str) -> Path:
    return CACHE_DIR / f"{hashlib.md5(ip.encode()).hexdigest()}.json"


def read_cache(ip: str) -> dict | None:
    path = _cache_key_path(ip)
    if not path.exists():
        return None
    if time.time() - path.stat().st_mtime > CACHE_TTL:
        return None
    try:
        return json.loads(path.read_text())
    except Exception:
        return None


def write_cache(ip: str, result: dict) -> None:
    try:
        _cache_key_path(ip).write_text(json.dumps(result))
    except Exception:
        pass


# ─────────────────────────────────────────────────────────────────────────────
# Proxycheck.io lookup
# ─────────────────────────────────────────────────────────────────────────────
VPN_TYPES = {"vpn", "tor", "web proxy", "public proxy", "hosting"}
RISK_THRESHOLD = 66


async def check_ip_via_proxycheck(ip: str, api_key: str) -> dict:
    """Query proxycheck.io and return result dict."""
    base = f"https://proxycheck.io/v2/{ip}"
    params = "vpn=1&risk=1" + (f"&key={api_key}&asn=1" if api_key else "")
    url = f"{base}?{params}"

    result: dict = {"blocked": False, "reason": "ok", "ip": ip}

    try:
        async with httpx.AsyncClient(timeout=5) as client:
            r = await client.get(url)
            data = r.json()
    except Exception:
        return result  # fail open — allow on timeout

    entry = data.get(ip)
    if not entry or not isinstance(entry, dict):
        return result

    is_proxy = entry.get("proxy", "no") == "yes"
    ip_type  = entry.get("type", "").lower()
    is_vpn   = ip_type in VPN_TYPES
    risk     = int(entry.get("risk", 0))

    if is_proxy or is_vpn or risk >= RISK_THRESHOLD:
        result = {
            "blocked": True,
            "reason":  entry.get("type") or ("Proxy" if is_proxy else "VPN"),
            "risk":    risk,
            "ip":      ip,
        }

    return result


# ─────────────────────────────────────────────────────────────────────────────
# FastAPI app
# ─────────────────────────────────────────────────────────────────────────────
app = FastAPI(title="VPN Check", docs_url=None, redoc_url=None)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["GET"],
    allow_headers=["*"],
)


@app.get("/")
async def vpn_check(request: Request):
    cfg = load_config()

    # Feature disabled → always allow
    if not cfg.get("vpn_block"):
        return JSONResponse({"blocked": False, "reason": "disabled"})

    ip = get_real_ip(request)

    # Whitelist local / private IPs (dev / internal)
    if is_private_ip(ip):
        return JSONResponse({"blocked": False, "reason": "local"})

    # Check cache first
    cached = read_cache(ip)
    if cached is not None:
        return JSONResponse(cached)

    # Live lookup
    result = await check_ip_via_proxycheck(ip, cfg.get("vpn_api_key", ""))

    # Cache and return
    write_cache(ip, result)
    return JSONResponse(result)
