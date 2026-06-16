<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit();
}

require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_aplikasi  = trim($_POST['nama_aplikasi']);
    $developer      = trim($_POST['developer']);
    $kategori       = trim($_POST['kategori']);
    $deskripsi      = trim($_POST['deskripsi']);
    $link_playstore = trim($_POST['link_playstore']);
    $pengunggah     = $_SESSION['username'];
    $tanggal        = date('Y-m-d');
    $nama_ikon_baru = "";

    // Validasi & Upload Ikon Aplikasi (Maksimal 2MB)
    if (isset($_FILES['ikon']) && $_FILES['ikon']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['ikon']['tmp_name'];
        $file_name = $_FILES['ikon']['name'];
        $ekstensi  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'])) {
            if (!is_dir('uploads/apps')) {
                mkdir('uploads/apps', 0755, true);
            }
            $nama_ikon_baru = 'app_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
            move_uploaded_file($file_tmp, 'uploads/apps/' . $nama_ikon_baru);
        } else {
            echo "<script>alert('Format gambar salah! Gunakan JPG, PNG, atau WEBP.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Wajib mengunggah ikon aplikasi!'); window.history.back();</script>";
        exit();
    }

    // Simpan ke Database
    try {
        $query = "INSERT INTO aplikasi (nama_aplikasi, developer, kategori, deskripsi, ikon, link_playstore, pengunggah, tanggal_upload) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt  = $koneksi->prepare($query);
        $stmt->bind_param("ssssssss", $nama_aplikasi, $developer, $kategori, $deskripsi, $nama_ikon_baru, $link_playstore, $pengunggah, $tanggal);

        if ($stmt->execute()) {
            echo "<script>alert('Aplikasi Mobile berhasil dipublikasikan!'); window.location.href='aplikasi_mobile.php';</script>";
            exit();
        } else {
            echo "<script>alert('Gagal menyimpan data.'); window.history.back();</script>";
        }
    } catch (mysqli_sql_exception $e) {
        echo "<script>alert('Kesalahan Sistem: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Aplikasi</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        
        /* Efek transisi masuk halaman (Identik dengan artikel.php) */
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
            animation: fadeInUp 0.7s ease-out;
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
        
        /* Efek Transisi saat Input aktif */
        .group input:focus, .group select:focus, .group textarea:focus { 
            border-color: #6a0d6a; 
            outline: none; 
            box-shadow: 0 0 8px rgba(106, 13, 106, 0.2);
        }
        
        .btn-submit { 
            background: #6a0d6a; 
            color: white; 
            border: none; 
            padding: 12px 20px; 
            border-radius: 6px; 
            font-weight: bold; 
            cursor: pointer; 
            width: 100%; 
            transition: background 0.2s, transform 0.2s; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            margin-top: 10px;
        }
        .btn-submit:hover { 
            background: #4a094a; 
            transform: translateY(-1px);
        }

        /* Responsive Mobile */
        @media (max-width: 480px) {
            .form-box { padding: 20px; }
            .btn-submit { padding: 14px 20px; }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <div class="form-box">
        <h2>📱 Daftarkan Aplikasi Mobile</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="group"><label>Nama Aplikasi</label><input type="text" name="nama_aplikasi" required></div>
            <div class="group"><label>Nama Developer / Tim</label><input type="text" name="developer" required></div>
            <div class="group">
                <label>Kategori</label>
                <select name="kategori" required>
                    <option value="E-Learning">E-Learning / Edukasi</option>
                    <option value="Productivity">Productivity / Alat</option>
                    <option value="Social">Social / Komunitas</option>
                    <option value="Entertainment">Entertainment / Hiburan</option>
                </select>
            </div>
            <div class="group"><label>Link Google Play Store</label><input type="url" name="link_playstore" placeholder="https://play.google.com/store/apps/details?id=..." required></div>
            <div class="group"><label>Ikon Aplikasi (Rasio 1:1)</label><input type="file" name="ikon" accept="image/*" required></div>
            <div class="group"><label>Deskripsi Ringkas Aplikasi</label><textarea name="deskripsi" rows="4" required></textarea></div>
            <button type="submit" class="btn-submit">PUBLIKASIKAN APLIKASI</button>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>