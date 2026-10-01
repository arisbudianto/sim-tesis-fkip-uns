#!/bin/bash
#
# Backup harian SIM-TESIS FKIP UNS — database MySQL + folder storage
# (naskah mahasiswa, dokumen resmi ber-QR). Dijadwalkan via cPanel Cron
# Jobs (lihat RUNBOOK-DEPLOYMENT.md §3.7):
#
#   0 2 * * * bash /home/speakver/pgv.speakverse.id/scripts/backup.sh >> /home/speakver/backup.log 2>&1
#
# Menyimpan 14 hari backup terakhir (rotasi otomatis).

set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_DIR="$HOME/backups/sim-tesis"
TANGGAL=$(date +%Y%m%d-%H%M%S)
RETENSI_HARI=14

mkdir -p "$BACKUP_DIR"

DB_DATABASE=$(grep -E '^DB_DATABASE=' "$APP_DIR/.env" | cut -d '=' -f2-)
DB_USERNAME=$(grep -E '^DB_USERNAME=' "$APP_DIR/.env" | cut -d '=' -f2-)
DB_PASSWORD=$(grep -E '^DB_PASSWORD=' "$APP_DIR/.env" | cut -d '=' -f2-)

echo "[$TANGGAL] Backup database '$DB_DATABASE'..."
mysqldump -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" \
  | gzip > "$BACKUP_DIR/db-$TANGGAL.sql.gz"

echo "[$TANGGAL] Backup folder storage..."
tar -czf "$BACKUP_DIR/storage-$TANGGAL.tar.gz" -C "$APP_DIR/storage/app" public

find "$BACKUP_DIR" -name "db-*.sql.gz" -mtime +"$RETENSI_HARI" -delete
find "$BACKUP_DIR" -name "storage-*.tar.gz" -mtime +"$RETENSI_HARI" -delete

echo "[$TANGGAL] Backup selesai. Tersimpan di $BACKUP_DIR"
