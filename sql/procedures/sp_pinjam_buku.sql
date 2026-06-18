-- ============================================
-- SP: sp_pinjam_buku
-- Fungsi: Proses peminjaman buku dengan TCL + row locking
-- Demonstrasi: START TRANSACTION, COMMIT, SELECT FOR UPDATE
--
-- CARA RUN DI VSCODE:
-- Buka file ini → klik tombol "Run" (atau kanan → Execute)
-- Karena 1 file = 1 statement, extension jalanin full tanpa error delimiter
-- ============================================

USE perpustakaan_digital;

DROP PROCEDURE IF EXISTS sp_pinjam_buku;

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

    -- INSERT peminjaman baru (due_date diisi otomatis trigger trg_set_due_date)
    INSERT INTO borrowings (user_id, book_id, borrow_date, status)
    VALUES (p_user_id, p_book_id, CURDATE(), 'Dipinjam');

    COMMIT;
    SELECT CONCAT('Peminjaman berhasil. Buku ID ', p_book_id, ' untuk user ID ', p_user_id) AS hasil;
END;
