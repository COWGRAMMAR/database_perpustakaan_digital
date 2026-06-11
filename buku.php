<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Buku</title>
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
        .form-box input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd;
                           border-radius: 5px; }
        .form-box .row { display: flex; gap: 15px; }
        .form-box .row > div { flex: 1; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;
               font-weight: bold; }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; }
        .btn-danger { background: #e74c3c; color: white; padding: 5px 12px; font-size: 13px; }
        .btn-danger:hover { background: #c0392b; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px;
                overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #3498db; color: white; }
        tr:hover { background: #f5f5f5; }
        a.action { text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h1>📖 Data Buku</h1>

    <div class="nav">
        <a href="index.php">🏠 Beranda</a>
        <a href="buku.php">📖 Buku</a>
        <a href="anggota.php">👥 Anggota</a>
        <a href="pinjam.php">📋 Peminjaman</a>
    </div>

    <?php
    include 'config.php';

    // Tambah buku
    if (isset($_POST['simpan'])) {
        $judul  = mysqli_real_escape_string($conn, $_POST['judul']);
        $penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
        $penerbit = mysqli_real_escape_string($conn, $_POST['penerbit']);
        $tahun  = (int)$_POST['tahun'];
        $stok   = (int)$_POST['stok'];

        $sql = "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, stok)
                VALUES ('$judul', '$penulis', '$penerbit', $tahun, $stok)";

        if (mysqli_query($conn, $sql)) {
            echo "<div class='msg msg-ok'>✅ Buku berhasil ditambahkan!</div>";
        } else {
            echo "<div class='msg msg-err'>❌ Gagal: " . mysqli_error($conn) . "</div>";
        }
    }

    // Hapus buku
    if (isset($_GET['hapus'])) {
        $id = (int)$_GET['hapus'];
        mysqli_query($conn, "DELETE FROM buku WHERE id_buku = $id");
        echo "<div class='msg msg-ok'>🗑️ Buku berhasil dihapus!</div>";
    }
    ?>

    <!-- Form Tambah Buku -->
    <div class="form-box">
        <h3 style="margin-bottom:10px;">➕ Tambah Buku Baru</h3>
        <form method="post">
            <div class="row">
                <div>
                    <input type="text" name="judul" placeholder="Judul Buku" required>
                </div>
                <div>
                    <input type="text" name="penulis" placeholder="Penulis">
                </div>
            </div>
            <div class="row">
                <div>
                    <input type="text" name="penerbit" placeholder="Penerbit">
                </div>
                <div>
                    <input type="number" name="tahun" placeholder="Tahun Terbit" min="1900" max="2099">
                </div>
                <div>
                    <input type="number" name="stok" placeholder="Stok" min="1" value="1">
                </div>
            </div>
            <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
        </form>
    </div>

    <!-- Tabel Daftar Buku -->
    <table>
        <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Penerbit</th>
            <th>Tahun</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
        <?php
        $result = mysqli_query($conn, "SELECT * FROM buku ORDER BY id_buku DESC");
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['judul']) ?></td>
            <td><?= htmlspecialchars($row['penulis']) ?></td>
            <td><?= htmlspecialchars($row['penerbit']) ?></td>
            <td><?= $row['tahun_terbit'] ?></td>
            <td><?= $row['stok'] ?></td>
            <td>
                <a class="action" href="?hapus=<?= $row['id_buku'] ?>"
                   onclick="return confirm('Yakin hapus buku ini?')">
                    <button class="btn btn-danger">Hapus</button>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
