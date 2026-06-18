<?php
// ============================================================
// API: Auth (Login/Logout)
// Endpoint: /api/auth.php
// Method: POST
//   POST /api/auth.php?action=login   → Login
//   Body: { "username": "rafif", "password": "123" }
//
//   POST /api/auth.php?action=logout  → Logout
// Method: GET
//   GET /api/auth.php?action=cek      → Cek session
// ============================================================

require_once 'config.php';
header('Content-Type: application/json');

// Start session untuk auth
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method tidak diizinkan']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Username dan password harus diisi']);
        exit;
    }

    // Cari user
    $sql = "SELECT id, username, email, password FROM users WHERE username = '$username' AND is_active = 1";
    $result = mysqli_query($conn, $sql);

    if (!$result || mysqli_num_rows($result) === 0) {
        http_response_code(401);
        echo json_encode(['error' => 'Username tidak ditemukan']);
        exit;
    }

    $user = mysqli_fetch_assoc($result);

    // Cek password (support MD5 untuk dummy data & password_hash untuk PHP)
    if ($user['password'] === md5($password) || password_verify($password, $user['password'])) {
        // Login sukses
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Ambil role
        $sql_role = "SELECT r.role_name FROM user_roles ur
                     JOIN roles r ON ur.role_id = r.id
                     WHERE ur.user_id = {$user['id']}";
        $res_role = mysqli_query($conn, $sql_role);
        $roles = [];
        while ($row = mysqli_fetch_assoc($res_role)) {
            $roles[] = $row['role_name'];
        }

        echo json_encode([
            'status' => 'sukses',
            'data' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'roles' => $roles
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Password salah']);
    }

} elseif ($action === 'logout') {
    // ── Logout ──
    session_destroy();
    echo json_encode(['status' => 'sukses', 'data' => 'Logout berhasil']);

} elseif ($action === 'cek') {
    // ── Cek Session ──
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'status' => 'sukses',
            'data' => [
                'logged_in' => true,
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username']
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'sukses',
            'data' => ['logged_in' => false]
        ]);
    }

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Action tidak dikenal. Pilihan: login, logout, cek']);
}
?>
