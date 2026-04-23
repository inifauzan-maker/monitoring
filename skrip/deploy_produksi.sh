#!/usr/bin/env bash

set -euo pipefail

BRANCH="${1:-main}"
JALANKAN_SEEDER="${2:-false}"

echo "==> Memulai deploy branch: ${BRANCH}"

if [[ ! -f ".env" ]]; then
    echo "File .env belum tersedia di server."
    echo "Buat .env terlebih dahulu sebelum deploy otomatis dijalankan."
    exit 1
fi

if [[ ! -d ".git" ]]; then
    echo "Folder saat ini bukan repository git yang valid."
    exit 1
fi

for perintah in git php composer npm; do
    if ! command -v "${perintah}" >/dev/null 2>&1; then
        echo "Perintah ${perintah} tidak ditemukan di server."
        exit 1
    fi
done

echo "==> Menarik update terbaru dari origin/${BRANCH}"
git fetch origin "${BRANCH}"
git checkout "${BRANCH}"
git pull --ff-only origin "${BRANCH}"

echo "==> Install dependency PHP"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Install dependency frontend"
npm ci

echo "==> Build asset produksi"
npm run build

echo "==> Menjalankan migration"
php artisan migrate --force

if [[ "${JALANKAN_SEEDER}" == "true" ]]; then
    echo "==> Menjalankan seeder produksi"
    php artisan db:seed --force
fi

echo "==> Sinkronisasi storage link"
php artisan storage:link || true

echo "==> Membersihkan cache lama"
php artisan optimize:clear

echo "==> Membangun cache produksi"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Restart queue worker jika ada"
php artisan queue:restart || true

echo "==> Deploy selesai"
