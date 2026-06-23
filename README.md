# Sistem Perpustakaan Digital

Aplikasi web CRUD untuk manajemen perpustakaan digital, dibangun sebagai tugas kuliah (mata kuliah Sisbad). Mendukung 3 role pengguna: **Admin**, **Staff**, dan **Pembaca**, masing-masing dengan hak akses berbeda.

## Stack Teknologi

- **Backend:** PHP murni (tanpa framework, tanpa JavaScript kecuali `confirm()` native)
- **Database:** MySQL (via `mysqli`)
- **Styling:** Tailwind CSS (via CDN)
- **Server lokal:** XAMPP

## Struktur Folder

```
database_perpustakaan_digital/
├── index.php, login.php, signup.php, logout.php
├── config/
│   └── database.php          # koneksi mysqli
├── auth/
│   └── check_session.php     # requireLogin(), requireRole(), getBasePath()
├── includes/
│   ├── header.php
│   ├── sidebar.php
│   ├── footer.php
│   └── audit_helper.php      # logAudit()
├── admin/
├── staff/
├── pembaca/
├── sql/
│   ├── ddl.sql
│   ├── dml.sql
│   ├── trigger.sql
│   ├── function.sql
│   ├── procedure.sql
│   └── cursor.sql
└── assets/img/
```

## Cara Menjalankan

1. Clone/copy project ke `C:\xampp\htdocs\sisbad\database_perpustakaan_digital`
2. Import database `perpustakaan_digital` via phpMyAdmin (jalankan `sql/ddl.sql`, `sql/dml.sql`, lalu seluruh trigger & stored procedure)
3. Pastikan trigger `trg_set_due_date` dan `trg_auto_fine` sudah aktif (cek via `SHOW TRIGGERS`)
4. Akses melalui `http://localhost/sisbad/database_perpustakaan_digital/`

## Role & Akses

| Role | Hak Akses Utama |
|---|---|
| **Admin** | Monitoring penuh (read-only di banyak modul), CRUD User, lihat Laporan & Audit Log |
| **Staff** | CRUD operasional penuh (Buku, Peminjaman, Denda), tidak bisa kelola User |
| **Pembaca** | Akses data milik sendiri (Peminjaman, Denda, Wishlist, Bookmark, Riwayat Membaca), CRUD Ulasan & Rating sendiri |

Detail lengkap matrix permission per modul: lihat [`docs/PERMISSION_MATRIX.md`](docs/PERMISSION_MATRIX.md)

## Dokumentasi Lengkap

| Dokumen | Isi |
|---|---|
| [`docs/DATABASE.md`](docs/DATABASE.md) | Skema tabel, ERD ringkas, trigger, function |
| [`docs/STORED_PROCEDURES.md`](docs/STORED_PROCEDURES.md) | Daftar SP, parameter, pattern pemanggilan via mysqli |
| [`docs/PERMISSION_MATRIX.md`](docs/PERMISSION_MATRIX.md) | Matrix akses lengkap per modul per role |
| [`docs/MODUL.md`](docs/MODUL.md) | Daftar file per modul, status pengerjaan, fitur tiap halaman |
| [`docs/CODE_EXPLANATION.md`](docs/CODE_EXPLANATION.md) | Penjelasan alur kerja kode (bukan cuma daftar file) — peminjaman, denda, laporan, audit log, dll |
| [`docs/SETUP.md`](docs/SETUP.md) | Langkah instalasi & konfigurasi detail |

## Catatan Pengembangan

- Password user di-hash dengan **MD5**
- Signup publik hanya untuk role **Pembaca**
- Stored procedure tidak boleh dibuat ulang — gunakan versi yang sudah ada di `sql/procedures/`
- Pattern pemanggilan SP via mysqli mengikuti aturan flush `more_results()`/`next_result()` (lihat `docs/STORED_PROCEDURES.md`)

## Status Project

Seluruh modul utama (Auth, Master Data, Manajemen Buku, Manajemen User/Staff, Peminjaman, Denda & Pembayaran, Laporan, Audit Log, seluruh modul Pembaca) **sudah selesai**. Dashboard Admin & Staff sengaja dibiarkan sebagai placeholder ringkas. Dashboard Pembaca (`pembaca/dashboard.php`) berfungsi ganda sebagai halaman Profil (read-only) — menu "Profil Saya" terpisah sudah dihapus dari sidebar karena fungsinya digabung di sini. Detail lihat `docs/MODUL.md`.
