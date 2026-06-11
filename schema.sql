-- ============================================================
-- Database Perpustakaan Digital
-- UAS Project
-- ============================================================

CREATE DATABASE IF NOT EXISTS db_perpustakaan;
USE db_perpustakaan;

-- ----------------------------------------
-- Tabel Buku
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS buku (
    id_buku INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(255) NOT NULL,
    penulis VARCHAR(255),
    penerbit VARCHAR(255),
    tahun_terbit YEAR,
    stok INT DEFAULT 1
);

-- ----------------------------------------
-- Tabel Anggota
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS anggota (
    id_anggota INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    no_telepon VARCHAR(20)
);

-- ----------------------------------------
-- Tabel Peminjaman
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS peminjaman (
    id_pinjam INT PRIMARY KEY AUTO_INCREMENT,
    id_buku INT NOT NULL,
    id_anggota INT NOT NULL,
    tgl_pinjam DATE NOT NULL,
    tgl_kembali DATE DEFAULT NULL,
    status ENUM('dipinjam', 'dikembalikan') DEFAULT 'dipinjam',
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE,
    FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE
);

-- ----------------------------------------
-- Contoh Data
-- ----------------------------------------
INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, stok) VALUES
('Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 3),
('Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 2),
('Perahu Kertas', 'Dee Lestari', 'Bentang Pustaka', 2009, 1),
('Sang Pemimpi', 'Andrea Hirata', 'Bentang Pustaka', 2006, 2),
('Ronggeng Dukuh Paruk', 'Ahmad Tohari', 'Gramedia', 1982, 1);

INSERT INTO anggota (nama, email, no_telepon) VALUES
('Rafif', 'rafif@email.com', '081234567890'),
('Sari', 'sari@email.com', '081234567891'),
('Budi', 'budi@email.com', '081234567892');
