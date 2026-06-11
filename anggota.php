<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Anggota</title>
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
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px;
                overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #3498db; color: white; }
        tr:hover { background: #f5f5f5; }
    </style>
</head>
<body>
<div class="container">
    <h1>👥 Data Anggota</h1>

    <div class="nav">
        <a href="index.php">🏠 Beranda</a>
        <a href="buku.php">📖 Buku</a>
        <a href="anggota.php">👥 Anggota</a>
        <a href="pinjam.php">📋 Peminjaman</a>
    </div>

    <?php
    include 'config.php';

    // Tambah anggota
    if (isset($_POST['simpan'])) {
        $nama  = mysqli_real_escape_string($conn, $_POST['nama']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $telp  = mysqli_real_escape_string($conn, $_POST['no_telepon']);

        $sql = "INSERT INTO anggota (nama, email, no_telepon) VALUES ('$nama', '$email', '$telp')";

        if (mysqli_query($conn, $sql)) {
            echo "<div class='msg msg-ok'>✅ Anggota berhasil ditambahkan!</div>";
        } else {
            echo "<div class='msg msg-err'>❌ Gagal: " . mysqli_error($conn) . "</div>";
        }
    }
    ?>

    <!-- Form Tambah Anggota -->
    <div class="form-box">
        <h3 style="margin-bottom:10px;">➕ Tambah Anggota Baru</h3>
        <form method="post">
            <div class="row">
                <div>
                    <input type="text" name="nama" placeholder="Nama Lengkap" required>
                </div>
                <div>
                    <input type="email" name="email" placeholder="Email">
                </div>
                <div>
                    <input type="text" name="no_telepon" placeholder="No. Telepon">
                </div>
            </div>
            <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
        </form>
    </div>

    <!-- Tabel Daftar Anggota -->
    <table>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Email</th>
            <th>No. Telepon</th>
        </tr>
        <?php
        $result = mysqli_query($conn, "SELECT * FROM anggota ORDER BY id_anggota DESC");
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['no_telepon']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
