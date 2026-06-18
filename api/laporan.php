<?php
// ============================================================
// API: Laporan
// Endpoint: /api/laporan.php
// Method: GET
//   GET /api/laporan.php?bulan=6&tahun=2026   → Laporan bulanan
//   GET /api/laporan.php?jenis=buku_terlaris   → Top 10 buku terlaris
//   GET /api/laporan.php?jenis=anggota_teraktif → 10 anggota paling aktif
// ============================================================

require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method tidak diizinkan']);
    exit;
}

$jenis = $_GET['jenis'] ?? 'bulanan';

if ($jenis === 'bulanan') {
    // ── Panggil sp_laporan_bulanan ──
    $bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : intval(date('m'));
    $tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : intval(date('Y'));

    $sql = "CALL sp_laporan_bulanan($bulan, $tahun)";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => mysqli_error($conn)]);
        exit;
    }

    $output = [];
    do {
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        if (count($rows) > 0) {
            $output[] = $rows;
        }
    } while (mysqli_next_result($conn));

    echo json_encode([
        'status' => 'sukses',
        'periode' => "$bulan/$tahun",
        'data' => $output
    ]);

} elseif ($jenis === 'buku_terlaris') {
    // ── Top 10 Buku Terlaris ──
    $sql = "SELECT bk.title, COUNT(b.id) AS total_dipinjam
            FROM borrowings b
            JOIN books bk ON b.book_id = bk.id
            GROUP BY b.book_id, bk.title
            ORDER BY total_dipinjam DESC
            LIMIT 10";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengambil data']);
        exit;
    }

    $list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }

    echo json_encode(['status' => 'sukses', 'data' => $list]);

} elseif ($jenis === 'anggota_teraktif') {
    // ── 10 Anggota Paling Aktif ──
    $sql = "SELECT u.id, u.username, mp.full_name, COUNT(b.id) AS total_pinjam
            FROM borrowings b
            JOIN users u ON b.user_id = u.id
            JOIN member_profiles mp ON u.id = mp.user_id
            GROUP BY u.id, u.username, mp.full_name
            ORDER BY total_pinjam DESC
            LIMIT 10";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengambil data']);
        exit;
    }

    $list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }

    echo json_encode(['status' => 'sukses', 'data' => $list]);

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Jenis laporan tidak dikenal. Pilihan: bulanan, buku_terlaris, anggota_teraktif']);
}
?>
