# Dokumentasi Belajar — Sistem Basis Data
## Project: Perpustakaan Digital (PHP + MySQL, 20 Tabel)

> Dokumen ini dibuat khusus buat belajar konsep Basis Data lewat contoh nyata.
> Bukan dokumentasi teknis — ini penjelasan santai biar paham teorinya sambil liat
> implementasinya di project.

---

## 1. Perancangan Basis Data (ERD, Relasi, Normalisasi, Data Dictionary)

### (a) Teori Singkat

**Perancangan basis data** adalah tahap paling awal sebelum ngoding. Kita perlu
mengidentifikasi:
- **Entitas** (benda/objek yang perlu disimpan datanya — misal: Buku, User, Peminjaman)
- **Atribut** (property dari entitas — misal: judul, ISBN, tanggal pinjam)
- **Relasi** (gimana entitas saling nyambung — one-to-many, many-to-many)
- **Normalisasi** (proses ngilangin redudansi data biar gak boros tempat dan gak
  inkonsisten sampai bentuk ke-3 / 3NF)
- **Data Dictionary** (kamus yang jelasin tiap kolom di tiap tabel)

### (b) Contoh dari Project Perpustakaan Digital

**Relasi antar entitas (gambaran kasar):**

```
User ──> User_Roles ──> Roles          (Many-to-Many via pivot)
User ──> Member_Profiles                (One-to-One)
User ──> Staff_Profiles                 (One-to-One)
User ──> Borrowings ──> Books           (One-to-Many: 1 user bisa pinjam banyak buku)
Books ──> Book_Authors ──> Authors      (Many-to-Many via pivot)
Books ──> Book_Categories ──> Categories (Many-to-Many via pivot)
Books ──> Book_Files                    (One-to-Many: 1 buku bisa punya banyak file)
Borrowings ──> Fines ──> Payments       (One-to-Many: 1 denda bisa banyak pembayaran)
```

**Normalisasi — Contoh tabel `borrowings`:**

Bentuk ** tidak normal (Unnormalized)** — kalau kita simpen semua dalam 1 tabel:
| user_name | book_title | borrow_date | due_date | return_date | status |
|---|---|---|---|---|---|
| Andi | Belajar SQL | 01-06-2026 | 08-06-2026 | NULL | Dipinjam |
| Andi | Matematika | 01-06-2026 | 08-06-2026 | 05-06-2026 | Kembali |
| Siti | Belajar SQL | 02-06-2026 | 09-06-2026 | NULL | Dipinjam |

**Masalah**: Nama user "Andi" diulang-ulang (redudansi). Judul buku "Belajar SQL"
juga diulang. Kalau ada perubahan nama, harus diubah di banyak baris.

**1NF (First Normal Form):** Setiap kolom bernilai atomik (gak ada array/list).
Tabel di atas udah 1NF karena tiap kolom cuma 1 nilai.

**2NF (Second Normal Form):** 1NF + gak ada partial dependency (ketergantungan
sebagian dari composite key). Tabel `borrowings` pake `id` sebagai PK (surrogate),
jadi otomatis 2NF.

**3NF (Third Normal Form):** 2NF + gak ada transitive dependency (kolom non-key
gak boleh tergantung sama kolom non-key lain). Contoh: kita pisah `user_name` ke
tabel `users` + `member_profiles`, `book_title` ke tabel `books`. Makanya di
project ini `borrowings` cuma nyimpen `user_id` dan `book_id` (foreign key):

```sql
CREATE TABLE borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date DATE NOT NULL DEFAULT (CURDATE()),
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('Dipinjam','Kembali','Terlambat') DEFAULT 'Dipinjam',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);
```

**Data Dictionary (tabel inti):**

| Tabel | Kolom | Tipe | Constraint | Deskripsi |
|---|---|---|---|---|
| `users` | id | INT | PK, AUTO_INCREMENT | ID user |
| | username | VARCHAR(50) | UNIQUE, NOT NULL | Username login |
| | email | VARCHAR(100) | UNIQUE, NOT NULL | Email |
| | password | VARCHAR(255) | NOT NULL | Hash MD5 |
| | is_active | TINYINT(1) | DEFAULT 1 | Status aktif |
| | created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP | Waktu daftar |
| `roles` | id | INT | PK, AUTO_INCREMENT | ID role |
| | role_name | ENUM('Admin','Staff','Pembaca') | NOT NULL | Nama role |
| `user_roles` | user_id | INT | FK → users(id) | User |
| | role_id | INT | FK → roles(id) | Role (pivot) |
| `books` | id | INT | PK, AUTO_INCREMENT | ID buku |
| | publisher_id | INT | FK → publishers(id) | Penerbit |
| | title | VARCHAR(255) | NOT NULL | Judul buku |
| | isbn | CHAR(13) | UNIQUE | ISBN 13 digit |
| | publication_year | YEAR | | Tahun terbit |
| `borrowings` | id | INT | PK, AUTO_INCREMENT | ID peminjaman |
| | user_id | INT | FK → users(id) | Peminjam |
| | book_id | INT | FK → books(id) | Buku dipinjam |
| | borrow_date | DATE | DEFAULT CURDATE() | Tgl pinjam |
| | due_date | DATE | NOT NULL | Jatuh tempo |
| | return_date | DATE | NULL | Tgl kembali |
| | status | ENUM | DEFAULT 'Dipinjam' | Status |
| `fines` | id | INT | PK, AUTO_INCREMENT | ID denda |
| | borrowing_id | INT | FK → borrowings(id) | Peminjaman terkait |
| | amount | DECIMAL(10,2) | NOT NULL | Jumlah denda |
| | fine_status | ENUM('Belum bayar','Lunas') | DEFAULT 'Belum bayar' | Status bayar |
| `payments` | id | INT | PK, AUTO_INCREMENT | ID pembayaran |
| | fine_id | INT | FK → fines(id) ON DELETE SET NULL | Denda dibayar |
| | payment_method | ENUM('E-Wallet','Bank Transfer') | NOT NULL | Metode bayar |

### (c) Status:  SUDAH

Project ini punya 20 tabel yang udah ternormalisasi sampai 3NF, lengkap dengan
PK, FK, dan constraint. Data Dictionary bisa diekstrak dari `sql/ddl.sql`.
Yang *kurang*: ERD dalam bentuk diagram (cuma bisa ditebak dari struktur tabel).
Saran: bikin ERD pake draw.io atau MySQL Workbench biar keliatan nyambungnya.

---

## 2. Implementasi Database (DDL, DML, Constraint)

### (a) Teori Singkat

**DDL (Data Definition Language):** Perintah untuk bikin dan ngubah struktur
database — `CREATE TABLE`, `ALTER TABLE`, `DROP TABLE`.
**DML (Data Manipulation Language):** Perintah untuk ngolah data — `INSERT`,
`UPDATE`, `DELETE`, `SELECT`.
**Constraint:** Aturan buat jaga integritas data — `PRIMARY KEY`, `FOREIGN KEY`,
`UNIQUE`, `NOT NULL`, `CHECK`, `DEFAULT`, `ON DELETE CASCADE`.

