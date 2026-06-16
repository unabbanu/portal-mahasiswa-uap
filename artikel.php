<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);

// --- TAMBAHAN KEAMANAN: GENERATE CSRF TOKEN JIKA BELUM ADA ---
if (isset($_SESSION['username']) && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">
<style>
    /* --- RESET & DASAR ANIMASI --- */
    * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    }
    
    @keyframes fadeInUp { 
        from { opacity: 0; transform: translateY(25px); } 
        to { opacity: 1; transform: translateY(0); } 
    }

    body { 
        background: linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), url('logo.png');
        background-size: 100px;
        background-repeat: repeat;
        min-height: 100vh;
    }

    .main-content {
        min-height: calc(100vh - 85px);
        padding: 40px 20px;
        width: 100%;
        animation: fadeInUp 0.7s ease-out;
    }

    .admin-container {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
        display: flex;
        flex-direction: column;
    }

    .admin-hero {
        text-align: center;
        color: white;
        margin-bottom: 40px;
    }
    .admin-hero h1 {
        font-size: 2.3rem;
        margin-bottom: 10px;
        font-weight: 700;
        animation: fadeInUp 0.6s ease-out;
    }
    .admin-hero p {
        font-size: 1.1rem;
        opacity: 0.9;
        max-width: 600px;
        margin: 0 auto;
        animation: fadeInUp 0.8s ease-out;
    }

    .header-actions {
        margin-top: 25px;
        text-align: center;
    }

    .btn-add {
        background-color: #ffffff;
        color: #6a0d6a;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s;
        display: inline-block;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        letter-spacing: 0.5px;
        font-size: 0.95rem;
    }
    .btn-add:hover { background-color: #fcecfc; transform: translateY(-2px); }

    .admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        width: 100%;
    }

    .admin-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        width: 100%;
    }
    .admin-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.35);
    }

    .card-number {
        position: absolute;
        top: 12px; left: 12px;
        background: rgba(0,0,0,0.6);
        color: white;
        width: 28px; height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: bold;
        z-index: 5;
    }

    .card-status-badge {
        position: absolute;
        top: 12px; right: 12px;
        padding: 5px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: bold;
        z-index: 5;
    }
    .badge-publish { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .badge-draft   { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }

    .card-thumbnail-wrapper {
        width: 100%; height: 180px;
        overflow: hidden;
        background-color: #eee;
        border-bottom: 1px solid rgba(106,13,106,0.1);
    }
    .img-custom { width: 100%; height: 100%; object-fit: cover; }
    .no-img-box {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem; color: #777; font-style: italic; background: #e9ecef;
    }

    .card-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .card-tag {
        font-size: 0.75rem; font-weight: bold;
        text-transform: uppercase; color: #6a0d6a;
        margin-bottom: 8px; letter-spacing: 0.5px;
    }
    .card-title {
        font-size: 1.2rem; color: #4a094a;
        font-weight: bold; margin-bottom: 12px; line-height: 1.4;
    }
    .card-meta {
        display: flex; justify-content: space-between;
        font-size: 0.8rem; color: #666;
        margin-bottom: 18px;
        padding-top: 10px;
        border-top: 1px dashed rgba(106,13,106,0.15);
        margin-top: auto;
    }
    .author-text { font-weight: 700; color: #4a094a; }

    .card-actions {
        display: flex; gap: 6px;
        width: 100%; flex-wrap: wrap;
        align-items: center;
    }
    .btn-action {
        flex: 1; min-width: 65px;
        padding: 10px 5px; border-radius: 6px;
        text-decoration: none; font-weight: bold;
        font-size: 0.85rem; text-align: center; transition: 0.2s;
    }
    .btn-view   { background-color: #6a0d6a; color: white; }
    .btn-view:hover { background-color: #4a094a; }
    .btn-edit   { background: #fff; color: #007bff; border: 1px solid #007bff; display: inline-block; }
    .btn-edit:hover { background: #007bff; color: white; }
    
    /* Sinkronisasi style button form hapus agar seragam dengan tombol lainnya */
    .form-delete-inline { display: flex; flex: 1; min-width: 65px; }
    .btn-delete { 
        background: #fff; color: #dc3545; border: 1px solid #dc3545; 
        width: 100%; cursor: pointer; font-family: inherit;
    }
    .btn-delete:hover { background: #dc3545; color: white; }
    
    .btn-pdf    { background-color: #28a745; color: white; }
    .btn-pdf:hover { background-color: #218838; }

    /* ===== MOBILE ===== */
    @media (max-width: 768px) {
        .main-content { padding: 30px 15px; }
        .admin-hero h1 { font-size: 1.8rem; }
        .admin-hero p  { font-size: 0.95rem; }
        .btn-add { width: 100%; text-align: center; }
        .admin-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        .admin-card {
            width: 100%;
            max-width: 100%;
        }
    }

    @media (max-width: 360px) {
        .admin-hero h1 { font-size: 1.4rem; }
        .btn-action { font-size: 0.8rem; padding: 9px 4px; }
    }
</style>
</head>
<body>
    
<?php include 'header.php'; ?>

<div class="main-content">
    <div class="admin-container">
        
        <div class="admin-hero">
            <h1>Pusat Pengelola Artikel FTI</h1>
            <p>Sistem manajemen data artikel, publikasi, dan draf informasi internal FTI HUB UAP.</p>
            <div class="header-actions">
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="tambah_artikel.php" class="btn-add">+ TULIS ARTIKEL BARU</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-grid">
            <?php                
            include 'koneksi.php';

            // --- OPTIMASI KEAMANAN: Menggunakan Prepared Statement untuk menarik data ---
            $query = "SELECT id, judul, kategori, gambar, konten, status, pembuat, tanggal, file_pdf FROM artikel ORDER BY id DESC";
            $stmt  = $koneksi->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $bulan_indo = [
                'Jan'=>'Jan','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Apr','May'=>'Mei','Jun'=>'Jun',
                'Jul'=>'Jul','Aug'=>'Agu','Sep'=>'Sep','Oct'=>'Okt','Nov'=>'Nov','Dec'=>'Des'
            ];

            if ($result && $result->num_rows > 0) {
                while ($data = $result->fetch_assoc()) {
                    $status_class = ($data['status'] == 'Publish') ? 'badge-publish' : 'badge-draft';
                    $status_text  = ($data['status'] == 'Publish') ? 'Published' : 'Draft';
                    $tanggal_indo = strtr(date('d M Y', strtotime($data['tanggal'])), $bulan_indo);
                    $id_aman      = intval($data['id']);
                    $path_gambar  = trim($data['gambar']);
                    $path_pdf     = isset($data['file_pdf']) ? trim($data['file_pdf']) : '';
                    $pembuat_username    = (!empty($data['pembuat'])) ? trim($data['pembuat']) : 'admin';
                    $nama_tampilan_pembuat = ucwords(str_replace('_', ' ', $pembuat_username));
                    
                    $user_login = isset($_SESSION['username']) ? strtolower(trim($_SESSION['username'])) : '';
                    $is_owner = ($user_login === strtolower($pembuat_username)) || ($user_login === 'admin');
                    
                    // Ambil nilai CSRF Token dari session untuk disisipkan ke form data
                    $token_param = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
                    ?>
                    <div class="admin-card">
                        <span class="card-status-badge <?= $status_class; ?>"><?= htmlspecialchars($status_text); ?></span>
                        
                        <div class="card-thumbnail-wrapper">
                            <?php if (!empty($path_gambar) && file_exists($path_gambar)): ?>
                                <img src="<?= htmlspecialchars($path_gambar); ?>" class="img-custom" alt="Sampul">
                            <?php elseif (!empty($path_gambar) && file_exists('uploads/' . $path_gambar)): ?>
                                <img src="uploads/<?= htmlspecialchars($path_gambar); ?>" class="img-custom" alt="Sampul">
                            <?php else: ?>
                                <div class="no-img-box">Tidak ada gambar</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body">
                            <span class="card-tag"><?= htmlspecialchars($data['kategori']); ?></span>
                            <h2 class="card-title"><?= htmlspecialchars($data['judul']); ?></h2>
                            <div class="card-meta">
                                <span class="author-text">👤 <?= htmlspecialchars($nama_tampilan_pembuat); ?></span>
                                <span>📅 <?= $tanggal_indo; ?></span>
                            </div>
                            <div class="card-actions">
                                <a href="baca_artikel.php?id=<?= $id_aman; ?>" class="btn-action btn-view">Lihat</a>
                                <?php if (!empty($path_pdf) && file_exists($path_pdf)): ?>
                                    <a href="<?= htmlspecialchars($path_pdf); ?>" target="_blank" class="btn-action btn-pdf">PDF</a>
                                <?php endif; ?>
                                <?php if ($is_owner): ?>
                                    <a href="edit_artikel.php?id=<?= $id_aman; ?>" class="btn-action btn-edit">Edit</a>
                                    
                                    <form action="hapus_artikel.php" method="POST" class="form-delete-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token_param); ?>">
                                        <input type="hidden" name="id" value="<?= $id_aman; ?>">
                                        <button type="submit" class="btn-action btn-delete">Hapus</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<div style='grid-column:1/-1;text-align:center;color:#fff;font-weight:600;padding:30px;background:rgba(0,0,0,0.2);border-radius:10px;'>Belum ada data artikel.</div>";
            }
            $stmt->close();
            ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>