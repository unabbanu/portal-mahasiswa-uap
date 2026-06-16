<?php
// 1. Memulai session wajib berada di baris paling atas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Hubungkan jembatan database dari folder yang sama
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // =====================================================================
    // PERTAHANAN 1: Filter User-Agent (Memblokir Automated Script / cURL Standar)
    // =====================================================================
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if (preg_match('/(curl|postman|wget|python|go-http-client|java|nikto|dirbuster)/i', $userAgent)) {
        http_response_code(403);
        die("Akses Ilegal Terdeteksi. Request Anda diblokir oleh sistem keamanan.");
    }

    // =====================================================================
    // PERTAHANAN 2: Anti-CSRF Token (Mencegah Replay Attack via Burp Suite)
    // =====================================================================
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        header("Location: login.php?error=invalid_csrf");
        exit;
    }

    // =====================================================================
    // PERTAHANAN 3: Sederhana Rate Limiting (Mencegah Brute Force via Burp Intruder)
    // =====================================================================
    if (!isset($_SESSION['last_login_attempt'])) {
        $_SESSION['last_login_attempt'] = time();
        $_SESSION['login_attempts'] = 1;
    } else {
        // Jika request dikirim dalam waktu kurang dari 2 detik dari request sebelumnya
        if (time() - $_SESSION['last_login_attempt'] < 2) {
            $_SESSION['login_attempts']++;
        } else {
            $_SESSION['login_attempts'] = 1; // Reset jika jeda waktu wajar
        }
        
        $_SESSION['last_login_attempt'] = time();

        // Jika melakukan request terlalu cepat berturut-turut (deteksi otomatisasi robot)
        if ($_SESSION['login_attempts'] > 5) {
            http_response_code(429);
            echo "<script>alert('Terlalu banyak percobaan login dalam waktu singkat. Harap tunggu sebentar.'); window.history.back();</script>";
            exit;
        }
    }


    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 3. Menggunakan Prepared Statements untuk mengambil data user beserta status login
    $query = "SELECT id, username, password, avatar, is_logged_in, last_activity FROM user WHERE username = ?";
    $stmt  = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $data = mysqli_fetch_assoc($result);

        // Pencocokan input teks biasa dengan string hash di DB
        if (password_verify($password, $data['password'])) {
            
            // =====================================================================
            // VALIDASI LOGIKAL: Proteksi Blokir Login Ganda (Strict Single Login)
            // =====================================================================
            $timeout_minutes = 10; // Toleransi waktu aktivitas (dalam menit)
            $is_session_active = false;

            if ($data['is_logged_in'] == 1 && !empty($data['last_activity'])) {
                $last_active_time = strtotime($data['last_activity']);
                $current_time = time();
                
                // Cek apakah aktivitas terakhir masih di bawah batas toleransi menit
                if (($current_time - $last_active_time) < ($timeout_minutes * 60)) {
                    $is_session_active = true;
                }
            }

            // Jika status masih ditandai login DAN belum kedaluwarsa, blokir login baru
            if ($is_session_active) {
                mysqli_stmt_close($stmt);
                header("Location: login.php?error=already_logged_in");
                exit;
            }
            
            // =====================================================================
            // PERTAHANAN 4: Proteksi Session (Mencegah Session Hijacking)
            // =====================================================================
            session_regenerate_id(true); // Mengganti ID session yang lama dengan yang baru setelah login sukses

            // Daftarkan session username utama
            $_SESSION['user_id']  = $data['id']; // Menyimpan ID user untuk keperluan update middleware aktivitas
            $_SESSION['username'] = $data['username'];
            
            // SERAGAM: Mengubah semua bentuk username menjadi satu format entitas nama yang sama
            $_SESSION['nama_lengkap'] = ucwords(str_replace('_', ' ', $data['username']));
            
            // SERAGAM: Identitas peran diisi teks instansi global yang setara tanpa membedakan role
            $_SESSION['role']         = 'Sivitas FTI UAP'; 
            
            // Bersihkan teks path dari spasi tak terlihat
            $db_avatar = trim($data['avatar']); 
            
            // DISELARASKAN: Menggunakan default path 'uploads/avatar_default.png' sesuai validasi header.php
            if (empty($db_avatar) || $db_avatar === 'uploads/avatar_default.png' || $db_avatar === 'assets/default-avatar.png') {
                $_SESSION['avatar'] = 'uploads/avatar_default.png';
            } else {
                $_SESSION['avatar'] = $db_avatar;
            }
            
            // =====================================================================
            // UPDATE DATABASE: Set status menjadi aktif login dan catat waktunya
            // =====================================================================
            $update_query = "UPDATE user SET is_logged_in = 1, last_activity = NOW() WHERE id = ?";
            $update_stmt = mysqli_prepare($koneksi, $update_query);
            mysqli_stmt_bind_param($update_stmt, "i", $data['id']);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);

            // =====================================================================
            // PERTAHANAN 5: Penghancuran Token Lama
            // =====================================================================
            unset($_SESSION['csrf_token']); // Token dihapus setelah berhasil login agar tidak bisa dipakai ulang
            unset($_SESSION['login_attempts']);

            mysqli_stmt_close($stmt);
            echo "<script>alert('Login Berhasil! Selamat Datang.'); window.location.href='index.php';</script>";
            exit;
            
        } else {
            mysqli_stmt_close($stmt);
            header("Location: login.php?error=wrong_credentials");
            exit;
        }
    } else {
        mysqli_stmt_close($stmt);
        header("Location: login.php?error=wrong_credentials");
        exit;
    }

} else {
    header('Location: login.php'); 
    exit;
}
?>