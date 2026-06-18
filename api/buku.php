<?php
// ============================================================
// API: Buku
// Endpoint: /api/buku.php
// Method: GET
//   - GET /api/buku.php         → List semua buku
//   - GET /api/buku.php?id=5    → Detail buku + rata-rata rating
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
    // ── Detail satu buku ──
    $sql = "SELECT
                b.id, b.title, b.isbn, b.publication_year,
                b.total_pages, b.synopsis,
                p.publisher_name,
                COALESCE(fn_avg_rating(b.id), 0) AS avg_rating,
                GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', ') AS authors,
                GROUP_CONCAT(DISTINCT c.category_name SEPARATOR ', ') AS categories
            FROM books b
            LEFT JOIN publishers p ON b.publisher_id = p.id
            LEFT JOIN book_authors ba ON b.id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.id
            LEFT JOIN book_categories bc ON b.id = bc.book_id
            LEFT JOIN categories c ON bc.category_id = c.id
            WHERE b.id = $id
            GROUP BY b.id";

    $result = mysqli_query($conn, $sql);

    if (!$result || mysqli_num_rows($result) === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Buku tidak ditemukan']);
        exit;
    }

    $buku = mysqli_fetch_assoc($result);
    echo json_encode(['status' => 'sukses', 'data' => $buku]);
} else {
    // ── List semua buku ──
    $sql = "SELECT
                b.id, b.title, b.isbn, b.publication_year,
                p.publisher_name,
                COALESCE(fn_avg_rating(b.id), 0) AS avg_rating,
                (SELECT COUNT(*) FROM borrowings br WHERE br.book_id = b.id) AS total_dipinjam
            FROM books b
            LEFT JOIN publishers p ON b.publisher_id = p.id
            ORDER BY b.title ASC";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal mengambil data buku']);
        exit;
    }

    $buku_list = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $buku_list[] = $row;
    }

    echo json_encode(['status' => 'sukses', 'data' => $buku_list]);
}
?>
