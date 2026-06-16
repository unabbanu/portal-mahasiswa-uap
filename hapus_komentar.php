<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
include 'koneksi.php';

// 1. Pastikan user sudah login dan parameter ID komentar tersedia
if (!isset($_SESSION['username']) || !isset($_GET['id'])) {
    header("Location: komunitas.php");
    exit();
}

// 2. Proteksi Akses: Validasi CSRF Token dari URL parameter GET
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Error: Request tidak sah (CSRF Token Invalid atau Kedaluwarsa).");
}

$comment_id = intval($_GET['id']);
$user_login = strtolower(trim($_SESSION['username']));

// 3. Ambil data komentar menggunakan Prepared Statement untuk cek siapa pemiliknya
$query_cek = "SELECT pembuat FROM komunitas_komentar WHERE id = ?";
$stmt_cek = $koneksi->prepare($query_cek);
$stmt_cek->bind_param("i", $comment_id);
$stmt_cek->execute();
$result_cek = $stmt_cek->get_result();

if ($result_cek && $result_cek->num_rows > 0) {
    $data = $result_cek->fetch_assoc();
    $pembuat_komentar = strtolower(trim($data['pembuat']));
    $stmt_cek->close(); // Tutup statement cek setelah data diambil

    // 4. Validasi Keamanan: Hanya boleh dihapus jika pemiliknya sendiri atau dia adalah admin
    if ($user_login === $pembuat_komentar || $user_login === 'admin') {
        
        // Eksekusi penghapusan menggunakan Prepared Statement
        $query_hapus = "DELETE FROM komunitas_komentar WHERE id = ?";
        $stmt_hapus = $koneksi->prepare($query_hapus);
        $stmt_hapus->bind_param("i", $comment_id);
        
        if ($stmt_hapus->execute()) {
            $stmt_hapus->close();
            echo "<script>
                    alert('Komentar berhasil dihapus!');
                    window.location.href = 'komunitas.php';
                  </script>";
            exit();
        } else {
            $stmt_hapus->close();
            echo "<script>
                    alert('Gagal menghapus komentar dari database.');
                    window.location.href = 'komunitas.php';
                  </script>";
            exit();
        }
    } else {
        // Jika mencoba menghapus komentar orang lain lewat manipulasi URL (IDOR / Bypassing)
        echo "<script>
                alert('Akses Ditolak! Anda tidak berhak menghapus komentar ini.');
                window.location.href = 'komunitas.php';
              </script>";
        exit();
    }
} else {
    $stmt_cek->close();
    echo "<script>
            alert('Komentar tidak ditemukan.');
            window.location.href = 'komunitas.php';
          </script>";
    exit();
}
?>