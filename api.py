"""
api.py  —  FastAPI replacement for api.php
Requires: fastapi, uvicorn[standard], httpx, aiofiles

Install:
    pip install fastapi "uvicorn[standard]" httpx aiofiles python-multipart

Run (development):
    uvicorn api:app --host 0.0.0.0 --port 8000 --reload

Run (production):
    uvicorn api:app --host 0.0.0.0 --port 8000 --workers 4

Reverse-proxy (Nginx) example  ─  add inside your server {} block:
    location /api/ {
        proxy_pass         http://127.0.0.1:8000/;
        proxy_set_header   Host              $host;
        proxy_set_header   X-Real-IP         $remote_addr;
        proxy_set_header   X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
    }

Then update every  fetch('api.php?...')  call in your PHP/HTML to
    fetch('/api/?...')

Fitur Baru:
    1. Rate Limiter Per-Endpoint (rate_limiter.py) — batas berbeda tiap action
    2. Email Notifikasi Pembeli (email_notifier.py) — konfirmasi via SMTP
    3. CAPTCHA Verifier (captcha_verifier.py) — Turnstile & reCAPTCHA
"""

from __future__ import annotations

import asyncio
import fcntl
import glob
import hashlib
import hmac
import json
import logging
import os
import re
import secrets
import time
from collections import defaultdict
from pathlib import Path
from typing import Any

import httpx
from fastapi import FastAPI, HTTPException, Request, Response
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse

# ── Modul baru ────────────────────────────────────────────────────────────────
from rate_limiter import (
    check_rate_limit as _smart_rate_limit,
    cleanup_old_entries as _rl_cleanup,
    get_stats as _rl_stats,
    add_to_whitelist as _rl_whitelist_add,
)
from email_notifier import send_order_confirmation, send_test_email
from captcha_verifier import verify_captcha, should_verify_captcha

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(name)s] %(levelname)s: %(message)s")

# ─────────────────────────────────────────────────────────────────────────────
# Paths  (same directory layout as the PHP project)
# ─────────────────────────────────────────────────────────────────────────────
BASE_DIR   = Path(__file__).parent
DATA_DIR   = BASE_DIR / "data"
CONFIG_FILE   = DATA_DIR / "config.json"
PRODUCTS_FILE = DATA_DIR / "products.json"
ORDER_DIR     = DATA_DIR / "orders"
STOCK_DIR     = DATA_DIR / "stocks"
LOCK_FILE     = DATA_DIR / "orders.lock"
SESSION_DIR   = DATA_DIR / "sessions"

for d in (DATA_DIR, ORDER_DIR, STOCK_DIR, SESSION_DIR):
    d.mkdir(parents=True, exist_ok=True)

# ─────────────────────────────────────────────────────────────────────────────
# Rate limiter — sekarang dikelola oleh rate_limiter.py (per-endpoint)
# ─────────────────────────────────────────────────────────────────────────────
# Import sudah dilakukan di atas. Konstanta lama dihapus.


# ─────────────────────────────────────────────────────────────────────────────
# Session helpers  (file-based, compatible with PHP session structure)
# ─────────────────────────────────────────────────────────────────────────────
SESSION_TTL = 7200  # 2 hours


def _session_file(sid: str) -> Path:
    safe = re.sub(r"[^a-zA-Z0-9]", "", sid)
    return SESSION_DIR / f"{safe}.json"


def load_session(sid: str) -> dict:
    f = _session_file(sid)
    if f.exists():
        try:
            data = json.loads(f.read_text())
            if time.time() - data.get("_ts", 0) < SESSION_TTL:
                return data
        except Exception:
            pass
    return {}


def save_session(sid: str, data: dict) -> None:
    data["_ts"] = time.time()
    _session_file(sid).write_text(json.dumps(data))


def get_session_id(request: Request) -> str:
    return request.cookies.get("session_id", "")


def is_admin(request: Request) -> bool:
    sid = get_session_id(request)
    if not sid:
        return False
    sess = load_session(sid)
    return bool(sess.get("admin_logged_in"))


