"""
email_notifier.py — Notifikasi Email ke Pembeli via SMTP
=========================================================
Mengirim email konfirmasi otomatis ke pembeli setelah order selesai.

Fitur:
- Template HTML yang rapi dan mobile-friendly
- Mendukung Gmail, Outlook, Mailtrap, atau SMTP apapun
- Async-safe: mengirim email di background thread agar tidak blokir response
- Retry otomatis hingga 3x jika gagal
- Config diambil dari config.json (tidak hardcode credential)

Konfigurasi di config.json:
    "smtp_host":       "smtp.gmail.com"
    "smtp_port":       587              (587=STARTTLS, 465=SSL, 25=plain)
    "smtp_user":       "email@gmail.com"
    "smtp_password":   "app_password"
    "smtp_from_name":  "WilzXiterz Store"
    "smtp_enabled":    true
"""

from __future__ import annotations

import asyncio
import logging
import smtplib
import ssl
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from pathlib import Path
from typing import Optional
import json
import time

logger = logging.getLogger("email_notifier")

# ─────────────────────────────────────────────────────────────────────────────
# Template HTML Email
# ─────────────────────────────────────────────────────────────────────────────

def _build_html(order: dict, site_title: str) -> str:
    """Buat body HTML email dari data order."""
    order_id       = order.get("order_id", "-")
    customer_name  = order.get("name", "Pelanggan")
    product        = order.get("product", "-")
    item           = order.get("item", "-")
    amount         = order.get("amount", 0)
    product_content = order.get("product_content", "")
    created_at     = order.get("created_at", int(time.time()))

    # Format tanggal
    import datetime
    dt = datetime.datetime.fromtimestamp(int(created_at)).strftime("%d %B %Y, %H:%M WIB")

    # Format harga
    try:
        price_str = f"Rp {int(amount):,}".replace(",", ".")
    except Exception:
        price_str = str(amount)

    # Escape HTML
    def esc(s: str) -> str:
        return str(s).replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")

    content_rows = ""
    if product_content and product_content not in ("Menunggu Pembayaran...", "HUBUNGI CS"):
        for line in product_content.strip().split("\n"):
            line = line.strip()
            if line:
                content_rows += f"""
                <tr>
                  <td style="padding:8px 12px;background:#1e293b;border-radius:6px;
                             font-family:monospace;font-size:14px;color:#a5f3fc;
                             word-break:break-all;">{esc(line)}</td>
                </tr>"""

    product_content_block = ""
    if content_rows:
        product_content_block = f"""
        <tr><td style="padding-top:24px;">
          <p style="margin:0 0 10px;font-size:14px;color:#94a3b8;font-weight:600;
                    text-transform:uppercase;letter-spacing:.5px;">
            Produk Anda
          </p>
          <table width="100%" cellspacing="0" cellpadding="0">
            <tr><td style="background:#0f172a;border-radius:8px;padding:12px;">
              <table width="100%" cellspacing="4" cellpadding="0">
                {content_rows}
              </table>
            </td></tr>
          </table>
          <p style="margin:10px 0 0;font-size:12px;color:#64748b;">
            Simpan informasi di atas dengan aman. Jangan bagikan ke siapapun.
          </p>
        </td></tr>"""

    return f"""<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Order Berhasil — {esc(site_title)}</title>
</head>
<body style="margin:0;padding:0;background:#0f172a;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellspacing="0" cellpadding="0" style="background:#0f172a;padding:32px 16px;">
    <tr><td align="center">
      <table width="600" cellspacing="0" cellpadding="0"
             style="max-width:600px;background:#1e293b;border-radius:16px;
                    overflow:hidden;border:1px solid #334155;">

        <!-- Header -->
        <tr><td style="background:linear-gradient(135deg,#6366f1,#8b5cf6);
                       padding:32px 40px;text-align:center;">
          <h1 style="margin:0;font-size:28px;color:#ffffff;font-weight:800;
                     letter-spacing:-0.5px;">
            {esc(site_title)}
          </h1>
          <p style="margin:8px 0 0;font-size:14px;color:#c4b5fd;">
            Konfirmasi Pembelian
          </p>
        </td></tr>

        <!-- Body -->
        <tr><td style="padding:32px 40px;">
          <table width="100%" cellspacing="0" cellpadding="0">

            <!-- Salam -->
            <tr><td>
              <p style="margin:0 0 8px;font-size:22px;color:#f1f5f9;font-weight:700;">
                Hei, {esc(customer_name)}! 👋
              </p>
              <p style="margin:0 0 24px;font-size:14px;color:#94a3b8;line-height:1.6;">
                Terima kasih telah berbelanja di <strong style="color:#a5b4fc;">
                {esc(site_title)}</strong>. Pembayaran Anda telah diterima dan
                order Anda sudah diproses.
              </p>
            </td></tr>

            <!-- Detail order -->
            <tr><td>
              <p style="margin:0 0 10px;font-size:14px;color:#94a3b8;font-weight:600;
                        text-transform:uppercase;letter-spacing:.5px;">
                Detail Order
              </p>
              <table width="100%" cellspacing="0" cellpadding="0"
                     style="background:#0f172a;border-radius:10px;overflow:hidden;
                            border:1px solid #1e3a5f;">
                <tr>
                  <td style="padding:12px 16px;font-size:13px;color:#64748b;
                             border-bottom:1px solid #1e293b;width:40%;">Order ID</td>
                  <td style="padding:12px 16px;font-size:13px;color:#e2e8f0;
                             border-bottom:1px solid #1e293b;font-weight:600;">
                    {esc(order_id)}
                  </td>
                </tr>
                <tr>
                  <td style="padding:12px 16px;font-size:13px;color:#64748b;
                             border-bottom:1px solid #1e293b;">Produk</td>
                  <td style="padding:12px 16px;font-size:13px;color:#e2e8f0;
                             border-bottom:1px solid #1e293b;">
                    {esc(product)} — {esc(item)}
                  </td>
                </tr>
                <tr>
                  <td style="padding:12px 16px;font-size:13px;color:#64748b;
                             border-bottom:1px solid #1e293b;">Total Bayar</td>
                  <td style="padding:12px 16px;font-size:13px;color:#4ade80;
                             border-bottom:1px solid #1e293b;font-weight:700;">
                    {esc(price_str)}
                  </td>
                </tr>
                <tr>
                  <td style="padding:12px 16px;font-size:13px;color:#64748b;">
                    Tanggal
                  </td>
                  <td style="padding:12px 16px;font-size:13px;color:#e2e8f0;">
                    {esc(dt)}
                  </td>
                </tr>
              </table>
            </td></tr>

            {product_content_block}

            <!-- CTA -->
            <tr><td style="padding-top:28px;text-align:center;">
              <p style="margin:0 0 16px;font-size:13px;color:#64748b;">
                Ada pertanyaan? Hubungi kami:
              </p>
              <a href="https://wa.me/{order.get('whatsapp','')}"
                 style="display:inline-block;background:linear-gradient(135deg,#6366f1,#8b5cf6);
                        color:#ffffff;text-decoration:none;padding:12px 28px;
                        border-radius:8px;font-size:14px;font-weight:600;">
                Hubungi Support
              </a>
            </td></tr>

          </table>
        </td></tr>

        <!-- Footer -->
        <tr><td style="padding:20px 40px;border-top:1px solid #334155;
                       text-align:center;">
          <p style="margin:0;font-size:12px;color:#475569;line-height:1.6;">
            Email ini dikirim otomatis. Jangan balas email ini.<br>
            &copy; {time.strftime('%Y')} {esc(site_title)}. All rights reserved.
          </p>
        </td></tr>

      </table>
    </td></tr>
  </table>
</body>
</html>"""


