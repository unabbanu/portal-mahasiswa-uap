<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Proteksi Halaman
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$current_page = 'game_browser.php'; 
require_once 'koneksi.php';

$id_game = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_login = strtolower(trim($_SESSION['username']));

// 2. Ambil data lama game dari database
$query = "SELECT * FROM games WHERE id = ? LIMIT 1";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id_game);
$stmt->execute();
$result = $stmt->get_result();
$game = $result->fetch_assoc();
$stmt->close();

if (!$game) {
    echo "<script>alert('Data game tidak ditemukan!'); window.location.href='game_browser.php';</script>";
    exit();
}

// Pastikan hanya pemilik atau admin yang bisa edit
if ($user_login !== strtolower($game['pembuat']) && $user_login !== 'admin') {
    echo "<script>alert('Akses Ditolak! Anda bukan pemilik game ini.'); window.location.href='game_browser.php';</script>";
    exit();
}

// 3. Proses Update saat Form di-Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul       = trim($_POST['judul']);
    $genre       = trim($_POST['genre']);
    $deskripsi   = trim($_POST['deskripsi']);
    $banner_lama = $game['banner'];
    $folder_lama = $game['folder_game'];
    
    $banner_final = $banner_lama;
    $folder_final = $folder_lama;

    // --- PROSES UPDATE BANNER (Opsional) ---
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['banner']['tmp_name'];
        $file_name = $_FILES['banner']['name'];
        $ekstensi  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'])) {
            // Hapus banner lama jika ada
            if (!empty($banner_lama) && file_exists('uploads/games/' . $banner_lama)) {
                unlink('uploads/games/' . $banner_lama);
            }
            // Upload banner baru
            $banner_final = 'game_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
            move_uploaded_file($file_tmp, 'uploads/games/' . $banner_final);
        }
    }

    // --- PROSES UPDATE FILE GAME .ZIP ---
    if (isset($_FILES['file_game']) && $_FILES['file_game']['error'] === UPLOAD_ERR_OK) {
        $zip_tmp  = $_FILES['file_game']['tmp_name'];
        $zip_name = $_FILES['file_game']['name'];
        $ekstensi_zip = strtolower(pathinfo($zip_name, PATHINFO_EXTENSION));

        if ($ekstensi_zip === 'zip') {
            // Hapus folder lama
            $dir_lama = 'game/' . $folder_lama;
            if (!empty($folder_lama) && is_dir($dir_lama)) {
                array_map('unlink', glob("$dir_lama/*"));
                @rmdir($dir_lama); 
            }

            // Bikin nama slug folder baru
            $folder_final = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $judul)) . '_' . time();
            $dir_baru     = 'game/' . $folder_final;

            // Ekstrak ZIP baru
            $zip = new ZipArchive;
            if ($zip->open($zip_tmp) === TRUE) {
                if (!is_dir($dir_baru)) { mkdir($dir_baru, 0755, true); }
                $zip->extractTo($dir_baru);
                $zip->close();
            }
        }
    }

    // 4. Update Data ke Database
    try {
        $query_update = "UPDATE games SET judul = ?, genre = ?, deskripsi = ?, banner = ?, folder_game = ? WHERE id = ?";
        $stmt_update  = $koneksi->prepare($query_update);
        $stmt_update->bind_param("sssssi", $judul, $genre, $deskripsi, $banner_final, $folder_final, $id_game);
        
        if ($stmt_update->execute()) {
            echo "<script>alert('Data game berhasil diperbarui!'); window.location.href='game_browser.php';</script>";
            exit();
        } else {
            echo "<script>alert('Gagal memperbarui database.'); window.history.back();</script>";
        }
        $stmt_update->close();
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
    <title>Edit Game: <?= htmlspecialchars($game['judul']); ?></title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">
    <style>
        /* --- RESET & STYLING SELARAS DENGAN TAMBAH_GAME.PHP --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* --- ANIMASI TRANSISI HALAMAN --- */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        body { 
            background: linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), url('logo.png'); 
            background-size: 100px; 
            background-repeat: repeat;
            min-height: 100vh; 
        }

        .main-content { 
            min-height: calc(100vh - 85px);
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 40px 20px; 
        }

        .form-container {
            width: 100%;
            max-width: 800px;
            animation: fadeIn 0.8s ease-out; /* Efek transisi masuk 0.8 detik murni */
        }

        .form-box { 
            background: white; 
            padding: 35px 30px; 
            border-radius: 16px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.4); 
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #333;
        }

        .form-header-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f2e6f2; 
            padding-bottom: 10px; 
            margin-bottom: 25px; 
        }

        .form-box h3 { 
            color: #6a0d6a; 
            font-size: 1.6rem;
            font-weight: 700;
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        /* Tombol Panduan */
        .btn-info {
            background-color: #2575fc;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 0.85rem;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-info:hover {
            background-color: #1a5bc4;
            transform: translateY(-1px);
        }

        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #555; font-size: 1rem; }
        
        .input-group input[type="text"], .input-group select, .input-group textarea { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #ddd; 
            border-radius: 8px; 
            font-size: 1rem; 
            transition: 0.3s;
            font-family: inherit;
            color: #333;
        }

        .input-group input:focus, .input-group select:focus, .input-group textarea:focus { 
            border-color: #6a0d6a; 
            outline: none; 
            box-shadow: 0 0 8px rgba(106, 13, 106, 0.15);
        }

        .input-group input[type="file"] {
            padding: 8px 10px;
            background-color: #fdf8fd;
            border: 1px dashed #6a0d6a;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
        }

        .input-group small { display: block; margin-top: 5px; color: #666; font-size: 0.85rem; }
        
        .preview-img { 
            max-width: 250px; 
            max-height: 150px; 
            border-radius: 8px; 
            margin-top: 10px; 
            display: block; 
            object-fit: cover; 
            border: 2px solid #6a0d6a; 
        }

        .btn-group { display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px; }
        
        .btn-save { 
            background-color: #6a0d6a; 
            color: white; 
            border: none; 
            padding: 12px 28px; 
            border-radius: 8px; 
            font-weight: bold; 
            cursor: pointer; 
            font-size: 1rem;
            transition: 0.3s; 
            box-shadow: 0 4px 12px rgba(106, 13, 106, 0.2);
        }
        .btn-save:hover { 
            background-color: #4a094a; 
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(106, 13, 106, 0.3);
        }

        .btn-cancel { 
            background-color: white; 
            color: #666; 
            border: 2px solid #ddd; 
            padding: 12px 25px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: bold; 
            font-size: 1rem;
            text-align: center; 
            transition: 0.3s;
        }
        .btn-cancel:hover { background-color: #f5f5f5; color: #333; }

        /* --- STYLING MODAL POP-UP PANDUAN KEMBAR --- */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            display: flex; align-items: center; justify-content: center;
            z-index: 9999; opacity: 0; pointer-events: none;
            transition: 0.3s ease;
        }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        
        .modal-content-box {
            background: white; padding: 30px; border-radius: 16px;
            width: 90%; max-width: 600px; max-height: 85vh; overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            transform: translateY(-30px); transition: 0.3s ease;
        }
        .modal-overlay.active .modal-content-box { transform: translateY(0); }
        
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .modal-header h4 { color: #2575fc; font-size: 1.3rem; font-weight: bold; }
        .close-modal { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999; }
        .close-modal:hover { color: #333; }
        
        .guide-step { margin-bottom: 15px; padding-left: 10px; border-left: 3px solid #6a0d6a; }
        .guide-step strong { color: #333; display: block; margin-bottom: 3px; }
        .guide-step p { color: #555; font-size: 0.9rem; line-height: 1.5; }
        .badge-alert { background: #fff3cd; color: #856404; padding: 10px; border-radius: 6px; border-left: 4px solid #ffeeba; font-size: 0.85rem; margin-top: 15px; }

        @media (max-width: 768px) {
            .main-content { padding: 20px 10px; }
            .form-box { padding: 25px 20px; }
            .form-header-wrapper { flex-direction: column; align-items: flex-start; gap: 10px; }
            .btn-info { width: 100%; justify-content: center; }
            .btn-group { flex-direction: column-reverse; gap: 10px; }
            .btn-save, .btn-cancel { width: 100%; }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main-content">
    <div class="form-container">
        <div class="form-box">
            
            <div class="form-header-wrapper">
                <h3>🔧 Pengaturan & Edit Game</h3>
                <button type="button" class="btn-info" onclick="toggleModal(true)">💡 PANDUAN UPLOAD</button>
            </div>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="judul">Judul Game :</label>
                    <input type="text" id="judul" name="judul" value="<?= htmlspecialchars($game['judul']); ?>" required autocomplete="off">
                </div>

                <div class="input-group">
                    <label for="genre">Genre Permainan :</label>
                    <select id="genre" name="genre" required>
                        <option value="Action" <?= $game['genre'] == 'Action' ? 'selected' : ''; ?>>Action / Petualangan</option>
                        <option value="Puzzle" <?= $game['genre'] == 'Puzzle' ? 'selected' : ''; ?>>Puzzle / Asah Otak</option>
                        <option value="Sports" <?= $game['genre'] == 'Sports' ? 'selected' : ''; ?>>Sports / Olahraga</option>
                        <option value="Strategy" <?= $game['genre'] == 'Strategy' ? 'selected' : ''; ?>>Strategy / Simulasi</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="banner">Ganti Gambar Sampul Banner (Opsional) :</label>
                    <input type="file" id="banner" name="banner" accept="image/*">
                    <small>Format: JPG, JPEG, PNG, WEBP. Biarkan kosong jika tidak ingin mengubah banner saat ini.</small>
                    <img src="uploads/games/<?= htmlspecialchars($game['banner']); ?>" class="preview-img" alt="Banner Saat Ini">
                </div>

                <div class="input-group">
                    <label for="file_game">Ganti Berkas Game ZIP Baru (Opsional) :</label>
                    <input type="file" id="file_game" name="file_game" accept=".zip">
                    <small>Pilih file <strong>.ZIP</strong> baru jika game saat ini rusak atau ingin diperbarui. Biarkan kosong jika game berjalan normal.</small>
                    <p style="margin-top: 8px; font-size: 0.85rem; color: #6a0d6a;">📁 Direktori Aktif: <code>game/<?= htmlspecialchars($game['folder_game']); ?>/</code></p>
                </div>

                <div class="input-group">
                    <label for="deskripsi">Petunjuk Main & Ringkasan Deskripsi :</label>
                    <textarea id="deskripsi" name="deskripsi" rows="5" required><?= htmlspecialchars($game['deskripsi']); ?></textarea>
                </div>

                <div class="btn-group">
                    <a href="game_browser.php" class="btn-cancel">BATALKAN</a>
                    <button type="submit" class="btn-save">SIMPAN PERUBAHAN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal-overlay" id="guideModal">
    <div class="modal-content-box">
        <div class="modal-header">
            <h4>⚙️ Panduan Pengemasan Berkas Game (.ZIP)</h4>
            <button type="button" class="close-modal" onclick="toggleModal(false)">&times;</button>
        </div>
        
        <div class="guide-step">
            <strong>1. Pastikan Game Menggunakan HTML5</strong>
            <p>Sistem ini menjalankan game berbasis web. Pastikan game Anda memiliki struktur file client-side (HTML, CSS, JS) dan bisa berjalan langsung di browser.</p>
        </div>
        
        <div class="guide-step">
            <strong>2. File 'index.html' Harus Berada Di Luar</strong>
            <p>Buka folder project game Anda, pastikan file utama eksekusi bernama <strong>index.html</strong> (Gunakan huruf kecil semua). Jangan masukkan index.html ke dalam sub-folder lagi.</p>
        </div>
        
        <div class="guide-step">
            <strong>3. Cara Melakukan Compress yang Benar</strong>
            <p>Blok/pilih semua file di dalam project game Anda (index.html beserta folder assets, js, css-nya), klik kanan -> <strong>Send to -> Compressed (zipped) folder</strong> atau gunakan aplikasi WinRAR/7-Zip dan pilih format arsip <strong>.ZIP</strong>.</p>
        </div>

        <div class="badge-alert">
            ⚠️ <strong>PENTING:</strong> Sistem backend server memakai ekstensi otomatis <code>ZipArchive</code>. Format berkas <strong>.RAR tidak didukung</strong>. Pastikan ekstensi file Anda berakhiran <strong>.zip</strong> sebelum diunggah!
        </div>
    </div>
</div>

<script>
    function toggleModal(show) {
        const modal = document.getElementById('guideModal');
        if (show) {
            modal.classList.add('active');
        } else {
            modal.classList.remove('active');
        }
    }

    // Menutup modal jika user klik di luar kotak putih
    document.getElementById('guideModal').addEventListener('click', function(e) {
        if(e.target === this) {
            toggleModal(false);
        }
    });
</script>

<?php include 'footer.php'; ?>
</body>
</html>