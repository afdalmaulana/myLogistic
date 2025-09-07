<?php
require 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$id = $_POST['id'] ?? '';
$tabel = $_POST['table'] ?? '';

if (!$id || !in_array($tabel, ['barang_masuk', 'barang_keluar'])) {
    http_response_code(400);
    echo "Bad Request";
    exit;
}

$stmt = $conn->prepare("DELETE FROM $tabel WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "Failed to delete";
}
