<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

$successMessage = '';
$errorMessage = '';

// Filter query sesuai role
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' || (isset($_SESSION['kode_uker']) && $_SESSION['kode_uker'] === '0050')) {
    $query = "SELECT * FROM pengajuan ORDER BY kode_pengajuan DESC";
} else {
    $kodeUker = $conn->real_escape_string($_SESSION['kode_uker']);
    $query = "SELECT * FROM pengajuan WHERE kode_uker = '$kodeUker' ORDER BY kode_pengajuan DESC";
}

$result = $conn->query($query);
?>

<div class="content-wrapper">
    <div class="sub-content">
        <h4 style="font-weight: 800; font-size:32px;">List Pengajuan</h4>
        <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input">
        <div class="tabs">
            <button class="tabslinks active" onclick="openTab(event, 'incomplete')">Incomplete</button>
            <button class="tabslinks" onclick="openTab(event, 'approved')">Complete</button>
        </div>


        <div id="incomplete" class="tabscontent" style="display: block;">
            <div class="body-content">
                <div class="table-container">
                    <table id="dataTable-incomplete" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Kode Pengajuan</th>
                                <th>Kode Uker</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Perihal</th>
                                <th>Status</th>
                                <th>No. Surat</th>
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
                                if (!in_array($status, ['pending', 'forward'])) continue;
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
                                    <td><?= htmlspecialchars($row['perihal']) ?></td>
                                    <td class="status-cell <?= $class ?>"><?= htmlspecialchars($row['status']) ?></td>
                                    <td class="nomor-surat-cell"><?= htmlspecialchars($row['nomor_surat'] ?? '') ?></td>
                                    <td style="background: none;">
                                        <?php if ($status === 'pending'): ?>
                                            <div style="font-size:12px;">Menunggu Approval KC</div>
                                        <?php elseif ($status === 'forward'): ?>
                                            <div style="font-size:12px;">Menunggu Approval Kanwil</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ((isset($_SESSION['role']) && $_SESSION['role'] === 'admin') || (isset($_SESSION['kode_uker']) && $_SESSION['kode_uker'] === '0050')): ?>
                                            <button class="btn-action"
                                                data-kode="<?= $row['kode_pengajuan'] ?>"
                                                data-status="<?= $status ?>"
                                                style="font-size:24px; background: none; padding:10px; border:none">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
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
                                <th>Perihal</th>
                                <th>Status</th>
                                <th>No. Surat</th>
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
                                if (!in_array($status, ['approved', 'rejected'])) continue;
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
                                    <td><?= htmlspecialchars($row['perihal']) ?></td>
                                    <td class="status-cell <?= $class ?>"><?= htmlspecialchars($row['status']) ?></td>
                                    <td class="nomor-surat-cell"><?= htmlspecialchars($row['nomor_surat'] ?? '') ?></td>
                                    <td style="background: none;">
                                        <?php if ($status === 'approved'): ?>
                                            <div style="font-size:12px;">Pengajuan disetujui</div>
                                        <?php elseif ($status === 'rejected'): ?>
                                            <div style="font-size:12px;">Pengajuan ditolak</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ((isset($_SESSION['role']) && $_SESSION['role'] === 'admin') || (isset($_SESSION['kode_uker']) && $_SESSION['kode_uker'] === '0050')): ?>
                                            <button class="btn-action"
                                                data-kode="<?= $row['kode_pengajuan'] ?>"
                                                data-status="<?= $status ?>"
                                                style="font-size:24px; background: none; padding:10px; border:none">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                        <?php else: ?>
                                            <div></div>
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
</div>