# Dokumentasi Modul

Daftar lengkap file per role beserta judul halaman (`$pageTitle`) dan ringkasan fungsi, berdasarkan kode aktual di repo.

## Admin

| File | Judul Halaman | Fungsi |
|---|---|---|
| `dashboard.php` | Dashboard Admin | Placeholder ringkas (sengaja, tidak perlu data asli) |
| `users.php` | Manajemen User | Full CRUD user + role + profil staff/member |
| `staff.php` | Manajemen Staff | List + detail read-only, link Edit ke `users.php` |
| `books.php` | Manajemen Buku (Monitoring) | Read-only daftar buku |
| `book_files.php` | File Buku | Read-only daftar file digital per buku (`?book_id=`) |
| `master_data.php` | Master Data Buku (Monitoring) | Read-only, tab Penulis/Penerbit/Kategori |
| `borrowings.php` | Peminjaman (Monitoring) | Read-only seluruh transaksi peminjaman |
| `fines.php` | Denda & Pembayaran (Monitoring) | Read-only seluruh denda & pembayaran |
| `laporan.php` | Laporan | 1 halaman tab: Buku Terlaris, Peminjaman Per Periode, Denda Per Bulan, Member Baru Per Bulan, Statistik Per Kategori, Ringkasan Bulanan (pakai `sp_laporan_bulanan`) |
| `audit_log.php` | Audit Log | Semua aktivitas sistem (semua role) |

## Staff

| File | Judul Halaman | Fungsi |
|---|---|---|
| `dashboard.php` | Dashboard Staff | Placeholder ringkas (sengaja) |
| `books.php` | Manajemen Buku | Full CRUD buku + relasi penulis/kategori (many-to-many) |
| `book_files.php` | File Buku | Full CRUD link file digital (`?book_id=`), input `file_url` manual (bukan upload fisik) |
| `master_data.php` | Master Data Buku | Full CRUD Penulis/Penerbit/Kategori (digabung 1 halaman tab) |
| `borrowings.php` | Manajemen Peminjaman | CALL `sp_pinjam_buku`, proses kembali manual, auto-CALL `sp_proses_keterlambatan` di awal script tiap load |
| `fines.php` | Denda & Pembayaran | Verifikasi pembayaran via `sp_bayar_denda` |
| `members.php` | Data Pembaca | Read-only list + detail member, termasuk riwayat peminjaman & denda |
| `audit_log.php` | Audit System (Aktivitas Staff) | Filter audit log khusus aktivitas role Staff |

## Pembaca

| File | Judul Halaman | Fungsi |
|---|---|---|
| `dashboard.php` | Profil Saya | Tampilkan data profil sendiri (nama, no. anggota, membership type, kontak, alamat) — read-only, **berfungsi sebagai Dashboard sekaligus halaman Profil** (lihat catatan di `PERMISSION_MATRIX.md`) |
| `catalog.php` | Katalog Buku | List semua buku + search judul + filter kategori, badge "Sedang Dipinjam", tombol tambah Wishlist |
| `detail_buku.php` | Detail Buku | Info buku lengkap, tombol Baca per file, Wishlist, Bookmark CRUD (modal tambah/edit/hapus), Ulasan & Rating (1 user 1 ulasan per buku) |
| `baca.php` | - | Endpoint jembatan (bukan halaman tampilan): cek/update `reading_history`, lalu redirect ke `file_url` asli |
| `wishlist.php` | Wishlist | List + delete (tidak ada edit), query aman dengan `WHERE id = ? AND user_id = ?` |
| `reading_history.php` | Riwayat Membaca | 1 baris per buku, kolom halaman terakhir + progress bar, modal update halaman manual |
| `borrowings.php` | Peminjaman Saya | Read-only, filter `$_SESSION['user_id']` |
| `fines.php` | Denda & Pembayaran Saya | Read-only riwayat denda & pembayaran sendiri |

## Includes & Auth

| File | Fungsi |
|---|---|
| `auth/check_session.php` | `requireLogin()`, `requireRole(array)`, `redirectToDashboard()`, `getBasePath()` (base path dinamis — otomatis deteksi dari `__DIR__` + `DOCUMENT_ROOT`) |
| `config/database.php` | Koneksi mysqli |
| `includes/header.php` / `footer.php` | Layout bersama |
| `includes/sidebar.php` | Menu dinamis per role, highlight halaman aktif berdasarkan `basename($_SERVER['PHP_SELF'])` |
| `includes/audit_helper.php` | `logAudit($conn, $user_id, $action, $table_name, $description)` — dipakai manual di `staff/borrowings.php` (PROSES_PINJAM, PROSES_KEMBALI) dan `staff/fines.php` (VERIFIKASI_BAYAR), karena trigger SQL tidak bisa akses session PHP |

## Status Pengerjaan

Semua modul di atas **sudah selesai dan berjalan**. Tidak ada modul placeholder kecuali Dashboard Admin & Staff (sengaja dibiarkan ringkas).

## Known Issues (per kondisi terakhir)

- Tidak ada lagi — menu "Profil Saya" terpisah sudah dihapus dari sidebar karena fungsinya digabung ke `pembaca/dashboard.php`. Tidak ada link mati.
