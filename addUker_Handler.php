<?php
require 'db_connect.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$kode = $conn->real_escape_string(trim($data['kode'] ?? ''));
$nama = $conn->real_escape_string(trim($data['nama'] ?? ''));

if (!$kode || !$nama) {
    echo json_encode(['success' => false, 'message' => 'Input tidak lengkap']);
    exit;
}

// Cek duplikat
$check = $conn->query("SELECT * FROM unit_kerja WHERE kode_uker = '$kode'");
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Kode uker sudah ada']);
    exit;
}

// Insert
$insert = $conn->query("INSERT INTO unit_kerja (kode_uker, nama_uker) VALUES ('$kode', '$nama')");

if ($insert) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data']);
}
