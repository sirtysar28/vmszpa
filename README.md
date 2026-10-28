# VMS ZPA — Visitor Management System

Aplikasi manajemen kunjungan tamu **PT. Zinkpower Austridno** (domain: **vmszpa.com**),
dibangun dengan **Laravel 13 (terbaru)** dan aman dijalankan pada **PHP 8.4 / 8.5**.

Dikembangkan oleh [Trijaya Solution](https://trijayasolution.com).

---

## ✨ Fitur

### 📱 Mode Kiosk Tablet (untuk tamu, di pos security — tanpa login)
| Layar | Keterangan |
|---|---|
| **Layar Check-in** | Welcome + counter CHECK-IN / CHECK-OUT hari ini + tombol besar daftar/check-out |
| **Layar Daftar Tamu** | Pilihan **"Sudah pernah Daftar"** atau **"Daftar Sekarang"** |
| **Registrasi Tamu** | Nama lengkap, No HP, Jenis Kendaraan (Motor/Mobil/Truck), Dari Perusahaan, Jenis Identitas (KTP/SIM/Passport/Lainnya), No Identitas → mendapat **Kode Tamu** (mis. `ZPA-8F3K2A`) |
| **Sudah Pernah Daftar** | Cari berdasarkan Nama + No HP, tampil daftar tamu terdaftar |
| **Check-In** | Tujuan\*, Nomor Kendaraan\*, Bertemu dengan siapa, Catatan → CHECK-IN |
| **Check-Out** | Daftar tamu yang sedang Check-In (nama / perusahaan / tujuan) → pilih → CHECK-OUT |

### 🖥️ Area Management (login, responsif mobile — menu berbentuk **icon di footer**)
- **Login**: form di **kiri**, informasi aplikasi di **kanan**; ada fitur **intip password**,
  **captcha 5 karakter** (bisa diklik untuk refresh), **lupa password** via email.
- **Dashboard**: statistik, tamu yang sedang di dalam, grafik 7 hari, kunjungan terbaru.
- **Data Tamu**: tabel lengkap (Kode Tamu, Tamu, No HP, Perusahaan, Tgl & Jam Check-In/Out,
  Tujuan, No Kendaraan, Bertemu, Catatan), pencarian + filter tanggal + status, export CSV, cetak.
- **Laporan**: rekap periode (tujuan terbanyak, perusahaan, harian) + export CSV + cetak.
- **Pengguna** (khusus admin): kelola akun petugas/admin (tambah, edit, aktif/nonaktif, hapus).
- Footer setiap halaman: *Developed by [Trijaya Solution](https://trijayasolution.com)*.

---

## 🚀 Menjalankan di Lokal

```bash
cd vmszpa
composer install
cp .env.example .env          # lalu sesuaikan
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```



---

## 🔐 Keamanan & Mitigasi Serangan PHP2Shell / Webshell

Aplikasi ini dirancang **tanpa fitur upload file publik sama sekali** — semua input
berupa teks tervalidasi. Lapisan keamanan yang sudah aktif:

1. **Tanpa upload file** — kiosk menolak request multipart (`KioskGuard`), tidak ada
   endpoint upload → tidak ada vektor upload webshell.
2. **.htaccess hardened** (`public/` & `storage/`):
   - Eksekusi file `.php/.phtml/.phar/...` **diblokir** di dalam `public/`
     (kecuali front controller `index.php`).
   - Directory listing dimatikan, file konfigurasi (`.env`, `composer.json`, dll) ditolak.
3. **CSP ketat** (`SecurityHeaders` middleware): JavaScript **hanya** dari file eksternal
   (`script-src 'self'`), inline-script diblokir, `object-src 'none'`, `frame-ancestors 'none'`.
4. **Security headers**: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`,
   `Referrer-Policy`, `Permissions-Policy`.
5. **CSRF protection** di semua form (`@csrf`), session terenkripsi, http-only, same-site lax.
6. **Rate limiting**: login 5x/2menit + throttle route; lupa password 3x/5menit; kiosk 60 req/menit.
7. **Captcha 5 karakter** sekali-pakai (hangus 10 menit) + audit trail tabel `login_attempts`.
8. **Password policy**: minimal 8 karakter huruf+angka, hash bcrypt/argon2 otomatis.
9. **Query aman** via Eloquent ORM (prepared statements) + `$fillable` (anti mass-assignment).
10. **Blade escaping** (`{{ }}`) di semua output — anti XSS.

### ⚙️ Hardening tambahan di server produksi (disarankan)

`php.ini` — matikan fungsi eksekusi sistem yang dimanfaatkan webshell:

```ini
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source,dl
expose_php = Off
allow_url_include = Off
allow_url_fopen = Off
display_errors = Off
log_errors = On
open_basedir = /var/www/vmszpa:/var/www/vmszpa/storage:/tmp
```

Nginx (blokir eksekusi PHP di folder non-framework):

```nginx
server {
    server_name vmszpa.com www.vmszpa.com;
    root /var/www/vmszpa/public;
    index index.php;

    # Folder statis: tolak PHP
    location ~* ^/(css|js|images)/.*\.(php|phtml|phar)$ { deny all; }

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Tolak file sensitif
    location ~ /\.(env|git) { deny all; }
}
```

Selain itu:
- Selalu jalankan `composer audit` secara berkala.
- Set `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true` di produksi.
- Pakai HTTPS (Let's Encrypt) dan redirect HTTP → HTTPS.

---

## 🗄️ Struktur Data

| Tabel | Isi |
|---|---|
| `users` | Akun management (role: `admin` / `petugas`, status aktif) |
| `guests` | Master tamu: kode tamu unik, nama, HP, perusahaan, kendaraan, identitas |
| `visits` | Kunjungan: tujuan, no kendaraan, bertemu, catatan, check-in/out at, petugas |
| `login_attempts` | Audit trail login (IP, user-agent, sukses/gagal) |

---

## 📋 Alur Kiosk (sesuai workflow)

```
Layar Check-in (welcome + counter)
 ├─ DAFTAR & CHECK-IN → Layar Daftar Tamu
 │   ├─ Sudah pernah Daftar → cari nama+HP → pilih tamu ┐
 │   └─ Daftar Sekarang → form registrasi → Kode Tamu ──┤
 │                                                       ├─ Form Check-In
 │                                                       │  (Tujuan*, No Kendaraan*,
 │                                                       │   Bertemu, Catatan)
 │                                                       └─ CHECK-IN → kembali
 └─ CHECK-OUT → daftar tamu di dalam → pilih → CHECK-OUT → kembali
```

---

## ✅ Testing

```bash
php artisan test
```

19 test (login & captcha, alur kiosk lengkap, management, security headers) — semua lulus.

---

&copy; PT. Zinkpower Austridno — Developed by **[Trijaya Solution](https://trijayasolution.com)**
# vmszpa
