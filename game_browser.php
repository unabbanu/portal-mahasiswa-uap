<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = 'game_browser.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Browser</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">
</head>
<body>

<?php include 'header.php'; ?>

<style>
    /* --- RESET DASAR --- */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    body { color: #333; overflow-x: hidden; }
    
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    body { 
        background: linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), url('logo.png');
        background-size: 100px;
        background-repeat: repeat;
        min-height: 100vh;
    }

    .main-content {
        min-height: calc(100vh - 85px);
        padding: 40px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .game-container {
        width: 100%;
        max-width: 1200px;
        animation: fadeIn 0.8s ease-out;
    }

    .game-header {
        margin-bottom: 30px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        padding-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    .game-header-text { text-align: left; flex: 1; min-width: 280px; }
    .game-header h2 { color: #ffffff; font-size: 2rem; text-shadow: 0 2px 5px rgba(0,0,0,0.3); font-weight: 700; }
    .game-header p { color: rgba(255, 255, 255, 0.8); font-size: 1rem; margin-top: 5px; }

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
        font-size: 0.95rem;
    }
    .btn-add:hover { background-color: #fcecfc; transform: translateY(-2px); }

    .game-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        width: 100%;
    }

    .game-card {
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
    .game-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.35);
    }

    .game-banner {
        width: 100%;
        height: 180px;
        object-fit: cover;
        background-color: #eee;
        border-bottom: 1px solid rgba(106,13,106,0.1);
    }

    .game-details {
        padding: 20px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .game-title {
        font-size: 1.2rem; color: #4a094a;
        font-weight: bold; margin-bottom: 12px; line-height: 1.4;
    }

    .game-genre {
        font-size: 0.75rem; font-weight: bold;
        text-transform: uppercase; color: #6a0d6a;
        margin-bottom: 8px; letter-spacing: 0.5px;
    }

    .game-description {
        color: #555;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 18px;
    }

    .game-meta {
        display: flex; justify-content: space-between;
        font-size: 0.8rem; color: #666;
        margin-bottom: 18px;
        padding-top: 10px;
        border-top: 1px dashed rgba(106,13,106,0.15);
        margin-top: auto;
    }
    .author-text { font-weight: 700; color: #4a094a; }

    .card-actions { display: flex; gap: 6px; width: 100%; flex-wrap: wrap; }
    
    .btn-play-game {
        flex: 1; min-width: 65px;
        padding: 10px 5px; border-radius: 6px;
        text-decoration: none; font-weight: bold;
        font-size: 0.85rem; text-align: center; transition: 0.2s;
        background-color: #6a0d6a; color: white;
    }
    .btn-play-game:hover { background-color: #4a094a; }
    
    .btn-delete {
        flex: 1; min-width: 65px;
        padding: 10px 5px; border-radius: 6px;
        text-decoration: none; font-weight: bold;
        font-size: 0.85rem; text-align: center; transition: 0.2s;
        background: #fff; color: #dc3545; border: 1px solid #dc3545;
    }
    .btn-delete:hover { background: #dc3545; color: white; }

    .empty-state {
        grid-column: 1 / -1; text-align: center; padding: 60px 20px;
        background: rgba(255, 255, 255, 0.1); border-radius: 15px;
        border: 2px dashed rgba(255, 255, 255, 0.3); color: white;
    }

    @media (max-width: 768px) {
        .main-content { padding: 30px 15px; }
        .game-header h2 { font-size: 1.8rem; text-align: center; }
        .game-header p  { font-size: 0.95rem; text-align: center; }
        .game-header { flex-direction: column; text-align: center; }
        .btn-add { width: 100%; text-align: center; }
        .game-grid { grid-template-columns: 1fr; gap: 16px; }
    }
</style>

<div class="main-content">
    <div class="game-container">
        
        <div class="game-header">
            <div class="game-header-text">
                <h2>Pusat Kreator Game FTI</h2>
                <p>Eksplorasi, mainkan, dan unggah karya game HTML5 kreasi mahasiswa Fakultas Teknologi Informasi.</p>
            </div>
            <div>
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="tambah_game.php" class="btn-add">+ UNGGAH GAME BARU</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="game-grid">
            
            <?php  
            include 'koneksi.php';
            $query = "SELECT id, judul, genre, deskripsi, banner, folder_game, pembuat, tanggal FROM games ORDER BY id DESC";
            $sql   = mysqli_query($koneksi, $query);

            if ($sql && mysqli_num_rows($sql) > 0) {
                while ($data = mysqli_fetch_array($sql)) {
                    $id_game       = intval($data['id']);
                    $pembuat_raw   = trim($data['pembuat']);
                    $nama_tampilan = ucwords(str_replace('_', ' ', $pembuat_raw));
                    
                    $user_login = isset($_SESSION['username']) ? strtolower(trim($_SESSION['username'])) : '';
                    $is_owner   = ($user_login === strtolower($pembuat_raw)) || ($user_login === 'admin');
                    ?>
                    <div class="game-card">
                        <img src="uploads/games/<?= htmlspecialchars($data['banner']); ?>" class="game-banner" alt="Banner Game">
                        <div class="game-details">
                            <span class="game-genre"><?= htmlspecialchars($data['genre']); ?></span>
                            <h2 class="game-title"><?= htmlspecialchars($data['judul']); ?></h2>
                            <p class="game-description"><?= htmlspecialchars($data['deskripsi']); ?></p>
                            <div class="game-meta">
                                <span class="author-text">👤 <?= htmlspecialchars($nama_tampilan); ?></span>
                                <span>📅 <?= date('d M Y', strtotime($data['tanggal'])); ?></span>
                            </div>
                            <div class="card-actions">
                                <a href="play_game.php?game=<?= htmlspecialchars($data['folder_game']); ?>" class="btn-play-game">🎮 Mainkan</a>
    
                                <?php if ($is_owner): ?>
                                    <a href="edit_game.php?id=<?= $id_game; ?>" class="btn-edit-game" style="flex: 1; min-width: 65px; padding: 10px 5px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.85rem; text-align: center; transition: 0.2s; background-color: #2575fc; color: white;">Edit</a>
        
                                    <a href="hapus_game.php?id=<?= $id_game; ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus game ini dari platform?')">Hapus</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                // Tampilan jika data di database masih kosong melompong
                echo '<div class="empty-state">
                        <h3>🎮 Belum Ada Game yang Tersedia</h3>
                        <p style="margin-top: 8px; color: rgba(255,255,255,0.7);">Silakan login dan jadilah orang pertama yang mengunggah game HTML5 di sini!</p>
                      </div>';
            }
            ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>