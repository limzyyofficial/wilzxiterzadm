"""
captcha_verifier.py — Verifikasi Cloudflare Turnstile & Google reCAPTCHA
=========================================================================
Mendukung dua provider CAPTCHA: Cloudflare Turnstile dan Google reCAPTCHA v2/v3.

Fitur:
- Deteksi otomatis provider dari token (atau dari config)
- reCAPTCHA v3: threshold score yang bisa dikonfigurasi per-action
- Turnstile: idempotency key mencegah replay attack
- Async-safe: menggunakan httpx async client
- Graceful fallback: jika CAPTCHA tidak dikonfigurasi, request tetap diizinkan

Konfigurasi di config.json:
    "captcha_provider":         "turnstile"  | "recaptcha" | "" (nonaktif)
    "turnstile_secret":         "<secret key dari Cloudflare>"
    "recaptcha_secret":         "<secret key dari Google>"
    "recaptcha_v3_min_score":   0.5          (0.0–1.0, default 0.5)
    "captcha_required_actions": ["checkout", "create_order", "admin_login"]
"""

from __future__ import annotations

import hashlib
import logging
import time
from typing import Optional

import httpx

logger = logging.getLogger("captcha_verifier")

# ─────────────────────────────────────────────────────────────────────────────
# Endpoint verifikasi
# ─────────────────────────────────────────────────────────────────────────────

TURNSTILE_VERIFY_URL  = "https://challenges.cloudflare.com/turnstile/v0/siteverify"
RECAPTCHA_VERIFY_URL  = "https://www.google.com/recaptcha/api/siteverify"

# Action yang secara default memerlukan CAPTCHA
DEFAULT_REQUIRED_ACTIONS = {"checkout", "create_order", "admin_login"}

# Cache token Turnstile yang sudah dipakai (mencegah replay dalam 5 menit)
_used_tokens: dict[str, float] = {}
_TOKEN_TTL = 300  # detik


# ─────────────────────────────────────────────────────────────────────────────
# Fungsi utama
# ─────────────────────────────────────────────────────────────────────────────

def _is_required(action: str, config: dict) -> bool:
    """Apakah action ini memerlukan CAPTCHA?"""
    required = set(config.get("captcha_required_actions") or DEFAULT_REQUIRED_ACTIONS)
    return action in required


def _cleanup_token_cache() -> None:
    """Bersihkan cache token yang sudah kedaluwarsa."""
    now = time.time()
    expired = [k for k, ts in _used_tokens.items() if now - ts > _TOKEN_TTL]
    for k in expired:
        del _used_tokens[k]


async def verify_turnstile(token: str, secret: str, remote_ip: str = "") -> tuple[bool, str]:
    """
    Verifikasi token Cloudflare Turnstile.
    Kembalikan (valid: bool, pesan: str).
    """
    if not token or not secret:
        return False, "Token atau secret Turnstile tidak tersedia"

    # Cek replay
    token_hash = hashlib.sha256(token.encode()).hexdigest()
    _cleanup_token_cache()
    if token_hash in _used_tokens:
        return False, "Token Turnstile sudah digunakan (replay attack)"
    _used_tokens[token_hash] = time.time()

    payload = {"secret": secret, "response": token}
    if remote_ip:
        payload["remoteip"] = remote_ip

    try:
        async with httpx.AsyncClient(timeout=10) as client:
            r = await client.post(TURNSTILE_VERIFY_URL, data=payload)
            resp = r.json()
    except Exception as exc:
        logger.error(f"Turnstile request error: {exc}")
        return False, f"Gagal menghubungi Cloudflare: {exc}"

    if resp.get("success"):
        return True, "Turnstile valid"

    error_codes = resp.get("error-codes", [])
    logger.warning(f"Turnstile gagal: {error_codes}")

    # Terjemahkan error code ke pesan ramah
    messages = {
        "missing-input-secret":   "Server secret tidak ada",
        "invalid-input-secret":   "Server secret tidak valid",
        "missing-input-response": "Token tidak ditemukan",
        "invalid-input-response": "Token tidak valid atau sudah kedaluwarsa",
        "bad-request":            "Request tidak valid",
        "timeout-or-duplicate":   "Token sudah digunakan atau kedaluwarsa",
        "internal-error":         "Error internal Cloudflare",
    }
    msg = "; ".join(messages.get(e, e) for e in error_codes) or "Verifikasi CAPTCHA gagal"
    return False, msg


