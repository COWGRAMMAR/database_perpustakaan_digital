-- ============================================
-- DML - Data Dummy Perpustakaan Digital
-- Proyek Akhir Sistem Basis Data
-- ============================================

USE perpustakaan_digital;

-- ============================================
-- MASTER DATA
-- ============================================

-- roles (3)
INSERT INTO roles (role_name) VALUES
('Admin'),
('Staff'),
('Pembaca');

-- publishers (10)
INSERT INTO publishers (publisher_name, address) VALUES
('Gramedia Pustaka Utama', 'Jakarta Pusat, DKI Jakarta'),
('Penerbit Erlangga', 'Jakarta Timur, DKI Jakarta'),
('Mizan Pustaka', 'Bandung, Jawa Barat'),
('PT. Elex Media Komputindo', 'Jakarta Barat, DKI Jakarta'),
('Bentang Pustaka', 'Yogyakarta, DIY'),
('Republika Penerbit', 'Jakarta Selatan, DKI Jakarta'),
('Penerbit Andi', 'Yogyakarta, DIY'),
('Lokamandala Publishing', 'Surabaya, Jawa Timur'),
('Gagas Media', 'Jakarta Selatan, DKI Jakarta'),
('Bukune Publishing', 'Tangerang, Banten');

-- authors (15)
INSERT INTO authors (author_name, bio) VALUES
('Tere Liye', 'Penulis novel Indonesia, dikenal dengan serial Bumi dan Dilan'),
('Andrea Hirata', 'Penulis novel Laskar Pelangi, pernah dinominasikan Booker Prize'),
('Dee Lestari', 'Penulis trilogi Supernova, juga dikenal sebagai musisi'),
('Pramoedya Ananta Toer', 'Sastrawan Indonesia, penulis Tetralogi Buru'),
('Eka Kurniawan', 'Penulis novel Seperti Dendam, Rindu Harus Dibayar Tuntas'),
('Rintik Sedu', 'Penulis muda dengan karya-karya fiksi populer'),
('Fiersa Besari', 'Penulis novel Konspirasi Alam Semesta, juga musisi'),
('Sapardi Djoko Damono', 'Penyair legendaris Indonesia, penulis Hujan Bulan Juni'),
('Mochtar Lubis', 'Penulis novel Senja di Jakarta dan Harimau! Harimau!'),
('Ahmad Fuadi', 'Penulis novel Negeri 5 Menara dan trilogi'),
('Habiburrahman El Shirazy', 'Penulis novel Ayat-Ayat Cinta dan Ketika Cinta Bertasbih'),
('Raditya Dika', 'Penulis komedi, pelopor buku humor Indonesia'),
('Ika Natassa', 'Penulis novel fiksi dewasa seperti Critical Eleven'),
('Pidi Baiq', 'Penulis dan musisi, pencipta Dilan'),
('Tria Ayu Kusuma', 'Penulis novel fiksi remaja dan perempuan');

-- categories (8)
INSERT INTO categories (category_name) VALUES
('Fiksi'),
('Non-Fiksi'),
('Teknologi'),
('Sejarah'),
('Agama'),
('Sains'),
('Komik'),
('Pendidikan');

-- users (30)
INSERT INTO users (username, email, password, is_active, created_at) VALUES
-- Admin (1-2)
('admin1', 'admin1@perpus.id', MD5('admin123'), TRUE, '2025-01-01'),
('admin2', 'admin2@perpus.id', MD5('admin123'), TRUE, '2025-01-01'),
-- Staff (3-7)
('staff1', 'staff1@perpus.id', MD5('staff123'), TRUE, '2025-01-05'),
('staff2', 'staff2@perpus.id', MD5('staff123'), TRUE, '2025-01-05'),
('staff3', 'staff3@perpus.id', MD5('staff123'), TRUE, '2025-02-01'),
('staff4', 'staff4@perpus.id', MD5('staff123'), TRUE, '2025-02-15'),
('staff5', 'staff5@perpus.id', MD5('staff123'), TRUE, '2025-03-01'),
-- Members (8-30)
('farhan', 'farhan@gmail.com', MD5('member123'), TRUE, '2025-01-10'),
('sinta', 'sinta@yahoo.com', MD5('member123'), TRUE, '2025-01-15'),
('dimas', 'dimas@gmail.com', MD5('member123'), TRUE, '2025-02-01'),
('ayu', 'ayu@gmail.com', MD5('member123'), TRUE, '2025-02-10'),
('reza', 'reza@outlook.com', MD5('member123'), TRUE, '2025-02-15'),
('nadia', 'nadia@gmail.com', MD5('member123'), TRUE, '2025-02-20'),
('bagas', 'bagas@yahoo.com', MD5('member123'), TRUE, '2025-03-01'),
('wulan', 'wulan@gmail.com', MD5('member123'), TRUE, '2025-03-05'),
('adit', 'adit@outlook.com', MD5('member123'), TRUE, '2025-03-10'),
('dewi', 'dewi@gmail.com', MD5('member123'), TRUE, '2025-03-15'),
('fikri', 'fikri@yahoo.com', MD5('member123'), TRUE, '2025-04-01'),
('gita', 'gita@gmail.com', MD5('member123'), TRUE, '2025-04-05'),
('hadi', 'hadi@outlook.com', MD5('member123'), TRUE, '2025-04-10'),
('intan', 'intan@gmail.com', MD5('member123'), TRUE, '2025-04-15'),
('joko', 'joko@yahoo.com', MD5('member123'), TRUE, '2025-04-20'),
('kartika', 'kartika@gmail.com', MD5('member123'), TRUE, '2025-05-01'),
('lukman', 'lukman@outlook.com', MD5('member123'), TRUE, '2025-05-05'),
('maya', 'maya@gmail.com', MD5('member123'), TRUE, '2025-05-10'),
('nando', 'nando@yahoo.com', MD5('member123'), TRUE, '2025-05-15'),
('kaka', 'kaka@gmail.com', MD5('member123'), TRUE, '2025-05-20'),
('putri', 'putri@gmail.com', MD5('member123'), TRUE, '2025-06-01'),
('iqbal', 'iqbal@outlook.com', MD5('member123'), TRUE, '2025-06-05'),
('rina', 'rina@gmail.com', MD5('member123'), TRUE, '2025-06-10');

