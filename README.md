# Monitoring

Aplikasi monitoring berbasis Laravel 13 dengan modul:

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

## Akun Contoh

Seeder default membuat akun berikut dengan password `password`:

- `superadmin@monitoring.test`
- `level1@monitoring.test`
- `level2@monitoring.test`
- `level3@monitoring.test`
- `level4@monitoring.test`
- `level5@monitoring.test`

## Endpoint Penting

- Login: `/masuk`
- Health check deploy: `/health`

## Deploy

Panduan deploy lengkap ada di [panduan_deploy.md](panduan_deploy.md).

Deploy dari GitHub Actions juga sudah disiapkan lewat workflow:

- `.github/workflows/deploy-produksi.yml`

Workflow ini berjalan otomatis saat ada `push` ke `main`, dan juga masih bisa dijalankan manual dari tab `Actions`.

Untuk shared hosting yang memakai `public_html`, aplikasi ini juga mendukung `APP_PUBLIC_PATH=public_html` agar source Laravel tetap aman di root/domain path tetapi folder publik mengikuti struktur hosting.
