<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Peminjaman</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 30px; }
        .container { max-width: 900px; margin: auto; }
        h1 { color: #2c3e50; margin-bottom: 20px; }
        .nav { background: #2c3e50; padding: 12px 20px; border-radius: 8px; margin-bottom: 25px; }
        .nav a { color: white; text-decoration: none; margin-right: 20px; font-weight: bold; }
        .nav a:hover { color: #3498db; }
        .msg { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .msg-ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .msg-err { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-box { background: white; padding: 20px; border-radius: 8px; margin-bottom: 25px;
                     box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .form-box select, .form-box input { width: 100%; padding: 10px; margin: 8px 0;
                                             border: 1px solid #ddd; border-radius: 5px; }
        .form-box .row { display: flex; gap: 15px; }
        .form-box .row > div { flex: 1; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;
               font-weight: bold; }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
        .btn-success { background: #27ae60; color: white; padding: 5px 12px; font-size: 13px;
                        text-decoration: none; border-radius: 5px; }
        .btn-success:hover { background: #1e8449; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px;
                overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #3498db; color: white; }
        tr:hover { background: #f5f5f5; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-dipinjam { background: #e74c3c; color: white; }
        .badge-kembali { background: #27ae60; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h1>📋 Peminjaman Buku</h1>

    <div class="nav">
        <a href="index.php">🏠 Beranda</a>
        <a href="buku.php">📖 Buku</a>
        <a href="anggota.php">👥 Anggota</a>
        <a href="pinjam.php">📋 Peminjaman</a>
    </div>

    <?php
    include 'config.php';

    // Proses pinjam buku
    if (isset($_POST['pinjam'])) {
        $id_buku    = (int)$_POST['id_buku'];
        $id_anggota = (int)$_POST['id_anggota'];
        $tgl_pinjam = mysqli_real_escape_string($conn, $_POST['tgl_pinjam']);

        // Cek stok
        $cek = mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku = $id_buku");
        $buku = mysqli_fetch_assoc($cek);

        if ($buku['stok'] > 0) {
            $sql = "INSERT INTO peminjaman (id_buku, id_anggota, tgl_pinjam, status)
                    VALUES ($id_buku, $id_anggota, '$tgl_pinjam', 'dipinjam')";
            if (mysqli_query($conn, $sql)) {
                // Kurangi stok
                mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id_buku = $id_buku");
                echo "<div class='msg msg-ok'>✅ Buku berhasil dipinjam!</div>";
            } else {
                echo "<div class='msg msg-err'>❌ Gagal: " . mysqli_error($conn) . "</div>";
            }
        } else {
            echo "<div class='msg msg-err'>❌ Stok buku habis!</div>";
        }
    }

    // Proses kembali buku
    if (isset($_GET['kembali'])) {
        $id_pinjam = (int)$_GET['kembali'];
        $hari_ini = date('Y-m-d');

        // Ambil id_buku dari peminjaman
        $q = mysqli_query($conn, "SELECT id_buku FROM peminjaman WHERE id_pinjam = $id_pinjam");
        $p = mysqli_fetch_assoc($q);
        $id_buku = $p['id_buku'];

        $sql = "UPDATE peminjaman SET tgl_kembali = '$hari_ini', status = 'dikembalikan'
                WHERE id_pinjam = $id_pinjam";
        if (mysqli_query($conn, $sql)) {
            // Tambah stok lagi
            mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku = $id_buku");
            echo "<div class='msg msg-ok'>✅ Buku berhasil dikembalikan!</div>";
        } else {
            echo "<div class='msg msg-err'>❌ Gagal: " . mysqli_error($conn) . "</div>";
        }
    }
    ?>

    <!-- Form Pinjam Buku -->
    <div class="form-box">
        <h3 style="margin-bottom:10px;">➕ Pinjam Buku</h3>
        <form method="post">
            <div class="row">
                <div>
                    <select name="id_buku" required>
                        <option value="">-- Pilih Buku --</option>
                        <?php
                        $buku = mysqli_query($conn, "SELECT * FROM buku WHERE stok > 0 ORDER BY judul");
                        while ($b = mysqli_fetch_assoc($buku)):
                        ?>
                        <option value="<?= $b['id_buku'] ?>">
                            <?= htmlspecialchars($b['judul']) ?> (Stok: <?= $b['stok'] ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <select name="id_anggota" required>
                        <option value="">-- Pilih Anggota --</option>
                        <?php
                        $anggota = mysqli_query($conn, "SELECT * FROM anggota ORDER BY nama");
                        while ($a = mysqli_fetch_assoc($anggota)):
                        ?>
                        <option value="<?= $a['id_anggota'] ?>">
                            <?= htmlspecialchars($a['nama']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <input type="date" name="tgl_pinjam" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <button type="submit" name="pinjam" class="btn btn-primary">Pinjam</button>
        </form>
    </div>

    <!-- Daftar Peminjaman -->
    <h2 style="margin-bottom:10px;">📋 Riwayat Peminjaman</h2>
    <table>
        <tr>
            <th>No</th>
            <th>Buku</th>
            <th>Peminjam</th>
            <th>Tgl Pinjam</th>
            <th>Tgl Kembali</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php
        $result = mysqli_query($conn, "
            SELECT p.*, b.judul, a.nama
            FROM peminjaman p
            JOIN buku b ON p.id_buku = b.id_buku
            JOIN anggota a ON p.id_anggota = a.id_anggota
            ORDER BY p.id_pinjam DESC
        ");
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['judul']) ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= $row['tgl_pinjam'] ?></td>
            <td><?= $row['tgl_kembali'] ?? '-' ?></td>
            <td>
                <span class="badge badge-<?= $row['status'] ?>">
                    <?= $row['status'] ?>
                </span>
            </td>
            <td>
                <?php if ($row['status'] == 'dipinjam'): ?>
                <a href="?kembali=<?= $row['id_pinjam'] ?>"
                   onclick="return confirm('Kembalikan buku ini?')"
                   class="btn-success">Kembalikan</a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
