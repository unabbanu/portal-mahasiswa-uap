<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// 1. Ambil nama unik folder game dari URL parameter
$game_folder = isset($_GET['game']) ? trim($_GET['game']) : '';

$game_title = "";
$iframe_src = "";

// 2. Gagalkan proses jika parameter game di URL kosong
if (empty($game_folder)) {
    $game_title = "Game Tidak Ditentukan";
} else {
    // 3. JALUR DINAMIS UTAMA: Cari data game langsung ke database berdasarkan folder_game
    try {
        $query = "SELECT judul, folder_game FROM games WHERE folder_game = ? LIMIT 1";
        $stmt  = $koneksi->prepare($query);
        $stmt->bind_param("s", $game_folder);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $game_title = $data['judul'];
            
            // Cek apakah data ini mengarah ke link eksternal (seperti gamezop) atau folder lokal
            if (filter_var($data['folder_game'], FILTER_VALIDATE_URL)) {
                $iframe_src = $data['folder_game'];
            } else {
                $iframe_src = "game/" . $data['folder_game'] . "/index.html";
            }
        } else {
            // Fallback jika folder game tidak ditemukan di database
            $game_title = "Game Tidak Ditemukan";
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        $game_title = "Kesalahan Sistem";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playing <?php echo htmlspecialchars($game_title); ?></title>
    <link rel="icon" type="image/x-icon" href="/uap/logo1.ico?v=1">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body {
            background: linear-gradient(rgba(106, 13, 106, 0.9), rgba(106, 13, 106, 0.9)), url('logo.png');
            background-size: 100px; background-repeat: repeat; min-height: 100vh; color: white;
        }
        .container { max-width: 1100px; margin: 0 auto; padding: 30px 20px; text-align: center; }
        .btn-back {
            display: inline-flex; align-items: center; background-color: rgba(255, 255, 255, 0.2);
            color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px;
            font-weight: bold; margin-bottom: 20px; transition: 0.3s; float: left;
        }
        .btn-back:hover { background-color: rgba(255, 255, 255, 0.4); }
        
        /* Jaminan judul berwarna putih bersih dan terbaca jelas */
        .game-title { 
            clear: both; 
            font-size: 2rem; 
            margin-bottom: 20px; 
            text-shadow: 0 2px 5px rgba(0,0,0,0.5); 
            font-weight: 700; 
            color: #ffffff !important; 
        }
        
        .iframe-container {
            position: relative; width: 100%; padding-bottom: 56.25%; height: 0;
            overflow: hidden; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            border: 4px solid #6a0d6a; background-color: #000;
        }
        .iframe-container iframe {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;
        }

        .error-box {
            padding: 50px 20px; background: rgba(255, 255, 255, 0.1); border-radius: 16px;
            border: 2px dashed rgba(255, 255, 255, 0.3); margin-bottom: 20px;
        }

        .game-controls { margin-top: 15px; display: flex; justify-content: flex-end; }
        .btn-fullscreen {
            display: inline-flex; align-items: center; gap: 8px; background-color: #6a0d6a; color: white;
            border: 2px solid rgba(255, 255, 255, 0.2); padding: 10px 20px; font-size: 0.95rem;
            font-weight: bold; border-radius: 8px; cursor: pointer; transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .btn-fullscreen:hover { background-color: #4a094a; transform: translateY(-2px); }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <a href="game_browser.php" class="btn-back">⬅ Kembali ke Portal</a>
    
    <h2 class="game-title"><?php echo htmlspecialchars($game_title); ?></h2>

    <?php if (!empty($iframe_src)): ?>
        <div class="iframe-container" id="gameArea">
            <iframe src="<?php echo htmlspecialchars($iframe_src); ?>" allowfullscreen="true" frameborder="0"></iframe>
        </div>

        <div class="game-controls">
            <button class="btn-fullscreen" onclick="openFullscreen();">📺 Main Layar Penuh</button>
        </div>
    <?php else: ?>
        <div class="error-box">
            <h3>🎮 Konten Game Tidak Ditemukan</h3>
            <p style="margin-top: 10px; color: #ddd;">Maaf, game yang Anda pilih tidak tersedia atau telah dihapus dari sistem FTI HUB.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

<script>
function openFullscreen() {
    var elem = document.getElementById("gameArea");
    if (!elem) return;
    
    if (elem.requestFullscreen) { elem.requestFullscreen(); } 
    else if (elem.webkitRequestFullscreen) { elem.webkitRequestFullscreen(); } 
    else if (elem.msRequestFullscreen) { elem.msRequestFullscreen(); }
}
</script>

</body>
</html>