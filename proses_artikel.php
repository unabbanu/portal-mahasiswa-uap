<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. PROTEKSI UTAMA: Tolak penyimpanan jika session username kosong demi mematuhi aturan Foreign Key
if (!isset($_SESSION['username']) || empty(trim($_SESSION['username']))) {
    echo "<script>alert('Akses Ditolak! Sesi login Anda tidak valid atau telah berakhir. Silakan login kembali.'); window.location.href='login.php';</script>";
    exit();
}

// 2. Hubungkan koneksi database MySQLi Anda
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- TAMBAHAN KEAMANAN: VALIDASI CSRF TOKEN ---
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Request tidak sah atau sesi form telah kedaluwarsa (CSRF Invalid).");
    }

    // Ambil data kiriman form dan bersihkan spasi
    $judul    = trim($_POST['judul']);
    $kategori = trim($_POST['kategori']);
    $konten   = trim($_POST['konten']);
    $status   = trim($_POST['status']);
    
    // Nilai pembuat wajib diambil langsung dari session yang sudah terdaftar di tabel user
    $pembuat  = trim($_SESSION['username']);
    $tanggal  = date('Y-m-d'); 
    $jalur_gambar = ""; 
    $jalur_pdf    = ""; // Menyimpan path file PDF

    // 3. Validasi Proses Unggah Gambar Sampul (Maksimal 2MB)
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_size = $_FILES['gambar']['size'];
        
        $ekstensi_diperbolehkan = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi_file = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($ekstensi_file, $ekstensi_diperbolehkan)) {
            echo "<script>alert('Format gambar salah! Hanya menerima JPG, JPEG, PNG, dan WEBP.'); window.history.back();</script>";
            exit();
        } 
        if ($file_size > 10 * 1024 * 1024) {
            echo "<script>alert('Ukuran gambar terlalu besar! Maksimal kapasitas file adalah 10MB.'); window.history.back();</script>";
            exit();
        } 

        if (!is_dir('uploads/artikel')) {
            mkdir('uploads/artikel', 0755, true);
        }
        $nama_gambar_baru = 'art_' . time() . '_' . rand(100, 999) . '.' . $ekstensi_file;
        $jalur_gambar     = 'uploads/artikel/' . $nama_gambar_baru;
        move_uploaded_file($file_tmp, $jalur_gambar);
    }

    // ==========================================================================
    // VALIDASI PROSES UNGHAH FILE PDF (Maksimal 5MB)
    // ==========================================================================
    if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === UPLOAD_ERR_OK) {
        $pdf_tmp  = $_FILES['file_pdf']['tmp_name'];
        $pdf_name = $_FILES['file_pdf']['name'];
        $pdf_size = $_FILES['file_pdf']['size'];
        
        $ekstensi_pdf = strtolower(pathinfo($pdf_name, PATHINFO_EXTENSION));
        
        if ($ekstensi_pdf !== 'pdf') {
            if (!empty($jalur_gambar) && file_exists($jalur_gambar)) { unlink($jalur_gambar); }
            echo "<script>alert('Format lampiran salah! Sistem hanya menerima file dokumen berformat PDF.'); window.history.back();</script>";
            exit();
        } 
        if ($pdf_size > 10 * 1024 * 1024) {
            if (!empty($jalur_gambar) && file_exists($jalur_gambar)) { unlink($jalur_gambar); }
            echo "<script>alert('Ukuran berkas PDF terlalu besar! Kapasitas maksimal adalah 10MB.'); window.history.back();</script>";
            exit();
        } 

        if (!is_dir('uploads/dokumen')) {
            mkdir('uploads/dokumen', 0755, true);
        }
        $nama_pdf_baru = 'doc_' . time() . '_' . rand(100, 999) . '.pdf';
        $jalur_pdf     = 'uploads/dokumen/' . $nama_pdf_baru;
        move_uploaded_file($pdf_tmp, $jalur_pdf);
    }

    // 4. Eksekusi Penyimpanan Data Menggunakan Prepared Statement (8 Kolom)
    try {
        $query = "INSERT INTO artikel (judul, kategori, gambar, konten, status, pembuat, tanggal, file_pdf) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt  = $koneksi->prepare($query);
        $stmt->bind_param("ssssssss", $judul, $kategori, $jalur_gambar, $konten, $status, $pembuat, $tanggal, $jalur_pdf);
        
        if ($stmt->execute()) {
            echo "<script>alert('Selamat! Artikel baru berhasil diterbitkan.'); window.location.href='artikel.php';</script>";
            exit();
        } else {
            if (!empty($jalur_gambar) && file_exists($jalur_gambar)) { unlink($jalur_gambar); }
            if (!empty($jalur_pdf) && file_exists($jalur_pdf)) { unlink($jalur_pdf); }
            echo "<script>alert('Gagal menyimpan data ke sistem database.'); window.history.back();</script>";
            exit();
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        if (!empty($jalur_gambar) && file_exists($jalur_gambar)) { unlink($jalur_gambar); }
        if (!empty($jalur_pdf) && file_exists($jalur_pdf)) { unlink($jalur_pdf); }
        
        if ($e->getCode() == 1452) {
            echo "<script>alert('Gagal Menerbitkan! Username Akun Anda (@" . htmlspecialchars($pembuat) . ") tidak terdaftar sah di database. Silakan Logout dan Login kembali.'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Kesalahan Sistem: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        }
        exit();
    }

} else {
    header("Location: artikel.php");
    exit();
}
?>