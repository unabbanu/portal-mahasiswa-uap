<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Proteksi Halaman: Wajib login untuk mengeksekusi perubahan data
if (!isset($_SESSION['username']) || empty(trim($_SESSION['username']))) {
    echo "<script>alert('Akses Ditolak! Silakan login terlebih dahulu.'); window.location.href='login.php';</script>";
    exit();
}

// Hubungkan jembatan database MySQLi Anda
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- TAMBAHAN KEAMANAN: VALIDASI CSRF TOKEN ---
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Request tidak sah atau sesi form telah kedaluwarsa (CSRF Invalid).");
    }

    // Paksa ID menjadi tipe data Integer murni
    $id       = intval($_POST['id']); 
    $judul    = trim($_POST['judul']);
    $kategori = trim($_POST['kategori']);
    $konten   = trim($_POST['konten']);
    $status   = trim($_POST['status']);
    
    // 2. Ambil data path gambar, file_pdf lama dan pembuat untuk validasi hak akses kepemilikan
    $stmt_lama = $koneksi->prepare("SELECT gambar, pembuat, file_pdf FROM artikel WHERE id = ?");
    $stmt_lama->bind_param("i", $id);
    $stmt_lama->execute();
    $result_lama = $stmt_lama->get_result();
    
    if ($result_lama->num_rows === 0) {
        $stmt_lama->close();
        echo "<script>alert('Gagal memproses! Data artikel tidak ditemukan.'); window.location.href='artikel.php';</script>";
        exit();
    }
    
    $data_lama  = $result_lama->fetch_assoc();
    $stmt_lama->close();
    
    // --- SINKRONISASI PROTEKSI: Amankan string username penyerang/pengedit (Anti-Bypass) ---
    $user_login      = strtolower(trim($_SESSION['username']));
    $pembuat_artikel = strtolower(trim($data_lama['pembuat']));

    // MODIFIKASI: Hanya pembuat asli ATAU akun 'admin' yang diperbolehkan menyimpan perubahan
    if ($user_login !== $pembuat_artikel && $user_login !== 'admin') {
        echo "<script>alert('Akses Ditolak! Anda tidak berhak mengubah isi artikel milik akun lain.'); window.location.href='artikel.php';</script>";
        exit();
    }

    $jalur_gambar_sekarang = $data_lama['gambar'];   // Jalur default jika tidak ganti gambar
    $jalur_pdf_sekarang    = $data_lama['file_pdf']; // Jalur default jika tidak ganti file PDF

    // 3. Logika validasi dan unggah berkas jika user memilih file gambar baru
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $gambar_nama   = $_FILES['gambar']['name'];
        $gambar_ukuran = $_FILES['gambar']['size'];
        $gambar_tmp    = $_FILES['gambar']['tmp_name'];
        
        $ekstensi_valid = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi_file  = strtolower(pathinfo($gambar_nama, PATHINFO_EXTENSION));

        if (!in_array($ekstensi_file, $ekstensi_valid)) {
            echo "<script>alert('Format file tidak didukung! Hanya JPG, JPEG, PNG, dan WEBP.'); window.history.back();</script>";
            exit();
        }

        if ($gambar_ukuran > 10 * 1024 * 1024) { // Batasan maksimal 10MB
            echo "<script>alert('Ukuran berkas gambar terlalu besar! Maksimal 10MB.'); window.history.back();</script>";
            exit();
        }
        
        if (!is_dir('uploads/artikel')) {
            mkdir('uploads/artikel', 0755, true);
        }

        if (!is_writable('uploads/artikel')) {
            echo "<script>alert('Error: Folder penyimpanan gambar tidak dapat ditulis. Periksa permission server.'); window.history.back();</script>";
            exit();
        }

        $nama_gambar_baru = 'art_' . time() . '_' . rand(100, 999) . '.' . $ekstensi_file;
        $target_jalur     = 'uploads/artikel/' . $nama_gambar_baru;

        if (move_uploaded_file($gambar_tmp, $target_jalur)) {
            if (!empty($data_lama['gambar']) && file_exists($data_lama['gambar'])) {
                unlink($data_lama['gambar']);
            }
            $jalur_gambar_sekarang = $target_jalur;
        } else {
            echo "<script>alert('Terjadi kesalahan teknis saat mengunggah gambar baru ke server.'); window.history.back();</script>";
            exit();
        }
    }

    // 4. Logika validasi dan unggah berkas file PDF baru (Maksimal 10MB)
    if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === UPLOAD_ERR_OK) {
        $pdf_nama   = $_FILES['file_pdf']['name'];
        $pdf_ukuran = $_FILES['file_pdf']['size'];
        $pdf_tmp    = $_FILES['file_pdf']['tmp_name'];
        
        $ekstensi_pdf = strtolower(pathinfo($pdf_nama, PATHINFO_EXTENSION));

        if ($ekstensi_pdf !== 'pdf') {
            echo "<script>alert('Format lampiran salah! Sistem hanya menerima file dokumen berformat PDF.'); window.history.back();</script>";
            exit();
        }

        if ($pdf_ukuran > 10 * 1024 * 1024) { // Batasan maksimal 10MB
            echo "<script>alert('Ukuran berkas PDF terlalu besar! Maksimal kapasitas file adalah 10MB.'); window.history.back();</script>";
            exit();
        }
        
        if (!is_dir('uploads/dokumen')) {
            mkdir('uploads/dokumen', 0755, true);
        }

        if (!is_writable('uploads/dokumen')) {
            echo "<script>alert('Error: Folder penyimpanan dokumen tidak dapat ditulis. Periksa permission server.'); window.history.back();</script>";
            exit();
        }

        $nama_pdf_baru = 'doc_' . time() . '_' . rand(100, 999) . '.pdf';
        $target_pdf    = 'uploads/dokumen/' . $nama_pdf_baru;

        if (move_uploaded_file($pdf_tmp, $target_pdf)) {
            if (!empty($data_lama['file_pdf']) && file_exists($data_lama['file_pdf'])) {
                unlink($data_lama['file_pdf']);
            }
            $jalur_pdf_sekarang = $target_pdf;
        } else {
            echo "<script>alert('Terjadi kesalahan teknis saat mengunggah berkas PDF baru ke server.'); window.history.back();</script>";
            exit();
        }
    }

    // 5. Eksekusi Query UPDATE Menggunakan Prepared Statement
    $query_update = "UPDATE artikel SET judul = ?, kategori = ?, gambar = ?, konten = ?, status = ?, file_pdf = ? WHERE id = ?";
    $stmt_update  = $koneksi->prepare($query_update);
    
    if ($stmt_update) {
        $stmt_update->bind_param("ssssssi", $judul, $kategori, $jalur_gambar_sekarang, $konten, $status, $jalur_pdf_sekarang, $id);

        if ($stmt_update->execute()) {
            $stmt_update->close(); 
            echo "<script>alert('Perubahan data artikel berhasil disimpan!'); window.location.href='artikel.php';</script>";
            exit();
        } else {
            $error_msg = addslashes($stmt_update->error);
            $stmt_update->close(); 
            echo "<script>alert('Gagal memperbarui data artikel. Sistem Error: " . $error_msg . "'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Gagal mempersiapkan sistem query database.'); window.history.back();</script>";
        exit();
    }

} else {
    header('Location: artikel.php');
    exit();
}
?>