def _build_text(order: dict, site_title: str) -> str:
    """Fallback plain-text email."""
    order_id        = order.get("order_id", "-")
    customer_name   = order.get("name", "Pelanggan")
    product         = order.get("product", "-")
    item            = order.get("item", "-")
    amount          = order.get("amount", 0)
    product_content = order.get("product_content", "")

    try:
        price_str = f"Rp {int(amount):,}".replace(",", ".")
    except Exception:
        price_str = str(amount)

    lines = [
        f"=== {site_title} — Konfirmasi Pembelian ===",
        f"",
        f"Hei {customer_name},",
        f"",
        f"Terima kasih! Pembayaran Anda telah diterima.",
        f"",
        f"DETAIL ORDER:",
        f"  Order ID : {order_id}",
        f"  Produk   : {product} — {item}",
        f"  Total    : {price_str}",
    ]

    if product_content and product_content not in ("Menunggu Pembayaran...", "HUBUNGI CS"):
        lines += ["", "PRODUK ANDA:", "  " + "\n  ".join(product_content.strip().split("\n"))]

    lines += [
        "",
        "Simpan informasi ini dengan aman.",
        "",
        f"Salam,",
        f"Tim {site_title}",
    ]
    return "\n".join(lines)


# ─────────────────────────────────────────────────────────────────────────────
# Fungsi pengiriman
# ─────────────────────────────────────────────────────────────────────────────

