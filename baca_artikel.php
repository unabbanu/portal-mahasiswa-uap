<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = 'artikel.php'; 
include 'koneksi.php';

// Cek apakah request meminta respon JSON (lewat mekanisme Fetch JavaScript AJAX)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == '1');

// --- TAMBAHAN KEAMANAN: GENERATE CSRF TOKEN JIKA BELUM ADA ---
if (isset($_SESSION['username']) && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==========================================
// BACKEND HANDLER: PROSES INPUT KOMENTAR BARU
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_komentar'])) {
    // 1. Validasi Login
    if (!isset($_SESSION['username'])) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu!']);
            exit;
        } else {
            echo "<script>alert('Anda harus login terlebih dahulu!'); window.history.back();</script>";
            exit;
        }
    }

    // 2. Validasi CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Token keamanan CSRF tidak valid atau kedaluwarsa.']);
            exit;
        } else {
            die("Error: Request tidak sah (CSRF Token Invalid atau Kedaluwarsa).");
        }
    }

    $post_id  = intval($_POST['post_id']);
    $pembuat  = $_SESSION['username'];
    $komentar = trim($_POST['komentar']);

    if (!empty($komentar) && $post_id > 0) {
        // Simpan komentar baru ke database
        $stmt_insert = $koneksi->prepare("INSERT INTO komentar_artikel (post_id, pembuat, komentar) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("iss", $post_id, $pembuat, $komentar);
        
        if ($stmt_insert->execute()) {
            $inserted_id = $koneksi->insert_id; // Ambil ID komentar untuk kebutuhan render tombol hapus
            
            // ========================================================
            // SEGMEN TAMBAHAN: KIRIM NOTIFIKASI KE PEMILIK ARTIKEL
            // ========================================================
            $stmt_owner = $koneksi->prepare("SELECT pembuat FROM artikel WHERE id = ?");
            $stmt_owner->bind_param("i", $post_id);
            $stmt_owner->execute();
            $res_owner = $stmt_owner->get_result();
            
            if ($res_owner->num_rows === 1) {
                $row_owner = $res_owner->fetch_assoc();
                $user_target = !empty($row_owner['pembuat']) ? trim($row_owner['pembuat']) : 'admin';
                
                // Kirim notifikasi HANYA JIKA pembuat komentar BUKAN pemilik artikel itu sendiri
                if ($pembuat !== $user_target) {
                    $tipe = 'komen_artikel';
                    $stmt_notif = $koneksi->prepare("INSERT INTO notifikasi (user_target, user_pemicu, tipe, id_sumber) VALUES (?, ?, ?, ?)");
                    $stmt_notif->bind_param("sssi", $user_target, $pembuat, $tipe, $post_id);
                    $stmt_notif->execute();
                    $stmt_notif->close();
                }
            }
            $stmt_owner->close();
            // ========================================================

            if ($is_ajax) {
                header('Content-Type: application/json');
                
                $waktu_sekarang = date('d M Y, H:i');
                
                // Buat template HTML komentar baru yang instan agar bisa langsung ditempel JavaScript
                $html_komentar = '
                <div class="comment-item" style="position: relative;">
                    <div class="comment-header">
                        <span class="comment-user">'.htmlspecialchars($pembuat).'</span>
                        <span class="comment-date">'.$waktu_sekarang.'</span>
                    </div>
                    <div class="comment-body" style="margin-right: 60px;">'.htmlspecialchars($komentar).'</div>
                    <div style="position: absolute; right: 20px; bottom: 15px;">
                        <form action="" method="POST" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus komentar ini?\');">
                            <input type="hidden" name="csrf_token" value="'.$_SESSION['csrf_token'].'">
                            <input type="hidden" name="comment_id" value="'.$inserted_id.'">
                            <input type="hidden" name="post_id" value="'.$post_id.'">
                            <button type="submit" name="hapus_komentar" style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 0.85rem; font-weight: bold; padding: 0;">
                                ❌ Hapus
                            </button>
                        </form>
                    </div>
                </div>';

                // Hitung total komentar terbaru saat ini
                $stmt_count = $koneksi->prepare("SELECT COUNT(*) as total FROM komentar_artikel WHERE post_id = ?");
                $stmt_count->bind_param("i", $post_id);
                $stmt_count->execute();
                $res_count = $stmt_count->get_result()->fetch_assoc();
                $stmt_count->close();

                echo json_encode([
                    'success' => true,
                    'html' => $html_komentar,
                    'total_comments' => (int)$res_count['total']
                ]);
                exit;
            } else {
                header("Location: baca_artikel.php?id=" . $post_id);
                exit;
            }
        }
        $stmt_insert->close();
    } else {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Komentar tidak boleh kosong!']);
            exit;
        }
    }
}

