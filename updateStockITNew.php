<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

// Ambil data JSON dari fetch()
$data = json_decode(file_get_contents("php://input"), true);

// Validasi input
if (!isset($data['id'], $data['merk_komputer'], $data['hostname'], $data['serial_number'], $data['jumlah'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$id = (int) $data['id'];
$merk_komputer = $conn->real_escape_string($data['merk_komputer']);
$hostname = $conn->real_escape_string($data['hostname']);
$serial_number = $conn->real_escape_string($data['serial_number']);
$jumlah = (int) $data['jumlah'];

// Jalankan query update
$query = "UPDATE stok_barang_it SET merk_komputer='$merk_komputer', hostname='$hostname', serial_number='$serial_number', jumlah=$jumlah WHERE id=$id";

if ($conn->query($query)) {
    if ($conn->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak berubah']);
    }
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
