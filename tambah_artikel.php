<?php
// 1. Memulai session wajib diletakkan di bagian paling atas jika belum dimulai di header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi Halaman: Hanya pengguna masuk log yang boleh menulis artikel baru demi mematuhi Foreign Key
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// --- TAMBAHAN KEAMANAN: GENERATE CSRF TOKEN JIKA BELUM ADA ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Deteksi nama halaman aktif untuk fungsionalitas class active di navbar
$current_page = 'artikel.php'; // Menjaga menu Artikel tetap menyala aktif di navbar

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Artikel</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico">
</head>
<body>

<?php include 'header.php'; ?>
<style>
    /* --- RESET DASAR --- */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    
    /* --- ANIMASI --- */
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    /* --- SINKRONISASI BACKGROUND DENGAN LOGIN.PHP & ARTIKEL.PHP --- */
    body { 
        background: linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), url('logo.png');
        background-size: 100px;
        background-repeat: repeat;
        min-height: 100vh;
    }

    /* --- WRAAPER KONTEN UTAMA (Memaksa Navbar Tetap Berada Di Atas Layar) --- */
    .main-content {
        min-height: calc(100vh - 85px); /* Memotong tinggi area bar navigasi */
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    /* --- LAYOUT CONTAINER FORMULIR --- */
    .form-container {
        width: 100%;
        max-width: 800px;
        animation: fadeIn 0.8s ease-out;
    }

    .form-box {
        background: white;
        padding: 35px 30px;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.4); /* Bayangan tegas agar kontras dengan latar belakang gelap */
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #333;
    }

    .form-box h3 { 
        color: #6a0d6a; 
        margin-bottom: 25px; 
        font-size: 1.6rem; 
        border-bottom: 2px solid #f2e6f2; 
        padding-bottom: 10px; 
        font-weight: 700;
    }
    
    .input-group { margin-bottom: 20px; text-align: left; }
    .input-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #555; font-size: 1rem; }
    
    .input-group input[type="text"], .input-group select, .input-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        transition: 0.3s;
        font-family: inherit;
        color: #333;
    }

    .input-group input:focus, .input-group select:focus, .input-group textarea:focus {
        border-color: #6a0d6a;
        outline: none;
        box-shadow: 0 0 8px rgba(106, 13, 106, 0.15);
    }

    /* Kustomisasi tombol pilih file gambar sampul & PDF */
    .input-group input[type="file"] {
        padding: 8px 10px;
        background-color: #fdf8fd;
        border: 1px dashed #6a0d6a;
        border-radius: 8px;
        width: 100%;
        cursor: pointer;
    }

    .btn-group { display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px; }
    
    .btn-save { 
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
    .btn-save:hover { 
        background-color: #4a094a; 
        transform: translateY(-2px); 
        box-shadow: 0 6px 15px rgba(106, 13, 106, 0.3);
    }
    
    .btn-cancel { 
        background-color: white; 
        color: #666; 
        border: 2px solid #ddd; 
        padding: 12px 25px; 
        border-radius: 8px; 
        text-decoration: none; 
        font-weight: bold; 
        font-size: 1rem; 
        text-align: center; 
        transition: 0.3s; 
    }
    .btn-cancel:hover { background-color: #f5f5f5; color: #333; }

    /* --- RESPONSIVITAS HP --- */
    @media (max-width: 768px) {
        .main-content { padding: 20px 10px; }
        .form-box { padding: 25px 20px; }
        .form-box h3 { font-size: 1.4rem; }
        .btn-group { flex-direction: column-reverse; gap: 10px; }
        .btn-save, .btn-cancel { width: 100%; }
    }
</style>

<div class="main-content">
    <div class="form-container">
        <div class="form-box">
            <h3>Tulis Artikel Baru</h3>
            <form action="proses_artikel.php" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
                
                <div class="input-group">
                    <label for="judul">Judul Artikel :</label>
                    <input type="text" id="judul" name="judul" placeholder="Ketikkan judul artikel fti di sini..." required autocomplete="off">
                </div>

                <div class="input-group">
                    <label for="kategori">Kategori Artikel :</label>
                    <select id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Kegiatan">Kegiatan Kampus</option>
                        <option value="Edukasi">Edukasi & Teknologi</option>
                        <option value="Tutorial">Tutorial Programming</option>
                        <option value="Pengumuman">Pengumuman Resmi</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="gambar">Gambar Sampul Berita :</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*" required>
                </div>

                <div class="input-group">
                    <label for="konten">Isi Berita / Konten Pembahasan :</label>
                    <textarea id="konten" name="konten" rows="10" placeholder="Tulis isi pembahasan lengkap artikel di sini..." required></textarea>
                </div>

                <div class="input-group">
                    <label for="file_pdf">Dokumen Lampiran (Format PDF) :</label>
                    <input type="file" id="file_pdf" name="file_pdf" accept="application/pdf">
                </div>

                <div class="input-group">
                    <label for="status">Simpan Sebagai :</label>
                    <select id="status" name="status">
                        <option value="Publish">Langsung Terbitkan (Publish)</option>
                        <option value="Draft">Simpan Ke Draft Dokumen</option>
                    </select>
                </div>

                <div class="btn-group">
                    <a href="artikel.php" class="btn-cancel">BATALKAN</a>
                    <button type="submit" class="btn-save">SIMPAN ARTIKEL</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php 
// Menyisipkan komponen penutup halaman global
include 'footer.php'; 
?>