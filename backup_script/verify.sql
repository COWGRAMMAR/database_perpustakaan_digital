-- ============================================
-- VERIFIKASI RESTORE — Perpustakaan Digital
-- Jalanin setelah restore buat ngecek semuanya
-- ============================================

-- 1. Cek jumlah tabel
SELECT 'Jumlah Tabel' AS keterangan, COUNT(*) AS value
FROM information_schema.tables
WHERE table_schema = 'perpustakaan_digital';

-- 2. Cek jumlah data per tabel utama
SELECT 'Data buku' AS keterangan, COUNT(*) AS value FROM books
UNION ALL
SELECT 'Data users', COUNT(*) FROM users
UNION ALL
SELECT 'Data peminjaman', COUNT(*) FROM borrowings
UNION ALL
SELECT 'Data denda', COUNT(*) FROM fines
UNION ALL
SELECT 'Data pembayaran', COUNT(*) FROM payments
UNION ALL
SELECT 'Data ulasan', COUNT(*) FROM reviews_ratings
UNION ALL
SELECT 'Data wishlist', COUNT(*) FROM wishlists
UNION ALL
SELECT 'Data bookmark', COUNT(*) FROM bookmarks
UNION ALL
SELECT 'Data riwayat baca', COUNT(*) FROM reading_history
UNION ALL
SELECT 'Data audit log', COUNT(*) FROM audit_logs;

-- 3. Cek trigger masih aktif
SELECT trigger_name AS keterangan
FROM information_schema.triggers
WHERE trigger_schema = 'perpustakaan_digital';

-- 4. Cek stored procedure masih ada
SELECT 'Stored Procedure' AS keterangan, COUNT(*) AS value
FROM information_schema.routines
WHERE routine_schema = 'perpustakaan_digital' AND routine_type = 'PROCEDURE';

-- 5. Cek function masih ada
SELECT 'Function' AS keterangan, COUNT(*) AS value
FROM information_schema.routines
WHERE routine_schema = 'perpustakaan_digital' AND routine_type = 'FUNCTION';
