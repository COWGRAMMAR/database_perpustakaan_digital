# Proposal Database: Sistem Perpustakaan Digital

> Dokumen ini adalah konversi dari proposal asli kelompok (perpus_db_detail.docx).
> Semua typo dari dokumen asli sudah dikoreksi:
> - `tittle` → `title`
> - `fune_id` → `fine_id`
> - `last_accesed` → `last_accessed`
> - `phone_number` tipe `INT` → `VARCHAR(15)`

---

## MODUL 1: MANAJEMEN PENGGUNA & AKSES (RBAC)

### Tabel: `users`
> Menyimpan data akun untuk login

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID unik untuk akun user |
| username | VARCHAR(50) | UNIQUE | Nama pengguna untuk login |
| email | VARCHAR(100) | UNIQUE | Alamat email pengguna |
| password | VARCHAR(255) | - | Password akun yang sudah di-hash |
| is_active | BOOLEAN | - | Status keaktifan (default: TRUE) |
| created_at | TIMESTAMP | - | Waktu pembuatan akun |

---

### Tabel: `roles`
> Daftar role di sistem

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID unik untuk jenis role |
| role_name | ENUM('Admin', 'Staff', 'Pembaca') | - | Nama role |

---

### Tabel: `user_roles`
> Tabel penghubung User dan Role

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID unik untuk tabel hubung |
| user_id | INT | FK → users.id | ID pengguna |
| role_id | INT | FK → roles.id | ID role |

---

### Tabel: `staff_profiles`
> Detail data profil Staff

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID unik untuk profil staff |
| user_id | INT | FK → users.id | ID pengguna |
| staff_number | VARCHAR(20) | UNIQUE | Nomor induk karyawan/NIP |
| full_name | VARCHAR(100) | - | Nama lengkap staff |
| phone_number | VARCHAR(15) | - | Nomor telepon staff |

---

### Tabel: `member_profiles`
> Detail data profil Pembaca/Customer

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID unik untuk profil pembaca |
| user_id | INT | FK → users.id | ID pengguna |
| member_number | VARCHAR(20) | UNIQUE | Nomor kartu anggota digital |
| full_name | VARCHAR(100) | - | Nama lengkap pembaca |
| address | TEXT | - | Alamat pembaca |
| phone_number | VARCHAR(15) | - | Nomor telepon pembaca |
| membership_type | ENUM('Free', 'Premium') | - | Jenis keanggotaan |

---

## MODUL 2: KATALOG BUKU DIGITAL

### Tabel: `books`
> Data utama buku

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID unik untuk data buku |
| publisher_id | INT | FK → publishers.id | ID penerbit |
| title | VARCHAR(255) | - | Judul lengkap buku digital |
| isbn | VARCHAR(13) | UNIQUE | Nomor ISBN buku |
| publication_year | YEAR | - | Tahun terbit buku |
| synopsis | TEXT | - | Ringkasan/sinopsis buku |
| total_pages | INT | - | Jumlah total halaman buku |

---

### Tabel: `authors`
> Data penulis

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID unik untuk data penulis |
| author_name | VARCHAR(100) | - | Nama lengkap penulis buku |
| bio | TEXT | - | Biografi singkat penulis |

---

### Tabel: `book_authors`
> Tabel penghubung Buku & Penulis (Many to Many)

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID unik untuk tabel hubung |
| book_id | INT | FK → books.id | ID buku |
| author_id | INT | FK → authors.id | ID penulis |

---

### Tabel: `publishers`
> Data penerbit

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID unik untuk data penerbit |
| publisher_name | VARCHAR(100) | - | Nama perusahaan penerbit |
| address | TEXT | - | Alamat kantor penerbit |

---

### Tabel: `categories`
> Kategori buku (contoh isi: 'Teknologi', 'Fiksi', dll)

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID unik untuk kategori |
| category_name | VARCHAR(50) | - | Nama kategori |

---

### Tabel: `book_categories`
> Tabel penghubung Buku & Kategori (Many to Many)

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID relasi buku dan kategori |
| book_id | INT | FK → books.id | ID buku |
| category_id | INT | FK → categories.id | ID kategori |

---

### Tabel: `book_files`
> Data file digital dari buku

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID file buku |
| book_id | INT | FK → books.id | ID buku |
| file_url | VARCHAR(255) | - | Lokasi penyimpanan file digital |
| file_size_mb | DECIMAL(5,2) | - | Ukuran file dalam MB |
| file_format | ENUM('PDF', 'EPUB') | - | Format file |

---

## MODUL 3: AKTIVITAS & TRANSAKSI PEMBACA

### Tabel: `borrowings`
> Transaksi peminjaman akses buku

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID transaksi peminjaman |
| user_id | INT | FK → users.id | ID pengguna yang meminjam |
| book_id | INT | FK → books.id | ID buku yang dipinjam |
| borrow_date | DATE | - | Tanggal peminjaman |
| due_date | DATE | - | Batas pengembalian buku |
| return_date | DATE | NULLABLE | Tanggal pengembalian buku |
| status | ENUM('Dipinjam', 'Kembali', 'Terlambat') | - | Status peminjaman |

