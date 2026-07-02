#  Sistem Perpustakaan Digital

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=flat&logo=tailwind-css&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=flat&logo=xampp&logoColor=white)
![Status](https://img.shields.io/badge/Status-Selesai-2ea44f?style=flat)

Aplikasi web **manajemen perpustakaan digital** berbasis PHP + MySQL — tugas mata kuliah Sistem Basis Data (Sisbad). Mendukung **3 role pengguna**: Admin, Staff, dan Pembaca dengan sistem autentikasi, manajemen buku, peminjaman, denda, laporan, dan audit log.

>  **Demo:** `http://localhost/<NAMA_FOLDER>/` (XAMPP)
>  **Dokumentasi lengkap:** [`docs/`](docs/)

---

##  Fitur Utama

| Fitur | Admin | Staff | Pembaca |
|---|---|---|---|
| **Manajemen Buku** |  Lihat |  CRUD |  |
| **Manajemen User** |  CRUD |  |  |
| **Peminjaman** |  Lihat |  CRUD |  Milik sendiri |
| **Denda & Pembayaran** |  Lihat |  CRUD |  Milik sendiri |
| **Ulasan & Rating** |  |  |  CRUD |
| **Wishlist / Bookmark** |  |  |  |
| **Laporan** |  Lihat |  Lihat |  |
| **Audit Log** |  Lihat |  |  |

## Stack Teknologi

- **Backend:** PHP murni (tanpa framework, tanpa JavaScript kecuali `confirm()` native)
- **Database:** MySQL (via `mysqli`) + Stored Procedure, Trigger, User-Defined Function
- **Styling:** Tailwind CSS (via CDN)
- **Server lokal:** XAMPP

## Struktur Folder

```
<NAMA_FOLDER>/
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
├── docs/                      # Dokumentasi lengkap project
│   ├── DATABASE.md, SETUP.md, MODUL.md
│   ├── PERMISSION_MATRIX.md, STORED_PROCEDURES.md
│   ├── CODE_EXPLANATION.md, TESTING.md
│   ├── BELAJAR.md             # Dokumentasi belajar sesuai rubrik + pipeline
│   ├── proposal.md, plan.md
├── sql/
│   ├── ddl.sql, dml.sql, trigger.sql
│   ├── function.sql, procedure.sql, cursor.sql
│   └── procedures/            # Stored procedure (per file)
└── assets/img/
```

## Cara Menjalankan

1. Clone/copy project ke `C:\xampp\htdocs\<NAMA_FOLDER>`
2. Import database `perpustakaan_digital` via phpMyAdmin (jalankan `sql/ddl.sql`, `sql/dml.sql`, lalu seluruh trigger & stored procedure)
3. Import function & stored procedure: jalankan `sql/function.sql`, `sql/procedure.sql` (atau per-file di `sql/procedures/`)
4. Pastikan trigger `trg_set_due_date` dan `trg_auto_fine` sudah aktif (cek via `SHOW TRIGGERS`)
5. Akses melalui `http://localhost/<NAMA_FOLDER>/`

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
| [`docs/CODE_EXPLANATION.md`](docs/CODE_EXPLANATION.md) | Penjelasan alur kerja kode — peminjaman, denda, laporan, audit log, dll |
| [`docs/SETUP.md`](docs/SETUP.md) | Langkah instalasi & konfigurasi detail |
| [`docs/TESTING.md`](docs/TESTING.md) | Panduan setup XAMPP symlink + eksekusi SQL + test API |
| [`docs/proposal.md`](docs/proposal.md) | Proposal awal database (konversi dari docx) |
| [`docs/plan.md`](docs/plan.md) | Checklist progress implementasi & rencana kerja |
| [`docs/BELAJAR.md`](docs/BELAJAR.md) | Dokumentasi belajar 8 rubrik + 10 pipeline — bahan matkul Sistem Basis Data |

## Catatan Pengembangan

- Password user di-hash dengan **MD5** (untuk tugas kuliah, bukan production)
- Signup publik hanya untuk role **Pembaca**
- **UDF** `fn_hitung_denda` dan `fn_avg_rating` sudah terintegrasi ke trigger (`trg_auto_fine`) dan halaman PHP — jika ingin mengubah tarif denda, cukup edit `fn_hitung_denda` di `sql/function.sql`
- Stored procedure tidak boleh dibuat ulang — gunakan versi yang sudah ada di `sql/procedures/`
- Pattern pemanggilan SP via mysqli mengikuti aturan flush `more_results()`/`next_result()` (lihat `docs/STORED_PROCEDURES.md`)

## Status Project

Seluruh modul utama (Auth, Master Data, Manajemen Buku, Manajemen User/Staff, Peminjaman, Denda & Pembayaran, Laporan, Audit Log, seluruh modul Pembaca) **sudah selesai**.

**UDF Integration (PR #2):** Function `fn_hitung_denda` dan `fn_avg_rating` sudah tidak dead code — terintegrasi ke trigger `trg_auto_fine`, halaman rating buku (`pembaca/detail_buku.php`), dan proyeksi denda staff (`staff/borrowings.php`).

Dashboard Admin & Staff sengaja dibiarkan sebagai placeholder ringkas. Dashboard Pembaca (`pembaca/dashboard.php`) berfungsi ganda sebagai halaman Profil (read-only) — menu "Profil Saya" terpisah sudah dihapus dari sidebar karena fungsinya digabung di sini. Detail lihat `docs/MODUL.md` dan `docs/BELAJAR.md`.
