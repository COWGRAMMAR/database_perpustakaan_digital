<?php
// ============================================================
// API: Peminjaman Buku
// Endpoint: /api/peminjaman.php
// Method: POST
//   POST /api/peminjaman.php   → Pinjam buku (panggil sp_pinjam_buku)
//   Body: { "id_user": 5, "id_buku": 3 }
//
// Method: GET
//   GET /api/peminjaman.php?id_user=5   → Riwayat peminjaman user
// ============================================================

require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ── Pinjam Buku ──
    $input = json_decode(file_get_contents('php://input'), true);
    $id_user = intval($input['id_user'] ?? 0);
    $id_buku = intval($input['id_buku'] ?? 0);

    if ($id_user <= 0 || $id_buku <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Parameter id_user dan id_buku harus diisi']);
        exit;
    }

    // Panggil stored procedure
    $sql = "CALL sp_pinjam_buku($id_user, $id_buku)";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(400);
        echo json_encode(['error' => mysqli_error($conn)]);
        exit;
    }

    // Ambil hasil dari procedure
    $row = mysqli_fetch_assoc($result);
    echo json_encode(['status' => 'sukses', 'data' => $row]);

    // Bersihin buffer (kalo procedure return multiple result set)
    while (mysqli_next_result($conn)) {;}

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // ── Riwayat Peminjaman ──
    $id_user = isset($_GET['id_user']) ? intval($_GET['id_user']) : 0;

    if ($id_user <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Parameter id_user harus diisi']);
        exit;
    }

    $sql = "SELECT
                b.id, bk.title AS judul_buku,
                b.borrow_date, b.due_date, b.return_date, b.status,
                COALESCE(f.amount, 0) AS denda,
                f.fine_status AS status_denda
            FROM borrowings b
            JOIN books bk ON b.book_id = bk.id
            LEFT JOIN fines f ON b.id = f.borrowing_id
            WHERE b.user_id = $id_user
            ORDER BY b.borrow_date DESC
            LIMIT 20";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengambil riwayat peminjaman']);
        exit;
    }

    $list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $list[] = $row;
    }

    echo json_encode(['status' => 'sukses', 'data' => $list]);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method tidak diizinkan']);
}
?>
