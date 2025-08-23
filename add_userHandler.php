<?php
session_start();
include 'db_connect.php';

$username = $_POST['username'] ?? '';
$kode_uker = $_POST['kode_uker'] ?? '';
$password = $_POST['password'] ?? '';
$nama_pekerja = $_POST['nama_pekerja'] ?? '';
$role = $_POST['role'] ?? '';
$jabatan = $_POST['jabatan'] ?? '';


if (empty($username) || empty($nama_pekerja) || empty($kode_uker) || empty($password) || empty($role) || empty($jabatan)) {
    header("Location: index.php?page=add-user&status=incomplete");
    exit;
}

// 🔒 CEK APAKAH USERNAME SUDAH ADA
$stmt_check = $conn->prepare("SELECT username FROM users WHERE username = ?");
$stmt_check->bind_param("s", $username);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Username sudah ada
    header("Location: index.php?page=add-user&status=duplicate");
    exit;
}
$stmt_check->close();

// 🔍 Ambil nama_uker dari kode_uker
$stmt2 = $conn->prepare("SELECT nama_uker FROM unit_kerja WHERE kode_uker = ?");
$stmt2->bind_param("s", $kode_uker);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2->num_rows === 0) {
    // Kode uker tidak ditemukan
    header("Location: index.php?page=add-user&status=error");
    exit;
}
$row = $result2->fetch_assoc();
$nama_uker = $row['nama_uker'];
$stmt2->close();

// 🔐 Hash password dan simpan user
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, nama_pekerja, password, role, kode_uker, jabatan) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $username, $nama_pekerja, $hashed_password, $role, $kode_uker, $jabatan);

if ($stmt->execute()) {
    header("Location: index.php?page=add-user&status=success");
} else {
    header("Location: index.php?page=add-user&status=error");
}

$stmt->close();
$conn->close();
exit;
