<?php
require_once 'db_connect.php';
session_start();

// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=request_pengajuan_approved.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Role dan akses pengguna
$role = $_SESSION['role'] ?? '';
$user = $_SESSION['user'] ?? '';
$kodeUker = $_SESSION['kode_uker'] ?? '';
$idJabatan = $_SESSION['id_jabatan'] ?? '';

$isAdmin = $role === 'admin';
$isKanwil = $idJabatan === 'JB3';
$pnLogistikSudirman = $user === '00344250';
$pnLogistikAyani = $user === '00203119';
$pnLogistikTamalanrea = $user === '00220631';
$isSudirmanAccess = in_array($user, ['00068898', '00031021']);
$isAyaniAccess = in_array($user, ['00008839', '00030413']);
$isTamalanreaAccess = in_array($user, ['00028145', '00062209']);

$isLogistikSudirman = $pnLogistikSudirman || $isSudirmanAccess;
$isLogistikAhmadYani = $pnLogistikAyani || $isAyaniAccess;
$isLogistikTamalanrea = $pnLogistikTamalanrea || $isTamalanreaAccess;

// Daftar kode uker per grup logistik
$sudirmanCodes = ['0334', '1548', '3050', '3411', '3581', '3582', '3810', '3811', '3815', '3816', '3819', '3821', '3822', '3825', '4986', '7016', '7077'];
$ahmadYaniCodes = ['0050', '1074', '0664', '2086', '2051', '2054', '1436'];
$tamalanreaCodes = ['0403', '7442', '4987', '3823', '3818', '3806', '3419', '3057', '2085', '1831', '1814', '1709', '1554'];

// Query dasar dengan JOIN ke tabel anggaran
$baseQuery = "
    SELECT p.*, a.nama_anggaran
    FROM pengajuan p
    LEFT JOIN anggaran a ON p.id_anggaran = a.id_anggaran
    WHERE (LOWER(p.status) = 'approved' OR LOWER(p.status) = 'rejected')
";

// Tambahkan filter berdasarkan role
if ($isAdmin || $isKanwil) {
    // Admin dan Kanwil lihat semua data
    // Tidak ada filter tambahan
} elseif ($isLogistikSudirman) {
    $allowed = "'" . implode("','", $sudirmanCodes) . "'";
    $baseQuery .= " AND p.kode_uker IN ($allowed)";
} elseif ($isLogistikAhmadYani) {
    $allowed = "'" . implode("','", $ahmadYaniCodes) . "'";
    $baseQuery .= " AND p.kode_uker IN ($allowed)";
} elseif ($isLogistikTamalanrea) {
    $allowed = "'" . implode("','", $tamalanreaCodes) . "'";
    $baseQuery .= " AND p.kode_uker IN ($allowed)";
} else {
    // User biasa hanya lihat pengajuan unitnya sendiri
    $kodeUkerEscaped = $conn->real_escape_string($kodeUker);
    $baseQuery .= " AND p.kode_uker = '$kodeUkerEscaped'";
}

// Tambahkan urutan
$baseQuery .= " ORDER BY p.tanggal_pengajuan DESC";

// Eksekusi query
$result = $conn->query($baseQuery);

// Output Excel table
echo "<table border='1'>";
echo "<tr>
        <th>ID</th>
        <th>Kode Pengajuan</th>
        <th>Kode Uker</th>
        <th>Tanggal Pengajuan</th>
        <th>Nama Barang</th>
        <th>Status</th>
        <th>Jumlah</th>
        <th>Satuan</th>
        <th>Nama Anggaran</th>
        <th>Keterangan</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['kode_pengajuan']}</td>
        <td>{$row['kode_uker']}</td>
        <td>{$row['tanggal_pengajuan']}</td>
        <td>{$row['nama_barang']}</td>
        <td>{$row['status']}</td>
        <td>{$row['jumlah']}</td>
        <td>{$row['satuan']}</td>
        <td>{$row['nama_anggaran']}</td>
        <td>{$row['keterangan']}</td>
    </tr>";
}
echo "</table>";