---

### Tabel: `reading_history`
> Log histori membaca user secara realtime

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID histori membaca |
| user_id | INT | FK → users.id | ID pengguna |
| book_id | INT | FK → books.id | ID buku |
| last_page_read | INT | - | Halaman terakhir yang dibaca |
| last_accessed | TIMESTAMP | - | Waktu terakhir mengakses buku |

---

### Tabel: `bookmarks`
> Fitur penanda halaman buku digital

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID bookmark |
| user_id | INT | FK → users.id | ID pengguna |
| book_id | INT | FK → books.id | ID buku |
| page_number | INT | - | Nomor halaman yang ditandai |
| notes | VARCHAR(255) | NULLABLE | Catatan pada halaman |

---

### Tabel: `reviews_ratings`
> Ulasan dan rating dari pembaca

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID ulasan |
| user_id | INT | FK → users.id | ID pengguna |
| book_id | INT | FK → books.id | ID buku |
| rating | INT | - | Nilai rating (skala 1–5) |
| review_text | TEXT | - | Isi ulasan |
| review_date | TIMESTAMP | - | Tanggal ulasan dibuat |

---

### Tabel: `wishlists`
> Daftar buku yang ingin dibaca nanti

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID wishlist |
| user_id | INT | FK → users.id | ID pengguna |
| book_id | INT | FK → books.id | ID buku yang ingin dibaca |
| added_at | TIMESTAMP | - | Tanggal ditambahkan ke wishlist |

---

## MODUL 4: DENDA & FINANSIAL

### Tabel: `fines`
> Pencatatan denda keterlambatan

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID denda |
| borrowing_id | INT | FK → borrowings.id | ID transaksi peminjaman asal |
| amount | DECIMAL(10,2) | - | Nominal denda |
| fine_status | ENUM('Belum bayar', 'Lunas') | - | Status pembayaran denda |

---

### Tabel: `payments`
> Transaksi pembayaran denda/premium

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID pembayaran |
| user_id | INT | FK → users.id | ID pengguna |
| fine_id | INT | FK → fines.id, NULLABLE | ID denda (null jika pembayaran premium) |
| payment_amount | DECIMAL(10,2) | - | Nominal pembayaran |
| payment_date | TIMESTAMP | - | Tanggal pembayaran |
| payment_method | ENUM('E-Wallet', 'Bank Transfer') | - | Metode pembayaran |

---

## MODUL 5: SISTEM & LOG AUDIT

### Tabel: `audit_logs`
> Catatan aktivitas staf/admin untuk keamanan data

| Nama Kolom | Tipe Data | Key | Keterangan |
|---|---|---|---|
| id | INT | PK | ID log aktivitas |
| user_id | INT | FK → users.id | ID admin atau staff |
| action | VARCHAR(50) | - | Jenis aksi yang dilakukan |
| table_name | VARCHAR(50) | - | Nama tabel yang dimanipulasi |
| description | TEXT | - | Detail perubahan data |
| ip_address | VARCHAR(45) | - | Alamat IP pelaku |
| created_at | TIMESTAMP | - | Waktu aktivitas dicatat |

---

## SITEMAP & USER FLOW

**3 Role Utama:** Admin, Staff, Pembaca (Customer)

### Admin
- Dashboard Admin: ringkasan sistem & statistik
- Manajemen User: tambah/edit/hapus user, verifikasi pembaca, role user, status akun
- Manajemen Staff: data staff, profil staff
- Manajemen Buku: buku, penulis, penerbit, kategori, file buku
- Manajemen Transaksi: peminjaman, pengembalian, denda, pembayaran
- Laporan: laporan buku, laporan peminjaman, laporan denda, laporan kategori, statistik pembaca
- Audit Log: aktivitas sistem

### Staff
- Dashboard Staff: ringkasan data & aktivitas perpustakaan
- Manajemen Buku: daftar buku, tambah buku, edit buku, daftar penulis, daftar penerbit, daftar kategori, file buku
- Manajemen Peminjaman: data peminjaman, pengembalian, riwayat peminjaman
- Manajemen Denda: daftar denda, verifikasi pembayaran
- Data Pembaca: daftar anggota, detail anggota
- Audit System: menampilkan aktivitas seluruh staff

### Pembaca (Customer)
- Dashboard: ringkasan aktivitas membaca
- Profil Saya: data pribadi, edit profil, pengaturan akun
- Katalog Buku: semua buku, kategori, detail buku, download/baca buku
- Aktivitas Membaca: riwayat membaca, bookmark, wishlist
- Peminjaman: buku dipinjam, riwayat peminjaman, riwayat pengembalian
- Ulasan & Rating: tulis ulasan, riwayat ulasan
- Denda & Pembayaran: riwayat pembayaran, metode pembayaran
