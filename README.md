# Simarketing

Aplikasi Simarketing berbasis Laravel 13 dengan modul:

- RBAC pengguna
- Produk, Leads, Siswa, Omzet
- Projects dan histori progres tugas
- Artikel editorial dan export PDF
- Link publik dan analitik
- LMS: kursus, materi, playlist, progres belajar, bank soal, kuis
- Notifikasi aplikasi
- Log aktivitas

## Menjalankan Lokal

1. Salin `.env.example` menjadi `.env`
2. Sesuaikan konfigurasi database
3. Jalankan:

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run build
php artisan serve
```

## Akun Awal

Seeder default sekarang membuat akun admin awal dari nilai `.env` berikut:

- `ADMIN_AWAL_NAMA`
- `ADMIN_AWAL_EMAIL`
- `ADMIN_AWAL_PASSWORD`
- `ADMIN_AWAL_LEVEL_AKSES`

Data contoh dan akun demo hanya akan dibuat jika `SEED_DATA_CONTOH=true`.

## Bersihkan Data Contoh

Untuk membersihkan data contoh/demo di seluruh modul utama:

```bash
php artisan app:bersihkan-data-contoh --force
```

Jika sekalian ingin menghapus akun demo yang terdaftar di konfigurasi data awal:

```bash
php artisan app:bersihkan-data-contoh --force --hapus-akun-demo
```

## Endpoint Penting

- Login: `/masuk`
- Health check deploy: `/health`

## Deploy

Panduan deploy lengkap ada di [panduan_deploy.md](panduan_deploy.md).

Deploy dari GitHub Actions juga sudah disiapkan lewat workflow:

- `.github/workflows/deploy-produksi.yml`

Workflow ini berjalan otomatis saat ada `push` ke `main`, dan juga masih bisa dijalankan manual dari tab `Actions`.

Untuk shared hosting yang memakai `public_html`, aplikasi ini mendukung dua pola:

- `APP_PUBLIC_PATH=public_html` jika source Laravel berada di root domain dan folder publik tetap `public_html`
- `APP_PUBLIC_PATH=.` jika seluruh source aplikasi memang dipasang langsung di dalam `public_html`
