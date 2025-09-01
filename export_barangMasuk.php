<?php
require 'db_connect.php';
session_start();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=barang_masuk.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Cek filter uker
$filterUker = isset($_SESSION['filter_uker']) ? $conn->real_escape_string($_SESSION['filter_uker']) : '';
$isAdminOrCabang = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') || ($_SESSION['kode_uker'] === '0050');

if ($isAdminOrCabang) {
    $whereClause = (!empty($filterUker)) ? "WHERE kode_uker = '$filterUker'" : "";
} else {
    $kode_uker = $conn->real_escape_string($_SESSION['kode_uker']);
    $whereClause = "WHERE kode_uker = '$kode_uker'";
}

$result = $conn->query("SELECT * FROM barang_masuk $whereClause ORDER BY tanggal DESC");

echo "<table border='1'>";
echo "<tr>
        <th>Kode Uker</th>
        <th>Tanggal Input</th>
        <th>Tanggal Nota</th>
        <th>Tanggal Approval</th>
        <th>Nama Barang</th>
        <th>Harga Barang</th>
        <th>Jumlah</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['kode_uker']}</td>
        <td>{$row['tanggal']}</td>
        <td>{$row['tanggal_nota']}</td>
        <td>{$row['tanggal_approve']}</td>
        <td>{$row['nama_barang']}</td>
        <td>{$row['price']}</td>
        <td>{$row['jumlah']}</td>
    </tr>";
}
echo "</table>";
