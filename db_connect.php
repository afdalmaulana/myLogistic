<?php
$host = "153.92.15.64"; // biasanya tetap 'localhost' di Hostinger, meskipun hosting-nya online
$user = "u420934953_logitrack";
$password = "L0g1_tr4ck!"; // masukkan password database Hostinger kamu
$dbname = "u420934953_logitrack";

// Koneksi ke database Hostinger
$conn = new mysqli($host, $user, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
