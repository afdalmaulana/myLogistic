<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $merk = $_POST['merk'] ?? null;
    $jumlah = intval($_POST['jumlah'] ?? 1); // default 1 jika tidak dikirim

    if (!$id || !$merk) {
        echo json_encode(['success' => false, 'message' => 'ID atau Merk tidak valid']);
        exit;
    }

    // Cek apakah stok sudah ada untuk merk dan "Stok Gudang"
    $cekQuery = "SELECT * FROM stok_barang_it WHERE merk_komputer = ? AND hostname = 'Stok Gudang'";
    $stmt = $conn->prepare($cekQuery);
    $stmt->bind_param("s", $merk);
    $stmt->execute();
    $result = $stmt->get_result();
    $stokData = $result->fetch_assoc();

    if ($stokData) {
        // Jika ada, update jumlah
        $updateQuery = "UPDATE stok_barang_it SET jumlah = jumlah + ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($updateQuery);
        $stmtUpdate->bind_param("ii", $jumlah, $stokData['id']);
        $stmtUpdate->execute();
    } else {
        // Jika tidak ada, insert data baru
        $insertQuery = "INSERT INTO stok_barang_it (merk_komputer, hostname, serial_number, jumlah) VALUES (?, 'Stok Gudang', '-', ?)";
        $stmtInsert = $conn->prepare($insertQuery);
        $stmtInsert->bind_param("si", $merk, $jumlah);
        $stmtInsert->execute();
    }

    // Hapus dari barangit_keluar
    $deleteQuery = "DELETE FROM barangit_keluar WHERE id = ?";
    $stmtDelete = $conn->prepare($deleteQuery);
    $stmtDelete->bind_param("i", $id);
    $stmtDelete->execute();

    echo json_encode(['success' => true]);
}
