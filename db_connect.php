<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "db_dashboard";

$conn = new mysqli('localhost', 'root', '', 'db_dashboard', null, '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock');
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
