# ============================================
# RESTORE DATABASE — Perpustakaan Digital
# Pilih file backup -> DROP + restore + verify
# ============================================

# ─── KONFIGURASI ────────────────────────────
$DB_NAME     = 'perpustakaan_digital'
$MYSQL_USER  = 'root'
$MYSQL_PASS  = ''
$MYSQL_HOST  = 'localhost'
# ────────────────────────────────────────────

# ─── Auto-detect mysql client ───────────────
function Find-MySQL {
    $exe = (Get-Command 'mysql' -ErrorAction SilentlyContinue).Source
    if ($exe) { return $exe }

    $paths = @(
        "$env:ProgramFiles\MySQL\MySQL Server 8.0\bin\mysql.exe"
        "$env:ProgramFiles\MySQL\MySQL Server 5.7\bin\mysql.exe"
        "${env:ProgramFiles(x86)}\MySQL\MySQL Server 8.0\bin\mysql.exe"
        "${env:ProgramFiles(x86)}\MySQL\MySQL Server 5.7\bin\mysql.exe"
        "$env:ProgramFiles\MariaDB 10.11\bin\mysql.exe"
        "$env:ProgramFiles\MariaDB 10.6\bin\mysql.exe"
        "C:\xampp\mysql\bin\mysql.exe"
        "D:\xampp\mysql\bin\mysql.exe"
        "E:\xampp\mysql\bin\mysql.exe"
        "C:\laragon\bin\mysql\mysql-8.0\bin\mysql.exe"
    )

    foreach ($p in $paths) {
        if (Test-Path $p) { return $p }
    }

    return $null
}

$MYSQL = Find-MySQL
if (-not $MYSQL) {
    Write-Host "`n[ERROR] mysql client tidak ditemukan!" -ForegroundColor Red
    Write-Host "Coba cari lokasi mysql.exe, lalu install MySQL/XAMPP.`n" -ForegroundColor Yellow
    pause
    exit 1
}
# ────────────────────────────────────────────

$SCRIPT_DIR      = Split-Path -Parent $PSCommandPath
$BACKUP_DIR      = Join-Path (Split-Path -Parent $SCRIPT_DIR) 'backup_file'
$VERIFY_SQL      = Join-Path $SCRIPT_DIR 'verify.sql'

# ─── Functions ──────────────────────────────
function Get-MySQLArgs {
    param([string[]]$Extra)
    $argsList = @("-h$MYSQL_HOST", "-u$MYSQL_USER")
    if ($MYSQL_PASS) { $argsList += "-p$MYSQL_PASS" }
    foreach ($a in $Extra) { $argsList += $a }
    return $argsList
}

function Invoke-MySQL {
    param([string]$Query)
    $a = Get-MySQLArgs -Extra @("-e", $Query)
    & $MYSQL @a 2>$null
    return $LASTEXITCODE
}
# ────────────────────────────────────────────

Clear-Host
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  RESTORE DATABASE: $DB_NAME" -ForegroundColor Cyan
Write-Host "  Host: $MYSQL_HOST" -ForegroundColor Cyan
Write-Host "  Tool: $MYSQL" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# ─── Cek koneksi MySQL ──────────────────────
$a = Get-MySQLArgs -Extra @("-e", "SELECT 1 AS test")
Write-Host "Mencoba konek: $MYSQL -h$MYSQL_HOST -u$MYSQL_USER ..." -ForegroundColor Gray
& $MYSQL @a 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Tidak bisa konek ke MySQL. Pastikan server MySQL/XAMPP sudah jalan." -ForegroundColor Red
    Write-Host "       Coba buka XAMPP Control Panel -> start MySQL." -ForegroundColor Yellow
    pause
    exit 1
}

# ─── List file backup ───────────────────────
$FILES = @(Get-ChildItem (Join-Path $BACKUP_DIR "backup_${DB_NAME}_*.sql") | Sort-Object LastWriteTime -Descending)

