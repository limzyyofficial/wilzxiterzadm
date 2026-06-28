# Fitur Baru — WilzX Enhanced

Tiga fitur baru telah ditambahkan ke `api.py` tanpa merusak fungsi yang ada.

---

## 1. Rate Limiter Per-Endpoint (`rate_limiter.py`)

### Perbedaan dengan versi lama

| | Lama | Baru |
|---|---|---|
| Granularitas | Global (semua endpoint sama) | Per-endpoint |
| Batas checkout | 5 req/menit | 5 req/menit |
| Batas get_products | 5 req/menit (sama dengan checkout!) | 120 req/menit |
| Batas admin_login | 5 req/menit | 5 req/300 detik |
| Repeat offenders | Tidak ada penalty tambahan | Exponential backoff |
| Headers info | Hanya `Retry-After` | `X-RateLimit-Limit/Remaining/Reset/Policy` |

### Konfigurasi batas per endpoint

Edit `ENDPOINT_LIMITS` di `rate_limiter.py`:

```python
"checkout":     EndpointLimit(max_hits=5,   window=60),
"admin_login":  EndpointLimit(max_hits=5,   window=300),
"get_products": EndpointLimit(max_hits=120, window=60),
```

### Whitelist IP (untuk server webhook)

```python
from rate_limiter import add_to_whitelist
add_to_whitelist("203.0.113.10")  # IP payment gateway
```

### Lihat statistik (endpoint admin)

```
GET /?action=rate_limit_stats
Cookie: session_id=...
```

---

## 2. Email Notifikasi Pembeli (`email_notifier.py`)

Email HTML otomatis dikirim ke pembeli setelah order selesai (saat `complete_order()` dipanggil — baik via webhook maupun polling `check_status`).

### Konfigurasi di Admin Panel (tambahkan ke `config.json`)

```json
{
  "smtp_enabled":     true,
  "smtp_host":        "smtp.gmail.com",
  "smtp_port":        587,
  "smtp_user":        "toko@gmail.com",
  "smtp_password":    "xxxx xxxx xxxx xxxx",
  "smtp_from_name":   "WilzXiterz Store"
}
```

### Setup Gmail (App Password)

1. Aktifkan 2FA di akun Google
2. Buka: https://myaccount.google.com/apppasswords
3. Buat App Password untuk "Mail"
4. Gunakan password 16 karakter tersebut di `smtp_password`

### Provider SMTP lainnya

| Provider | Host | Port |
|---|---|---|
| Gmail | smtp.gmail.com | 587 |
| Outlook/Hotmail | smtp-mail.outlook.com | 587 |
| Yahoo | smtp.mail.yahoo.com | 587 |
| Mailtrap (testing) | smtp.mailtrap.io | 587 |
| SMTP2GO | mail.smtp2go.com | 2587 |

### Tes kirim email (endpoint admin)

```http
POST /?action=test_email
Cookie: session_id=...
Content-Type: application/json

{"email": "test@example.com"}
```

---

## 3. CAPTCHA Verifier (`captcha_verifier.py`)

Mendukung **Cloudflare Turnstile** dan **Google reCAPTCHA v2/v3**.  
CAPTCHA aktif pada: `checkout`, `create_order`, `admin_login`.

### Pilihan Provider

#### A. Cloudflare Turnstile (Direkomendasikan)
- Gratis unlimited
- Tidak mengganggu UX (invisible)
- Daftar di: https://dash.cloudflare.com → Turnstile

```json
{
  "captcha_provider":  "turnstile",
  "turnstile_secret":  "0x4AAAAAAA..."
}
```

Frontend (tambahkan di form checkout):
```html
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async></script>
<div class="cf-turnstile" data-sitekey="0x4AAAAAAA..." data-callback="onTurnstileSuccess"></div>
<script>
function onTurnstileSuccess(token) {
  document.getElementById('cf_turnstile_token').value = token;
}
</script>
```

Kirim token di body request:
```json
{"cf_turnstile_token": "token_dari_turnstile", ...}
```

#### B. Google reCAPTCHA v3
- Daftar di: https://www.google.com/recaptcha/admin

```json
{
  "captcha_provider":       "recaptcha",
  "recaptcha_secret":       "6Le...",
  "recaptcha_v3_min_score": 0.5
}
```

Frontend:
```js
grecaptcha.ready(() => {
  grecaptcha.execute('SITE_KEY', {action: 'checkout'}).then(token => {
    // tambahkan token ke body request
    body.g_recaptcha_token = token;
  });
});
```

#### C. Nonaktifkan CAPTCHA

```json
{"captcha_provider": ""}
```

### Tes verifikasi CAPTCHA

```http
POST /?action=verify_captcha
Content-Type: application/json

{"captcha_token": "token_dari_frontend"}
```

---

## Ringkasan Endpoint Baru

| Action | Metode | Auth | Deskripsi |
|---|---|---|---|
| `test_email` | POST | Admin | Kirim email tes ke alamat tertentu |
| `rate_limit_stats` | GET/POST | Admin | Statistik rate limiter |
| `verify_captcha` | POST | Publik | Tes token CAPTCHA |

---

## Instalasi Dependensi

```bash
pip install fastapi "uvicorn[standard]" httpx aiofiles python-multipart
```

Tidak ada dependensi tambahan — `smtplib` dan `ssl` sudah built-in Python.
