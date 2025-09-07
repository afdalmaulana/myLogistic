<?php
require 'db_connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Unauthorized";
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

if ($stmt->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "Failed to delete";
}
?>
