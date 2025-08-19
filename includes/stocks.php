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

$queryStock = "SELECT * FROM stok_barang WHERE $whereClause ORDER BY nama_barang ASC";
$stocks = $conn->query($queryStock);

$query = "SELECT * FROM stok_barang ORDER BY nama_barang ASC";
$result = $conn->query($query);
?>


<!-- <div class="mail-count"></div> -->
<!-- <div> <?php echo $stocks->num_rows ?> </div> -->
<!-- <div class="">Jenis Barang yang Tersedia</div> -->

<div class="body-content">
    <div class="sub-menu">
        <p>Daftar Stok Barang</p>
        <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input">
    </div>
    <div class="table-container">
        <table id="dataTable" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($stocks->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td><?= htmlspecialchars($row['jumlah']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="text-align:center;">Belum ada data stok barang</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>