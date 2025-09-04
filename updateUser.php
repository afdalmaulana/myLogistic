<?php
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $conn->real_escape_string($data['username']);
$nama = $conn->real_escape_string($data['nama']);
$role = $conn->real_escape_string($data['role']);
$jabatan = $conn->real_escape_string($data['jabatan']);
$uker = $conn->real_escape_string($data['uker']);

$query = "UPDATE users SET nama_pekerja='$nama', role='$role', id_jabatan='$jabatan', kode_uker='$uker' WHERE username='$username'";

if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
