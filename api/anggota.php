<?php
// ============================================================
// API: Anggota (Member)
// Endpoint: /api/anggota.php
// Method: GET
//   - GET /api/anggota.php          → List semua anggota
//   - GET /api/anggota.php?id=5     → Detail anggota + status peminjaman
// ============================================================

require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method tidak diizinkan']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // ── Detail anggota ──
    $sql = "SELECT
                u.id, u.username, u.email, u.is_active, u.created_at,
                mp.full_name, mp.member_number, mp.address, mp.phone_number, mp.membership_type
            FROM users u
            JOIN member_profiles mp ON u.id = mp.user_id
            WHERE u.id = $id";

    $result = mysqli_query($conn, $sql);

    if (!$result || mysqli_num_rows($result) === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Anggota tidak ditemukan']);
        exit;
    }

    $anggota = mysqli_fetch_assoc($result);

    // Ambil peminjaman aktif
    $sql_pinjam = "SELECT
                    b.id AS borrowing_id, bk.title AS judul_buku,
                    br.borrow_date, br.due_date, br.status
                   FROM borrowings b
                   JOIN books bk ON b.book_id = bk.id
                   WHERE b.user_id = $id
                   ORDER BY b.borrow_date DESC
                   LIMIT 10";

    $res_pinjam = mysqli_query($conn, $sql_pinjam);
    $peminjaman = [];
    while ($row = mysqli_fetch_assoc($res_pinjam)) {
        $peminjaman[] = $row;
    }

    $anggota['riwayat_peminjaman'] = $peminjaman;

    echo json_encode(['status' => 'sukses', 'data' => $anggota]);
} else {
    // ── List semua anggota ──
    $sql = "SELECT
                u.id, u.username, u.email, u.is_active,
                mp.full_name, mp.member_number, mp.membership_type
            FROM users u
            JOIN member_profiles mp ON u.id = mp.user_id
            ORDER BY mp.full_name ASC";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengambil data anggota']);
        exit;
    }

    $anggota_list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $anggota_list[] = $row;
    }

    echo json_encode(['status' => 'sukses', 'data' => $anggota_list]);
}
?>
