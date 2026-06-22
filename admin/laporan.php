<?php
require_once '../auth/check_session.php';
requireRole(['Admin']);
require_once '../config/database.php';

$pageTitle = 'Laporan';
$tab = $_GET['tab'] ?? 'terlaris';
?>

<?php require_once '../includes/header.php'; ?>

<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Laporan</h1>

    <div class="mb-5 flex flex-wrap gap-2">
        <?php
        $tabs = [
            'terlaris'   => 'Buku Terlaris',
            'peminjaman' => 'Peminjaman Per Periode',
            'denda'      => 'Denda Per Bulan',
            'member'     => 'Member Baru Per Bulan',
            'kategori'   => 'Statistik Per Kategori',
            'ringkasan'  => 'Ringkasan Bulanan (SP)',
        ];
        foreach ($tabs as $key => $label): ?>
            <a href="laporan.php?tab=<?= $key ?>"
               class="px-3 py-1 rounded text-sm <?= $tab===$key ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($tab === 'terlaris'): ?>

        <?php
        $sql = "SELECT bk.title AS judul, GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', ') AS penulis,
                       GROUP_CONCAT(DISTINCT c.category_name SEPARATOR ', ') AS kategori,
                       COUNT(br.id) AS total_dipinjam
                FROM borrowings br
                JOIN books bk ON br.book_id = bk.id
                LEFT JOIN book_authors ba ON ba.book_id = bk.id
                LEFT JOIN authors a ON a.id = ba.author_id
                LEFT JOIN book_categories bc ON bc.book_id = bk.id
                LEFT JOIN categories c ON c.id = bc.category_id
                GROUP BY bk.id, bk.title
                ORDER BY total_dipinjam DESC
                LIMIT 20";
        $res = $conn->query($sql);
        ?>
        <h2 class="font-semibold mb-2">Buku Terlaris (All Time)</h2>
        <table class="w-full bg-white shadow rounded text-sm">
            <thead class="bg-gray-100">
                <tr><th class="p-2 text-left">Judul</th><th class="p-2 text-left">Penulis</th><th class="p-2 text-left">Kategori</th><th class="p-2 text-right">Total Dipinjam</th></tr>
            </thead>
            <tbody>
                <?php while ($r = $res->fetch_assoc()): ?>
                <tr class="border-t">
                    <td class="p-2"><?= htmlspecialchars($r['judul']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($r['penulis'] ?? '-') ?></td>
                    <td class="p-2"><?= htmlspecialchars($r['kategori'] ?? '-') ?></td>
                    <td class="p-2 text-right"><?= $r['total_dipinjam'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php elseif ($tab === 'peminjaman'): ?>

        <?php
        $bulan = (int)($_GET['bulan'] ?? date('n'));
        $tahun = (int)($_GET['tahun'] ?? date('Y'));

        $sql = "SELECT mp.full_name AS nama_member, bk.title AS judul_buku,
                       br.borrow_date, br.due_date, br.return_date, br.status
                FROM borrowings br
                JOIN books bk ON br.book_id = bk.id
                JOIN users u ON br.user_id = u.id
                LEFT JOIN member_profiles mp ON mp.user_id = u.id
                WHERE MONTH(br.borrow_date) = ? AND YEAR(br.borrow_date) = ?
                ORDER BY br.borrow_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $bulan, $tahun);
        $stmt->execute();
        $res = $stmt->get_result();
        ?>
        <form method="GET" class="mb-4 flex gap-2 items-end">
            <input type="hidden" name="tab" value="peminjaman">
            <div>
                <label class="block text-xs">Bulan</label>
                <select name="bulan" class="border rounded p-2">
                    <?php for ($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>" <?= $m==$bulan?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs">Tahun</label>
                <input type="number" name="tahun" value="<?= $tahun ?>" class="border rounded p-2 w-24">
            </div>
            <button class="bg-blue-600 text-white px-3 py-2 rounded text-sm">Filter</button>
        </form>

        <table class="w-full bg-white shadow rounded text-sm">
            <thead class="bg-gray-100">
                <tr><th class="p-2 text-left">Member</th><th class="p-2 text-left">Buku</th><th class="p-2 text-left">Pinjam</th><th class="p-2 text-left">Jatuh Tempo</th><th class="p-2 text-left">Kembali</th><th class="p-2 text-center">Status</th></tr>
            </thead>
            <tbody>
                <?php while ($r = $res->fetch_assoc()): ?>
                <tr class="border-t">
                    <td class="p-2"><?= htmlspecialchars($r['nama_member'] ?? '-') ?></td>
                    <td class="p-2"><?= htmlspecialchars($r['judul_buku']) ?></td>
                    <td class="p-2"><?= $r['borrow_date'] ?></td>
                    <td class="p-2"><?= $r['due_date'] ?></td>
                    <td class="p-2"><?= $r['return_date'] ?? '-' ?></td>
                    <td class="p-2 text-center"><?= $r['status'] ?></td>
                </tr>
                <?php endwhile; ?>
                <?php if ($res->num_rows === 0): ?>
                <tr><td colspan="6" class="p-4 text-center text-gray-500">Tidak ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php $stmt->close(); ?>

    <?php elseif ($tab === 'denda'): ?>

        <?php
        $sql = "SELECT DATE_FORMAT(f.created_month, '%Y-%m') AS bulan,
                       SUM(f.amount) AS total_denda_masuk,
                       SUM(CASE WHEN f.fine_status='Belum bayar' THEN f.amount ELSE 0 END) AS total_belum_bayar,
                       SUM(CASE WHEN f.fine_status='Lunas' THEN f.amount ELSE 0 END) AS total_lunas
                FROM (
                    SELECT fn.*, b.due_date AS created_month
                    FROM fines fn
                    JOIN borrowings b ON fn.borrowing_id = b.id
                ) f
                GROUP BY DATE_FORMAT(f.created_month, '%Y-%m')
                ORDER BY bulan DESC";
        $res = $conn->query($sql);
        ?>
        <h2 class="font-semibold mb-2">Total Denda Per Bulan</h2>
        <table class="w-full bg-white shadow rounded text-sm">
            <thead class="bg-gray-100">
                <tr><th class="p-2 text-left">Bulan</th><th class="p-2 text-right">Total Masuk</th><th class="p-2 text-right">Belum Bayar</th><th class="p-2 text-right">Lunas</th></tr>
            </thead>
            <tbody>
                <?php while ($r = $res->fetch_assoc()): ?>
                <tr class="border-t">
                    <td class="p-2"><?= $r['bulan'] ?></td>
                    <td class="p-2 text-right">Rp<?= number_format($r['total_denda_masuk'],0,',','.') ?></td>
                    <td class="p-2 text-right">Rp<?= number_format($r['total_belum_bayar'],0,',','.') ?></td>
                    <td class="p-2 text-right">Rp<?= number_format($r['total_lunas'],0,',','.') ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php elseif ($tab === 'member'): ?>

        <?php
        $sql = "SELECT DATE_FORMAT(u.created_at, '%Y-%m') AS bulan,
                       COUNT(*) AS jumlah_member_baru,
                       SUM(CASE WHEN mp.membership_type='Free' THEN 1 ELSE 0 END) AS tipe_free,
                       SUM(CASE WHEN mp.membership_type='Premium' THEN 1 ELSE 0 END) AS tipe_premium
                FROM users u
                JOIN member_profiles mp ON mp.user_id = u.id
                GROUP BY DATE_FORMAT(u.created_at, '%Y-%m')
                ORDER BY bulan DESC";
        $res = $conn->query($sql);
        ?>
        <h2 class="font-semibold mb-2">Jumlah Member Baru Per Bulan</h2>
        <table class="w-full bg-white shadow rounded text-sm">
            <thead class="bg-gray-100">
                <tr><th class="p-2 text-left">Bulan</th><th class="p-2 text-right">Member Baru</th><th class="p-2 text-right">Free</th><th class="p-2 text-right">Premium</th></tr>
            </thead>
            <tbody>
                <?php while ($r = $res->fetch_assoc()): ?>
                <tr class="border-t">
                    <td class="p-2"><?= $r['bulan'] ?></td>
                    <td class="p-2 text-right"><?= $r['jumlah_member_baru'] ?></td>
                    <td class="p-2 text-right"><?= $r['tipe_free'] ?></td>
                    <td class="p-2 text-right"><?= $r['tipe_premium'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php elseif ($tab === 'kategori'): ?>

        <?php
        $sql = "SELECT c.category_name AS kategori,
                       COUNT(DISTINCT bc.book_id) AS jumlah_buku,
                       COALESCE(AVG(rr.rating), 0) AS rata_rata_rating,
                       COUNT(br.id) AS total_dipinjam
                FROM categories c
                LEFT JOIN book_categories bc ON bc.category_id = c.id
                LEFT JOIN books bk ON bk.id = bc.book_id
                LEFT JOIN reviews_ratings rr ON rr.book_id = bk.id
                LEFT JOIN borrowings br ON br.book_id = bk.id
                GROUP BY c.id, c.category_name
                ORDER BY jumlah_buku DESC";
        $res = $conn->query($sql);
        ?>
        <h2 class="font-semibold mb-2">Statistik Buku Per Kategori</h2>
        <table class="w-full bg-white shadow rounded text-sm">
            <thead class="bg-gray-100">
                <tr><th class="p-2 text-left">Kategori</th><th class="p-2 text-right">Jumlah Buku</th><th class="p-2 text-right">Rata-rata Rating</th><th class="p-2 text-right">Total Dipinjam</th></tr>
            </thead>
            <tbody>
                <?php while ($r = $res->fetch_assoc()): ?>
                <tr class="border-t">
                    <td class="p-2"><?= htmlspecialchars($r['kategori']) ?></td>
                    <td class="p-2 text-right"><?= $r['jumlah_buku'] ?></td>
                    <td class="p-2 text-right"><?= number_format($r['rata_rata_rating'],2) ?></td>
                    <td class="p-2 text-right"><?= $r['total_dipinjam'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php elseif ($tab === 'ringkasan'): ?>

        <?php
        $bulan = (int)($_GET['bulan'] ?? date('n'));
        $tahun = (int)($_GET['tahun'] ?? date('Y'));

        $stmt = $conn->prepare("CALL sp_laporan_bulanan(?, ?)");
        $stmt->bind_param('ii', $bulan, $tahun);
        $stmt->execute();

        $hasil = [];
        do {
            if ($r = $stmt->get_result()) {
                $hasil[] = $r->fetch_all(MYSQLI_ASSOC);
            }
        } while ($stmt->more_results() && $stmt->next_result());
        $stmt->close();
        ?>
        <form method="GET" class="mb-4 flex gap-2 items-end">
            <input type="hidden" name="tab" value="ringkasan">
            <div>
                <label class="block text-xs">Bulan</label>
                <select name="bulan" class="border rounded p-2">
                    <?php for ($m=1;$m<=12;$m++): ?>
                        <option value="<?= $m ?>" <?= $m==$bulan?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs">Tahun</label>
                <input type="number" name="tahun" value="<?= $tahun ?>" class="border rounded p-2 w-24">
            </div>
            <button class="bg-blue-600 text-white px-3 py-2 rounded text-sm">Tampilkan</button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <?php
            $labels = ['Total Peminjaman', 'Total Denda Masuk', 'Member Baru'];
            foreach ($labels as $i => $label):
                $val = $hasil[$i][0]['laporan'] ?? '-';
            ?>
            <div class="bg-white shadow rounded p-4">
                <div class="text-xs text-gray-500"><?= $label ?></div>
                <div class="text-lg font-bold"><?= htmlspecialchars($val) ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <h2 class="font-semibold mb-2">Buku Paling Sering Dipinjam Bulan Ini</h2>
        <table class="w-full bg-white shadow rounded text-sm">
            <thead class="bg-gray-100">
                <tr><th class="p-2 text-left">Judul</th><th class="p-2 text-right">Total Dipinjam</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($hasil[3])): foreach ($hasil[3] as $r): ?>
                <tr class="border-t">
                    <td class="p-2"><?= htmlspecialchars($r['judul_buku']) ?></td>
                    <td class="p-2 text-right"><?= $r['total_dipinjam'] ?></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="2" class="p-4 text-center text-gray-500">Tidak ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>