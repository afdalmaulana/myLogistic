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

$isKanwil = isset($_SESSION['id_jabatan']) && ($_SESSION['id_jabatan'] === 'JB3');

$queryRecent = "SELECT * FROM pengajuan WHERE $whereClause ORDER BY updated_at DESC LIMIT 5";

$resultRecent = $conn->query($queryRecent);


// Gunakan $whereClause untuk semua query pengajuan
$queryAll = "SELECT * FROM pengajuan WHERE $whereClause ORDER BY kode_pengajuan DESC";
$tampung = $conn->query($queryAll);

$queryPending = "SELECT * FROM pengajuan WHERE $whereClause AND status = 'Pending'";
$resultPendingPengajuan = $conn->query($queryPending);

$queryApproved = "SELECT * FROM pengajuan WHERE $whereClause AND status = 'Approved' AND status_sisa = 'done'";
$approvedPengajuan = $conn->query($queryApproved);

$queryForward = "SELECT * FROM pengajuan WHERE $whereClause AND status = 'Forward'";
$forwardPengajuan = $conn->query($queryForward);

$queryRejected = "SELECT * FROM pengajuan WHERE $whereClause AND status = 'Rejected'";
$rejectedPengajuan = $conn->query($queryRejected);

$query = "SELECT * FROM stok_barang WHERE $whereClause ORDER BY nama_barang ASC";
$stocks = $conn->query($query);

$query = "SELECT * FROM barang_masuk WHERE $whereClause ORDER BY nama_barang ASC";
$instocks = $conn->query($query);

$query = "SELECT * FROM barang_keluar WHERE $whereClause ORDER BY nama_barang ASC";
$outstocks = $conn->query($query);

$dashboardRecent = [
    [
        'heading' => 'Quick Actions',
        'actions' => [
            ['title' => 'New Submission', 'link' => 'index.php?page=submission-in'],
            ['title' => 'Add New Item', 'link' => 'index.php?page=inventory-management'],
        ]
    ],
    [
        'heading' => 'LogiTrack Update Pengajuan',
        'title' => '',
        'link' => '',
    ]
];
$dashboardStats = [
    [
        'title' => $isKanwil ? 'Approved Submission' : 'Submission Summary',
        'result' => $isKanwil ? $approvedPengajuan : $tampung,
        'icon' => 'fa-archive',
        'color' => '',
        'link' => $isKanwil ? 'index.php?page=submission-out#approved' : 'index.php?page=submission-out#request',
    ],
    [
        'title' => $isKanwil ? 'Pending Approval' : ($isAdminOrCabang ? 'Incoming Submission' : 'Pending Request'),
        'result' => $resultPendingPengajuan,
        'icon' => 'fa-bell-o',
        'color' => 'orange',
        'link' => $isKanwil ? 'index.php?page=submission-out#incomplete' : ($isAdminOrCabang ? 'index.php?page=submission-out#request' : 'index.php?page=submission-out#incomplete'),
    ],
    [
        'title' => 'Outgoing Items',
        'result' => $outstocks,
        'icon' => 'fa fa-mail-forward',
        'color' => '',
        'link' => 'index.php?page=log-inventory#barang_keluar',
    ],
    [
        'title' => 'Incoming Items',
        'result' => $instocks,
        'icon' => 'fa fa-mail-reply',
        'color' => '',
        'link' => 'index.php?page=log-inventory#barang_masuk',
    ],
];

?>

<div class="dashboard-menu">
    <div class="content-heading">
        Dashboard LogiTrack
    </div>
    <div><i>Welcome back! Here what's happening with your activity today</i></div>
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
            $heading = $item['heading'];
            $actions = isset($item['actions']) ? $item['actions'] : null;
            ?>
            <div class="card-recent">
                <div class="recent-content">
                    <div>
                        <div class="dashboard-title"><?php echo $heading; ?></div>
                        <?php if ($actions): ?>
                            <?php foreach ($actions as $a): ?>
                                <a href="<?php echo $a['link']; ?>" class="dashboard-title btnRecent"><?php echo $a['title']; ?></a>
                            <?php endforeach; ?>
                        <?php elseif (!empty($titles)): ?>
                            <a href="<?php echo $a; ?>" class="dashboard-title btnRecent"><?php echo $a['link']; ?></a>
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