<?php
require __DIR__ . '/../db_connect.php';

// PROSES HAPUS (ditangani sebelum output HTML)
$successMessage = '';
$errorMessage = '';

$query = "SELECT * FROM barang_keluar ORDER BY tanggal DESC";
$result = $conn->query($query);
?>
<!-- <div class="content-wrapper"> -->
<div class="mail-in">
    <div class="sub-menu">
        <h4>Log Barang Keluar</h4>
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
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
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
<!-- </div> -->