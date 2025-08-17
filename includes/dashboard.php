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

$queryRecent = "SELECT * FROM pengajuan WHERE $whereClause ORDER BY updated_at DESC LIMIT 5";

$resultRecent = $conn->query($queryRecent);


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

$dashboardRecent = [
    [
        'heading' => 'Quick Actions',
        'title' => [
            'Pengajuan Baru',
            'Tambah Barang'
        ],
        'link' => 'index.php?page=submission-in'
    ],
    [
        'heading' => 'LogiTrack Update Pengajuan',
        'title' => '',
        'link' => '',
    ]
];
$dashboardStats = [
    [
        'title' => 'Total Pengajuan',
        'result' => $tampung,
        'icon' => 'fa-archive',
        'color' => '',
        'link' => 'index.php?page=submission-out',
    ],
    [
        'title' => $isAdminOrCabang ? 'Pengajuan Masuk' : 'Pengajuan Terkirim',
        'result' => $resultPendingPengajuan,
        'icon' => 'fa-bell-o',
        'color' => 'orange',
        'link' => $isAdminOrCabang ? 'index.php?page=submission-out' : 'index.php?page=mail-out',
    ],
    [
        'title' => 'Barang Keluar',
        'result' => $outstocks,
        'icon' => 'fa-envelope-open-o',
        'color' => '',
        'link' => 'index.php?page=log-stock-out',
    ],
    [
        'title' => 'Barang Masuk',
        'result' => $instocks,
        'icon' => 'fa-envelope-open-o',
        'color' => '',
        'link' => 'index.php?page=log-stock-in',
    ],
];

?>

<div class="dashboard-menu">
    <div class="dashboard-heading" style="font-weight: 800; font-size:32px;">
        Dashboard LogiTrack
    </div>
    <div>Welcome back! Here what's happening with your activity today</div>
    <div class="dashboard-grid">

        <?php foreach ($dashboardStats as $item): ?>
            <?php
            $count = $item['result']->num_rows;
            $icon = $item['icon'];
            $colorClass = $item['color'];
            $title = $item['title'];
            $link = $item['link'];
            ?>
            <a href="<?php echo $link; ?>" class="dashboard-card-link">
                <div class="dashboard-card">
                    <div class="card-contents">
                        <div class="card-left">
                            <div class="dashboard-title"><?php echo $title; ?></div>
                            <div class="dashboard-count"><?php echo $count; ?></div>
                        </div>
                        <div class="card-right">
                            <div class="dashboard-icon <?php echo $colorClass; ?>">
                                <i class="fa <?php echo $icon; ?>"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        Activity
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="dashboard-recent">
        <?php foreach ($dashboardRecent as $item): ?>
            <?php
            $titles = $item['title']; // bisa array atau string
            $heading = $item['heading'];
            $link = $item['link'];
            ?>
            <div class="card-recent">
                <div class="recent-content">
                    <div>
                        <div class="dashboard-title"><?php echo $heading; ?></div>
                        <?php if (is_array($titles)): ?>
                            <?php foreach ($titles as $t): ?>
                                <a href="<?php echo $link; ?>" class="dashboard-title btnRecent"><?php echo $t; ?></a>
                            <?php endforeach; ?>
                        <?php elseif (!empty($titles)): ?>
                            <a href="<?php echo $link; ?>" class="dashboard-title btnRecent"><?php echo $titles; ?></a>
                        <?php else: ?>
                            <div class="">
                                <?php if ($resultRecent->num_rows > 0): ?>
                                    <?php while ($row = $resultRecent->fetch_assoc()): ?>
                                        <div class="recent-update">
                                            <strong><?php echo $row['kode_uker']; ?></strong>
                                            Pengajuan <strong><?php echo $row['kode_pengajuan']; ?></strong>
                                            status <span class="status-<?php echo strtolower($row['status']); ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                            (<?php echo date("d M Y", strtotime($row['updated_at'])); ?>)
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div>Tidak ada update terbaru.</div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>