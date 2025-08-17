<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

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

<div class="dashboard-wrapper">
    <div class="mail-in">
        <div class="sub-menu">
            <h4>Log Barang Masuk</h4>
            <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input">
        </div>

        <div class="table-container">
            <table id="dataTable" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th>Nomor Nota</th>
                        <th>Tanggal Input</th>
                        <th>Tanggal Nota</th>
                        <th>Nama Barang</th>
                        <th>Harga Barang Satuan</th>
                        <th>Jumlah</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($stocksIn->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nomor_nota']) ?></td>
                                <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                <td><?= htmlspecialchars($row['tanggal_nota']) ?></td>
                                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($row['harga_barang']) ?></td>
                                <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                <td></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">Belum ada data barang masuk</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
        </div>