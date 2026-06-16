<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi Halaman
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$current_page = 'game_browser.php'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Game</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">
</head>
<body>

<?php include 'header.php'; ?>

<style>
    /* --- RESET DASAR --- */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    body { 
        background: linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), url('logo.png');
        background-size: 100px;
        background-repeat: repeat;
        min-height: 100vh;
    }

    .main-content {
        min-height: calc(100vh - 85px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .form-container {
        width: 100%;
        max-width: 800px;
        animation: fadeIn 0.8s ease-out;
    }

    .form-box {
        background: white;
        padding: 35px 30px;
        border-radius: 16px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #333;
    }

    .form-header-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #f2e6f2; 
        padding-bottom: 10px; 
        margin-bottom: 25px; 
    }

    .form-box h3 { 
        color: #6a0d6a; 
        font-size: 1.6rem; 
        font-weight: 700;
    }

    /* Tombol Panduan */
    .btn-info {
        background-color: #2575fc;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        font-size: 0.85rem;
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .btn-info:hover {
        background-color: #1a5bc4;
        transform: translateY(-1px);
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

    .input-group input[type="file"] {
        padding: 8px 10px;
        background-color: #fdf8fd;
        border: 1px dashed #6a0d6a;
        border-radius: 8px;
        width: 100%;
        cursor: pointer;
    }

    .input-group small {
        display: block;
        margin-top: 5px;
        color: #666;
        font-size: 0.85rem;
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

    /* --- STYLING MODAL POP-UP PANDUAN --- */
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(5px);
        display: flex; align-items: center; justify-content: center;
        z-index: 9999; opacity: 0; pointer-events: none;
        transition: 0.3s ease;
    }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    
    .modal-content-box {
        background: white; padding: 30px; border-radius: 16px;
        width: 90%; max-width: 600px; max-height: 85vh; overflow-y: auto;
        box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        transform: translateY(-30px); transition: 0.3s ease;
    }
    .modal-overlay.active .modal-content-box { transform: translateY(0); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
    .modal-header h4 { color: #2575fc; font-size: 1.3rem; font-weight: bold; }
    .close-modal { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999; }
    .close-modal:hover { color: #333; }
    
    .guide-step { margin-bottom: 15px; padding-left: 10px; border-left: 3px solid #6a0d6a; }
    .guide-step strong { color: #333; display: block; margin-bottom: 3px; }
    .guide-step p { color: #555; font-size: 0.9rem; line-height: 1.5; }
    .badge-alert { background: #fff3cd; color: #856404; padding: 10px; border-radius: 6px; border-left: 4px solid #ffeeba; font-size: 0.85rem; margin-top: 15px; }

    @media (max-width: 768px) {
        .main-content { padding: 20px 10px; }
        .form-box { padding: 25px 20px; }
        .form-header-wrapper { flex-direction: column; align-items: flex-start; gap: 10px; }
        .btn-info { width: 100%; justify-content: center; }
        .btn-group { flex-direction: column-reverse; gap: 10px; }
        .btn-save, .btn-cancel { width: 100%; }
    }
</style>

<div class="main-content">
    <div class="form-container">
        <div class="form-box">
            
            <div class="form-header-wrapper">
                <h3>Unggah Karya Game Baru</h3>
                <button type="button" class="btn-info" onclick="toggleModal(true)">💡 PANDUAN UPLOAD</button>
            </div>

            <form action="proses_game.php" method="POST" enctype="multipart/form-data">
                
                <div class="input-group">
                    <label for="judul">Judul Game :</label>
                    <input type="text" id="judul" name="judul" placeholder="Ketikkan nama game buatanmu di sini..." required autocomplete="off">
                </div>

                <div class="input-group">
                    <label for="genre">Genre Permainan :</label>
                    <select id="genre" name="genre" required>
                        <option value="">-- Pilih Kategori Genre --</option>
                        <option value="Action">Action / Petualangan</option>
                        <option value="Puzzle">Puzzle / Asah Otak</option>
                        <option value="Sports">Sports / Olahraga</option>
                        <option value="Strategy">Strategy / Simulasi</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="banner">Gambar Sampul Banner Game :</label>
                    <input type="file" id="banner" name="banner" accept="image/*" required>
                    <small>Format: JPG, JPEG, PNG, WEBP (Maksimal 2MB)</small>
                </div>

                <div class="input-group">
                    <label for="file_game">Berkas Asset Game (Format .ZIP) :</label>
                    <input type="file" id="file_game" name="file_game" accept=".zip" required>
                    <small>Gunakan format .ZIP (Format .RAR tidak didukung otomatis oleh sistem extractor php bawaan server)</small>
                </div>

                <div class="input-group">
                    <label for="deskripsi">Petunjuk Main & Ringkasan Deskripsi :</label>
                    <textarea id="deskripsi" name="deskripsi" rows="6" placeholder="Tuliskan cerita singkat game beserta instruksi kontrol tombol (Keyboard/Mouse) di sini..." required></textarea>
                </div>

                <div class="btn-group">
                    <a href="game_browser.php" class="btn-cancel">BATALKAN</a>
                    <button type="submit" class="btn-save">PUBLIKASIKAN GAME</button>
                </div>

            </form>
        </div>
    </div>
</div>

<div class="modal-overlay" id="guideModal">
    <div class="modal-content-box">
        <div class="modal-header">
            <h4>⚙️ Panduan Pengemasan Berkas Game (.ZIP)</h4>
            <button type="button" class="close-modal" onclick="toggleModal(false)">&times;</button>
        </div>
        
        <div class="guide-step">
            <strong>1. Pastikan Game Menggunakan HTML5</strong>
            <p>Sistem ini menjalankan game berbasis web. Pastikan game Anda memiliki struktur file client-side (HTML, CSS, JS) dan bisa berjalan langsung di browser.</p>
        </div>
        
        <div class="guide-step">
            <strong>2. File 'index.html' Harus Berada Di Luar</strong>
            <p>Buka folder project game Anda, pastikan file utama eksekusi bernama <strong>index.html</strong> (Gunakan huruf kecil semua). Jangan masukkan index.html ke dalam sub-folder lagi.</p>
        </div>
        
        <div class="guide-step">
            <strong>3. Cara Melakukan Compress yang Benar</strong>
            <p>Blok/pilih semua file di dalam project game Anda (index.html beserta folder assets, js, css-nya), klik kanan -> <strong>Send to -> Compressed (zipped) folder</strong> atau gunakan aplikasi WinRAR/7-Zip dan pilih format arsip <strong>.ZIP</strong>.</p>
        </div>

        <div class="badge-alert">
            ⚠️ <strong>PENTING:</strong> Sistem backend server memakai ekstensi otomatis <code>ZipArchive</code>. Format berkas <strong>.RAR tidak didukung</strong>. Pastikan ekstensi file Anda berakhiran <strong>.zip</strong> sebelum diunggah!
        </div>
    </div>
</div>

<script>
    function toggleModal(show) {
        const modal = document.getElementById('guideModal');
        if (show) {
            modal.classList.add('active');
        } else {
            modal.classList.remove('active');
        }
    }

    // Menutup modal jika user klik di luar kotak putih
    document.getElementById('guideModal').addEventListener('click', function(e) {
        if(e.target === this) {
            toggleModal(false);
        }
    });
</script>

<?php include 'footer.php'; ?>
</body>
</html>