async def verify_recaptcha(
    token: str,
    secret: str,
    remote_ip: str = "",
    min_score: float = 0.5,
    expected_action: str = "",
) -> tuple[bool, str]:
    """
    Verifikasi token Google reCAPTCHA v2 atau v3.
    Untuk v3, sertakan min_score dan expected_action.
    Kembalikan (valid: bool, pesan: str).
    """
    if not token or not secret:
        return False, "Token atau secret reCAPTCHA tidak tersedia"

    payload = {"secret": secret, "response": token}
    if remote_ip:
        payload["remoteip"] = remote_ip

    try:
        async with httpx.AsyncClient(timeout=10) as client:
            r = await client.post(RECAPTCHA_VERIFY_URL, data=payload)
            resp = r.json()
    except Exception as exc:
        logger.error(f"reCAPTCHA request error: {exc}")
        return False, f"Gagal menghubungi Google reCAPTCHA: {exc}"

    if not resp.get("success"):
        error_codes = resp.get("error-codes", [])
        msg = "; ".join(error_codes) or "Verifikasi reCAPTCHA gagal"
        return False, msg

    # reCAPTCHA v3: periksa score dan action
    if "score" in resp:
        score = float(resp.get("score", 0))
        action = resp.get("action", "")
        logger.debug(f"reCAPTCHA v3 score={score} action={action}")

        if score < min_score:
            return False, f"reCAPTCHA score terlalu rendah ({score:.2f} < {min_score:.2f}). Kemungkinan bot."

        if expected_action and action != expected_action:
            logger.warning(f"reCAPTCHA action mismatch: expected={expected_action}, got={action}")
            return False, f"reCAPTCHA action tidak sesuai"

    return True, "reCAPTCHA valid"


async def verify_captcha(
    token: str,
    config: dict,
    remote_ip: str = "",
    action: str = "",
) -> tuple[bool, str]:
    """
    Verifikasi CAPTCHA generik — otomatis pilih provider dari config.

    Args:
        token:     Token dari frontend (cf-turnstile-response / g-recaptcha-response)
        config:    Config aplikasi
        remote_ip: IP pengguna (opsional, untuk validasi tambahan)
        action:    Nama action (untuk reCAPTCHA v3 action matching)

    Returns:
        (valid: bool, pesan: str)
        Jika CAPTCHA tidak dikonfigurasi, selalu kembalikan (True, "CAPTCHA tidak aktif").
    """
    provider = (config.get("captcha_provider") or "").lower().strip()

    if not provider:
        return True, "CAPTCHA tidak aktif"

    if not token:
        return False, "Token CAPTCHA tidak ditemukan. Selesaikan verifikasi terlebih dahulu."

    if provider == "turnstile":
        secret = config.get("turnstile_secret", "")
        if not secret:
            logger.warning("Turnstile secret belum dikonfigurasi")
            return True, "CAPTCHA tidak aktif (secret belum diset)"
        return await verify_turnstile(token, secret, remote_ip)

    elif provider in ("recaptcha", "recaptcha_v2", "recaptcha_v3"):
        secret    = config.get("recaptcha_secret", "")
        min_score = float(config.get("recaptcha_v3_min_score", 0.5))
        if not secret:
            logger.warning("reCAPTCHA secret belum dikonfigurasi")
            return True, "CAPTCHA tidak aktif (secret belum diset)"
        return await verify_recaptcha(token, secret, remote_ip, min_score, action)

    else:
        logger.warning(f"Provider CAPTCHA tidak dikenal: {provider!r}")
        return True, f"Provider CAPTCHA tidak dikenal: {provider}"


def should_verify_captcha(action: str, config: dict) -> bool:
    """
    Apakah request dengan action ini harus diverifikasi CAPTCHA?
    Kembalikan False jika CAPTCHA tidak aktif atau action tidak memerlukan.
    """
    provider = (config.get("captcha_provider") or "").lower().strip()
    if not provider:
        return False
    return _is_required(action, config)
