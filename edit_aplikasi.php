<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';

$id_app = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_login = strtolower(trim($_SESSION['username']));

// Ambil data aplikasi lama
$query = "SELECT * FROM aplikasi WHERE id = ? LIMIT 1";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id_app);
$stmt->execute();
$result = $stmt->get_result();
$app = $result->fetch_assoc();
$stmt->close();

if (!$app) {
    echo "<script>alert('Data aplikasi tidak ditemukan!'); window.location.href='aplikasi_mobile.php';</script>";
    exit();
}

// Hak akses owner/admin
if ($user_login !== strtolower($app['pengunggah']) && $user_login !== 'admin') {
    echo "<script>alert('Akses Ditolak!'); window.location.href='aplikasi_mobile.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_aplikasi  = trim($_POST['nama_aplikasi']);
    $developer      = trim($_POST['developer']);
    $kategori       = trim($_POST['kategori']);
    $deskripsi      = trim($_POST['deskripsi']);
    $link_playstore = trim($_POST['link_playstore']);
    $ikon_final     = $app['ikon'];

    // Proses Ganti Ikon (jika ada upload baru)
    if (isset($_FILES['ikon']) && $_FILES['ikon']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['ikon']['tmp_name'];
        $file_name = $_FILES['ikon']['name'];
        $ekstensi  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'])) {
            // Hapus ikon lama jika ada
            if (!empty($app['ikon']) && file_exists('uploads/apps/' . $app['ikon'])) {
                unlink('uploads/apps/' . $app['ikon']);
            }
            $ikon_final = 'app_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
            move_uploaded_file($file_tmp, 'uploads/apps/' . $ikon_final);
        }
    }

    try {
        $query_update = "UPDATE aplikasi SET nama_aplikasi = ?, developer = ?, kategori = ?, deskripsi = ?, ikon = ?, link_playstore = ? WHERE id = ?";
        $stmt_update  = $koneksi->prepare($query_update);
        $stmt_update->bind_param("ssssssi", $nama_aplikasi, $developer, $kategori, $deskripsi, $ikon_final, $link_playstore, $id_app);
        
        if ($stmt_update->execute()) {
            echo "<script>alert('Data aplikasi berhasil diperbarui!'); window.location.href='aplikasi_mobile.php';</script>";
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        echo "<script>alert('Gagal memperbarui data.'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Aplikasi</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        
        /* Efek transisi masuk halaman */
        @keyframes fadeInUp { 
            from { opacity: 0; transform: translateY(25px); } 
            to { opacity: 1; transform: translateY(0); } 
        }

        body { 
            background: linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), url('logo.png'); 
            background-size: 100px; 
            background-repeat: repeat;
            min-height: 100vh; 
            color: #333; 
        }
        
        .container { 
            display: flex; 
            justify-content: center; 
            padding: 40px 20px; 
            animation: fadeInUp 0.7s ease-out; /* Selaras dengan artikel.php */
        }
        
        .form-box { 
            background: rgba(255, 255, 255, 0.95); 
            padding: 30px; 
            border-radius: 15px; 
            width: 100%; 
            max-width: 600px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); 
        }
        
        .form-box h2 { 
            color: #6a0d6a; 
            margin-bottom: 20px; 
            border-bottom: 2px solid rgba(106,13,106,0.1); 
            padding-bottom: 10px; 
            font-weight: 700;
        }
        
        .group { margin-bottom: 15px; }
        .group label { display: block; font-weight: bold; margin-bottom: 5px; color: #4a094a; font-size: 0.95rem; }
        
        .group input, .group select, .group textarea { 
            width: 100%; 
            padding: 11px; 
            border: 2px solid #ddd; 
            border-radius: 6px; 
            font-size: 1rem; 
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        /* Efek Transisi saat Input diklik */
        .group input:focus, .group select:focus, .group textarea:focus { 
            border-color: #6a0d6a; 
            outline: none; 
            box-shadow: 0 0 8px rgba(106, 13, 106, 0.2);
        }
        
        .preview-img { 
            width: 75px; 
            height: 75px; 
            border-radius: 14px; 
            margin-top: 10px; 
            object-fit: cover; 
            border: 2px solid rgba(106,13,106,0.15); 
            display: block; 
        }
        
        .btn-group { 
            display: flex; 
            gap: 10px; 
            justify-content: flex-end; 
            margin-top: 25px; 
        }
        
        .btn-save { 
            background: #6a0d6a; 
            color: white; 
            border: none; 
            padding: 12px 24px; 
            border-radius: 6px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: background 0.2s, transform 0.2s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .btn-save:hover { 
            background: #4a094a; 
            transform: translateY(-1px);
        }
        
        .btn-cancel { 
            background: white; 
            color: #666; 
            border: 2px solid #ddd; 
            padding: 11px 24px; 
            border-radius: 6px; 
            text-decoration: none; 
            font-weight: bold; 
            transition: background 0.2s, border-color 0.2s;
        }
        .btn-cancel:hover {
            background: #f5f5f5;
            border-color: #bbb;
        }

        /* Responsive Mobile */
        @media (max-width: 480px) {
            .form-box { padding: 20px; }
            .btn-group { flex-direction: column-reverse; }
            .btn-cancel, .btn-save { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <div class="form-box">
        <h2>🔧 Edit Aplikasi Mobile</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="group"><label>Nama Aplikasi</label><input type="text" name="nama_aplikasi" value="<?= htmlspecialchars($app['nama_aplikasi']); ?>" required></div>
            <div class="group"><label>Nama Developer / Tim</label><input type="text" name="developer" value="<?= htmlspecialchars($app['developer']); ?>" required></div>
            <div class="group">
                <label>Kategori</label>
                <select name="kategori" required>
                    <option value="E-Learning" <?= $app['kategori'] == 'E-Learning' ? 'selected' : ''; ?>>E-Learning / Edukasi</option>
                    <option value="Productivity" <?= $app['kategori'] == 'Productivity' ? 'selected' : ''; ?>>Productivity / Alat</option>
                    <option value="Social" <?= $app['kategori'] == 'Social' ? 'selected' : ''; ?>>Social / Komunitas</option>
                    <option value="Entertainment" <?= $app['kategori'] == 'Entertainment' ? 'selected' : ''; ?>>Entertainment / Hiburan</option>
                </select>
            </div>
            <div class="group"><label>Link Google Play Store</label><input type="url" name="link_playstore" value="<?= htmlspecialchars($app['link_playstore']); ?>" required></div>
            <div class="group">
                <label>Ganti Ikon Aplikasi (Opsional)</label>
                <input type="file" name="ikon" accept="image/*">
                <img src="uploads/apps/<?= htmlspecialchars($app['ikon']); ?>" class="preview-img" alt="Ikon Sekarang">
            </div>
            <div class="group"><label>Deskripsi Ringkas</label><textarea name="deskripsi" rows="4" required><?= htmlspecialchars($app['deskripsi']); ?></textarea></div>
            <div class="btn-group">
                <a href="aplikasi_mobile.php" class="btn-cancel">BATAL</a>
                <button type="submit" class="btn-save">SIMPAN PERUBAHAN</button>
            </div>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>