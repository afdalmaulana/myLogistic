<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../db_connect.php';

$isAdminOrCabang = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ||
    (isset($_SESSION['kode_uker']) && $_SESSION['kode_uker'] === '0050');

if ($isAdminOrCabang) {
    // Admin atau Kanwil melihat semua data
    $whereClause = "1"; // tidak ada filter
} else {
    // Selain itu, hanya melihat data berdasarkan kode_uker
    $kode_uker = $conn->real_escape_string($_SESSION['kode_uker']);
    $whereClause = "kode_uker = '$kode_uker'";
}

// PROSES HAPUS (ditangani sebelum output HTML)
$successMessage = '';
$errorMessage = '';

$query = "SELECT * FROM barang_masuk WHERE $whereClause ORDER BY nama_barang DESC";
$stocksIn = $conn->query($query);

$query = "SELECT * FROM barang_masuk ORDER BY tanggal DESC";
$result = $conn->query($query)
?>



<div class="content-wrapper">
    <div class="content-heading">Log Inventory Management</div>
    <div>Track incoming, and outgoing inventory</div>
    <div class="button-invent-group">
        <button onclick="loadLog('log-stock-in', this)">Log Barang Masuk</button>
        <button onclick="loadLog('log-stock-out', this)">Log Barang Keluar</button>
    </div>

    <div id="content-area">
        <?php include 'includes/log-stock-in.php'; ?>
    </div>

    <div id="loading-indicator" style="display: none;">
        <div class="spinner"></div>
    </div>
</div>