<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

// Ambil data dari form
$tanggal        = date('Y-m-d'); // Tanggal otomatis hari ini
$merk_komputer  = $_POST['merk_komputer'] ?? '';
$hostname       = $_POST['hostname'] ?? null;
$serial_number  = $_POST['serial_number'] ?? '';
$id_divisi      = $_POST['id_divisi'] ?? '';
$kode_uker      = $_POST['kode_uker'] ?? '';
$jumlah         = intval($_POST['jumlah'] ?? 0); // pastikan jumlah angka

// Validasi sederhana (hostname boleh kosong)
if (empty($merk_komputer) || empty($serial_number) || empty($id_divisi) || empty($kode_uker) || $jumlah <= 0) {
    header("Location: index.php?page=inventory-It&status=incomplete");
    exit;
}

// Simpan ke log barang masuk
$sql = "INSERT INTO barangit_masuk (tanggal, merk_komputer, hostname, serial_number, id_divisi, kode_uker)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $tanggal, $merk_komputer, $hostname, $serial_number, $id_divisi, $kode_uker);

if ($stmt->execute()) {
    // ===== Cek apakah kombinasi merk_komputer + serial_number sudah ada =====
    $cek = $conn->prepare("SELECT jumlah FROM stok_barang_it WHERE merk_komputer = ? AND serial_number = ?");
    $cek->bind_param("ss", $merk_komputer, $serial_number);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        // Jika sudah ada → update jumlah
        $update = $conn->prepare("UPDATE stok_barang_it SET jumlah = jumlah + ? WHERE merk_komputer = ? AND serial_number = ?");
        $update->bind_param("iss", $jumlah, $merk_komputer, $serial_number);
        $update->execute();
    } else {
        // Jika belum ada → insert stok baru
        $insert = $conn->prepare("INSERT INTO stok_barang_it (merk_komputer, hostname, serial_number, jumlah)
                                  VALUES (?, ?, ?, ?)");
        $insert->bind_param("sssi", $merk_komputer, $hostname, $serial_number, $jumlah);
        $insert->execute();
    }

    header("Location: index.php?page=inventory-It&status=success");
} else {
    header("Location: index.php?page=inventory-It&status=error");
}


$stmt->close();
$conn->close();