def _send_email_sync(
    *,
    smtp_host: str,
    smtp_port: int,
    smtp_user: str,
    smtp_password: str,
    from_name: str,
    to_email: str,
    subject: str,
    html_body: str,
    text_body: str,
) -> None:
    """Kirim email secara sinkron (dipanggil di thread pool)."""
    msg = MIMEMultipart("alternative")
    msg["Subject"] = subject
    msg["From"]    = f"{from_name} <{smtp_user}>"
    msg["To"]      = to_email

    msg.attach(MIMEText(text_body, "plain", "utf-8"))
    msg.attach(MIMEText(html_body, "html",  "utf-8"))

    if smtp_port == 465:
        context = ssl.create_default_context()
        with smtplib.SMTP_SSL(smtp_host, smtp_port, context=context) as server:
            server.login(smtp_user, smtp_password)
            server.sendmail(smtp_user, to_email, msg.as_bytes())
    else:
        with smtplib.SMTP(smtp_host, smtp_port) as server:
            server.ehlo()
            if smtp_port in (587, 2587):
                server.starttls(context=ssl.create_default_context())
                server.ehlo()
            server.login(smtp_user, smtp_password)
            server.sendmail(smtp_user, to_email, msg.as_bytes())


async def send_order_confirmation(order: dict, config: dict) -> bool:
    """
    Kirim email konfirmasi ke pembeli secara asinkron (non-blocking).

    Args:
        order:  Data order (dari load_order / complete_order)
        config: Config aplikasi (dari load_config)

    Returns:
        True jika berhasil, False jika SMTP tidak aktif atau gagal.
    """
    # Cek apakah SMTP diaktifkan
    if not config.get("smtp_enabled"):
        logger.debug("SMTP tidak diaktifkan, melewati pengiriman email.")
        return False

    to_email = (order.get("email") or order.get("google_email") or "").strip()
    if not to_email or "@" not in to_email:
        logger.debug("Email pembeli tidak tersedia, melewati pengiriman.")
        return False

    smtp_host     = config.get("smtp_host", "smtp.gmail.com")
    smtp_port     = int(config.get("smtp_port", 587))
    smtp_user     = config.get("smtp_user", "")
    smtp_password = config.get("smtp_password", "")
    from_name     = config.get("smtp_from_name") or config.get("title", "Store")
    site_title    = config.get("title", "Store")

    if not smtp_user or not smtp_password:
        logger.warning("SMTP user/password belum dikonfigurasi.")
        return False

    # Tambahkan nomor WA ke order untuk template
    order_with_wa = {**order, "whatsapp": config.get("whatsapp", "")}

    subject   = f"✅ Pesanan #{order.get('order_id', '')} Berhasil — {site_title}"
    html_body = _build_html(order_with_wa, site_title)
    text_body = _build_text(order_with_wa, site_title)

    loop = asyncio.get_event_loop()

    for attempt in range(1, 4):  # 3 percobaan
        try:
            await loop.run_in_executor(
                None,
                lambda: _send_email_sync(
                    smtp_host=smtp_host,
                    smtp_port=smtp_port,
                    smtp_user=smtp_user,
                    smtp_password=smtp_password,
                    from_name=from_name,
                    to_email=to_email,
                    subject=subject,
                    html_body=html_body,
                    text_body=text_body,
                ),
            )
            logger.info(f"Email terkirim ke {to_email} (order {order.get('order_id')})")
            return True
        except Exception as exc:
            logger.warning(f"Gagal kirim email (percobaan {attempt}/3): {exc}")
            if attempt < 3:
                await asyncio.sleep(2 ** attempt)

    logger.error(f"Gagal kirim email setelah 3 percobaan ke {to_email}")
    return False


async def send_test_email(config: dict, to_email: str) -> tuple[bool, str]:
    """
    Kirim email tes untuk memverifikasi konfigurasi SMTP.
    Kembalikan (berhasil: bool, pesan: str).
    """
    dummy_order = {
        "order_id":        "TEST-0000",
        "name":            "Test User",
        "email":           to_email,
        "product":         "Produk Demo",
        "item":            "Paket Test",
        "amount":          99000,
        "product_content": "LICENSE-XXXX-YYYY-ZZZZ",
        "created_at":      int(time.time()),
    }

    ok = await send_order_confirmation(dummy_order, {**config, "smtp_enabled": True})
    if ok:
        return True, f"Email tes berhasil dikirim ke {to_email}"
    return False, "Gagal mengirim email tes. Periksa konfigurasi SMTP."
