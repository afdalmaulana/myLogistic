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

$query = "SELECT * FROM barang_masuk WHERE $whereClause ORDER BY nama_barang DESC";
$stocksIn = $conn->query($query);

$query = "SELECT * FROM barang_masuk ORDER BY tanggal DESC";
$result = $conn->query($query);

$query = "SELECT * FROM barang_keluar ORDER BY tanggal DESC";
$resultOut = $conn->query($query);
?>


<div class="content-wrappers">
    <div class="content-heading">Log Inventory Management</div>
    <div>Track incoming, and outgoing inventory</div>
    <div class="tab">
        <button class="tablinks active" onclick="openCity(event, 'barang_masuk')">STOCK IN</button>
        <button class="tablinks" onclick="openCity(event, 'barang_keluar')">STOCK OUT</button>
    </div>

    <div id="barang_masuk" class="tabcontent" style="display: block;">
        <div class="body-content">
            <div class="sub-menu">
                <p>Log Record</p>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input">
            </div>

            <div class="table-container">
                <table id="dataTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Kode Uker</th>
                            <th>Nomor Nota</th>
                            <th>Tanggal Input</th>
                            <th>Tanggal Nota</th>
                            <th>Tanggal Approval</th>
                            <th>Nama Barang</th>
                            <th>Harga Barang Satuan</th>
                            <th>Jumlah</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stocksIn->num_rows > 0): ?>
                            <?php while ($row = $stocksIn->fetch_assoc()): ?>
                                <tr>
                                <tr>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['nomor_nota']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal']) ?></td>

                                    <?php if ($row['tanggal_nota'] === null): ?>
                                        <td>
                                            Input Tanggal Nota
                                            <button style="background: none; border: none" class="btn-edit-nota"
                                                data-id="<?= $row['id'] ?>"
                                                data-current="<?= $row['tanggal_nota'] ?>">
                                                <i class="fa fa-edit" style="font-size:16px;color:red"></i>
                                            </button>
                                        </td>
                                    <?php else: ?>
                                        <td><?= htmlspecialchars($row['tanggal_nota']) ?></td>
                                    <?php endif; ?>

                                    <td><?= htmlspecialchars($row['tanggal_approve']) ?></td>
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
                </table>
            </div>
        </div>
    </div>

    <div id="barang_keluar" class="tabcontent">
        <div class="body-content">
            <div class="sub-menu">
                <p>Log Record</p>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input">
            </div>

            <div class="table-container">
                <table id="dataTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Divisi</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($resultOut->num_rows > 0) {
                            while ($row = $resultOut->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['tanggal']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_barang']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['jumlah']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['divisi']) . "</td>";
                                echo "<td></td>"; // kolom kosong terakhir
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr><td colspan="5" style="text-align:center;">Belum ada data barang keluar</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>