"""
rate_limiter.py — Per-endpoint rate limiter yang lebih cerdas
=============================================================
Menggantikan sistem rate limit global di api.py dengan sistem berbasis
sliding-window per-endpoint, per-IP.

Fitur:
- Konfigurasi batas berbeda untuk setiap endpoint/action
- Sliding window per IP + endpoint
- Exponential backoff otomatis untuk repeat offenders
- Header X-RateLimit-* informatif
- Whitelist IP (misal: server internal)
"""

from __future__ import annotations

import asyncio
import time
from collections import defaultdict
from dataclasses import dataclass, field
from typing import Optional


# ─────────────────────────────────────────────────────────────────────────────
# Konfigurasi limit per endpoint
# ─────────────────────────────────────────────────────────────────────────────

@dataclass
class EndpointLimit:
    """Konfigurasi rate limit untuk satu endpoint."""
    max_hits: int        # Maksimum request yang diizinkan
    window: int          # Jendela waktu dalam detik
    burst_multiplier: float = 1.5  # Toleransi burst singkat
    description: str = ""


# Tabel konfigurasi per action. Key = nama action, value = EndpointLimit.
ENDPOINT_LIMITS: dict[str, EndpointLimit] = {
    # Endpoint publik — relatif longgar
    "get_products":       EndpointLimit(max_hits=120, window=60,  description="Lihat produk"),
    "get_recent_orders":  EndpointLimit(max_hits=30,  window=60,  description="Order terbaru"),
    "check_status":       EndpointLimit(max_hits=40,  window=60,  description="Cek status order"),

    # Endpoint transaksi — ketat
    "checkout":           EndpointLimit(max_hits=5,   window=60,  description="Checkout"),
    "create_order":       EndpointLimit(max_hits=5,   window=60,  description="Buat order"),

    # Endpoint auth — sangat ketat (cegah brute-force)
    "admin_login":        EndpointLimit(max_hits=5,   window=300, description="Login admin"),

    # Endpoint webhook (dari payment gateway, bukan user)
    "webhook":            EndpointLimit(max_hits=60,  window=60,  description="Webhook Pakasir"),
    "pakasir_webhook":    EndpointLimit(max_hits=60,  window=60,  description="Webhook Pakasir"),
    "binance_webhook":    EndpointLimit(max_hits=60,  window=60,  description="Webhook Binance"),

    # Endpoint admin — sedang
    "get_orders":         EndpointLimit(max_hits=60,  window=60,  description="Lihat orders admin"),
    "get_config":         EndpointLimit(max_hits=30,  window=60,  description="Lihat config"),
    "save_config":        EndpointLimit(max_hits=10,  window=60,  description="Simpan config"),
    "save_product":       EndpointLimit(max_hits=20,  window=60,  description="Simpan produk"),
    "delete_product":     EndpointLimit(max_hits=10,  window=60,  description="Hapus produk"),
    "add_licenses":       EndpointLimit(max_hits=20,  window=60,  description="Tambah lisensi"),
    "complete_order":     EndpointLimit(max_hits=30,  window=60,  description="Selesaikan order"),

    # Default untuk action yang tidak terdaftar
    "_default":           EndpointLimit(max_hits=60,  window=60,  description="Default"),
}

# IP yang dibebaskan dari rate limit (misalnya: server webhook internal)
IP_WHITELIST: set[str] = set()

# Maksimum penalty multiplier untuk repeat offenders (2^n, maks 2^MAX_STRIKES)
MAX_STRIKES = 4


# ─────────────────────────────────────────────────────────────────────────────
# State in-memory
# ─────────────────────────────────────────────────────────────────────────────

@dataclass
class _IPEndpointBucket:
    """Sliding-window bucket per (IP, endpoint)."""
    timestamps: list[float] = field(default_factory=list)
    violations: int = 0          # Berapa kali sudah kena limit
    blocked_until: float = 0.0   # Unix timestamp kapan blokir berakhir


# Nested dict: _store[ip][action] = _IPEndpointBucket
_store: dict[str, dict[str, _IPEndpointBucket]] = defaultdict(lambda: defaultdict(_IPEndpointBucket))
_lock = asyncio.Lock()


# ─────────────────────────────────────────────────────────────────────────────
# Fungsi utama
# ─────────────────────────────────────────────────────────────────────────────

