-- phpMyAdmin SQL Dump
-- version 5.2.3deb1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 16 Jun 2026 pada 04.54
-- Versi server: 8.4.9-0ubuntu0.26.04.1
-- Versi PHP: 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `uap`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `aplikasi`
--

CREATE TABLE `aplikasi` (
  `id` int NOT NULL,
  `nama_aplikasi` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `developer` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci NOT NULL,
  `ikon` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `link_playstore` text COLLATE utf8mb4_general_ci NOT NULL,
  `pengunggah` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_upload` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `aplikasi`
--

INSERT INTO `aplikasi` (`id`, `nama_aplikasi`, `developer`, `kategori`, `deskripsi`, `ikon`, `link_playstore`, `pengunggah`, `tanggal_upload`) VALUES
(4, '8 BALL BILLIARDS CLASSIC', 'Banu Sihwanto', 'Entertainment', 'pengetesan', 'app_1780047366_438.jpg', 'https://www.facebook.com/share/v/1ECRS3wa1V/', 'Banu Sihwanto', '2026-05-29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `artikel`
--

CREATE TABLE `artikel` (
  `id` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `kategori` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `konten` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Publish','Draft') COLLATE utf8mb4_general_ci DEFAULT 'Publish',
  `pembuat` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` date NOT NULL,
  `file_pdf` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `artikel`
--

INSERT INTO `artikel` (`id`, `judul`, `kategori`, `gambar`, `konten`, `status`, `pembuat`, `tanggal`, `file_pdf`, `created_at`) VALUES
(24, 'Mengenal Apa itu Database', 'Edukasi', 'uploads/artikel/art_1780050332_962.jpg', 'Apa itu Database ?\r\nDatabase adalah basis data atau sekumpulan data yang dikelola sedemikian rupa sesuai ketentuan tertentu dan saling berhubungan supaya mudah dikelola. Pengelolaan database memudahkan setiap orang mencari, menyimpan, dan menghapus informasi.\r\nDatabase juga bisa diartikan sebagai sebuah sistem yang berfungsi mengumpulkan data, arsip, atau tabel yang disimpan dan terhubung ke media elektronik, seperti aplikasi atau situs web. Database membuat penyimpanan dan pengelolaan data lebih efisien.\r\n\r\nPengertian lain tentang database menurut Oracle adalah kumpulan terorganisir dari informasi terstruktur atau data yang disimpan secara elektronik ke dalam sistem komputer. Database biasanya dikendalikan oleh DBMS (sistem manajemen database). Data dan DBMS bersama aplikasi yang terkait biasa disebut sebagai sistem database.\r\n\r\nData dalam database umumnya dimodelkan dalam baris dan kolom dalam serangkaian tabel. Hal ini bertujuan membuat pemrosesan dan kueri data jadi lebih efisien. Data lalu bisa diakses, dikelola, dimodifikasi, diperbarui, dikendalikan, dan diatur. Sebagian besar database menggunakan bahasa kueri terstruktur (SQL) untuk menulis dan meminta data.\r\n\r\nApa itu Database Management System (DBMS) ?\r\nDatabase membutuhkan aplikasi atau perangkat lunak yang dinamai sistem manajemen database (DBMS). DBMS ini berfungsi sebagai antarmuka antara database dan pengguna atau program. Ini memungkinkan Anda dapat mengambil, memperbarui, dan mengelola bagaimana informasi diatur dan dimaksimalkan.\r\n\r\nSelain itu, DBMS juga memfasilitiasi pengawasan dan pengendalian basis data. Dengan bantuan DBMS, admin bisa memantau kinerja, menyeting aplikasi, dan melakukan backup dan recover database ketika dibutuhkan. Contoh DBMS adalah: MySQL, Microsoft Access, Microsoft SQL Server, FileMaker Pro, Oracle Database, dan dBASE.', 'Publish', 'Banu Sihwanto', '2026-05-29', 'uploads/dokumen/doc_1780050332_802.pdf', '2026-05-29 10:25:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `games`
--

CREATE TABLE `games` (
  `id` int NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `genre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci NOT NULL,
  `banner` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `folder_game` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `pembuat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `games`
--

INSERT INTO `games` (`id`, `judul`, `genre`, `deskripsi`, `banner`, `folder_game`, `pembuat`, `tanggal`) VALUES
(4, '8 Ball Billiards Classic', 'Strategy', 'Permainan mengontrol bidikan dan kekuatan stik untuk memasukkan semua bola target (kelompok bola penuh atau garis) ke dalam lubang, diakhiri dengan bola hitam bernomor 8.', 'game_1780062202_587.jpg', '8-ball-billiards-classic_1779557727', 'Banu Sihwanto', '2026-05-23'),
(6, 'Shape Matcher', 'Puzzle', 'Pemain harus menukar (swap), mengetuk, atau menempatkan bentuk geometris seperti lingkaran, segitiga, kotak, atau bintang agar sesuai dengan pola, target, atau tempat yang telah ditentukan. Permainan ini dirancang untuk menguji kecepatan reaksi, fokus, dan pemecahan masalah.', 'game_1780064743_146.jpg', 'shape-matcher_1780064855', 'Banu Sihwanto', '2026-05-29'),
(7, 'Canvas Stick Hero', 'Strategy', 'Pemain menahan tombol atau layar untuk memanjangkan tongkat. Jika tongkat terlalu panjang atau terlalu pendek, karakter akan jatuh ke jurang dan permainan berakhir.', 'game_1780065077_535.jpg', 'canvas-stick-hero_1780066995', 'Banu Sihwanto', '2026-05-29'),
(8, 'Planet Defense', 'Strategy', 'Permainan strategi (tower defense) dan aksi antariksa dimana pemain bertugas melindungi planet asal dari gelombang invasi dan serangan asteroid.', 'game_1780068839_523.jpg', 'planet-defense_1780068839', 'Banu Sihwanto', '2026-05-29'),
(9, 'Flappy Bird KW', 'Sports', 'Flappy Bird adalah game arkade legendaris di mana pemain mengetuk layar agar burung bernama Faby bisa terbang dan menghindari celah di antara pipa-pipa hijau.', 'game_1780070027_247.jpg', 'flappy-bird-kw_1780070540', 'Banu Sihwanto', '2026-05-29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `komentar_artikel`
--

CREATE TABLE `komentar_artikel` (
  `id` int NOT NULL,
  `post_id` int NOT NULL,
  `pembuat` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `komentar` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `komentar_artikel`
--

INSERT INTO `komentar_artikel` (`id`, `post_id`, `pembuat`, `komentar`, `created_at`) VALUES
(1, 24, 'Banu Sihwanto', 'tes komentar🧡😁', '2026-06-05 14:02:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `komunitas`
--

CREATE TABLE `komunitas` (
  `id` int NOT NULL,
  `pembuat` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `konten` text COLLATE utf8mb4_general_ci NOT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `video` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `komunitas`
--

INSERT INTO `komunitas` (`id`, `pembuat`, `konten`, `gambar`, `video`, `created_at`) VALUES
(35, 'Banu Sihwanto', 'solusi tanpa konklusi', 'uploads/komunitas/post_img_1779558094_845.mp4', NULL, '2026-05-23 17:41:34'),
(36, 'Banu Sihwanto', '', NULL, 'uploads/komunitas/post_vid_1779558125_889.mp4', '2026-05-23 17:42:05'),
(39, 'Banu Sihwanto', '', NULL, 'uploads/komunitas/post_vid_1779560453_950.mp4', '2026-05-23 18:20:53'),
(42, 'Banu Sihwanto', 'Lewis Capaldi - Strangers', NULL, 'uploads/komunitas/post_vid_1780049955_271.mp4', '2026-05-29 10:19:15'),
(47, 'Devinco Sayendra', '', 'uploads/komunitas/post_img_1780449941_928.mp4', NULL, '2026-06-03 01:25:41'),
(48, 'Mohamad Rafa Naresa', 'bakso tanpa tepung', 'uploads/komunitas/post_img_1780449963_434.mp4', NULL, '2026-06-03 01:26:03'),
(51, 'Intan Nul Janah', '', 'uploads/komunitas/post_img_1780452681_742.mp4', NULL, '2026-06-03 02:11:21'),
(53, 'Nur Aminudin', '', 'uploads/komunitas/post_img_1780453073_141.mp4', NULL, '2026-06-03 02:17:53'),
(54, 'Yolanda Nurjana Maulin', 'ea', 'uploads/komunitas/post_img_1780453261_172.mp4', NULL, '2026-06-03 02:21:01'),
(55, 'Mohamad Rafa Naresa', 'READY\r\nJasa Roamer Jago😎', NULL, 'uploads/komunitas/post_vid_1780453462_708.mp4', '2026-06-03 02:24:22'),
(56, 'Mely Rahmawati', 'Jalan - jalan ke kota tua , \r\nBertemu lagi dengan kita berdua 😜', 'uploads/komunitas/post_img_1780453591_438.mp4', NULL, '2026-06-03 02:26:31'),
(57, 'mila ayu rizkiyana', 'Sarangeoo', NULL, NULL, '2026-06-03 02:30:20'),
(58, 'mila ayu rizkiyana', 'Sarangeoo', 'uploads/komunitas/post_img_1780453849_159.mp4', NULL, '2026-06-03 02:30:49'),
(63, 'Nabila Assyifa Putri', 'warna merah warna cinta 🤍🤍', 'uploads/komunitas/post_img_1780469829_453.mp4', NULL, '2026-06-03 06:57:09'),
(65, 'Arya Seno', 'Test Encode 2', NULL, NULL, '2026-06-03 11:58:35'),
(66, 'Serly Melani Putri', 'minuman minuman apa yang cape?', NULL, NULL, '2026-06-03 12:56:14'),
(71, 'Banu Sihwanto', 'angkatan 24 kelas A cantik2 dan gagah2 kan 🥰😍', NULL, 'uploads/komunitas/post_vid_1780742543_501.mp4', '2026-06-06 10:42:23'),
(72, 'Yolanda Nurjana Maulin', 'sunday BBQ🍢', NULL, 'uploads/komunitas/post_vid_1780818966_276.mp4', '2026-06-07 07:56:06'),
(73, 'Yolanda Nurjana Maulin', '🐱🐈', 'uploads/komunitas/post_img_1780820101_477.mp4', NULL, '2026-06-07 08:15:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `komunitas_komentar`
--

CREATE TABLE `komunitas_komentar` (
  `id` int NOT NULL,
  `post_id` int NOT NULL,
  `pembuat` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `komentar` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `komunitas_komentar`
--

INSERT INTO `komunitas_komentar` (`id`, `post_id`, `pembuat`, `komentar`, `created_at`) VALUES
(7, 35, 'Arya Seno', 'Test1', '2026-06-02 14:49:58'),
(8, 51, 'Banu Sihwanto', 'nice', '2026-06-03 02:14:01'),
(9, 51, 'Nur Aminudin', 'Ahlan Wasahlan', '2026-06-03 02:15:47'),
(10, 48, 'Nur Aminudin', 'Bakso nopo niki njeh?', '2026-06-03 02:16:52'),
(11, 51, 'Mohamad Rafa Naresa', 'AI ini', '2026-06-03 02:17:52'),
(12, 53, 'Nur Aminudin', 'Universitas Nusantara PGRI Kediri Gelar CIVITAS 2026, Perkuat Kolaborasi Internasional untuk Transformasi Sosial Berkelanjutan', '2026-06-03 02:19:20'),
(13, 51, 'Fadhila Marwa Aulia', 'BIDADARI SURGA', '2026-06-03 02:25:04'),
(14, 55, 'Gilang Dwiky Arvianto', 'Gas 🗿', '2026-06-03 02:28:11'),
(15, 56, 'Fadhila Marwa Aulia', 'gadisss ti', '2026-06-03 02:29:41'),
(16, 57, 'Fadhila Marwa Aulia', 'SARANG TAWON', '2026-06-03 02:31:05'),
(18, 63, 'Banu Sihwanto', 'kurang cinta itu ya, itu bisa bikin emoj love love gmn caranya bil', '2026-06-03 09:36:37'),
(19, 58, 'Banu Sihwanto', 'yang depan petama okee, yang depan kedua okee, itu cowok yang paling ujung siluman kah wkwkwk', '2026-06-03 09:39:46'),
(21, 66, 'Banu Sihwanto', 'kayaknya gw tau ni, Es em pe bukan yaa', '2026-06-03 13:38:54'),
(22, 65, 'Banu Sihwanto', 'lah ini man videonya sen', '2026-06-03 13:39:21'),
(23, 65, 'Arya Seno', 'ini yg test pass upload terus tak refresh browser nya mas', '2026-06-03 14:20:26'),
(24, 65, 'Banu Sihwanto', 'owalah videonya ke cencel ya wkwk, nanti malem ku kerjain lagi untuk kompresinya sen', '2026-06-03 14:28:48'),
(25, 55, 'Nabila Assyifa Putri', 'info mabar', '2026-06-03 14:35:38'),
(26, 66, 'Banu Sihwanto', '😂', '2026-06-03 16:24:30'),
(27, 72, 'Banu Sihwanto', 'keren 😋', '2026-06-07 08:03:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `komunitas_likes`
--

CREATE TABLE `komunitas_likes` (
  `id` int NOT NULL,
  `post_id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `komunitas_likes`
--

INSERT INTO `komunitas_likes` (`id`, `post_id`, `username`, `created_at`) VALUES
(17, 42, 'Banu Sihwanto', '2026-05-31 15:19:13'),
(18, 36, 'Banu Sihwanto', '2026-05-31 15:21:57'),
(19, 35, 'Banu Sihwanto', '2026-05-31 15:22:11'),
(21, 39, 'Banu Sihwanto', '2026-05-31 17:18:49'),
(22, 42, 'Arya Seno', '2026-06-02 14:49:33'),
(23, 48, 'Devinco Sayendra', '2026-06-03 01:27:27'),
(24, 47, 'Devinco Sayendra', '2026-06-03 01:28:24'),
(26, 51, 'Banu Sihwanto', '2026-06-03 02:12:15'),
(27, 48, 'Nur Aminudin', '2026-06-03 02:16:23'),
(28, 51, 'Nur Aminudin', '2026-06-03 02:16:28'),
(29, 56, 'Fadhila Marwa Aulia', '2026-06-03 02:28:54'),
(32, 57, 'mila ayu rizkiyana', '2026-06-03 02:30:59'),
(33, 55, 'Mohamad Rafa Naresa', '2026-06-03 02:31:05'),
(34, 58, 'mila ayu rizkiyana', '2026-06-03 02:31:12'),
(36, 57, 'Fitrah Ayu Dealova', '2026-06-03 02:31:38'),
(37, 57, 'Vyhna Khoirul Lativah', '2026-06-03 02:39:02'),
(38, 58, 'Vyhna Khoirul Lativah', '2026-06-03 02:39:09'),
(39, 56, 'Vyhna Khoirul Lativah', '2026-06-03 02:39:13'),
(40, 58, 'Banu Sihwanto', '2026-06-03 05:17:21'),
(41, 57, 'Banu Sihwanto', '2026-06-03 05:17:31'),
(43, 57, 'Nabila Assyifa Putri', '2026-06-03 06:53:57'),
(44, 63, 'Nabila Assyifa Putri', '2026-06-03 06:57:57'),
(45, 53, 'Nabila Assyifa Putri', '2026-06-03 06:59:09'),
(46, 63, 'Banu Sihwanto', '2026-06-03 09:35:41'),
(47, 53, 'Banu Sihwanto', '2026-06-03 11:18:43'),
(48, 66, 'Banu Sihwanto', '2026-06-03 13:37:36'),
(49, 65, 'Banu Sihwanto', '2026-06-03 13:39:02'),
(50, 56, 'Banu Sihwanto', '2026-06-03 13:39:46'),
(52, 55, 'Banu Sihwanto', '2026-06-03 13:39:55'),
(53, 48, 'Banu Sihwanto', '2026-06-03 13:40:09'),
(54, 47, 'Banu Sihwanto', '2026-06-03 13:40:13'),
(56, 72, 'Yolanda Nurjana Maulin', '2026-06-07 07:56:46'),
(57, 72, 'Banu Sihwanto', '2026-06-07 08:02:33'),
(58, 73, 'Banu Sihwanto', '2026-06-07 08:26:45'),
(59, 71, 'admin', '2026-06-07 11:35:23'),
(60, 73, 'admin', '2026-06-07 11:35:32'),
(61, 72, 'admin', '2026-06-07 11:35:36'),
(62, 66, 'admin', '2026-06-07 11:35:44'),
(63, 65, 'admin', '2026-06-07 11:35:49'),
(64, 63, 'admin', '2026-06-07 11:35:54'),
(65, 58, 'admin', '2026-06-07 11:36:03'),
(66, 56, 'admin', '2026-06-07 11:36:11'),
(67, 55, 'admin', '2026-06-07 11:36:25'),
(68, 54, 'admin', '2026-06-07 11:36:30'),
(69, 53, 'admin', '2026-06-07 11:36:35'),
(70, 51, 'admin', '2026-06-07 11:36:40'),
(71, 48, 'admin', '2026-06-07 11:36:45'),
(72, 47, 'admin', '2026-06-07 11:36:50'),
(73, 71, 'Satria Adjie Saputra', '2026-06-07 15:14:50'),
(74, 39, 'Ardafa Ilham Pranosa', '2026-06-07 15:46:06'),
(75, 54, 'Banu Sihwanto', '2026-06-10 03:25:14'),
(76, 35, 'admin', '2026-06-10 03:25:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `Id` int NOT NULL,
  `user_target` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `user_pemicu` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tipe` enum('komen_artikel','komen_sosial','like_sosial') COLLATE utf8mb4_general_ci NOT NULL,
  `id_sumber` int NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`Id`, `user_target`, `user_pemicu`, `tipe`, `id_sumber`, `is_read`, `created_at`) VALUES
(1, 'Yolanda Nurjana Maulin', 'Banu Sihwanto', 'like_sosial', 72, 1, '2026-06-07 08:02:33'),
(2, 'Yolanda Nurjana Maulin', 'Banu Sihwanto', 'komen_sosial', 72, 1, '2026-06-07 08:03:28'),
(3, 'Yolanda Nurjana Maulin', 'Banu Sihwanto', 'like_sosial', 73, 1, '2026-06-07 08:26:45'),
(4, 'Banu Sihwanto', 'admin', 'like_sosial', 71, 1, '2026-06-07 11:35:23'),
(5, 'Yolanda Nurjana Maulin', 'admin', 'like_sosial', 73, 1, '2026-06-07 11:35:32'),
(6, 'Yolanda Nurjana Maulin', 'admin', 'like_sosial', 72, 1, '2026-06-07 11:35:36'),
(7, 'Serly Melani Putri', 'admin', 'like_sosial', 66, 0, '2026-06-07 11:35:44'),
(8, 'Arya Seno', 'admin', 'like_sosial', 65, 0, '2026-06-07 11:35:49'),
(9, 'Nabila Assyifa Putri', 'admin', 'like_sosial', 63, 0, '2026-06-07 11:35:54'),
(10, 'mila ayu rizkiyana', 'admin', 'like_sosial', 58, 0, '2026-06-07 11:36:03'),
(11, 'Mely Rahmawati', 'admin', 'like_sosial', 56, 0, '2026-06-07 11:36:11'),
(12, 'Mohamad Rafa Naresa', 'admin', 'like_sosial', 55, 0, '2026-06-07 11:36:25'),
(13, 'Yolanda Nurjana Maulin', 'admin', 'like_sosial', 54, 1, '2026-06-07 11:36:30'),
(14, 'Nur Aminudin', 'admin', 'like_sosial', 53, 0, '2026-06-07 11:36:35'),
(15, 'Intan Nul Janah', 'admin', 'like_sosial', 51, 0, '2026-06-07 11:36:40'),
(16, 'Mohamad Rafa Naresa', 'admin', 'like_sosial', 48, 0, '2026-06-07 11:36:45'),
(17, 'Devinco Sayendra', 'admin', 'like_sosial', 47, 0, '2026-06-07 11:36:50'),
(18, 'Banu Sihwanto', 'Satria Adjie Saputra', 'like_sosial', 71, 1, '2026-06-07 15:14:50'),
(19, 'Banu Sihwanto', 'Ardafa Ilham Pranosa', 'like_sosial', 39, 1, '2026-06-07 15:46:06'),
(20, 'Yolanda Nurjana Maulin', 'Banu Sihwanto', 'like_sosial', 54, 0, '2026-06-10 03:25:14'),
(21, 'Banu Sihwanto', 'admin', 'like_sosial', 35, 1, '2026-06-10 03:25:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_logged_in` tinyint(1) DEFAULT '0',
  `last_activity` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `avatar`, `is_logged_in`, `last_activity`, `created_at`) VALUES
(5, 'admin', '$2y$10$IoWngVlXyY01kw9dWH/XIO7cpTL325fNApraJfZgBKZU8zZqHWXW.', 'uploads/profil/avatar_admin_1780514621.jpg', 1, '2026-06-15 05:42:27', '2026-05-20 07:34:11'),
(15, 'Banu Sihwanto', '$2y$12$cjxStVxmB80LgqgjGurLSuZyMdL1EyJRxksfRR2F5GymTnFRxSqmy', 'uploads/profil/avatar_Banu Sihwanto_1780514176.jpg', 1, '2026-06-11 01:35:31', '2026-05-23 17:11:19'),
(16, 'Arya Seno', '$2y$12$MwyQ4e6m0m.CS7Uq7IfHzuzogZersieEI5zoaDDsJYbBXZ1Sa6pei', 'uploads/profil/avatar_Arya Seno_1780411746.jpg', 0, NULL, '2026-05-23 17:11:56'),
(17, 'Muhamad Rafi Maulana', '$2y$12$.h1lU1yFpfZymS62Z61Pq.xkXZwoVSGwCu6omiEokIfJKqUCYg.Nu', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:12:15'),
(18, 'Muhamad Adi Winata', '$2y$12$tNr2vLApwCtShLRUF/CZrOmUc/MDitgP4g3E0GqSNOVQNF8/eFTKS', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:12:34'),
(19, 'Vyhna Khoirul Lativah', '$2y$12$6J9OvUfM3vcP/c.ykuJ2teWADbJMrlZAuPNjByu.WQthTABO4K5Te', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:12:51'),
(21, 'Fadhila Marwa Aulia', '$2y$12$xnqD9V1CmGQviTbAulfFYu9HlMu/nBOGSCNJBYB24ljX7f8djeXj6', 'uploads/profil/avatar_Fadhila Marwa Aulia_1780453558.jpg', 0, NULL, '2026-05-23 17:15:18'),
(22, 'Intan Nul Janah', '$2y$12$.XvocSUDnqKLTx2iEJo8fupLprpewZn3UgLl7yPPNUhx3TzhLgZ1K', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:15:37'),
(23, 'Hafid Ibnu Dakwan', '$2y$12$ghafQ3ZWLrWblZVFuoCYw.pU.6vc8NBpL8ASx0uW6Me6Wz/0kkIsK', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:16:15'),
(24, 'Zola Melbi Yantara Pratama', '$2y$12$5EDDLmI9PpKcPwc56Fg7z.i31YU7hQ5yk7p1f./2u0bG3ktjjO.lS', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:16:33'),
(25, 'Nabila Assyifa Putri', '$2y$12$G4wgDaAVFGRR0CuBlJup5eU9QmRkoWglGjpqsfAQM.hpnR6W0jFQ2', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:19:28'),
(26, 'Rino Alzidhan Purnomo', '$2y$12$FDUovuEjBmjK1/ARF339nu7LZED2Xbk6H8f3xbxTe0r3WIQ6l.yEG', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:20:13'),
(27, 'Risti Eka Saputri', '$2y$12$1CtoHzOZn8CwwB7g8U4rpuNcZnDSWuaPqkuwu2wRfR8iMtKO4xu8q', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:20:28'),
(28, 'Fitrah Ayu Dealova', '$2y$12$e1LqVDomM0ZJ1Zv8U/my1eJLu2Vf2ZKZFDCwpx5/dRLLN7TLFtgv.', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:20:46'),
(29, 'Yolanda Nurjana Maulin', '$2y$12$eEKwEF/3UU1FstCXEeMVIOFR.M2FnFMZ8N0.RRbNKV462pVL85ZmS', 'uploads/profil/avatar_Yolanda Nurjana Maulin_1780453085.jpeg', 0, NULL, '2026-05-23 17:21:05'),
(30, 'Gilang Dwiky Arvianto', '$2y$12$wIUTBjzUN5cn5obiLF3SCO09L/cldpEfHYhhqVQpldkVIUZnZizP2', 'uploads/profil/avatar_Gilang Dwiky Arvianto_1780453567.jpg', 0, NULL, '2026-05-23 17:21:22'),
(31, 'Serly Melani Putri', '$2y$12$Tj6pMIheedNGODYJtmWwce7ayPJ./OZmvKHO5A9sdmNlhp9/h0FzW', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:21:36'),
(32, 'Arif Nurul Al Aziz', '$2y$12$JMUzVojzDf5FYELKvz2Q1ujCiVJmWOlFBAL3y2qxQ82GQOFjfghsK', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:21:54'),
(33, 'Zahara Aulia', '$2y$12$26DItiBIQHALApj5n2M.yO7gWc5VeqNyoY721QHY00rjJ5WIq7UcK', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:22:07'),
(34, 'Mohamad Rafa Naresa', '$2y$12$qqtVb1J/OM3dGYBb/Ke4bealQPqj5pPd1zcaretRIDlGs9DWscagu', 'uploads/profil/avatar_Mohamad Rafa Naresa_1780453195.jpg', 0, NULL, '2026-05-23 17:22:21'),
(35, 'Mely Rahmawati', '$2y$12$G7ccyvXzp8JEIpriZEPWMeEbZaV9LAcUB.JalOZP/FZ/EW2uHKwfu', 'uploads/profil/avatar_Mely Rahmawati_1780453919.jpeg', 0, NULL, '2026-05-23 17:22:36'),
(36, 'Bagas Rifanda', '$2y$12$.TaDDfD2b2QRzqK1Xch1BOuETemBW/CMsztZQEik0.EVd5VNsiyP.', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:22:52'),
(37, 'Angga M Aditiya', '$2y$12$7D9HSACmAC3/V6r7mhiMX.EVkpVo1k3o8d9WvzoPGWtDtyF9InjcK', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:23:12'),
(38, 'M Arifur Rokhman', '$2y$12$3.AF/ukCKAaq5d5TtW5HS.occLFMH3s5FjFNVGJ/LRLJPkCyyVi1S', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:23:28'),
(40, 'Talenta Pratama Dio', '$2y$12$LTqRiKx9Mqv13RyYJNx/fuX4deIzZ08pcUXdArTO881v5STjNph.a', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:24:01'),
(41, 'Achmad Fauzidan Defitra', '$2y$12$Bs05v3rtOnYd5H0BxYiB9ec9JLZW8kno9tMPDdjBl9QsQy6rq4j2e', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:24:15'),
(42, 'Devinco Sayendra', '$2y$12$dfkeczMtihC6im0mNxG2gOzuz81/0ez34tu90WQyz95DKcCaIndui', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:24:30'),
(43, 'Pangestu Abi Fauzan', '$2y$12$TECvAonNZzVT99M78163kOIHshjYJEBy/AzW.mebR0lc7GNw9CnJG', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:24:44'),
(44, 'Reza Artikamevia', '$2y$12$ZWWs6Zo.TqhH0doKXsVTi.M5gPH7hBzm7jb8fwlc625izOeb1uh0K', 'uploads/profil/default.jpg', 0, NULL, '2026-05-23 17:25:00'),
(45, 'Nur Aminudin', '$2y$12$h7tAgm6cK.UWOaDZJ1AEDOTwcwMdA27y2GbF/h6zxh2NA4y1owTui', 'uploads/profil/avatar_Nur Aminudin_1780453243.jpg', 0, NULL, '2026-06-03 01:44:11'),
(46, 'mila ayu rizkiyana', '$2y$12$W7/c1S2Bv.yMKJbydgp4keiBTJHRLlad4c8ag9oMUH17s13qhXUfO', 'uploads/profil/default.jpg', 0, NULL, '2026-06-03 02:27:44'),
(47, 's.ti dwijayanti', '$2y$12$uWkX3vT7qktQ4BjVO2elLe/7VW.27U3xuU8KZB9DYIv6SDhg/tqYO', 'uploads/profil/default.jpg', 0, NULL, '2026-06-03 02:29:13'),
(48, 'alfin deny sugiarto', '$2y$12$bw2SDVnZ0OPOby09W6fzseb56K6N4hmu6P3wJ4.ArzBzCgX1VObiW', 'uploads/profil/default.jpg', 0, NULL, '2026-06-03 02:31:09'),
(49, 'Hafsah Mukaromah', '$2y$12$FPw0N9m7cOp0nxzj2gqAdOa86UdnK8gzEDTsj9zop6G0LXfE5zF4.', 'uploads/profil/default.jpg', 0, NULL, '2026-06-05 05:54:40'),
(50, 'Bhakti Halaza', '$2y$12$nmWRA375EoDbsQpMNPaDKOGAPUoI3WqHhn9m8D9AJmpkWBIZPQZKe', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:45:19'),
(51, 'Amri Fahmi', '$2y$12$TXHx3OyKx7eTFG2hGAsIGe5JhQ5DqZf3nPJXNSxcUsGcpIOVEtF0q', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:45:55'),
(52, 'Oktavia Wulan dari', '$2y$12$PyR2TejqaaxiS8Vpw1P7G.X3HCh9QoqMoimaLp4wTH9HCFTsktrWa', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:46:15'),
(53, 'Salsa Geti Anjayeni', '$2y$12$OfPHimfgUMLOxXR/X7viZ.XlDASlMUPkbwFNjxc3il7eD8HL7uCS6', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:50:20'),
(54, 'Keisya Shifa Ahadiyah', '$2y$12$tKiZGwGUDCgKzR/7g7n6C.ZaAAMXJd7MhIgeYo/.WFEGVabBZpfBO', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:50:47'),
(55, 'Shofi Zamrotus Suni', '$2y$12$TC7H4kBt.wRzgGsiT5dnNeNK4haJTuUlAX3NPEJWnyCR3Y2xL4pYK', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:51:08'),
(56, 'Akbar Maulana Kusuma', '$2y$12$56b4OY/gy2lCTQRb32ucnuh0Z9xV2aqicYFaJAqEZ0Wtw3cocnHwO', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:51:35'),
(57, 'Haikal Wahyu Ramadani', '$2y$12$IxDAY1V0Yn3GkHUioWp2f.I1XxjhYYnB.FItAhojM.3eGESyP/edm', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:51:55'),
(58, 'Redho Kurniawan', '$2y$12$boy07r3VDnIeT4RJWx7FGuxzrKXybIdwDuTPE2n8ozhZsNIVrb0QG', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:52:17'),
(59, 'Harpy Ibrahim', '$2y$12$bleAmT14jqPKWFTR84C3.eVqAa8oscU5SkhoR9UdLL2.o/301Uoty', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:52:39'),
(60, 'Queen Viola Maharani', '$2y$12$8zFcnShXxtPEXb/bNfIzverb1BG.tUZc8onPnE2AbTv/JZBQc9oya', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:53:03'),
(61, 'Siti Anisa', '$2y$12$x73P.AdE19/wVbcSTDp/3O532hcipvGI1A9Ubh5IvHlcZbgkfBVg6', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:53:44'),
(62, 'Tahria Andani', '$2y$12$oFCSV1VGhVcmPPqRSV58uudega8kKN6wg8y1n9B6YHG2z/Yw1TFq2', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:58:18'),
(63, 'Dimas Ramadani', '$2y$12$AVICduluSBB7fUYpH4XDi./HUaPSaOPPMPjgezQEjlFfvNWB2.sya', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:58:39'),
(64, 'Novrian Abi Pratama', '$2y$12$BRi6.bVpiauZqJr0EhyPXutYvKz7DiiDF9mcsw0BX5ut6E6FiWylu', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:59:02'),
(65, 'Sachi Meylasari', '$2y$12$9.SLbVcHEI1PmZlO9VY2/.BnCD04LagjxtgmSoWGhlhFIaDLU/e7.', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 15:59:40'),
(66, 'Ayudya Nabila Putri', '$2y$12$UVaaactWp9v2d265GIbu/.Mgxf/rjmvFp0V3OmpeAOmdsoyGXxc7u', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:02:18'),
(67, 'Satria Adjie Saputra', '$2y$12$bPf1daJhAHzIrBg7eVjIFuzA5x43R5xet2Psf6rL.EVY/WvPsROXO', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:02:39'),
(68, 'Ilham Aziz', '$2y$12$EdvRWmndjP2wkqD25hJp.uXy4.DBz7s2FNOdnqhOQ2xR10LR2.aW6', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:02:59'),
(69, 'Iklal Aqbar', '$2y$12$rtAL/HxDRtvgc2/gWQ3ko.iGvTRvL/Q75Nu8QFP6ElE/nvlmOK3kK', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:03:37'),
(70, 'Haris Hilmy Al Muyassar', '$2y$12$QtV26nzQCLykpaz5hj3LPO88M.uaLxS9.P6d647JU4VuBsxQ83mLK', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:04:01'),
(71, 'Muhammad Nailul Farid', '$2y$12$eS2/JDZUWqtSNqUyYUwzuO4FIMWTagT3LGVwAfdDgojno9Lcvbhbi', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:04:36'),
(72, 'Morza Delistia Putri', '$2y$12$eBV6GoGR/NzN0a7fZ6z9LeUEp18McAAei0ZPUEPXEqJjB47Rc1FQG', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:04:56'),
(73, 'Naja Andrean Maulana', '$2y$12$XwtrfydLG95J7dx4ghesEukjYR/Ckoz1qs0C5aPfTAn0UV5fbI/0m', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:05:20'),
(74, 'Rafly Desmawan', '$2y$12$vLMkleLuifSAJd/h6p/KU.rlW8IAHphbLg5Rktgp9CFqaplW/aJ4S', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:06:18'),
(75, 'Isma Liana', '$2y$12$UV3oSNI2ByDQmR/PY0vcs.WZuHtgza8ruV50mqIamMp8goZr1FtyG', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:06:39'),
(76, 'Abdullah Fatih Nawfal', '$2y$12$KkZ2c6asEBl/15s7Xd47..c8/QsWmryxsV0oS5CnGFWkUyzf.XqRq', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:07:15'),
(77, 'Naufal Rafiq', '$2y$12$eH97556xQ4zKvqUH00.rqe7JleTFxqZ8qwSbYq03xZvuwt0Q8QD66', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:07:42'),
(78, 'Eka Noviani', '$2y$12$tl3vyx3AJafCyYJ6jBtSVOtIRpbFZyl/Cta.Y7KGUMVr7315eiRYS', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:08:13'),
(79, 'Yova Aldi Nanta', '$2y$12$IKTvynpwO9l9lLdhlXXE7uysa4c.jDQtujkMt/i2K2Mp3CE6Sx6DG', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:10:30'),
(80, 'Nayna Alya Syarief', '$2y$12$RdUtePhwpVefH7lcKC8ltegk6K4eRU.vpsx3Sy8nN3850oL59YIS6', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:10:50'),
(81, 'Najwani Gian Massaid', '$2y$12$r87Sn6XRVuSVwUQT2gv2ferlASW97ezECV0EOk8amZD5F4BFWyjmG', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:11:13'),
(82, 'Rafi Rasyid Joevana', '$2y$12$P9HE1Rhx9eBmfhvIV4YtKeROKHizWmHKqO.nJvsWZW3oINQs/F4xe', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:13:21'),
(83, 'Pandu Hidayat', '$2y$12$RNmcqxrWfmYQ8hlremC3v.zS/Hl/5tQmdG.oq/2BxS1D2iau0xDV6', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:13:43'),
(84, 'Hanif Albukhori', '$2y$12$19lpe1nMCe6bSxWii5fdI.CxC8QeHxk75yzzyXSr2hS8wZm.XCgbS', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:14:03'),
(85, 'Muna Fauziana', '$2y$12$FqrGVh.LZPbcH.o3E57cZujh1BifHPIwgU5zd6.qpc7E64dG7Flze', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:14:34'),
(86, 'Dimas Putra Abimanyu', '$2y$12$1GAAohaK5UZA9HDF.VLFFe/645TNlQM84esaG5vrkE8rIQ./YxJYC', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:14:57'),
(87, 'Dina Amelia Putri', '$2y$12$qINK4SInuGIc.to5Oa6qn.ek9.AxPVshNlIG9QNKnkmopj5owFk3.', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:15:19'),
(88, 'Lidianti Hariani', '$2y$12$YDZo7MA1.WMPULD/oeKTFeVt2nqem2pizdD66fA96KgWK5qbuhKIq', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:15:48'),
(89, 'Tegar Surya Adiputra', '$2y$12$kPw6BOKgz8xE47Pj9bIawuaJERD26R7gZSE3DXOtXD7xJsmiXx.ya', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:16:21'),
(90, 'Muhammad Daval Ahfarozi', '$2y$12$D9FbJJmSz1tP76jt19SOcO01lgWIVnTJsLWKmetnbbPVLQOjeXfly', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:16:43'),
(91, 'Haikal Zidan Albar', '$2y$12$H6s5EA4bYrAoIOySoHP6HuCrVygzcDvqzD76xF.DXMbgRlYc091fm', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:17:04'),
(92, 'Syeluna Kirania Johan', '$2y$12$oJy89e6TAkqfgjqjJh9I.uEzxrG3OimPZZbzi.TCax0uVAZIA5xiS', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:17:25'),
(93, 'Jessicha Aulia Putri', '$2y$12$SfbyjmE3uKoqsuqETAX4IuvlR562FJSk18liKN0J2BYyDMSSAJ30W', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:19:46'),
(94, 'Rahma Maulida', '$2y$12$Z2Hooy7yIXsrlCT7STwLm.k4Eh.T/DHBL6A/WDzYWxBXU.uGlGX6i', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:20:54'),
(95, 'Ibnu Satrio', '$2y$12$rGu47uFnJNIPxDzrIsRAue8ruA6WPNVSHXRNTvXyZ1EPYBn4PziM2', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:22:43'),
(96, 'Vinkan Febrina Sari', '$2y$12$alq1i./2myVY2IE3FodmN.zSQvEgMQfYZxJne7/Ys/9RqNWzva1K2', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:23:00'),
(97, 'Marchel Danes Seva', '$2y$12$W514JJ62.3KYndhwDZ9/TOvX/m6QtXJ4sea2xy.MfiQeZP4iuSvBe', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:23:32'),
(98, 'Dzulfahmi Hafidz', '$2y$12$REgMECDCn.yXlMNFu.K99.ulDsjsjapr9qZVZSzIAb51SfV15FoOa', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:23:51'),
(99, 'Muhammad Ridho', '$2y$12$8J40nRWqI7/zgLTODvSm.OBLybJ5ICBSD3INb3g/Kpm9qH/RGvIBK', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:24:12'),
(100, 'Junior Rafi Riskiansah', '$2y$12$LXsOXJvJvcSUEK5LrWIobOII91o5vVo/klRFzlVc/qPyY0CLwobv6', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:24:37'),
(101, 'Tri Sandia Mukti', '$2y$12$tNCX5yj/9G9Rp8lfC643Qey.YnWUN.3FuhccJkVNJv7v0BK.PqYsy', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:25:15'),
(102, 'Rena Revalina', '$2y$12$Weja9t1lwbL3brZh65zHK.fc7rwiMALQ2lg67Dmqc8OfnIF8YkBv2', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:26:41'),
(103, 'Dimas Prasetio', '$2y$12$hpAhbr0yTXeay1VRZBen5eqrc4VAIA5lfF/vqs00gRY51.E4RgCRm', 'uploads/profil/avatar_Dimas Prasetio_1780808400.jpg', 0, NULL, '2026-06-06 16:27:03'),
(104, 'Tegar Febriyano', '$2y$12$X5.y2TRNkkmK.VzU9OckE.bSxkAzUAEYooL2NsxvWth.bWfpEpLsO', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:27:21'),
(105, 'Hagym Muda Pasarela', '$2y$12$tDf/OPGKsxHxw7NDZy20d.IMPreXCu0dk0edyIWGNgYlCTkD2tzrS', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:27:42'),
(106, 'Wahyu Bagus Pratama', '$2y$12$M/dgTOH.mYUgqHFPUyfyweZd/bQlrGqiNe24asYHf7xQpYbtbSuHO', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:28:02'),
(107, 'Arum Permadi', '$2y$12$upNp9xxrISsK26czU2eF4e3PWyEtcG.amEMqNo.cRuGmukNunm4W2', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:28:23'),
(108, 'M Gibran Ardafi', '$2y$12$4xHZmf5dsDqhMFhD1QL40OxJa3ZyLNNMByQm0RQ3JVd0fWWqvNeEK', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:29:56'),
(109, 'Desi Mawati', '$2y$12$VWhpmo5a1mIN14NnME1nxOR3qgEsGsXceQRurDpq2WARcygX00bqm', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:30:35'),
(110, 'Ahmad Sihabudin', '$2y$12$4ycARUeeg3uVdl0dV15Wbeghnw307paPz3vb22LadgMJdYEqt7v8C', 'uploads/profil/avatar_Ahmad Sihabudin_1780822916.jpg', 0, NULL, '2026-06-06 16:31:01'),
(111, 'Putri Puspita Damayanti', '$2y$12$2f.1fnM/bPiMhTJDogylKe6vNYnGUKf2YJ4B6qnFDWUaWcjbvsqCK', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:31:21'),
(112, 'Nabelia Putri', '$2y$12$azdMf.hfpVjJOXymj5hrz.FlZIgfPzDBhEqFtCTHnhlUFmjKpW782', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:31:46'),
(113, 'Yusef Dita Kusuma', '$2y$12$OXuIrLzG14wU7ZJz0vyXAu.lDRUnITWlSeDA8cqL90.ji0oyD3Wtq', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:32:10'),
(114, 'Ridho Febri Tioko', '$2y$12$vFtfYzaysKS6L05r8D1GP.IJSd3Vqrb.Gmh8Nf7qrhkzFyTdZ7Ky.', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:32:38'),
(115, 'Lila Anisaroh', '$2y$12$Cjzviw72wmVHbWUYId2RDeE1U0TRaNMtkJASR0teMGjy/UkbLnIje', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:32:59'),
(116, 'Muhammad Zefri Dwitama', '$2y$12$lAAv4ffFSJQxmjPABceqduI6UXePUV5ziBPSDJdnpm0R371qGlMtO', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:33:30'),
(117, 'Habib Gusnan', '$2y$12$PmLLRlO0f2P7FId1zgygouIpkp1h2yJ9X6/I2Wm2NYPZoB9yziQbC', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:35:09'),
(118, 'Alya Kamalia', '$2y$12$LtBroNIwfI5fWZK4cVHED.MLH457ENPmthGnRPSBa3tzmiYskZPyq', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:35:35'),
(119, 'Andine Yashinta Prilly', '$2y$12$foR7Z9bd5zDSl6yBEhQUS./AMfat0iO09UmAnaNdM4DwOcaMQ6qUa', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:36:01'),
(120, 'Bilqis Salsabilla', '$2y$12$a9CM7Fx9F0Z1d/2CDAfbduUrBcqxmkHWIb9HezmuI/Kg2nenDYl96', 'uploads/profil/avatar_Bilqis Salsabilla_1780847249.jpg', 0, NULL, '2026-06-06 16:36:31'),
(121, 'Ardafa Ilham Pranosa', '$2y$12$os7kUliTMfjHhbpsoE7lDuiRiHQjCgFlrHGa9aE8DGut/4JsBXZkS', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:36:51'),
(122, 'Zalfaa Nur Aziizah', '$2y$12$gmPt3buFIMP1oPsq2Mg3q.k9SaD77cKXm.WIz/0CeYyjzkgMmt2TC', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:37:15'),
(123, 'Luna Azzalira', '$2y$12$TVBGE.zvsJaaR53YqFejA.uMqQCyFY6NIPMjxqnSG1wV8/TdtLfnm', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:37:40'),
(124, 'Shofiyyah Salwa Haajiroh', '$2y$12$CxD84V7XRVJnmSmhZ6ObUu0HnXvHDzZA3u23jQNxlGZuzsyZhF.TC', 'uploads/profil/default.jpg', 0, NULL, '2026-06-06 16:37:59'),
(125, 'Esa Rajib Nugroho', '$2y$12$mpeU.oAxaHPXb7bFQ1f3Xe3g3NO/OJODlnkSUFM9aXGQzevXE3X/a', 'uploads/profil/default.jpg', 0, NULL, '2026-06-08 06:49:13');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `aplikasi`
--
ALTER TABLE `aplikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_aplikasi_pengunggah` (`pengunggah`);

--
-- Indeks untuk tabel `artikel`
--
ALTER TABLE `artikel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_artikel_pembuat` (`pembuat`);

--
-- Indeks untuk tabel `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_games_pembuat` (`pembuat`);

--
-- Indeks untuk tabel `komentar_artikel`
--
ALTER TABLE `komentar_artikel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_komentar_artikel_post` (`post_id`),
  ADD KEY `fk_komentar_artikel_user` (`pembuat`);

--
-- Indeks untuk tabel `komunitas`
--
ALTER TABLE `komunitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_komunitas_user` (`pembuat`);

--
-- Indeks untuk tabel `komunitas_komentar`
--
ALTER TABLE `komunitas_komentar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_komentar_post` (`post_id`),
  ADD KEY `fk_komentar_user` (`pembuat`);

--
-- Indeks untuk tabel `komunitas_likes`
--
ALTER TABLE `komunitas_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`post_id`,`username`),
  ADD KEY `fk_likes_user` (`username`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `fk_notif_target` (`user_target`),
  ADD KEY `fk_notif_pemicu` (`user_pemicu`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `aplikasi`
--
ALTER TABLE `aplikasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `artikel`
--
ALTER TABLE `artikel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `games`
--
ALTER TABLE `games`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `komentar_artikel`
--
ALTER TABLE `komentar_artikel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `komunitas`
--
ALTER TABLE `komunitas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT untuk tabel `komunitas_komentar`
--
ALTER TABLE `komunitas_komentar`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `komunitas_likes`
--
ALTER TABLE `komunitas_likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `Id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `aplikasi`
--
ALTER TABLE `aplikasi`
  ADD CONSTRAINT `fk_aplikasi_pengunggah` FOREIGN KEY (`pengunggah`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `artikel`
--
ALTER TABLE `artikel`
  ADD CONSTRAINT `fk_artikel_pembuat` FOREIGN KEY (`pembuat`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `fk_games_pembuat` FOREIGN KEY (`pembuat`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `komentar_artikel`
--
ALTER TABLE `komentar_artikel`
  ADD CONSTRAINT `fk_komentar_artikel_post` FOREIGN KEY (`post_id`) REFERENCES `artikel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_komentar_artikel_user` FOREIGN KEY (`pembuat`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `komunitas`
--
ALTER TABLE `komunitas`
  ADD CONSTRAINT `fk_komunitas_user` FOREIGN KEY (`pembuat`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `komunitas_komentar`
--
ALTER TABLE `komunitas_komentar`
  ADD CONSTRAINT `fk_komentar_post` FOREIGN KEY (`post_id`) REFERENCES `komunitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_komentar_user` FOREIGN KEY (`pembuat`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `komunitas_likes`
--
ALTER TABLE `komunitas_likes`
  ADD CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`) REFERENCES `komunitas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_likes_user` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `fk_notif_pemicu` FOREIGN KEY (`user_pemicu`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notif_target` FOREIGN KEY (`user_target`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
