<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Hubungkan koneksi database MySQLi Anda
require_once 'koneksi.php';

// Cek apakah request meminta respon JSON (lewat mekanisme Fetch JavaScript AJAX)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == '1');

// 1. Proteksi Akses: Tolak jika pengguna belum login masuk sistem
if (!isset($_SESSION['username'])) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}

// 2. Proteksi Akses: Validasi CSRF Token dari serangan luar
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Token keamanan CSRF tidak valid atau kedaluwarsa.']);
        exit();
    } else {
        die("Error: Request tidak sah (CSRF Token Invalid atau Kedaluwarsa).");
    }
}

// 3. Validasi Metode Pengiriman Data POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $post_id  = intval($_POST['post_id']);
    
    // SINKRONISASI VARIABEL: Menangkap string berdasarkan atribut name="isi_komentar" dari form
    $komentar = isset($_POST['isi_komentar']) ? trim($_POST['isi_komentar']) : '';
    $pembuat  = $_SESSION['username'];

    // 4. Eksekusi Penyimpanan jika Kolom Komentar Tidak Kosong
    if (!empty($komentar)) {
        try {
            // Menggunakan Prepared Statement untuk keamanan penuh dari SQL Injection
            $query = "INSERT INTO komunitas_komentar (post_id, pembuat, komentar) VALUES (?, ?, ?)";
            $stmt  = $koneksi->prepare($query);
            $stmt->bind_param("iss", $post_id, $pembuat, $komentar);
            
            // Jika komentar sukses tersimpan, picu logika notifikasi
            if ($stmt->execute()) {
                $inserted_id = $koneksi->insert_id; // Ambil ID komentar barusan untuk link hapus
                
                // ====================================================================
                // TRIGGER NOTIFIKASI: Cari tahu siapa pemilik asli postingan komunitas
                // ====================================================================
                $query_owner = "SELECT pembuat FROM komunitas WHERE id = ?";
                $stmt_owner = $koneksi->prepare($query_owner);
                $stmt_owner->bind_param("i", $post_id);
                $stmt_owner->execute();
                $res_owner = $stmt_owner->get_result();

                if ($res_owner->num_rows === 1) {
                    $owner_data = $res_owner->fetch_assoc();
                    $user_target = $owner_data['pembuat'];

                    // Kirim notifikasi hanya jika komentator adalah ORANG LAIN (bukan dirinya sendiri)
                    if ($pembuat !== $user_target) {
                        $tipe_notif = 'komen_sosial';
                        $query_notif = "INSERT INTO notifikasi (user_target, user_pemicu, tipe, id_sumber) VALUES (?, ?, ?, ?)";
                        $stmt_notif = $koneksi->prepare($query_notif);
                        $stmt_notif->bind_param("sssi", $user_target, $pembuat, $tipe_notif, $post_id);
                        $stmt_notif->execute();
                        $stmt_notif->close();
                    }
                }
                $stmt_owner->close();

                // ====================================================================
                // RESPON OUTPUT: Bedakan output jika menggunakan AJAX vs Biasa
                // ====================================================================
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    
                    // Tarik data foto profil user pengomentar saat ini
                    $stmt_user = $koneksi->prepare("SELECT avatar FROM user WHERE LOWER(TRIM(username)) = LOWER(TRIM(?))");
                    $stmt_user->bind_param("s", $pembuat);
                    $stmt_user->execute();
                    $res_user = $stmt_user->get_result()->fetch_assoc();
                    $stmt_user->close();
                    
                    $avatar_path = (!empty($res_user['avatar']) && file_exists($res_user['avatar'])) ? $res_user['avatar'] : 'assets/default-avatar.png';
                    $nama_tampilan = ucwords(str_replace('_', ' ', $pembuat));

                    // Siapkan cetakan HTML struktur komentar baru agar bisa langsung disisipkan JavaScript
                    $html_komentar = '
                    <div class="comment-item" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 10px;">
                        <div style="display: flex; gap: 10px; align-items: flex-start; flex: 1;">
                            <img src="'.htmlspecialchars($avatar_path).'" class="comment-avatar image-clickable" alt="User">
                            <div class="comment-box">
                                <span class="comment-user">'.htmlspecialchars($nama_tampilan).'</span>
                                <span style="color:#333;">'.htmlspecialchars($komentar).'</span>
                            </div>
                        </div>
                        <a href="hapus_komentar.php?id='.$inserted_id.'&csrf_token='.$_SESSION['csrf_token'].'" 
                            style="text-decoration: none; color: #dc3545; font-size: 0.75rem; font-weight: bold; margin-top: 8px; white-space: nowrap;" 
                            onclick="return confirm(\'Apakah Anda yakin ingin menghapus komentar ini?\')">
                            ❌ Hapus
                        </a>
                    </div>';

                    // Hitung total komentar terbaru untuk update angka di halaman depan
                    $stmt_count = $koneksi->prepare("SELECT COUNT(*) as total FROM komunitas_komentar WHERE post_id = ?");
                    $stmt_count->bind_param("i", $post_id);
                    $stmt_count->execute();
                    $res_count = $stmt_count->get_result()->fetch_assoc();
                    $stmt_count->close();

                    echo json_encode([
                        'success' => true,
                        'html' => $html_komentar,
                        'total_comments' => (int)$res_count['total']
                    ]);
                    exit();
                }
            }
            $stmt->close();
            
        } catch (mysqli_sql_exception $e) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan komentar ke database: ' . $e->getMessage()]);
                exit();
            } else {
                echo "<script>alert('Gagal mengirim komentar: " . addslashes($e->getMessage()) . "'); window.location.href='komunitas.php';</script>";
                exit();
            }
        }
    } else {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Isi komentar tidak boleh kosong!']);
            exit();
        }
    }
    
    // Fallback jika tidak menggunakan AJAX (request form submit standar)
    header("Location: komunitas.php#post-" . $post_id);
    exit();
}

// Alihkan jika file diakses langsung tanpa form POST
header("Location: komunitas.php");
exit();
?>