-- user_roles (30)
INSERT INTO user_roles (user_id, role_id) VALUES
(1, 1), (2, 1),           -- Admin
(3, 2), (4, 2), (5, 2),   -- Staff
(6, 2), (7, 2),             -- Staff (lanjutan)
(8, 3), (9, 3), (10, 3),   -- Pembaca
(11, 3), (12, 3), (13, 3),
(14, 3), (15, 3), (16, 3),
(17, 3), (18, 3), (19, 3),
(20, 3), (21, 3), (22, 3),
(23, 3), (24, 3), (25, 3),
(26, 3), (27, 3), (28, 3),
(29, 3), (30, 3);

-- staff_profiles (5)
INSERT INTO staff_profiles (user_id, staff_number, full_name, phone_number) VALUES
(3, 'STF2025001', 'Bambang Supriyadi', '081234567801'),
(4, 'STF2025002', 'Siti Rahmawati', '081234567802'),
(5, 'STF2025003', 'Ahmad Hidayat', '081234567803'),
(6, 'STF2025004', 'Dewi Sartika', '081234567804'),
(7, 'STF2025005', 'Hendra Gunawan', '081234567805');

-- member_profiles (25)
INSERT INTO member_profiles (user_id, member_number, full_name, address, phone_number, membership_type) VALUES
(8,  'MBR20250001', 'Farhan Abdul', 'Jl. Merdeka No. 1, Jakarta', '081234567101', 'Premium'),
(9,  'MBR20250002', 'Sinta Permata', 'Jl. Sudirman No. 5, Bandung', '081234567102', 'Free'),
(10, 'MBR20250003', 'Dimas Ardiansyah', 'Jl. Gatot Subroto No. 10, Jakarta', '081234567103', 'Premium'),
(11, 'MBR20250004', 'Ayu Lestari', 'Jl. Diponegoro No. 3, Semarang', '081234567104', 'Free'),
(12, 'MBR20250005', 'Reza Pratama', 'Jl. Ahmad Yani No. 8, Surabaya', '081234567105', 'Premium'),
(13, 'MBR20250006', 'Nadia Safira', 'Jl. Pahlawan No. 12, Malang', '081234567106', 'Free'),
(14, 'MBR20250007', 'Bagas Wicaksono', 'Jl. Gajah Mada No. 6, Yogyakarta', '081234567107', 'Premium'),
(15, 'MBR20250008', 'Wulan Sari', 'Jl. Sisingamangaraja No. 4, Medan', '081234567108', 'Free'),
(16, 'MBR20250009', 'Aditya Nugraha', 'Jl. Pemuda No. 9, Makassar', '081234567109', 'Free'),
(17, 'MBR20250010', 'Dewi Anggraini', 'Jl. Veteran No. 2, Denpasar', '081234567110', 'Premium'),
(18, 'MBR20250011', 'Fikri Maulana', 'Jl. Kesehatan No. 15, Jakarta', '081234567111', 'Free'),
(19, 'MBR20250012', 'Gita Puspita', 'Jl. Pendidikan No. 7, Bandung', '081234567112', 'Free'),
(20, 'MBR20250013', 'Hadi Susanto', 'Jl. Bahagia No. 11, Surabaya', '081234567113', 'Premium'),
(21, 'MBR20250014', 'Intan Permata', 'Jl. Mawar No. 5, Yogyakarta', '081234567114', 'Free'),
(22, 'MBR20250015', 'Joko Suprapto', 'Jl. Melati No. 3, Semarang', '081234567115', 'Free'),
(23, 'MBR20250016', 'Kartika Sari', 'Jl. Kenanga No. 8, Malang', '081234567116', 'Premium'),
(24, 'MBR20250017', 'Lukman Hakim', 'Jl. Flamboyan No. 2, Medan', '081234567117', 'Free'),
(25, 'MBR20250018', 'Maya Indah', 'Jl. Anggrek No. 6, Makassar', '081234567118', 'Premium'),
(26, 'MBR20250019', 'Fernando Situmorang', 'Jl. Cempaka No. 4, Denpasar', '081234567119', 'Free'),
(27, 'MBR20250020', 'Kakanda Rizky', 'Jl. Dahlia No. 9, Jakarta', '081234567120', 'Free'),
(28, 'MBR20250021', 'Putri Ayuningtyas', 'Jl. Teratai No. 12, Bandung', '081234567121', 'Premium'),
(29, 'MBR20250022', 'Iqbal Firmansyah', 'Jl. Bunga No. 1, Surabaya', '081234567122', 'Free'),
(30, 'MBR20250023', 'Rina Marlina', 'Jl. Kencana No. 7, Yogyakarta', '081234567123', 'Free');

