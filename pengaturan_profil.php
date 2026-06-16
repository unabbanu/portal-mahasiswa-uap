<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi Halaman: Tendang ke halaman login jika user belum masuk sistem
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// --- TAMBAHAN KEAMANAN: GENERATE CSRF TOKEN JIKA BELUM ADA ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Panggil file koneksi database
require_once 'koneksi.php';

$pesan = '';
$tipe_pesan = '';

// Proses form saat tombol simpan ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- TAMBAHAN KEAMANAN: VALIDASI CSRF TOKEN ---
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Jika token tidak cocok, berhentikan skrip demi keamanan
        die("Error: Request pembaruan profil tidak sah atau sesi form telah kedaluwarsa (CSRF Invalid).");
    }

    $pesan = "Silakan pilih file foto terlebih dahulu.";
    $tipe_pesan = "gagal";

    // Proses Unggah Foto Avatar (Jika ada file yang dipilih)
    if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['avatar_file']['tmp_name'];
        $file_name = $_FILES['avatar_file']['name'];
        $file_size = $_FILES['avatar_file']['size'];
        
        // Validasi Ekstensi Gambar (Karena dikompres via canvas, outputnya akan selalu menjadi jpg/jpeg)
        $ekstensi_diperbolehkan = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi_file = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($ekstensi_file, $ekstensi_diperbolehkan)) {
            $pesan = "Format file salah! Hanya menerima JPG, JPEG, PNG, dan WEBP.";
            $tipe_pesan = "gagal";
        } 
        // Validasi Ukuran File (Maksimal 10MB setelah dikompresi)
        elseif ($file_size > 10 * 1024 * 1024) {
            $pesan = "Ukuran file hasil kompresi terlalu besar! Maksimal kapasitas file adalah 10MB.";
            $tipe_pesan = "gagal";
        } 
        // Eksekusi Pemindahan File & Query Database
        else {
            if (!is_dir('uploads/profil')) {
                mkdir('uploads/profil', 0755, true);
            }
            
            // Karena output canvas diubah ke JPEG, kita pakai ekstensi .jpg
            $nama_file_baru = 'avatar_' . $_SESSION['username'] . '_' . time() . '.jpg';
            $jalur_tujuan = 'uploads/profil/' . $nama_file_baru;
            
            if (move_uploaded_file($file_tmp, $jalur_tujuan)) {
                try {
                    $stmt = $koneksi->prepare("UPDATE user SET avatar = ? WHERE username = ?");
                    $stmt->bind_param("ss", $jalur_tujuan, $_SESSION['username']);
                    $stmt->execute();

                    if (isset($_SESSION['avatar']) && file_exists($_SESSION['avatar']) && strpos($_SESSION['avatar'], 'http') === false) {
                        unlink($_SESSION['avatar']);
                    }
                    
                    $_SESSION['avatar'] = $jalur_tujuan;
                    $pesan = "Foto profil berhasil diperbarui! Menyambungkan kembali...";
                    $tipe_pesan = "sukses";
                    $stmt->close();
                } catch (mysqli_sql_exception $e) {
                    if (file_exists($jalur_tujuan)) {
                        unlink($jalur_tujuan);
                    }
                    $pesan = "Gagal memperbarui database: " . $e->getMessage();
                    $tipe_pesan = "gagal";
                }
            } else {
                $pesan = "Gagal mengunggah file ke direktori server.";
                $tipe_pesan = "gagal";
            }
        }
    }
}

