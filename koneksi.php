<?php
// Informasi koneksi database
$server = "localhost";   // Nama server database (misalnya "localhost" jika dijalankan secara lokal)
$username = "root";  // Nama pengguna MySQL
$password = "";  // Kata sandi MySQL
$database = "sipegbprs_db"; // Nama database yang sudah Anda buat

// Membuat koneksi ke database
$koneksiku = new mysqli($server, $username, $password, $database);

// Memeriksa apakah koneksi berhasil
if ($koneksiku->connect_error) {
    die("Koneksi gagal: " . $koneksiku->connect_error);
}

//echo "Koneksi berhasil!";
?>