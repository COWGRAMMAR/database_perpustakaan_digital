
-- ============================================
-- TESTING: Drop beberapa tabel + SP + trigger
--
-- Cara pake:
--   1. Backup dulu: double click backup.bat
--   2. Jalanin file ini: mysql -u root < testing\drop_tables_test.sql
--      atau copy-paste ke SQL tab phpMyAdmin
--   3. Cek tabel hilang: SHOW TABLES;
--   4. Restore: double click restore.bat
--   5. Verifikasi tabel balik lagi
-- ============================================

USE perpustakaan_digital;

-- ─── Drop tabel (non-critical) ──────────────
DROP TABLE IF EXISTS bookmarks;
DROP TABLE IF EXISTS wishlists;
DROP TABLE IF EXISTS reading_history;
DROP TABLE IF EXISTS audit_logs;

-- ─── Drop trigger ───────────────────────────
DROP TRIGGER IF EXISTS trg_audit_borrowings_insert;

-- ─── Drop stored procedure ──────────────────
DROP PROCEDURE IF EXISTS sp_proses_keterlambatan;

-- ─── Verifikasi ────────────────────────────
SELECT 'DROP COMPLETE' AS hasil;

SHOW TABLES;
