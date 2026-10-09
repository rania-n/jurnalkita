#!/bin/bash
echo "Memulihkan database dari database_backup.sql..."
# Hapus baris yang bikin error hak akses
sed -e '/SQL_LOG_BIN/d' -e '/GTID_PURGED/d' database_backup.sql > filtered_backup.sql
# Import ke MySQL
mysql -u jurnal -pjurnal123 jurnalkita_db < filtered_backup.sql
echo "Selesai! Database berhasil dipulihkan."