# ─────────────────────────────────────────────────────────────────────────────
# Config
# ─────────────────────────────────────────────────────────────────────────────
DEFAULT_CONFIG: dict[str, Any] = {
    "title":               "WilzXiterz",
    "whatsapp":            "6285173360622",
    "telegram":            "WilzXiterzVN",
    "channel":             "",
    "discord":             "",
    "maintenance":         False,
    "download_url":        "",
    "pakasir_merchant_id": "",
    "pakasir_api_key":     "",
    "binance_api_key":     "",
    "binance_secret_key":  "",
    "binance_merchant_id": "",
    "admin_password":      "",
    "google_client_id":    "",
    "vpn_block":           False,
    "vpn_api_key":         "",
    # ── SMTP Email Notifikasi ─────────────────────────────────────────────────
    "smtp_enabled":        False,
    "smtp_host":           "smtp.gmail.com",
    "smtp_port":           587,
    "smtp_user":           "",
    "smtp_password":       "",
    "smtp_from_name":      "",
    # ── CAPTCHA ───────────────────────────────────────────────────────────────
    "captcha_provider":         "",   # "turnstile" | "recaptcha" | ""
    "turnstile_secret":         "",   # Cloudflare Turnstile secret key
    "recaptcha_secret":         "",   # Google reCAPTCHA secret key
    "recaptcha_v3_min_score":   0.5,  # Minimum score untuk reCAPTCHA v3
    "captcha_required_actions": ["checkout", "create_order", "admin_login"],
}


def load_config() -> dict:
    cfg = dict(DEFAULT_CONFIG)
    if CONFIG_FILE.exists():
        try:
            saved = json.loads(CONFIG_FILE.read_text())
            if isinstance(saved, dict):
                cfg.update(saved)
        except Exception:
            pass
    return cfg


def save_config(cfg: dict) -> None:
    CONFIG_FILE.write_text(json.dumps(cfg, indent=4, ensure_ascii=False))


# ─────────────────────────────────────────────────────────────────────────────
# Products I/O
# ─────────────────────────────────────────────────────────────────────────────
def load_products() -> list[dict]:
    if not PRODUCTS_FILE.exists():
        return []
    try:
        return json.loads(PRODUCTS_FILE.read_text()) or []
    except Exception:
        return []


def save_products(products: list[dict]) -> None:
    PRODUCTS_FILE.write_text(json.dumps(products, indent=4, ensure_ascii=False))


# ─────────────────────────────────────────────────────────────────────────────
# Order I/O  (per-file, same format as PHP)
# ─────────────────────────────────────────────────────────────────────────────
def _order_file(order_id: str) -> Path:
    safe = re.sub(r"[^A-Za-z0-9\-\.]", "", order_id)
    return ORDER_DIR / f"{safe}.json"


def load_order(order_id: str) -> dict | None:
    f = _order_file(order_id)
    if not f.exists():
        return None
    try:
        return json.loads(f.read_text())
    except Exception:
        return None


def save_order(order: dict) -> None:
    _order_file(order["order_id"]).write_text(
        json.dumps(order, indent=4, ensure_ascii=False)
    )


def _safe_int(val, default: int = 0) -> int:
    """Safely convert a value to int, returning default on failure."""
    try:
        return int(val)
    except (TypeError, ValueError):
        return default


def load_all_orders() -> list[dict]:
    orders = []
    for f in ORDER_DIR.glob("*.json"):
        try:
            d = json.loads(f.read_text())
            if d and "order_id" in d:
                orders.append(d)
        except Exception:
            pass
    return orders


# ─────────────────────────────────────────────────────────────────────────────
# Stock I/O  (file-locked, same format as PHP)
# ─────────────────────────────────────────────────────────────────────────────
_stock_lock = asyncio.Lock()


async def pop_license_key(stock_id: str) -> str:
    stock_file = STOCK_DIR / f"{stock_id}.json"
    async with _stock_lock:
        loop = asyncio.get_event_loop()
        return await loop.run_in_executor(None, _pop_key_sync, stock_file)


def _pop_key_sync(stock_file: Path) -> str:
    key = "STOK_KOSONG_HUBUNGI_ADMIN"
    lock_path = str(LOCK_FILE)
    fp = open(lock_path, "a")
    try:
        fcntl.flock(fp, fcntl.LOCK_EX)
        if stock_file.exists():
            stocks: list = json.loads(stock_file.read_text()) or []
            if stocks:
                key = stocks.pop(0)
                stock_file.write_text(json.dumps(stocks, indent=4))
    finally:
        fcntl.flock(fp, fcntl.LOCK_UN)
        fp.close()
    return key


def get_stock_count(stock_id: str) -> int:
    stock_file = STOCK_DIR / f"{stock_id}.json"
    if not stock_file.exists():
        return 0
    try:
        stocks = json.loads(stock_file.read_text())
        return len(stocks) if isinstance(stocks, list) else 0
    except Exception:
        return 0