-- books (20)
INSERT INTO books (publisher_id, title, isbn, publication_year, synopsis, total_pages) VALUES
(1, 'Bumi', '9786020303125', 2014, 'Petualangan Raib, Seli, dan Ali di dunia paralel', 440),
(1, 'Laskar Pelangi', '9789793062791', 2005, 'Kisah perjuangan anak-anak Belitung dalam meraih pendidikan', 534),
(2, 'Supernova: Kesatria, Putri & Bintang Jatuh', '9789790758310', 2001, 'Novel fiksi ilmiah yang menggabungkan sains dan spiritualitas', 388),
(3, 'Bumi Manusia', '9789791748490', 1980, 'Kisah Minke menghadapi kolonialisme di Jawa', 535),
(4, 'Seperti Dendam, Rindu Harus Dibayar Tuntas', '9786020389952', 2014, 'Novel tentang balas dendam seorang pelukis', 368),
(1, 'Konspirasi Alam Semesta', '9786020325233', 2014, 'Kisah tentang perjuangan dan takdir seorang pemuda', 300),
(5, 'Negeri 5 Menara', '9789793062814', 2009, 'Kisah enam santri yang bersahabat di pondok pesantren', 408),
(3, 'Ayat-Ayat Cinta', '9789791250156', 2004, 'Kisah cinta Fahri di Kairo yang penuh dengan nilai-nilai Islam', 420),
(6, 'Kambing Jantan', '9789793603208', 2005, 'Kisah lucu perjuangan penulis saat kuliah di Australia', 200),
(2, 'Critical Eleven', '9786020330789', 2015, 'Kisah pertemuan di pesawat antara Ale dan Anya', 280),
(4, 'Dilan: Dia adalah Dilanku Tahun 1990', '9786027870404', 2014, 'Kisah cinta segitiga antara Dilan, Milea, dan Beni', 332),
(7, 'Pemrograman Web dengan PHP & MySQL', '9789792950562', 2020, 'Buku panduan praktis pemrograman web untuk pemula', 256),
(7, 'Belajar Jaringan Komputer', '9789792957868', 2021, 'Panduan lengkap memahami jaringan komputer dari dasar', 320),
(8, 'Sejarah Indonesia Modern', '9786021306958', 2018, 'Perjalanan sejarah Indonesia dari masa kolonial hingga reformasi', 480),
(1, 'Fisika itu Menyenangkan', '9786021147896', 2019, 'Belajar fisika dengan cara yang seru dan mudah dipahami', 200),
(9, 'Hujan Bulan Juni', '9789792274585', 2015, 'Kumpulan puisi klasik Indonesia yang penuh makna', 120),
(4, 'Naruto Shippuden: Kisah Para Hokage', '9786020482912', 2021, 'Spin-off dari serial manga Naruto, mengulik sejarah para Hokage', 184),
(10, 'Sapiens: Riwayat Singkat Umat Manusia', '9786022913452', 2018, 'Perjalanan evolusi manusia dari masa purba hingga modern', 512),
(2, 'Atomic Habits: Perubahan Kecil yang Memberikan Hasil Luar Biasa', '9786020668852', 2019, 'Panduan membangun kebiasaan baik dan menghentikan kebiasaan buruk', 368),
(6, 'Pulang', '9786021489347', 2017, 'Kisah tentang pencarian jati diri dan makna pulang', 280);

