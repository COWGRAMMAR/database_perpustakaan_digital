# PLAN: Implementasi Database Perpustakaan Digital
> DBMS: MySQL (XAMPP)
> Stack: PHP + MySQL
> Referensi skema: lihat `proposal.md`

---

## CHECKLIST PROGRESS

### 1. DDL (Data Definition Language)
- [ ] Buat database `perpustakaan_digital`
- [ ] Buat semua tabel sesuai urutan dependency (parent dulu, baru child):

**Urutan CREATE TABLE:**
1. `roles`
2. `publishers`
3. `authors`
4. `categories`
5. `users`
6. `user_roles`
7. `staff_profiles`
8. `member_profiles`
9. `books`
10. `book_authors`
11. `book_categories`
12. `book_files`
13. `borrowings`
14. `reading_history`
15. `bookmarks`
16. `reviews_ratings`
17. `wishlists`
18. `fines`
19. `payments`
20. `audit_logs`

**Koreksi typo dari proposal asli:**
- `tittle` → `title` (tabel books)
- `fune_id` → `fine_id` (tabel payments)
- `last_accesed` → `last_accessed` (tabel reading_history)
- `phone_number` tipe INT → VARCHAR(15) (staff_profiles & member_profiles)

---

### 2. DML (Data Manipulation Language)
- [ ] Insert minimal 100 data dummy yang realistis untuk kebutuhan reporting
- [ ] Data harus mencakup variasi: peminjaman terlambat, sudah lunas, member premium, dll
- [ ] Urutan insert: master data dulu (roles, publishers, authors, categories), lalu users, lalu transaksi

**Target data per tabel (minimal):**
| Tabel | Minimal |
|---|---|
| roles | 3 |
| publishers | 10 |
| authors | 15 |
| categories | 8 |
| users | 30 |
| user_roles | 30 |
| staff_profiles | 5 |
| member_profiles | 25 |
| books | 20 |
| book_authors | 25 |
| book_categories | 30 |
| book_files | 20 |
| borrowings | 50 |
| reading_history | 40 |
| bookmarks | 30 |
| reviews_ratings | 40 |
| wishlists | 30 |
| fines | 20 |
| payments | 15 |
| audit_logs | otomatis dari trigger |

---

### 3. TRIGGER (Minimal 3)

#### Trigger 1: `trg_set_due_date`
- **Event:** AFTER INSERT ON `borrowings`
- **Fungsi:** Auto-set `due_date` = `borrow_date` + 7 hari
- **Kenapa:** Biar due_date tidak perlu diisi manual, konsisten

#### Trigger 2: `trg_auto_fine`
- **Event:** AFTER UPDATE ON `borrowings`
- **Kondisi:** Jika `return_date` > `due_date` AND `status` = 'Terlambat'
- **Fungsi:** Auto INSERT ke tabel `fines`
- **Kalkulasi denda:** `(return_date - due_date) * 1000` (Rp1.000/hari)

#### Trigger 3: `trg_audit_log`
- **Event:** AFTER INSERT / AFTER UPDATE / AFTER DELETE ON tabel-tabel penting
- **Target tabel:** `borrowings`, `fines`, `payments`, `users`
- **Fungsi:** Otomatis catat aksi ke `audit_logs`
- **Catatan:** Buat per tabel, misal `trg_audit_borrowings_insert`, dll

---

### 4. USER DEFINED FUNCTION / UDF (Minimal 2)

#### Function 1: `fn_hitung_denda(borrowing_id INT)`
- **Return:** DECIMAL(10,2)
- **Logika:** Ambil `due_date` dan `return_date` dari borrowings, hitung selisih hari x Rp1.000
- **Return 0** jika tidak terlambat

#### Function 2: `fn_avg_rating(book_id INT)`
- **Return:** DECIMAL(3,2)
- **Logika:** SELECT AVG(rating) FROM reviews_ratings WHERE book_id = input
- **Return 0.00** jika belum ada review

---

### 5. AGGREGATE FUNCTION (Minimal 5 Query)

1. **SUM** — Total denda yang belum dibayar per user
2. **AVG** — Rata-rata rating per kategori buku
3. **MAX** — Buku dengan jumlah peminjaman terbanyak dalam sebulan
4. **MIN** — Buku dengan halaman paling sedikit per kategori
5. **COUNT** — Jumlah member aktif per tipe keanggotaan

---

### 6. TCL (Transaction Control Language)

- **Implementasi di dalam:** `sp_bayar_denda` (Stored Procedure)
- **COMMIT:** Jika update fines + insert payments berhasil semua
- **ROLLBACK:** Jika salah satu gagal (misal fine_id tidak ditemukan)
- Sertakan screenshot hasil pengujian: sekali sukses (commit), sekali gagal (rollback)

---

### 7. TABLE LOCKING

