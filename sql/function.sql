-- ============================================
-- USER DEFINED FUNCTION (UDF)
-- Proyek Akhir Sistem Basis Data
-- ============================================

USE perpustakaan_digital;

-- ============================================
-- Function 1: fn_hitung_denda
-- Menghitung denda keterlambatan berdasarkan borrowing_id
-- Rumus: (return_date - due_date) * Rp1.000/hari
-- Return 0 jika tidak terlambat
-- ============================================
DELIMITER //

CREATE FUNCTION fn_hitung_denda(p_borrowing_id INT)
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE v_denda DECIMAL(10,2) DEFAULT 0;
    DECLARE v_due_date DATE;
    DECLARE v_return_date DATE;
    DECLARE v_selisih INT;

    -- Ambil due_date dan return_date dari borrowing yang dimaksud
    SELECT due_date, return_date INTO v_due_date, v_return_date
    FROM borrowings
    WHERE id = p_borrowing_id;

    -- Hitung selisih hari jika return_date > due_date
    IF v_return_date IS NOT NULL AND v_return_date > v_due_date THEN
        SET v_selisih = DATEDIFF(v_return_date, v_due_date);
        SET v_denda = v_selisih * 1000;
    END IF;

    RETURN v_denda;
END //

DELIMITER ;

-- ============================================
-- Function 2: fn_avg_rating
-- Menghitung rata-rata rating sebuah buku
-- Return 0.00 jika belum ada review
-- ============================================
DELIMITER //

CREATE FUNCTION fn_avg_rating(p_book_id INT)
RETURNS DECIMAL(3,2)
DETERMINISTIC
BEGIN
    DECLARE v_avg DECIMAL(3,2) DEFAULT 0.00;

    SELECT AVG(rating) INTO v_avg
    FROM reviews_ratings
    WHERE book_id = p_book_id;

    -- Jika NULL (belum ada review), return 0.00
    IF v_avg IS NULL THEN
        SET v_avg = 0.00;
    END IF;

    RETURN v_avg;
END //

DELIMITER ;
