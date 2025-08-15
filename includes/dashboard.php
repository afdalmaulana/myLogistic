<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

// Cek role
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

// Gunakan $whereClause untuk semua query pengajuan
$queryAll = "SELECT * FROM pengajuan WHERE $whereClause ORDER BY kode_pengajuan DESC";
$tampung = $conn->query($queryAll);

$queryPending = "SELECT * FROM pengajuan WHERE $whereClause AND status = 'Pending'";
$resultPendingPengajuan = $conn->query($queryPending);

$queryApproved = "SELECT * FROM pengajuan WHERE $whereClause AND status = 'Approved'";
$approvedPengajuan = $conn->query($queryApproved);

$queryForward = "SELECT * FROM pengajuan WHERE $whereClause AND status = 'Forward'";
$forwardPengajuan = $conn->query($queryForward);

$queryRejected = "SELECT * FROM pengajuan WHERE $whereClause AND status = 'Rejected'";
$rejectedPengajuan = $conn->query($queryRejected);

// Query lainnya (surat keluar, stok) tidak perlu difilter
// $query = "SELECT * FROM surat_keluar ORDER BY pengirim DESC";
// $resultSuratKeluar = $conn->query($query);
$query = "SELECT * FROM stok_barang WHERE $whereClause ORDER BY nama_barang ASC";
$stocks = $conn->query($query);

$query = "SELECT * FROM barang_masuk WHERE $whereClause ORDER BY nama_barang ASC";
$instocks = $conn->query($query);

$query = "SELECT * FROM barang_keluar WHERE $whereClause ORDER BY nama_barang ASC";
$outstocks = $conn->query($query);
$dashboardStats = [
    [
        'title' => 'Semua Pengajuan',
        'result' => $tampung,
        'icon' => 'fa-archive',
        'color' => '',
        'link' => 'index.php?page=submission-out',
    ],
    [
        'title' => 'Pengajuan Approved',
        'result' => $approvedPengajuan,
        'icon' => 'fa-envelope-open-o',
        'color' => '',
        'link' => 'index.php?page=submission-out',
    ],
    [
        'title' => $isAdminOrCabang ? 'Jumlah Pengajuan Masuk' : 'Pengajuan Terkirim',
        'result' => $resultPendingPengajuan,
        'icon' => 'fa-bell-o',
        'color' => 'orange',
        'link' => $isAdminOrCabang ? 'index.php?page=submission-out' : 'index.php?page=mail-out',
    ],
    [
        'title' => 'Pengajuan Rejected',
        'result' => $rejectedPengajuan,
        'icon' => 'fa-minus-circle',
        'color' => 'red',
        'link' => 'index.php?page=submission-out',
    ],
    [
        'title' => 'Pengajuan Forward',
        'result' => $forwardPengajuan,
        'icon' => 'fa-envelope-open-o',
        'color' => '',
        'link' => 'index.php?page=submission-out',
    ],
    [
        'title' => 'Stock Barang',
        'result' => $stocks,
        'icon' => 'fa-envelope-open-o',
        'color' => '',
        'link' => 'index.php?page=submission-out',
    ],
    [
        'title' => 'Barang Keluar',
        'result' => $outstocks,
        'icon' => 'fa-envelope-open-o',
        'color' => '',
        'link' => 'index.php?page=submission-out',
    ],
    [
        'title' => 'Barang Masuk',
        'result' => $instocks,
        'icon' => 'fa-envelope-open-o',
        'color' => '',
        'link' => 'index.php?page=submission-out',
    ],
];

?>

<div class="dashboard-grid">
    <?php foreach ($dashboardStats as $item): ?>
        <?php
        $count = $item['result']->num_rows;
        $icon = $item['icon'];
        $colorClass = $item['color'];
        $title = $item['title'];
        $link = $item['link'];
        ?>
        <div class="dashboard-card">
            <div class="dashboard-icon <?php echo $colorClass; ?>">
                <i class="fa <?php echo $icon; ?>"></i>
            </div>
            <div class="dashboard-count"><?php echo $count; ?></div>
            <div class="dashboard-title"><?php echo $title; ?></div>
            <div class="dashboard-link">
                <a href="<?php echo $link; ?>">Lihat Semua</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>