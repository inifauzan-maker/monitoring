# Panduan Deploy Simarketing

Panduan ini memakai asumsi server VPS Linux dengan:

- Nginx
- PHP 8.3 atau lebih baru
- MySQL/MariaDB
- Composer
- Node.js 20+ dan npm

Jika Anda deploy ke shared hosting atau panel, alurnya tetap mirip. Yang berubah biasanya hanya lokasi folder, cara set document root, dan cara menjalankan command.

## 1. Persiapan Server

Pastikan server sudah memiliki:

- PHP extension umum Laravel: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`
- Composer
- Node.js dan npm untuk build asset Vite
- Database MySQL/MariaDB yang sudah dibuat

## 2. Ambil Source Code

```bash
cd /var/www
git clone https://github.com/inifauzan-maker/monitoring.git
cd monitoring
git checkout main
```

Jika project sudah ada di server dan hanya ingin update:

```bash
cd /var/www/monitoring
git pull origin main
```

## 3. Siapkan Environment

Salin env produksi:

```bash
cp .env.produksi.example .env
```

Lalu isi minimal:

- `APP_URL`
- `APP_PUBLIC_PATH` jika deploy di shared hosting yang memakai `public_html`
- `APP_KEY` nanti dibuat dengan artisan
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `MAIL_*` jika email dipakai
- `YOUTUBE_API_KEY` jika fitur playlist YouTube dipakai
- `ADMIN_AWAL_*` untuk akun admin awal
- `SEED_DATA_CONTOH=false` untuk produksi

Catatan:

- App ini memakai `SESSION_DRIVER=database`
- Cache default juga `database`
- Queue default `database`
- Untuk shared hosting seperti Hostinger/cPanel:
  - isi `APP_PUBLIC_PATH=public_html` bila source Laravel dipasang di root domain dan web root tetap mengarah ke folder `public_html`
  - isi `APP_PUBLIC_PATH=.` bila seluruh source aplikasi memang dipasang langsung di dalam `public_html`

Jadi migration wajib dijalankan agar tabel pendukung tersedia.

## 4. Install Dependency

```bash
composer install --no-dev --optimize-autoloader
npm ci
```

## 5. Generate Key dan Build Asset

```bash
php artisan key:generate
npm run build
```

Jika Anda tidak ingin build di server, asset juga bisa dibuild di lokal/CI lalu hasil `public/build` dibawa ke server. Tetapi untuk alur paling sederhana, build langsung di server lebih mudah.

## 6. Jalankan Migration

```bash
php artisan migrate --force
```

Opsional jika server staging/demo ingin langsung terisi data contoh:

```bash
php artisan db:seed --force
```

Untuk produksi, pastikan `.env` berisi `SEED_DATA_CONTOH=false`.

## 7. Rapikan Permission dan Storage Link

```bash
php artisan storage:link
```

Pastikan folder berikut bisa ditulis oleh web server:

- `storage`
- `bootstrap/cache`

Contoh di Ubuntu:

```bash
sudo chown -R www-data:www-data /var/www/monitoring
sudo find /var/www/monitoring/storage -type d -exec chmod 775 {} \;
sudo find /var/www/monitoring/bootstrap/cache -type d -exec chmod 775 {} \;
```

## 8. Optimasi Laravel

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 9. Contoh Konfigurasi Nginx

```nginx
server {
    listen 80;
    server_name simarketing.sivmi.id;
    root /var/www/monitoring/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Sesuaikan:

- `server_name`
- versi socket PHP-FPM
- path project

## 10. Catatan Shared Hosting

Jika struktur hosting Anda seperti ini:

- `~/domains/simarketing.sivmi.id`
- `~/domains/simarketing.sivmi.id/public_html`

dan Anda ingin source Laravel dipasang langsung di root domain, maka:

1. clone source ke `~/domains/simarketing.sivmi.id`
2. isi `.env` di root itu juga
3. set `APP_PUBLIC_PATH=public_html`
4. salin isi folder `public/` Laravel ke `public_html/`
5. file `public_html/index.php` bisa tetap memakai pola default Laravel karena `vendor` dan `bootstrap` berada satu level di atasnya

Dengan pendekatan ini, perintah seperti `php artisan storage:link` akan mengarah ke `public_html/storage`, bukan ke folder `public/storage` bawaan Laravel.

Jika source aplikasi justru dipasang langsung di dalam `public_html`, gunakan:

- `.env` di `public_html/.env`
- `APP_PUBLIC_PATH=.`

Pada pola ini, Laravel akan membaca asset build dari `build/manifest.json` di root `public_html`, bukan dari `public/build`.

## 11. Verifikasi Setelah Deploy

Periksa hal berikut:

1. `https://domain-anda/health`
   Respons sehat akan mengembalikan JSON dengan `status: ok`.
2. `https://domain-anda/masuk`
   Pastikan halaman login muncul.
3. Coba login memakai akun yang valid.
4. Pastikan asset CSS/JS termuat normal.
5. Coba buka modul utama seperti `Produk`, `Projects`, `LMS`, dan `Notifikasi`.

## 12. Checklist Produksi Hostinger

Checklist minimum sebelum aplikasi dipakai:

1. `APP_URL` sudah mengarah ke domain final
2. `APP_PUBLIC_PATH` sudah sesuai struktur hosting
3. `DB_*` sudah benar dan lulus `php artisan migrate --force`
4. `ADMIN_AWAL_*` sudah diisi untuk akun produksi
5. `SEED_DATA_CONTOH=false`
6. `storage:link` sudah berhasil
7. `/health` mengembalikan status `ok`
8. login memakai akun produksi berhasil
9. asset `build/manifest.json` tersedia di public path aktif
10. akun demo dan data contoh sudah dibersihkan bila tidak dibutuhkan

Untuk membersihkan data contoh di server:

```bash
php artisan app:bersihkan-data-contoh --force --hapus-akun-demo
```

## 13. Checklist Update Rilis Berikutnya

Saat ada update code:

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 14. Deploy dari GitHub Actions

Repository ini sudah disiapkan untuk deploy manual dari GitHub Actions melalui workflow:

- `.github/workflows/deploy-produksi.yml`

Workflow ini:

1. checkout branch yang dipilih
2. install dependency
3. jalankan test
4. build asset
5. SSH ke server
6. menjalankan skrip `skrip/deploy_produksi.sh`
7. mengirim hasil `public/build` ke public path server
8. opsional mengecek endpoint `/health`

### Secret GitHub yang perlu diisi

Isi di `Settings > Secrets and variables > Actions`:

- `DEPLOY_HOST`
  IP atau domain server tujuan
- `DEPLOY_PORT`
  Port SSH, biasanya `22`
- `DEPLOY_USER`
  User SSH untuk deploy
- `DEPLOY_PATH`
  Path project di server, contoh `/var/www/monitoring`
- `DEPLOY_SSH_KEY`
  Private key SSH yang dipakai GitHub Actions untuk masuk ke server
- `DEPLOY_HEALTHCHECK_URL`
  Opsional, contoh `https://simarketing.sivmi.id/health`

### Langkah awal di server sebelum workflow dipakai

Minimal sekali saja:

```bash
cd /var/www
git clone https://github.com/inifauzan-maker/monitoring.git
cd monitoring
cp .env.produksi.example .env
php artisan key:generate
```

Lalu isi `.env` sesuai server produksi.

Catatan:

- Untuk alur deploy GitHub Actions terbaru, server tidak wajib punya `npm`
- Asset frontend dibuild di GitHub Actions lalu dikirim ke server setelah deploy
- Jika memakai shared hosting dengan `public_html`, sesuaikan `APP_PUBLIC_PATH`:
  - `public_html` bila source ada di root domain
  - `.` bila source aplikasi langsung berada di `public_html`

Jika repository private, server juga harus bisa menarik source code dari GitHub saat `git pull` dijalankan. Biasanya dengan deploy key atau PAT yang sudah terpasang di server.

### Cara menjalankan deploy

Workflow akan berjalan otomatis setiap ada `push` ke branch `main`.

Selain itu, Anda juga tetap bisa menjalankan manual:

1. buka tab `Actions` di GitHub
2. pilih workflow `Deploy Produksi`
3. klik `Run workflow`
4. pilih branch, biasanya `main`
5. pilih apakah ingin menjalankan seeder atau tidak
6. jalankan workflow

### Catatan

- Workflow ini sekarang otomatis jalan saat ada `push` ke `main`
- `workflow_dispatch` tetap dipertahankan agar Anda masih bisa deploy manual saat diperlukan
- skrip deploy server ada di `skrip/deploy_produksi.sh`

## 15. Catatan Risiko

- Jika `SESSION_DRIVER=database` tetapi migration belum dijalankan, login akan gagal.
- Jika MySQL mati, aplikasi akan ikut gagal karena session, cache, dan sebagian modul memakai database.
- Jika asset belum dibuild, tampilan Tabler/Vite tidak akan tampil benar.
- Jika `storage:link` belum dibuat, file publik seperti avatar/link image tidak akan tampil.
