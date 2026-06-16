<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- PROTEKSI SINKRONISASI SESSION (Sama Persis dengan hapus_artikel.php) ---
if (!isset($_SESSION['username']) || empty(trim($_SESSION['username']))) {
    header("Location: login.php");
    exit;
}

// Gunakan standarisasi format string: huruf kecil & hilangkan spasi (anti-bypass)
$user_login = strtolower(trim($_SESSION['username']));

// --- TAMBAHAN KEAMANAN: GENERATE CSRF TOKEN JIKA BELUM ADA ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico">
</head>
<body>

<?php
// 1. Ambil komponen header dan koneksi database
include 'header.php';
include 'koneksi.php';

// Deteksi nama halaman aktif untuk fungsionalitas class active di navbar
$current_page = 'artikel.php'; // Menjaga menu Artikel tetap menyala aktif di navbar

// 2. Validasi parameter ID yang dikirim dari tombol tabel utama secara aman
if (!isset($_GET['id']) || empty(trim($_GET['id']))) {
    echo "<script>alert('Akses tidak sah! ID artikel tidak ditemukan.'); window.location.href='artikel.php';</script>";
    exit;
}

$id_artikel = intval($_GET['id']);

// 3. Ambil data artikel lama berdasarkan ID menggunakan Prepared Statement (Aman dari SQL Injection)
$query = "SELECT judul, kategori, gambar, konten, status, pembuat, file_pdf FROM artikel WHERE id = ?";
$stmt  = $koneksi->prepare($query);
$stmt->bind_param("i", $id_artikel);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    echo "<script>alert('Data artikel tidak ditemukan di database!'); window.location.href='artikel.php';</script>";
    exit;
}

$data = $result->fetch_assoc();
$stmt->close();

// --- PROTEKSI KEPEMILIKAN: Diselaraskan Total dengan hapus_artikel.php ---
$pembuat_artikel = strtolower(trim($data['pembuat']));

// Hanya pembuat asli artikel ATAU admin yang boleh mengedit isi artikel
if ($user_login !== $pembuat_artikel && $user_login !== 'admin') {
    echo "<script>alert('Akses Ditolak! Anda tidak berhak mengubah isi artikel milik kreator lain.'); window.location.href='artikel.php';</script>";
    exit();
}

$path_gambar = trim($data['gambar']);
?>

