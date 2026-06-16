<?php
// Mengamankan buffer output agar spasi liar di luar script tidak merusak JSON
ob_start();

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

include 'koneksi.php';

$response = [];

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    
    // 1. Ambil 30 notifikasi terbaru
    $query = "SELECT * FROM notifikasi WHERE user_target = ? ORDER BY id DESC LIMIT 30";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $user_pemicu_clean = htmlspecialchars(ucwords(str_replace('_', ' ', $row['user_pemicu'])));
        $waktu = date('d M, H:i', strtotime($row['created_at']));
        $id_sumber = $row['id_sumber'];
        
        if ($row['tipe'] === 'komen_artikel') {
            $icon = "📝";
            $pesan = "mengomentari Artikel Anda.";
            // Menuju halaman baca artikel, lalu melompat ke elemen ID diskusi/komentar tertentu
            $link = "baca_artikel.php?id=" . $id_sumber . "#discussion-counter";
        } elseif ($row['tipe'] === 'komen_sosial') {
            $icon = "💬";
            $pesan = "membalas kiriman Sosial Anda.";
            // Menuju halaman komunitas berdasarkan post ID, dan melompat ke container post tersebut
            $link = "komunitas.php?post=" . $id_sumber . "#post-" . $id_sumber; 
        } else {
            $icon = "❤️";
            $pesan = "menyukai postingan Sosial Anda.";
            // Menuju halaman komunitas berdasarkan post ID, dan melompat ke container post tersebut
            $link = "komunitas.php?post=" . $id_sumber . "#post-" . $id_sumber; 
        }
        
        $response[] = [
            'id' => (int)$row['id'],
            'user_pemicu' => $user_pemicu_clean,
            'icon' => $icon,
            'pesan' => $pesan,
            'link' => $link,
            'waktu' => $waktu . " WIB",
            'is_read' => (int)$row['is_read']
        ];
    }
    $stmt->close();

    // 2. PERBAIKAN UTAMA: Otomatis tandai semua notifikasi lama user ini sebagai "Sudah Dibaca"
    // Ini yang bertugas menghilangkan angka badge merah saat lonceng dibuka
    $update_query = "UPDATE notifikasi SET is_read = 1 WHERE user_target = ? AND is_read = 0";
    $stmt_update = $koneksi->prepare($update_query);
    $stmt_update->bind_param("s", $username);
    $stmt_update->execute();
    $stmt_update->close();
}

// Bersihkan data sampah/string tidak sengaja sebelum meluncurkan JSON
ob_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;