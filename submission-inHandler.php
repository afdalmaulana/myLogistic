<?php
session_start();
include 'db_connect.php';

$kode_pengajuan = $_POST['kode_pengajuan'] ?? '';
$tanggal_pengajuan = $_POST['tanggal_pengajuan'] ?? '';
$perihal = $_POST['perihal'] ?? '';
$kode_uker = $_SESSION['kode_uker'] ?? null; // ambil dari session

// Validasi input
if (empty($kode_pengajuan) || empty($tanggal_pengajuan) || empty($perihal) || empty($kode_uker)) {
    header("Location: index.php?page=submission-in&status=incomplete");
    exit;
}

// Simpan ke database
$sql = "INSERT INTO pengajuan (kode_pengajuan, tanggal_pengajuan, perihal, kode_uker)
        VALUES (?, ?, ?, ?)";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("ssss", $kode_pengajuan, $tanggal_pengajuan, $perihal, $kode_uker);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    header("Location: index.php?page=submission-in&status=success");
    exit;
} catch (Exception $e) {
    // Simpan pesan error supaya bisa tampil di Swal
    $errorMsg = urlencode($e->getMessage());
    header("Location: index.php?page=submission-in&status=error");
    exit;
}