### (b) Contoh dari Project

**DDL — CREATE TABLE dengan constraint lengkap:**
```sql
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    publisher_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    isbn CHAR(13) UNIQUE NOT NULL,
    publication_year YEAR,
    synopsis TEXT,
    total_pages INT,
    FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

Penjelasan constraint:
- `PRIMARY KEY (id)` — tiap buku punya ID unik
- `UNIQUE (isbn)` — gak boleh ada 2 buku dengan ISBN sama
- `NOT NULL` — judul, ISBN, publisher wajib diisi
- `FOREIGN KEY ... ON DELETE CASCADE` — kalau publisher dihapus, semua bukunya ikut kehapus

**DML — Contoh dari alur peminjaman:**

`INSERT` (peminjaman baru via SP):
```sql
CALL sp_pinjam_buku(1, 3);
-- Ini di belakang layar ngelakuin:
-- INSERT INTO borrowings (user_id, book_id) VALUES (1, 3);
-- due_date otomatis diisi trigger = CURDATE() + 7
```

`UPDATE` (pengembalian buku):
```sql
UPDATE borrowings
SET return_date = CURDATE(),
    status = 'Kembali'
WHERE id = 5 AND user_id = 1;
-- trigger trg_auto_fine bakal otomatis cek:
--   kalo return_date > due_date → INSERT ke fines Rp1.000/hari
```

`DELETE` (hapus buku):
```sql
DELETE FROM books WHERE id = 10;
-- ON DELETE CASCADE → book_authors, book_categories, dll ikut kehapus
```

### (c) Status:  SUDAH

Sudah ada `sql/ddl.sql` berisi 20 tabel dengan PK, FK, UNIQUE, NOT NULL, ENUM,
ENGINE=InnoDB, ON DELETE CASCADE/SET NULL. DML sample data juga ada di
`sql/dml.sql`. Alur INSERT/UPDATE/DELETE dari peminjaman bisa dilacak dari
file `staff/borrowings.php`.

---

## 3. Implementasi Trigger

### (a) Teori Singkat

**Trigger** adalah prosedur yang otomatis jalan saat terjadi event tertentu
(`INSERT`, `UPDATE`, `DELETE`) di tabel tertentu. Trigger dipake buat:
- Otomatisasi (isi kolom otomatis)
- Validasi (cegah data salah)
- Logging (catat perubahan)
Kapan trigger jalan: `BEFORE` (sebelum operasi) atau `AFTER` (sesudah operasi).

### (b) Contoh dari Project (3 Trigger)

**Trigger 1: `trg_set_due_date`**
```sql
CREATE TRIGGER trg_set_due_date
BEFORE INSERT ON borrowings
FOR EACH ROW
SET NEW.due_date = CURDATE() + 7;
```
- **Event**: BEFORE INSERT di `borrowings`
- **Fungsi**: Otomatis ngisi `due_date` = 7 hari dari sekarang
- **Kenapa pake trigger?** Biar staff gak perlu manual ngisi tanggal jatuh tempo.
  Kalau lupa, datanya bakal NULL.
- **Alternatif tanpa trigger**: Isi manual pas INSERT dari PHP:
  ```php
  $due_date = date('Y-m-d', strtotime('+7 days'));
  $stmt = $conn->prepare("INSERT INTO borrowings (user_id, book_id, due_date) VALUES (?, ?, ?)");
  ```
  Tapi riskan — kalau lupa, due_date jadi NULL.

**Trigger 2: `trg_auto_fine`**
```sql
CREATE TRIGGER trg_auto_fine
AFTER UPDATE ON borrowings
FOR EACH ROW
BEGIN
    IF NEW.return_date IS NOT NULL AND NEW.return_date > NEW.due_date THEN
        INSERT INTO fines (borrowing_id, amount)
        VALUES (NEW.id, DATEDIFF(NEW.return_date, NEW.due_date) * 1000);
    END IF;
END;
```
- **Event**: AFTER UPDATE di `borrowings`
- **Fungsi**: Otomatis buat denda kalo telat ngembaliin (Rp1.000/hari)
- **Kenapa pake trigger?** Biar objektif — gak bisa dimanipulasi staff.
  Staff gak bisa "lupa" ngasih denda.
- **Alternatif tanpa trigger**: Hitung denda dari PHP pas pengembalian:
  ```php
  $selisih = strtotime($return_date) - strtotime($due_date);
  if ($selisih > 0) {
      $denda = floor($selisih / 86400) * 1000;
      $conn->query("INSERT INTO fines (borrowing_id, amount) VALUES ($id, $denda)");
  }
  ```
  Tapi ini bisa diakali — ada kemungkinan bug atau sengaja di-skip.

**Trigger 3: `trg_audit_borrowings_insert`**
```sql
CREATE TRIGGER trg_audit_borrowings_insert
AFTER INSERT ON borrowings
FOR EACH ROW
BEGIN
    INSERT INTO audit_logs (user_id, action, table_name, description)
    VALUES (NEW.user_id, 'INSERT', 'borrowings',
            CONCAT('Peminjaman buku id=', NEW.book_id));
END;
```
- **Event**: AFTER INSERT di `borrowings`
- **Fungsi**: Catat otomatis ke log audit tiap ada peminjaman baru
- **Kenapa pake trigger?** Biar semua aktivitas ke-track tanpa perlu ngandelin
  developer ingat manggil `logAudit()` dari PHP.
- **Alternatif tanpa trigger**: Panggil fungsi `logAudit()` manual dari PHP.

### (c) Status:  SUDAH

3 trigger udah lengkap di `sql/trigger.sql`. Masing-masing punya event jelas
(BEFORE INSERT, AFTER UPDATE, AFTER INSERT) dan alasan kenapa pake trigger
(bukan PHP). Alternatif tanpa trigger juga udah dijelasin di atas.

---

## 4. Implementasi Function & Aggregate

### (a) Teori Singkat

**User-Defined Function (UDF)** adalah fungsi yang kita bikin sendiri di MySQL,
bisa dipanggil langsung di query SELECT. Bedanya sama **Stored Procedure (SP)**:

| Aspek | Function | Stored Procedure |
|---|---|---|
| Return value | **Harus** return 1 nilai | Bisa return 0 / banyak result set |
| Dipanggil di SELECT? | Bisa (`SELECT fn(...)`) | Gak bisa |
| Parameter | IN only | IN, OUT, INOUT |
| Output | Single value (scalar) | Result set / output parameter |
| Transaksi | Gak boleh | Boleh |

**Aggregate Function** adalah fungsi bawaan MySQL yang ngolah sekelompok baris
jadi 1 nilai: `COUNT`, `SUM`, `AVG`, `MAX`, `MIN`. Biasanya dipake bareng
`GROUP BY`.

### (b) Contoh dari Project

**2 UDF:**

**`fn_hitung_denda(p_borrowing_id INT)` — RETURNS DECIMAL(10,2)**
```sql
CREATE FUNCTION fn_hitung_denda(p_borrowing_id INT)
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE denda DECIMAL(10,2);
    SELECT COALESCE(DATEDIFF(return_date, due_date), 0) * 1000 INTO denda
    FROM borrowings WHERE id = p_borrowing_id;
    RETURN denda;
