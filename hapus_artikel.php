<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memanggil jembatan koneksi database
include 'koneksi.php';

// PROTEKSI 1: Pastikan user sudah login demi mematuhi foreign key & keamanan
if (!isset($_SESSION['username']) || empty(trim($_SESSION['username']))) {
    header("Location: login.php");
    exit;
}

// PERUBAHAN KEAMANAN: Mengubah metode penanganan dari GET menjadi POST demi mendukung token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && !empty($_POST['id'])) {
    
    // --- TAMBAHAN KEAMANAN: VALIDASI CSRF TOKEN ---
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Request penghapusan tidak sah atau sesi form telah kedaluwarsa (CSRF Invalid).");
    }
    
    // Mengamankan parameter ID menggunakan casting integer
    $id = intval($_POST['id']);
    $user_login = strtolower(trim($_SESSION['username']));
    
    // 1. LANGKAH AMAN: Ambil informasi aset menggunakan Prepared Statement (Anti SQL Injection)
    $query_artikel = "SELECT gambar, file_pdf, pembuat FROM artikel WHERE id = ?";
    $stmt_ambil    = $koneksi->prepare($query_artikel);
    $stmt_ambil->bind_param("i", $id);
    $stmt_ambil->execute();
    $result_artikel = $stmt_ambil->get_result();
    $data_artikel   = $result_artikel->fetch_assoc();
    $stmt_ambil->close();
    
    // Memeriksa apakah artikel tersebut terdaftar di database
    if ($data_artikel) {
        $pembuat_artikel = strtolower(trim($data_artikel['pembuat']));
        
        // PROTEKSI 2: Hanya pembuat asli artikel ATAU admin yang boleh menghapus
        if ($user_login !== $pembuat_artikel && $user_login !== 'admin') {
            echo "<script>alert('Akses Ditolak! Anda tidak berhak menghapus artikel milik kreator lain.'); window.location.href='artikel.php';</script>";
            exit;
        }

        $jalur_gambar = trim($data_artikel['gambar']);
        $jalur_pdf    = trim($data_artikel['file_pdf']);
        
        // 2. LANGKAH PEMBERSIHAN BERKAS GAMBAR (SAMPUL)
        if (!empty($jalur_gambar) && file_exists($jalur_gambar)) {
            unlink($jalur_gambar);
        }
        
        // 3. LANGKAH PEMBERSIHAN BERKAS DOKUMEN (PDF LAMPIRAN)
        if (!empty($jalur_pdf) && file_exists($jalur_pdf)) {
            unlink($jalur_pdf);
        }
        
        // 4. LANGKAH EKSEKUSI: Hapus baris data artikel menggunakan Prepared Statement
        $query_hapus = "DELETE FROM artikel WHERE id = ?";
        $stmt_hapus  = $koneksi->prepare($query_hapus);
        $stmt_hapus->bind_param("i", $id);
        
        if ($stmt_hapus->execute()) {
            $stmt_hapus->close();
            echo "<script>
                    alert('Artikel, aset gambar sampul, dan berkas lampiran PDF berhasil dihapus bersih!');
                    window.location.href='artikel.php';
                  </script>";
            exit;
        } else {
            $error_db = addslashes($stmt_hapus->error);
            $stmt_hapus->close();
            echo "<script>alert('Gagal menghapus data dari database. Sistem Error: " . $error_db . "'); window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('Data artikel tidak ditemukan!'); window.location.href='artikel.php';</script>";
        exit;
    }
    
} else {
    header("Location: artikel.php");
    exit;
}
?>