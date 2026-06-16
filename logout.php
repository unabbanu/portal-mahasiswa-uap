<?php
// 1. Memulai session wajib berada di baris paling atas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Hubungkan koneksi database untuk memperbarui status login
include 'koneksi.php';

// 3. Jika ID user tersimpan di session, ubah statusnya di DB menjadi 0 (Bebas)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Gunakan prepared statement agar aman dari SQL Injection
    $query_logout = "UPDATE user SET is_logged_in = 0 WHERE id = ?";
    $stmt_logout  = mysqli_prepare($koneksi, $query_logout);
    mysqli_stmt_bind_param($stmt_logout, "i", $user_id);
    mysqli_stmt_execute($stmt_logout);
    mysqli_stmt_close($stmt_logout);
}

// 4. Proses pembersihan total session dari browser dan server
session_unset();     // Mengosongkan semua data session
session_destroy();   // Menghancurkan session di server

// 5. Arahkan kembali ke halaman utama atau halaman login
echo "<script>alert('Anda telah berhasil keluar sistem.'); window.location.href='login.php';</script>";
exit;
?>