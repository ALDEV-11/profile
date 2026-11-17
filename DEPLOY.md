# Deploy ke Vercel

Panduan ini memakai stack: **Vercel Hobby (container FrankenPHP)**, **Neon Postgres**, **Cloudflare R2**, **Resend**, dan domain `*.vercel.app`.

## 1. Siapkan Neon Postgres

1. Buat project Neon di region **Singapore (ap-southeast-1)**.
2. Salin dua connection string dari dashboard Neon:
   - **Direct** — untuk migrasi dan import data.
   - **Pooled** (`...-pooler...`) — untuk runtime Vercel.
3. Buat `.env.neon` dari `.env` lokal, lalu isi bagian database:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<project>.vercel.app
APP_KEY=base64:...

DB_CONNECTION=pgsql
DB_HOST=<direct-host>
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=<user>
DB_PASSWORD=<password>
DB_SSLMODE=require

SESSION_DRIVER=cookie
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
QUEUE_CONNECTION=deferred
```

> Session dan cache **jangan** memakai database Neon. Setiap request akan
> membayar round-trip ke database, dan Neon yang sedang suspend bisa menambah
> beberapa detik. Pakai `cookie` + `file` untuk deployment single-container.

4. Jalankan migrasi dan import data:

```bash
php artisan migrate --force --env=neon
php artisan data:import storage/app/private/exports/portfolio.json --force --env=neon
```

> `.env.neon` berisi secret dan sudah di-ignore Git. Jangan commit.

## 2. Siapkan Cloudflare R2

1. Buat bucket, misalnya `profile-assets`.
2. Aktifkan **Public Development URL** (`https://pub-xxxx.r2.dev`) atau pasang custom domain.
3. Buat **R2 API Token** dengan akses Object Read & Write.
4. Tambahkan ke `.env.neon`:

```dotenv
PUBLIC_DISK_DRIVER=s3
PUBLIC_DISK_URL=https://pub-xxxx.r2.dev
STORAGE_KEY=<r2-access-key>
STORAGE_SECRET=<r2-secret-key>
STORAGE_REGION=auto
STORAGE_BUCKET=profile-assets
STORAGE_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
STORAGE_PATH_STYLE=true

> Gunakan nama `STORAGE_*` di Vercel. `AWS_ACCESS_KEY_ID` dan
> `AWS_SECRET_ACCESS_KEY` dicadangkan runtime Vercel sehingga nilai yang
> diisi di dashboard tidak sampai ke aplikasi. `config/filesystems.php`
> masih membaca `AWS_*` sebagai fallback untuk pengembangan lokal.
```

> `PUBLIC_DISK_DRIVER` dan `PUBLIC_DISK_URL` wajib diisi di Vercel. Tanpa dua
> variabel ini Laravel memakai disk lokal container yang ephemeral: data tetap
> masuk database, tetapi file gambar akan hilang setelah restart/redeploy dan
> URL `/storage/...` menghasilkan 404.

5. Upload gambar lama (7 set gallery + foto profile):

```bash
php artisan storage:push-public --dry-run --env=neon
php artisan storage:push-public --env=neon
```

Path object harus sama dengan yang tersimpan di database (`galleries/...`, `profiles/...`).

## 3. Siapkan Resend

1. Daftar dengan email admin (`aldosatrio202@gmail.com`).
2. Buat API key.
3. Selama domain belum diverifikasi, gunakan `onboarding@resend.dev`. Pengirim ini hanya bisa mengirim ke email pemilik akun Resend — cukup untuk OTP admin dan notifikasi contact form.
4. Tambahkan ke environment Vercel:

```dotenv
MAIL_MAILER=resend
RESEND_API_KEY=re_xxxxxxxx
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME="Aldo Portfolio"
```

## 4. Environment variables Vercel

Tambahkan semua variabel berikut di **Project Settings → Environment Variables** (Production):

```dotenv
APP_NAME="Aldo Portfolio"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...            # php artisan key:generate --show
APP_URL=https://<project>.vercel.app
LOG_CHANNEL=stderr
LOG_LEVEL=warning

DB_CONNECTION=pgsql
DB_HOST=<pooled-host>
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=<user>
DB_PASSWORD=<password>
DB_SSLMODE=require

# Session disimpan di cookie terenkripsi dan cache di filesystem container.
# Ini menghilangkan query session/cache ke Neon pada setiap request.
SESSION_DRIVER=cookie
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
QUEUE_CONNECTION=deferred

PUBLIC_DISK_DRIVER=s3
PUBLIC_DISK_URL=https://pub-xxxx.r2.dev
STORAGE_KEY=<r2-access-key>
STORAGE_SECRET=<r2-secret-key>
STORAGE_REGION=auto
STORAGE_BUCKET=profile-assets
STORAGE_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
STORAGE_PATH_STYLE=true

> Gunakan nama `STORAGE_*` di Vercel. `AWS_ACCESS_KEY_ID` dan
> `AWS_SECRET_ACCESS_KEY` dicadangkan runtime Vercel sehingga nilai yang
> diisi di dashboard tidak sampai ke aplikasi. `config/filesystems.php`
> masih membaca `AWS_*` sebagai fallback untuk pengembangan lokal.

MAIL_MAILER=resend
RESEND_API_KEY=re_xxxxxxxx
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME="Aldo Portfolio"

BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=<dari .env lokal>
PUSHER_APP_KEY=<dari .env lokal>
PUSHER_APP_SECRET=<dari .env lokal>
PUSHER_APP_CLUSTER=ap1

CRON_SECRET=<openssl rand -hex 32>
```

