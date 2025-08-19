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
        <div class="body-content">
            <div class="table-container">
                <table id="dataTable" style="width:100%;">
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
                        <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <?php
        $status = strtolower($row['status']);
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
            <td style="background: none;"><?php if ($row['status'] === 'Pending'): ?>
                        <div style="font-size:12px;">Menuggu Approval KC</div>
                    <?php elseif ($row['status'] === 'Forward'): ?>
                        <div style="font-size:12px">Pengajuan dikirim ke Kanwil</div>
                    <?php elseif($row['status'] === 'Approved'): ?>
                        <div style="font-size:12px">Pengajuan disetujui</div>
                    <?php elseif($row['status'] === 'Rejected'): ?>
                        <div style="font-size:12px">Pengajuan ditolak</div>
                    <?php endif; ?></td>
            <td>
                <?php if ((isset($_SESSION['role']) && $_SESSION['role'] === 'admin') || (isset($_SESSION['kode_uker']) && $_SESSION['kode_uker'] === '0050')): ?>
        <button class="btn-action"
                data-kode="<?= $row['kode_pengajuan'] ?>"
                data-status="<?= strtolower($row['status']) ?>"
                style="font-size:24px; background: none; padding:10px; border:none">
            <i class="fa fa-ellipsis-v"></i>
        </button>
    <?php elseif ($row['status'] === 'Pending'): ?>
        <button class="button-trash" data-kode="<?= htmlspecialchars($row['kode_pengajuan']) ?>">
            Hapus <i class="fa fa-trash-o"></i>
        </button>
    <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="8" style="text-align:center;">Belum ada Pengajuan</td>
    </tr>
<?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div id="global-actions" class="dropdown-action" style="display:none; position:absolute; z-index:9999;">
    <button id="btn-forward" class="button-approve">Forward</button>
    <button id="btn-approve" class="button-approve">Approve</button>
    <button id="btn-reject" class="button-reject">Reject</button>
</div>
