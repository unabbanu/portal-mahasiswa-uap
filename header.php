<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================================
// PROTEKSI: Update waktu aktivitas terakhir pengguna di database
// =====================================================================
if (isset($_SESSION['user_id'])) {
    include 'koneksi.php'; // Hubungkan ke database
    $current_user_id = $_SESSION['user_id'];
    
    // Perbarui kolom last_activity dengan waktu sekarang (WIB)
    $stmt_activity = $koneksi->prepare("UPDATE user SET last_activity = NOW() WHERE id = ?");
    $stmt_activity->bind_param("i", $current_user_id);
    $stmt_activity->execute();
    $stmt_activity->close();
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$current_page = basename($_SERVER['PHP_SELF']);

$notif_count = 0;
if ($username) {
    // File koneksi sudah di-include di atas jika user_id ada, 
    // namun baris di bawah ini tetap aman agar koneksi selalu siap
    include 'koneksi.php'; 
    
    $stmt_count = $koneksi->prepare("SELECT COUNT(*) AS total FROM notifikasi WHERE user_target = ? AND is_read = 0");
    $stmt_count->bind_param("s", $username);
    $stmt_count->execute();
    $res_count = $stmt_count->get_result()->fetch_assoc();
    if ($res_count) {
        $notif_count = $res_count['total'];
    }
    $stmt_count->close();
}
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    body { background-color: #f8f0f8; color: #333; }

    nav {
        background-color: #4a094a;
        color: white;
        width: 100%;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        min-height: 85px;
        display: flex;
        align-items: center;
    }

    .nav-container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .logo-container {
        display: inline-flex;
        align-items: center;
        gap: 14px; 
        text-decoration: none;
        color: white;
        z-index: 1010;
    }

    .logo-img { height: 55px; width: 55px; object-fit: contain; display: block; }
    .logo-text { font-size: 1.5rem; font-weight: bold; text-transform: uppercase; flex-shrink: 0; letter-spacing: 1px; }
    .nav-menu-wrapper { display: flex; align-items: center; margin-left: auto; }

    .nav-links { list-style: none; display: flex; align-items: center; margin-right: 25px; }
    .nav-links li { margin: 0 12px; }
    .nav-links a { 
        color: rgba(255, 255, 255, 0.75); text-decoration: none; font-size: 1.05rem; font-weight: 600; text-transform: uppercase;
        transition: all 0.3s ease; display: inline-block; padding: 6px 0; position: relative;
    }
    .nav-links a::after { content: ''; position: absolute; width: 0; height: 2px; bottom: 0; left: 0; background-color: white; transition: width 0.3s ease; }
    .nav-links a:hover::after, .nav-links a.active::after { width: 100%; }
    .nav-links a:hover, .nav-links a.active { color: white; }

    .hamburger-toggle { display: none; flex-direction: column; justify-content: space-between; width: 30px; height: 21px; background: transparent; border: none; cursor: pointer; z-index: 1010; margin-left: 20px; }
    .hamburger-toggle span { width: 100%; height: 3px; background-color: white; border-radius: 2px; transition: all 0.3s ease; }
    .hamburger-toggle.open span:nth-child(1) { transform: translateY(9px) rotate(45deg); }
    .hamburger-toggle.open span:nth-child(2) { opacity: 0; }
    .hamburger-toggle.open span:nth-child(3) { transform: translateY(-9px) rotate(-45deg); }

    .notif-dropdown-container {
        position: relative;
        margin-right: 20px;
        display: inline-flex;
        align-items: center;
    }
    .btn-notif-bell {
        text-decoration: none;
        font-size: 1.4rem;
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        display: block;
        line-height: 1;
        transition: transform 0.2s;
    }
    .btn-notif-bell:hover { transform: scale(1.1); }
    .notif-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 0.7rem;
        font-weight: bold;
        z-index: 10;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .notif-dropdown-box {
        position: absolute;
        top: 55px;
        right: -60px;
        width: 320px;
        background: #ffffff;
        border: 1px solid rgba(106, 13, 106, 0.1);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        display: none;
        z-index: 1050;
        overflow: hidden;
    }
    .notif-dropdown-box.show { display: block; }
    .notif-dropdown-box::before { 
        content: ''; position: absolute; top: -8px; right: 67px; width: 16px; height: 16px; 
        background: #6a0d6a; transform: rotate(45deg); z-index: 1;
    }
    .notif-drop-header {
        background: #6a0d6a;
        color: white;
        padding: 12px 15px;
        font-weight: bold;
        font-size: 0.9rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        z-index: 2;
    }
    
    /* MODIFIKASI: Mengubah tinggi maks untuk menampung pas 5 item & mengaktifkan scrolling */
    .notif-drop-list { 
        max-height: 360px; 
        overflow-y: auto;   /* Membuka scroll vertikal jika item > 5 */
        display: block;     /* Memastikan container membungkus list secara block */
        background: white; 
    }
    
    .notif-drop-item {
        display: flex; gap: 12px; padding: 12px 15px; border-bottom: 1px solid #f2e6f2;
        text-decoration: none; color: #333; font-size: 0.85rem; transition: background 0.2s; align-items: flex-start;
    }
    .notif-drop-item:hover { background: #fbf4fb; }
    .notif-drop-item.unread { background: #fff5ff; border-left: 3px solid #6a0d6a; }
    .notif-drop-icon { font-size: 1.2rem; line-height: 1; }
    .notif-drop-text { flex: 1; line-height: 1.4; text-align: left; }
    .notif-drop-time { display: block; font-size: 0.75rem; color: #888; margin-top: 4px; }
    .notif-drop-empty { padding: 25px 15px; text-align: center; color: #777; font-style: italic; font-size: 0.85rem; }

    .profile-menu-container { position: relative; display: inline-block; flex-shrink: 0; z-index: 1010; }
    .profile-trigger { display: flex; align-items: center; background: none; border: none; cursor: pointer; padding: 2px; border-radius: 12px; transition: transform 0.2s ease; }
    .profile-trigger:hover { transform: scale(1.05); }
    .avatar-nav { width: 54px; height: 54px; border-radius: 10px; object-fit: cover; border: 2px solid rgba(255,255,255,0.8); box-shadow: 0 4px 10px rgba(0,0,0,0.2); background-color: #eee; }
    
    .dropdown-menu {
        position: absolute; right: -5px; top: 72px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px); min-width: 280px;
        box-shadow: 0 10px 30px rgba(106, 13, 106, 0.15), 0 1px 8px rgba(0,0,0,0.06); border-radius: 14px; border: 1px solid rgba(106, 13, 106, 0.08);
        opacity: 0; visibility: hidden; transform: translateY(15px); transition: all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55); z-index: 1015;
    }
    .dropdown-menu.show { opacity: 1; visibility: visible; transform: translateY(0); }
    .dropdown-menu::before { content: ''; position: absolute; top: -8px; right: 22px; width: 16px; height: 16px; background: white; transform: rotate(45deg); border-top: 1px solid rgba(106, 13, 106, 0.08); border-left: 1px solid rgba(106, 13, 106, 0.08); }
    .dropdown-header { padding: 18px 20px; border-bottom: 1px solid #f2e6f2; background: linear-gradient(to bottom, #fdf8fd, white); border-radius: 14px 14px 0 0; }
    .dropdown-header-content { display: flex; align-items: center; gap: 14px; }
    .avatar-dropdown-thumb { width: 58px; height: 58px; border-radius: 10px; object-fit: cover; border: 2px solid #6a0d6a; box-shadow: 0 3px 8px rgba(106, 13, 106, 0.15); background-color: #eee; flex-shrink: 0; }
    .user-info-text { display: flex; flex-direction: column; gap: 4px; overflow: hidden; }
    .dropdown-header .user-name { display: block; font-size: 1.05rem; font-weight: 700; color: #4a094a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .dropdown-header .user-role { font-size: 0.75rem; color: #6a0d6a; background-color: #fcecfc; padding: 2px 8px; border-radius: 8px; font-weight: bold; align-self: flex-start; }
    .dropdown-menu a { color: #444; padding: 14px 20px; text-decoration: none; display: flex; align-items: center; gap: 12px; font-size: 0.95rem; font-weight: 600; transition: all 0.2s ease; text-align: left; }
    .dropdown-menu a span.menu-icon { transition: transform 0.2s ease; }
    .dropdown-menu a:hover { background-color: #fbf4fb; color: #6a0d6a; }
    .dropdown-menu a:hover span.menu-icon { transform: translateX(3px); }
    .dropdown-menu a.menu-logout { color: #dc3545; border-top: 1px solid #f2e6f2; border-radius: 0 0 14px 14px; }
    .dropdown-menu a.menu-logout:hover { background-color: #fff5f5; color: #bd2130; }

    .auth-buttons { z-index: 1010; }
    .btn-login-nav { 
        background: white; color: #6a0d6a; padding: 10px 25px; border-radius: 25px; 
        font-weight: bold; font-size: 1rem; text-decoration: none; text-transform: uppercase;
        display: inline-block; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .btn-login-nav:hover { background: #ffccff; color: #4a094a; transform: translateY(-1px); box-shadow: 0 6px 15px rgba(0,0,0,0.15); }

    @media (max-width: 992px) {
        .nav-container { padding: 0 15px; }
        .logo-text { font-size: 1.1rem; }
        .hamburger-toggle { display: flex; }

        .nav-links {
            position: fixed; top: 0; right: -100%; width: 280px; height: 100vh;
            background-color: #3b053b; flex-direction: column; align-items: flex-start;
            padding: 100px 30px 30px 30px; gap: 20px; margin: 0;
            box-shadow: -5px 0 25px rgba(0,0,0,0.3);
            transition: right 0.4s cubic-bezier(0.1, 0.9, 0.2, 1); z-index: 1005;
        }
        .nav-links.active { right: 0; }
        .nav-links li { width: 100%; margin: 0; }
        .nav-links a { font-size: 1.2rem; width: 100%; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .nav-links a::after { display: none; }
    
        .dropdown-menu { right: 0; top: 65px; position: absolute; }
        .dropdown-menu::before { right: 20px; }
        .notif-dropdown-container { margin-right: 15px; }
    
        .notif-dropdown-box { 
            position: fixed;      
            top: 75px;            
            right: 15px;          
            left: 15px;           
            width: auto;          
            max-width: 330px;     
            margin-left: auto;    
        }
    
        .notif-dropdown-box::before { 
            right: 48px; 
            top: -8px;
        }
    }
</style>

<nav>
    <div class="nav-container">
        <a href="index.php" class="logo-container">
            <img src="logo1.png" alt="Logo FTI UAP" class="logo-img" onerror="this.src='https://placehold.co'">
            <div class="logo-text">FTI HUB UAP</div>
        </a>
        
        <div class="nav-menu-wrapper">
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
                <li><a href="artikel.php" class="<?= ($current_page == 'artikel.php') ? 'active' : ''; ?>">Artikel</a></li>
                <li><a href="komunitas.php" class="<?= ($current_page == 'komunitas.php') ? 'active' : ''; ?>">Sosial</a></li>
                <li><a href="game_browser.php" class="<?= ($current_page == 'game_browser.php') ? 'active' : ''; ?>">Games</a></li>
                <li><a href="aplikasi_mobile.php" class="<?= ($current_page == 'aplikasi_mobile.php') ? 'active' : ''; ?>">Aplikasi Mobile</a></li>
                <li><a href="tentang.php" class="<?= ($current_page == 'tentang.php') ? 'active' : ''; ?>">Tentang</a></li>
                <?php if(isset($username) && $username == "admin"): ?>
                    <li><a href="user.php" class="<?= ($current_page == 'user.php') ? 'active' : ''; ?>">User Management</a></li>
                <?php endif; ?>
            </ul>

            <?php if (isset($_SESSION['username'])): ?>
                
                <div class="notif-dropdown-container">
                    <button class="btn-notif-bell" id="btnNotifLonceng" aria-label="Notifikasi">
                        🔔 
                        <?php if ($notif_count > 0): ?>
                            <span class="notif-badge" id="badgeNotifCount"><?= $notif_count; ?></span>
                        <?php endif; ?>
                    </button>

                    <div class="notif-dropdown-box" id="boxNotifDropdown">
                        <div class="notif-drop-header">
                            <span>Pemberitahuan</span>
                            <span style="font-size: 0.75rem; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 10px;" id="headerNotifCount"><?= $notif_count; ?> Baru</span>
                        </div>
            
                        <div class="notif-drop-list" id="listNotifDropdown">
                            <div class="notif-drop-empty">Memuat pemberitahuan...</div>
                        </div>
                    </div>
                </div>

                <div class="profile-menu-container">
                    <button class="profile-trigger" id="profileTrigger" aria-label="Menu Profil" aria-haspopup="true" aria-expanded="false">
                        <img src="<?= (isset($_SESSION['avatar']) && !empty($_SESSION['avatar']) && file_exists($_SESSION['avatar'])) ? htmlspecialchars($_SESSION['avatar']) : 'uploads/avatar_default.png'; ?>" alt="Foto Profil" class="avatar-nav">
                    </button>
                    
                    <div class="dropdown-menu" id="dropdownMenu">
                        <div class="dropdown-header">
                            <div class="dropdown-header-content">
                                <img src="<?= (isset($_SESSION['avatar']) && !empty($_SESSION['avatar']) && file_exists($_SESSION['avatar'])) ? htmlspecialchars($_SESSION['avatar']) : 'uploads/avatar_default.png'; ?>" alt="Mini Thumbnail" class="avatar-dropdown-thumb">
                                <div class="user-info-text">
                                    <span class="user-name"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $_SESSION['username']))); ?></span>
                                    <span class="user-role">Sivitas FTI UAP</span>
                                </div>
                            </div>
                        </div>
                        <a href="pengaturan_profil.php"><span class="menu-icon">⚙️</span> Ganti Foto Profil</a>
                        <a href="pengaturan_password.php"><span class="menu-icon">🔑</span> Ganti Kata Sandi</a>
                        <a href="logout.php" class="menu-logout"><span class="menu-icon">🚪</span> Logout</a>
                    </div>
                </div>

            <?php else: ?>
                <div class="auth-buttons">
                    <a href="login.php" class="btn-login-nav">Login</a>
                </div>
            <?php endif; ?>

            <button class="hamburger-toggle" id="hamburgerToggle" aria-label="Buka Menu Navigasi">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('hamburgerToggle');
    const navLinks = document.getElementById('navLinks');
    const profileTrigger = document.getElementById('profileTrigger');
    const dropdownMenu = document.getElementById('dropdownMenu');
    
    const btnLonceng = document.getElementById('btnNotifLonceng');
    const boxDropdown = document.getElementById('boxNotifDropdown');
    const listDropdown = document.getElementById('listNotifDropdown');
    const badgeCount = document.getElementById('badgeNotifCount');
    const headerCount = document.getElementById('headerNotifCount');

    if (hamburger && navLinks) {
        hamburger.addEventListener('click', (e) => {
            e.stopPropagation();
            hamburger.classList.toggle('open');
            navLinks.classList.toggle('active');
            if(dropdownMenu) dropdownMenu.classList.remove('show');
            if(boxDropdown) boxDropdown.classList.remove('show');
        });
    }

    if (btnLonceng && boxDropdown) {
        btnLonceng.addEventListener('click', (e) => {
            e.stopPropagation();
            if(dropdownMenu) dropdownMenu.classList.remove('show');
            boxDropdown.classList.toggle('show');

            if (boxDropdown.classList.contains('show')) {
                listDropdown.innerHTML = '<div class="notif-drop-empty">Memuat pemberitahuan...</div>';
                
                fetch('ambil_notif_ajax.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP status ' + response.status);
                        }
                        return response.text(); 
                    })
                    .then(text => {
                        try {
                            const data = JSON.parse(text); 
                            listDropdown.innerHTML = '';
                            
                            if (data.length > 0) {
                                data.forEach(item => {
                                    const unreadClass = item.is_read == 0 ? 'unread' : '';
                                    listDropdown.innerHTML += `
                                        <a href="${item.link}" class="notif-drop-item ${unreadClass}">
                                            <div class="notif-drop-icon">${item.icon}</div>
                                            <div class="notif-drop-text">
                                                <strong>${item.user_pemicu}</strong> ${item.pesan}
                                                <span class="notif-drop-time">${item.waktu}</span>
                                            </div>
                                        </a>
                                    `;
                                });
                            } else {
                                listDropdown.innerHTML = '<div class="notif-drop-empty">Tidak ada pemberitahuan baru.</div>';
                            }

                            if (badgeCount) badgeCount.style.display = 'none';
                            if (headerCount) headerCount.innerText = '0 Baru';
                        } catch (err) {
                            console.error("JSON Parsing Error:", text);
                            listDropdown.innerHTML = `<div class="notif-drop-empty" style="color:#dc3545; font-style:normal;"><strong>Penyebab Rusak:</strong><br><code style="font-size:11px; display:block; margin-top:5px; background:#fff0f0; padding:5px; border:1px solid #ffcccc; text-align:left; max-height:100px; overflow-y:auto;">${text.replace(/</g, '&lt;')}</code></div>`;
                        }
                    })
                    .catch(error => {
                        listDropdown.innerHTML = '<div class="notif-drop-empty">Gagal memuat data (Koneksi Terputus).</div>';
                    });
            }
        });
    }

    if (profileTrigger && dropdownMenu) {
        profileTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            if(boxDropdown) boxDropdown.classList.remove('show');
            
            const isOpen = dropdownMenu.classList.contains('show');
            dropdownMenu.classList.toggle('show');
            profileTrigger.setAttribute('aria-expanded', !isOpen);
            if(hamburger) {
                hamburger.classList.remove('open');
                navLinks.classList.remove('active');
            }
        });
    }

    document.addEventListener('click', (e) => {
        if (boxDropdown && !boxDropdown.contains(e.target) && e.target !== btnLonceng) {
            boxDropdown.classList.remove('show');
        }
        if (dropdownMenu && profileTrigger && !profileTrigger.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('show');
        }
        if (navLinks && hamburger && !hamburger.contains(e.target) && !navLinks.contains(e.target)) {
            hamburger.classList.remove('open');
            navLinks.classList.remove('active');
        }
    });
});
</script>