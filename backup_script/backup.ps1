# ============================================
# BACKUP DATABASE — Perpustakaan Digital
# Output: backup_perpustakaan_digital_YYYYMMDD_HHmmss.sql
# ============================================

# ─── KONFIGURASI ────────────────────────────
$DB_NAME     = 'perpustakaan_digital'
$MYSQL_USER  = 'root'
$MYSQL_PASS  = ''
$MYSQL_HOST  = 'localhost'
# ────────────────────────────────────────────

# ─── Auto-detect mysqldump ──────────────────
function Find-MySQLDump {
    # Coba dari PATH dulu
    $exe = (Get-Command 'mysqldump' -ErrorAction SilentlyContinue).Source
    if ($exe) { return $exe }

    # Common install paths
    $paths = @(
        "$env:ProgramFiles\MySQL\MySQL Server 8.0\bin\mysqldump.exe"
        "$env:ProgramFiles\MySQL\MySQL Server 5.7\bin\mysqldump.exe"
        "${env:ProgramFiles(x86)}\MySQL\MySQL Server 8.0\bin\mysqldump.exe"
        "${env:ProgramFiles(x86)}\MySQL\MySQL Server 5.7\bin\mysqldump.exe"
        "$env:ProgramFiles\MariaDB 10.11\bin\mysqldump.exe"
        "$env:ProgramFiles\MariaDB 10.6\bin\mysqldump.exe"
        "C:\xampp\mysql\bin\mysqldump.exe"
        "D:\xampp\mysql\bin\mysqldump.exe"
        "E:\xampp\mysql\bin\mysqldump.exe"
        "C:\laragon\bin\mysql\mysql-8.0\bin\mysqldump.exe"
    )

    foreach ($p in $paths) {
        if (Test-Path $p) { return $p }
    }

    return $null
}

$MYSQLDUMP = Find-MySQLDump
if (-not $MYSQLDUMP) {
    Write-Host "`n[ERROR] mysqldump tidak ditemukan!" -ForegroundColor Red
    Write-Host "Coba cari lokasi mysqldump.exe, lalu edit variable di bagian atas script ini." -ForegroundColor Yellow
    Write-Host "Atau install MySQL/XAMPP dan pastikan PATH-nya terdaftar.`n" -ForegroundColor Yellow
    pause
    exit 1
}
# ────────────────────────────────────────────

$SCRIPT_DIR    = Split-Path -Parent $PSCommandPath
$BACKUP_DIR    = Join-Path (Split-Path -Parent $SCRIPT_DIR) 'backup_file'
$TIMESTAMP     = Get-Date -Format 'yyyyMMdd_HHmmss'
$FILENAME      = Join-Path $BACKUP_DIR "backup_${DB_NAME}_${TIMESTAMP}.sql"

# Auto-create backup_file/ folder kalo belum ada
if (-not (Test-Path $BACKUP_DIR)) {
    New-Item -ItemType Directory -Path $BACKUP_DIR -Force | Out-Null
}

Clear-Host
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  BACKUP DATABASE: $DB_NAME" -ForegroundColor Cyan
Write-Host "  Host: $MYSQL_HOST" -ForegroundColor Cyan
Write-Host "  Tool: $MYSQLDUMP" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Build password arg
$PASS_ARG = if ($MYSQL_PASS) { "-p$MYSQL_PASS" } else { '' }

Write-Host "Memproses backup..." -ForegroundColor Yellow

$ARGUMENTS = @(
    "-h$MYSQL_HOST"
    "-u$MYSQL_USER"
    $PASS_ARG
    '--routines'
    '--triggers'
    '--databases'
    $DB_NAME
)

# Redirect stderr to null (mysqldump prints warnings to stderr)
$OUTPUT = & $MYSQLDUMP $ARGUMENTS 2>$null

if ($LASTEXITCODE -eq 0 -and $OUTPUT) {
    $OUTPUT | Out-File -FilePath $FILENAME -Encoding utf8
    $FILESIZE = (Get-Item $FILENAME).Length
    Write-Host "`n[SUKSES] Backup selesai!" -ForegroundColor Green
    Write-Host "File   : $FILENAME"
    if ($FILESIZE -ge 1MB) {
        Write-Host ("Ukuran : {0:N2} MB" -f ($FILESIZE / 1MB))
    } else {
        Write-Host ("Ukuran : {0:N2} KB" -f ($FILESIZE / 1KB))
    }
} else {
    Write-Host "`n[GAGAL] Backup error!" -ForegroundColor Red
    Write-Host "`nKemungkinan penyebab:"
    Write-Host "1. MySQL server tidak jalan"
    Write-Host "2. Username/password salah"
    Write-Host "3. mysqldump tidak kompatibel"
    Write-Host "`nCoba jalankan XAMPP Control Panel -> Start MySQL.`n"
}

Write-Host ""
pause