# ─────────────────────────────────────────────────────────────────────────────
# Complete Order  (idempotent)
# ─────────────────────────────────────────────────────────────────────────────
async def complete_order(order_id: str) -> str | None:
    order = load_order(order_id)
    if not order:
        return None
    if order.get("status") == "completed":
        return order.get("product_content", "HUBUNGI CS")

    key = await pop_license_key(order.get("stock_id", ""))
    order["status"]          = "completed"
    order["product_content"] = key
    order["completed_at"]    = time.strftime("%Y-%m-%d %H:%M:%S")
    save_order(order)

    # ── Kirim email konfirmasi ke pembeli (non-blocking) ─────────────────────
    try:
        cfg = load_config()
        asyncio.create_task(send_order_confirmation(order, cfg))
    except Exception as _email_exc:
        pass  # Jangan sampai gagal email membatalkan order

    return key


# ─────────────────────────────────────────────────────────────────────────────
# Google JWT helper  (no external library — manual base64url decode)
# ─────────────────────────────────────────────────────────────────────────────
def decode_google_jwt(credential: str) -> dict | None:
    """Decode (NOT cryptographically verify) a Google ID token payload."""
    try:
        parts = credential.split(".")
        if len(parts) != 3:
            return None
        pad = parts[1] + "=" * ((-len(parts[1])) % 4)
        import base64
        payload = json.loads(base64.urlsafe_b64decode(pad))
        return payload
    except Exception:
        return None


def verify_google_token(credential: str, google_sub: str, google_email: str,
                         client_id: str) -> tuple[str, str]:
    """Returns (verified_sub, verified_email) or ('', '') on failure."""
    payload = decode_google_jwt(credential)
    if not payload:
        return "", ""
    if client_id and payload.get("aud") != client_id:
        return "", ""
    if payload.get("sub") != google_sub:
        return "", ""
    email = payload.get("email", google_email).strip().lower()
    return google_sub, email


# ─────────────────────────────────────────────────────────────────────────────
# Real IP helper
# ─────────────────────────────────────────────────────────────────────────────
def get_real_ip(request: Request) -> str:
    for header in ("cf-connecting-ip", "x-real-ip", "x-forwarded-for"):
        val = request.headers.get(header)
        if val:
            ip = val.split(",")[0].strip()
            if ip:
                return ip
    return request.client.host or "0.0.0.0"


# ─────────────────────────────────────────────────────────────────────────────
# FastAPI app
# ─────────────────────────────────────────────────────────────────────────────
app = FastAPI(title="WilzXiterz API", docs_url=None, redoc_url=None)

# CORS hanya ditambahkan jika api.py dijalankan standalone (bukan di-mount via main_app.py)
# Saat di-mount, main_app.py sudah menangani CORS sehingga tidak perlu duplikat.
import os as _os
if _os.environ.get("STANDALONE_API", "0") == "1":
    app.add_middleware(
        CORSMiddleware,
        allow_origins=["*"],
        allow_methods=["GET", "POST"],
        allow_headers=["Content-Type"],
    )


# ── Per-endpoint rate-limit middleware ───────────────────────────────────────
@app.middleware("http")
async def rate_limit_middleware(request: Request, call_next):
    ip     = get_real_ip(request)
    action = request.query_params.get("action", "_default")

    allowed, rl_headers = await _smart_rate_limit(ip, action)
    if not allowed:
        return JSONResponse(
            status_code=429,
            content={"success": False, "message": "Terlalu banyak permintaan. Coba lagi sebentar."},
            headers={**rl_headers, "Retry-After": rl_headers.get("Retry-After", "60")},
        )

    response = await call_next(request)

    # Tambahkan header X-RateLimit-* ke semua response
    for key, val in rl_headers.items():
        response.headers[key] = val

    return response


# ─────────────────────────────────────────────────────────────────────────────
# ROUTES
# ─────────────────────────────────────────────────────────────────────────────

