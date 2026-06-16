<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$current_page = 'komunitas.php'; // Tetap nyalakan menu Komunitas di navbar

// --- TAMBAHAN KEAMANAN: GENERATE CSRF TOKEN ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sosial Komunitas</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">
</head>
<body>

<?php include 'header.php'; ?>

<style>
    /* --- RESET DASAR --- */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }

    html {
    scroll-behavior: smooth;
    }
    
    html, body { 
        width: 100%;
        overflow-x: hidden;
        background: linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), url('logo.png');
        background-size: 100px; background-repeat: repeat; min-height: 100vh;
    }
    
    .main-content { 
        width: 100%;
        min-height: calc(100vh - 170px); 
        padding: 40px 20px; 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
    }
    
    .forum-container { 
        width: 100%; 
        max-width: 650px; 
        animation: fadeIn 0.8s ease-out; 
    }
    
    .forum-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid rgba(255, 255, 255, 0.3); padding-bottom: 15px; }
    .forum-header h2 { color: #ffffff; font-size: 1.8rem; text-shadow: 0 2px 5px rgba(0,0,0,0.3); }
    
    .btn-trigger-post {
        background-color: #ffffff; color: #6a0d6a; padding: 10px 20px; border-radius: 8px;
        text-decoration: none; font-weight: bold; transition: 0.3s; display: inline-block;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .btn-trigger-post:hover { background-color: #fcecfc; transform: translateY(-2px); }
    
    .feed-card { 
        background: rgba(255, 255, 255, 0.95); 
        backdrop-filter: blur(10px); 
        border-radius: 14px; 
        padding: 20px; 
        margin-bottom: 25px; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.2); 
        border: 1px solid rgba(255,255,255,0.1); 
        text-align: left;
        position: relative; 
        overflow: visible !important; 
        z-index: 10;
    }

    .feed-card:focus-within,
    .feed-card:hover {
        z-index: 15;
    }

    .post-header { display: flex; gap: 15px; align-items: center; margin-bottom: 15px; }
    .author-avatar { width: 48px; height: 48px; border-radius: 10px; object-fit: cover; border: 2px solid #6a0d6a; background-color: #eee; cursor: pointer; transition: transform 0.2s ease; }
    .author-avatar:hover { transform: scale(1.05); }
    .author-name { color: #6a0d6a; font-weight: 700; font-size: 1rem; display: block; }
    .post-time { color: #777; font-size: 0.8rem; }
    .post-text { color: #222; font-size: 1.05rem; line-height: 1.5; white-space: pre-line; margin-bottom: 15px; }
    .post-image-attached { width: 100%; max-height: 400px; object-fit: cover; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #eee; cursor: pointer; transition: transform 0.2s ease; }
    .post-image-attached:hover { transform: scale(1.01); }
    .post-video-attached { width: 100%; max-height: 400px; background: #000; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 1px solid #eee; display: block; }

    .action-bar { 
        display: flex; 
        gap: 20px; 
        border-top: 1px solid #eee; 
        border-bottom: 1px solid #eee; 
        padding: 10px 5px; 
        margin-bottom: 15px; 
        position: relative;
        overflow: visible !important;
    }
    .action-link { text-decoration: none; font-weight: bold; font-size: 0.9rem; display: flex; align-items: center; gap: 5px; transition: 0.2s; }
    .link-like { color: #dc3545; cursor: pointer; } 
    .link-comment { color: #007bff; cursor: pointer; user-select: none; } 
    .action-link:hover { transform: scale(1.02); }
    
    .like-count-display { display: flex; align-items: center; cursor: pointer; }
    
    .like-dropdown {
        position: absolute; 
        top: 40px; 
        left: 75px; 
        background: #ffffff !important; 
        min-width: 200px;
        max-width: 280px;
        max-height: 220px; 
        overflow-y: auto; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important; 
        border: 1px solid #d1d1d1 !important; 
        border-radius: 8px;
        padding: 6px 0; 
        z-index: 999999 !important; 
        display: none; 
        animation: fadeIn 0.15s ease-out;
    }
    .like-dropdown.show { display: block !important; }
    .like-dropdown-item { padding: 8px 15px; font-size: 0.85rem; color: #333; font-weight: 600; border-bottom: 1px solid #f5f5f5; text-align: left; background: #ffffff; }
    .like-dropdown-item:last-child { border-bottom: none; }
    .like-dropdown-header { padding: 6px 15px; font-size: 0.75rem; color: #6a0d6a; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #f2e6f2; margin-bottom: 4px; text-align: left; background: #ffffff; position: sticky; top: 0; }

    .comments-section { 
        background: rgba(106, 13, 106, 0.03); 
        padding: 12px; 
        border-radius: 8px; 
        margin-bottom: 10px; 
        position: relative;
        overflow: visible !important;
    }
    .list-komentar-wrapper { animation: slideDown 0.25s ease-out; margin-bottom: 10px; } 
    .comment-item { display: flex; gap: 10px; margin-bottom: 10px; font-size: 0.9rem; align-items: flex-start; }
    .comment-avatar { width: 32px; height: 32px; border-radius: 6px; object-fit: cover; border: 1px solid #6a0d6a; }
    .comment-box { background: #f0f2f5; padding: 8px 12px; border-radius: 12px; width: 100%; }
    .comment-user { font-weight: bold; color: #4a094a; margin-right: 5px; }
    
    .comment-form { 
        display: flex; 
        gap: 10px; 
        margin-top: 10px; 
        border-top: 1px solid rgba(0,0,0,0.05); 
        padding-top: 10px;
    }
    .comment-input { width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 20px; font-size: 0.9rem; background: #fff; }
    .btn-comment-submit { background: #6a0d6a; color: white; border: none; padding: 0 15px; border-radius: 20px; font-weight: bold; cursor: pointer; font-size: 0.85rem; }
    .empty-feed { background: white; padding: 30px; border-radius: 14px; text-align: center; color: #555; font-weight: 600; font-style: italic; }
    
    .lightbox-modal {
        display: none; position: fixed; z-index: 9999999; left: 0; top: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.9); backdrop-filter: blur(5px); justify-content: center; align-items: center;
    }
    .lightbox-content { max-width: 90%; max-height: 85vh; border-radius: 8px; box-shadow: 0 0 20px rgba(255,255,255,0.1); animation: zoomIn 0.3s ease; }
    .lightbox-close { position: absolute; top: 20px; right: 30px; color: white; font-size: 40px; font-weight: bold; cursor: pointer; transition: 0.2s; }
    .lightbox-close:hover { color: #ffccff; }

    .btn-scroll-top {
        position: fixed; bottom: 25px; right: 25px; z-index: 99999; background-color: #6a0d6a; color: white;
        border: 2px solid rgba(255, 255, 255, 0.2); width: 50px; height: 50px; border-radius: 50%; font-size: 20px;
        cursor: pointer; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3); opacity: 0; visibility: hidden;
        transition: all 0.4s ease; display: flex; align-items: center; justify-content: center;
    }
    .btn-scroll-top:hover { background-color: #4a094a; transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4); }
    .btn-scroll-top.show { opacity: 1; visibility: visible; }
    
    @keyframes fadeIn { from { opacity: 0; transform: translateY(3px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes zoomIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); max-height: 0; overflow: hidden; } to { opacity: 1; transform: translateY(0); max-height: 1000px; } }

    @media (max-width: 768px) {
        .main-content { padding: 40px 15px; }
        .forum-header h2 { font-size: 1.5rem; }
        .btn-trigger-post { padding: 8px 14px; font-size: 1rem; }
        .feed-card { padding: 15px; border-radius: 12px; margin-bottom: 15px; }
        .post-image-attached, .post-video-attached { max-height: 220px; }
        .action-bar { gap: 15px; }
        .like-dropdown { left: 60px; top: 38px; }
        .btn-scroll-top { bottom: 20px; right: 20px; width: 44px; height: 44px; font-size: 18px; }
    }
</style>

<div class="main-content">
    <div class="forum-container">
        
        <div class="forum-header">
            <h2>Sosial Komunitas FTI</h2>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="tambah_postingan.php" class="btn-trigger-post">✍️ BUAT POSTINGAN</a>
            <?php endif; ?>
        </div>

        <div class="forum-feed">
            <?php
            include 'koneksi.php';
            
            $query = "SELECT komunitas.*, user.avatar FROM komunitas 
                      LEFT JOIN user ON LOWER(TRIM(komunitas.pembuat)) = LOWER(TRIM(user.username)) 
                      ORDER BY komunitas.id DESC";
            $sql = mysqli_query($koneksi, $query);

            if ($sql && mysqli_num_rows($sql) > 0) {
                while ($data = mysqli_fetch_assoc($sql)) {
                    $post_id = (int)$data['id'];
                    $nama_tampilan = ucwords(str_replace('_', ' ', $data['pembuat']));
                    $waktu_posting = date('d M Y, H:i', strtotime($data['created_at']));
                    $avatar_path = (!empty($data['avatar']) && file_exists($data['avatar'])) ? $data['avatar'] : 'assets/default-avatar.png';
                    $post_img_path = trim($data['gambar']);
                    $post_vid_path = trim($data['video']); 

                    $stmt_likes = mysqli_prepare($koneksi, "SELECT COUNT(*) as total, GROUP_CONCAT(username SEPARATOR ', ') as daftar_pemberi FROM komunitas_likes WHERE post_id = ?");
                    mysqli_stmt_bind_param($stmt_likes, "i", $post_id);
                    mysqli_stmt_execute($stmt_likes);
                    $q_likes = mysqli_stmt_get_result($stmt_likes);
                    $d_likes = mysqli_fetch_assoc($q_likes);
                    
                    $jumlah_like = $d_likes['total'];
                    $pemberi_array = !empty($d_likes['daftar_pemberi']) ? explode(', ', $d_likes['daftar_pemberi']) : [];
                    
                    $stmt_count_com = mysqli_prepare($koneksi, "SELECT COUNT(*) as total FROM komunitas_komentar WHERE post_id = ?");
                    mysqli_stmt_bind_param($stmt_count_com, "i", $post_id);
                    mysqli_stmt_execute($stmt_count_com);
                    $q_count_com = mysqli_stmt_get_result($stmt_count_com);
                    $d_count_com = mysqli_fetch_assoc($q_count_com);
                    ?>
                    <div class="feed-card" id="post-<?= $post_id; ?>">
                        <div class="post-header" style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; gap: 15px; align-items: center;">
                                <img src="<?= htmlspecialchars($avatar_path); ?>" class="author-avatar image-clickable" alt="Avatar">
                                <div>
                                    <span class="author-name"><?= htmlspecialchars($nama_tampilan); ?></span>
                                    <span class="post-time"><?= htmlspecialchars($waktu_posting); ?> WIB</span>
                                </div>
                            </div>
                            
                            <?php 
                            if (isset($_SESSION['username'])): 
                                $user_login = strtolower(trim($_SESSION['username']));
                                $user_pembuat = strtolower(trim($data['pembuat']));
                                
                                // DI SINI PERBAIKAN: Menggunakan $data, bukan $row yang undefined
                                if ($user_login === $user_pembuat || $user_login === 'admin'): 
                            ?>
                                    <form action="hapus_komunitas.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus postingan ini?');" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="id" value="<?= $data['id']; ?>">
                        
                                        <button type="submit" style="background:none; border:none; color:red; cursor:pointer;">
                                            ❌ Hapus
                                        </button>
                                     </form>
                            <?php 
                                endif;
                            endif; 
                            ?>
                        </div>
                        
                        <?php if (!empty(trim($data['konten']))) : ?>
                            <p class="post-text"><?= htmlspecialchars($data['konten']); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($post_img_path) && file_exists($post_img_path)): ?>
                            <img src="<?= htmlspecialchars($post_img_path); ?>" class="post-image-attached image-clickable" alt="Foto Kiriman">
                        <?php endif; ?>

                        <?php if (!empty($post_vid_path) && file_exists($post_vid_path)): ?>
                            <video src="<?= htmlspecialchars($post_vid_path); ?>" class="post-video-attached" controls preload="metadata"></video>
                        <?php endif; ?>
                        
                        <div class="action-bar">
                            <a href="like_komunitas.php?id=<?= $post_id; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" class="action-link link-like">❤️ Suka</a>
                            
                            <div class="like-count-display toggle-like-dropdown" data-id="<?= $post_id; ?>">
                            <?= (int)$jumlah_like; ?> Orang
                                
                                <div class="like-dropdown" id="dropdown-like-<?= $post_id; ?>">
                                    <div class="like-dropdown-header">Menyukai ini:</div>
                                    <?php if (count($pemberi_array) > 0): ?>
                                        <?php foreach ($pemberi_array as $pemberi): ?>
                                            <div class="like-dropdown-item">
                                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $pemberi))); ?>
                                            </div>
                                        <?php endforeach; ?> <?php else: ?>
                                        <div class="like-dropdown-item" style="color:#888; font-style:italic;">Belum ada suka</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <span class="action-link link-comment btn-toggle-komentar" data-id="<?= $post_id; ?>">💬 Komentar (<?= (int)$d_count_com['total']; ?>)</span>
                        </div>

                        <div class="comments-section">
                            
                            <div class="list-komentar-wrapper" id="comments-area-<?= $post_id; ?>" style="display: none;">
                                <?php
                                $stmt_comment = mysqli_prepare($koneksi, "SELECT kk.*, u.avatar FROM komunitas_komentar kk LEFT JOIN user u ON LOWER(TRIM(kk.pembuat)) = LOWER(TRIM(u.username)) WHERE kk.post_id = ? ORDER BY kk.id ASC");
                                mysqli_stmt_bind_param($stmt_comment, "i", $post_id);
                                mysqli_stmt_execute($stmt_comment);
                                $q_comment = mysqli_stmt_get_result($stmt_comment);

                                while ($com = mysqli_fetch_assoc($q_comment)) {
                                    $com_id = (int)$com['id']; 
                                    $com_nama = ucwords(str_replace('_', ' ', $com['pembuat']));
                                    $com_avatar = (!empty($com['avatar']) && file_exists($com['avatar'])) ? $com['avatar'] : 'assets/default-avatar.png';
                                    ?>
                                    <div class="comment-item" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 10px;">
                                        <div style="display: flex; gap: 10px; align-items: flex-start; flex: 1;">
                                            <img src="<?= htmlspecialchars($com_avatar); ?>" class="comment-avatar image-clickable" alt="User">
                                            <div class="comment-box">
                                                <span class="comment-user"><?= htmlspecialchars($com_nama); ?></span>
                                                <span style="color:#333;"><?= htmlspecialchars($com['komentar']); ?></span>
                                            </div>
                                        </div>
                                    
                                        <?php 
                                        if (isset($_SESSION['username'])): 
                                            $user_login = strtolower(trim($_SESSION['username']));
                                            $pembuat_komentar = strtolower(trim($com['pembuat']));
                        
                                            if ($user_login === $pembuat_komentar || $user_login === 'admin'): 
                                        ?>
                                            <a href="hapus_komentar.php?id=<?= $com_id; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" 
                                                style="text-decoration: none; color: #dc3545; font-size: 0.75rem; font-weight: bold; margin-top: 8px; white-space: nowrap;" 
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus komentar ini?')">
                                                ❌ Hapus
                                            </a>
                                        <?php 
                                            endif;
                                        endif; 
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>

                            <?php if (isset($_SESSION['username'])): ?>
                                <form action="komentar_komunitas.php" method="POST" class="comment-form">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="post_id" value="<?= $post_id; ?>">
                                    <input type="text" name="isi_komentar" class="comment-input" placeholder="Tulis komentar balasan..." required>
                                    <button type="submit" class="btn-comment-submit">Kirim</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php }
            } else {
                echo '<div class="empty-feed">Belum ada kiriman diskusi di forum Sosial Komunitas FTI UAP.</div>';
            }
            ?>
        </div>
    </div>
</div>

<button id="btnScrollTop" class="btn-scroll-top" title="Kembali ke Atas">⬆️</button>

<div id="lightboxModal" class="lightbox-modal">
    <span class="lightbox-close" id="lightboxClose">&times;</span>
    <img class="lightbox-content" id="lightboxTargetImage" src="" alt="Zoom">
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // ==========================================
    // 1. LOGIKA INTERAKSI LIKE VIA AJAX
    // ==========================================
    const likeButtons = document.querySelectorAll('.link-like');
    likeButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault(); // Menghentikan redirect halaman asli browser
            
            const originalUrl = btn.getAttribute('href');
            const ajaxUrl = originalUrl + '&format=json'; 

            try {
                const response = await fetch(ajaxUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();

                if (data.success) {
                    const card = btn.closest('.feed-card');
                    const countDisplay = card.querySelector('.toggle-like-dropdown');
                    const dropdown = card.querySelector('.like-dropdown');
                    
                    // Memperbarui text counter jumlah penyuka tanpa merusak element dropdown di dalamnya
                    countDisplay.childNodes[0].nodeValue = ` ${data.total_likes} Orang `;
                    
                    // Merender ulang struktur isi list nama penyuka di dalam panel dropdown box
                    if (data.pemberi.length > 0) {
                        dropdown.innerHTML = '<div class="like-dropdown-header">Menyukai ini:</div>' + 
                            data.pemberi.map(name => `<div class="like-dropdown-item">${name}</div>`).join('');
                    } else {
                        dropdown.innerHTML = '<div class="like-dropdown-header">Menyukai ini:</div><div class="like-dropdown-item" style="color:#888; font-style:italic;">Belum ada suka</div>';
                    }
                } else {
                    alert(data.message);
                }
            } catch (err) {
                console.error("Gagal memproses request Like AJAX:", err);
            }
        });
    });

    // ==========================================
    // 2. LOGIKA INPUT DATA KOMENTAR VIA AJAX
    // ==========================================
    const commentForms = document.querySelectorAll('.comment-form');
    commentForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault(); // Mencegah form melakukan muat ulang/pindah halaman web

            const formData = new FormData(form);
            formData.append('ajax', '1'); // Menyuntikkan data penanda asinkronus

            const inputField = form.querySelector('.comment-input');
            const postId = form.querySelector('input[name="post_id"]').value;

            try {
                const response = await fetch('komentar_komunitas.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    const commentsArea = document.getElementById(`comments-area-${postId}`);
                    
                    // Menyisipkan markup HTML komentar balasan secara langsung & instan
                    commentsArea.insertAdjacentHTML('beforeend', data.html);
                    commentsArea.style.display = "block"; // Otomatis membuka area jika tertutup
                    
                    inputField.value = ''; // Mengosongkan form text input pasca kirim

                    // Memperbarui counter teks jumlah komentar: "💬 Komentar (X)"
                    const card = form.closest('.feed-card');
                    const toggleBtn = card.querySelector('.btn-toggle-komentar');
                    toggleBtn.textContent = `💬 Komentar (${data.total_comments})`;
                    
                    // Daftarkan ulang event lightbox khusus untuk avatar komentar baru yang barusan muncul
                    attachLightboxToNewImages();
                } else {
                    alert(data.message);
                }
            } catch (err) {
                console.error("Gagal mengirim komentar via AJAX:", err);
            }
        });
    });

    // ==========================================
    // 3. LOGIKA DROPDOWN, TOGGLE AREA, & SCROLL UI
    // ==========================================
    const toggles = document.querySelectorAll('.toggle-like-dropdown');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            if (e.target.classList.contains('link-like')) return; 
            e.stopPropagation();
            const postId = toggle.getAttribute('data-id');
            const targetDropdown = document.getElementById(`dropdown-like-${postId}`);
            document.querySelectorAll('.like-dropdown').forEach(dropdown => {
                if (dropdown !== targetDropdown) dropdown.classList.remove('show');
            });
            if (targetDropdown) targetDropdown.classList.toggle('show');
        });
    });
    
    document.addEventListener('click', () => {
        document.querySelectorAll('.like-dropdown').forEach(dropdown => dropdown.classList.remove('show'));
    });

    const commentToggles = document.querySelectorAll('.btn-toggle-komentar');
    commentToggles.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const postId = btn.getAttribute('data-id');
            const targetCommentsSection = document.getElementById(`comments-area-${postId}`);
            if (targetCommentsSection) {
                targetCommentsSection.style.display = (targetCommentsSection.style.display === "none" || targetCommentsSection.style.display === "") ? "block" : "none";
            }
        });
    });

    // --- SISTEM MODAL LIGHTBOX FOTO ---
    const modal = document.getElementById('lightboxModal');
    const modalImg = document.getElementById('lightboxTargetImage');
    const closeBtn = document.getElementById('lightboxClose');

    function attachLightboxToNewImages() {
        const clickableImages = document.querySelectorAll('.image-clickable');
        clickableImages.forEach(img => {
            img.removeEventListener('click', openLightbox);
            img.addEventListener('click', openLightbox);
        });
    }

    function openLightbox(e) {
        modal.style.display = "flex";
        modalImg.src = e.target.src; 
    }

    attachLightboxToNewImages();

    if (closeBtn && modal) {
        closeBtn.addEventListener('click', () => { modal.style.display = "none"; });
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = "none"; });
    }

    // --- TOMBOL SCROLL TOP ---
    const btnScrollTop = document.getElementById('btnScrollTop');
    if (btnScrollTop) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 450) { btnScrollTop.classList.add('show'); } else { btnScrollTop.classList.remove('show'); }
        });
        btnScrollTop.addEventListener('click', () => { window.scrollTo({ top: 0, behavior: 'smooth' }); });
    }
});
</script>
<?php include 'footer.php'; ?>
</body>
</html>