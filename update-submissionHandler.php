<?php
require 'db_connect.php';

$kode_pengajuan = $_POST['kode_pengajuan'] ?? '';
$status = $_POST['status'] ?? '';

// Validasi kode_pengajuan
if (!$kode_pengajuan) {
    http_response_code(400);
    echo "Kode pengajuan tidak valid.";
    exit;
}

// Proses hapus
if ($status === 'delete') {
    $stmt = $conn->prepare("DELETE FROM pengajuan WHERE kode_pengajuan = ?");
    $stmt->bind_param("s", $kode_pengajuan);
    if ($stmt->execute()) {
        echo "Pengajuan berhasil dihapus.";
    } else {
        http_response_code(500);
        echo "Gagal menghapus pengajuan.";
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Validasi status yang diperbolehkan
$allowedStatuses = ['pending', 'forward', 'approved', 'rejected'];
if (!in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo "Status tidak valid.";
    exit;
}

// Update status
$stmt = $conn->prepare("UPDATE pengajuan SET status = ? WHERE kode_pengajuan = ?");
$stmt->bind_param("ss", $status, $kode_pengajuan);

if ($stmt->execute()) {
    echo "Status berhasil diperbarui menjadi " . ucfirst($status) . ".";
} else {
    http_response_code(500);
    echo "Gagal memperbarui status.";
}

$stmt->close();
$conn->close();