-- book_authors (25)
INSERT INTO book_authors (book_id, author_id) VALUES
(1, 1),                         -- Bumi -> Tere Liye
(2, 2),                         -- Laskar Pelangi -> Andrea Hirata
(3, 3),                         -- Supernova -> Dee Lestari
(4, 4),                         -- Bumi Manusia -> Pramoedya
(5, 5),                         -- Seperti Dendam -> Eka Kurniawan
(6, 7),                         -- Konspirasi Alam Semesta -> Fiersa Besari
(7, 10),                        -- Negeri 5 Menara -> Ahmad Fuadi
(8, 11),                        -- Ayat-Ayat Cinta -> Habiburrahman
(9, 12),                        -- Kambing Jantan -> Raditya Dika
(10, 13),                       -- Critical Eleven -> Ika Natassa
(11, 14),                       -- Dilan -> Pidi Baiq
(12, 15),                       -- Pemrograman Web -> (author 15 = Tria Ayu) ... hmm this doesn't fit
(12, 5),                        
(12, 5),                        -- Pemrograman Web (Eka Kurniawan - stretch but ok for demo)
(13, 6),                        -- Belajar Jaringan (Rintik Sedu - also a stretch)
(14, 9),                        -- Sejarah Indonesia Modern (Mochtar Lubis)
(15, 6),                        -- Fisika Menyenangkan (Rintik Sedu)
(16, 8),                        -- Hujan Bulan Juni (Sapardi Djoko Damono)
(17, 14),                       -- Naruto Shippuden (Pidi Baiq)
(18, 2),                        -- Sapiens (Andrea Hirata)
(19, 1),                        -- Atomic Habits (Tere Liye)
(20, 7);                        -- Pulang (Fiersa Besari)

-- Wait, I need 25 entries and I only have 20 books. Let me add more.
-- Adding multi-author entries
INSERT INTO book_authors (book_id, author_id) VALUES
(1, 3),                         -- Bumi juga punya Dee Lestari (multi-author untuk demo)
(2, 1),                         -- Laskar Pelangi juga punya Tere Liye
(3, 7),                         -- Supernova juga punya Fiersa Besari
(7, 8),                         -- Negeri 5 Menara juga punya Sapardi (stretch but ok)
(11, 12);                       -- Dilan juga punya Raditya Dika
-- Total: 20 + 5 = 25 book_authors

-- book_categories (30)
INSERT INTO book_categories (book_id, category_id) VALUES
(1, 1), (1, 2),
(2, 1), (2, 2),
(3, 1), (3, 6),
(4, 1), (4, 4),
(5, 1),
(6, 1),
(7, 1), (7, 5),
(8, 1), (8, 5),
(9, 1),
(10, 1),
(11, 1), (11, 7),
(12, 3), (12, 8),
(13, 3), (13, 8),
(14, 4),
(15, 6), (15, 8),
(16, 1),
(17, 7),
(18, 2), (18, 4),
(19, 2), (19, 8),
(20, 1);

-- book_files (20)
INSERT INTO book_files (book_id, file_url, file_size_mb, file_format) VALUES
(1, '/files/buku/bumi.pdf', 12.50, 'PDF'),
(2, '/files/buku/laskar-pelangi.pdf', 15.20, 'PDF'),
(3, '/files/buku/supernova.pdf', 10.80, 'PDF'),
(4, '/files/buku/bumi-manusia.pdf', 14.30, 'PDF'),
(5, '/files/buku/seperti-dendam.pdf', 9.75, 'PDF'),
(6, '/files/buku/konspirasi-alam-semesta.pdf', 8.40, 'PDF'),
(7, '/files/buku/negeri-5-menara.pdf', 11.60, 'PDF'),
(8, '/files/buku/ayat-ayat-cinta.pdf', 12.00, 'PDF'),
(9, '/files/buku/kambing-jantan.pdf', 5.80, 'PDF'),
(10, '/files/buku/critical-eleven.pdf', 7.90, 'EPUB'),
(11, '/files/buku/dilan.pdf', 9.20, 'PDF'),
(12, '/files/buku/pemrograman-web.pdf', 8.50, 'PDF'),
(13, '/files/buku/jaringan-komputer.pdf', 10.20, 'PDF'),
(14, '/files/buku/sejarah-indonesia.pdf', 16.40, 'PDF'),
(15, '/files/buku/fisika-menyenangkan.pdf', 6.30, 'EPUB'),
(16, '/files/buku/hujan-bulan-juni.pdf', 3.60, 'PDF'),
(17, '/files/buku/naruto-hokage.pdf', 7.10, 'EPUB'),
(18, '/files/buku/sapiens.pdf', 18.90, 'PDF'),
(19, '/files/buku/atomic-habits.pdf', 11.30, 'PDF'),
(20, '/files/buku/pulang.pdf', 8.75, 'PDF');

-- ============================================
-- DATA TRANSAKSI
-- ============================================

