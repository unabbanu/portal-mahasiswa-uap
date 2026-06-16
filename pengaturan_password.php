<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
require_once 'koneksi.php';

// Proteksi halaman wajib login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// --- TAMBAHAN KEAMANAN: GENERATE CSRF TOKEN JIKA BELUM ADA ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$username = $_SESSION['username'];
$error_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- TAMBAHAN KEAMANAN: VALIDASI CSRF TOKEN ---
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Request tidak sah atau sesi form telah kedaluwarsa (CSRF Invalid).");
    }

    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        $error_msg = "Semua kolom wajib diisi!";
    } elseif ($password_baru !== $konfirmasi_password) {
        $error_msg = "Konfirmasi password baru tidak cocok!";
    } elseif (strlen($password_baru) < 4) { 
        $error_msg = "Password baru terlalu pendek (Minimal 4 karakter)!";
    } else {
        // 1. Ambil data password (hash) dari database untuk dicocokkan
        $stmt = $koneksi->prepare("SELECT password FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $user_data = $res->fetch_assoc();
        $stmt->close();

        if ($user_data) {
            $password_db = $user_data['password'];
            
            // 2. Verifikasi password lama menggunakan password_verify() agar sinkron dengan proses login hash
            if (password_verify($password_lama, $password_db)) {
                
                // 3. ENKRIPSI AKTIF: Mengubah password baru menjadi string hash aman (BCRYPT)
                $password_baru_hashed = password_hash($password_baru, PASSWORD_BCRYPT);
                
                $update_stmt = $koneksi->prepare("UPDATE user SET password = ? WHERE username = ?");
                
                // Menggunakan variabel $password_baru_hashed yang telah di-hash aman
                $update_stmt->bind_param("ss", $password_baru_hashed, $username); 
                
                if ($update_stmt->execute()) {
                    $success_msg = "Password berhasil diperbarui!";
                } else {
                    $error_msg = "Gagal memperbarui password pada database.";
                }
                $update_stmt->close();
            } else {
                $error_msg = "Password lama yang Anda masukkan salah!";
            }
        } else {
            $error_msg = "Pengguna tidak ditemukan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Kata Sandi</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        body { 
            width: 100%;
            min-height: 100vh;
            background: linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), url('logo.png');
            background-size: 100px; background-repeat: repeat;
        }

        .main-content {
            min-height: calc(100vh - 85px);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 40px 20px;
        }

        .password-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            width: 100%; 
            max-width: 500px; 
            border-radius: 16px; 
            padding: 35px 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            animation: fadeIn 0.6s ease-out; 
            color: #333;
        }

        h3 { color: #6a0d6a; margin-bottom: 25px; font-weight: 700; font-size: 1.6rem; border-bottom: 2px solid #f2e6f2; padding-bottom: 10px; text-align: center; }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 12px;
            color: #6a0d6a;
        }

        .input-field {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border-radius: 8px;
            border: 2px solid #ddd;
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
        }

        .input-field:focus {
            border-color: #6a0d6a;
            box-shadow: 0 0 8px rgba(106, 13, 106, 0.15);
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            font-weight: 600;
            text-align: center;
        }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        .btn-group-nav { display: flex; gap: 12px; justify-content: space-between; margin-top: 30px; }

        .btn-submit {
            background: #6a0d6a; color: white; border: none;
            padding: 12px 25px; border-radius: 8px; font-weight: bold;
            font-size: 1rem; cursor: pointer; transition: 0.3s;
            box-shadow: 0 4px 12px rgba(106, 13, 106, 0.2);
            flex: 1;
        }
        .btn-submit:hover { 
            background: #4a094a; 
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(106, 13, 106, 0.3); 
        }

        .btn-batal {
            background-color: white; color: #666; border: 2px solid #ddd;
            padding: 12px 25px; border-radius: 8px; font-weight: bold;
            text-decoration: none; font-size: 1rem; text-align: center; transition: 0.3s;
            flex: 1;
        }
        .btn-batal:hover { background-color: #f5f5f5; color: #333; }

        @media (max-width: 576px) {
            .btn-group-nav { flex-direction: column-reverse; gap: 10px; }
            .btn-submit, .btn-batal { width: 100%; }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main-content">
    <div class="password-container">
        <h3><i class="fa-solid fa-key"></i> Ganti Kata Sandi</h3>
        
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?= $error_msg; ?></div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?= $success_msg; ?></div>
            <script>
                setTimeout(function() { window.location.href = 'index.php'; }, 2000);
            </script>
        <?php endif; ?>

        <form action="pengaturan_password.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-group">
                <label for="password_lama">Kata Sandi Lama</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock-open"></i>
                    <input type="password" name="password_lama" id="password_lama" class="input-field" placeholder="Masukkan kata sandi lama" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password_baru">Kata Sandi Baru</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password_baru" id="password_baru" class="input-field" placeholder="Masukkan kata sandi baru" required>
                </div>
            </div>

            <div class="form-group">
                <label for="konfirmasi_password">Konfirmasi Kata Sandi Baru</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-check-double"></i>
                    <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="input-field" placeholder="Ulangi kata sandi baru" required>
                </div>
            </div>

            <div class="btn-group-nav">
                <a href="index.php" class="btn-batal">BATAL</a>
                <button type="submit" class="btn-submit">SIMPAN PERUBAHAN</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>