<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. PROTEKSI KEAMANAN: Hanya admin yang boleh masuk halaman ini
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: beranda.php");
    exit();
}

// 2. KONEKSI DATABASE
include 'koneksi.php'; 

$pesan = "";
$tipe_pesan = "";

/**
 * Fungsi Pembantu untuk menghapus folder beserta seluruh file di dalamnya
 */
function hapusFolderRekursif($dir) {
    if (!is_dir($dir)) return;
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $jalur_target = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($jalur_target)) {
            hapusFolderRekursif($jalur_target);
        } else {
            unlink($jalur_target); 
        }
    }
    return rmdir($dir); 
}

// 3. PROSES LOGIKA: TAMBAH USER (DENGAN ENKRIPSI HASH)
if (isset($_POST['tambah_user'])) {
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password_raw = $_POST['password'];
    
    // ENKRIPSI PASSWORD MENGGUNAKAN BCRYPT HASH
    $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);
    
    $avatar_path = "uploads/profil/default.jpg"; 

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $file_name = $_FILES['avatar']['name'];
        $file_tmp = $_FILES['avatar']['tmp_name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = "avatar_" . $username . "_" . time() . "." . $file_ext;
        $target_dir = "uploads/profil/" . $new_file_name;

        if (move_uploaded_file($file_tmp, $target_dir)) {
            $avatar_path = $target_dir;
        }
    }

    $cek_user = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        $pesan = "Gagal! Username sudah digunakan.";
        $tipe_pesan = "error";
    } else {
        $query = "INSERT INTO user (username, password, avatar, created_at) VALUES ('$username', '$password_hashed', '$avatar_path', NOW())";
        if (mysqli_query($koneksi, $query)) {
            $pesan = "User berhasil ditambahkan dengan aman (Password Hashed)!";
            $tipe_pesan = "sukses";
        }
    }
}

// 4. PROSES LOGIKA: EDIT USER (UPDATE DATA)
if (isset($_POST['edit_user'])) {
    $id_user = mysqli_real_escape_string($koneksi, $_POST['id_user']);
    $username_baru = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password_baru = $_POST['password'];

    // Ambil data user lama untuk pengecekan aset
    $cari_user_lama = mysqli_query($koneksi, "SELECT * FROM user WHERE id = '$id_user'");
    $data_lama = mysqli_fetch_assoc($cari_user_lama);

    if ($data_lama) {
        $username_lama = $data_lama['username'];
        $avatar_path = $data_lama['avatar'];
        $boleh_update = true;

        // Validasi jika username diubah, pastikan tidak duplikat dengan akun lain
        if ($username_baru !== $username_lama) {
            $cek_username_lain = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username_baru' AND id != '$id_user'");
            if (mysqli_num_rows($cek_username_lain) > 0) {
                $pesan = "Gagal mengupdate! Username '" . htmlspecialchars($username_baru) . "' sudah dipakai user lain.";
                $tipe_pesan = "error";
                $boleh_update = false;
            }
        }

        // Mencegah perubahan nama admin utama demi integritas sistem
        if ($username_lama === 'admin' && $username_baru !== 'admin') {
            $pesan = "Gagal! Username 'admin' utama tidak boleh diganti.";
            $tipe_pesan = "error";
            $boleh_update = false;
        }

        if ($boleh_update) {
            // Logika Update File Gambar Avatar jika diunggah berkas baru
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
                $file_name = $_FILES['avatar']['name'];
                $file_tmp = $_FILES['avatar']['tmp_name'];
                $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_file_name = "avatar_" . $username_baru . "_" . time() . "." . $file_ext;
                $target_dir = "uploads/profil/" . $new_file_name;

                if (move_uploaded_file($file_tmp, $target_dir)) {
                    // Hapus berkas avatar lama fisik jika bukan bawaan default
                    if ($data_lama['avatar'] != "uploads/profil/default.jpg" && file_exists($data_lama['avatar'])) {
                        unlink($data_lama['avatar']);
                    }
                    $avatar_path = $target_dir;
                }
            }

            // Konstruksi Query Update Otomatis
            if (!empty($password_baru)) {
                // Jika password diisi, enkripsi password baru tersebut
                $password_hashed_baru = password_hash($password_baru, PASSWORD_DEFAULT);
                $query_update = "UPDATE user SET username = '$username_baru', password = '$password_hashed_baru', avatar = '$avatar_path' WHERE id = '$id_user'";
            } else {
                // Jika password kosong, biarkan menggunakan password hash lama di database
                $query_update = "UPDATE user SET username = '$username_baru', avatar = '$avatar_path' WHERE id = '$id_user'";
            }

            if (mysqli_query($koneksi, $query_update)) {
                // Sinkronisasi data nama pengunggah/pembuat di tabel lain jika username berubah
                if ($username_baru !== $username_lama) {
                    mysqli_query($koneksi, "UPDATE komunitas SET pembuat = '$username_baru' WHERE pembuat = '$username_lama'");
                    mysqli_query($koneksi, "UPDATE komunitas_komentar SET pembuat = '$username_baru' WHERE pembuat = '$username_lama'");
                    mysqli_query($koneksi, "UPDATE komunitas_likes SET username = '$username_baru' WHERE username = '$username_lama'");
                    mysqli_query($koneksi, "UPDATE artikel SET pembuat = '$username_baru' WHERE pembuat = '$username_lama'");
                    mysqli_query($koneksi, "UPDATE aplikasi SET pengunggah = '$username_baru' WHERE pengunggah = '$username_lama'");
                }

                $pesan = "Data Pengguna '" . htmlspecialchars($username_baru) . "' Berhasil Diperbarui!";
                $tipe_pesan = "sukses";
            } else {
                $pesan = "Gagal memperbarui data user ke database.";
                $tipe_pesan = "error";
            }
        }
    }
}