// ==========================================
// BACKEND HANDLER: PROSES HAPUS KOMENTAR
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_komentar'])) {
    // 1. Pastikan user sudah login
    if (!isset($_SESSION['username'])) {
        echo "<script>alert('Akses ditolak!'); window.history.back();</script>";
        exit;
    }

    // 2. Validasi CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Error: Request tidak sah (CSRF Token Invalid atau Kedaluwarsa).");
    }

    $comment_id = intval($_POST['comment_id']);
    $post_id    = intval($_POST['post_id']);
    $username_sekarang = $_SESSION['username'];

    // 3. Query delete dengan proteksi: ID komentar cocok DAN pembuatnya adalah user yang sedang login atau admin
    if (strtolower(trim($username_sekarang)) === 'admin') {
        $stmt_delete = $koneksi->prepare("DELETE FROM komentar_artikel WHERE id = ?");
        $stmt_delete->bind_param("i", $comment_id);
    } else {
        $stmt_delete = $koneksi->prepare("DELETE FROM komentar_artikel WHERE id = ? AND pembuat = ?");
        $stmt_delete->bind_param("is", $comment_id, $username_sekarang);
    }
    
    if ($stmt_delete->execute()) {
        header("Location: baca_artikel.php?id=" . $post_id);
        exit;
    } else {
        echo "<script>alert('Gagal menghapus komentar.'); window.history.back();</script>";
        exit;
    }
    $stmt_delete->close();
}

// ==========================================
// VALIDASI & AMBIL DATA ARTIKEL UTAMA
// ==========================================
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $id = intval($_GET['id']);
    
    $query = "SELECT judul, kategori, gambar, konten, tanggal, pembuat, file_pdf FROM artikel WHERE id = ?";
    $stmt  = $koneksi->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();
    } else {
        $data = false;
    }
    $stmt->close();
    
    if (!$data) {
        include 'header.php';
        echo "<div style='text-align:center; padding:80px 20px; color:#ffffff; min-height:calc(100vh - 200px); display:flex; flex-direction:column; align-items:center; justify-content:center;'>
                <h2 style='font-size:2rem; margin-bottom:10px;'>Artikel tidak ditemukan.</h2>
                <p style='color:#eee; margin-bottom:20px;'>Artikel mungkin telah dihapus atau dipindahkan.</p>
                <a href='artikel.php' style='color:#fff; font-weight:bold; text-decoration:none; border:2px solid #fff; padding:10px 20px; border-radius:8px;'>&larr; Kembali ke Pusat Pengelola</a>
              </div>";
        include 'footer.php';
        exit;
    }
} else {
    header("Location: artikel.php");
    exit;
}

// ==========================================
// AMBIL DAFTAR KOMENTAR UNTUK ARTIKEL INI
// ==========================================
$query_komentar = "SELECT Id, pembuat, komentar, created_at FROM komentar_artikel WHERE post_id = ? ORDER BY created_at ASC";
$stmt_komend = $koneksi->prepare($query_komentar);
$stmt_komend->bind_param("i", $id);
$stmt_komend->execute();
$result_komentar = $stmt_komend->get_result();

