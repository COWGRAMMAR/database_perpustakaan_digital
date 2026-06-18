-- ============================================
-- SP: sp_laporan_bulanan
-- Fungsi: Menampilkan rekap laporan per bulan & tahun
-- Return: Total peminjaman, total denda, member baru, buku terpopuler
-- ============================================

USE perpustakaan_digital;

DROP PROCEDURE IF EXISTS sp_laporan_bulanan;

CREATE PROCEDURE sp_laporan_bulanan(
    IN p_bulan INT,
    IN p_tahun INT
)
BEGIN
    -- 1. Total peminjaman bulan itu
    SELECT CONCAT('Total Peminjaman: ', COUNT(*)) AS laporan
    FROM borrowings
    WHERE MONTH(borrow_date) = p_bulan AND YEAR(borrow_date) = p_tahun;

    -- 2. Total denda masuk bulan itu
    SELECT CONCAT('Total Denda Masuk: Rp', COALESCE(SUM(payment_amount), 0)) AS laporan
    FROM payments
    WHERE MONTH(payment_date) = p_bulan AND YEAR(payment_date) = p_tahun;

    -- 3. Jumlah member baru bulan itu
    SELECT CONCAT('Member Baru: ', COUNT(*)) AS laporan
    FROM users
    WHERE MONTH(created_at) = p_bulan AND YEAR(created_at) = p_tahun;

    -- 4. Buku paling sering dipinjam bulan itu
    SELECT b.title AS judul_buku, COUNT(*) AS total_dipinjam
    FROM borrowings br
    JOIN books b ON br.book_id = b.id
    WHERE MONTH(br.borrow_date) = p_bulan AND YEAR(br.borrow_date) = p_tahun
    GROUP BY br.book_id, b.title
    ORDER BY total_dipinjam DESC
    LIMIT 1;
END;
