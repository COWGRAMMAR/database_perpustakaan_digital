# Matrix Permission Per Modul

Sumber: hasil cek `requireRole()` di setiap file (admin/, staff/, pembaca/) — bukan asumsi, tapi langsung dari kode.

| Modul | File | Admin | Staff | Pembaca |
|---|---|---|---|---|
| Dashboard | `dashboard.php` | Placeholder ringkas | Placeholder ringkas | Berisi data profil sendiri (lihat catatan) |
| Master Data Buku (Penulis/Penerbit/Kategori) | `master_data.php` | Read-only (monitoring) | Full CRUD | Tidak ada akses |
| Manajemen Buku | `books.php` | Read-only (monitoring) | Full CRUD | Tidak ada akses langsung (akses via Katalog) |
| File Buku (PDF/EPUB) | `book_files.php` | Read-only (`?book_id=`) | Full CRUD (`?book_id=`) | Tidak ada akses langsung (tombol "Baca" muncul di Detail Buku) |
| Manajemen User | `users.php` | Full CRUD (satu-satunya yang bisa) | Tidak ada akses | Tidak ada akses |
| Manajemen Staff | `staff.php` | List + detail read-only, link Edit ke `users.php` | - | - |
| Data Pembaca | `members.php` | - | Read-only list + detail (+ riwayat pinjam/denda) | - |
| Peminjaman | `borrowings.php` | Read-only (monitoring) | Full akses (CALL `sp_pinjam_buku`, proses kembali manual, auto-CALL `sp_proses_keterlambatan` di awal script) | Read-only, hanya data sendiri |
| Denda & Pembayaran | `fines.php` | Read-only (monitoring) | Full akses (verifikasi bayar via `sp_bayar_denda`) | Read-only, hanya data sendiri |
| Laporan | `laporan.php` | Akses penuh (5 jenis laporan, termasuk `sp_laporan_bulanan`) | Tidak ada akses | Tidak ada akses |
| Audit Log | `audit_log.php` | Semua aktivitas (semua role) | Filter hanya aktivitas role Staff | Tidak ada akses |
| Katalog Buku | `catalog.php` | - | - | Full (search, filter kategori, badge ketersediaan, tambah wishlist) |
| Detail Buku | `detail_buku.php` | - | - | Full (baca file, wishlist, bookmark CRUD, ulasan & rating CRUD) |
| Baca (jembatan ke file) | `baca.php` | - | - | Endpoint proses (catat `reading_history`, redirect ke `file_url`) |
| Wishlist | `wishlist.php` | - | - | Read + delete (tidak ada edit), aman dari hapus milik orang lain |
| Riwayat Membaca | `reading_history.php` | - | - | Read + update halaman manual (progress bar) |

## Catatan Penting

1. **Dashboard Pembaca (`pembaca/dashboard.php`)** saat ini menampilkan **data profil** (nama, nomor anggota, membership type, kontak, alamat) — bersifat read-only, bukan dashboard statistik (jumlah peminjaman aktif/wishlist/dll belum ada). Menu "Profil Saya" terpisah sudah **dihapus dari sidebar** karena fungsinya sudah digabung ke Dashboard. Ini keputusan final yang disengaja, bukan bug.
2. **Bookmark** dan **Ulasan & Rating** tidak punya halaman/menu sendiri — keduanya diakses sebagai section di dalam `detail_buku.php`.
3. **Manajemen Denda & Pembayaran (Admin)** dan **Manajemen Peminjaman (Admin)** keduanya read-only murni untuk tujuan monitoring; semua aksi tulis (proses pinjam, proses kembali, verifikasi bayar) hanya bisa dilakukan Staff.
4. Hak istimewa Staff untuk **Master Data Buku** menggunakan `requireRole(['Staff'])` saja (bukan `['Admin','Staff']`), supaya Admin tidak bisa menulis lewat halaman Staff meski tahu URL-nya.