> `PUBLIC_DISK_DRIVER` dan `PUBLIC_DISK_URL` wajib diisi di Vercel. Tanpa dua
> variabel ini Laravel memakai disk lokal container yang ephemeral: data tetap
> masuk database, tetapi file gambar akan hilang setelah restart/redeploy dan
> URL `/storage/...` menghasilkan 404.

## 5. Pengaturan project Vercel

1. **Framework Preset → Container** (bukan Vite).
2. **Root Directory → `.`**.
3. **Build Command / Output Directory → kosongkan** (container yang menangani build).
4. **Sandbox Region → Singapore (`sin1`)** dan **Functions Region → Singapore (`sin1`)**.
   Container service memakai Sandbox Region; kalau masih `iad1`, PHP akan
   menyeberang ke Neon Singapore pada setiap query. Cek build log: harus
   `services/app/container [sin1]`, bukan `[iad1]`.
5. Pastikan `Dockerfile`, `Caddyfile`, `vercel.json`, dan `docker/php.ini` ada di root repo.
6. Deploy ulang.

`vercel.json` sudah mengatur:

- service container `app` dari `Dockerfile`;
- region `sin1` untuk function/service;
- rewrite semua request ke service tersebut;
- cron harian `/cron/visitors-cleanup` pukul 03:00 UTC.

> Vercel Hobby hanya mendukung cron harian. Untuk mencegah Neon auto-suspend,
> panggil `/cron/keepalive` dari cron eksternal (UptimeRobot, cron-job.org)
> setiap 4–5 menit dengan header `Authorization: Bearer <CRON_SECRET>`.
> Kalau paket Neon mendukung, matikan auto-suspend sekalian.

## 6. Smoke test setelah deploy

- `/up` → 200.
- Homepage tampil, foto profile dan gallery berasal dari domain R2.
- Dark mode dan navigasi jalan.
- Contact form mengirim email ke admin dan pesan masuk di dashboard.
- Login admin + OTP Resend.
- Upload / hapus gallery dan ganti foto profile.
- Analytics dan notifikasi Pusher.
- Cron: panggil manual dengan header yang benar:

```bash
curl -i https://<project>.vercel.app/cron/visitors-cleanup \
  -H "Authorization: Bearer <CRON_SECRET>"

curl -i https://<project>.vercel.app/cron/keepalive \
  -H "Authorization: Bearer <CRON_SECRET>"
```

Keduanya harus `204 No Content`.

Cek performa server (bukan Lighthouse):

```bash
curl -sS -o /dev/null -w 'home ttfb=%{time_starttransfer}s\n' https://<project>.vercel.app/
curl -sS -o /dev/null -w 'login ttfb=%{time_starttransfer}s\n' https://<project>.vercel.app/222/auth/login
```

`x-vercel-id` seharusnya `sin1::sin1`, bukan `sin1::iad1`.

## Catatan

- Jangan menjalankan migrasi saat container start; jalankan sekali dari lokal/CI.
- Filesystem container tidak persisten. Semua upload harus lewat R2.
- Cache `file` bersifat per-instance. Kalau nanti memakai banyak instance atau
  butuh rate limit yang konsisten, pindah ke Redis/Upstash di region Singapore.
- Session driver `cookie` membuat semua admin logout sekali setelah diganti.
- `route:cache` dan `view:cache` sudah dijalankan saat build Docker. Jangan
  menambahkan closure baru ke `routes/web.php` tanpa menjalankan ulang cache.
- Request body Vercel maksimal 4.5 MB; validasi upload aplikasi 4 MB.
- Kalau email OTP gagal, cek `RESEND_API_KEY`, alamat pengirim, dan log runtime Vercel.
- Kalau halaman 502, cek `Caddyfile` tetap memakai `:{$PORT:80}` dan container listen di port yang benar.
- Kalau muncul error `No application encryption key`, `APP_KEY` belum di-set di Vercel.