END;
```
- Ngitung denda berdasarkan selisih return_date - due_date × 1000
- Bisa dipanggil: `SELECT fn_hitung_denda(5)`

**`fn_avg_rating(p_book_id INT)` — RETURNS DECIMAL(3,2)**
```sql
CREATE FUNCTION fn_avg_rating(p_book_id INT)
RETURNS DECIMAL(3,2)
DETERMINISTIC
BEGIN
    DECLARE avg_rating DECIMAL(3,2);
    SELECT COALESCE(AVG(rating), 0) INTO avg_rating
    FROM reviews_ratings WHERE book_id = p_book_id;
    RETURN avg_rating;
END;
```
- Ngitung rata-rata rating 1 buku
- Dipanggil: `SELECT fn_avg_rating(2)`

**Aggregate Function di Project (lebih dari 5):**

| # | Aggregate | File | Query | Fungsi |
|---|---|---|---|---|
| 1 | `COUNT(br.id)` | `admin/laporan.php:38` | Tab "Buku Terlaris" | Hitung jumlah peminjaman per buku |
| 2 | `SUM(f.amount)` | `admin/laporan.php:129` | Tab "Denda Per Bulan" | Total nominal denda per bulan |
| 3 | `AVG(rr.rating)` | `pembaca/catalog.php:48` | Katalog buku | Rating rata-rata tiap buku |
| 4 | `COUNT(DISTINCT bc.book_id)` | `admin/laporan.php:192` | Tab "Statistik Per Kategori" | Jumlah buku unik per kategori |
| 5 | `SUM(CASE WHEN...)` | `admin/laporan.php:130-131` | Tab "Denda Per Bulan" | Kondisi SUM (denda lunas vs belum) |
| 6 | `COUNT(*)` | `pembaca/detail_buku.php:152` | Detail buku | Cek apakah user pernah pinjam buku |
| 7 | `COUNT(*)` | `pembaca/detail_buku.php:163` | Detail buku | Total review untuk 1 buku |
| 8 | `AVG(rating)` (via `fn_avg_rating`) | `pembaca/detail_buku.php:157` | Detail buku | Rata-rata rating — panggil UDF |
| 9 | `GROUP BY` multiple | `admin/laporan.php` | Semua tab | Pengelompokan data per kategori/bulan |

### (c) Status:  SUDAH

2 UDF terdefinisi di `sql/function.sql`. Aggregate function ≥ 5 (tepatnya
ada 8+ penggunaan dari COUNT, SUM, AVG, SUM CASE, COUNT DISTINCT + GROUP BY
di berbagai file).

**Update — UDF udah terintegrasi:**
- `fn_hitung_denda` → dipanggil di `trg_auto_fine` (`sql/trigger.sql`) buat
  ngitung denda pas pengembalian, dan di `staff/borrowings.php` buat nampilin
  proyeksi denda buku yang terlambat.
- `fn_avg_rating` → dipanggil di `pembaca/detail_buku.php` (prepared statement)
  buat nampilin rata-rata rating di halaman detail buku.

---

## 5. Implementasi TCL & Table Locking

### (a) Teori Singkat

**TCL (Transaction Control Language):** Perintah buat ngatur transaksi —
`START TRANSACTION`, `COMMIT`, `ROLLBACK`, `SAVEPOINT`. Transaksi memastikan
semua query dalam 1 group **berhasil semua** atau **gagal semua** (atomicity).

**Table Locking vs Row Locking:**
- **Row Lock (`SELECT ... FOR UPDATE`)**: Kunci cuma 1 baris tertentu.
  Cocok kalo banyak user akses beda baris secara bersamaan.
- **Table Lock (`LOCK TABLES ... WRITE/READ`)**: Kunci seluruh tabel.
  Cocok kalo perlu akses eksklusif (misal: backup, bulk update).
  Tapi efeknya: user lain gak bisa akses tabel itu sama sekali — berat.

### (b) Contoh dari Project

**Transaksi Signup (3 step dalam 1 transaksi):**

File `signup.php` — daftar user baru:
```php
$conn->begin_transaction();
try {
    // Step 1: INSERT ke users
    $conn->query("INSERT INTO users (username, email, password) VALUES ('$username', '$email', MD5('$pass'))");
    $user_id = $conn->insert_id;

    // Step 2: INSERT ke user_roles (role Pembaca = 3)
    $conn->query("INSERT INTO user_roles (user_id, role_id) VALUES ($user_id, 3)");

    // Step 3: INSERT ke member_profiles
    $conn->query("INSERT INTO member_profiles (user_id, member_number, full_name) VALUES ($user_id, 'MBR-XXXXX', '$full_name')");

    $conn->commit(); // Semua berhasil → simpan
} catch (Exception $e) {
    $conn->rollback(); // Ada gagal → batalkan semua
}
```

Konsep: Kalau step 2 gagal (misal user_id error), step 1 gak bakal kepake
karena di-rollback. Ini mencegah "user tanpa role" atau "user tanpa profile".

**Row Locking di `sp_pinjam_buku`:**
```sql
START TRANSACTION;
-- Kunci baris buku yang mau dipinjam (cegah double booking)
SELECT hasil FROM books WHERE id = p_book_id FOR UPDATE;

-- Cek apakah user lagi pinjam buku yang sama
SELECT COUNT(*) INTO existing FROM borrowings
WHERE user_id = p_user_id AND book_id = p_book_id
  AND status IN ('Dipinjam', 'Terlambat');

IF existing > 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Masih dipinjam';
    ROLLBACK;