$list_komentar = [];
while ($row = $result_komentar->fetch_assoc()) {
    $list_komentar[] = $row;
}
$stmt_komend->close();

$pembuat_username      = !empty($data['pembuat']) ? trim($data['pembuat']) : 'admin';
$nama_tampilan_pembuat = ucwords(str_replace('_', ' ', $pembuat_username));
$path_gambar           = trim($data['gambar']);
$path_pdf              = isset($data['file_pdf']) ? trim($data['file_pdf']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baca Artikel - <?= htmlspecialchars($data['judul']); ?></title>
    <link class="image-clickable" rel="icon" type="image/x-icon" href="logo1.ico?v=1">
</head>
<body>

<?php include 'header.php'; ?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

    @keyframes fadeInUp { 
        from { opacity: 0; transform: translateY(25px); } 
        to   { opacity: 1; transform: translateY(0); } 
    }

    body { 
        background: linear-gradient(rgba(106,13,106,0.9), rgba(106,13,106,0.9)), url('logo.png');
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
        width: 100%;
    }

    .page-container {
        width: 100%;
        max-width: 900px;
        animation: fadeInUp 0.7s ease-out;
    }

    .article-container {
        background: rgba(255,255,255,0.95);
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        border-top: 5px solid #6a0d6a;
        width: 100%;
    }

    .article-meta-info {
        margin-bottom: 20px;
        font-size: 0.95rem;
        color: #666;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .category-tag {
        background-color: #6a0d6a;
        color: white;
        padding: 4px 12px;
        border-radius: 6px;
        font-weight: bold;
        font-size: 0.85rem;
        text-transform: uppercase;
    }

    .article-main-title {
        color: #4a094a;
        font-size: 2.4rem;
        line-height: 1.3;
        margin-bottom: 30px;
        font-weight: 700;
    }

    .article-image-frame {
        width: 100%;
        max-height: 480px;
        overflow: hidden;
        border-radius: 10px;
        margin-bottom: 35px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .article-image-frame img {
        width: 100%; height: 100%; object-fit: cover;
    }

    .article-body-text {
        color: #333;
        font-size: 1.15rem;
        line-height: 1.9;
        text-align: justify;
        white-space: pre-line;
        margin-bottom: 30px;
    }

    .pdf-download-box {
        background-color: #fdf8fd;
        border: 2px dashed #6a0d6a;
        padding: 20px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    .pdf-info-text {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #4a094a;
        font-weight: 600;
        font-size: 1.05rem;
    }
    .pdf-icon { font-size: 2rem; }

    .btn-download-pdf {
        background-color: #6a0d6a;
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: bold;
        transition: 0.3s;
    }
    .btn-download-pdf:hover { background-color: #4a094a; transform: translateY(-2px); }

    .back-navigation {
        margin-top: 40px;
        border-top: 1px solid #eee;
        padding-top: 20px;
    }
    .btn-return {
        display: inline-block;
        color: #6a0d6a;
        text-decoration: none;
        font-weight: bold;
        transition: 0.2s;
    }
    .btn-return:hover { color: #4a094a; }

    /* ===== AREA DISKUSI/KOMENTAR ===== */
    .discussion-section {
        margin-top: 40px;
        border-top: 2px solid #f0e6f0;
        padding-top: 30px;
    }
    .discussion-title {
        color: #4a094a;
        font-size: 1.4rem;
        margin-bottom: 20px;
        font-weight: 700;
    }
    .comment-form-box {
        background: #fdfafdfd;
        border: 1px solid #e1cfe1;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 30px;
    }
    .comment-textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #c7aec7;
        border-radius: 8px;
        resize: vertical;
        font-size: 1rem;
        margin-bottom: 12px;
    }
    .comment-textarea:focus {
        outline: none;
        border-color: #6a0d6a;
        box-shadow: 0 0 5px rgba(106,13,106,0.2);
    }
    .btn-submit-comment {
        background-color: #6a0d6a;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-submit-comment:hover { background-color: #4a094a; }
    
    .comment-item {
        background: #fff;
        border: 1px solid #eee;
        border-left: 4px solid #6a0d6a;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        animation: fadeInComment 0.3s ease-out;
    }
    .comment-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
        font-size: 0.9rem;
    }
    .comment-user { color: #6a0d6a; font-weight: bold; }
    .comment-date { color: #888; }
    .comment-body { color: #444; line-height: 1.6; white-space: pre-line; }

    @keyframes fadeInComment {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    html {
    scroll-behavior: smooth;
    }

    /* ===== MOBILE ===== */
    @media (max-width: 768px) {
        .main-content { padding: 30px 15px; }

        .article-container { 
            padding: 25px 18px;
            width: 100%;
            max-width: 100%;   
        }

        .article-main-title { 
            font-size: 1.6rem; 
            margin-bottom: 16px; 
        }
        .article-meta-info  { font-size: 0.85rem; gap: 10px; }
        .article-body-text  { font-size: 1rem; line-height: 1.8; }
        .article-image-frame { max-height: 240px; margin-bottom: 20px; }

        .pdf-download-box { 
            flex-direction: column; 
            text-align: center; 
            align-items: center;
        }
        .btn-download-pdf { width: 100%; text-align: center; }
    }

    @media (max-width: 360px) {
        .article-main-title { font-size: 1.3rem; }
        .article-body-text  { font-size: 0.95rem; }
    }
</style>

<div class="main-content">
    <div class="page-container">
        <article class="article-container">

            <div class="article-meta-info">
                <span class="category-tag"><?= htmlspecialchars($data['kategori']); ?></span>
                <span>Oleh: <b style="color:#6a0d6a;">@<?= htmlspecialchars($nama_tampilan_pembuat); ?></b></span>
                <span>&bull;</span>
                <span>Rilis: <b><?= date('d M Y', strtotime($data['tanggal'])); ?></b></span>
            </div>

            <h1 class="article-main-title"><?= htmlspecialchars($data['judul']); ?></h1>

            <?php if (!empty($path_gambar) && file_exists($path_gambar)): ?>
                <div class="article-image-frame">
                    <img src="<?= htmlspecialchars($path_gambar); ?>" alt="Sampul Artikel" class="image-clickable">
                </div>
            <?php elseif (!empty($path_gambar) && file_exists('uploads/artikel/' . $path_gambar)): ?>
                <div class="article-image-frame">
                    <img src="uploads/artikel/<?= htmlspecialchars($path_gambar); ?>" alt="Sampul Artikel" class="image-clickable">
                </div>
            <?php endif; ?>

            <div class="article-body-text">
                <?= htmlspecialchars($data['konten']); ?>
            </div>

            <?php if (!empty($path_pdf) && file_exists($path_pdf)): ?>
                <div class="pdf-download-box">
                    <div class="pdf-info-text">
                        <span class="pdf-icon">📄</span>
                        <span>Dokumen Lampiran Pendukung Resmi (PDF)</span>
                    </div>
                    <a href="<?= htmlspecialchars($path_pdf); ?>" target="_blank" class="btn-download-pdf">📥 Unduh Lampiran</a>
                </div>
            <?php endif; ?>

            <div class="discussion-section">
                <h2 class="discussion-title" id="discussion-counter">💬 Diskusi Komunitas (<?= count($list_komentar); ?>)</h2>

                <div class="comments-list" id="comments-container-box">
                    <?php if (empty($list_komentar)): ?>
                        <p id="empty-comment-notice" style="color: #888; font-style: italic; text-align: center; padding: 10px 0; margin-bottom: 20px;">Belum ada diskusi di artikel ini. Yuk, mulai diskusinya!</p>
                    <?php else: ?>
                        <?php foreach ($list_komentar as $komend): ?>
                            <div class="comment-item" style="position: relative;">
                                <div class="comment-header">
                                    <span class="comment-user"><?= htmlspecialchars($komend['pembuat']); ?></span>
                                    <span class="comment-date"><?= date('d M Y, H:i', strtotime($komend['created_at'])); ?></span>
                                </div>
                                <div class="comment-body" style="margin-right: 60px;"><?= htmlspecialchars($komend['komentar']); ?></div>

                                <?php 
                                if (isset($_SESSION['username'])): 
                                    $user_login = strtolower(trim($_SESSION['username']));
                                    $pembuat_komentar = strtolower(trim($komend['pembuat']));
                                    
                                    if ($user_login === $pembuat_komentar || $user_login === 'admin'): 
                                ?>
                                    <div style="position: absolute; right: 20px; bottom: 15px;">
                                        <form action="" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini?');">
                                            <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
                                            <input type="hidden" name="comment_id" value="<?= $komend['Id']; ?>">
                                            <input type="hidden" name="post_id" value="<?= $id; ?>">
                                            <button type="submit" name="hapus_komentar" style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 0.85rem; font-weight: bold; padding: 0;">
                                                ❌ Hapus
                                            </button>
                                        </form>
                                    </div>
                                <?php 
                                    endif;
                                endif; 
                                ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="comment-form-box" style="margin-top: 20px;">
                <?php if (isset($_SESSION['username'])): ?>
                    <form action="" method="POST" id="article-comment-form">
                        <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>">
                        <input type="hidden" name="post_id" value="<?= $id; ?>">
                        <textarea name="komentar" class="comment-textarea" rows="3" placeholder="Tulis komentar atau opini Anda mengenai artikel ini..." required></textarea>
                        <button type="submit" name="kirim_komentar" class="btn-submit-comment">Kirim Komentar</button>
                    </form>
                <?php else: ?>
                    <p style="color: #666; font-style: italic; text-align: center;">
                        Anda harus <a href="login.php" style="color: #6a0d6a; font-weight: bold; text-decoration: none;">Login</a> terlebih dahulu untuk ikut berdiskusi di artikel ini.
                    </p>
                <?php endif; ?>
            </div>
        

            <div class="back-navigation">
                <a href="artikel.php" class="btn-return">&larr; Kembali ke Pusat Pengelola</a>
            </div>

        </article>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const commentForm = document.getElementById('article-comment-form');
    
    if (commentForm) {
        commentForm.addEventListener('submit', async (e) => {
            e.preventDefault(); // Mencegah reload halaman bawaan browser

            const formData = new FormData(commentForm);
            formData.append('ajax', '1'); // Penanda request asinkronus ke backend
            formData.append('kirim_komentar', '1'); // Memastikan isset($_POST['kirim_komentar']) terpenuhi

            const textarea = commentForm.querySelector('.comment-textarea');
            const containerBox = document.getElementById('comments-container-box');
            const counterTitle = document.getElementById('discussion-counter');
            const emptyNotice = document.getElementById('empty-comment-notice');

            try {
                const response = await fetch('baca_artikel.php?id=' + formData.get('post_id'), {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                
                const data = await response.json();

                if (data.success) {
                    // Jika ini komentar pertama, hapus teks pemberitahuan "Belum ada diskusi"
                    if (emptyNotice) {
                        emptyNotice.remove();
                    }

                    // Tempel elemen HTML komentar baru secara instan di dalam container list
                    containerBox.insertAdjacentHTML('beforeend', data.html);
                    
                    // Kosongkan kolom textarea masukan teks setelah berhasil terkirim
                    textarea.value = '';

                    // Perbarui counter teks jumlah komentar terhitung ("💬 Diskusi Komunitas (X)")
                    counterTitle.textContent = `💬 Diskusi Komunitas (${data.total_comments})`;
                } else {
                    alert(data.message);
                }
            } catch (err) {
                console.error("Gagal mengirim komentar artikel via AJAX:", err);
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>