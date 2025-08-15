<?php
session_start();
include 'db_connect.php';

// Ambil data form
$tanggal = $_POST['tanggal'] ?? '';
$tanggal_nota = $_POST['tanggal_nota'] ?? '';
$nomor_nota = $_POST['nomor_nota'] ?? '';
$nama_barang = $_POST['nama_barang'] ?? '';
$harga_barang = $_POST['harga_barang'] ?? '';
$jumlah = intval($_POST['jumlah'] ?? 0); // pastikan jumlah angka
$kode_uker = $_SESSION['kode_uker'] ?? null;
// Validasi sederhana
if (empty($tanggal) || empty($tanggal_nota) || empty($nomor_nota) || empty($nama_barang) || empty($harga_barang) || $jumlah <= 0 || empty($kode_uker)) {
    header("Location: index.php?page=stock-in&status=incomplete");
    exit;
}

// Simpan ke tabel barang_masuk
$sql = "INSERT INTO barang_masuk (tanggal, tanggal_nota, nomor_nota, nama_barang, harga_barang, jumlah, kode_uker) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssis", $tanggal, $tanggal_nota, $nomor_nota, $nama_barang, $harga_barang, $jumlah, $kode_uker);

if ($stmt->execute()) {

    // ==== ✅ Update stok_barang ====
    $cek = $conn->prepare("SELECT jumlah FROM stok_barang WHERE nama_barang = ?");
    $cek->bind_param("s", $nama_barang);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        // Barang sudah ada → update jumlah
        $update = $conn->prepare("UPDATE stok_barang SET jumlah = jumlah + ? WHERE nama_barang = ?");
        $update->bind_param("iss", $jumlah, $nama_barang, $kode_uker);
        $update->execute();
    } else {
        // Barang belum ada → insert baru
        $insert = $conn->prepare("INSERT INTO stok_barang (nama_barang, jumlah, kode_uker) VALUES (?, ?, ?)");
        $insert->bind_param("sis", $nama_barang, $jumlah, $kode_uker);
        $insert->execute();
    }
    // ==== END update stok_barang ====

    // Tampilkan pesan sukses
    header("Location: index.php?page=stock-in&status=success");;
} else {
    header("Location: index.php?page=stock-in&status=error");
}

$stmt->close();
$conn->close();
