<?php
session_start();
include 'db_connect.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi input kosong
if (empty($username) || empty($password)) {
    header("Location: signin.php?status=incomplete");
    exit;
}

// Ambil data user dari DB
$query = $conn->prepare("SELECT * FROM users WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Cocokkan password polos
    if (password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['nama_uker'] = $user['nama_uker'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['kode_uker'] = $user['kode_uker'];
        header("Location: index.php");
        exit;
    }
}

// Jika gagal login
header("Location: signin.php?status=error");
exit;
