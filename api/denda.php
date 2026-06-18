<?php
// ============================================================
// API: Denda & Pembayaran
// Endpoint: /api/denda.php
// Method: GET
//   GET /api/denda.php?id_user=5    → Cek denda user
//
// Method: POST
//   POST /api/denda.php             → Bayar denda (panggil sp_bayar_denda)
//   Body: { "id_denda": 5, "metode": "E-Wallet" }
// ============================================================

require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // ── Cek Denda ──
    $id_user = isset($_GET['id_user']) ? intval($_GET['id_user']) : 0;

    if ($id_user <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Parameter id_user harus diisi']);
        exit;
    }

    $sql = "SELECT
                f.id AS fine_id, bk.title AS judul_buku,
                f.amount, f.fine_status,
                b.borrow_date, b.due_date
            FROM fines f
            JOIN borrowings b ON f.borrowing_id = b.id
            JOIN books bk ON b.book_id = bk.id
            WHERE b.user_id = $id_user AND f.fine_status = 'Belum bayar'
            ORDER BY b.due_date ASC";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengambil data denda']);
        exit;
    }

    $denda_list = [];
    $total_denda = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $denda_list[] = $row;
        $total_denda += floatval($row['amount']);
    }

    echo json_encode([
        'status' => 'sukses',
        'data' => [
            'total_denda' => $total_denda,
            'jumlah_item' => count($denda_list),
            'daftar_denda' => $denda_list
        ]
    ]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ── Bayar Denda ──
    $input = json_decode(file_get_contents('php://input'), true);
    $id_denda = intval($input['id_denda'] ?? 0);
    $metode = $input['metode'] ?? 'E-Wallet';

    if ($id_denda <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Parameter id_denda harus diisi']);
        exit;
    }

    // Validasi metode pembayaran
    if (!in_array($metode, ['E-Wallet', 'Bank Transfer'])) {
        $metode = 'E-Wallet';
    }

    // Panggil stored procedure
    $sql = "CALL sp_bayar_denda($id_denda, '$metode')";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(400);
        echo json_encode(['error' => mysqli_error($conn)]);
        exit;
    }

    $row = mysqli_fetch_assoc($result);
    echo json_encode(['status' => 'sukses', 'data' => $row]);

    while (mysqli_next_result($conn)) {;}

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method tidak diizinkan']);
}
?>