@app.get("/")
@app.post("/")
async def root(request: Request, action: str = ""):
    """
    Main dispatcher — mirrors the PHP switch($action).
    GET  actions: get_products, get_products_admin, get_recent_orders,
                  check_status, get_orders, webhook (GET fallback)
    POST actions: checkout, create_order, webhook, binance_webhook,
                  save_product, delete_product, add_licenses,
                  complete_order, get_user_orders, admin_login, save_config
    """
    cfg = load_config()

    # ── GET_PRODUCTS ──────────────────────────────────────────────────────────
    if action == "get_products":
        products = load_products()
        result = []
        for p in products:
            pp = dict(p)
            pp.setdefault("prices", [])
            for idx, pr in enumerate(pp["prices"]):
                stock_id = f"{pp.get('id', '')}_{idx}"
                pr["stock"] = get_stock_count(stock_id)
                pr.pop("licenses", None)
            result.append(pp)
        return JSONResponse(result)

    # ── GET_PRODUCTS_ADMIN ────────────────────────────────────────────────────
    if action == "get_products_admin":
        if not is_admin(request):
            raise HTTPException(403, detail={"success": False, "message": "Unauthorized"})
        products = load_products()
        result = []
        for p in products:
            pp = dict(p)
            pp.setdefault("prices", [])
            for idx, pr in enumerate(pp["prices"]):
                stock_id = f"{pp.get('id', '')}_{idx}"
                pr["stock"] = get_stock_count(stock_id)
                stock_file = STOCK_DIR / f"{stock_id}.json"
                try:
                    pr["licenses"] = json.loads(stock_file.read_text()) if stock_file.exists() else []
                except Exception:
                    pr["licenses"] = []
            result.append(pp)
        return JSONResponse(result)

    # ── GET_RECENT_ORDERS ─────────────────────────────────────────────────────
    if action == "get_recent_orders":
        orders = load_all_orders()
        orders.sort(key=lambda o: _safe_int(o.get("created_at"), 0), reverse=True)
        recent = []
        for o in orders:
            if o.get("status") == "completed":
                recent.append({
                    "name":       o.get("name", "Guest"),
                    "product":    o.get("product", ""),
                    "item":       o.get("item", ""),
                    "created_at": _safe_int(o.get("created_at"), int(time.time())),
                })
                if len(recent) >= 10:
                    break
        return JSONResponse(recent)

    # ── CHECK_STATUS ──────────────────────────────────────────────────────────
    if action == "check_status":
        order_id = request.query_params.get("order_id", "")
        if not order_id:
            return JSONResponse({"status": "not_found"})

        order = load_order(order_id)
        if not order:
            return JSONResponse({"status": "not_found"})

        if order.get("status") == "completed":
            return JSONResponse({
                "status":          "completed",
                "product_content": order.get("product_content", "HUBUNGI CS"),
            })

        pay_method = order.get("payment_method", "qris")

        async with httpx.AsyncClient(timeout=20) as client:
            if pay_method == "binance":
                gateway_ref = order.get("gateway_ref", "")
                if not gateway_ref:
                    return JSONResponse({"status": "pending"})

                api_key    = cfg.get("binance_api_key", "")
                secret_key = cfg.get("binance_secret_key", "")
                ts         = int(time.time() * 1000)
                nonce      = secrets.token_hex(16)
                payload    = json.dumps({"prepayId": gateway_ref})
                sign_str   = f"{ts}\n{nonce}\n{payload}\n"
                signature  = hmac.new(
                    secret_key.encode(), sign_str.encode(), "sha512"
                ).hexdigest().upper()

                try:
                    r = await client.post(
                        "https://bpay.binanceapi.com/binancepay/openapi/v3/order/query",
                        content=payload,
                        headers={
                            "Content-Type":             "application/json",
                            "BinancePay-Timestamp":     str(ts),
                            "BinancePay-Nonce":         nonce,
                            "BinancePay-Certificate-SN": api_key,
                            "BinancePay-Signature":     signature,
                        },
                    )
                    resp = r.json()
                except Exception:
                    return JSONResponse({"status": "pending"})

                bnb_status = resp.get("data", {}).get("status", "")
                if bnb_status in ("PAID", "SETTLED"):
                    key = await complete_order(order_id)
                    return JSONResponse({
                        "status":          "completed",
                        "product_content": key or "HUBUNGI CS",
                    })
                return JSONResponse({"status": "pending"})

            else:  # pakasir
                slug    = cfg.get("pakasir_merchant_id", "")
                api_key = cfg.get("pakasir_api_key", "")
                url = (
                    "https://app.pakasir.com/api/transactiondetail"
                    f"?project={slug}&order_id={order_id}&api_key={api_key}"
                )
                try:
                    r = await client.get(url, headers={"Accept": "application/json"})
                    resp = r.json()
                except Exception:
                    return JSONResponse({
                        "status":          "pending",
                        "product_content": order.get("product_content", "Menunggu Pembayaran..."),
                    })

                tx_status = (
                    resp.get("transaction", {}).get("status")
                    or resp.get("data", {}).get("status")
                    or resp.get("status", "")
                ).lower()

                if tx_status in ("paid", "success", "completed", "settlement"):
                    key = await complete_order(order_id)
                    return JSONResponse({
                        "status":          "completed",
                        "product_content": key or "HUBUNGI CS",
                    })
                return JSONResponse({
                    "status":          "pending",
                    "product_content": order.get("product_content", "Menunggu Pembayaran..."),
                })

    # ── GET_ORDERS (admin) ────────────────────────────────────────────────────
    if action == "get_orders":
        if not is_admin(request):
            raise HTTPException(403, detail={"success": False, "message": "Unauthorized"})
        orders = load_all_orders()
        orders.sort(key=lambda o: _safe_int(o.get("created_at"), 0), reverse=True)
        return JSONResponse(orders)

    # ── GET_CONFIG (admin) ────────────────────────────────────────────────────
    if action == "get_config":
        if not is_admin(request):
            raise HTTPException(403, detail={"success": False, "message": "Unauthorized"})
        safe = dict(cfg)
        safe.pop("admin_password", None)
        return JSONResponse({"success": True, "config": safe})

    # ─────────────────────────────────────────────────────────────────────────
    # POST-only actions — parse body
    # ─────────────────────────────────────────────────────────────────────────
    body: dict = {}
    if request.method == "POST":
        try:
            body = await request.json()
        except Exception:
            body = {}

    # ── CHECKOUT / CREATE_ORDER ───────────────────────────────────────────────
    if action in ("checkout", "create_order"):
        if not body:
            return JSONResponse({"success": False, "message": "Invalid input"})

        # ── Verifikasi CAPTCHA (jika dikonfigurasi) ───────────────────────────
        if should_verify_captcha(action, cfg):
            captcha_token = (
                body.get("cf_turnstile_token")
                or body.get("g_recaptcha_token")
                or body.get("captcha_token")
                or ""
            )
            ip = get_real_ip(request)
            captcha_ok, captcha_msg = await verify_captcha(captcha_token, cfg, ip, action)
            if not captcha_ok:
                return JSONResponse({"success": False, "message": f"Verifikasi CAPTCHA gagal: {captcha_msg}"})

        product_id     = body.get("product_id", "")
        item_index     = int(body.get("item_index", 0))
        customer_name  = body.get("name") or body.get("customer_name") or "Guest"
        customer_phone = body.get("phone") or body.get("customer_phone") or ""
        amount         = int(body.get("amount", 0))
        pay_method     = body.get("payment_method", "qris")

        # Google login
        google_sub        = body.get("google_sub", "")
        google_email      = body.get("google_email", "").strip().lower()
        google_credential = body.get("google_credential", "")
        if google_credential and google_sub:
            google_sub, google_email = verify_google_token(
                google_credential, google_sub, google_email,
                cfg.get("google_client_id", "")
            )

        if amount <= 0:
            return JSONResponse({"success": False, "message": "Invalid amount"})

        products = load_products()
        product  = next((p for p in products if str(p.get("id")) == str(product_id)), None)
        if not product:
            return JSONResponse({"success": False, "message": "Product not found"})

        prices = product.get("prices", [])
        if item_index >= len(prices):
            return JSONResponse({"success": False, "message": "Item not found"})
        item = prices[item_index]

        stock_id = f"{product_id}_{item_index}"
        prefix   = (product.get("name", "XX")[:2]).upper()
        rand4    = str(secrets.randbelow(9000) + 1000)
        order_id = body.get("order_id") or f"ORD-{prefix}.{rand4}.{int(time.time()*1000)}"

        async with httpx.AsyncClient(timeout=30) as client:
            if pay_method == "binance":
                api_key     = cfg.get("binance_api_key", "")
                secret_key  = cfg.get("binance_secret_key", "")
                merchant_id = cfg.get("binance_merchant_id", "")
                if not api_key or not secret_key or not merchant_id:
                    return JSONResponse({"success": False, "message": "Binance Pay belum dikonfigurasi di Admin Panel"})

                usdt_amount = max(round(amount / 18000, 2), 0.01)
                ts          = int(time.time() * 1000)
                nonce       = secrets.token_hex(16)
                scheme      = request.url.scheme
                host        = request.headers.get("host", "")
                base_url    = f"{scheme}://{host}"
                payload_dict = {
                    "env":              {"terminalType": "WEB"},
                    "merchantTradeNo":  order_id,
                    "orderAmount":      f"{usdt_amount:.8f}",
                    "currency":         "USDT",
                    "description":      f"{product['name']} - {item.get('label', '')}",
                    "goodsDetails": [{
                        "goodsType":        "02",
                        "goodsCategory":    "Z000",
                        "referenceGoodsId": product_id,
                        "goodsName":        product["name"],
                        "goodsUnitAmount":  {"currency": "USDT", "amount": f"{usdt_amount:.8f}"},
                        "goodsQuantity":    "1",
                        "goodsDetail":      item.get("label", ""),
                    }],
                    "returnUrl": f"{base_url}/detail.php",
                    "cancelUrl": f"{base_url}/detail.php",
                }
                payload_str  = json.dumps(payload_dict)
                sign_str     = f"{ts}\n{nonce}\n{payload_str}\n"
                signature    = hmac.new(secret_key.encode(), sign_str.encode(), "sha512").hexdigest().upper()

                try:
                    r = await client.post(
                        "https://bpay.binanceapi.com/binancepay/openapi/v3/order",
                        content=payload_str,
                        headers={
                            "Content-Type":              "application/json",
                            "BinancePay-Timestamp":      str(ts),
                            "BinancePay-Nonce":          nonce,
                            "BinancePay-Certificate-SN": api_key,
                            "BinancePay-Signature":      signature,
                        },
                    )
                    resp = r.json()
                except Exception as e:
                    return JSONResponse({"success": False, "message": f"Request error: {e}"})

                if resp.get("status") != "SUCCESS":
                    return JSONResponse({
                        "success": False,
                        "message": resp.get("errorMessage") or resp.get("message") or "Gagal membuat order Binance",
                    })

                order = {
                    "order_id":        order_id,
                    "amount":          amount,
                    "name":            customer_name,
                    "phone":           customer_phone,
                    "product":         product["name"],
                    "item":            item.get("label", ""),
                    "product_id":      product_id,
                    "stock_id":        stock_id,
                    "payment_method":  "binance",
                    "gateway_ref":     resp.get("data", {}).get("prepayId", ""),
                    "checkout_url":    resp.get("data", {}).get("checkoutUrl", ""),
                    "usdt_amount":     f"{usdt_amount:.2f}",
                    "status":          "pending",
                    "product_content": "Menunggu Pembayaran...",
                    "created_at":      int(time.time()),
                    "google_sub":      google_sub,
                    "google_email":    google_email,
                    "email":           google_email,
                }
                save_order(order)
                return JSONResponse({
                    "success":      True,
                    "order_id":     order_id,
                    "checkout_url": order["checkout_url"],
                    "usdt_amount":  order["usdt_amount"],
                })

            else:  # pakasir QRIS
                slug    = cfg.get("pakasir_merchant_id", "")
                api_key = cfg.get("pakasir_api_key", "")
                if not slug or not api_key:
                    return JSONResponse({"success": False, "message": "QRIS belum dikonfigurasi di Admin Panel"})

                pakasir_payload = {"project": slug, "order_id": order_id, "amount": amount, "api_key": api_key}
                try:
                    r = await client.post(
                        "https://app.pakasir.com/api/transactioncreate/qris",
                        json=pakasir_payload,
                        headers={"Content-Type": "application/json", "User-Agent": "WilzXiterz/2.0"},
                    )
                    resp = r.json()
                except Exception as e:
                    return JSONResponse({"success": False, "message": f"Gagal menghubungi Pakasir: {e}"})

                if r.status_code >= 500:
                    return JSONResponse({"success": False, "message": f"Server Pakasir error (HTTP {r.status_code}). Coba lagi."})
                if "payment" not in resp:
                    return JSONResponse({
                        "success": False,
                        "message": "Gagal membuat order QRIS: " + (resp.get("message") or str(resp)),
                    })

                qr_string = resp["payment"].get("payment_number", "")
                qr_url    = (
                    f"https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={qr_string}"
                    if qr_string else ""
                )

                order = {
                    "order_id":        order_id,
                    "amount":          amount,
                    "name":            customer_name,
                    "phone":           customer_phone,
                    "product":         product["name"],
                    "item":            item.get("label", ""),
                    "product_id":      product_id,
                    "stock_id":        stock_id,
                    "payment_method":  "qris",
                    "gateway_ref":     order_id,
                    "status":          "pending",
                    "product_content": "Menunggu Pembayaran...",
                    "created_at":      int(time.time()),
                    "google_sub":      google_sub,
                    "google_email":    google_email,
                    "email":           google_email,
                }
                save_order(order)
                return JSONResponse({
                    "success":      True,
                    "order_id":     order_id,
                    "qr_url":       qr_url,
                    "qr_image_url": qr_url,
                })

    # ── WEBHOOK PAKASIR ───────────────────────────────────────────────────────
    if action in ("webhook", "pakasir_webhook"):
        data     = body or {}
        order_id = data.get("order_id", "")
        status   = data.get("status", "").lower()
        if order_id and status in ("completed", "paid", "success", "settlement"):
            await complete_order(order_id)
        return JSONResponse({"success": True})

    # ── WEBHOOK BINANCE ───────────────────────────────────────────────────────
    if action == "binance_webhook":
        biz_str  = body.get("bizContent", "")
        try:
            biz_data = json.loads(biz_str) if isinstance(biz_str, str) else biz_str
        except Exception:
            biz_data = {}
        prepay_id = biz_data.get("prepayId", "")
        status    = biz_data.get("transactionStatus", "")
        if prepay_id and status == "PAY_SUCCESS":
            for f in ORDER_DIR.glob("*.json"):
                try:
                    o = json.loads(f.read_text())
                    if o.get("gateway_ref") == prepay_id:
                        await complete_order(o["order_id"])
                        break
                except Exception:
                    pass
        return JSONResponse({"returnCode": "SUCCESS", "returnMessage": None})

    # ── SAVE_PRODUCT ──────────────────────────────────────────────────────────
    if action == "save_product":
        if not is_admin(request):
            raise HTTPException(403, detail={"success": False, "message": "Unauthorized"})
        if not body:
            return JSONResponse({"success": False, "message": "Invalid input"})

        products = load_products()
        prod_id  = body.get("id")
        if not prod_id:
            prod_id   = f"prod_{int(time.time()*1000)}"
            body["id"] = prod_id

        # Save stock files separately
        for idx, pr in enumerate(body.get("prices", [])):
            licenses = pr.pop("licenses", None)
            if licenses and isinstance(licenses, list):
                stock_id   = f"{prod_id}_{idx}"
                stock_file = STOCK_DIR / f"{stock_id}.json"
                existing   = []
                if stock_file.exists():
                    try:
                        existing = json.loads(stock_file.read_text()) or []
                    except Exception:
                        pass
                new_keys = [k.strip() for k in licenses if str(k).strip()]
                if new_keys:
                    merged = list(dict.fromkeys(existing + new_keys))
                    stock_file.write_text(json.dumps(merged, indent=4))

        found = False
        for i, p in enumerate(products):
            if str(p.get("id")) == str(prod_id):
                products[i] = {**p, **body}
                for pr in products[i].get("prices", []):
                    pr.pop("licenses", None)
                found = True
                break

        if not found:
            for pr in body.get("prices", []):
                pr.pop("licenses", None)
            products.append(body)

        save_products(products)
        return JSONResponse({"success": True, "message": "Produk disimpan"})

    # ── DELETE_PRODUCT ────────────────────────────────────────────────────────
    if action == "delete_product":
        if not is_admin(request):
            raise HTTPException(403, detail={"success": False, "message": "Unauthorized"})
        prod_id  = body.get("id", "")
        products = [p for p in load_products() if str(p.get("id")) != str(prod_id)]
        save_products(products)
        return JSONResponse({"success": True})

    # ── ADD_LICENSES ──────────────────────────────────────────────────────────
    if action == "add_licenses":
        if not is_admin(request):
            raise HTTPException(403, detail={"success": False, "message": "Unauthorized"})
        prod_id    = body.get("product_id", "")
        item_index = body.get("item_index")
        if item_index is None:
            return JSONResponse({"success": False, "message": "item_index diperlukan"})
        keys_raw   = body.get("keys", "")
        keys       = [k.strip() for k in keys_raw.split("\n") if k.strip()]

        stock_id   = f"{prod_id}_{item_index}"
        stock_file = STOCK_DIR / f"{stock_id}.json"
        existing   = []
        if stock_file.exists():
            try:
                existing = json.loads(stock_file.read_text()) or []
            except Exception:
                pass
        merged = list(dict.fromkeys(existing + keys))
        stock_file.write_text(json.dumps(merged, indent=4))
        return JSONResponse({"success": True, "message": f"{len(keys)} key ditambahkan"})

    # ── COMPLETE_ORDER (manual admin) ─────────────────────────────────────────
    if action == "complete_order":
        if not is_admin(request):
            raise HTTPException(403, detail={"success": False, "message": "Unauthorized"})
        oid = body.get("order_id", "")
        if oid:
            await complete_order(oid)
        return JSONResponse({"success": True})

    # ── GET_USER_ORDERS ───────────────────────────────────────────────────────
    if action == "get_user_orders":
        if not body:
            return JSONResponse({"success": False, "message": "Invalid input"})

        credential  = body.get("credential", "")
        email_input = body.get("email", "").strip().lower()
        sub_input   = body.get("sub", "").strip()

        verified_email = ""
        verified_sub   = ""

        if credential:
            payload = decode_google_jwt(credential)
            if payload:
                client_id = cfg.get("google_client_id", "")
                if client_id and payload.get("aud") != client_id:
                    return JSONResponse({"success": False, "message": "Token tidak valid"})
                if payload.get("exp", 0) < int(time.time()):
                    return JSONResponse({"success": False, "message": "Token expired. Login ulang."})
                verified_email = payload.get("email", email_input).strip().lower()
                verified_sub   = payload.get("sub", "")

        if not verified_email:
            if cfg.get("google_client_id"):
                return JSONResponse({"success": False, "message": "Autentikasi gagal"})
            verified_email = email_input

        if not verified_email:
            return JSONResponse({"success": False, "message": "Email tidak ditemukan"})

        all_orders = load_all_orders()
        user_orders = []
        for o in all_orders:
            o_email = (o.get("email") or o.get("google_email") or "").strip().lower()
            o_sub   = o.get("google_sub", "")
            match   = (verified_sub and o_sub and o_sub == verified_sub) or \
                      (verified_email and o_email and o_email == verified_email)
            if match:
                user_orders.append({
                    "order_id":       o.get("order_id", ""),
                    "product":        o.get("product", ""),
                    "item":           o.get("item", ""),
                    "amount":         o.get("amount", 0),
                    "status":         o.get("status", "pending"),
                    "payment_method": o.get("payment_method", ""),
                    "created_at":     _safe_int(o.get("created_at"), 0),
                    "product_content": (
                        o.get("product_content", "")
                        if o.get("status") == "completed"
                        else "Menunggu Pembayaran..."
                    ),
                })

        user_orders.sort(key=lambda o: _safe_int(o.get("created_at"), 0), reverse=True)
        return JSONResponse({"success": True, "orders": user_orders})

    # ── ADMIN_LOGIN ───────────────────────────────────────────────────────────
    if action == "admin_login":
        password = body.get("password", "")
        if not password:
            return JSONResponse({"success": False, "message": "Password required"})

        # CAPTCHA pada login admin (opsional, tapi direkomendasikan)
        if should_verify_captcha(action, cfg):
            captcha_token = (
                body.get("cf_turnstile_token")
                or body.get("g_recaptcha_token")
                or body.get("captcha_token")
                or ""
            )
            ip = get_real_ip(request)
            captcha_ok, captcha_msg = await verify_captcha(captcha_token, cfg, ip, action)
            if not captcha_ok:
                return JSONResponse({"success": False, "message": f"Verifikasi CAPTCHA gagal: {captcha_msg}"})

        if password == cfg.get("admin_password", ""):
            sid = secrets.token_hex(32)
            save_session(sid, {"admin_logged_in": True})
            response = JSONResponse({"success": True})
            response.set_cookie("session_id", sid, httponly=True, samesite="lax", max_age=SESSION_TTL)
            return response
        return JSONResponse({"success": False, "message": "Password salah"})

    # ── ADMIN_LOGOUT ──────────────────────────────────────────────────────────
    if action == "admin_logout":
        sid = get_session_id(request)
        if sid:
            _session_file(sid).unlink(missing_ok=True)
        response = JSONResponse({"success": True})
        response.delete_cookie("session_id")
        return response

    # ── SAVE_CONFIG (admin) ───────────────────────────────────────────────────
    if action == "save_config":
        if not is_admin(request):
            raise HTTPException(403, detail={"success": False, "message": "Unauthorized"})
        current = load_config()
        for k, v in body.items():
            current[k] = v
        save_config(current)
        return JSONResponse({"success": True, "message": "Config disimpan"})

    # ── TEST_EMAIL (admin) ────────────────────────────────────────────────────
    if action == "test_email":
        if not is_admin(request):
            raise HTTPException(403, detail={"success": False, "message": "Unauthorized"})
        to_email = body.get("email", "").strip()
        if not to_email or "@" not in to_email:
            return JSONResponse({"success": False, "message": "Alamat email tidak valid"})
        ok, msg = await send_test_email(cfg, to_email)
        return JSONResponse({"success": ok, "message": msg})

    # ── RATE_LIMIT_STATS (admin) ──────────────────────────────────────────────
    if action == "rate_limit_stats":
        if not is_admin(request):
            raise HTTPException(403, detail={"success": False, "message": "Unauthorized"})
        return JSONResponse({"success": True, "stats": _rl_stats()})

    # ── CAPTCHA_VERIFY (tes manual dari frontend) ─────────────────────────────
    if action == "verify_captcha":
        captcha_token = body.get("captcha_token", "")
        ip = get_real_ip(request)
        ok, msg = await verify_captcha(captcha_token, cfg, ip, "checkout")
        return JSONResponse({"success": ok, "message": msg})

    # ── UNKNOWN ───────────────────────────────────────────────────────────────
    return JSONResponse({"success": False, "message": "Unknown action"})


# ─────────────────────────────────────────────────────────────────────────────
# Background tasks
# ─────────────────────────────────────────────────────────────────────────────

@app.on_event("startup")
async def _startup_background_tasks():
    """Jalankan background cleanup task."""
    async def _periodic_cleanup():
        while True:
            await asyncio.sleep(3600)  # Setiap 1 jam
            removed = await _rl_cleanup()
            if removed:
                import logging
                logging.getLogger("rate_limiter").info(
                    f"Cleanup: {removed} rate limit bucket dihapus"
                )

    asyncio.create_task(_periodic_cleanup())

