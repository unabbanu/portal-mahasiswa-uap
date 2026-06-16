<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'koneksi.php';

// Cek apakah request meminta respon JSON (via AJAX Fetch)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || (isset($_GET['format']) && $_GET['format'] == 'json');

// 1. Proteksi Akses: Tolak jika pengguna belum login masuk sistem atau parameter ID kosong
if (!isset($_SESSION['username']) || !isset($_GET['id'])) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login terlebih dahulu.']);
        exit();
    } else {
        header("Location: komunitas.php");
        exit();
    }
}

// 2. Proteksi Akses: Validasi CSRF Token dari URL parameter GET
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Token keamanan CSRF tidak valid atau kedaluwarsa.']);
        exit();
    } else {
        die("Error: Request tidak sah (CSRF Token Invalid atau Kedaluwarsa).");
    }
}

$post_id = intval($_GET['id']);
$username = $_SESSION['username'];

try {
    // Cek apakah user sudah pernah memberikan Like pada post ini
    $stmt_cek = $koneksi->prepare("SELECT id FROM komunitas_likes WHERE post_id = ? AND username = ?");
    $stmt_cek->bind_param("is", $post_id, $username);
    $stmt_cek->execute();
    $result = $stmt_cek->get_result();

    if ($result->num_rows > 0) {
        // Jika sudah ada, hapus Like (Unlike)
        $stmt_un = $koneksi->prepare("DELETE FROM komunitas_likes WHERE post_id = ? AND username = ?");
        $stmt_un->bind_param("is", $post_id, $username);
        $stmt_un->execute();
        $stmt_un->close();

        // ====================================================================
        // Hapus riwayat notifikasi jika user membatalkan (Unlike)
        // ====================================================================
        $tipe_notif = 'like_sosial';
        $stmt_del_notif = $koneksi->prepare("DELETE FROM notifikasi WHERE user_pemicu = ? AND tipe = ? AND id_sumber = ?");
        $stmt_del_notif->bind_param("ssi", $username, $tipe_notif, $post_id);
        $stmt_del_notif->execute();
        $stmt_del_notif->close();

    } else {
        // Jika belum ada, tambah data Like Baru
        $stmt_like = $koneksi->prepare("INSERT INTO komunitas_likes (post_id, username) VALUES (?, ?)");
        $stmt_like->bind_param("is", $post_id, $username);
        
        if ($stmt_like->execute()) {
            // ====================================================================
            // TRIGGER NOTIFIKASI: Cari tahu siapa pemilik asli postingan komunitas
            // ====================================================================
            $stmt_owner = $koneksi->prepare("SELECT pembuat FROM komunitas WHERE id = ?");
            $stmt_owner->bind_param("i", $post_id);
            $stmt_owner->execute();
            $res_owner = $stmt_owner->get_result();

            if ($res_owner->num_rows === 1) {
                $owner_data = $res_owner->fetch_assoc();
                $user_target = $owner_data['pembuat'];

                // Kirim notifikasi hanya jika yang menekan LIKE adalah ORANG LAIN (bukan dirinya sendiri)
                if ($username !== $user_target) {
                    $tipe_notif = 'like_sosial';
                    $stmt_notif = $koneksi->prepare("INSERT INTO notifikasi (user_target, user_pemicu, tipe, id_sumber) VALUES (?, ?, ?, ?)");
                    $stmt_notif->bind_param("sssi", $user_target, $username, $tipe_notif, $post_id);
                    $stmt_notif->execute();
                    $stmt_notif->close();
                }
            }
            $stmt_owner->close();
        }
        $stmt_like->close();
    }
    $stmt_cek->close();

    // ====================================================================
    // RESPON BALIKAN DATA (AJAX vs NORMAL REFRESH)
    // ====================================================================
    if ($is_ajax) {
        header('Content-Type: application/json');

        // Ambil total count like terbaru & daftar nama barunya untuk render dropdown instan
        $stmt_total = $koneksi->prepare("SELECT COUNT(*) as total, GROUP_CONCAT(username SEPARATOR ', ') as daftar_pemberi FROM komunitas_likes WHERE post_id = ?");
        $stmt_total->bind_param("i", $post_id);
        $stmt_total->execute();
        $res_total = $stmt_total->get_result()->fetch_assoc();
        $stmt_total->close();

        $total_likes = (int)$res_total['total'];
        $pemberi_raw = !empty($res_total['daftar_pemberi']) ? explode(', ', $res_total['daftar_pemberi']) : [];
        
        // Format teks nama (Kapital/Ucwords) agar serasi dengan JavaScript di komunitas.php
        $pemberi_formatted = array_map(function($name) {
            return ucwords(str_replace('_', ' ', $name));
        }, $pemberi_raw);

        echo json_encode([
            'success' => true,
            'total_likes' => $total_likes,
            'pemberi' => $pemberi_formatted
        ]);
        exit();
    }

} catch (mysqli_sql_exception $e) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Gagal memproses Like ke database.']);
        exit();
    }
}

// Fallback jika diakses manual/bukan AJAX, kembalikan posisi halaman seperti biasa
header("Location: komunitas.php#post-" . $post_id);
exit();
?>