-- ============================================
-- SP + CURSOR: sp_proses_keterlambatan
-- Fungsi: Iterasi semua peminjaman yang terlambat,
--         update status ke 'Terlambat', trigger auto generate fine
-- Demonstrasi: CURSOR, LOOP, HANDLER, UPDATE
-- ============================================

USE perpustakaan_digital;

DROP PROCEDURE IF EXISTS sp_proses_keterlambatan;

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
END;
