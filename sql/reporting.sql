-- ============================================
-- REPORTING: AGGREGATE FUNCTION + LAPORAN
-- Proyek Akhir Sistem Basis Data
-- ============================================

USE perpustakaan_digital;

-- ============================================
-- BAGIAN A: AGGREGATE FUNCTION (Minimal 5)
-- ============================================

-- 1. SUM — Total denda yang belum dibayar per user
-- Menampilkan total denda yang masih 'Belum bayar' per member
SELECT
    mp.full_name,
    SUM(f.amount) AS total_denda_belum_dibayar
FROM fines f
JOIN borrowings b ON f.borrowing_id = b.id
JOIN users u ON b.user_id = u.id
JOIN member_profiles mp ON u.id = mp.user_id
WHERE f.fine_status = 'Belum bayar'
GROUP BY mp.full_name;

-- 2. AVG — Rata-rata rating per kategori buku
SELECT
    c.category_name,
    AVG(rr.rating) AS rata_rata_rating
FROM reviews_ratings rr
JOIN books b ON rr.book_id = b.id
JOIN book_categories bc ON b.id = bc.book_id
JOIN categories c ON bc.category_id = c.id
GROUP BY c.category_name;

-- 3. MAX — Buku dengan jumlah peminjaman terbanyak dalam sebulan
SELECT
    b.title AS judul_buku,
    COUNT(br.id) AS total_pinjam
FROM borrowings br
JOIN books b ON br.book_id = b.id
WHERE MONTH(br.borrow_date) = 3 AND YEAR(br.borrow_date) = 2025
GROUP BY b.id, b.title
ORDER BY total_pinjam DESC
LIMIT 1;

-- 4. MIN — Buku dengan halaman paling sedikit per kategori
SELECT
    c.category_name,
    b.title AS judul_buku,
    b.total_pages
FROM books b
JOIN book_categories bc ON b.id = bc.book_id
JOIN categories c ON bc.category_id = c.id
WHERE b.total_pages = (
    SELECT MIN(b2.total_pages)
    FROM books b2
    JOIN book_categories bc2 ON b2.id = bc2.book_id
    WHERE bc2.category_id = c.id
)
GROUP BY c.category_name, b.id, b.title, b.total_pages;

-- 5. COUNT — Jumlah member aktif per tipe keanggotaan
SELECT
    membership_type,
    COUNT(*) AS jumlah_member
FROM member_profiles
GROUP BY membership_type;

-- ============================================
-- BAGIAN B: LAPORAN (Minimal 5)
-- ============================================

-- Laporan 1: Buku Terlaris (All Time)
SELECT
    b.title AS judul_buku,
    GROUP_CONCAT(DISTINCT a.author_name ORDER BY a.author_name SEPARATOR ', ') AS penulis,
    GROUP_CONCAT(DISTINCT c.category_name ORDER BY c.category_name SEPARATOR ', ') AS kategori,
    pinjam.total_dipinjam
FROM books b
JOIN (
    SELECT book_id, COUNT(*) AS total_dipinjam
    FROM borrowings
    GROUP BY book_id
) pinjam ON b.id = pinjam.book_id
LEFT JOIN book_authors ba ON b.id = ba.book_id
LEFT JOIN authors a ON ba.author_id = a.id
LEFT JOIN book_categories bc ON b.id = bc.book_id
LEFT JOIN categories c ON bc.category_id = c.id
GROUP BY b.id, b.title, pinjam.total_dipinjam
ORDER BY pinjam.total_dipinjam DESC;

-- Laporan 2: Peminjaman Per Periode (? bisa diganti dengan int pilihan)
SELECT mp.user_id AS ID, mp.full_name AS nama_member, bk.title AS judul_buku,
       br.borrow_date, br.due_date, br.return_date, br.status
FROM borrowings br
JOIN books bk ON br.book_id = bk.id
JOIN users u ON br.user_id = u.id
LEFT JOIN member_profiles mp ON mp.user_id = u.id
WHERE MONTH(br.borrow_date) = ? AND YEAR(br.borrow_date) = ?
ORDER BY br.borrow_date DESC;

-- Laporan 3: Total Denda Per Bulan
SELECT 
    DATE_FORMAT(f.created_month, '%Y-%m') AS bulan,
    SUM(f.amount) AS total_denda_masuk,
    SUM(CASE WHEN f.fine_status='Belum bayar' THEN f.amount ELSE 0 END) AS total_belum_bayar,
    SUM(CASE WHEN f.fine_status='Lunas' THEN f.amount ELSE 0 END) AS total_lunas
FROM (
    SELECT fn.*, b.due_date AS created_month
    FROM fines fn
    JOIN borrowings b ON fn.borrowing_id = b.id
) f
GROUP BY DATE_FORMAT(f.created_month, '%Y-%m')
ORDER BY bulan DESC;

-- Laporan 4: Jumlah Member Baru Per Bulan
SELECT
    MONTH(u.created_at) AS bulan,
    YEAR(u.created_at) AS tahun,
    COUNT(*) AS jumlah_member_baru,
    SUM(CASE WHEN mp.membership_type = 'Free' THEN 1 ELSE 0 END) AS tipe_free,
    SUM(CASE WHEN mp.membership_type = 'Premium' THEN 1 ELSE 0 END) AS tipe_premium
FROM users u
JOIN member_profiles mp ON u.id = mp.user_id
GROUP BY YEAR(u.created_at), MONTH(u.created_at)
ORDER BY tahun, bulan;

-- Laporan 5: Statistik Buku Per Kategori
SELECT
    c.category_name AS kategori,
    COUNT(DISTINCT b.id) AS jumlah_buku,
    COALESCE(AVG(rr.rating), 0) AS rata_rata_rating,
    COUNT(DISTINCT br.id) AS total_dipinjam
FROM categories c
LEFT JOIN book_categories bc ON c.id = bc.category_id
LEFT JOIN books b ON bc.book_id = b.id
LEFT JOIN reviews_ratings rr ON b.id = rr.book_id
LEFT JOIN borrowings br ON b.id = br.book_id
GROUP BY c.id, c.category_name
ORDER BY jumlah_buku DESC;
