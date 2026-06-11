<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Perpustakaan Digital</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 30px; }
        .container { max-width: 900px; margin: auto; }
        h1 { color: #2c3e50; margin-bottom: 20px; }
        .nav { background: #2c3e50; padding: 12px 20px; border-radius: 8px; margin-bottom: 25px; }
        .nav a { color: white; text-decoration: none; margin-right: 20px; font-weight: bold; }
        .nav a:hover { color: #3498db; }
        .stats { display: flex; gap: 20px; margin-bottom: 25px; }
        .card { background: white; padding: 20px; border-radius: 8px; flex: 1; text-align: center;
                 box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .card h3 { color: #7f8c8d; font-size: 14px; }
        .card p { font-size: 32px; font-weight: bold; color: #2c3e50; margin-top: 5px; }
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
    <h1>📚 Perpustakaan Digital</h1>

    <div class="nav">
        <a href="index.php">🏠 Beranda</a>
        <a href="buku.php">📖 Buku</a>
        <a href="anggota.php">👥 Anggota</a>
        <a href="pinjam.php">📋 Peminjaman</a>
    </div>

    <?php
    include 'config.php';

    $total_buku = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku");
    $total_buku = mysqli_fetch_assoc($total_buku)['total'];

    $total_anggota = mysqli_query($conn, "SELECT COUNT(*) AS total FROM anggota");
    $total_anggota = mysqli_fetch_assoc($total_anggota)['total'];

    $dipinjam = mysqli_query($conn, "SELECT COUNT(*) AS total FROM peminjaman WHERE status='dipinjam'");
    $dipinjam = mysqli_fetch_assoc($dipinjam)['total'];
    ?>

    <div class="stats">
        <div class="card">
            <h3>Total Buku</h3>
            <p><?= $total_buku ?></p>
        </div>
        <div class="card">
            <h3>Total Anggota</h3>
            <p><?= $total_anggota ?></p>
        </div>
        <div class="card">
            <h3>Sedang Dipinjam</h3>
            <p><?= $dipinjam ?></p>
        </div>
    </div>

    <h2 style="margin-bottom:10px;">📖 Buku Terbaru</h2>
    <table>
        <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Stok</th>
        </tr>
        <?php
        $result = mysqli_query($conn, "SELECT * FROM buku ORDER BY id_buku DESC LIMIT 5");
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['judul']) ?></td>
            <td><?= htmlspecialchars($row['penulis']) ?></td>
            <td><?= $row['stok'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
