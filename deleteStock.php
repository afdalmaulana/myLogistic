<?php
require 'db_connect.php';
session_start();

$allowedRoles = ['admin', 'user'];

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}


$id = $_POST['id'] ?? '';

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo "Bad Request";
    exit;
}

$stmt = $conn->prepare("DELETE FROM stok_barang WHERE id = ?");
$stmt->bind_param("i", $id);
header('Content-Type: application/json');

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete']);
}
