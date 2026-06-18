-- ============================================
-- SP: sp_bayar_denda
-- Fungsi: Proses pembayaran denda dengan TCL
-- Demonstrasi: START TRANSACTION, COMMIT, ROLLBACK, error handler
-- ============================================

USE perpustakaan_digital;

DROP PROCEDURE IF EXISTS sp_bayar_denda;

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
END;
