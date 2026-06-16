<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memanggil jembatan koneksi database
include 'koneksi.php';

// PROTEKSI 1: Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Memeriksa apakah parameter ID dikirimkan melalui URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id = intval($_GET['id']); // Mengamankan parameter ID menggunakan casting integer
    $user_login = strtolower(trim($_SESSION['username']));
    
    // 1. LANGKAH AMAN: Ambil informasi aset (banner, folder) dan pemilik game sebelum data dihapus
    $query_game = "SELECT banner, folder_game, pembuat FROM games WHERE id = '$id'";
    $sql_game   = mysqli_query($koneksi, $query_game);
    $data_game  = mysqli_fetch_array($sql_game);
    
    // Memeriksa apakah game tersebut terdaftar di database
    if ($data_game) {
        $pembuat_game = strtolower(trim($data_game['pembuat']));
        
        // PROTEKSI 2: Hanya pemilik asli game ATAU admin yang boleh menghapus
        if ($user_login !== $pembuat_game && $user_login !== 'admin') {
            echo "<script>alert('Akses Ditolak! Anda tidak berhak menghapus game milik kreator lain.'); window.location.href='game_browser.php';</script>";
            exit;
        }

        $nama_file_banner = $data_game['banner'];
        $nama_folder_game = $data_game['folder_game'];
        
        // 2. LANGKAH PEMBERSIHAN BERKAS GAMBAR (BANNER)
        if (!empty($nama_file_banner) && file_exists('uploads/games/' . $nama_file_banner)) {
            unlink('uploads/games/' . $nama_file_banner);
        }
        
        // 3. LANGKAH PEMBERSIHAN FOLDER GAME HTML5 SECARA REKURSIF
        $direktori_game = 'game/' . $nama_folder_game;
        if (!empty($nama_folder_game) && is_dir($direktori_game)) {
            // Panggil fungsi pembantu untuk menghapus folder beserta seluruh isinya (HTML, CSS, JS)
            hapusFolderRekursif($direktori_game);
        }
        
        // 4. LANGKAH EKSEKUSI: Hapus baris data game dari database
        $query_hapus = "DELETE FROM games WHERE id = '$id'";
        
        if (mysqli_query($koneksi, $query_hapus)) {
            echo "<script>
                    alert('Game, aset banner, dan berkas direktori HTML5 berhasil dihapus bersih!');
                    window.location.href='game_browser.php';
                  </script>";
            exit;
        } else {
            echo "Gagal menghapus data dari database: " . mysqli_error($koneksi);
        }
    } else {
        echo "<script>alert('Data game tidak ditemukan!'); window.location.href='game_browser.php';</script>";
        exit;
    }
    
} else {
    header("Location: game_browser.php");
    exit;
}

/**
 * Fungsi Pembantu untuk menghapus folder beserta seluruh file di dalamnya
 * karena fungsi rmdir() bawaan PHP hanya bisa menghapus folder yang sudah kosong.
 */
function hapusFolderRekursif($dir) {
    if (!is_dir($dir)) return;
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $jalur_target = $dir . DIRECTORY_SEPARATOR . $file;
        // Jika di dalam folder game ada sub-folder lagi, jalankan rekursif
        if (is_dir($jalur_target)) {
            hapusFolderRekursif($jalur_target);
        } else {
            unlink($jalur_target); // Hapus file tunggal
        }
    }
    return rmdir($dir); // Hapus folder utama setelah kosong melompong
}
?>