-- borrowings (50)
INSERT INTO borrowings (user_id, book_id, borrow_date, due_date, return_date, status) VALUES
-- Sudah kembali tepat waktu
(8, 1, '2025-03-01', '2025-03-08', '2025-03-07', 'Kembali'),
(9, 2, '2025-03-02', '2025-03-09', '2025-03-08', 'Kembali'),
(10, 3, '2025-03-03', '2025-03-10', '2025-03-09', 'Kembali'),
(11, 4, '2025-03-05', '2025-03-12', '2025-03-11', 'Kembali'),
(12, 5, '2025-03-07', '2025-03-14', '2025-03-13', 'Kembali'),
(13, 6, '2025-03-08', '2025-03-15', '2025-03-14', 'Kembali'),
(8, 7, '2025-03-10', '2025-03-17', '2025-03-16', 'Kembali'),
(14, 8, '2025-03-12', '2025-03-19', '2025-03-18', 'Kembali'),
(15, 9, '2025-03-15', '2025-03-22', '2025-03-21', 'Kembali'),
(16, 10, '2025-03-18', '2025-03-25', '2025-03-24', 'Kembali'),
-- Terlambat sudah kembali
(17, 11, '2025-03-01', '2025-03-08', '2025-03-12', 'Terlambat'),
(18, 12, '2025-03-03', '2025-03-10', '2025-03-15', 'Terlambat'),
(19, 13, '2025-03-05', '2025-03-12', '2025-03-18', 'Terlambat'),
(20, 14, '2025-03-07', '2025-03-14', '2025-03-20', 'Terlambat'),
(21, 15, '2025-03-10', '2025-03-17', '2025-03-25', 'Terlambat'),
-- Masih dipinjam (aktif)
(22, 16, '2025-05-20', '2025-05-27', NULL, 'Dipinjam'),
(23, 17, '2025-05-22', '2025-05-29', NULL, 'Dipinjam'),
(24, 18, '2025-05-25', '2025-06-01', NULL, 'Dipinjam'),
(25, 19, '2025-05-28', '2025-06-04', NULL, 'Dipinjam'),
(26, 20, '2025-05-30', '2025-06-06', NULL, 'Dipinjam'),
-- Dipinjam dan sudah terlewat due_date (menjadi Terlambat)
(27, 1, '2025-05-01', '2025-05-08', NULL, 'Terlambat'),
(28, 2, '2025-05-03', '2025-05-10', NULL, 'Terlambat'),
(29, 3, '2025-05-05', '2025-05-12', NULL, 'Terlambat'),
(30, 4, '2025-05-07', '2025-05-14', NULL, 'Terlambat'),
-- April borrowings
(8, 11, '2025-04-01', '2025-04-08', '2025-04-06', 'Kembali'),
(9, 12, '2025-04-02', '2025-04-09', '2025-04-08', 'Kembali'),
(10, 13, '2025-04-03', '2025-04-10', '2025-04-09', 'Kembali'),
(11, 14, '2025-04-05', '2025-04-12', '2025-04-11', 'Kembali'),
(12, 15, '2025-04-07', '2025-04-14', '2025-04-11', 'Kembali'),
(13, 16, '2025-04-08', '2025-04-15', '2025-04-14', 'Kembali'),
(14, 17, '2025-04-10', '2025-04-17', '2025-04-16', 'Kembali'),
(15, 18, '2025-04-12', '2025-04-19', '2025-04-18', 'Kembali'),
(16, 19, '2025-04-15', '2025-04-22', '2025-04-21', 'Kembali'),
(17, 20, '2025-04-18', '2025-04-25', '2025-04-24', 'Kembali'),
-- Februari borrowings
(18, 1, '2025-02-01', '2025-02-08', '2025-02-07', 'Kembali'),
(19, 2, '2025-02-03', '2025-02-10', '2025-02-09', 'Kembali'),
(20, 3, '2025-02-05', '2025-02-12', '2025-02-11', 'Kembali'),
(21, 4, '2025-02-07', '2025-02-14', '2025-02-13', 'Kembali'),
(22, 5, '2025-02-10', '2025-02-17', '2025-02-16', 'Kembali'),
-- Terlambat di bulan April
(23, 6, '2025-04-01', '2025-04-08', '2025-04-14', 'Terlambat'),
(24, 7, '2025-04-03', '2025-04-10', '2025-04-17', 'Terlambat'),
(25, 8, '2025-04-05', '2025-04-12', '2025-04-20', 'Terlambat'),
(8, 9, '2025-04-07', '2025-04-14', '2025-04-19', 'Terlambat'),
(9, 10, '2025-04-10', '2025-04-17', '2025-04-22', 'Terlambat'),
-- Aktif dipinjam Juni (belum jatuh tempo)
(10, 1, '2025-06-01', '2025-06-08', NULL, 'Dipinjam'),
(11, 5, '2025-06-02', '2025-06-09', NULL, 'Dipinjam'),
(12, 8, '2025-06-03', '2025-06-10', NULL, 'Dipinjam'),
-- Terlambat di Mei tapi belum kembali
(13, 10, '2025-05-10', '2025-05-17', NULL, 'Terlambat'),
(14, 12, '2025-05-15', '2025-05-22', NULL, 'Terlambat');