if ($FILES.Count -eq 0) {
    Write-Host "[TIDAK ADA] Tidak ada file backup di folder ini.`n" -ForegroundColor Red
    Write-Host "Pastikan file backup_${DB_NAME}_*.sql ada di folder:" -ForegroundColor Yellow
    Write-Host $BACKUP_DIR
    pause
    exit 1
}

Write-Host "File backup yang tersedia:`n"
for ($i = 0; $i -lt $FILES.Count; $i++) {
    $SIZE_STR = if ($FILES[$i].Length -ge 1MB) {
        "{0:N2} MB" -f ($FILES[$i].Length / 1MB)
    } else {
        "{0:N2} KB" -f ($FILES[$i].Length / 1KB)
    }
    Write-Host ("[{0}] {1}  ({2}, {3})" -f ($i + 1), $FILES[$i].Name, $SIZE_STR, $FILES[$i].LastWriteTime.ToString("dd-MMM-yyyy HH:mm"))
}

# ─── Pilih file ─────────────────────────────
Write-Host ""
$PILIHAN = Read-Host "Masukkan nomor (1-$($FILES.Count))"

$FILEPATH = $null
# Cek apakah input angka valid
if ($PILIHAN -match '^\d+$') {
    $IDX = [int]$PILIHAN - 1
    if ($IDX -ge 0 -and $IDX -lt $FILES.Count) {
        $FILEPATH = $FILES[$IDX].FullName
    }
} else {
    # Coba cari berdasarkan nama file
    $MATCH = $FILES | Where-Object { $_.Name -eq $PILIHAN }
    if ($MATCH) { $FILEPATH = $MATCH.FullName }
}

if (-not $FILEPATH) {
    Write-Host "[ERROR] Pilihan tidak valid." -ForegroundColor Red
    pause
    exit 1
}

Write-Host "`nFile dipilih: $(Split-Path $FILEPATH -Leaf)" -ForegroundColor Yellow

# ─── Konfirmasi ─────────────────────────────
Write-Host "`nPERINGATAN: Semua data di database '$DB_NAME' akan DIHAPUS!" -ForegroundColor Red
$YAKIN = Read-Host "Yakin lanjut restore? (y/n)"
if ($YAKIN -ne 'y' -and $YAKIN -ne 'Y') {
    Write-Host "`n[BATAL] Restore dibatalkan." -ForegroundColor Yellow
    pause
    exit 1
}

Write-Host ""

# ─── Step 1: Drop ──────────────────────────
Write-Host "[1/4] Drop database lama..." -NoNewline
$DROP = Invoke-MySQL -Query "DROP DATABASE IF EXISTS $DB_NAME"
if ($LASTEXITCODE -eq 0) {
    Write-Host " OK" -ForegroundColor Green
} else {
    Write-Host " ERROR" -ForegroundColor Red
    pause
    exit 1
}

# ─── Step 2: Restore ───────────────────────
Write-Host "[2/4] Restore dari file backup..." -NoNewline
$a = Get-MySQLArgs
Get-Content $FILEPATH -Raw | & $MYSQL @a 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host " OK" -ForegroundColor Green
} else {
    Write-Host " ERROR" -ForegroundColor Red
    pause
    exit 1
}

# ─── Step 3: Verifikasi ────────────────────
Write-Host "[3/4] Verifikasi restore..." -NoNewline
if (Test-Path $VERIFY_SQL) {
    $a = Get-MySQLArgs -Extra @($DB_NAME, "-t")
    Get-Content $VERIFY_SQL -Raw | & $MYSQL @a 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host " OK" -ForegroundColor Green
    } else {
        Write-Host " WARNING" -ForegroundColor Yellow
    }
} else {
    Write-Host " SKIP (verify.sql tidak ditemukan)" -ForegroundColor Yellow
}

# ─── Step 4: Selesai ────────────────────────
Write-Host "`n[4/4] Selesai." -ForegroundColor Green
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  RESTORE SELESAI" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Database $DB_NAME berhasil diretore dari:" -ForegroundColor Green
Write-Host $FILEPATH
Write-Host ""
pause