- **Demo di:** Proses peminjaman buku (`sp_pinjam_buku`)
- **Skenario:** Simulasikan 2 user meminjam buku yang sama secara bersamaan
- **Command:**
  ```sql
  LOCK TABLES borrowings WRITE;
  -- proses insert
  UNLOCK TABLES;
  ```
- Dokumentasikan dengan screenshot sebelum dan sesudah locking

---

### 8. STORED PROCEDURE (Minimal 3)

#### SP 1: `sp_pinjam_buku(p_user_id INT, p_book_id INT)`
- Cek apakah user sudah meminjam buku ini dan belum dikembalikan
- Jika aman, INSERT ke `borrowings` (due_date di-handle trigger)
- Jika tidak aman, kirim pesan error
- Gunakan LOCK TABLES untuk demo concurrency

#### SP 2: `sp_bayar_denda(p_fine_id INT, p_payment_method VARCHAR(20))`
- Cek apakah fine_id valid dan statusnya 'Belum bayar'
- START TRANSACTION
- UPDATE `fines` SET fine_status = 'Lunas'
- INSERT ke `payments`
- COMMIT / ROLLBACK

#### SP 3: `sp_laporan_bulanan(p_bulan INT, p_tahun INT)`
- Return rekap dalam 1 prosedur:
  - Total peminjaman bulan itu
  - Total denda masuk bulan itu
  - Jumlah member baru bulan itu
  - Buku paling sering dipinjam bulan itu

---

### 9. CURSOR (Minimal 1)

#### Cursor: `cur_cek_keterlambatan` dalam SP `sp_proses_keterlambatan()`
- **Fungsi:** Iterasi semua `borrowings` yang `due_date` < CURDATE() dan `status` = 'Dipinjam'
- **Per baris:** UPDATE status ke 'Terlambat', trigger otomatis generate fine

---

### 10. BACKUP & RESTORE

#### Backup
- **Metode:** `mysqldump` via command line
- **Command:**
  ```bash
  mysqldump -u root -p perpustakaan_digital > backup_perpus.sql
  ```

#### Restore
- **Command:**
  ```bash
  mysql -u root -p perpustakaan_digital < backup_perpus.sql
  ```
- **Pengujian restore:** Hapus database -> buat ulang -> restore -> verifikasi data kembali

---

### 11. REPORTING (Minimal 5 Laporan)

#### Laporan 1: Buku Terlaris
- Buku paling sering dipinjam (all time / per bulan)
- Kolom: judul, penulis, kategori, total_dipinjam

#### Laporan 2: Laporan Peminjaman Per Periode
- Filter by bulan/tahun
- Kolom: nama_member, judul_buku, borrow_date, due_date, return_date, status

#### Laporan 3: Total Denda Per Bulan
- Total denda masuk vs belum dibayar
- Kolom: bulan, total_denda_masuk, total_belum_bayar, total_lunas

#### Laporan 4: Jumlah Member Baru Per Bulan
- Pertumbuhan member per bulan
- Kolom: bulan, jumlah_member_baru, tipe_free, tipe_premium

#### Laporan 5: Statistik Buku Per Kategori
- Kolom: kategori, jumlah_buku, rata_rata_rating, total_dipinjam

---

### 12. DASHBOARD (Minimal 2)

#### Dashboard Admin
- Total peminjaman aktif
- Total denda belum dibayar
- Jumlah member aktif
- Buku terpopuler bulan ini
- Grafik peminjaman 6 bulan terakhir

#### Dashboard Staff
- Daftar peminjaman jatuh tempo hari ini / sudah terlambat
- Total pengembalian hari ini
- Aktivitas staff (dari audit_logs)
- Laporan peminjaman per rentang tanggal

---

## URUTAN PENGERJAAN YANG DISARANKAN

1. DDL — buat semua tabel
2. DML — insert data dummy
3. UDF — buat 2 function
4. Trigger — buat 3 trigger
5. Stored Procedure — buat 3 SP (termasuk TCL & locking di dalamnya)
6. Cursor — buat 1 cursor
7. Query Aggregate — buat 5 query
8. Backup & Restore — dokumentasi
9. Reporting — buat 5 query laporan
10. Dashboard — implementasi di PHP

---

## CATATAN PENTING

- Pisahkan file SQL per bagian: `ddl.sql`, `dml.sql`, `trigger.sql`, `function.sql`, `procedure.sql`, `cursor.sql`, `reporting.sql`
- Tarif denda default: **Rp1.000/hari**
- Durasi peminjaman default: **7 hari**
- Password user di-hash dengan MD5 untuk demo (atau bcrypt di PHP)
- Untuk demo TCL: buat skenario gagal (fine_id tidak ada) agar ROLLBACK bisa ditunjukkan
- Stack: PHP + MySQL via XAMPP
