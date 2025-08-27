<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

// Ambil data dari form
$tanggal        = date('Y-m-d'); // Otomatis hari ini
$merk_komputer  = $_POST['merk_komputer'] ?? '';
$hostname       = $_POST['hostname'] ?? null;
$serial_number  = $_POST['serial_number'] ?? '';
$id_divisi      = $_POST['id_divisi'] ?? '';
$kode_uker      = $_POST['kode_uker'] ?? '';

// Validasi sederhana
if (empty($merk_komputer) || empty($serial_number) || empty($id_divisi) || empty($kode_uker)) {
    header("Location: index.php?page=inventory-It&status=incomplete");
    exit;
}

// Simpan ke log barang masuk
$sql = "INSERT INTO barangit_masuk (tanggal, merk_komputer, hostname, serial_number, id_divisi, kode_uker) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $tanggal, $merk_komputer, $hostname, $serial_number, $id_divisi, $kode_uker);

if ($stmt->execute()) {
    // ===== ✅ Update stok jika hostname kosong (barang umum) =====
    if (empty($hostname)) {
        // Cek apakah barang dengan merk dan kode uker sudah ada di stok
        $cek = $conn->prepare("SELECT jumlah FROM stok_barang_it WHERE merk_komputer = ?");
        $cek->bind_param("s", $merk_komputer);
        $cek->execute();
        $result = $cek->get_result();

        if ($result->num_rows > 0) {
            // Sudah ada → update jumlah
            $update = $conn->prepare("UPDATE stok_barang_it SET jumlah = jumlah + 1 WHERE merk_computer = ?");
            $update->bind_param("s", $merk_komputer);
            $update->execute();
        } else {
            // Belum ada → insert stok baru
            $jumlah = 1;
            $insert = $conn->prepare("INSERT INTO stok_barang_it (merk_komputer, hostname, serial_number, jumlah) VALUES (?, ?, ?, ?)");
            $insert->bind_param("sssi", $merk_komputer, $hostname, $serial_number, $jumlah);
            $insert->execute();
        }
    }

    // ===== Sukses =====
    header("Location: index.php?page=inventory-It&status=success");
} else {
    // Gagal
    header("Location: index.php?page=inventory-It&status=error");
}

$stmt->close();
$conn->close();
