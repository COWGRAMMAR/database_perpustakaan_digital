# Dokumentasi Database — `perpustakaan_digital`

## Daftar Tabel (urutan dependency, parent → child)

| # | Tabel | Keterangan |
|---|---|---|
| 1 | `roles` | Daftar role: Admin, Staff, Pembaca |
| 2 | `publishers` | Data penerbit |
| 3 | `authors` | Data penulis |
| 4 | `categories` | Kategori buku |
| 5 | `users` | Akun login (semua role) |
| 6 | `user_roles` | Penghubung user ↔ role |
| 7 | `staff_profiles` | Detail profil Staff |
| 8 | `member_profiles` | Detail profil Pembaca |
| 9 | `books` | Data utama buku |
| 10 | `book_authors` | Penghubung buku ↔ penulis (many-to-many) |
| 11 | `book_categories` | Penghubung buku ↔ kategori (many-to-many) |
| 12 | `book_files` | File digital buku (PDF/EPUB), berupa `file_url` |
| 13 | `borrowings` | Transaksi peminjaman |
| 14 | `reading_history` | Histori membaca (1 baris per buku per user) |
| 15 | `bookmarks` | Penanda halaman |
| 16 | `reviews_ratings` | Ulasan & rating (1 user = 1 ulasan per buku) |
| 17 | `wishlists` | Daftar buku ingin dibaca |
| 18 | `fines` | Denda keterlambatan |
| 19 | `payments` | Transaksi pembayaran denda |
| 20 | `audit_logs` | Log aktivitas Staff/Admin |

> Skema lengkap kolom & tipe data: lihat [`docs/proposal.md`](proposal.md) (sudah dikoreksi dari typo asli: `tittle`→`title`, `fune_id`→`fine_id`, `last_accesed`→`last_accessed`, `phone_number` INT→VARCHAR(15)).

## Trigger

| Nama | Event | Fungsi |
|---|---|---|
| `trg_set_due_date` | BEFORE INSERT ON `borrowings` | Set `due_date` = `borrow_date` + 7 hari otomatis. **Wajib aktif** sebelum `sp_pinjam_buku` dipanggil. |
| `trg_auto_fine` | AFTER UPDATE ON `borrowings` | Jika `return_date` > `due_date` dan `status` = 'Terlambat', otomatis INSERT ke `fines` (Rp1.000/hari). |
| `trg_audit_borrowings_insert` | AFTER INSERT ON `borrowings` | Catat ke `audit_logs` otomatis pakai `NEW.user_id` (trigger tidak tahu session PHP, jadi hanya menangkap aksi yang langsung tercermin di kolom tabel). |

> Aksi Staff lain yang tidak tertangkap trigger (misal proses kembali buku, verifikasi pembayaran) dicatat manual lewat `includes/audit_helper.php` → fungsi `logAudit()`.

## Function (UDF)

| Nama | Return | Fungsi |
|---|---|---|
| `fn_hitung_denda(borrowing_id INT)` | DECIMAL(10,2) | Hitung denda dari selisih `due_date` & `return_date` × Rp1.000. Return 0 jika tidak terlambat. |
| `fn_avg_rating(book_id INT)` | DECIMAL(3,2) | Rata-rata rating dari `reviews_ratings`. Return 0.00 jika belum ada review. |

## Tarif & Aturan Default

- Durasi peminjaman: **7 hari**
- Tarif denda: **Rp1.000/hari**
- Password: hash **MD5**
