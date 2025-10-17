<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

header('Content-Type: application/json'); // ✅ Kirim JSON response

$tanggal = date('Y-m-d');
$nama_barang = $_POST['nama_barang'] ?? '';
$jumlah = intval($_POST['jumlah'] ?? 0);
$kode_uker = $_SESSION['kode_uker'] ?? null;
$satuan = $_POST['satuan'] ?? '';

if (empty($nama_barang) || $jumlah <= 0 || empty($kode_uker)) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

// Simpan ke tabel barang_masuk
$sql = "INSERT INTO barang_masuk (tanggal, nama_barang, jumlah, satuan, kode_uker) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiss", $tanggal, $nama_barang, $jumlah, $satuan, $kode_uker);

if ($stmt->execute()) {
    // Cek stok_barang
    $cek = $conn->prepare("SELECT jumlah FROM stok_barang WHERE nama_barang = ? AND kode_uker = ?");
    $cek->bind_param("ss", $nama_barang, $kode_uker);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        $update = $conn->prepare("UPDATE stok_barang SET jumlah = jumlah + ? WHERE nama_barang = ? AND kode_uker = ?");
        $update->bind_param("iss", $jumlah, $nama_barang, $kode_uker);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO stok_barang (nama_barang, jumlah, satuan, kode_uker) VALUES (?, ?, ?, ?)");
        $insert->bind_param("siss", $nama_barang, $jumlah, $satuan, $kode_uker);
        $insert->execute();
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Stok berhasil ditambahkan!'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menambahkan ke database.'
    ]);
}

$stmt->close();
$conn->close();
