<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

$tanggal = date("Y-m-d");
$nama_barang = $_POST['nama_barang'] ?? '';
$jumlah = $_POST['jumlah'] ?? '';
$divisi = $_POST['divisi'] ?? '';
$kode_uker = $_SESSION['kode_uker'] ?? null;

// Validasi input
if (empty($tanggal) || empty($nama_barang) || empty($jumlah) || empty($divisi) || empty($kode_uker)) {
    header("Location: index.php?page=stock-in&status=incomplete");
    exit;
}

// Cek apakah stok barang tersedia di stok_barang
$cek = $conn->prepare("SELECT jumlah FROM stok_barang WHERE nama_barang = ?");
$cek->bind_param("s", $nama_barang);
$cek->execute();
$result = $cek->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stok_sekarang = intval($row['jumlah']);
    $jumlah_keluar = intval($jumlah);

    if ($stok_sekarang >= $jumlah_keluar) {
        // Jalankan transaksi agar lebih aman
        $conn->begin_transaction();

        try {
            // 1. Kurangi stok
            $update = $conn->prepare("UPDATE stok_barang SET jumlah = jumlah - ? WHERE nama_barang = ?");
            $update->bind_param("is", $jumlah_keluar, $nama_barang);
            $update->execute();

            // 2. Catat ke barang_keluar
            $insert = $conn->prepare("INSERT INTO barang_keluar (tanggal, nama_barang, jumlah, divisi, kode_uker) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("sssss", $tanggal, $nama_barang, $jumlah, $divisi, $kode_uker);
            $insert->execute();

            // 3. Commit transaksi jika berhasil
            $conn->commit();
            header("Location: index.php?page=inventory-management&status=success");
        } catch (Exception $e) {
            // Rollback jika ada error
            $conn->rollback();
            header("Location: index.php?page=inventory-management&status=error");
        }
    } else {
        // Stok tidak cukup
        header("Location: index.php?page=inventory-management&status=outstock");
    }
} else {
    // Barang tidak ditemukan di stok_barang
    header("Location: index.php?page=inventory-management&status=notfound");
}

$conn->close();
