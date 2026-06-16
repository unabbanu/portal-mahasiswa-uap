-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 05 Jun 2026 pada 18.55
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uap`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `aplikasi`
--

CREATE TABLE `aplikasi` (
  `id` int(11) NOT NULL,
  `nama_aplikasi` varchar(255) NOT NULL,
  `developer` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `deskripsi` text NOT NULL,
  `ikon` varchar(255) NOT NULL,
  `link_playstore` text NOT NULL,
  `pengunggah` varchar(50) NOT NULL,
  `tanggal_upload` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `aplikasi`
--

INSERT INTO `aplikasi` (`id`, `nama_aplikasi`, `developer`, `kategori`, `deskripsi`, `ikon`, `link_playstore`, `pengunggah`, `tanggal_upload`) VALUES
(6, 'ok', 'dsa', 'Productivity', 'sdasd', 'app_1780041461_867.jpg', 'http://localhost/uap/tambah_aplikasi.php', 'tes', '2026-05-29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `artikel`
--

CREATE TABLE `artikel` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `konten` text NOT NULL,
  `status` enum('Publish','Draft') DEFAULT 'Publish',
  `pembuat` varchar(50) NOT NULL,
  `tanggal` date NOT NULL,
  `file_pdf` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `artikel`
--

INSERT INTO `artikel` (`id`, `judul`, `kategori`, `gambar`, `konten`, `status`, `pembuat`, `tanggal`, `file_pdf`, `created_at`) VALUES
(16, 'fdfds', 'Kegiatan', 'uploads/artikel/art_1780042087_352.jpg', 'sdsdada', 'Publish', 'tes', '2026-05-29', 'uploads/dokumen/doc_1780042087_545.pdf', '2026-05-29 08:08:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `genre` varchar(50) NOT NULL,
  `deskripsi` text NOT NULL,
  `banner` varchar(255) NOT NULL,
  `folder_game` varchar(255) NOT NULL,
  `pembuat` varchar(50) NOT NULL,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `games`
--

INSERT INTO `games` (`id`, `judul`, `genre`, `deskripsi`, `banner`, `folder_game`, `pembuat`, `tanggal`) VALUES
(6, 'fffdsfsd', 'Puzzle', 'faffsf', 'game_1780041875_666.png', 'fffdsfsd_1780041766', 'tes', '2026-05-29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `komentar_artikel`
--

CREATE TABLE `komentar_artikel` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `pembuat` varchar(50) NOT NULL,
  `komentar` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `komentar_artikel`
--

INSERT INTO `komentar_artikel` (`id`, `post_id`, `pembuat`, `komentar`, `created_at`) VALUES
(1, 16, 'tes', 'ooooowwwww', '2026-06-05 13:27:44');

-- --------------------------------------------------------

--
-- Struktur dari tabel `komunitas`
--

CREATE TABLE `komunitas` (
  `id` int(11) NOT NULL,
  `pembuat` varchar(50) NOT NULL,
  `konten` text NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `komunitas`
--

INSERT INTO `komunitas` (`id`, `pembuat`, `konten`, `gambar`, `video`, `created_at`) VALUES
(41, 'tes', '', NULL, 'uploads/komunitas/post_vid_1780505421_200.mp4', '2026-06-03 16:50:21'),
(42, 'tes', '', 'uploads/komunitas/post_img_1780505441_379.mp4', NULL, '2026-06-03 16:50:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `komunitas_komentar`
--

CREATE TABLE `komunitas_komentar` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `pembuat` varchar(50) NOT NULL,
  `komentar` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `komunitas_komentar`
--

INSERT INTO `komunitas_komentar` (`id`, `post_id`, `pembuat`, `komentar`, `created_at`) VALUES
(8, 42, 'admin', 'yang depan petama okee, yang depan kedua okee, itu cowok yang paling ujung siluman kah wkwkwk', '2026-06-05 15:13:19'),
(10, 41, 'admin', 'Find, install and publish Python packages with the Python Package Index', '2026-06-05 15:52:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `komunitas_likes`
--

CREATE TABLE `komunitas_likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `komunitas_likes`
--

INSERT INTO `komunitas_likes` (`id`, `post_id`, `username`, `created_at`) VALUES
(22, 42, 'admin', '2026-06-05 15:12:03'),
(24, 41, 'admin', '2026-06-05 15:52:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `Id` int(11) NOT NULL,
  `user_target` varchar(50) NOT NULL,
  `user_pemicu` varchar(50) NOT NULL,
  `tipe` enum('komen_artikel','komen_sosial','like_sosial') NOT NULL,
  `id_sumber` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`Id`, `user_target`, `user_pemicu`, `tipe`, `id_sumber`, `is_read`, `created_at`) VALUES
(1, 'tes', 'admin', 'like_sosial', 42, 1, '2026-06-05 15:12:03'),
(2, 'tes', 'admin', 'komen_sosial', 42, 1, '2026-06-05 15:13:19'),
(3, 'tes', 'admin', 'like_sosial', 41, 1, '2026-06-05 15:52:45'),
(4, 'tes', 'admin', 'komen_sosial', 41, 1, '2026-06-05 15:52:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `avatar`, `created_at`) VALUES
(5, 'admin', '$2y$10$TO8b98htVmKfF1aO.2ZAj.fNoJw0doMingutj9nKunPlp4iJ1bTva', 'uploads/profil/avatar_admin_1780063102.jpg', '2026-05-20 07:34:11'),
(12, 'tes', '$2y$10$JNU5vL/nD7lefgW.RDTO8.gRZtHE3wZCMeB1OHeLv1a2xUJkx9MTW', 'uploads/profil/avatar_tes_1780514016.jpg', '2026-05-29 07:54:13'),
(14, 'ees', '$2y$10$CiebgCl8u0KUqdbj8dEUAepqAoWrI73877OGnKNEFvcZG/HcsLaGy', 'uploads/profil/default.jpg', '2026-06-03 05:04:47'),
(15, 'eet', '$2y$10$NLZA9IUIwcCGblsKAJmwQeEWXaX5/8WjdWu5B/kC.W.4rlGTAyjzy', 'uploads/profil/default.jpg', '2026-06-03 05:04:52');

--
-- Indexes for dumped tables
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `artikel`
--
ALTER TABLE `artikel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `komentar_artikel`
--
ALTER TABLE `komentar_artikel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `komunitas`
--
ALTER TABLE `komunitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT untuk tabel `komunitas_komentar`
--
ALTER TABLE `komunitas_komentar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `komunitas_likes`
--
ALTER TABLE `komunitas_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  ADD CONSTRAINT `fk_artikel_pembuat` FOREIGN KEY (`pembuat`) REFERENCES `user` (`username`) ON UPDATE CASCADE;

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
