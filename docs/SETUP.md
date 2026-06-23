# Setup & Instalasi

## Prasyarat

- [XAMPP](https://www.apachefriends.org/) (PHP 8+, MySQL/MariaDB, Apache)
- Browser modern

## Langkah Instalasi

### 1. Tempatkan project

Copy/clone folder project ke:

```
C:\xampp\htdocs\sisbad\database_perpustakaan_digital
```

> Path ini **hardcoded** di `auth/check_session.php` fungsi `getBasePath()`:
> ```php
> function getBasePath() {
>     $folderName = 'sisbad/database_perpustakaan_digital';
>     return '/' . $folderName . '/';
> }
> ```
> Kalau lokasi/path project diubah, fungsi ini **harus disesuaikan**, atau semua redirect (login, requireRole, dll) akan salah arah.

### 2. Jalankan XAMPP

Buka **XAMPP Control Panel** → Start **Apache** dan **MySQL** (pastikan keduanya hijau).

### 3. Setup Database

Buka [http://localhost/phpmyadmin](http://localhost/phpmyadmin), lalu jalankan file SQL di folder `sql/` dengan **urutan berikut (penting, tidak boleh dibalik)**:

| Urutan | File | Isi |
|---|---|---|
| 1 | `sql/ddl.sql` | Membuat database `perpustakaan_digital` + 20 tabel |
| 2 | `sql/function.sql` | 2 User-Defined Function (`fn_hitung_denda`, `fn_avg_rating`) |
| 3 | `sql/trigger.sql` | 3 Trigger (`trg_set_due_date`, `trg_auto_fine`, `trg_audit_borrowings_insert`) |
| 4 | `sql/procedure.sql` | 4 Stored Procedure (lihat `docs/STORED_PROCEDURES.md`) |
| 5 | `sql/dml.sql` | Data dummy (jalankan **setelah** trigger aktif, agar data konsisten) |
| 6 | `sql/reporting.sql` | Query agregat & laporan (opsional, bisa langsung dieksekusi untuk testing) |

**Cara import per file:**
1. Klik tab **SQL** di navbar atas phpMyAdmin (pastikan database `perpustakaan_digital` sudah terpilih di sidebar setelah `ddl.sql` jalan)
2. Klik **Choose File**, pilih file sesuai urutan
3. Klik **Go**

### 4. Verifikasi Database

- Database `perpustakaan_digital` harus punya **20 tabel**
- `SHOW TRIGGERS;` harus menunjukkan 3 trigger aktif
- `SHOW PROCEDURE STATUS WHERE Db = 'perpustakaan_digital';` harus menunjukkan 4 stored procedure
- Tabel `books`, `users`, dll sudah terisi data dummy setelah `dml.sql`

### 5. Akses Aplikasi

```
http://localhost/sisbad/database_perpustakaan_digital/
```

Login dengan akun dari data dummy (`sql/dml.sql`), atau signup baru (signup publik hanya untuk role **Pembaca**).

## File SQL yang TIDAK perlu dijalankan manual

- `sql/backup.sql` — isinya **dokumentasi** command `mysqldump`/restore, bukan untuk di-import langsung.
- `sql/procedures/*.sql` — versi pecahan per-SP, sudah digabung semua di `sql/procedure.sql`. Cukup jalankan `procedure.sql`; file di folder `procedures/` untuk referensi/baca per-SP saja.

## Troubleshooting Umum

| Masalah | Kemungkinan Sebab | Solusi |
|---|---|---|
| "Koneksi gagal" / halaman blank | MySQL belum jalan, atau kredensial di `config/database.php` salah | Start MySQL di XAMPP, cek `config/database.php` |
| Semua link redirect ke login terus | Path project tidak sesuai `getBasePath()` | Sesuaikan `$folderName` di `auth/check_session.php` |
| "Commands out of sync" saat panggil fitur Peminjaman/Denda/Laporan | Lupa flush `more_results()`/`next_result()` setelah `CALL` SP | Lihat pattern di `docs/STORED_PROCEDURES.md` |
| Denda tidak otomatis muncul saat telat | Trigger `trg_auto_fine` belum aktif | Cek `SHOW TRIGGERS`, jalankan ulang `sql/trigger.sql` |
| `due_date` peminjaman kosong/null | Trigger `trg_set_due_date` belum aktif sebelum `sp_pinjam_buku` dipanggil | Cek `SHOW TRIGGERS`, urutan import SQL harus benar |
