# Prosedur Testing — Perpustakaan Digital

> Ikutin urutan ini dari 0 sampai bisa lihat data di browser.

---

## 1. Persiapan Awal

### 1.1. Pindahkan folder project ke htdocs

Folder project `database_perpustakaan_digital` harus ada di dalam `C:\xampp\htdocs\sisbad\`.

Biasanya lokasinya: `C:\xampp\htdocs\`

Jadi hasilnya: `C:\xampp\htdocs\sisbad\database_perpustakaan_digital\`

Kalau project-nya masih di luar (Desktop/Documents), **copy** folder `database_perpustakaan_digital` ke `C:\xampp\htdocs\sisbad\`. Buat folder `sisbad` dulu kalau belum ada.

### 1.2. Jalankan XAMPP

1. Buka **XAMPP Control Panel**
2. Klik **Start** pada baris **Apache**
3. Klik **Start** pada baris **MySQL**
   - Pastikan dua-duanya berwarna **hijau**

---

## 2. Setup Database (Jalankan SQL)

Semua file SQL ada di folder `sql/`.

### 2.1. Buka phpMyAdmin

- Di browser buka: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)

### 2.2. Import SQL — urutan PENTING

Ada 6 file SQL yang harus dijalankan. Urutannya **tidak boleh salah** karena file setelahnya bergantung pada file sebelumnya.

| No | File | Isi | Keterangan |
|----|------|-----|------------|
| 1 | `sql/ddl.sql` | Struktur database + 20 tabel | Bikin database `perpustakaan_digital` dan semua tabel |
| 2 | `sql/function.sql` | 2 User-Defined Function | Baru bisa dipanggil setelah tabel exist |
| 3 | `sql/trigger.sql` | 3 Trigger | Baru bisa dipasang setelah tabel borrowing dll exist |
| 4 | `sql/procedure.sql` | 4 Stored Procedure | Baru bisa dipasang setelah tabel & fungsi exist |
| 5 | `sql/dml.sql` | 100+ data dummy | Wajib jalan SETELAH trigger agar trigger aktif |
| 6 | `sql/reporting.sql` | Query aggregate + laporan | Opsional, tinggal execute langsung |

**Cara import di phpMyAdmin:**

1. Klik tab **SQL** di navbar atas
2. Klik tombol **Choose File** / **Pilih File**
3. Pilih file dari folder `sql/` (mulai dari `ddl.sql`)
4. Klik **Go**
5. Ulangi untuk file berikutnya sesuai urutan

> **Catatan**: File `backup.sql` isinya dokumentasi perintah mysqldump, bukan untuk di-import.

### 2.3. Verifikasi database

1. Klik database `perpustakaan_digital` di sidebar kiri phpMyAdmin
2. Seharusnya muncul **20 tabel**
3. Klik tabel `books` → **Browse** → lihat apakah ada data (setelah import dml.sql)

---

## 3. Testing API + Halaman HTML

### 3.1. Cek API langsung (via browser)

Buka endpoint-endpoint ini di browser untuk lihat JSON:

| Endpoint | Fungsi |
|----------|--------|
| [http://localhost/sisbad/database_perpustakaan_digital/api/buku.php](http://localhost/sisbad/database_perpustakaan_digital/api/buku.php) | List semua buku |
| [http://localhost/sisbad/database_perpustakaan_digital/api/buku.php?id=1](http://localhost/sisbad/database_perpustakaan_digital/api/buku.php?id=1) | Detail buku ID 1 |
| [http://localhost/sisbad/database_perpustakaan_digital/api/anggota.php](http://localhost/sisbad/database_perpustakaan_digital/api/anggota.php) | List anggota |
| [http://localhost/sisbad/database_perpustakaan_digital/api/peminjaman.php?id_user=5](http://localhost/sisbad/database_perpustakaan_digital/api/peminjaman.php?id_user=5) | Riwayat pinjam user ID 5 |
| [http://localhost/sisbad/database_perpustakaan_digital/api/denda.php?id_user=5](http://localhost/sisbad/database_perpustakaan_digital/api/denda.php?id_user=5) | Cek denda user ID 5 |
| [http://localhost/sisbad/database_perpustakaan_digital/api/laporan.php?bulan=6&tahun=2026](http://localhost/sisbad/database_perpustakaan_digital/api/laporan.php?bulan=6&tahun=2026) | Laporan bulan Juni 2026 |
| [http://localhost/sisbad/database_perpustakaan_digital/api/laporan.php?jenis=buku_terlaris](http://localhost/sisbad/database_perpustakaan_digital/api/laporan.php?jenis=buku_terlaris) | Top 10 buku terlaris |
| [http://localhost/sisbad/database_perpustakaan_digital/api/laporan.php?jenis=anggota_teraktif](http://localhost/sisbad/database_perpustakaan_digital/api/laporan.php?jenis=anggota_teraktif) | 10 anggota paling aktif |

Kalau API berhasil, bakal muncul teks JSON di browser.

### 3.2. Buka Halaman Test HTML

Buka di browser:

> [http://localhost/sisbad/database_perpustakaan_digital/old/index.html](http://localhost/sisbad/database_perpustakaan_digital/old/index.html)

Navigasi antar halaman:

| Halaman | Isi |
|---------|-----|
| `index.html` | Daftar buku (klik **Detail** buat lihat info lengkap) |
| `pinjam.html` | Form pinjam baru (isi ID User + ID Buku) + riwayat peminjaman |
| `denda.html` | Cek denda per user + bayar denda |
| `laporan.html` | Laporan bulanan interaktif + top buku + anggota teraktif |

**Cara pakai halaman test:**

1. **Daftar Buku** → langsung muncul pas halaman dibuka
2. **Pinjam Buku** → isi ID User dan ID Buku, klik Pinjam
3. **Riwayat** → isi ID User, klik Cari
4. **Cek Denda** → isi ID User, klik Cek Denda
5. **Bayar Denda** → isi ID Denda (dari hasil cek), pilih metode, klik Bayar
6. **Laporan Bulanan** → pilih bulan & tahun, klik Lihat Laporan

---

## 4. Testing via Postman (Opsional)

Bisa juga test API pake Postman:

**POST — Pinjam buku:**
```
URL: http://localhost/sisbad/database_perpustakaan_digital/api/peminjaman.php
Method: POST
Headers: Content-Type: application/json
Body (raw JSON):
{"id_user": 5, "id_buku": 3}
```

**POST — Bayar denda:**
```
URL: http://localhost/sisbad/database_perpustakaan_digital/api/denda.php
Method: POST
Headers: Content-Type: application/json
Body (raw JSON):
{"id_denda": 1, "metode": "E-Wallet"}
```

**POST — Login:**
```
URL: http://localhost/sisbad/database_perpustakaan_digital/api/auth.php?action=login
Method: POST
Headers: Content-Type: application/json
Body (raw JSON):
{"username": "rafif", "password": "123"}
```

---

## 5. Troubleshooting

| Masalah | Penyebab | Solusi |
|---------|----------|--------|
| "Koneksi gagal" | MySQL belum jalan | Start MySQL di XAMPP |
| 404 Not Found | Path salah | Pastikan folder ada di `htdocs` |
| JSON kosong | Database belum diisi | Jalankan dml.sql |
| "Buku tidak ditemukan" di API | ID buku salah | Cek ID di phpMyAdmin tabel `books` |
| CORS error di browser | Beda port/domain | Pastikan akses via `localhost` (bukan file://) |

---

## 6. Struktur Folder (Final)

```
database_perpustakaan_digital/
├── config/               ← Koneksi database
├── auth/                 ← Session & role check
├── includes/             ← Header, sidebar, footer, audit_helper
├── admin/                ← Halaman role Admin
├── staff/                ← Halaman role Staff
├── pembaca/              ← Halaman role Pembaca
├── api/                  ← API endpoints (interface)
│   ├── config.php, auth.php, buku.php
│   ├── anggota.php, peminjaman.php
│   └── denda.php, laporan.php
├── old/                  ← Halaman test HTML
│   ├── index.html, pinjam.html
│   ├── denda.html, laporan.html
├── sql/                  ← Semua file SQL (implementasi)
│   ├── ddl.sql, dml.sql, function.sql
│   ├── trigger.sql, procedure.sql
│   ├── reporting.sql, backup.sql
│   └── procedures/       ← Stored procedure (per file, tanpa DELIMITER)
├── docs/                 ← Dokumentasi lengkap
│   ├── DATABASE.md, SETUP.md, MODUL.md
│   ├── PERMISSION_MATRIX.md, STORED_PROCEDURES.md
│   ├── CODE_EXPLANATION.md, TESTING.md   ← Prosedur ini
│   ├── proposal.md, plan.md
├── assets/img/
├── AGENTS.md
└── README.md
```
