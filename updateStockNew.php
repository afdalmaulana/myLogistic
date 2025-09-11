<?php
require_once 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$nama_barang = $conn->real_escape_string($data['nama_barang']);
$jumlah = $conn->real_escape_string($data['jumlah']);

$query = "UPDATE stok_barang SET nama_barang='$nama_barang', jumlah='$jumlah' WHERE nama_barang='$nama_barang'";

if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
