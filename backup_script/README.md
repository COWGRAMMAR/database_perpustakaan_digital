# Tutorial Backup & Restore Database

Script untuk **backup** dan **restore** database `perpustakaan_digital`.

> **Struktur folder:**
> ```
> backup_script/   ← script backup/restore (.ps1, .bat, verify.sql, README)
> backup_file/     ← hasil generate file backup (.sql) — isinya otomatis
> ```
>
> **Butuh apa?** Cuma double click file `.bat` di `backup_script/` — MySQL XAMPP harus jalan.

---

## Daftar Isi

- [Cara Backup Database](#cara-backup-database)
- [Cara Restore Database](#cara-restore-database)
- [Verifikasi Restore](#verifikasi-restore)
- [Yang Dibackup](#yang-dibackup)
- [Troubleshooting](#troubleshooting)

---

## Cara Backup Database

1. **Pastikan MySQL XAMPP jalan** (buka XAMPP Control Panel → Start MySQL).
2. **Double click** file `backup.bat`.
3. Tunggu sampai muncul tulisan `[SUKSES] Backup selesai!`.
4. File backup akan tersimpan di folder `backup_file/` (satu level di atas) dengan format:
   ```
   backup_perpustakaan_digital_20260629_143000.sql
   ```
   (artinya: backup tanggal 29 Juni 2026 jam 14:30:00)

Gampang tinggal klik doang.

> **Catatan:** Script otomatis mendeteksi lokasi `mysqldump.exe` — dari PATH, XAMPP, MySQL Server, Laragon, atau MariaDB. Jadi gak perlu konfigurasi manual.

---

## Cara Restore Database

> **Peringatan:** Restore akan **MENGHAPUS** semua data yang ada di database `perpustakaan_digital` saat ini, lalu menggantinya dengan data dari file backup.

1. **Pastikan MySQL XAMPP jalan**.
2. **Double click** file `restore.bat`.
3. Script otomatis cari file backup di folder `backup_file/`, lalu tampilkan daftarnya:

   ```
   [1] backup_perpustakaan_digital_20260629_140000.sql  (1.23 MB, 29-Jun-2026 14:00)
   [2] backup_perpustakaan_digital_20260629_143000.sql  (1.25 MB, 29-Jun-2026 14:30)
   ```

4. **Ketik nomor** file backup yang mau diretore, lalu Enter.
5. Konfirmasi dengan mengetik `y` lalu Enter.
6. Proses: Drop DB → Restore → Verifikasi otomatis.
7. Kalau sukses, muncul tabel verifikasi.

---

## Verifikasi Restore

Setelah restore, script `restore.ps1` otomatis menjalankan `verify.sql`, yang mengecek:

| Yang Dicek               | Contoh Output                    |
| ------------------------ | -------------------------------- |
| Jumlah tabel             | `20`                             |
| Jumlah data per tabel    | `books: 15`, `users: 12`, ...    |
| Trigger masih ada        | `trg_set_due_date`, `trg_auto_fine` |
| Stored Procedure         | `4`                              |
| Function                 | `2` (`fn_hitung_denda`, `fn_avg_rating`) |

Kalau pengen verifikasi manual, bisa jalankan:

```sql
-- Di phpMyAdmin atau MySQL CLI
source D:/path/ke/backup/verify.sql;
```

Atau copy paste isi `verify.sql` ke SQL tab phpMyAdmin.

---

## Yang Dibackup

Perintah `mysqldump` yang dipakai:

```
mysqldump --routines --triggers --databases perpustakaan_digital
```

Artinya semua ini kebackup:

| Komponen              | Keterangan                          |
| --------------------- | ----------------------------------- |
| Semua tabel           | 20 tabel dari `ddl.sql`             |
| Data di setiap tabel  | Data dari `dml.sql` + data transaksi |
| Stored Procedure      | `sp_pinjam_buku`, `sp_bayar_denda`, `sp_laporan_bulanan`, `sp_proses_keterlambatan` |
| Trigger               | `trg_set_due_date`, `trg_auto_fine` |
| Function              | `fn_hitung_denda`, `fn_avg_rating`  |

**Tidak dibackup:** user MySQL (grant/privilege). Di XAMPP pake `root` tanpa password jadi gak masalah.

---

## Troubleshooting

### `mysqldump` / mysql client tidak ditemukan

Script otomatis mencari di path umum. Kalau tetap tidak ketemu:

1. Buka XAMPP Control Panel -> cari tau dimana MySQL terinstall.
2. Buka `backup.ps1` atau `restore.ps1` dengan Notepad.
3. Cari bagian konfigurasi di baris atas:
   ```powershell
   $MYSQL_USER  = 'root'
   $MYSQL_PASS  = ''
   $MYSQL_HOST  = 'localhost'
   ```
4. Kalau MySQL butuh password, isi `$MYSQL_PASS`.

> Alternatif: pastikan folder `C:\xampp\mysql\bin` ada di PATH environment variable.

### Access denied for user

Buka file `.ps1`, isi `$MYSQL_PASS` dengan password MySQL kamu:

```powershell
$MYSQL_PASS = 'root'
```

### MySQL server not running

Buka XAMPP Control Panel → klik **Start** di baris MySQL.

### Tidak ada file backup yang muncul

Pastikan file backup ada di folder `backup_file/` (satu folder di atas `backup_script/`). Format nama file harus:

```
backup_perpustakaan_digital_20260629_*.sql
```

### Error `ExecutionPolicy` di PowerShell

Kalau muncul error soal kebijakan eksekusi PowerShell, jalankan ini sekali:

```powershell
Set-ExecutionPolicy -Scope CurrentUser RemoteSigned
```

Atau alternatifnya, klik kanan `backup.bat` → **Run as Administrator**.

---

## Tips

- **Backup rutin** sebelum uji coba fitur baru, biar gampang rollback.
- File backup timestamp jadi **gak bakal ketimpa** — backup pagi, siang, sore, semua ke-save.
- File backup ada di folder `backup_file/` — tinggal copas aja kalo mau dipindah.
- File backup bisa langsung di-restore di laptop temen: tinggal copas file `.sql` ke `backup_file/` mereka, lalu jalankan `restore.bat`.
- Script `.ps1` otomatis mendeteksi lokasi MySQL — gak perlu setting PATH manual.
