<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'koneksi.php';

// Ambil data aplikasi mobile dari database
$query = "SELECT * FROM aplikasi ORDER BY id DESC";
$tampil_aplikasi = mysqli_query($koneksi, $query);
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Mobile</title>
    <link rel="icon" type="image/x-icon" href="logo1.ico?v=1">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }

        @keyframes fadeInUp { 
            from { opacity: 0; transform: translateY(20px); } 
            to { opacity: 1; transform: translateY(0); } 
        }

        body { 
            background: linear-gradient(rgba(106, 13, 106, 0.92), rgba(106, 13, 106, 0.92)), url('logo.png'); 
            background-size: 100px; 
            background-repeat: repeat;
            min-height: 100vh; 
            color: #333;
            display: flex;
            flex-direction: column;
        }

        .main-container {
            flex: 1;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            animation: fadeInUp 0.6s ease-out;
        }

        /* Bagian Atas/Header Halaman */
        .page-header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .page-header p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.85);
            max-width: 700px;
            margin: 0 auto 25px auto;
            line-height: 1.5;
        }

        .btn-add-app {
            background: #ffffff;
            color: #6a0d6a;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .btn-add-app:hover {
            background: #f4e6f4;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        /* STRUKTUR GRID RESPONSIF - Mencegah Ruang Kosong Masif */
        .apps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            width: 100%;
        }

        /* KARTU APLIKASI MODERN */
        .app-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .app-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
        }

        /* Atas Kartu: Identitas & Gambar */
        .app-top-info {
            display: flex;
            gap: 15px;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .app-icon {
            width: 70px;
            height: 70px;
            border-radius: 14px;
            object-fit: cover;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 3px 8px rgba(0,0,0,0.05);
            flex-shrink: 0;
        }

        .app-meta {
            flex: 1;
            min-width: 0; /* Mengamankan pembatasan teks elipsis */
        }

        .badge-category {
            display: inline-block;
            background: rgba(106, 13, 106, 0.1);
            color: #6a0d6a;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .app-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #222;
            line-height: 1.3;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .app-developer {
            font-size: 0.85rem;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Tengah Kartu: Deskripsi Terbaca Jelas */
        .app-body {
            margin-bottom: 20px;
            flex: 1;
        }

        .app-description {
            font-size: 0.9rem;
            color: #444;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 4; /* Membatasi teks maksimal 4 baris agar proporsional */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: justify;
        }

        /* Detail Pengunggah */
        .app-footer-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 12px;
            margin-bottom: 15px;
        }

        .uploader-info {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 60%;
        }

        /* Bawah Kartu: Grup Tombol Aksi */
        .app-actions {
            display: flex;
            gap: 8px;
            width: 100%;
        }

        .btn-action {
            flex: 1;
            text-align: center;
            padding: 9px 5px;
            font-size: 0.85rem;
            font-weight: bold;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.1s ease;
            cursor: pointer;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border: none;
        }

        .btn-download {
            background: #00875a;
            color: white;
        }

        .btn-download:hover {
            background: #006644;
        }

        .btn-edit {
            background: #0052cc;
            color: white;
        }

        .btn-edit:hover {
            background: #003d99;
        }

        .btn-delete {
            background: #de350b;
            color: white;
        }

        .btn-delete:hover {
            background: #b32400;
        }

        /* Pesan Kosong */
        .empty-state {
            grid-column: 1 / -1;
            background: rgba(255, 255, 255, 0.9);
            text-align: center;
            padding: 40px;
            border-radius: 12px;
            color: #666;
            font-size: 1.1rem;
        }

        /* RESPONSIVE BREAKPOINTS */
        @media (max-width: 768px) {
            .page-header h1 { font-size: 1.8rem; }
            .page-header p { font-size: 0.95rem; }
            .apps-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<main class="main-container">
    <div class="page-header">
        <h1>Etalase Aplikasi Mobile Mahasiswa</h1>
        <p>Inovasi aplikasi Android kreasi dan portofolio digital civitas akademika FTI Universitas Aisyah Pringsewu.</p>
        <a href="tambah_aplikasi.php" class="btn-add-app">➕ DAFTARKAN APLIKASI BARU</a>
    </div>

    <div class="apps-grid">
        <?php if (mysqli_num_rows($tampil_aplikasi) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($tampil_aplikasi)): ?>
                <div class="app-card">
                    <div>
                        <div class="app-top-info">
                            <img src="uploads/apps/<?= htmlspecialchars($row['ikon']); ?>" alt="Ikon <?= htmlspecialchars($row['nama_aplikasi']); ?>" class="app-icon" onerror="this.src='logo.png';">
                            <div class="app-meta">
                                <span class="badge-category"><?= htmlspecialchars($row['kategori']); ?></span>
                                <h2 class="app-title" title="<?= htmlspecialchars($row['nama_aplikasi']); ?>"><?= htmlspecialchars($row['nama_aplikasi']); ?></h2>
                                <div class="app-developer" title="Oleh: <?= htmlspecialchars($row['developer']); ?>">Oleh: <?= htmlspecialchars($row['developer']); ?></div>
                            </div>
                        </div>

                        <div class="app-body">
                            <p class="app-description">
                                <?= nl2br(htmlspecialchars($row['deskripsi'])); ?>
                            </p>
                        </div>
                    </div>

                    <div>
                        <div class="app-footer-meta">
                            <span class="uploader-info" title="User: <?= htmlspecialchars($row['pengunggah']); ?>">👤 <?= htmlspecialchars($row['pengunggah']); ?></span>
                            <span>📅 <?= date('d M Y', strtotime($row['tanggal_upload'])); ?></span>
                        </div>

                        <div class="app-actions">
                            <a href="<?= htmlspecialchars($row['link_playstore']); ?>" target="_blank" class="btn-action btn-download">📲 Unduh</a>
                            
                            <?php if (isset($_SESSION['username']) && ($_SESSION['username'] === $row['pengunggah'] || $_SESSION['username'] === 'admin')): ?>
                                <a href="edit_aplikasi.php?id=<?= $row['id']; ?>" class="btn-action btn-edit">Edit</a>
                                <a href="hapus_aplikasi.php?id=<?= $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus aplikasi <?= htmlspecialchars($row['nama_aplikasi']); ?>?')">Hapus</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                📱 Belum ada portofolio aplikasi mobile yang terdaftar.
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>