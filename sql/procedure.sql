-- ============================================
-- STORED PROCEDURE + TCL + CURSOR
-- Proyek Akhir Sistem Basis Data
--
-- CARA PAKAI:
-- 1. phpMyAdmin → SQL → paste semua → Go
-- 2. VSCode: Buka file di sql/procedures/ per-procedure
-- ============================================

USE perpustakaan_digital;

-- ============================================
-- SP 1: sp_pinjam_buku
-- Fungsi: Proses peminjaman buku dengan TCL + row locking
-- Demonstrasi: START TRANSACTION, COMMIT, SELECT FOR UPDATE
-- ============================================
DELIMITER //

DROP PROCEDURE IF EXISTS sp_pinjam_buku //

CREATE PROCEDURE sp_pinjam_buku(
    IN p_user_id INT,
    IN p_book_id INT
)
BEGIN
    DECLARE v_sudah_dipinjam INT DEFAULT 0;
    DECLARE v_buku_ada INT DEFAULT 0;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT CONCAT('Peminjaman gagal, di-ROLLBACK. Error: ', 'terjadi kesalahan') AS hasil;
    END;

    -- Cek apakah buku ada
    SELECT COUNT(*) INTO v_buku_ada FROM books WHERE id = p_book_id;

    IF v_buku_ada = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Buku tidak ditemukan';
    END IF;

    -- Transaction + row-level locking (pengganti LOCK TABLES)
    START TRANSACTION;

    -- Cek apakah user sedang meminjam buku ini (dengan row lock FOR UPDATE)
    SELECT COUNT(*) INTO v_sudah_dipinjam
    FROM borrowings
    WHERE user_id = p_user_id
      AND book_id = p_book_id
      AND status IN ('Dipinjam', 'Terlambat')
    FOR UPDATE;

    IF v_sudah_dipinjam > 0 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Anda masih meminjam buku ini, kembalikan dulu';
    END IF;

    -- INSERT peminjaman baru (due_date diisi otomatis trigger)
    INSERT INTO borrowings (user_id, book_id, borrow_date, status)
    VALUES (p_user_id, p_book_id, CURDATE(), 'Dipinjam');

    COMMIT;
    SELECT CONCAT('Peminjaman berhasil. Buku ID ', p_book_id, ' untuk user ID ', p_user_id) AS hasil;
END //

DELIMITER ;

-- ============================================
-- SP 2: sp_bayar_denda
-- Fungsi: Proses pembayaran denda dengan TCL
-- Demonstrasi: COMMIT & ROLLBACK
-- ============================================
DELIMITER //

DROP PROCEDURE IF EXISTS sp_bayar_denda //

CREATE PROCEDURE sp_bayar_denda(
    IN p_fine_id INT,
    IN p_payment_method VARCHAR(20)
)
BEGIN
    DECLARE v_user_id INT;
    DECLARE v_amount DECIMAL(10,2);
    DECLARE v_status VARCHAR(20);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Transaksi gagal, di-ROLLBACK' AS hasil;
    END;

    -- Cek apakah fine_id valid
    SELECT f.user_id, f.amount, f.fine_status INTO v_user_id, v_amount, v_status
    FROM fines f
    WHERE f.id = p_fine_id;

    IF v_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Fine ID tidak ditemukan';
    END IF;

    IF v_status = 'Lunas' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Denda sudah lunas';
    END IF;

    -- START TRANSACTION
    START TRANSACTION;

    -- UPDATE status denda jadi Lunas
    UPDATE fines
    SET fine_status = 'Lunas'
    WHERE id = p_fine_id;

    -- INSERT ke tabel payments
    INSERT INTO payments (user_id, fine_id, payment_amount, payment_date, payment_method)
    VALUES (v_user_id, p_fine_id, v_amount, NOW(), p_payment_method);

    -- Jika semua berhasil, COMMIT
    COMMIT;
    SELECT CONCAT('Pembayaran denda Rp', v_amount, ' berhasil, di-COMMIT') AS hasil;
END //

DELIMITER ;

-- ============================================
-- SP 3: sp_laporan_bulanan
-- Fungsi: Menampilkan rekap laporan per bulan & tahun
-- Return: Total peminjaman, total denda, member baru, buku terpopuler
-- ============================================
DELIMITER //

DROP PROCEDURE IF EXISTS sp_laporan_bulanan //

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
END //

DELIMITER ;

-- ============================================
-- CURSOR + SP 4: sp_proses_keterlambatan
-- Fungsi: Iterasi semua peminjaman yang terlambat,
--         update status ke 'Terlambat', trigger auto generate fine
-- ============================================
DELIMITER //

DROP PROCEDURE IF EXISTS sp_proses_keterlambatan //

CREATE PROCEDURE sp_proses_keterlambatan()
BEGIN
    DECLARE v_borrow_id INT;
    DECLARE v_borrow_date DATE;
    DECLARE v_due_date DATE;
    DECLARE v_selesai INT DEFAULT 0;

    -- Cursor untuk data borrowing yang due_date < CURDATE() dan status 'Dipinjam'
    DECLARE cur_cek_keterlambatan CURSOR FOR
        SELECT id, borrow_date, due_date
        FROM borrowings
        WHERE due_date < CURDATE() AND status = 'Dipinjam';

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_selesai = 1;

    OPEN cur_cek_keterlambatan;

    proses_loop: LOOP
        FETCH cur_cek_keterlambatan INTO v_borrow_id, v_borrow_date, v_due_date;

        IF v_selesai = 1 THEN
            LEAVE proses_loop;
        END IF;

        -- Update status ke 'Terlambat'
        UPDATE borrowings
        SET status = 'Terlambat'
        WHERE id = v_borrow_id;

    END LOOP proses_loop;

    CLOSE cur_cek_keterlambatan;

    SELECT CONCAT('Proses keterlambatan selesai') AS hasil;
END //

DELIMITER ;
