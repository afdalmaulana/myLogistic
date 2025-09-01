<?php
require 'db_connect.php';
session_start();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=barang_keluar.xls");
header("Pragma: no-cache");
header("Expires: 0");

$result = $conn->query("SELECT * FROM barang_keluar ORDER BY tanggal DESC");

echo "<table border='1'>";
echo "<tr>
        <th>Tanggal</th>
        <th>Nama Barang</th>
        <th>Jumlah</th>
        <th>Divisi</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['tanggal']}</td>
        <td>{$row['nama_barang']}</td>
        <td>{$row['jumlah']}</td>
        <td>{$row['divisi']}</td>
    </tr>";
}
echo "</table>";