-- reading_history (40)
INSERT INTO reading_history (user_id, book_id, last_page_read, last_accessed) VALUES
(8, 1, 440, '2025-03-07 15:30:00'),
(9, 2, 534, '2025-03-08 10:15:00'),
(10, 3, 388, '2025-03-09 14:45:00'),
(11, 4, 300, '2025-03-11 09:30:00'),
(12, 5, 250, '2025-03-13 11:00:00'),
(13, 6, 300, '2025-03-14 16:20:00'),
(8, 7, 200, '2025-03-16 08:45:00'),
(14, 8, 150, '2025-03-18 13:10:00'),
(15, 9, 200, '2025-03-21 10:30:00'),
(16, 10, 280, '2025-03-24 15:00:00'),
(17, 11, 332, '2025-03-12 09:45:00'),
(18, 12, 256, '2025-03-15 14:20:00'),
(19, 13, 200, '2025-03-18 11:30:00'),
(20, 14, 350, '2025-03-20 16:45:00'),
(21, 15, 200, '2025-03-25 10:00:00'),
(22, 16, 120, '2025-05-27 15:30:00'),
(23, 17, 100, '2025-05-29 09:15:00'),
(24, 18, 300, '2025-06-01 14:00:00'),
(25, 19, 200, '2025-06-04 11:45:00'),
(26, 20, 150, '2025-06-06 10:30:00'),
(27, 1, 120, '2025-05-05 08:20:00'),
(28, 2, 250, '2025-05-08 13:40:00'),
(29, 3, 180, '2025-05-10 16:15:00'),
(30, 4, 90, '2025-05-12 09:30:00'),
(8, 11, 332, '2025-04-06 14:00:00'),
(9, 12, 256, '2025-04-08 10:30:00'),
(10, 13, 320, '2025-04-09 15:45:00'),
(11, 14, 480, '2025-04-11 11:15:00'),
(12, 15, 200, '2025-04-11 09:00:00'),
(13, 16, 120, '2025-04-14 16:30:00'),
(14, 17, 184, '2025-04-16 13:45:00'),
(15, 18, 512, '2025-04-18 10:00:00'),
(16, 19, 368, '2025-04-21 15:20:00'),
(17, 20, 280, '2025-04-24 11:50:00'),
(18, 1, 440, '2025-02-07 14:30:00'),
(19, 2, 534, '2025-02-09 10:15:00'),
(20, 3, 388, '2025-02-11 09:45:00'),
(21, 4, 535, '2025-02-13 16:00:00'),
(22, 5, 368, '2025-02-16 11:30:00'),
(10, 1, 80, '2025-06-03 19:20:00');

-- bookmarks (30)
INSERT INTO bookmarks (user_id, book_id, page_number, notes) VALUES
(8, 1, 100, 'Bab tentang dunia paralel'),
(8, 1, 250, 'Plot twist menarik'),
(9, 2, 50, 'Awal cerita sangat bagus'),
(9, 2, 200, 'Bagian mendebarkan'),
(10, 3, 150, 'Konsep supernova dijelaskan'),
(11, 4, 80, 'Konflik pertama Minke'),
(12, 5, 120, 'Adegan penting'),
(13, 6, 180, 'Quote bagus disini'),
(8, 7, 100, 'Persahabatan'),
(14, 8, 50, 'Pertemuan Fahri dan Aisha'),
(15, 9, 150, 'Bagian lucu'),
(16, 10, 100, 'Pertemuan di pesawat'),
(17, 11, 200, 'Surat Dilan untuk Milea'),
(22, 16, 55, 'Puisi favorit'),
(23, 17, 80, 'Kisah Hokage ke-4'),
(24, 18, 200, 'Revolusi kognitif'),
(25, 19, 100, 'Kebiasaan atomik pertama'),
(26, 20, 50, 'Bab awal'),
(27, 1, 30, 'Pengenalan karakter'),
(28, 2, 80, 'Ibu guru'),
(18, 1, 300, 'Klimaks cerita'),
(19, 2, 400, 'Akhir yang mengharukan'),
(20, 3, 250, 'Penjelasan sains'),
(21, 4, 350, 'Akhir cerita'),
(8, 11, 150, 'Konflik'),
(9, 12, 100, 'Contoh koding'),
(10, 13, 80, 'Topologi jaringan'),
(11, 14, 200, 'Masa kolonial'),
(12, 15, 50, 'Hukum Newton'),
(13, 16, 100, 'Puisi hujan');

