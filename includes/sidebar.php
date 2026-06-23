<?php
$currentRole = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);
$base = getBasePath(); 

function isActive($page, $currentPage) {
    return $page === $currentPage
        ? 'bg-indigo-600 text-white'
        : 'text-gray-200 hover:bg-indigo-700 hover:text-white';
}

$menus = [];

if ($currentRole === 'Admin') {
    $menus = [
        ['label' => 'Dashboard',         'file' => 'dashboard.php',    'href' => $base . 'admin/dashboard.php'],
        ['label' => 'Manajemen User',    'file' => 'users.php',        'href' => $base . 'admin/users.php'],
        ['label' => 'Manajemen Staff',   'file' => 'staff.php',        'href' => $base . 'admin/staff.php'],
        ['label' => 'Data Buku',         'file' => 'books.php',        'href' => $base . 'admin/books.php'],
        ['label' => 'Master Data Buku',  'file' => 'master_data.php',  'href' => $base . 'admin/master_data.php'],
        ['label' => 'Peminjaman',        'file' => 'borrowings.php',   'href' => $base . 'admin/borrowings.php'],
        ['label' => 'Denda & Bayar',     'file' => 'fines.php',        'href' => $base . 'admin/fines.php'],
        ['label' => 'Laporan',           'file' => 'laporan.php',      'href' => $base . 'admin/laporan.php'],
        ['label' => 'Audit Log',         'file' => 'audit_log.php',    'href' => $base . 'admin/audit_log.php'],
    ];
} elseif ($currentRole === 'Staff') {
    $menus = [
        ['label' => 'Dashboard',         'file' => 'dashboard.php',    'href' => $base . 'staff/dashboard.php'],
        ['label' => 'Data Buku',         'file' => 'books.php',        'href' => $base . 'staff/books.php'],
        ['label' => 'Master Data Buku',  'file' => 'master_data.php',  'href' => $base . 'staff/master_data.php'],
        ['label' => 'Peminjaman',        'file' => 'borrowings.php',   'href' => $base . 'staff/borrowings.php'],
        ['label' => 'Denda & Bayar',     'file' => 'fines.php',        'href' => $base . 'staff/fines.php'],
        ['label' => 'Data Pembaca',      'file' => 'members.php',      'href' => $base . 'staff/members.php'],
        ['label' => 'Audit System',      'file' => 'audit_log.php',    'href' => $base . 'staff/audit_log.php'],
    ];
} elseif ($currentRole === 'Pembaca') {
    $menus = [
        ['label' => 'Dashboard',          'file' => 'dashboard.php',        'href' => $base . 'pembaca/dashboard.php'],
        ['label' => 'Katalog Buku',       'file' => 'catalog.php',          'href' => $base . 'pembaca/catalog.php'],
        ['label' => 'Riwayat Membaca',    'file' => 'reading_history.php',  'href' => $base . 'pembaca/reading_history.php'],
        ['label' => 'Wishlist',           'file' => 'wishlist.php',         'href' => $base . 'pembaca/wishlist.php'],
        ['label' => 'Peminjaman Saya',    'file' => 'borrowings.php',       'href' => $base . 'pembaca/borrowings.php'],
        ['label' => 'Denda & Bayar',      'file' => 'fines.php',            'href' => $base . 'pembaca/fines.php'],
    ];
}
?>

<aside class="w-64 min-h-screen bg-indigo-800 flex-shrink-0">
    <div class="px-4 py-3 border-b border-indigo-700">
        <p class="text-indigo-200 text-xs uppercase tracking-wide">Menu <?= htmlspecialchars($currentRole) ?></p>
    </div>
    <nav class="mt-2">
        <ul>
            <?php foreach ($menus as $menu): ?>
            <li>
                <a href="<?= $menu['href'] ?>"
                   class="flex items-center px-4 py-2.5 text-sm font-medium transition <?= isActive($menu['file'], $currentPage) ?>">
                    <?= htmlspecialchars($menu['label']) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>