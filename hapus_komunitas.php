<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
require_once 'koneksi.php';

// 1. PROTEKSI HAK AKSES
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 2. PROSES VALIDASI METODE POST DAN CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Token keamanan tidak valid.");
    }

    $id_postingan = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $username_login = strtolower(trim($_SESSION['username']));

    if ($id_postingan > 0) {
        try {
            // PERBAIKAN: Menggunakan kolom 'pembuat' sesuai dengan struktur tabel komunitas Anda
            $stmt_select = $koneksi->prepare("SELECT gambar, video, pembuat FROM komunitas WHERE id = ?");
            $stmt_select->bind_param("i", $id_postingan);
            $stmt_select->execute();
            $result = $stmt_select->get_result();
            
            if ($data = $result->fetch_assoc()) {
                $pembuat_postingan = strtolower(trim($data['pembuat']));

                // VALIDASI HAK AKSES: Pemilik asli atau akun bertindak sebagai 'admin'
                if ($pembuat_postingan !== $username_login && $username_login !== 'admin') {
                    echo "<script>alert('Anda tidak memiliki izin untuk menghapus postingan ini.'); window.location.href='komunitas.php';</script>";
                    exit();
                }

                $jalur_gambar = trim($data['gambar']);
                $jalur_video = trim($data['video']);

                // 3. PROSES HAPUS FILE FISIK
                if (!empty($jalur_gambar) && file_exists($jalur_gambar)) {
                    unlink($jalur_gambar);
                }
                
                if (!empty($jalur_video) && file_exists($jalur_video)) {
                    unlink($jalur_video);
                }

                // 4. HAPUS DATA DARI DATABASE
                $stmt_delete = $koneksi->prepare("DELETE FROM komunitas WHERE id = ?");
                $stmt_delete->bind_param("i", $id_postingan);
                $stmt_delete->execute();
                $stmt_delete->close();

                echo "<script>alert('Postingan berhasil dihapus.'); window.location.href='komunitas.php';</script>";
                exit();
            } else {
                echo "<script>alert('Postingan tidak ditemukan.'); window.location.href='komunitas.php';</script>";
                exit();
            }
            $stmt_select->close();

        } catch (mysqli_sql_exception $e) {
            // Jika ada kendala query database, cetak pesan error aslinya demi mempermudah debug
            echo "<script>alert('Gagal menghapus postingan: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
            exit();
        }
    }
} else {
    header("Location: komunitas.php");
    exit();
}
?>