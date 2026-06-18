-- ============================================
-- TRIGGER
-- Proyek Akhir Sistem Basis Data
-- Minimal 3 Trigger
-- ============================================

USE perpustakaan_digital;

-- ============================================
-- Trigger 1: trg_set_due_date
-- Event: BEFORE INSERT ON borrowings
-- Fungsi: Auto-set due_date = borrow_date + 7 hari
-- ============================================
DELIMITER //

CREATE TRIGGER trg_set_due_date
BEFORE INSERT ON borrowings
FOR EACH ROW
BEGIN
    IF NEW.due_date IS NULL THEN
        SET NEW.due_date = DATE_ADD(NEW.borrow_date, INTERVAL 7 DAY);
    END IF;
END //

DELIMITER ;

-- ============================================
-- Trigger 2: trg_auto_fine
-- Event: AFTER UPDATE ON borrowings
-- Fungsi: Jika return_date > due_date, auto INSERT ke fines
-- Kalkulasi: (return_date - due_date) * Rp1.000/hari
-- ============================================
DELIMITER //

CREATE TRIGGER trg_auto_fine
AFTER UPDATE ON borrowings
FOR EACH ROW
BEGIN
    DECLARE v_selisih INT;
    DECLARE v_denda DECIMAL(10,2);

    -- Cek apakah return_date diisi dan lebih besar dari due_date
    IF NEW.return_date IS NOT NULL AND NEW.return_date > NEW.due_date THEN
        SET v_selisih = DATEDIFF(NEW.return_date, NEW.due_date);
        SET v_denda = v_selisih * 1000;

        INSERT INTO fines (borrowing_id, amount, fine_status)
        VALUES (NEW.id, v_denda, 'Belum bayar');
    END IF;
END //

DELIMITER ;

-- ============================================
-- Trigger 3: trg_audit_borrowings_insert
-- Event: AFTER INSERT ON borrowings
-- Fungsi: Catat aktivitas peminjaman ke audit_logs
-- ============================================
DELIMITER //

CREATE TRIGGER trg_audit_borrowings_insert
AFTER INSERT ON borrowings
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (user_id, action, table_name, description, ip_address)
    VALUES (
        NEW.user_id,
        'INSERT',
        'borrowings',
        CONCAT('Peminjaman buku ID ', NEW.book_id, ' oleh user ID ', NEW.user_id, ' pada ', NEW.borrow_date),
        '127.0.0.1'
    );
END //

DELIMITER ;
