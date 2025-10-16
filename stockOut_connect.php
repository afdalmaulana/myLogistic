<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

header('Content-Type: application/json'); // Set JSON header

$tanggal = date("Y-m-d");
$nama_barang = $_POST['nama_barang'] ?? '';
$jumlah = $_POST['jumlah'] ?? '';
$divisi = $_POST['divisi'] ?? '';
$kode_uker = $_SESSION['kode_uker'] ?? null;
$satuan = $_POST['satuan'] ?? '';

if (empty($tanggal) || empty($nama_barang) || empty($jumlah) || empty($divisi) || empty($kode_uker)) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

$cek = $conn->prepare("SELECT jumlah FROM stok_barang WHERE nama_barang = ?");
$cek->bind_param("s", $nama_barang);
$cek->execute();
$result = $cek->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stok_sekarang = intval($row['jumlah']);
    $jumlah_keluar = intval($jumlah);

    if ($stok_sekarang >= $jumlah_keluar) {
        $conn->begin_transaction();

        try {
            $update = $conn->prepare("UPDATE stok_barang SET jumlah = jumlah - ? WHERE nama_barang = ?");
            $update->bind_param("is", $jumlah_keluar, $nama_barang);
            $update->execute();

            $insert = $conn->prepare("INSERT INTO barang_keluar (tanggal, nama_barang, jumlah, satuan, divisi, kode_uker) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->bind_param("ssssss", $tanggal, $nama_barang, $jumlah, $satuan, $divisi, $kode_uker);
            $insert->execute();

            $conn->commit();

            echo json_encode(['status' => 'success', 'message' => 'Barang berhasil dikeluarkan']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan pada server']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Stok tidak cukup']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Barang tidak ditemukan']);
}

$conn->close();
exit;
