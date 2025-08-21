<?php
session_start();
include 'db_connect.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header("Location: signin.php?status=incomplete");
    exit;
}

$query = $conn->prepare("SELECT * FROM users WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['nama_uker'] = $user['nama_uker'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['kode_uker'] = $user['kode_uker'];
        $_SESSION['nama_pekerja'] = $user['nama_pekerja'];

        // ✅ TANDAI LOGIN BERHASIL DENGAN SESSION
        $_SESSION['login_success'] = true;

        header("Location: index.php");
        exit;
    }
}

header("Location: signin.php?status=error");
exit;
