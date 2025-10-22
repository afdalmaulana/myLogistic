<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

header('Content-Type: application/json');

$tanggal = date('Y-m-d');
$nama_barang = $_POST['nama_barang'] ?? '';
$jumlah = intval($_POST['jumlah'] ?? 0);
$kode_uker = $_SESSION['kode_uker'] ?? null;
$satuan = $_POST['satuan'] ?? '';

if (empty($nama_barang) || $jumlah <= 0 || empty($kode_uker) || empty($satuan)) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

try {
    // Mulai transaksi untuk menjaga konsistensi data
    $conn->begin_transaction();

    // ✅ Cek apakah barang sudah ada di stok
    $cek = $conn->prepare("SELECT jumlah, satuan FROM stok_barang WHERE nama_barang = ? AND kode_uker = ?");
    $cek->bind_param("ss", $nama_barang, $kode_uker);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        // Barang sudah ada, lakukan pengecekan satuan
        $row = $result->fetch_assoc();

        if ($row['satuan'] !== $satuan) {
            $conn->rollback();
            echo json_encode([
                'status' => 'error',
                'message' => 'Satuan barang berbeda dengan yang sudah ada di stok. Harap pengecekan kembali.'
            ]);
            exit;
        }

        // Satuan cocok, update stok_barang saja
        $update = $conn->prepare("UPDATE stok_barang SET jumlah = jumlah + ? WHERE nama_barang = ? AND kode_uker = ?");
        $update->bind_param("iss", $jumlah, $nama_barang, $kode_uker);
        $update->execute();

        $conn->commit();
        echo json_encode([
            'status' => 'success',
            'message' => 'Stok berhasil diperbarui (tanpa menambah barang_masuk karena barang sudah ada).'
        ]);
    } else {
        // Barang belum ada, insert ke stok dan ke barang_masuk
        $insert_stok = $conn->prepare("INSERT INTO stok_barang (nama_barang, jumlah, satuan, kode_uker) VALUES (?, ?, ?, ?)");
        $insert_stok->bind_param("siss", $nama_barang, $jumlah, $satuan, $kode_uker);
        $insert_stok->execute();

        $insert_masuk = $conn->prepare("INSERT INTO barang_masuk (tanggal, nama_barang, jumlah, satuan, kode_uker) VALUES (?, ?, ?, ?, ?)");
        $insert_masuk->bind_param("ssiss", $tanggal, $nama_barang, $jumlah, $satuan, $kode_uker);
        $insert_masuk->execute();

        $conn->commit();
        echo json_encode([
            'status' => 'success',
            'message' => 'Barang baru berhasil ditambahkan ke stok dan barang_masuk.'
        ]);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan saat menyimpan data.'
    ]);
}

$conn->close();
