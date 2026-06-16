<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Proteksi halaman wajib login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

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
    <title>Buat Postingan</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- RESET & TRANSISI LAYOUT --- */
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

        .post-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            width: 100%; 
            max-width: 700px; 
            border-radius: 16px; 
            padding: 35px 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            text-align: center;
            animation: fadeIn 0.8s ease-out; 
            color: #333;
        }

        h3 { color: #6a0d6a; margin-bottom: 25px; font-weight: 700; font-size: 1.6rem; border-bottom: 2px solid #f2e6f2; padding-bottom: 10px; }

        .textarea-input {
            width: 100%; height: 180px;
            padding: 15px; border-radius: 8px;
            border: 2px solid #ddd; font-size: 1rem;
            resize: none; outline: none; margin-bottom: 20px;
            transition: 0.3s;
            font-family: inherit;
            color: #333;
        }
        .textarea-input:focus { 
            border-color: #6a0d6a; 
            box-shadow: 0 0 8px rgba(106, 13, 106, 0.15); 
        }

        .button-group {
            display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; margin-bottom: 25px;
        }

        .btn-action {
            background: white; color: #6a0d6a;
            border: 2px dashed #6a0d6a; padding: 10px 18px;
            border-radius: 8px; font-weight: 600; font-size: 0.9rem;
            cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-action:hover { 
            background: #fdf2fd; 
            transform: translateY(-2px); 
            box-shadow: 0 4px 10px rgba(106, 13, 106, 0.1);
        }

        .btn-group-nav { display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px; }

        .btn-submit {
            background: #6a0d6a; color: white; border: none;
            padding: 12px 28px; border-radius: 8px; font-weight: bold;
            font-size: 1rem; cursor: pointer; transition: 0.3s;
            box-shadow: 0 4px 12px rgba(106, 13, 106, 0.2);
            display: inline-flex; align-items: center; gap: 8px; justify-content: center;
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
            display: inline-block;
        }
        .btn-batal:hover { background-color: #f5f5f5; color: #333; }

        .preview-box {
            margin-top: 15px; width: 100%; max-height: 300px;
            border-radius: 10px; overflow: hidden; display: none;
            border: 2px solid #6a0d6a; background: #000;
        }
        .preview-box img, .preview-box video {
            width: 100%; height: auto; max-height: 300px; object-fit: contain;
        }

        /* MODAL POP-UP / ACTION SHEET OVERLAY STYLING */
        .camera-modal {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(5px);
            display: flex; justify-content: center; align-items: flex-end;
            z-index: 9999; visibility: hidden; opacity: 0; transition: 0.3s ease;
        }
        .camera-modal.show { visibility: visible; opacity: 1; }

        .modal-sheet {
            background: white; width: 100%; max-width: 500px;
            border-radius: 20px 20px 0 0; padding: 25px;
            box-shadow: 0 -10px 25px rgba(0,0,0,0.3);
            transform: translateY(100%); transition: 0.3s ease;
            text-align: center;
        }
        .camera-modal.show .modal-sheet { transform: translateY(0); }
        .modal-sheet h4 { color: #333; margin-bottom: 20px; font-weight: 700; font-size: 1.1rem; }
        .sheet-options { display: flex; justify-content: space-around; gap: 15px; margin-bottom: 25px; }
        
        .sheet-btn {
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            background: #fdf6fd; border: 1px solid #f2e6f2; padding: 20px;
            border-radius: 14px; width: 45%; cursor: pointer; transition: 0.2s;
            color: #6a0d6a; font-weight: bold; font-size: 0.95rem;
        }
        .sheet-btn i { font-size: 2rem; color: #6a0d6a; }
        .sheet-btn:hover { background: #6a0d6a; color: white; border-color: #6a0d6a; }
        .sheet-btn:hover i { color: white; }

        .btn-close-sheet {
            background: #f5f5f5; color: #666; border: none; width: 100%;
            padding: 12px; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.2s;
        }
        .btn-close-sheet:hover { background: #e8e8e8; color: #333; }

        @media (max-width: 768px) {
            .main-content { padding: 20px 10px; }
            .post-container { padding: 25px 20px; }
            .btn-group-nav { flex-direction: column-reverse; gap: 10px; }
            .btn-submit, .btn-batal { width: 100%; }
        }

        @media (min-width: 577px) {
            .camera-modal { align-items: center; }
            .modal-sheet { border-radius: 16px; width: 90%; transform: scale(0.8); }
            .camera-modal.show .modal-sheet { transform: scale(1); }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="main-content">
    <div class="post-container">
        <h3>Tulis Postingan Baru</h3>
        
        <form action="proses_komunitas.php" method="POST" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" id="csrfToken" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            
            <textarea name="isi_kiriman" id="isiKiriman" class="textarea-input" placeholder="Apa yang ingin Anda diskusikan dengan Sivitas FTI UAP hari ini?"></textarea>
            
            <input type="file" name="media_galeri" id="mediaGaleri" accept="image/*,video/*" style="display: none;">
            <input type="file" name="foto_kamera" id="fotoKamera" accept="image/*" capture="environment" style="display: none;">
            <input type="file" name="video_kamera" id="videoKamera" accept="video/*" capture="environment" style="display: none;">

            <div class="button-group">
                <div class="btn-action" onclick="document.getElementById('mediaGaleri').click();">
                    <i class="fa-solid fa-images"></i> Galeri (Foto/Video)
                </div>
                <div class="btn-action" id="btnBukaKamera">
                    <i class="fa-solid fa-camera"></i> Ambil Kamera
                </div>
            </div>

            <div class="preview-box" id="previewMediaBox"></div>

            <div class="btn-group-nav">
                <a href="komunitas.php" class="btn-batal">BATALKAN</a>
                <button type="submit" class="btn-submit">KIRIM STATUS</button>
            </div>
        </form>
    </div>
</div>

<div class="camera-modal" id="cameraModal">
    <div class="modal-sheet">
        <h4>Pilih Mode Kamera</h4>
        <div class="sheet-options">
            <div class="sheet-btn" id="optFoto">
                <i class="fa-solid fa-camera-retro"></i>
                <span>Ambil Foto</span>
            </div>
            <div class="sheet-btn" id="optVideo">
                <i class="fa-solid fa-video"></i>
                <span>Rekam Video</span>
            </div>
        </div>
        <button type="button" class="btn-close-sheet" id="btnTutupSheet">Batal</button>
    </div>
</div>

<script>
    const mediaGaleri = document.getElementById('mediaGaleri');
    const fotoKamera = document.getElementById('fotoKamera');
    const videoKamera = document.getElementById('videoKamera');
    const btnBukaKamera = document.getElementById('btnBukaKamera');
    const cameraModal = document.getElementById('cameraModal');
    const btnTutupSheet = document.getElementById('btnTutupSheet');
    const optFoto = document.getElementById('optFoto');
    const optVideo = document.getElementById('optVideo');
    const previewBox = document.getElementById('previewMediaBox');
    const uploadForm = document.getElementById('uploadForm');
    const btnSubmit = document.querySelector('.btn-submit');
    const csrfToken = document.getElementById('csrfToken').value; // Ambil nilai token dari HTML

    // Variabel global untuk menyimpan file yang akan dikirim (termasuk foto hasil kompresi)
    let fileSiapKirim = null; 
    let namaInputAktif = ""; 

    btnBukaKamera.addEventListener('click', () => cameraModal.classList.add('show'));
    btnTutupSheet.addEventListener('click', () => cameraModal.classList.remove('show'));
    cameraModal.addEventListener('click', (e) => { if(e.target === cameraModal) cameraModal.classList.remove('show'); });

    optFoto.addEventListener('click', () => { cameraModal.classList.remove('show'); fotoKamera.click(); });
    optVideo.addEventListener('click', () => { cameraModal.classList.remove('show'); videoKamera.click(); });

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

    // Penanganan Event Perubahan File Input
    [mediaGaleri, fotoKamera, videoKamera].forEach(input => {
        input.addEventListener('change', async function() {
            const file = this.files[0];
            if (!file) return;

            const maxFotoSize = 10 * 1024 * 1024;  // 10MB
            const maxVideoSize = 100 * 1024 * 1024; // 100MB

            // Validasi awal ukuran sebelum diproses
            if (file.type.startsWith('image/') && file.size > maxFotoSize) {
                alert("Gagal memuat berkas! Ukuran foto maksimal adalah 10MB.");
                resetPilihanMedia();
                return;
            }
            if (file.type.startsWith('video/') && file.size > maxVideoSize) {
                alert("Gagal memuat berkas! Ukuran video maksimal adalah 100MB.");
                resetPilihanMedia();
                return;
            }

            previewBox.innerHTML = '';
            previewBox.style.display = 'block';
            namaInputAktif = this.name; // Simpan nama field asal data (media_galeri / foto_kamera / video_kamera)

            if (file.type.startsWith('image/')) {
                // Tampilkan indikator memproses kompresi lokal sementara
                previewBox.innerHTML = '<div style="color:#6a0d6a; padding:20px; font-weight:bold;"><i class="fa-solid fa-compact-disc fa-spin"></i> Mengoptimalkan Ukuran Gambar...</div>';
                
                // Jalankan fungsi kompresi gambar (Kualitas set ke 0.75 atau 75%)
                const blobHasilKompresi = await compressImageNative(file, 0.75);
                
                // Konversi objek Blob menjadi struktur objek File agar PHP mengenalinya dengan baik
                fileSiapKirim = new File([blobHasilKompresi], file.name.replace(/\.[^/.]+$/, "") + ".jpg", { type: "image/jpeg" });
                
                // Tampilkan Preview foto hasil kompresi ke user
                previewBox.innerHTML = '';
                const img = document.createElement('img');
                img.src = URL.createObjectURL(fileSiapKirim);
                previewBox.appendChild(img);

            } else if (file.type.startsWith('video/')) {
                // Video Murni Asli (Sesuai Permintaan: Menghilangkan teks "Mengompresi Video...")
                fileSiapKirim = file;
                
                const video = document.createElement('video');
                video.src = URL.createObjectURL(fileSiapKirim);
                video.controls = true;
                previewBox.appendChild(video);
            }
        });
    });

    function resetPilihanMedia() {
        mediaGaleri.value = '';
        fotoKamera.value = '';
        videoKamera.value = '';
        fileSiapKirim = null;
        namaInputAktif = "";
        previewBox.style.display = 'none';
        previewBox.innerHTML = '';
    }

    // --- PROSES KIRIM DATA MENGGUNAKAN FORMDATA AJAX/FETCH ---
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Hentikan kirim form konvensional bawaan browser

        const isiKirimanText = document.getElementById('isiKiriman').value.trim();

        // Validasi utama: kiriman tidak boleh benar-benar kosong
        if (isiKirimanText === "" && !fileSiapKirim) {
            alert("Gagal menerbitkan! Kiriman Anda tidak boleh kosong.");
            return;
        }

        // Kunci tombol kirim dan tampilkan pesan loading animasi tunggal yang bersih
        btnSubmit.disabled = true;
        btnSubmit.style.backgroundColor = '#4a094a';
        btnSubmit.style.cursor = 'not-allowed';
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim Postingan...';

        // Racik muatan data manual menggunakan objek FormData JavaScript
        const formData = new FormData();
        formData.append('isi_kiriman', isiKirimanText);
        
        // --- BERIKAN SELIPAN CSRF TOKEN KE DALAM FORMDATA AJAX ---
        formData.append('csrf_token', csrfToken);

        // Jika user menyertakan lampiran file, pasangkan file siap kirim tersebut ke nama input asalnya
        if (fileSiapKirim && namaInputAktif !== "") {
            formData.append(namaInputAktif, fileSiapKirim);
        }

        // Kirim paket data ke file backend 'proses_komunitas.php' secara asynchronous
        fetch('proses_komunitas.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Setelah selesai diproses server, arahkan pengguna ke halaman utama komunitas
            window.location.href = 'komunitas.php';
        })
        .catch(error => {
            alert("Terjadi kesalahan koneksi saat mengirim data ke server.");
            btnSubmit.disabled = false;
            btnSubmit.style.backgroundColor = '#6a0d6a';
            btnSubmit.innerHTML = 'KIRIM STATUS';
        });
    });
</script>

<?php include 'footer.php'; ?>

</body>
</html>