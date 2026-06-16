<?php
// Setelan konfigurasi server database lokal
$host     = "localhost";
$user     = "root";
$password = ""; // Kosongkan jika menggunakan XAMPP bawaan
$database = "uap";

// Menjalankan fungsi koneksi mysqli
$koneksi = mysqli_connect($host, $user, $password, $database);

// 1. PERIKSA KONEKSI TERLEBIH DAHULU (Pindahkan ke atas)
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// 2. SETEL WAKTU DATABASE (Jalankan setelah dipastikan koneksi aman)
// Karena menggunakan fungsi murni mysqli_connect (prosedural), gunakan mysqli_query
mysqli_query($koneksi, "SET time_zone = '+07:00'");
?>