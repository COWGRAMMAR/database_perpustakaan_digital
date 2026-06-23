# Penjelasan Kode (Code Walkthrough)

Dokumen ini menjelaskan **cara kerja** logic di balik modul-modul penting — bukan cuma daftar file (lihat `MODUL.md` untuk itu), tapi alur eksekusinya step-by-step. Berguna untuk presentasi/demo tugas atau buat siapa pun yang baru baca kode ini.

---

## 1. Alur Autentikasi & Proteksi Halaman

Setiap halaman yang butuh login wajib diawali pattern ini:

```php
require_once '../auth/check_session.php';
requireRole(['Staff']);   // atau ['Admin'], ['Pembaca']
require_once '../config/database.php';
```

**Cara kerjanya (`auth/check_session.php`):**
1. `session_start()` dipanggil otomatis saat file ini di-include.
2. `requireRole($allowedRoles)` lebih dulu memanggil `requireLogin()` → cek `$_SESSION['user_id']` ada atau tidak. Kalau tidak ada, redirect ke `login.php`.
3. Kalau sudah login tapi role-nya (`$_SESSION['role']`) tidak ada di `$allowedRoles`, dilempar ke dashboard role-nya sendiri lewat `redirectToDashboard()` — **bukan** ke halaman error 403. Jadi Pembaca yang coba akses URL Staff secara langsung akan otomatis dibalikkan ke dashboard Pembaca, tanpa pesan apa pun.
4. `getBasePath()` mengembalikan path absolut hardcoded `/sisbad/database_perpustakaan_digital/`. Semua redirect dan link sidebar pakai fungsi ini, supaya konsisten walau halaman diakses dari folder berbeda (`admin/`, `staff/`, `pembaca/`).

---

## 2. Alur Peminjaman Buku (`staff/borrowings.php`)

Ini modul paling kompleks karena menggabungkan stored procedure, trigger, dan audit log manual.

**Setiap kali halaman dibuka** (bukan cuma saat submit form):
```php
$conn->query("CALL sp_proses_keterlambatan()");
while ($conn->more_results() && $conn->next_result()) { /* flush */ }
```
Ini menjalankan SP yang pakai **cursor** untuk mengubah status semua peminjaman yang `due_date` sudah lewat dari `'Dipinjam'` menjadi `'Terlambat'`. Efeknya: status terlambat selalu up-to-date setiap kali Staff membuka halaman ini, tanpa perlu cron job.

**Saat Staff mencatat peminjaman baru** (form submit `action=pinjam`):
1. PHP memanggil `CALL sp_pinjam_buku(?, ?)` — SP ini sendiri yang mengecek apakah user masih punya pinjaman aktif untuk buku yang sama (row lock `FOR UPDATE`), baru INSERT ke `borrowings` kalau aman.
2. `due_date` **tidak diisi oleh PHP maupun SP** — trigger `trg_set_due_date` (BEFORE INSERT) yang otomatis mengisinya = `borrow_date + 7 hari`.
3. Hasil SP dibaca dari kolom `hasil` (bukan `$conn->error`), dicek dengan `str_contains($hasil, 'berhasil')`.
4. Kalau berhasil → `logAudit()` dipanggil manual untuk mencatat ke `audit_logs`, karena trigger SQL tidak tahu siapa Staff yang login (trigger hanya tahu `NEW.user_id` dari pembaca yang dipinjamkan, bukan dari session PHP Staff yang memprosesnya).

**Saat Staff memproses pengembalian** (`?return=<id>`):
1. PHP membandingkan tanggal hari ini dengan `due_date` buku itu secara manual untuk menentukan status: `'Terlambat'` jika lewat, `'Kembali'` jika tidak.
2. UPDATE `borrowings` dengan `return_date` & `status` baru.
3. Trigger `trg_auto_fine` (AFTER UPDATE) otomatis mendeteksi kondisi `return_date > due_date AND status = 'Terlambat'`, lalu INSERT ke `fines` dengan nominal `(return_date - due_date) × Rp1.000`.
4. `logAudit()` dipanggil manual lagi setelah update berhasil.

> **Kenapa due_date & fine dihitung lewat trigger, bukan PHP?** Supaya konsisten dipakai siapa pun yang mengubah data lewat jalur manapun (PHP, query manual phpMyAdmin, dll) — logic bisnis intinya tidak bisa "dilewati" walau ada bug di PHP.

---

## 3. Alur Pembayaran Denda (`staff/fines.php`)

1. **Validasi awal di PHP** (sebelum panggil SP): cek `fine_id` ada dan `fine_status` belum `'Lunas'`. Ini supaya pesan error ke user lebih informatif (SP juga punya validasi sendiri sebagai lapis kedua).
2. `CALL sp_bayar_denda(?, ?)` menjalankan TCL murni di sisi SQL:
   ```
   START TRANSACTION
   → UPDATE fines SET fine_status = 'Lunas'
   → INSERT INTO payments (...)
   → COMMIT
   ```
   Kalau salah satu gagal (misal constraint error), `EXIT HANDLER FOR SQLEXCEPTION` di dalam SP otomatis `ROLLBACK` dan mengembalikan pesan kegagalan lewat kolom `hasil`.
3. PHP membaca `$row['hasil']`, lalu flush dengan `more_results()`/`next_result()`.
4. Kalau hasilnya mengandung `"berhasil"` → `logAudit()` mencatat aksi `VERIFIKASI_BAYAR`.

