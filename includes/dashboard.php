<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

// Inisialisasi data user dan role
$sudirmanCodes = ['0334', '1548', '3050', '3411', '3581', '3582', '3810', '3811', '3815', '3816', '3819', '3821', '3822', '3825', '4986', '7016', '7077'];
$ahmadYaniCodes = ['0050', '1074', '0664', '2086', '2051', '2054', '1436'];
$tamalanreaCodes = ['0403', '7442', '4987', '3823', '3818', '3806', '3419', '3057', '2085', '1831', '1814', '1709', '1554'];

$role = $_SESSION['role'] ?? '';
$user = $_SESSION['user'] ?? '';
$kodeUker = $_SESSION['kode_uker'] ?? '';
$idJabatan = $_SESSION['id_jabatan'] ?? '';

$isKanwil = $idJabatan === 'JB3';
$isNoLimit = $idJabatan === ['JB1', 'JB2', 'JB8'];
$isSudirmanAccess = in_array($user, ['00068898', '00031021']);
$isAyaniAccess = in_array($user, ['00008839', '00030413']);
$isTamalanreaAccess = in_array($user, ['00028145', '00062209']);
$pnLogistikSudirman = $user === '00344250';
$pnLogistikAyani = $user === '00203119';
$pnLogistikTamalanrea = $user === '00220631';

$isLogistikSudirman = $pnLogistikSudirman || $isSudirmanAccess;
$isLogistikAhmadYani = $pnLogistikAyani || $isAyaniAccess;
$isLogistikTamalanrea = $pnLogistikTamalanrea || $isTamalanreaAccess;

// Tentukan WHERE clause
if ($isKanwil) {
    $whereClause = "1"; // Kanwil melihat semua
} elseif ($isLogistikSudirman || $isSudirmanAccess) {
    $inList = "'" . implode("','", $sudirmanCodes) . "'";
    $whereClause = "kode_uker IN ($inList)";
} elseif ($isLogistikAhmadYani || $isAyaniAccess) {
    $inList = "'" . implode("','", $ahmadYaniCodes) . "'";
    $whereClause = "kode_uker IN ($inList)";
} elseif ($isLogistikTamalanrea || $isTamalanreaAccess) {
    $inList = "'" . implode("','", $tamalanreaCodes) . "'";
    $whereClause = "kode_uker IN ($inList)";
} else {
    $kode_uker = $conn->real_escape_string($kodeUker);
    $whereClause = "kode_uker = '$kode_uker'";
}

// Query-query yang digunakan
$queryRecent = "SELECT * FROM pengajuan WHERE $whereClause ORDER BY updated_at DESC LIMIT 5";
$resultRecent = $conn->query($queryRecent);

$queryAll = "SELECT * FROM pengajuan WHERE $whereClause ORDER BY kode_pengajuan DESC";
$tampung = $conn->query($queryAll);

$queryPending = "SELECT * FROM pengajuan WHERE $whereClause AND status = 'Pending'";
$resultPendingPengajuan = $conn->query($queryPending);

$queryPendingApproval = "SELECT * FROM pengajuan WHERE $whereClause AND status = 'Forward'";
$resultPendingApproval = $conn->query($queryPendingApproval);

$queryApproved = "SELECT * FROM pengajuan WHERE $whereClause AND status = 'Approved' AND status_sisa = 'done'";
$approvedPengajuan = $conn->query($queryApproved);

$queryOut = "SELECT * FROM barang_keluar WHERE $whereClause ORDER BY nama_barang ASC";
$outstocks = $conn->query($queryOut);

$queryIn = "SELECT * FROM barang_masuk WHERE $whereClause ORDER BY nama_barang ASC";
$instocks = $conn->query($queryIn);

// Data untuk Dashboard Card
$dashboardStats = [
    [
        'title' => $isKanwil ? 'Approved Submission' : 'Submission Summary',
        'result' => $isKanwil ? $approvedPengajuan : $tampung,
        'icon' => 'fa-archive',
        'color' => 'bluee',
        'link' => $isKanwil ? 'index.php?page=submission-out#approved' : 'index.php?page=submission-out#request',
    ],
    [
        'title' => $isKanwil ? 'Pending Approval' : 'Pending Request',
        'result' => $isKanwil ? $resultPendingApproval : $resultPendingPengajuan,
        'icon' => 'fa-bell-o',
        'color' => 'orange',
        'link' => $isKanwil ? 'index.php?page=submission-out#incomplete' : 'index.php?page=submission-out#request',
    ],
    [
        'title' => 'Outgoing Items',
        'result' => $outstocks,
        'icon' => 'fa fa-mail-forward',
        'color' => 'langolango',
        'link' => 'index.php?page=log-inventory#barang_keluar',
    ],
    [
        'title' => 'Incoming Items',
        'result' => $instocks,
        'icon' => 'fa fa-mail-reply',
        'color' => 'greens',
        'link' => 'index.php?page=log-inventory#barang_masuk',
    ],
];

// Quick action dan recent update
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
    ]
];
?>

<div class="dashboard-menu">
    <div class="content-heading">Dashboard LogiTrack</div>
    <div><i>Welcome back! Here what's happening with your activity today</i></div>

    <div class="dashboard-grid">
        <?php foreach ($dashboardStats as $item): ?>
            <a href="<?= $item['link'] ?>" class="dashboard-card-link">
                <div class="dashboard-card">
                    <div class="card-contents">
                        <div class="card-left">
                            <div class="dashboard-title"><?= $item['title'] ?></div>
                            <div class="dashboard-count"><?= $item['result']->num_rows ?></div>
                        </div>
                        <div class="card-right">
                            <div class="dashboard-icon <?= $item['color'] ?>">
                                <i class="fa <?= $item['icon'] ?>"></i>
                            </div>
                        </div>
                    </div>
                    <div>Activity</div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="dashboard-recent">
        <?php foreach ($dashboardRecent as $item): ?>
            <div class="card-recent">
                <div class="recent-content">
                    <div>
                        <div class="dashboard-title"><?= $item['heading'] ?></div>
                        <?php if (!empty($item['actions'])): ?>
                            <?php foreach ($item['actions'] as $a): ?>
                                <a href="<?= $a['link'] ?>" class="dashboard-title btnRecent"><?= $a['title'] ?></a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div>
                                <?php if ($resultRecent->num_rows > 0): ?>
                                    <?php while ($row = $resultRecent->fetch_assoc()): ?>
                                        <div class="recent-update">
                                            <strong><?= $row['kode_uker'] ?></strong>
                                            Pengajuan <strong><?= $row['kode_pengajuan'] ?></strong>
                                            status <span class="status-<?= strtolower($row['status']) ?>">
                                                <?= $row['status'] ?>
                                            </span>
                                            (<?= date("d M Y", strtotime($row['updated_at'])) ?>)
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