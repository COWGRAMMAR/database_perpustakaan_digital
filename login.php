<?php
// login.php
require_once 'auth/check_session.php';
require_once 'config/database.php';

// Kalau sudah login, langsung lempar ke dashboard masing-masing
if (isset($_SESSION['user_id'])) {
    redirectToDashboard();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $hashedPassword = md5($password);

        // Ambil data user + role-nya
        $sql = "SELECT u.id, u.username, u.password, u.is_active, r.role_name
                FROM users u
                JOIN user_roles ur ON ur.user_id = u.id
                JOIN roles r ON r.id = ur.role_id
                WHERE u.username = ?
                LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if (!$user) {
            $error = 'Username tidak ditemukan.';
        } elseif ($user['password'] !== $hashedPassword) {
            $error = 'Password salah.';
        } elseif ((int)$user['is_active'] === 0) {
            $error = 'Akun Anda tidak aktif. Hubungi admin.';
        } else {
            // Login berhasil, set session
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role_name'];

            redirectToDashboard();
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white w-full max-w-sm p-8 rounded-xl shadow-md">
        <h1 class="text-2xl font-bold text-gray-800 mb-1 text-center">Perpustakaan Digital</h1>
        <p class="text-gray-500 text-sm mb-6 text-center">Masuk ke akun Anda</p>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 text-sm px-4 py-2 rounded-md mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-md transition">
                Masuk
            </button>
        </form>

        <p class="text-sm text-gray-500 text-center mt-6">
            Belum punya akun?
            <a href="signup.php" class="text-blue-600 hover:underline">Daftar di sini</a>
        </p>
    </div>

</body>
</html>
