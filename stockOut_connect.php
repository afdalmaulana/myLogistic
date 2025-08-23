<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

$tanggal = date("Y-m-d") ?? '';
$nama_barang = $_POST['nama_barang'] ?? '';
$jumlah = $_POST['jumlah'] ?? '';
$divisi = $_POST['divisi'] ?? '';
$kode_uker = $_SESSION['kode_uker'] ?? null; // ambil dari session

// Validasi input
if (empty($tanggal) || empty($nama_barang) || empty($jumlah) || empty($divisi) || empty($kode_uker)) {
    header("Location: index.php?page=stock-in&status=incomplete");
    exit;
}

// Insert ke barang_keluar
$sql = "INSERT INTO barang_keluar (tanggal, nama_barang, jumlah, divisi, kode_uker) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $tanggal, $nama_barang, $jumlah, $divisi, $kode_uker);

if ($stmt->execute()) {

    // Cek stok barang di stok_barang
    $cek = $conn->prepare("SELECT jumlah FROM stok_barang WHERE nama_barang = ?");
    $cek->bind_param("s", $nama_barang);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stok_sekarang = intval($row['jumlah']);
        $jumlah_keluar = intval($jumlah);

        if ($stok_sekarang >= $jumlah_keluar) {
            // Kurangi stok
            $update = $conn->prepare("UPDATE stok_barang SET jumlah = jumlah - ? WHERE nama_barang = ?");
            $update->bind_param("is", $jumlah_keluar, $nama_barang);
            $update->execute();

            echo '
            <div class="alert alert-success" style="
                padding: 15px; 
                background-color: #4CAF50; 
                color: white; 
                margin-bottom: 15px; 
                border-radius: 5px;
                font-weight: bold;
            ">
                Data berhasil disimpan dan stok berhasil diperbarui! Mohon tunggu ...
            </div>
            ';
            echo '
            <script>
                setTimeout(function() {
                    window.location.href = "index.php?page=log-stock-out";
                }, 3000);
            </script>
            ';
        } else {
            echo '
            <div class="alert alert-error" style="
                padding: 15px; 
                background-color: #f44336; 
                color: white; 
                margin-bottom: 15px; 
                border-radius: 5px;
                font-weight: bold;
            ">
                Stok tidak mencukupi! Stok saat ini: ' . $stok_sekarang . '
            </div>
            ';
        }
    } else {
        echo '
        <div class="alert alert-error" style="
            padding: 15px; 
            background-color: #f44336; 
            color: white; 
            margin-bottom: 15px; 
            border-radius: 5px;
            font-weight: bold;
        ">
            Barang tidak ditemukan di stok.
        </div>
        ';
    }
} else {
    echo '
    <div class="alert alert-error" style="
        padding: 15px; 
        background-color: #f44336; 
        color: white; 
        margin-bottom: 15px; 
        border-radius: 5px;
        font-weight: bold;
    ">
        Error: ' . htmlspecialchars($stmt->error) . '
    </div>
    ';
}

$stmt->close();
$conn->close();
