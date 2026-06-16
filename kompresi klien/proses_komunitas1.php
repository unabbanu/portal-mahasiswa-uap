<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
require_once 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $konten = isset($_POST['isi_kiriman']) ? trim($_POST['isi_kiriman']) : '';
    $pembuat = $_SESSION['username'];
    
    $jalur_gambar = null;
    $jalur_video = null;

    $file_target = null;
    $jenis_media = null;
    
    // 1. STRATEGI DETEKSI UNTUK SMARTPHONE
    if (isset($_FILES['video_kamera']) && $_FILES['video_kamera']['error'] === UPLOAD_ERR_OK) {
        $file_target = $_FILES['video_kamera'];
        $jenis_media = 'video';
    } elseif (isset($_FILES['foto_kamera']) && $_FILES['foto_kamera']['error'] === UPLOAD_ERR_OK) {
        $file_target = $_FILES['foto_kamera'];
        $jenis_media = 'gambar';
    } elseif (isset($_FILES['media_galeri']) && $_FILES['media_galeri']['error'] === UPLOAD_ERR_OK) {
        $file_target = $_FILES['media_galeri'];
        $tipe_mime = strtolower($file_target['type']);
        
        // Cek berdasarkan MIME Type atau ekstensi file galeri
        $ekstensi_asal = strtolower(pathinfo($file_target['name'], PATHINFO_EXTENSION));
        if (str_contains($tipe_mime, 'video') || in_array($ekstensi_asal, ['mp4', 'webm', 'mov', '3gp', 'mkv'])) {
            $jenis_media = 'video';
        } else {
            $jenis_media = 'gambar';
        }
    }

    if (empty($konten) && $file_target === null) {
        echo "<script>alert('Gagal menerbitkan! Kiriman Anda tidak boleh kosong.'); window.history.back();</script>";
        exit();
    }

    if ($file_target !== null) {
        $file_tmp  = $file_target['tmp_name'];
        $file_name = $file_target['name'];
        $file_size = $file_target['size'];
        
        // Ambil ekstensi dan paksa ke huruf kecil semua
        $ekstensi_file = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // ANTISIPASI: Jika rekaman langsung dari HP tidak menyertakan ekstensi di namanya
        if (empty($ekstensi_file)) {
            $tipe_mime_langsung = strtolower($file_target['type']);
            if (str_contains($tipe_mime_langsung, 'video/quicktime') || str_contains($tipe_mime_langsung, 'mov')) {
                $ekstensi_file = 'mov';
            } elseif (str_contains($tipe_mime_langsung, 'video/3gpp')) {
                $ekstensi_file = '3gp';
            } elseif (str_contains($tipe_mime_langsung, 'video/webm')) {
                $ekstensi_file = 'webm';
            } else {
                $ekstensi_file = 'mp4'; 
            }
        }
        
        if ($jenis_media === 'gambar') {
            $ekstensi_diperbolehkan = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $ukuran_maksimal = 10 * 1024 * 1024; // 10MB
            $pesan_ukuran_error = 'Ukuran foto lampiran terlalu besar (Maksimal 10MB).';
            $pesan_ekstensi_error = 'Format ekstensi gambar tidak didukung oleh sistem.';
            $prefix_nama = 'post_img_';
        } else {
            // Ditambahkan 'webm' ke dalam daftar putih agar video terkompresi frontend lolos filter
            $ekstensi_diperbolehkan = ['mp4', 'webm', 'mov', 'mkv', '3gp', 'quicktime'];
            $ukuran_maksimal = 100 * 1024 * 1024; // 100MB
            $pesan_ukuran_error = 'Ukuran video hasil rekaman terlalu besar.';
            $pesan_ekstensi_error = 'Format video HP Anda (' . $ekstensi_file . ') belum diizinkan oleh sistem FTI HUB.';
            $prefix_nama = 'post_vid_';
        }
        
        if (in_array($ekstensi_file, $ekstensi_diperbolehkan) || $jenis_media === 'video') { 
            if ($file_size <= $ukuran_maksimal) {
                if (!is_dir('uploads/komunitas')) {
                    mkdir('uploads/komunitas', 0755, true);
                }
                
                // Pastikan file baru disimpan dengan ekstensi yang valid sesuai input asalnya
                $ekstensi_final = in_array($ekstensi_file, ['mp4', 'webm', 'mov', 'mkv', '3gp']) ? $ekstensi_file : 'mp4';
                $nama_file_baru = $prefix_nama . time() . '_' . rand(100, 999) . '.' . $ekstensi_final;
                $jalur_tujuan = 'uploads/komunitas/' . $nama_file_baru;
                
                if (move_uploaded_file($file_tmp, $jalur_tujuan)) {
                    if ($jenis_media === 'gambar') {
                        $jalur_gambar = $jalur_tujuan;
                    } else {
                        $jalur_video = $jalur_tujuan;
                    }
                } else {
                    echo "<script>alert('Gagal mengunggah! Server gagal memindahkan file rekaman kamera.'); window.history.back();</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Gagal menerbitkan! $pesan_ukuran_error'); window.history.back();</script>";
                exit();
            }
        } else {
            echo "<script>alert('Gagal menerbitkan! $pesan_ekstensi_error'); window.history.back();</script>";
            exit();
        }
    }

    if (!empty($konten) || $jalur_gambar !== null || $jalur_video !== null) {
        try {
            $stmt = $koneksi->prepare("INSERT INTO komunitas (pembuat, konten, gambar, video) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $pembuat, $konten, $jalur_gambar, $jalur_video);
            $stmt->execute();
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            if ($jalur_gambar !== null && file_exists($jalur_gambar)) { unlink($jalur_gambar); }
            if ($jalur_video !== null && file_exists($jalur_video)) { unlink($jalur_video); }
            echo "<script>alert('Gagal memproses pengiriman data ke server database.'); window.history.back();</script>";
            exit();
        }
    }
}

header("Location: komunitas.php");
exit();
?>