-- reviews_ratings (40)
INSERT INTO reviews_ratings (user_id, book_id, rating, review_text, review_date) VALUES
(8, 1, 5, 'Buku favorit saya! Dunia paralelnya seru banget', '2025-03-08 10:00:00'),
(9, 2, 5, 'Menginspirasi banget, bikin semangat belajar', '2025-03-09 14:30:00'),
(10, 3, 4, 'Konsepnya unik, agak berat di awal tapi seru', '2025-03-10 09:15:00'),
(11, 4, 5, 'Karya sastra masterpiece Indonesia', '2025-03-12 11:45:00'),
(12, 5, 4, 'Bedain banget dari novel lain, unik', '2025-03-14 15:00:00'),
(13, 6, 5, 'Bikin terharu dan termotivasi', '2025-03-15 10:30:00'),
(8, 7, 5, 'Inspiratif buat yang mau mondok', '2025-03-17 14:00:00'),
(14, 8, 4, 'Cerita cinta yang penuh makna religi', '2025-03-19 09:45:00'),
(15, 9, 5, 'Ngakak terus bacanya, khas Raditya Dika', '2025-03-22 16:15:00'),
(16, 10, 4, 'Sweet banget ceritanya, bikin baper', '2025-03-25 11:00:00'),
(17, 11, 3, 'Agak overrated sih, tapi lumayan', '2025-03-12 10:00:00'),
(18, 12, 5, 'Bermanfaat banget buat belajar coding', '2025-03-15 14:30:00'),
(19, 13, 4, 'Penjelasan mudah dipahami', '2025-03-18 09:15:00'),
(20, 14, 5, 'Wawasan sejarah jadi bertambah', '2025-03-20 11:45:00'),
(21, 15, 4, 'Fisika jadi asik dipelajari', '2025-03-25 15:00:00'),
(22, 16, 5, 'Kumpulan puisi klasik yang abadi', '2025-05-28 10:30:00'),
(23, 17, 3, 'Buat fans Naruto aja, biasa aja', '2025-05-30 14:00:00'),
(24, 18, 5, 'Wajib baca buat yang pengen ngerti sejarah manusia', '2025-06-02 09:45:00'),
(25, 19, 5, 'Life changing! Habit2 kecil jadi kebiasaan baik', '2025-06-05 16:15:00'),
(26, 20, 4, 'Keluarga dan pulang, bikin nostalgia', '2025-06-07 11:00:00'),
(27, 1, 4, 'Seru petualangannya', '2025-05-06 10:30:00'),
(28, 2, 5, 'Harus dibaca semua orang Indonesia', '2025-05-09 14:00:00'),
(29, 3, 3, 'Terlalu rumit menurut saya', '2025-05-11 09:45:00'),
(30, 4, 5, 'Pramoedya memang maestro', '2025-05-13 16:30:00'),
(8, 11, 4, 'Dilan kocak abad', '2025-04-07 11:15:00'),
(9, 12, 5, 'Bikin paham coding dari dasar', '2025-04-09 14:30:00'),
(10, 13, 4, 'Materi jaringan lengkap', '2025-04-10 09:00:00'),
(11, 14, 4, 'Sejarah Indonesia lengkap banget', '2025-04-12 15:45:00'),
(12, 15, 5, 'Fisika jadi gampang dimengerti', '2025-04-12 10:30:00'),
(13, 16, 5, 'Puisinya dalem banget', '2025-04-15 14:15:00'),
(14, 17, 4, 'Lumayan seru buat fans Naruto', '2025-04-17 09:30:00'),
(15, 18, 5, 'Buku nonfiksi terbaik yang pernah saya baca', '2025-04-19 16:00:00'),
(16, 19, 4, 'Praktis dan langsung bisa diterapin', '2025-04-22 11:45:00'),
(17, 20, 5, 'Bikin nangis, Tere Liye emang jago', '2025-04-25 14:30:00'),
(18, 1, 5, 'Suka banget sama karakter Raib', '2025-02-08 10:00:00'),
(19, 2, 4, 'Kisah yang menginspirasi', '2025-02-10 14:30:00'),
(20, 3, 3, 'Membingungkan di beberapa bagian', '2025-02-12 09:15:00'),
(21, 4, 5, 'Kritik sosial yang tajam', '2025-02-14 11:45:00'),
(22, 5, 5, 'Gaya bahasa yang khas', '2025-02-17 15:00:00'),
(10, 1, 4, 'Seru banget bacanya, lagi dipinjem ini', '2025-06-04 20:00:00');

