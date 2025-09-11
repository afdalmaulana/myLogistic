<?php
require_once 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = (int) $data['id'];
$nama_barang = $conn->real_escape_string($data['nama_barang']);
$jumlah = (int) $data['jumlah'];

$query = "UPDATE stok_barang SET nama_barang='$nama_barang', jumlah='$jumlah' WHERE id='$id'";

if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