// Hubungkan dengan file navbar (Menjadi Header di posisi atas layar)
include 'header.php'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Foto Profil</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">
    <style>
        /* --- RESET DASAR --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* --- ANIMASI --- */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        /* --- BACKGROUND SEPERTI HALAMAN LOGIN --- */
        body { 
            background: linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), url('logo.png');
            background-size: 100px;
            background-repeat: repeat;
            min-height: 100vh;
        }

        /* --- PEMBUNGKUS BARU --- */
        .main-content {
            min-height: calc(100vh - 85px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* --- STYLE CONTAINER KARTU PENGATURAN --- */
        .page-container {
            width: 100%;
            max-width: 500px;
            animation: fadeInUp 0.8s ease-out;
        }

        .profile-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 35px 30px;
            color: #333;
        }

        .card-title {
            color: #6a0d6a;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
            border-bottom: 2px solid #f2e6f2;
            padding-bottom: 15px;
        }

        .avatar-upload-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .avatar-preview-large {
            width: 140px;
            height: 140px;
            border-radius: 16px;
            object-fit: cover;
            border: 3px solid #6a0d6a;
            box-shadow: 0 6px 15px rgba(106, 13, 106, 0.15);
            background-color: #eee;
        }

        .btn-custom-upload {
            background-color: #fcecfc;
            color: #6a0d6a;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            border: 1px dashed #6a0d6a;
            transition: all 0.2s ease;
        }
        .btn-custom-upload:hover {
            background-color: #6a0d6a;
            color: white;
            border-style: solid;
        }

        .alert-box {
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 0.95rem;
            text-align: center;
        }
        .alert-box.sukses { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-box.gagal { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

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
        .btn-submit-profile:disabled {
            background-color: #aaa;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        @media (max-width: 768px) {
            .profile-card { padding: 25px 20px; }
            .card-title { font-size: 1.3rem; }
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="page-container">
        <div class="profile-card">
            <h2 class="card-title">Perbarui Foto Profil</h2>

            <?php if (!empty($pesan)): ?>
                <div class="alert-box <?= $tipe_pesan; ?>">
                    <?= $pesan; ?>
                </div>
            <?php endif; ?>

            <form id="profileForm" action="pengaturan_profil.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="csrf_token" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="avatar-upload-section">
                    <img src="<?= isset($_SESSION['avatar']) ? htmlspecialchars($_SESSION['avatar']) : 'assets/default-avatar.png'; ?>" alt="Pratinjau Avatar" class="avatar-preview-large" id="avatarPreview">
                    <label for="avatar_file" class="btn-custom-upload">Pilih Foto Kotak Baru</label>
                    <input type="file" name="avatar_file" id="avatar_file" accept="image/*" style="display: none;">
                    
                    <small style="text-align: center; color: #777; line-height: 1.4;">
                        Format yang didukung: <strong>JPG, PNG, WEBP</strong><br>
                        Ukuran file otomatis dikompresi sistem.
                    </small>
                </div>
                <button type="submit" id="btnSubmit" class="btn-submit-profile">Simpan Foto Profil</button>
            </form>
        </div>
    </div>
</div>

<script>
// --- ALGORITMA: KOMPRESI FOTO NATIVE ONLY (Canvas API) ---
function compressImageNative(file, quality) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = event => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;

                const max_size = 1200; // Skala maksimal panjang gambar
                if (width > height) {
                    if (width > max_size) { height *= max_size / width; width = max_size; }
                } else {
                    if (height > max_size) { width *= max_size / height; height = max_size; }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                canvas.toBlob((blob) => {
                    resolve(blob);
                }, 'image/jpeg', quality);
            };
        };
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('avatar_file');
    const imagePreview = document.getElementById('avatarPreview');
    const profileForm = document.getElementById('profileForm');
    const btnSubmit = document.getElementById('btnSubmit');
    const csrfTokenInput = document.getElementById('csrf_token');

    // 1. Logika Pratinjau Gambar saat dipilih
    if (fileInput && imagePreview) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.addEventListener('load', function() {
                    imagePreview.setAttribute('src', this.result);
                });
                reader.readAsDataURL(file);
            }
        });
    }

    // 2. Cegat Pengiriman Form untuk Proses Kompresi terlebih dahulu
    if (profileForm) {
        profileForm.addEventListener('submit', async function(e) {
            const file = fileInput.files[0];
            
            // Jika user memilih file, lakukan kompresi sebelum dikirim
            if (file) {
                e.preventDefault(); // Tahan pengiriman form bawaan HTML
                btnSubmit.disabled = true;
                btnSubmit.innerText = 'Mengompres & Mengunggah...';

                // Jalankan fungsi kompresi gambar (Kualitas: 0.7 atau 70%)
                const compressedBlob = await compressImageNative(file, 0.7);

                // Bungkus blob hasil kompresi ke dalam objek File baru
                const compressedFile = new File([compressedBlob], file.name, {
                    type: 'image/jpeg',
                    lastModified: Date.now()
                });

                // PERUBAHAN UTAMA: Memasukkan token CSRF dan file terkompresi ke dalam FormData
                const formData = new FormData();
                formData.append('csrf_token', csrfTokenInput.value);
                formData.append('avatar_file', compressedFile);

                // Kirim ulang data form menggunakan Fetch API secara background ke PHP
                fetch('pengaturan_profil.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    // Refresh halaman untuk memicu pesan sukses/gagal dari PHP
                    document.open();
                    document.write(html);
                    document.close();
                })
                .catch(err => {
                    alert('Terjadi kesalahan saat mengunggah foto.');
                    btnSubmit.disabled = false;
                    btnSubmit.innerText = 'Simpan Foto Profil';
                });
            }
        });
    }

    // 3. Logika Pengalihan Otomatis Berjedah 2 Detik Setelah Sukses
    const alertSukses = document.querySelector('.alert-box.sukses');
    if (alertSukses) {
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 2000);
    }
});
</script>

</body>
</html>
<?php include 'footer.php'; ?>