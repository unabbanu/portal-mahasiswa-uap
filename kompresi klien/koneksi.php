<?php
// Setelan konfigurasi server database lokal
$host     = "localhost";
$user     = "admin";
$password = "Qwerty1234"; // Kosongkan jika menggunakan XAMPP bawaan
$database = "uap";

// Menjalankan fungsi koneksi mysqli
$koneksi = mysqli_connect($host, $user, $password, $database);

// Memeriksa status keberhasilan jembatan koneksi
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// 2. SETEL WAKTU DATABASE (Jalankan setelah dipastikan koneksi aman)
// Karena menggunakan fungsi murni mysqli_connect (prosedural), gunakan mysqli_query
mysqli_query($koneksi, "SET time_zone = '+07:00'");
?>