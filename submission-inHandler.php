<?php
session_start();
include 'db_connect.php';

$tanggal_pengajuan = $_POST['tanggal_pengajuan'] ?? '';
$kode_pengajuan = $_POST['kode_pengajuan'] ?? '';
$nama_barang = $_POST['nama_barang'] ?? '';
$jumlah = intval($_POST['jumlah'] ?? 0); // pastikan jumlah angka
$harga_barang = intval($_POST['harga_barang'] ?? 0);
$kode_uker = $_SESSION['kode_uker'] ?? null; // ambil dari session

// Validasi input
if (empty($kode_pengajuan) || empty($tanggal_pengajuan) || empty($nama_barang) || empty($jumlah) || empty($harga_barang) || empty($kode_uker)) {
    header("Location: index.php?page=submission-in&status=incomplete");
    exit;
}

// Simpan ke database
$sql = "INSERT INTO pengajuan (kode_pengajuan, tanggal_pengajuan, nama_barang,jumlah, harga_barang, kode_uker)
        VALUES (?, ?, ?, ?, ?, ?)";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception($conn->error);
    $stmt->bind_param("sssiis", $kode_pengajuan, $tanggal_pengajuan, $nama_barang, $jumlah, $harga_barang, $kode_uker);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    header("Location: index.php?page=submission-in&status=success");
    exit;
} catch (Exception $e) {
    $errorMsg = $e->getMessage();

    // Tangani error jika kode_pengajuan sudah ada (Duplicate entry)
    if (strpos($errorMsg, 'Duplicate entry') !== false) {
        header("Location: index.php?page=submission-in&status=duplicate");
        exit;
    }

    // Error lain
    header("Location: index.php?page=submission-in&status=error");
    exit;
}