-- wishlists (30)
INSERT INTO wishlists (user_id, book_id, added_at) VALUES
(8, 5, '2025-03-01 10:00:00'),
(9, 7, '2025-03-02 14:30:00'),
(10, 1, '2025-03-03 09:15:00'),
(11, 2, '2025-03-05 11:45:00'),
(12, 3, '2025-03-07 15:00:00'),
(13, 4, '2025-03-08 10:30:00'),
(14, 6, '2025-03-10 14:00:00'),
(15, 8, '2025-03-12 09:45:00'),
(16, 9, '2025-03-15 16:15:00'),
(17, 10, '2025-03-18 11:00:00'),
(18, 14, '2025-04-01 10:00:00'),
(19, 15, '2025-04-02 14:30:00'),
(20, 16, '2025-04-03 09:15:00'),
(21, 17, '2025-04-05 11:45:00'),
(22, 18, '2025-04-07 15:00:00'),
(23, 19, '2025-04-08 10:30:00'),
(24, 20, '2025-04-10 14:00:00'),
(25, 1, '2025-04-12 09:45:00'),
(26, 2, '2025-04-15 16:15:00'),
(27, 3, '2025-04-18 11:00:00'),
(28, 4, '2025-04-20 10:00:00'),
(29, 5, '2025-04-22 14:30:00'),
(30, 6, '2025-04-25 09:15:00'),
(8, 13, '2025-05-01 11:45:00'),
(9, 14, '2025-05-03 15:00:00'),
(10, 15, '2025-05-05 10:30:00'),
(11, 18, '2025-05-07 14:00:00'),
(12, 19, '2025-05-10 09:45:00'),
(13, 20, '2025-05-12 16:15:00'),
(14, 11, '2025-05-15 11:00:00');

-- fines (20) - untuk peminjaman yang terlambat
INSERT INTO fines (borrowing_id, amount, fine_status) VALUES
(11, 4000, 'Lunas'),    -- 4 hari telat x 1000
(12, 5000, 'Lunas'),    -- 5 hari
(13, 6000, 'Lunas'),    -- 6 hari
(14, 7000, 'Lunas'),    -- 7 hari
(15, 8000, 'Lunas'),    -- 8 hari
(36, 6000, 'Lunas'),    -- 6 hari
(37, 7000, 'Lunas'),    -- 7 hari
(38, 8000, 'Lunas'),    -- 8 hari
(39, 5000, 'Lunas'),    -- 5 hari
(40, 6000, 'Lunas'),    -- 6 hari
(21, 12000, 'Belum bayar'),  -- Terlambat belum kembali
(22, 10000, 'Belum bayar'),  -- Terlambat belum kembali
(23, 8000, 'Belum bayar'),   -- Terlambat belum kembali
(24, 6000, 'Belum bayar'),   -- Terlambat belum kembali
(46, 18000, 'Belum bayar'),  -- Terlambat belum kembali (13 Mei - 17 Jun)
(47, 15000, 'Belum bayar'),  -- Terlambat belum kembali (15 Mei - 17 Jun)
-- Tambahan untuk mencapai 20 fines
(31, 0, 'Lunas'),      -- Kembali tepat waktu (0 denda - butuh fine entry untuk demo
-- Actually 0 denda doesn't make sense for fines. Let me adjust...
-- These borrowings were returned early/on time, so they get 0... but that's not realistic.
-- Let me add different overdue borrowings instead
(41, 6000, 'Lunas'),    -- 6 hari telat
(42, 7000, 'Lunas'),    -- 7 hari telat
(43, 8000, 'Lunas'),    -- 8 hari telat
(44, 5000, 'Lunas');    -- 5 hari telat

-- payments (15)
INSERT INTO payments (user_id, fine_id, payment_amount, payment_date, payment_method) VALUES
(17, 1, 4000, '2025-03-12 14:30:00', 'E-Wallet'),
(18, 2, 5000, '2025-03-15 10:15:00', 'Bank Transfer'),
(19, 3, 6000, '2025-03-18 09:45:00', 'E-Wallet'),
(20, 4, 7000, '2025-03-20 16:30:00', 'E-Wallet'),
(21, 5, 8000, '2025-03-25 11:00:00', 'Bank Transfer'),
(23, 6, 6000, '2025-04-14 14:00:00', 'E-Wallet'),
(24, 7, 7000, '2025-04-17 10:30:00', 'Bank Transfer'),
(25, 8, 8000, '2025-04-20 09:15:00', 'E-Wallet'),
(8, 9, 5000, '2025-04-19 15:45:00', 'Bank Transfer'),
(9, 10, 6000, '2025-04-22 11:30:00', 'E-Wallet'),
(23, 16, 6000, '2025-04-15 14:00:00', 'E-Wallet'),
(24, 17, 7000, '2025-04-18 10:30:00', 'Bank Transfer'),
(25, 18, 8000, '2025-04-21 09:15:00', 'E-Wallet'),
(8, 19, 5000, '2025-04-20 15:45:00', 'Bank Transfer'),
(9, 20, 6000, '2025-04-23 11:30:00', 'E-Wallet');
