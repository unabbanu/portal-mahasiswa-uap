<?php
// 1. Jalankan session di bagian paling atas sebelum ada output HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Buat CSRF Token jika belum ada dalam session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login User</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">

    <?php include 'header.php';  ?>

    <style>
        /* --- RESET DASAR --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* --- ANIMASI --- */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        /* --- BACKGROUND --- */
        body { 
            background: linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), url('logo.png');
            background-size: 100px;
            background-repeat: repeat;
            min-height: 100vh;
        }

        .main-content {
            min-height: calc(100vh - 85px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .page-container {
            width: 100%;
            max-width: 500px;
            animation: fadeInUp 0.8s ease-out;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .login-welcome-text {
            color: white;
            text-align: center;
        }
        .login-welcome-text h1 { 
            font-size: 2.3rem; 
            margin-bottom: 10px; 
            font-weight: 700;
        }
        .login-welcome-text p { 
            font-size: 1.1rem; 
            opacity: 0.95; 
            line-height: 1.4;
        }

        .profile-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 35px 30px;
            color: #333;
            border-top: 5px solid #6a0d6a;
        }

        .card-title {
            color: #6a0d6a;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
            border-bottom: 2px solid #f2e6f2;
            padding-bottom: 15px;
        }

        /* --- NOTIFIKASI ERROR --- */
        .alert-error {
            background-color: #fdf2f2;
            border: 1px solid #f8b4b4;
            color: #9b1c1c;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.4;
            text-align: center;
        }

        .form-group { 
            margin-bottom: 20px; 
            text-align: left; 
        }
        .form-group label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 8px; 
            color: #555; 
            font-size: 0.95rem; 
        }
        .form-group input { 
            width: 100%; 
            padding: 12px 15px; 
            border: 2px solid #ddd; 
            border-radius: 8px; 
            font-size: 1rem; 
            transition: all 0.3s ease; 
            color: #333; 
        }
        .form-group input:focus { 
            border-color: #6a0d6a; 
            outline: none; 
            box-shadow: 0 0 8px rgba(106, 13, 106, 0.15); 
        }

        .btn-submit-profile {
            width: 100%;
            background-color: #6a0d6a;
            color: white;
            border: none;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(106, 13, 106, 0.2);
        }
        .btn-submit-profile:hover {
            background-color: #4a094a;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(106, 13, 106, 0.3);
        }

        @media (max-width: 768px) {
            .main-content { padding: 30px 15px; }
            .profile-card { padding: 25px 20px; }
            .card-title { font-size: 1rem; }
            .login-welcome-text h1 { font-size: 1.8rem; }
            .login-welcome-text p { font-size: 0.95rem; }
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="page-container">
        
        <div class="login-welcome-text">
            <h1>Selamat Datang</h1>
            <p>Silakan masuk untuk mengakses layanan FTI HUB UAP</p>
        </div>

        <div class="profile-card">
            
            <div class="card-title">
                <h2>LOGIN USER</h2>
                <p>Yang Tidak Bisa Login atau Lupa Password</p>
                <p>Silakan Menghubungi Admin (Banu)</p>
            </div>

            <!-- BLOK KONDISI UNTUK MENAMPILKAN PESAN ERROR -->
            <?php if (isset($_GET['error'])): ?>
                <div class="alert-error">
                    <?php 
                    if ($_GET['error'] == 'already_logged_in') {
                        echo "⚠️ Akun sedang aktif di perangkat lain. Silakan logout dari perangkat tersebut terlebih dahulu.";
                    } elseif ($_GET['error'] == 'wrong_credentials') {
                        echo "❌ Username atau Password salah.";
                    } elseif ($_GET['error'] == 'invalid_csrf') {
                        echo "❌ Akses tidak valid (CSRF Token Expired).";
                    } else {
                        echo "❌ Terjadi kesalahan, silakan coba lagi.";
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <form action="proses_login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div class="form-group">
                    <label for="username">Username / Nama Lengkap</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="password">NPM (Nomor Pokok Mahasiswa)</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan NPM Anda" required>
                </div>
                <button type="submit" class="btn-submit-profile">MASUK SEKARANG</button>
            </form>
        </div>

    </div>
</div>

</body>
</html>
<?php 
include 'footer.php'; 
?>