// 5. PROSES LOGIKA: HAPUS USER & SELURUH AKTIVITASNYA
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    $cari_user = mysqli_query($koneksi, "SELECT * FROM user WHERE id = '$id_hapus'");
    $data_user = mysqli_fetch_assoc($cari_user);

    if ($data_user) {
        $username_target = $data_user['username'];

        if ($username_target === 'admin') {
            $pesan = "Gagal! Akun utama administrator tidak dapat dihapus.";
            $tipe_pesan = "error";
        } else {
            // A. HAPUS FILE POSTINGAN KOMUNITAS
            $cari_media_post = mysqli_query($koneksi, "SELECT gambar, video FROM komunitas WHERE pembuat = '$username_target'");
            while ($post_media = mysqli_fetch_assoc($cari_media_post)) {
                if (!empty($post_media['gambar']) && file_exists($post_media['gambar'])) unlink($post_media['gambar']); 
                if (!empty($post_media['video']) && file_exists($post_media['video'])) unlink($post_media['video']); 
            }

            // B. HAPUS FILE ARTIKEL
            $cari_aset_artikel = mysqli_query($koneksi, "SELECT gambar, file_pdf FROM artikel WHERE pembuat = '$username_target'");
            while ($artikel_aset = mysqli_fetch_assoc($cari_aset_artikel)) {
                if (!empty($artikel_aset['gambar'])) {
                    if (file_exists($artikel_aset['gambar'])) unlink($artikel_aset['gambar']);
                    elseif (file_exists('uploads/' . $artikel_aset['gambar'])) unlink('uploads/' . $artikel_aset['gambar']);
                }
                if (!empty($artikel_aset['file_pdf']) && file_exists($artikel_aset['file_pdf'])) unlink($artikel_aset['file_pdf']);
            }

            // C. HAPUS FILE IKON APLIKASI
            $cari_ikon_app = mysqli_query($koneksi, "SELECT ikon FROM aplikasi WHERE pengunggah = '$username_target'");
            while ($app_aset = mysqli_fetch_assoc($cari_ikon_app)) {
                if (!empty($app_aset['ikon']) && file_exists('uploads/apps/' . $app_aset['ikon'])) unlink('uploads/apps/' . $app_aset['ikon']);
            }

            // D. HAPUS FILE GAME HTML5
            $cari_file_game = mysqli_query($koneksi, "SELECT banner, folder_game FROM games WHERE pembuat = '$username_target'");
            while ($game_aset = mysqli_fetch_assoc($cari_file_game)) {
                $nama_file_banner = $game_aset['banner'];
                if (!empty($nama_file_banner) && file_exists('uploads/games/' . $nama_file_banner)) unlink('uploads/games/' . $nama_file_banner);
                
                $nama_folder_game = $game_aset['folder_game'];
                $direktori_game = 'game/' . $nama_folder_game;
                if (!empty($nama_folder_game) && is_dir($direktori_game)) hapusFolderRekursif($direktori_game);
            }

            // E. HAPUS DATA DATABASE BERANTAI
            mysqli_query($koneksi, "DELETE FROM komunitas_likes WHERE username = '$username_target'");
            mysqli_query($koneksi, "DELETE FROM komunitas_komentar WHERE pembuat = '$username_target'");
            mysqli_query($koneksi, "DELETE FROM komunitas WHERE pembuat = '$username_target'");
            mysqli_query($koneksi, "DELETE FROM artikel WHERE pembuat = '$username_target'");
            mysqli_query($koneksi, "DELETE FROM aplikasi WHERE pengunggah = '$username_target'");

            // F. HAPUS AVATAR & USER UTAMA
            if ($data_user['avatar'] != "uploads/profil/default.jpg" && file_exists($data_user['avatar'])) unlink($data_user['avatar']); 
            mysqli_query($koneksi, "DELETE FROM user WHERE id = '$id_hapus'");
            
            $pesan = "User '" . htmlspecialchars($username_target) . "' beserta seluruh data dan berkas medianya berhasil dibersihkan!";
            $tipe_pesan = "sukses";
        }
    }
}

