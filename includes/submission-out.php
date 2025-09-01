<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

$successMessage = '';
$errorMessage = '';


// Filter query sesuai role
// ------------------- PERBAIKAN FILTER QUERY -----------------------
$sudirmanCodes = ['0334', '3556'];
$ahmadYaniCodes = ['0050', '1074', '0664', '2086', '2051', '2054', '1436'];

$role = $_SESSION['role'] ?? '';
$user = $_SESSION['user'] ?? '';
$kodeUker = $_SESSION['kode_uker'] ?? '';
$idJabatan = $_SESSION['id_jabatan'] ?? '';

$isAdmin = $role === 'admin';
$isKanwil = $idJabatan === 'JB3';
$isLogistikSudirman = $user === '00344250';
$isLogistikAhmadYani = $user === '00203119';

if ($isAdmin || $kodeUker === '0050' || $isKanwil) {
    // Admin, 0050, dan Kanwil bisa lihat semua
    $query = "
        SELECT p.*, a.nama_anggaran 
        FROM pengajuan p
        LEFT JOIN anggaran a ON p.id_anggaran = a.id_anggaran
        ORDER BY p.kode_pengajuan DESC
    ";
} elseif ($isLogistikSudirman) {
    // Logistik Sudirman hanya bisa lihat unit Sudirman
    $inClause = "'" . implode("','", $sudirmanCodes) . "'";
    $query = "
        SELECT p.*, a.nama_anggaran 
        FROM pengajuan p
        LEFT JOIN anggaran a ON p.id_anggaran = a.id_anggaran
        WHERE p.kode_uker IN ($inClause)
        ORDER BY p.kode_pengajuan DESC
    ";
} elseif ($isLogistikAhmadYani) {
    // Logistik Ahmad Yani hanya bisa lihat unit Ahmad Yani
    $inClause = "'" . implode("','", $ahmadYaniCodes) . "'";
    $query = "
        SELECT p.*, a.nama_anggaran 
        FROM pengajuan p
        LEFT JOIN anggaran a ON p.id_anggaran = a.id_anggaran
        WHERE p.kode_uker IN ($inClause)
        ORDER BY p.kode_pengajuan DESC
    ";
} else {
    // User biasa hanya bisa lihat pengajuan dari unit sendiri
    $kodeUkerEscaped = $conn->real_escape_string($kodeUker);
    $query = "
        SELECT p.*, a.nama_anggaran 
        FROM pengajuan p
        LEFT JOIN anggaran a ON p.id_anggaran = a.id_anggaran
        WHERE p.kode_uker = '$kodeUkerEscaped'
        ORDER BY p.kode_pengajuan DESC
    ";
}


$result = $conn->query($query);
$requestCount = 0;
$incompleteCount = 0;
$completeCount = 0;

// Reset pointer
$result->data_seek(0);

// Loop hanya untuk menghitung jumlah tab
while ($row = $result->fetch_assoc()) {
    $status = strtolower($row['status']);
    $status_sisa = strtolower($row['status_sisa'] ?? '');
    $sisa_jumlah = (int)($row['sisa_jumlah'] ?? 0);

    if ($status === 'pending') {
        $requestCount++;
    }

    if (in_array($status, ['approved', 'forward']) && $status_sisa === 'not done') {
        $incompleteCount++;
    }

    if (($status === 'approved' && $sisa_jumlah === 0) || $status === 'rejected') {
        $completeCount++;
    }
}

?>