END IF;
-- ... INSERT borrowings ...
COMMIT;
```

`FOR UPDATE` ngunci baris di tabel `books` selama transaksi berjalan. User lain
yang mau pinjam buku yang sama bakal nunggu sampai transaksi selesai (COMMIT
atau ROLLBACK). Ini beda sama **LOCK TABLES** yang ngunci seluruh tabel.

**LOCK TABLES di Project: TIDAK ADA.**

Setelah cek seluruh codebase, **gak ada satupun penggunaan `LOCK TABLES`**.
Yang ada cuma row-level locking (`FOR UPDATE`) di `sp_pinjam_buku`.

**Kapan LOCK TABLES beneran dibutuhin?**
Misal: Staff mau ngelakuin **reset semua status peminjaman** di akhir bulan.
Operasi ini butuh akses eksklusif ke tabel `borrowings` dan `fines` biar gak
ada peminjaman baru selama proses:
```sql
LOCK TABLES borrowings WRITE, fines WRITE;
-- Update semua status 'Dipinjam' jadi 'Terlambat'
UPDATE borrowings SET status = 'Terlambat' WHERE status = 'Dipinjam' AND due_date < CURDATE();
-- Insert denda untuk semua yang telat
INSERT INTO fines (borrowing_id, amount)
SELECT id, DATEDIFF(CURDATE(), due_date) * 1000 FROM borrowings
WHERE status = 'Terlambat' AND id NOT IN (SELECT borrowing_id FROM fines);
UNLOCK TABLES;
```

### (c) Status:  SEBAGIAN

Ada contoh transaksi (signup.php). Ada row locking (FOR UPDATE di SP).
Tapi **belum ada contoh LOCK TABLES** yang jelas.

**Yang kurang:**
- Demonstrasi LOCK TABLES (yang beda dari FOR UPDATE)
  → Solusi: tambahin contoh di atas ke dokumentasi atau kalo pengen ekstrim,
  bikin halaman admin "Bulk Update Denda" yang pake LOCK TABLES beneran.

---

## 6. Implementasi Stored Procedure

### (a) Teori Singkat

**Stored Procedure (SP)** adalah kumpulan query SQL yang disimpan di server
dan bisa dipanggil kapan aja. Keuntungan:
- **Reusable**: Panggil berkali-kali tanpa nulis ulang query
- **Security**: User bisa CALL SP tanpa perlu akses langsung ke tabel
- **Performance**: Query sudah compiled, lebih cepat dari query raw
- **Atomicity**: Bungkus transaksi rapi di 1 tempat

**Cursor** adalah mekanisme buat iterasi baris query satu per satu (kayak
foreach di PHP). Biasanya dipake kalo perlu ngelakuin operasi per-baris
yang gak bisa pake UPDATE biasa.

### (b) Contoh dari Project (4 SP)

**SP 1: `sp_pinjam_buku(p_user_id INT, p_book_id INT)`**

```sql
CREATE PROCEDURE sp_pinjam_buku(IN p_user_id INT, IN p_book_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Gagal: Kesalahan sistem' AS hasil;
    END;

    START TRANSACTION;
    -- Cek apakah buku ada
    IF NOT EXISTS (SELECT 1 FROM books WHERE id = p_book_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Buku tidak ditemukan';
    END IF;

    -- Row lock → cegah double booking
    SELECT hasil FROM books WHERE id = p_book_id FOR UPDATE;

    -- Cek duplikat peminjaman
    IF EXISTS (SELECT 1 FROM borrowings WHERE user_id = p_user_id AND book_id = p_book_id
               AND status IN ('Dipinjam', 'Terlambat')) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Masih dipinjam';
    END IF;

    INSERT INTO borrowings (user_id, book_id) VALUES (p_user_id, p_book_id);
    COMMIT;
    SELECT 'berhasil' AS hasil;
END;
```

**Fitur**: Row lock (`FOR UPDATE`), handler transaksi, cek duplikat, SIGNAL untuk error.

**SP 2: `sp_bayar_denda(p_fine_id INT, p_payment_method VARCHAR(20))`**

```sql
CREATE PROCEDURE sp_bayar_denda(IN p_fine_id INT, IN p_payment_method VARCHAR(20))
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 'Gagal' AS hasil;
    END;

    START TRANSACTION;
    -- Validasi denda ada
    IF NOT EXISTS (SELECT 1 FROM fines WHERE id = p_fine_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid fine_id';
    END IF;
    -- Cek apakah udah lunas
    IF (SELECT fine_status FROM fines WHERE id = p_fine_id) = 'Lunas' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sudah Lunas';
    END IF;

    UPDATE fines SET fine_status = 'Lunas' WHERE id = p_fine_id;
    INSERT INTO payments (fine_id, payment_amount, payment_method)
    SELECT p_fine_id, amount, p_payment_method FROM fines WHERE id = p_fine_id;
    COMMIT;
    SELECT 'berhasil' AS hasil;
END;
```

**Fitur**: Validasi pre-update, update + insert dalam 1 transaksi.

**SP 3: `sp_laporan_bulanan(p_bulan INT, p_tahun INT)`**

```sql
CREATE PROCEDURE sp_laporan_bulanan(IN p_bulan INT, IN p_tahun INT)
BEGIN
    -- Result set 1: Total peminjaman bulan ini
    SELECT CONCAT(COUNT(*), ' peminjaman') AS laporan
    FROM borrowings WHERE MONTH(borrow_date)=p_bulan AND YEAR(borrow_date)=p_tahun;

    -- Result set 2: Total denda masuk bulan ini
    SELECT CONCAT('Rp', FORMAT(COALESCE(SUM(amount),0),0)) AS laporan
    FROM fines WHERE MONTH(created_at)=p_bulan AND YEAR(created_at)=p_tahun;

    -- Result set 3: Member baru bulan ini
    SELECT CONCAT(COUNT(*), ' member baru') AS laporan
    FROM users WHERE MONTH(created_at)=p_bulan AND YEAR(created_at)=p_tahun;

    -- Result set 4: Buku paling sering dipinjam
    SELECT b.title AS judul_buku, COUNT(br.id) AS total_dipinjam
    FROM borrowings br JOIN books b ON br.book_id=b.id
    WHERE MONTH(br.borrow_date)=p_bulan AND YEAR(br.borrow_date)=p_tahun
    GROUP BY br.book_id ORDER BY total_dipinjam DESC LIMIT 5;
END;
```

**Fitur**: Multiple result set (4 hasil dalam 1 panggilan).
Pattern panggil dari PHP (wajib pake loop flush):

```php
$stmt = $conn->prepare("CALL sp_laporan_bulanan(?, ?)");
$stmt->bind_param('ii', $bulan, $tahun);
$stmt->execute();
$hasil = [];
do {
    if ($r = $stmt->get_result()) {
        $hasil[] = $r->fetch_all(MYSQLI_ASSOC);
    }
} while ($stmt->more_results() && $stmt->next_result());
$stmt->close();
// $hasil[0] = total pinjam, [1] = total denda,
// $hasil[2] = member baru, [3] = buku terpopuler
```

**SP 4: `sp_proses_keterlambatan()` — Contoh Cursor**

```sql
CREATE PROCEDURE sp_proses_keterlambatan()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_borrow_id INT;
    DECLARE cur CURSOR FOR
        SELECT id FROM borrowings
        WHERE status = 'Dipinjam' AND due_date < CURDATE();
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO v_borrow_id;
        IF done THEN LEAVE read_loop; END IF;

        UPDATE borrowings SET status = 'Terlambat' WHERE id = v_borrow_id;
    END LOOP;
    CLOSE cur;
    SELECT 'berhasil' AS hasil;
END;
```

**Penjelasan Cursor:**
1. `DECLARE cur CURSOR FOR ...` — definisi query yang bakal di-loop
2. `DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE` — trigger pas data abis
3. `OPEN cur` — jalankan query
4. `LOOP ... FETCH cur INTO ...` — ambil 1 baris tiap iterasi
5. `IF done THEN LEAVE read_loop` — berhenti kalo data udah abis
6. `CLOSE cur` — tutup cursor

**KENAPA PAKE CURSOR?** Karena butuh `UPDATE` per-baris? Sebenernya di kasus
ini bisa pake `UPDATE borrowings SET status = 'Terlambat' WHERE due_date < CURDATE()`
langsung tanpa cursor. Tapi cursor dipake sebagai **demonstrasi konsep** (memenuhi
rubrik penilaian) dan kalo di masa depan ada kebutuhan logika per-baris yang lebih
kompleks (misal kirim notifikasi tiap user yang telat).

**Pattern Handler (EXIT HANDLER FOR SQLEXCEPTION):**
Semua SP pake pattern yang sama:
```sql
DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
    ROLLBACK;
    SELECT 'pesan error' AS hasil;
END;
```
Ini beda dari biasanya — error MySQL di-tangkap dan di-return sebagai result
set (kolom `hasil`), BUKAN dilempar sebagai exception. Makanya cara ngecek
sukses/gagal dari PHP pake `str_contains($pesan, 'berhasil')`, BUKAN
`$conn->error`.

### (c) Status:  SUDAH

4 SP lengkap (definisi di `sql/procedures/` per file). Masing-masing punya
parameter, transaksi, handler. Konsep cursor udah dijelasin lewat
`sp_proses_keterlambatan`. Pattern pemanggilan PHP (flush result set) udah
standar.

---

## 7. Backup & Restore Database

### (a) Teori Singkat

**Logical Backup:** Backup dalam format SQL (`CREATE TABLE` + `INSERT`).
Bisa dibaca pake teks editor. Contoh: `mysqldump`.
**Physical Backup:** Backup file fisik database (file `.ibd`, `.frm`).
Lebih cepet tapi gak portable. Contoh: copy folder `data/` MySQL.
**Mana yang dipake?** Project ini pake **logical backup** via `mysqldump`.

### (b) Contoh dari Project (backup.ps1 / restore.ps1)

**Backup (backup.bat → backup.ps1):**

Alur:
1. Double-click `backup.bat` → panggil `backup.ps1`
2. Script auto-detect lokasi `mysqldump.exe` (PATH, XAMPP, Laragon, MariaDB)
3. Generate nama file: `backup_perpustakaan_digital_20260629_143000.sql`
4. Jalankan perintah:
   ```
   mysqldump -u root --routines --triggers --databases perpustakaan_digital > backup_file/backup_....sql
   ```
   - `--routines` → backup SP + function
   - `--triggers` → backup trigger
   - `--databases` → sertakan `CREATE DATABASE` + `USE` di output
5. Output masuk folder `backup_file/` (auto-create kalo belum ada)

**Restore (restore.bat → restore.ps1):**

Alur:
1. Double-click `restore.bat` → panggil `restore.ps1`
2. Script nampilin daftar file `.sql` di `backup_file/` (pake nomor)
3. User pilih nomor → konfirmasi (data lama bakal ilang!)
4. **DROP DATABASE IF EXISTS** perpustakaan_digital — hapus DB lama
5. **Restore via stdin pipe**: `Get-Content file.sql | & mysql.exe -u root`
   (bedanya sama `< redirect` — lebih stabil di PowerShell)
6. **Verifikasi otomatis**: jalankan `verify.sql` yang ngecek:
   - Jumlah tabel = 20
   - Jumlah data per tabel
   - Trigger masih ada (2)
   - SP masih ada (4)
   - Function masih ada

**Catatan penting:**
- Password kosong ditangani pake **array splatting** — flag `-p` cuma dipake
  kalo password terisi. Ini biar MySQL gak error parsing argument kosong.
- Ini **logical backup** karena outputnya file SQL yang bisa dibaca manusia.
  Kalo physical backup, kita bakal copy folder `C:\xampp\mysql\data\perpustakaan_digital\`.

### (c) Status:  SUDAH

Script backup/restore udah lengkap di `backup_script/`. Konsep logical vs
physical backup udah jelas bedanya. Yang bisa ditambah:
- **Auto-schedule** (Windows Task Scheduler) biar backup jalan otomatis tiap
  minggu (ini opsional, di luar rubrik).

---

## 8. Reporting

### (a) Teori Singkat

Reporting adalah fitur buat nyajinin data dalam bentuk ringkasan/agregat biar
mudah dipahami. Biasanya pake aggregate function (COUNT, SUM, AVG) +
GROUP BY. Rubrik penilaian minta **minimal 5 laporan + 2 dashboard**.

### (b) Contoh dari Project

**6 Tab Laporan di `admin/laporan.php`:**

| # | Tab | Query | Agregate | Metode |
|---|---|---|---|---|
| 1 | Buku Terlaris | COUNT borrowings per buku | COUNT + GROUP BY | SQL langsung |
| 2 | Peminjaman Per Periode | Filter by bulan/tahun | — | SQL + prepared |
| 3 | Denda Per Bulan | SUM amount per bulan | SUM + SUM CASE + GROUP BY | SQL langsung |
| 4 | Member Baru Per Bulan | COUNT + SUM CASE membership | COUNT + SUM CASE + GROUP BY | SQL langsung |
| 5 | Statistik Per Kategori | COUNT DISTINCT, AVG rating | COUNT DISTINCT + AVG + GROUP BY | SQL langsung |
| 6 | Ringkasan Bulanan | CALL sp_laporan_bulanan | 4 result set | SP |

**3 Dashboard (masing-masing role):**

| Dashboard | Isi | Aggregate? |
|---|---|---|
| Admin (admin/dashboard.php) | "Selamat datang di panel Admin" — **cuma teks** |  |
| Staff (staff/dashboard.php) | "Selamat datang di panel Staff" — **cuma teks** |  |
| Pembaca (pembaca/dashboard.php) | Profil member (nama, nomor anggota, alamat) |  |

**Masalah**: Dashboard Admin & Staff gak punya data agregat sama sekali.
Hanya halaman statis. Dashboard Pembaca cuma nampilin profil doang.
Sementara laporan.php udah cukup (6 tab > 5 laporan).

### (c) Status:  SEBAGIAN

Laporan: **PASS** — 6 tab > 5 laporan minimal. 
Dashboard: **FAIL** — cuma ada 3 dashboard, tapi isinya cuma teks statis.
Gak ada ringkasan data (misal: jumlah buku total, jumlah member aktif,
jumlah peminjaman hari ini).

**Yang kurang:**
Dashboard Admin minimal harus nampilin:
- Total buku di sistem (SELECT COUNT(*) FROM books)
- Total member aktif (SELECT COUNT(*) FROM users JOIN user_roles WHERE role_id=3)
- Total peminjaman aktif (SELECT COUNT(*) FROM borrowings WHERE status='Dipinjam')
- Total denda belum bayar (SELECT SUM(amount) FROM fines WHERE fine_status='Belum bayar')

Dashboard Staff minimal harus nampilin:
- Jumlah peminjaman hari ini
- Jumlah buku yang belum dikembalikan
- Member yang paling sering pinjam

**Saran:** Upgrade dashboard Admin dan Staff pake beberapa query COUNT sederhana
biar gak kosong. Ini plus nilai karena nunjukkin pemahaman reporting dashboard.

---

## Ringkasan Status Kelengkapan Rubrik

| No | Poin Rubrik | Status | Catatan |
|---|---|---|---|
| 1 | Perancangan Basis Data |  SUDAH | 20 tabel, 3NF, Data Dictionary ada |
| 2 | Implementasi DDL/DML |  SUDAH | CREATE TABLE + FK + CASCADE, contoh INSERT/UPDATE/DELETE |
| 3 | Implementasi Trigger |  SUDAH | 3 trigger + event + alternatif tanpa trigger |
| 4 | Function & Aggregate |  SUDAH | 2 UDF, 8+ aggregate function |
| 5 | TCL & Table Locking |  SEBAGIAN | Ada transaksi + FOR UPDATE, belum ada LOCK TABLES |
| 6 | Stored Procedure |  SUDAH | 4 SP + Cursor + Handler pattern |
| 7 | Backup & Restore |  SUDAH | Logical backup via mysqldump, restore + verifikasi |
| 8 | Reporting |  SEBAGIAN | Laporan 6 tab , Dashboard Admin/Staff kosong  |

## Best Practice yang Dilanggar

1. **MD5 untuk password** (baris login.php) — MD5 udah gak aman sejak 2010an.
   Untuk tugas kuliah sih wajar, tapi di production wajib pake `password_hash()`.

2. **Dashboard kosong** — Admin & Staff dashboard isinya cuma "Selamat datang".
   Sayang banget, padahal tinggal tambahin beberapa query COUNT doang.

3. **No input validation di sisi server** — Beberapa file pake `$_POST` langsung
   tanpa filter (rentan XSS). Tapi ini wajar untuk project tugas kuliah.

---

> Dokumen ini dibuat sebagai bahan belajar, bukan dokumentasi teknis.
> Untuk melihat kode asli, buka file di folder masing-masing.

---

## 9. Pipeline — Alur Data di Sistem Perpustakaan Digital

> **Pipeline** = alur lengkap data dari awal sampai akhir.
> Di pipeline kita liat: data masuk dari mana, lewat proses apa aja,
> tabel mana yang terlibat, dan berakhir di mana.
> Bahasa sederhananya: **"siapa ngapain, datanya ngikut kemana"**.

---

### Pipeline 1: Registrasi User (Daftar → Login)

**Pemain:** Pembaca (orang yang daftar lewat form)

**Alur:**
```
Form signup.php
  ↓ (input username, email, password, nama)
Mulai transaksi
  ↓
INSERT ke tabel users           → data akun dasar
  ↓
INSERT ke tabel user_roles      → kasih role Pembaca (role_id=3)
  ↓
INSERT ke tabel member_profiles → data profil member
  ↓
UPDATE member_number            → bikin nomor MBR-XXXXX
  ↓
COMMIT (kalau semua berhasil)
  ↓
Redirect ke login.php
  ↓
User login → buat session
  ↓
Redirect ke pembaca/dashboard.php (Profil Saya)
```

**Tabel yang terlibat:** `users`, `user_roles`, `member_profiles`, `roles`

**Kenapa pake transaksi?**
Karena 3 INSERT harus jadi semua. Kalau misal gagal di step 2 (user_roles),
data users yang udah ke-INSERT harus dihapus (ROLLBACK). Gak mau kan ada
user di tabel users tapi gak punya role?

---

### Pipeline 2: Peminjaman Buku (Staff → Buku Dipinjam)

**Pemain:** Staff (yang proses), Pembaca (yang pinjam)

**Alur:**
```
Staff buka staff/borrowings.php
  ↓
  ↓ (proses otomatis) CALL sp_proses_keterlambatan()
  ↓ → UPDATE status → 'Terlambat' untuk yang lewat due_date
  ↓
Staff pilih user + buku via form
  ↓
CALL sp_pinjam_buku(p_user_id, p_book_id)
  ↓
  [di dalam SP:]
  Mulai transaksi
  SELECT ... FOR UPDATE → kunci baris buku (biar gak dobel)
  Cek: apa user lagi pinjam buku ini? → kalo iya, tolak
  Cek: apa buku ada? → kalo gak ada, tolak
  INSERT ke borrowings           → catat peminjaman
  COMMIT
  ↓
TRIGGER: trg_set_due_date jalan
  → otomatis isi due_date = CURDATE() + 7
  ↓
TRIGGER: trg_audit_borrowings_insert jalan
  → otomatis catat ke audit_logs
  ↓
SP return "berhasil"
  ↓
Staff liat pesan "Peminjaman berhasil" di halaman
```

**Tabel yang terlibat:** `borrowings`, `books`, `users`, `audit_logs`

**Konsep penting:**
- **Row lock (FOR UPDATE)** = ngunci satu baris buku yang mau dipinjam,
  biar 2 staff gak bisa pinjemin buku yang sama ke 2 orang berbeda
  di saat yang bersamaan.
- **Trigger** = otomatis ngisi due_date + catat log.
  Staff gak perlu mikir ngisi tanggal atau log.

---

### Pipeline 3: Pengembalian Buku + Denda Otomatis

**Pemain:** Staff (proses), Pembaca (yang ngembaliin)

**Alur:**
```
Staff buka halaman peminjaman (staff/borrowings.php)
  ↓
Cari peminjaman yang statusnya 'Dipinjam'
  ↓
Klik tombol "Kembali"
  ↓
UPDATE borrowings SET return_date = CURDATE(), status = 'Kembali'
  ↓
TRIGGER: trg_auto_fine jalan
  → Cek: return_date > due_date?
    - Kalo iya → INSERT denda Rp1.000 × jumlah hari telat
    - Kalo enggak → gak ngapa-ngapain
  ↓
Kembali ke halaman peminjaman → status berubah jadi 'Kembali'
```

**Tabel yang terlibat:** `borrowings`, `fines`

**Konsep penting:**
- Trigger `trg_auto_fine` adalah contoh **business logic di database**.
  Daripada ngitung denda dari PHP (yang bisa diakali), kita serahin ke
  database yang gak bisa diboongin.
- Perhitungan denda panggil **UDF `fn_hitung_denda(NEW.id)`** — jadi kalo
  logic denda berubah (misal: Rp2.000/hari), tinggal edit UDF, trigger
  otomatis ngikut.
- Denda cuma muncul kalo **telat**. Kalo balikin tepat waktu, gak ada denda.
  Gak perlu staff input nominal — otomatis.
- Di halaman staff/borrowings.php, kolom **Denda** nampilin proyeksi denda
  buat buku yang statusnya 'Terlambat' (pake `fn_hitung_denda(id)` langsung
  dari query SELECT).

---

### Pipeline 4: Deteksi Keterlambatan (Auto-Process)

**Pemain:** Staff (gak sadar — jalan otomatis tiap halaman dibuka)

**Alur:**
```
Staff buka halaman staff/borrowings.php (atau halaman staff manapun)
  ↓
Di baris 8: $conn->query("CALL sp_proses_keterlambatan()")
  ↓
  [di dalam SP:]
  BUKA CURSOR → ambil semua borrowing yang:
    - status = 'Dipinjam'
    - due_date < CURDATE() (udah lewat jatuh tempo)
  ↓
  LOOP tiap baris:
    UPDATE status jadi 'Terlambat'
  ↓
  TUTUP CURSOR
  ↓
  Return "berhasil"
  ↓
Halaman lanjut loading → staff gak sadar ada proses di belakang
```

**Tabel yang terlibat:** `borrowings`

**Konsep penting:**
- **Cursor** = loop di database. Ambil data baris per baris terus proses.
  Mirip foreach di PHP tapi jalan di MySQL.
- Pipeline ini jalan **tanpa diminta** — cukup staff buka halaman aja.
  Ini namanya **fire-and-forget**: kita panggil SP, gak peduli hasilnya,
  lanjut loading halaman.

---

### Pipeline 5: Pembayaran Denda (Staff → Lunas)

**Pemain:** Staff, Pembaca (yang bayar)

**Alur:**
```
Pembaca dateng ke staff bilang "saya mau bayar denda"
  ↓
Staff buka staff/fines.php
  ↓
Cari denda yang statusnya 'Belum bayar'
  ↓
Klik "Bayar" → muncul modal pilih metode bayar
  ↓
CALL sp_bayar_denda(p_fine_id, 'E-Wallet')
  ↓
  [di dalam SP:]
  Cek: apa fine_id valid? → kalo gak, tolak
  Cek: apa udah lunas? → kalo udah, tolak
  UPDATE fines SET fine_status = 'Lunas'
  INSERT ke payments (ambil amount dari fines)
  COMMIT
  ↓
SP return "berhasil"
  ↓
Staff liat status berubah jadi 'Lunas'
```

**Tabel yang terlibat:** `fines`, `payments`

**Konsep penting:**
- SP ini beda dari pipeline lain karena ada **2 validasi pre-transaksi**:
  cek ID valid + cek duplikat pembayaran. Biar orang gak bisa bayar denda
  2 kali.
- **INSERT payments** ambil amount otomatis dari tabel fines — staff
  gak perlu ngetik nominal. Mencegah human error.

---

### Pipeline 6: Manajemen Buku (Staff CRUD)

**Pemain:** Staff (tambah/ubah/hapus buku)

**Alur:**
```
STAFF BUKA staff/books.php
  ↓
LIHAT daftar buku → query SELECT dengan JOIN:
  books + publishers + book_authors + book_categories
  ↓
TAMBAH BUKU:
  Form → INSERT ke books
  → INSERT ke book_authors (pilih penulis existing)
  → INSERT ke book_categories (pilih kategori existing)
  ↓
UBAH BUKU:
  Form → UPDATE books SET ...
  ↓
HAPUS BUKU:
  Klik hapus → DELETE buku
  → ON DELETE CASCADE otomatis hapus:
    book_authors, book_categories, book_files, dll
  ↓
UPLOAD FILE:
  Buka staff/book_files.php
  → upload PDF/EPUB
  → INSERT ke book_files (file_url, file_size_mb, file_format)
```

**Tabel yang terlibat:** `books`, `publishers`, `authors`, `book_authors`,
`categories`, `book_categories`, `book_files`

**Konsep penting:**
- **ON DELETE CASCADE** = pas hapus buku, semua data yang nyangkut (author,
  kategori, file) ikut kehapus otomatis. Staff gak perlu hapus satu-satu.
- Relasi **Many-to-Many** (buku  penulis, buku  kategori) pake tabel
  pivot `book_authors` dan `book_categories`.

---

### Pipeline 7: Aktivitas Pembaca (Jelajah + Interaksi)

**Pemain:** Pembaca (user yang udah login)

**Alur:**
```
CATALOG (pembaca/catalog.php)
  → SELECT buku + rata-rata rating + status pinjam
  → Search by judul, Filter by kategori
  → Klik buku → ke detail
  ↓
DETAIL BUKU (pembaca/detail_buku.php)
  → Info lengkap buku
  → Tombol: Baca, Pinjam, Wishlist, Bookmark, Review
  ↓
  ├── WISHLIST: INSERT/ DELETE wishlists (1 klik)
  │
  ├── BOOKMARK: INSERT/ UPDATE/ DELETE bookmarks
  │   → Simpan halaman terakhir baca + notes
  │
  ├── REVIEW: INSERT atau UPDATE reviews_ratings
  │   → 1 user cuma bisa 1 review per buku
  │   → Kalo review udah ada, UPDATE (bukan INSERT baru)
  │
  └── BACA: redirect ke pembaca/baca.php?id=X
      → Tampilin file PDF/EPUB (embed viewer)
      → UPDATE reading_history otomatis
  ↓
RIWAYAT PINJAM (pembaca/borrowings.php)
  → SELECT dari borrowings WHERE user_id = session
  → Liat status: Dipinjam/Kembali/Terlambat
  ↓
DENDA (pembaca/fines.php)
  → SELECT fines JOIN borrowings WHERE user_id = session
  → Bayar denda (INSERT payments manual via PHP)
  ↓
WISHLIST (pembaca/wishlist.php)
  → SELECT wishlists JOIN books
  → Hapus wishlist
```

**Tabel yang terlibat:** `books`, `borrowings`, `fines`, `wishlists`,
`bookmarks`, `reviews_ratings`, `reading_history`, `book_files`

**Konsep penting:**
- Semua query di pipeline ini pake filter `WHERE user_id = session` —
  pembaca cuma bisa liat data dirinya sendiri. Privasi.
- **1 user 1 review** di-implement pake INSERT or UPDATE logic:
  SELECT dulu, kalo ada → UPDATE, kalo gak ada → INSERT.
  Alternatif di MySQL: `INSERT ... ON DUPLICATE KEY UPDATE`.

---

### Pipeline 8: Laporan & Monitoring (Admin)

**Pemain:** Admin (monitoring, gak bisa ngubah data)

**Alur:**
```
Admin buka admin/laporan.php?tab=...
  ↓
6 TAB — masing-masing query beda:
  ↓
├── Buku Terlaris
│   → SELECT COUNT(borrowings) GROUP BY buku → ranking
│
├── Peminjaman Per Periode
│   → SELECT detail peminjaman filter bulan+tahun
│
├── Denda Per Bulan
│   → SELECT SUM(amount) GROUP BY bulan → total denda
│
├── Member Baru Per Bulan
│   → SELECT COUNT(*) GROUP BY bulan → jumlah daftar baru
│
├── Statistik Per Kategori
│   → SELECT COUNT buku, AVG rating, COUNT pinjam GROUP BY kategori
│
└── Ringkasan Bulanan (SP)
    → CALL sp_laporan_bulanan(bulan, tahun)
    → 4 result set: total pinjam, total denda, member baru, buku top 5
  ↓
Tampil tabel + angka di halaman
```

**Tabel yang terlibat:** Semua tabel — tergantung tab yang dipilih

**Konsep penting:**
- **Read-only** — Admin cuma bisa liat, gak bisa ubah data operasional.
  Sesuai prinsip RBAC: admin punya akses lebih luas tapi terbatas.
- **GROUP BY** = kunci utama reporting. Semua laporan pake GROUP BY buat
  ngelompokin data per kategori/bulan/buku.
- **SP multi-result** — `sp_laporan_bulanan` ngembaliin 4 tabel sekaligus
  dalam 1 panggilan. PHP harus loop pake `do...while` + `more_results()`.

---

### Pipeline 9: Audit Log (Catatan Aktivitas)

**Pemain:** Semua user — gak sadar (otomatis via trigger / manual via PHP)

**Alur:**
```
ADA 2 CARA DATA MASUK KE AUDIT LOG:
  ↓
CAR A (OTOMATIS — TRIGGER):
  INSERT ke borrowings
    ↓
  Trigger trg_audit_borrowings_insert jalan
    ↓
  INSERT ke audit_logs
    (user_id = peminjam, action = 'INSERT',
     table = 'borrowings', description = 'Peminjaman buku id=...')
  ↓
CAR B (MANUAL — PHP):
  Kode PHP panggil: logAudit($conn, $user_id, 'ACTION', 'table', 'desc')
    ↓
  INSERT ke audit_logs
    (user_id, action, table_name, description, ip_address, created_at)
  ↓
  Contoh: staff/books.php pas tambah buku:
    logAudit($conn, $_SESSION['user_id'], 'INSERT', 'books', 'Tambah buku: Judul Buku')
  ↓
LIHAT LOG:
  Admin → admin/audit_log.php (semua log)
  Staff → staff/audit_log.php (semua log)
  → SELECT + ORDER BY created_at DESC
```

**Tabel yang terlibat:** `audit_logs`, `borrowings` (trigger source)

**Konsep penting:**
- Audit log penting buat **tracking siapa ngapain**.
  Kalo ada masalah (misal: buku ilang), bisa dicek di log.
- Trigger cuma cover 1 event (INSERT borrowings). Sisanya manual dari PHP
  pake fungsi `logAudit()`. Artinya: kalo lupa panggil `logAudit()` di PHP,
  aktivitas itu gak tercatat. Kelemahan pendekatan hybrid.

---

### Pipeline 10: Backup & Restore Database

**Pemain:** Developer / Admin (yang maintain sistem)

**Alur Backup:**
```
Double-click backup.bat
  ↓
backup.bat → panggil backup.ps1
  ↓
Script cari mysqldump.exe (PATH / XAMPP / Laragon / MariaDB)
  ↓
Generate nama file: backup_perpustakaan_digital_20260629_143000.sql
  ↓
Jalankan: mysqldump --routines --triggers --databases perpustakaan_digital
  ↓
Output: file .sql di folder backup_file/
  ↓
Muncul pesan "[SUKSES]" + ukuran file
```

**Alur Restore:**
```
Double-click restore.bat
  ↓
restore.bat → panggil restore.ps1
  ↓
Script tampilin daftar file .sql (nomor + nama + ukuran)
  ↓
User pilih nomor
  ↓
Konfirmasi "Yakin? Data sekarang bakal ilang"
  ↓
[1/4] DROP DATABASE perpustakaan_digital
  ↓
[2/4] Restore: Get-Content file.sql | & mysql.exe -u root
  ↓
[3/4] Verifikasi: jalankan verify.sql
       → Cek jumlah tabel (20)
       → Cek jumlah data per tabel
       → Cek trigger masih ada (2)
       → Cek SP masih ada (4)
       → Cek function masih ada
  ↓
[4/4] Selesai — database kembali kayak waktu backup
```

**Tabel yang terlibat:** Semua (ini backup seluruh database)

**Konsep penting:**
- **Logical backup** = backup berbentuk teks SQL. Bisa dibaca, diedit,
  dan di-restore di versi MySQL manapun.
- **mysqldump** adalah tool bawaan MySQL — gak perlu install tambahan.
- **Array splatting** di PowerShell: flag `-p` cuma dipake kalo ada password.
  Biar gak error pas password kosong (XAMPP default).
- Alur **DROP → Restore → Verify** memastikan restore beneran sukses.
  Verify.sql ngecek jumlah tabel, data, trigger, SP — kalo ada yang kurang,
  berarti restore gagal.

---

### Diagram Pipeline Keseluruhan

```
                        ┌──────────────┐
                        │   PEMBACA    │
                        └──────┬───────┘
                               │
          ┌────────────────────┼────────────────────┐
          ▼                    ▼                    ▼
   [1. Registrasi]      [7. Aktivitas]      [7. Baca Buku]
   signup.php           catalog/detail       baca.php
          │                    │
          ▼                    ▼
   [Login]               Wishlist/Bookmark
   login.php             Review/Rating
          │                    │
          ▼                    ▼
   Session + Dashboard   Riwayat + Denda
          │
          ▼
┌──────────────────────────────────────────────────────┐
│                   STAFF                               │
├──────────────────────────────────────────────────────┤
│  [2. Pinjam] → sp_pinjam_buku → due_date otomatis    │
│  [3. Kembali] → UPDATE → auto_fine kalo telat       │
│  [4. Deteksi Telat] → sp_proses_keterlambatan        │
│  [5. Bayar Denda] → sp_bayar_denda → Lunas          │
│  [6. CRUD Buku] → books + authors + categories      │
│  [9. Audit Log] → Lihat log aktivitas               │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────┐
│                   ADMIN                               │
├──────────────────────────────────────────────────────┤
│  [8. Laporan] → 6 tab aggregate + SP bulanan         │
│  [8. Dashboard] → (kosong — perlu diisi)              │
│  [9. Audit Log] → Lihat semua log                    │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────┐
│                   SYSTEM                              │
├──────────────────────────────────────────────────────┤
│  [9. Audit Trigger] → otomatis catat log             │
│  [10. Backup] → backup.bat → file .sql               │
│  [10. Restore] → restore.bat → DROP → restore → verif│
└──────────────────────────────────────────────────────┘
```

**Catatan:** Nomor di diagram = nomor pipeline di atas.
Ada pipeline yang overlapping (misal pipeline 2, 3, 4 jalan berurutan:
pinjam → kembali → denda). Tapi tiap pipeline punya trigger/SP sendiri-sendiri.