// 6. AMBIL DATA USER TERBARU
$tampil_users = mysqli_query($koneksi, "SELECT * FROM user ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">
    <style>
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        
        body { 
            background: linear-gradient(rgba(106, 13, 106, 0.92), rgba(106, 13, 106, 0.92)), url('logo.png');
            background-size: 100px; background-repeat: repeat; color: #333; 
            display: flex; flex-direction: column; min-height: 100vh; 
        }
        
        .main-content { flex: 1; padding: 20px 10px; animation: fadeInUp 0.8s ease-out; width: 100%; max-width: 900px; margin: 0 auto; }
        .container-box { background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px); border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); overflow: hidden; margin-bottom: 20px; }
        .header-title { background: #4a094a; color: white; padding: 15px; font-size: 1.2rem; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; text-align: center; }
        
        .content-layout { display: flex; flex-direction: column; gap: 25px; padding: 20px; }
        
        .form-section { background: #faf5fa; padding: 20px; border-radius: 10px; border: 1px solid #e1cee1; width: 100%; transition: 0.3s ease; }
        .form-section.mode-edit { background: #fffde6; border-color: #ffe680; }
        .form-section h3 { color: #6a0d6a; margin-bottom: 15px; border-bottom: 2px solid #6a0d6a; padding-bottom: 5px; font-size: 1.1rem; }
        .form-section.mode-edit h3 { color: #8a6d3b; border-bottom-color: #8a6d3b; }
        .form-group { margin-bottom: 15px; display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-weight: bold; font-size: 0.9rem; color: #555; }
        .form-group input { padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 0.95rem; transition: 0.3s; width: 100%; }
        .form-group input:focus { border-color: #6a0d6a; outline: none; box-shadow: 0 0 8px rgba(106, 13, 106, 0.2); }
        
        .btn-submit { background: #6a0d6a; color: white; border: none; padding: 12px; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; width: 100%; transition: 0.3s; text-transform: uppercase; }
        .btn-submit:hover { background: #4a094a; }
        .btn-cancel { background: #6c757d; color: white; border: none; padding: 12px; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; width: 100%; transition: 0.3s; text-transform: uppercase; margin-top: 10px; display: none; text-align: center; text-decoration: none; }

        .table-section { width: 100%; }
        .table-section h3 { color: #6a0d6a; margin-bottom: 15px; font-size: 1.1rem; border-bottom: 2px solid #6a0d6a; padding-bottom: 5px; }
        
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 8px; border: 1px solid #eee; }
        table { width: 100%; border-collapse: collapse; background: white; min-width: 550px; }
        th, td { padding: 12px 10px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.9rem; vertical-align: middle; }
        th { background-color: #6a0d6a; color: white; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; }
        tr:hover { background-color: #fbf4fb; }
        .avatar-img { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; border: 1px solid #ddd; }
        
        .btn-action { color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; display: inline-block; text-align: center; border: none; cursor: pointer; }
        .btn-delete { background: #dc3545; }
        .btn-delete:hover { background: #bd2130; }
        .btn-edit { background: #ffc107; color: #212529; margin-right: 5px; }
        .btn-edit:hover { background: #e0a800; }
        .disabled-link { background: #ccc; color: #666; cursor: not-allowed; }

        .alert { padding: 12px; margin: 15px 20px 0 20px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; }
        .alert-sukses { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <main class="main-content">
        <div class="container-box">
            <div class="header-title">User Management Dashboard</div>
            
            <?php if (!empty($pesan)): ?>
                <div class="alert alert-<?= $tipe_pesan; ?>">
                    <?= $pesan; ?>
                </div>
            <?php endif; ?>

            <div class="content-layout">
                
                <div class="form-section" id="formContainer">
                    <h3 id="formTitle">Tambah User Baru</h3>
                    <form action="user.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="id_user" name="id_user" value="">

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" placeholder="Masukkan username..." required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="password" id="labelPassword">Password</label>
                            <input type="password" id="password" name="password" placeholder="Masukkan password..." required>
                            <small id="passwordHelp" style="color: #666; font-style: italic; display: none; margin-top: 2px;">*Kosongkan kolom ini jika tidak ingin mengubah password.</small>
                        </div>
                        <div class="form-group">
                            <label for="avatar">Foto Profil (Avatar)</label>
                            <input type="file" id="avatar" name="avatar" accept="image/*">
                        </div>
                        
                        <button type="submit" id="btnSubmitForm" name="tambah_user" class="btn-submit">Simpan User</button>
                        <button type="button" id="btnCancelForm" class="btn-cancel" onclick="resetFormKeModeTambah()">Batal Edit</button>
                    </form>
                </div>

                <div class="table-section">
                    <h3>Daftar Pengguna Sistem</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Avatar</th>
                                    <th>Username</th>
                                    <th>Password</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($tampil_users) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($tampil_users)): ?>
                                        <tr>
                                            <td>
                                                <img src="<?= htmlspecialchars($row['avatar']); ?>" alt="Avatar" class="avatar-img">
                                            </td>
                                            <td><strong><?= htmlspecialchars($row['username']); ?></strong></td>
                                            <td><code style="color: #28a745; font-weight: bold;">●●●●●● [Terlindungi Hash]</code></td>
                                            <td>
                                                <button type="button" class="btn-action btn-edit" 
                                                        onclick="aktifkanModeEdit('<?= $row['id']; ?>', '<?= htmlspecialchars($row['username']); ?>')">
                                                    Ubah
                                                </button>

                                                <?php if($row['username'] === 'admin'): ?>
                                                    <span class="btn-action disabled-link">Utama</span>
                                                <?php else: ?>
                                                    <a href="user.php?hapus=<?= $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Menghapus user <?= $row['username']; ?> akan menghapus seluruh data, artikel, aplikasi, dan aktivitas mereka secara permanen. Lanjutkan?')">Hapus</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: #999;">Tidak ada data user.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
    function aktifkanModeEdit(id, username) {
        // 1. Dapatkan objek elemen form
        const formContainer = document.getElementById('formContainer');
        const formTitle = document.getElementById('formTitle');
        const idInput = document.getElementById('id_user');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const labelPassword = document.getElementById('labelPassword');
        const passwordHelp = document.getElementById('passwordHelp');
        const btnSubmit = document.getElementById('btnSubmitForm');
        const btnCancel = document.getElementById('btnCancelForm');

        // 2. Ubah tema kontainer & judul form menjadi mode edit
        formContainer.classList.add('mode-edit');
        formTitle.innerText = "Ubah Data Pengguna: " + username;

        // 3. Masukkan data ke kolom inputan
        idInput.value = id;
        usernameInput.value = username;

        // 4. Sesuaikan aturan password untuk edit (Tidak wajib diisi jika hanya ganti nama/avatar)
        passwordInput.value = ""; 
        passwordInput.required = false; 
        passwordInput.placeholder = "Masukkan password baru jika ingin diganti...";
        labelPassword.innerText = "Password Baru (Opsional)";
        passwordHelp.style.display = "block";

        // 5. Ubah identitas tombol submit POST PHP
        btnSubmit.name = "edit_user";
        btnSubmit.innerText = "Perbarui Data User";
        btnSubmit.style.background = "#e0a800";
        btnSubmit.style.color = "#212529";

        // 6. Tampilkan tombol batal edit
        btnCancel.style.display = "block";

        // Gulingkan layar otomatis ke atas agar admin langsung melihat form editnya
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetFormKeModeTambah() {
        const formContainer = document.getElementById('formContainer');
        const formTitle = document.getElementById('formTitle');
        const idInput = document.getElementById('id_user');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const labelPassword = document.getElementById('labelPassword');
        const passwordHelp = document.getElementById('passwordHelp');
        const btnSubmit = document.getElementById('btnSubmitForm');
        const btnCancel = document.getElementById('btnCancelForm');

        // Kembalikan ke mode awal/tambah
        formContainer.classList.remove('mode-edit');
        formTitle.innerText = "Tambah User Baru";

        idInput.value = "";
        usernameInput.value = "";
        
        passwordInput.value = "";
        passwordInput.required = true;
        passwordInput.placeholder = "Masukkan password...";
        labelPassword.innerText = "Password";
        passwordHelp.style.display = "none";

        btnSubmit.name = "tambah_user";
        btnSubmit.innerText = "Simpan User";
        btnSubmit.style.background = "#6a0d6a";
        btnSubmit.style.color = "white";

        btnCancel.style.display = "none";
    }
    </script>
</body>
</html>