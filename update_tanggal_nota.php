<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $tanggal_nota = $_POST['tanggal_nota'] ?? '';

    if (!$id || !$tanggal_nota) {
        http_response_code(400);
        echo 'Data tidak lengkap';
        exit;
    }

    $stmt = $conn->prepare("UPDATE barang_masuk SET tanggal_nota = ? WHERE id = ?");
    $stmt->bind_param("si", $tanggal_nota, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Gagal mengupdate tanggal nota";
    }

    $stmt->close();
    $conn->close();
}