<style>
    /* --- RESET DASAR --- */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    body { color: #333; overflow-x: hidden; }

    /* --- SINKRONISASI BACKGROUND DENGAN LOGIN.PHP & ARTIKEL.PHP --- */
    body { 
        background: linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), url('logo.png');
        background-size: 100px;
        background-repeat: repeat;
        min-height: 100vh;
    }

    /* --- WRAPPER KONTEN UTAMA --- */
    .main-content {
        min-height: calc(100vh - 85px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }
    
    /* --- LAYOUT CONTAINER FORMULIR --- */
    .form-container {
        width: 100%;
        max-width: 800px;
        background: white;
        padding: 35px 30px;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.1);
        animation: fadeIn 0.6s ease-out;
    }
    
    .form-container h2 {
        color: #6a0d6a;
        margin-bottom: 25px;
        border-bottom: 2px solid #f2e6f2;
        padding-bottom: 10px;
        font-weight: 700;
        font-size: 1.6rem;
    }
    
    .form-group { margin-bottom: 20px; text-align: left; }
    
    .form-group label {
        display: block;
        font-weight: bold;
        color: #555;
        margin-bottom: 8px;
        font-size: 1rem;
    }
    
    .form-control {
        width: 100%;
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        box-sizing: border-box;
        transition: 0.3s;
        color: #333;
        font-family: inherit;
    }
    .form-control:focus { 
        border-color: #6a0d6a; 
        outline: none; 
        box-shadow: 0 0 8px rgba(106, 13, 106, 0.15);
    }
    
    textarea.form-control { min-height: 200px; resize: vertical; }

    .form-group input[type="file"] {
        padding: 8px 10px;
        background-color: #fdf8fd;
        border: 1px dashed #6a0d6a;
        border-radius: 8px;
        cursor: pointer;
    }
    
    .img-preview {
        margin-top: 10px;
        max-width: 200px;
        border-radius: 8px;
        display: block;
        box-shadow: 0 4px 10px rgba(106, 13, 106, 0.15);
        border: 2px solid #6a0d6a;
        object-fit: cover;
    }
    
    .btn-group { display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px; }
    
    .btn-submit {
        background-color: #6a0d6a;
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        font-size: 1rem;
        transition: 0.3s;
        box-shadow: 0 4px 12px rgba(106, 13, 106, 0.2);
    }
    .btn-submit:hover { 
        background-color: #4a094a; 
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(106, 13, 106, 0.3);
    }
    
    .btn-cancel {
        background-color: #fff;
        color: #666;
        border: 2px solid #ddd;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        text-align: center;
        transition: 0.3s;
        font-size: 1rem;
    }
    .btn-cancel:hover { background-color: #f5f5f5; color: #333; }

    @media (max-width: 768px) {
        .main-content { padding: 20px 10px; }
        .form-container { padding: 25px 20px; }
        .form-container h2 { font-size: 1.4rem; }
        .btn-group { flex-direction: column-reverse; gap: 10px; }
        .btn-submit, .btn-cancel { width: 100%; }
    }
</style>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    .old-file-text { font-size: 0.88rem; color: #666; margin-top: 6px; display: block; font-style: italic; }
</style>

<div class="main-content">
    <div class="form-container">
        <h2>Edit Artikel Sistem</h2>
        <form action="proses_edit_artikel.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
            <input type="hidden" name="id" value="<?= $id_artikel; ?>">

            <div class="form-group">
                <label for="judul">Judul Artikel :</label>
                <input type="text" id="judul" name="judul" class="form-control" value="<?= htmlspecialchars($data['judul']); ?>" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="kategori">Kategori Artikel :</label>
                <select id="kategori" name="kategori" class="form-control" required>
                    <option value="Kegiatan" <?= ($data['kategori'] === 'Kegiatan') ? 'selected' : ''; ?>>Kegiatan Kampus</option>
                    <option value="Edukasi" <?= ($data['kategori'] === 'Edukasi') ? 'selected' : ''; ?>>Edukasi & Teknologi</option>
                    <option value="Tutorial" <?= ($data['kategori'] === 'Tutorial') ? 'selected' : ''; ?>>Tutorial Programming</option>
                    <option value="Pengumuman" <?= ($data['kategori'] === 'Pengumuman') ? 'selected' : ''; ?>>Pengumuman Resmi</option>
                </select>
            </div>

            <div class="form-group">
                <label for="gambar">Ubah Gambar Sampul Berita (Biarkan kosong jika tidak diganti) :</label>
                <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*">
                
                <?php if (!empty($path_gambar) && file_exists($path_gambar)) : ?>
                    <img src="<?= htmlspecialchars($path_gambar); ?>" alt="Pratinjau Lama" class="img-preview">
                <?php elseif (!empty($path_gambar) && file_exists('uploads/artikel/' . $path_gambar)) : ?>
                    <img src="uploads/artikel/<?= htmlspecialchars($path_gambar); ?>" alt="Pratinjau Lama" class="img-preview">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="konten">Isi Berita / Konten Pembahasan :</label>
                <textarea id="konten" name="konten" class="form-control" required><?= htmlspecialchars($data['konten']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="file_pdf">Ubah Dokumen Lampiran PDF (Biarkan kosong jika tidak diganti) :</label>
                <input type="file" id="file_pdf" name="file_pdf" class="form-control" accept="application/pdf">
                
                <?php 
                $path_pdf = isset($data['file_pdf']) ? trim($data['file_pdf']) : '';
                if (!empty($path_pdf) && file_exists($path_pdf)) : 
                ?>
                    <span class="old-file-text">📄 File saat ini: <a href="<?= htmlspecialchars($path_pdf); ?>" target="_blank" style="color: #6a0d6a; font-weight: bold;"><?= basename($path_pdf); ?></a></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="status">Simpan Sebagai :</label>
                <select id="status" name="status" class="form-control">
                    <option value="Publish" <?= ($data['status'] === 'Publish') ? 'selected' : ''; ?>>Langsung Terbitkan (Publish)</option>
                    <option value="Draft" <?= ($data['status'] === 'Draft') ? 'selected' : ''; ?>>Simpan Ke Draft Dokumen</option>
                </select>
            </div>

            <div class="btn-group">
                <a href="artikel.php" class="btn-cancel">BATALKAN</a>
                <button type="submit" class="btn-submit">PERBARUI ARTIKEL</button>
            </div>

        </form>
    </div>
</div>

<?php 
include 'footer.php'; 
?>