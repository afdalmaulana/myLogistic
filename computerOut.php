<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

// Ambil data dari form
$tanggal        = date('Y-m-d'); // Tanggal otomatis hari ini
$merk_komputer  = $_POST['merk_komputer'] ?? '';
$hostname_baru  = $_POST['hostname_baru'] ?? null;
$serial_number  = $_POST['serial_number'] ?? '';
$id_divisi      = $_POST['id_divisi'] ?? '';
$jumlah         = intval($_POST['jumlah'] ?? 0); // pastikan jumlah angka
$kode_uker      = $_POST['kode_uker'] ?? '';


if (empty($hostname_baru)) {
    $hostname_baru = '-';
}
// Validasi sederhana (hostname boleh kosong)
if (empty($merk_komputer) || empty($serial_number) || empty($id_divisi) || $jumlah <= 0 || empty($kode_uker)) {
    header("Location: index.php?page=inventory-It&status=incomplete");
    exit;
}

// CEK STOCK
$cek = $conn->prepare("SELECT jumlah FROM stok_barang_it WHERE merk_komputer = ?");
$cek->bind_param("s", $merk_komputer);
$cek->execute();
$result = $cek->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $stok_sekarang = intval($row['jumlah']);
    $jumlah_keluar = intval($jumlah);

    if ($stok_sekarang >= $jumlah_keluar) {
        $conn->begin_transaction();
        try {
            $update = $conn->prepare("UPDATE stok_barang_it SET jumlah = jumlah - ? WHERE merk_komputer = ?");
            $update->bind_param("is", $jumlah_keluar, $merk_komputer);
            $update->execute();

            // Simpan ke log barang masuk
            $insert = $conn->prepare("INSERT INTO barangit_keluar (tanggal, merk_komputer, hostname_baru, serial_number, id_divisi, jumlah, kode_uker) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert->bind_param("sssssis", $tanggal, $merk_komputer, $hostname_baru, $serial_number, $id_divisi, $jumlah, $kode_uker);
            $insert->execute();

            $conn->commit();
            header("Location: index.php?page=inventory-It&status=success");
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Transaksi gagal: " . $e->getMessage());
            header("Location: index.php?page=inventory-It&status=error");
        }
    } else {
        //Stock Tidak Cukup
        header("Location: index.php?page=inventory-management&status=outstock");
    }
} else {
    header("Location: index.php?page=inventory-management&status=notfound");
}

$conn->close();