---

## 4. Alur Laporan Bulanan (`admin/laporan.php`)

`sp_laporan_bulanan(bulan, tahun)` itu unik karena **mengembalikan 4 result set sekaligus** dalam satu pemanggilan (4 statement `SELECT` berurutan di dalam SP). PHP **tidak bisa** langsung `get_result()` sekali saja — harus pakai loop `do...while`:

```php
$stmt = $conn->prepare("CALL sp_laporan_bulanan(?, ?)");
$stmt->bind_param('ii', $bulan, $tahun);
$stmt->execute();

$hasil = [];
do {
    if ($res = $stmt->get_result()) {
        $hasil[] = $res->fetch_all(MYSQLI_ASSOC);
    }
} while ($conn->more_results() && $conn->next_result());
```

Setiap elemen `$hasil[0..3]` berisi: total peminjaman, total denda masuk, jumlah member baru, dan buku paling sering dipinjam — sesuai urutan `SELECT` di dalam SP.

> **Bug klasik kalau lupa pattern ini:** kalau hanya panggil `get_result()` sekali tanpa loop `more_results()`, query berikutnya di halaman yang sama akan error **"Commands out of sync"**, karena koneksi mysqli masih "menahan" sisa result set yang belum dibaca.

---

## 5. Alur Baca Buku (Pembaca) — `pembaca/baca.php`

File ini **bukan halaman tampilan** — murni endpoint proses/jembatan. Saat user klik tombol "Baca" di `detail_buku.php`:

1. Browser request `baca.php?book_id=X&file_id=Y`
2. PHP cek apakah user sudah punya baris di `reading_history` untuk buku itu:
   - **Belum ada** → INSERT baris baru, `last_page_read` mulai dari `1`
   - **Sudah ada** → UPDATE `last_accessed` ke waktu sekarang (halaman terakhir **tidak** diubah otomatis, karena tidak ada reader sungguhan yang melacak posisi baca real-time)
3. Setelah proses selesai, langsung `header('Location: ' . $file_url)` ke alamat file PDF/EPUB asli yang tersimpan di `book_files.file_url`.

> **Konsekuensi desain ini:** progress baca (`last_page_read`) hanya berubah kalau user update manual lewat modal di `pembaca/reading_history.php` — bukan otomatis terdeteksi dari aktivitas baca.

---

## 6. Alur Detail Buku (Pembaca) — `pembaca/detail_buku.php`

Halaman ini menggabungkan beberapa fitur independen dalam satu file:

- **Tombol Baca**: di-render dari loop `$bookFiles` (hasil query ke tabel `book_files` berdasarkan `book_id`) — satu tombol emerald per baris file (PDF/EPUB bisa lebih dari satu per buku).
- **Wishlist**: sebelum INSERT, cek dulu apakah kombinasi `user_id` + `book_id` sudah ada di `wishlists`, supaya tidak duplikat.
- **Bookmark**: full CRUD di halaman yang sama menggunakan modal — tambah, edit per-item, hapus. Validasi `page_number` harus ≤ `total_pages` buku tersebut.
- **Ulasan & Rating**: cek dulu apakah `user_id` + `book_id` sudah punya baris di `reviews_ratings`. Kalau sudah ada → form otomatis berubah jadi mode "Edit Ulasan Saya" (UPDATE, bukan INSERT baru) — menegakkan aturan 1 user = 1 ulasan per buku di level aplikasi, bukan di level database (tidak ada UNIQUE constraint di tabel).

---

## 7. Manajemen Buku & Relasi Many-to-Many (`staff/books.php`)

Buku punya relasi many-to-many ke Penulis (`book_authors`) dan Kategori (`book_categories`). Pattern yang dipakai untuk update relasi ini **delete-then-reinsert**, bukan diff manual:

```php
// Saat UPDATE buku:
DELETE FROM book_authors WHERE book_id = $id;
DELETE FROM book_categories WHERE book_id = $id;
// lalu insert ulang semua author_ids[] dan category_ids[] yang baru dipilih dari form
```

Ini lebih sederhana daripada cek "mana yang ditambah, mana yang dihapus" satu per satu, dengan trade-off: setiap update buku selalu menghapus dan menulis ulang seluruh baris relasi, walau yang berubah cuma satu penulis.

---

## 8. Audit Log: Trigger vs Manual

Ada dua sumber log di `audit_logs`:

| Sumber | Kapan dipakai | Kelemahan |
|---|---|---|
| Trigger `trg_audit_borrowings_insert` | Otomatis saat INSERT ke `borrowings` | Hanya tahu `NEW.user_id` (pembaca yang dipinjamkan), tidak tahu **Staff mana** yang memproses dari sisi PHP, karena trigger SQL tidak punya akses ke `$_SESSION` |
| `logAudit()` manual (`includes/audit_helper.php`) | Dipanggil eksplisit di `staff/borrowings.php` (proses pinjam & kembali) dan `staff/fines.php` (verifikasi bayar) | Harus diingat untuk dipasang di setiap aksi baru — kalau lupa, aksi itu tidak akan tercatat |

`admin/audit_log.php` menampilkan gabungan kedua sumber ini (semua baris di `audit_logs`, tanpa membedakan asalnya dari trigger atau manual). `staff/audit_log.php` memfilter hanya baris yang `user_id`-nya berperan sebagai Staff.