def _get_limit(action: str) -> EndpointLimit:
    return ENDPOINT_LIMITS.get(action) or ENDPOINT_LIMITS["_default"]


async def check_rate_limit(ip: str, action: str = "_default") -> tuple[bool, dict]:
    """
    Periksa apakah request diizinkan.

    Returns:
        (allowed: bool, headers: dict)
        headers berisi X-RateLimit-* yang bisa dilempar ke response.
    """
    if ip in IP_WHITELIST:
        return True, {}

    limit_cfg = _get_limit(action)
    now = time.time()

    async with _lock:
        bucket = _store[ip][action]

        # Cek apakah IP sedang dalam masa blokir
        if bucket.blocked_until > now:
            retry_after = int(bucket.blocked_until - now) + 1
            return False, {
                "X-RateLimit-Limit":      str(limit_cfg.max_hits),
                "X-RateLimit-Remaining":  "0",
                "X-RateLimit-Reset":      str(int(bucket.blocked_until)),
                "Retry-After":            str(retry_after),
                "X-RateLimit-Policy":     f"{limit_cfg.max_hits};w={limit_cfg.window}",
            }

        # Sliding window: buang timestamp yang sudah kedaluwarsa
        window_start = now - limit_cfg.window
        bucket.timestamps = [t for t in bucket.timestamps if t > window_start]

        remaining = limit_cfg.max_hits - len(bucket.timestamps)

        if remaining <= 0:
            # Kena rate limit — hitung penalty blokir berdasarkan violations
            bucket.violations += 1
            penalty = limit_cfg.window * (2 ** min(bucket.violations - 1, MAX_STRIKES))
            bucket.blocked_until = now + penalty

            return False, {
                "X-RateLimit-Limit":      str(limit_cfg.max_hits),
                "X-RateLimit-Remaining":  "0",
                "X-RateLimit-Reset":      str(int(bucket.blocked_until)),
                "Retry-After":            str(int(penalty)),
                "X-RateLimit-Policy":     f"{limit_cfg.max_hits};w={limit_cfg.window}",
            }

        # Request diizinkan
        bucket.timestamps.append(now)

        # Reset violations jika sudah lama tidak melanggar
        if bucket.violations > 0 and not bucket.timestamps:
            bucket.violations = 0

        headers = {
            "X-RateLimit-Limit":     str(limit_cfg.max_hits),
            "X-RateLimit-Remaining": str(remaining - 1),
            "X-RateLimit-Reset":     str(int(window_start + limit_cfg.window)),
            "X-RateLimit-Policy":    f"{limit_cfg.max_hits};w={limit_cfg.window}",
        }
        return True, headers


async def cleanup_old_entries(max_age: int = 3600) -> int:
    """
    Bersihkan bucket yang tidak aktif. Panggil secara periodik.
    Mengembalikan jumlah entry yang dihapus.
    """
    now = time.time()
    removed = 0
    async with _lock:
        ips_to_remove = []
        for ip, actions in _store.items():
            actions_to_remove = []
            for action, bucket in actions.items():
                if (not bucket.timestamps or now - max(bucket.timestamps) > max_age) \
                        and bucket.blocked_until < now:
                    actions_to_remove.append(action)
            for action in actions_to_remove:
                del actions[action]
                removed += 1
            if not actions:
                ips_to_remove.append(ip)
        for ip in ips_to_remove:
            del _store[ip]
    return removed


def add_to_whitelist(ip: str) -> None:
    """Tambahkan IP ke whitelist (bebas dari rate limit)."""
    IP_WHITELIST.add(ip)


def remove_from_whitelist(ip: str) -> None:
    """Hapus IP dari whitelist."""
    IP_WHITELIST.discard(ip)


def get_stats() -> dict:
    """Kembalikan statistik rate limiter saat ini (untuk debugging/admin)."""
    now = time.time()
    total_ips = len(_store)
    total_blocked = sum(
        1
        for actions in _store.values()
        for bucket in actions.values()
        if bucket.blocked_until > now
    )
    return {
        "tracked_ips": total_ips,
        "currently_blocked": total_blocked,
        "whitelist_size": len(IP_WHITELIST),
        "endpoint_policies": {
            action: {
                "max_hits": cfg.max_hits,
                "window_seconds": cfg.window,
                "description": cfg.description,
            }
            for action, cfg in ENDPOINT_LIMITS.items()
        },
    }
