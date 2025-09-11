<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

// Ambil data JSON dari fetch()
$data = json_decode(file_get_contents("php://input"), true);

// Validasi input
if (!isset($data['id'], $data['nama_barang'], $data['jumlah'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$id = (int) $data['id'];
$nama_barang = $conn->real_escape_string($data['nama_barang']);
$jumlah = (int) $data['jumlah'];

// Jalankan query update
$query = "UPDATE stok_barang SET nama_barang='$nama_barang', jumlah=$jumlah WHERE id=$id";

if ($conn->query($query)) {
    if ($conn->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak berubah']);
    }
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
