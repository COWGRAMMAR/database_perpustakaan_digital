# Dokumentasi Stored Procedure

> **PENTING:** Stored procedure di bawah ini sudah dibuat sendiri (lihat `sql/procedures/`). Jangan dibuat ulang/diganti versi lain — gunakan apa adanya.

## Daftar Stored Procedure

### 1. `sp_pinjam_buku(p_user_id INT, p_book_id INT)`
- Mengecek apakah buku ada, dan apakah user masih punya peminjaman aktif (`Dipinjam`/`Terlambat`) untuk buku tersebut (row lock via `FOR UPDATE`)
- Jika aman → INSERT ke `borrowings` (status `'Dipinjam'`, `due_date` otomatis terisi lewat `trg_set_due_date`)
- Pakai `START TRANSACTION` + `COMMIT`/`ROLLBACK`
- Dipakai di: `staff/borrowings.php`

### 2. `sp_bayar_denda(p_fine_id INT, p_payment_method VARCHAR(20))`
- Validasi `fine_id` ada dan statusnya `'Belum bayar'`
- UPDATE `fines.fine_status` → `'Lunas'`, lalu INSERT ke `payments`
- TCL: `START TRANSACTION` / `COMMIT` / `ROLLBACK` via `EXIT HANDLER FOR SQLEXCEPTION`
- Dipakai di: `staff/fines.php`

### 3. `sp_laporan_bulanan(p_bulan INT, p_tahun INT)`
- Return **4 result set sekaligus** dalam satu pemanggilan:
  1. Total peminjaman bulan tersebut
  2. Total denda masuk (dari `payments`)
  3. Jumlah member baru
  4. Buku paling sering dipinjam bulan itu
- Dipakai di: `admin/laporan.php` (tab "Ringkasan Bulanan")

### 4. `sp_proses_keterlambatan()`
- Pakai **CURSOR** (`cur_cek_keterlambatan`) untuk iterasi semua `borrowings` dengan `due_date < CURDATE()` dan `status = 'Dipinjam'`
- Update status jadi `'Terlambat'` per baris → trigger `trg_auto_fine` otomatis jalan saat update berikutnya menyertakan `return_date`
- Dipanggil otomatis di awal script `staff/borrowings.php` setiap kali halaman dibuka

## Pattern Pemanggilan SP via mysqli

Semua SP di atas pakai `EXIT HANDLER FOR SQLEXCEPTION` yang mengembalikan **result set berisi kolom `hasil`** (bukan exception mysqli biasa). Maka:

```php
$stmt = $conn->prepare("CALL sp_pinjam_buku(?, ?)");
$stmt->bind_param('ii', $userId, $bookId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$hasil = $row['hasil'] ?? 'Terjadi kesalahan.';
$stmt->close();

// WAJIB: flush sisa result set, atau query berikutnya error "Commands out of sync"
while ($conn->more_results() && $conn->next_result()) { /* flush */ }

if (str_contains($hasil, 'berhasil')) {
    // sukses
} else {
    // gagal — tampilkan $hasil sebagai pesan error
}
```

**Cek sukses/gagal pakai string `"berhasil"` di kolom `hasil`, BUKAN `$conn->error`.**

### Pattern khusus untuk `sp_laporan_bulanan` (4 result set)

```php
$stmt = $conn->prepare("CALL sp_laporan_bulanan(?, ?)");
$stmt->bind_param('ii', $bulan, $tahun);
$stmt->execute();

$laporanResults = [];
do {
    if ($res = $stmt->get_result()) {
        $laporanResults[] = $res->fetch_all(MYSQLI_ASSOC);
    }
} while ($conn->more_results() && $conn->next_result());

$stmt->close();
// $laporanResults[0] = total peminjaman, [1] = total denda, [2] = member baru, [3] = buku terpopuler
```
