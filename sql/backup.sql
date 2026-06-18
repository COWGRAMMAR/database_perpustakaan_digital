-- ============================================
-- BACKUP & RESTORE
-- Proyek Akhir Sistem Basis Data
-- Metode: mysqldump via command line
-- ============================================

-- ============================================
-- 1. BACKUP DATABASE
-- Jalankan di terminal (CMD/Git Bash):
-- ============================================

-- Backup seluruh database ke file .sql
-- Command:
--   mysqldump -u root -p perpustakaan_digital > backup_perpus.sql

-- Backup dengan opsi lengkap (termasuk routines & triggers):
--   mysqldump -u root -p --routines --triggers perpustakaan_digital > backup_perpus.sql

-- ============================================
-- 2. RESTORE DATABASE
-- Jalankan di terminal (CMD/Git Bash):
-- ============================================

-- Step 1: Hapus database lama (opsional, untuk simulasi)
--   mysql -u root -p -e "DROP DATABASE IF EXISTS perpustakaan_digital"

-- Step 2: Buat database baru
--   mysql -u root -p -e "CREATE DATABASE perpustakaan_digital"

-- Step 3: Restore dari file backup
--   mysql -u root -p perpustakaan_digital < backup_perpus.sql

-- ============================================
-- 3. PENGUJIAN RESTORE
-- Verifikasi bahwa data berhasil dipulihkan
-- ============================================

-- Cek jumlah tabel
SELECT COUNT(*) AS jumlah_tabel
FROM information_schema.tables
WHERE table_schema = 'perpustakaan_digital';

-- Cek jumlah data per tabel utama
SELECT 'books' AS tabel, COUNT(*) AS jumlah FROM books
UNION ALL
SELECT 'users', COUNT(*) FROM users
UNION ALL
SELECT 'borrowings', COUNT(*) FROM borrowings
UNION ALL
SELECT 'fines', COUNT(*) FROM fines
UNION ALL
SELECT 'payments', COUNT(*) FROM payments;

-- Cek trigger masih ada
SHOW TRIGGERS;
