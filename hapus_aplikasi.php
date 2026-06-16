<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi.php';

// Proteksi login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $user_login = strtolower(trim($_SESSION['username']));
    
    // Ambil info ikon dan pengunggah
    $query_app = "SELECT ikon, pengunggah FROM aplikasi WHERE id = '$id'";
    $sql_app   = mysqli_query($koneksi, $query_app);
    $data_app  = mysqli_fetch_array($sql_app);
    
    if ($data_app) {
        $pengunggah_app = strtolower(trim($data_app['pengunggah']));
        
        // Proteksi hak akses
        if ($user_login !== $pengunggah_app && $user_login !== 'admin') {
            echo "<script>alert('Akses Ditolak!'); window.location.href='aplikasi_mobile.php';</script>";
            exit;
        }

        // Hapus berkas ikon fisik
        $nama_file_ikon = $data_app['ikon'];
        if (!empty($nama_file_ikon) && file_exists('uploads/apps/' . $nama_file_ikon)) {
            unlink('uploads/apps/' . $nama_file_ikon);
        }
        
        // Hapus data dari database
        $query_hapus = "DELETE FROM aplikasi WHERE id = '$id'";
        if (mysqli_query($koneksi, $query_hapus)) {
            echo "<script>
                    alert('Data aplikasi dan berkas ikon berhasil dihapus permanen!');
                    window.location.href='aplikasi_mobile.php';
                  </script>";
            exit;
        } else {
            echo "Gagal menghapus data dari database: " . mysqli_error($koneksi);
        }
    } else {
        echo "<script>alert('Data tidak ditemukan!'); window.location.href='aplikasi_mobile.php';</script>";
        exit;
    }
} else {
    header("Location: aplikasi_mobile.php");
    exit;
}
?>