<div class="content-wrapper">
    <div class="sub-content">
        <h4 style="font-weight: 800; font-size:32px;">Submission Overview</h4>
        <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input">
        <?php if ($isKanwil): ?>
            <div class="tabs">
                <button class="tabslinks" onclick="openTab(event, 'incomplete')">Request <span class="badge"><?= $incompleteCount ?></button>
                <button class="tabslinks" onclick="openTab(event, 'approved')">Complete <span class="badge"><?= $completeCount ?></button>
            </div>
        <?php else: ?>
            <div class="tabs">
                <button class="tabslinks active" onclick="openTab(event, 'request')">Request <span class="badge"><?= $requestCount ?></button>
                <button class="tabslinks" onclick="openTab(event, 'incomplete')">Incomplete <span class="badge"><?= $incompleteCount ?></button>
                <button class="tabslinks" onclick="openTab(event, 'approved')">Complete <span class="badge"><?= $completeCount ?></button>
            </div>
        <?php endif; ?>


        <div id="request" class="tabscontent" style="display: block;">
            <div class="body-content">
                <div class="table-container">
                    <table id="dataTable-incomplete" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Kode Pengajuan</th>
                                <th>Kode Uker</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Nama Barang</th>
                                <th style="cursor:pointer;" onclick="toggleSortStatus()">Status <span id="sortArrow">↓</span></th>
                                <th>Jumlah</th>
                                <th>Nama Anggaran</th>
                                <th>Nominal Anggaran</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result->data_seek(0);
                            $hasData = false;
                            while ($row = $result->fetch_assoc()):
                                $status = strtolower($row['status']);
                                if (!in_array($status, ['pending'])) continue;

                                // Filter khusus logistik berdasarkan kode uker
                                if ($isLogistikSudirman && !in_array($row['kode_uker'], $sudirmanCodes)) continue;
                                if ($isLogistikAhmadYani && !in_array($row['kode_uker'], $ahmadYaniCodes)) continue;

                                $hasData = true;
                                $class = match ($status) {
                                    'pending' => 'status-pending',
                                    'approved' => 'status-approved',
                                    'rejected' => 'status-rejected',
                                    'forward' => 'status-forward',
                                    default => '',
                                };
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['kode_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td class="status-cell <?= $class ?>"><?= htmlspecialchars($row['status']) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_anggaran']) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah_anggaran']) ?></td>
                                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                    <td>
                                        <?php if ($isAdmin || $kodeUker === '0050' || $isKanwil || $isLogistikSudirman || $isLogistikAhmadYani): ?>
                                            <button class="btn-action"
                                                data-kode="<?= $row['kode_pengajuan'] ?>"
                                                data-status="<?= $status ?>"
                                                style="font-size:24px; background: none; padding:10px; border:none">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                        <?php elseif ($status === 'pending'): ?>
                                            <button class="button-trash" data-kode="<?= $row['kode_pengajuan'] ?>">
                                                Hapus <i class="fa fa-trash-o"></i>
                                            </button>
                                        <?php else: ?>
                                            <button style="font-size:24px; background: none; padding:10px; border:none" class="btn-disabled" disabled>
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                            <?php if (!$hasData): ?>
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 20px; font-style: italic;">Belum ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        <div id="incomplete" class="tabscontent">
            <div class="body-content">
                <div class="table-container">
                    <table id="dataTable-incomplete" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Kode Pengajuan</th>
                                <th>Kode Uker</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Nama Barang</th>
                                <th style="cursor:pointer;" onclick="toggleSortStatus()">Status <span id="sortArrow">↓</span></th>
                                <th>Jumlah</th>
                                <th>Sisa</th>
                                <th>Harga Barang</th>
                                <th>Keterangan</th>
                                <th>Proses</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result->data_seek(0);
                            $hasData = false;
                            while ($row = $result->fetch_assoc()):
                                $status = strtolower($row['status']);
                                $status_sisa = strtolower($row['status_sisa'] ?? '');
                                if (!in_array($status, ['approved', 'forward']) || !in_array($status_sisa, ['not done', 'done'])) continue;
                                $hasData = true;
                                $class = match ($status) {
                                    'pending' => 'status-pending',
                                    'approved' => 'status-approved',
                                    'rejected' => 'status-rejected',
                                    'forward' => 'status-forward',
                                    'not done' => 'status-notdone',
                                    default => '',
                                };
                            ?>
                                <tr data-sisa-jumlah="<?= htmlspecialchars($row['sisa_jumlah']) ?>"
                                    data-proses="<?= htmlspecialchars($row['status_sisa']) ?>">
                                    <td><?= htmlspecialchars($row['kode_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td class="status-cell <?= $class ?>"><?= htmlspecialchars($row['status']) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                    <td><?= htmlspecialchars($row['sisa_jumlah']) ?></td>
                                    <td><?= htmlspecialchars($row['price']) ?></td>
                                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                    <td class="status-cell <?= $class ?>"><?= htmlspecialchars($row['status_sisa']) ?></td>
                                    <td>
                                        <?php if ($isKanwil || $isLogistikAhmadYani || $isLogistikSudirman): ?>
                                            <?php if ($isKanwil) : ?>
                                                <button class="btn-action"
                                                    data-kode="<?= $row['kode_pengajuan'] ?>"
                                                    data-status="<?= $status ?>"
                                                    style="font-size:24px; background: none; padding:10px; border:none">
                                                    <i class="fa fa-ellipsis-v"></i>
                                                </button>
                                            <?php else : ?>
                                                <button style="font-size:24px; background: none; padding:10px; border:none" class="btn-disabled" disabled><i class="fa fa-ellipsis-v"></i></button>
                                            <?php endif; ?>


                                        <?php else: ?>
                                            <?php if ($status === 'pending'): ?>
                                                <button class="button-trash" data-kode="<?= $row['kode_pengajuan'] ?>">
                                                    Hapus <i class="fa fa-trash-o"></i>
                                                </button>
                                            <?php else: ?>
                                                <div></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>


                                </tr>
                            <?php endwhile; ?>
                            <?php if (!$hasData): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 20px; font-style: italic;">Belum ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="approved" class="tabscontent">
            <div class="body-content">
                <div class="table-container">
                    <table id="dataTable-complete" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Kode Pengajuan</th>
                                <th>Kode Uker</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Nama Barang</th>
                                <th>Status</th>
                                <th>Jumlah</th>
                                <th>No. Surat</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result->data_seek(0);
                            $hasData = false;
                            while ($row = $result->fetch_assoc()):
                                $status = strtolower($row['status']);
                                if (
                                    ($status === 'approved' && (int)$row['sisa_jumlah'] > 0) ||
                                    (!in_array($status, ['approved', 'rejected']))
                                ) continue;
                                $hasData = true;
                                $class = match ($status) {
                                    'pending' => 'status-pending',
                                    'approved' => 'status-approved',
                                    'rejected' => 'status-rejected',
                                    'forward' => 'status-forward',
                                    default => '',
                                };
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['kode_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td class="status-cell <?= $class ?>"><?= htmlspecialchars($row['status']) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                    <td style="background: none;">
                                        <?php if ($status === 'approved'): ?>
                                            <div style="font-size:12px;">Pengajuan disetujui</div>
                                        <?php elseif ($status === 'rejected'): ?>
                                            <div style="font-size:12px;">Pengajuan ditolak</div>
                                        <?php endif; ?>
                                    </td>

                                </tr>
                            <?php endwhile; ?>
                            <?php if (!$hasData): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 20px; font-style: italic;">Belum ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>






    </div>
</div>
<div id="global-actions" class="dropdown-action" style="display:none; position:absolute; z-index:9999;">
    <button id="btn-forward" class="button-approve">Forward</button>
    <button id="btn-approve" class="button-approve">Approve</button>
    <button id="btn-reject" class="button-reject">Reject</button>
    <button id="btn-selesaikan" class="button-complete">Selesaikan</button>
</div>