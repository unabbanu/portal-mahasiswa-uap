<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. PROTEKSI UTAMA
if (!isset($_SESSION['username']) || empty(trim($_SESSION['username']))) {
    echo "<script>alert('Akses Ditolak! Sesi login Anda telah berakhir. Silakan login kembali.'); window.location.href='login.php';</script>";
    exit();
}

require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul       = trim($_POST['judul']);
    $genre       = trim($_POST['genre']);
    $deskripsi   = trim($_POST['deskripsi']);
    $pembuat     = trim($_SESSION['username']);
    $tanggal     = date('Y-m-d');
    $nama_banner_baru = "";

    // Otomatis membentuk format slug folder untuk direktori game (Contoh: "Space War 2D" -> "space-war-2d")
    $folder_game = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $judul)) . '_' . time();

    // ==========================================================================
    // 2. JALUR VALIDASI UNGGAH BANNER (Maksimal 2MB)
    // ==========================================================================
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['banner']['tmp_name'];
        $file_name = $_FILES['banner']['name'];
        $file_size = $_FILES['banner']['size'];
        
        $ekstensi_diperbolehkan = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi_file = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($ekstensi_file, $ekstensi_diperbolehkan)) {
            echo "<script>alert('Format gambar salah! Gunakan format JPG, JPEG, PNG, atau WEBP.'); window.history.back();</script>";
            exit();
        }
        if ($file_size > 10 * 1024 * 1024) {
            echo "<script>alert('Ukuran berkas terlalu besar! Batas maksimal banner adalah 10MB.'); window.history.back();</script>";
            exit();
        }

        if (!is_dir('uploads/games')) {
            mkdir('uploads/games', 0755, true);
        }

        $nama_banner_baru = 'game_' . time() . '_' . rand(100, 999) . '.' . $ekstensi_file;
        move_uploaded_file($file_tmp, 'uploads/games/' . $nama_banner_baru);
    } else {
        echo "<script>alert('Wajib mengunggah berkas gambar banner sampul!'); window.history.back();</script>";
        exit();
    }

    // ==========================================================================
    // 3. JALUR VALIDASI & EKSTRAKSI UNGGAH FILE ARSIP GAME ZIP (Maksimal 20MB)
    // ==========================================================================
    if (isset($_FILES['file_game']) && $_FILES['file_game']['error'] === UPLOAD_ERR_OK) {
        $zip_tmp  = $_FILES['file_game']['tmp_name'];
        $zip_name = $_FILES['file_game']['name'];
        $zip_size = $_FILES['file_game']['size'];
        
        $ekstensi_zip = strtolower(pathinfo($zip_name, PATHINFO_EXTENSION));
        
        if ($ekstensi_zip !== 'zip') {
            if (file_exists('uploads/games/' . $nama_banner_baru)) { unlink('uploads/games/' . $nama_banner_baru); }
            echo "<script>alert('Format berkas game salah! Hanya menerima kompresi berekstensi .ZIP'); window.history.back();</script>";
            exit();
        }
        if ($zip_size > 50 * 1024 * 1024) { // Batasi 50MB demi space hosting
            if (file_exists('uploads/games/' . $nama_banner_baru)) { unlink('uploads/games/' . $nama_banner_baru); }
            echo "<script>alert('Ukuran berkas ZIP game terlalu besar! Batas maksimal adalah 50MB.'); window.history.back();</script>";
            exit();
        }

        // Tentukan folder tujuan ekstraksi game (misal: direktori root projek /game/space-war-2d_17123456/)
        $direktori_ekstrak_game = 'game/' . $folder_game;

        // Eksekusi Unzip memanfaatkan built-in Class PHP ZipArchive
        $zip = new ZipArchive;
        if ($zip->open($zip_tmp) === TRUE) {
            // Buat foldernya terlebih dahulu jika belum ada
            if (!is_dir($direktori_ekstrak_game)) {
                mkdir($direktori_ekstrak_game, 0755, true);
            }
            // Ekstrak seluruh isi file zip ke dalam folder tersebut
            $zip->extractTo($direktori_ekstrak_game);
            $zip->close();
        } else {
            if (file_exists('uploads/games/' . $nama_banner_baru)) { unlink('uploads/games/' . $nama_banner_baru); }
            echo "<script>alert('Gagal mengekstrak berkas ZIP game. File arsip kemungkinan rusak.'); window.history.back();</script>";
            exit();
        }
    } else {
        if (file_exists('uploads/games/' . $nama_banner_baru)) { unlink('uploads/games/' . $nama_banner_baru); }
        echo "<script>alert('Wajib mengunggah berkas arsip .ZIP game Anda!'); window.history.back();</script>";
        exit();
    }

    // ==========================================================================
    // 4. PROSES SIMPAN DATA KE DATABASE (PREPARED STATEMENT)
    // ==========================================================================
    try {
        $query = "INSERT INTO games (judul, genre, deskripsi, banner, folder_game, pembuat, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt  = $koneksi->prepare($query);
        $stmt->bind_param("sssssss", $judul, $genre, $deskripsi, $nama_banner_baru, $folder_game, $pembuat, $tanggal);

        if ($stmt->execute()) {
            echo "<script>alert('Selamat! Hasil karya game Anda berhasil terbit dan diekstrak.'); window.location.href='game_browser.php';</script>";
            exit();
        } else {
            // Clean up files on database failure
            if (file_exists('uploads/games/' . $nama_banner_baru)) { unlink('uploads/games/' . $nama_banner_baru); }
            if (is_dir($direktori_ekstrak_game)) { array_map('unlink', glob("$direktori_ekstrak_game/*")); rmdir($direktori_ekstrak_game); }
            echo "<script>alert('Terjadi kegagalan memproses penyimpanan ke database.'); window.history.back();</script>";
            exit();
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        // Clean up files on crash
        if (file_exists('uploads/games/' . $nama_banner_baru)) { unlink('uploads/games/' . $nama_banner_baru); }
        if (is_dir($direktori_ekstrak_game)) { array_map('unlink', glob("$direktori_ekstrak_game/*")); rmdir($direktori_ekstrak_game); }
        
        if ($e->getCode() == 1452) {
            echo "<script>alert('Gagal! Identitas akun Anda tidak terdaftar sah di sistem database (Pelanggaran Relasi).'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Kesalahan Fatal: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        }
        exit();
    }
} else {
    header("Location: game_browser.php");
    exit();
}
?>