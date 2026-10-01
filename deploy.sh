#!/bin/bash
#
# Script deploy terpadu SIM-TESIS FKIP UNS.
#
# PRASYARAT: file kode baru SUDAH di-rsync ke folder ini (lihat
# RUNBOOK-DEPLOYMENT.md §0 dan §2.2 — WAJIB verifikasi isi staging
# sebelum rsync, ini pelajaran dari insiden penghapusan file produksi).
#
# Pemakaian:
#   cd /home/speakver/pgv.speakverse.id
#   bash deploy.sh

set -euo pipefail

echo "=== [0/9] Self-check: pastikan file inti ada sebelum lanjut ==="
for f in artisan composer.json app/Http/Controllers; do
    if [ ! -e "$f" ]; then
        echo "!!! '$f' TIDAK DITEMUKAN — folder ini sepertinya bukan/tidak lengkap. BERHENTI. !!!"
        exit 1
    fi
done
echo "OK — file inti lengkap."

echo "=== [1/9] Mode maintenance ==="
php artisan down --secret="deploy-$(date +%s)" || true

echo "=== [2/9] Composer install ==="
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "=== [3/9] NPM install & build ==="
npm install
npm run build

echo "=== [4/9] Regenerate autoload ==="
composer dump-autoload

echo "=== [5/9] Jalankan migration ==="
php artisan migrate --force

echo "=== [6/9] Bersihkan & bangun ulang cache produksi ==="
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== [7/9] Storage link ==="
php artisan storage:link || true

echo "=== [8/9] Jalankan test suite — WAJIB hijau sebelum situs live kembali ==="
if ! vendor/bin/phpunit; then
    echo ""
    echo "!!! TEST GAGAL — situs TETAP di mode maintenance, TIDAK di-'up'. !!!"
    exit 1
fi

echo "=== [9/9] Nonaktifkan mode maintenance ==="
php artisan up

echo ""
echo "=== Deploy selesai & semua test hijau. ==="
echo "  curl -sI https://pgv.speakverse.id/ | head -3"
