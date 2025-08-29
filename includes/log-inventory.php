<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

// Reset filter jika diminta
if (isset($_GET['reset_filter'])) {
    unset($_SESSION['filter_uker']);
    header("Location: index.php?page=log-inventory");
    exit;
}

$isBerwenang = isset($_SESSION['id_jabatan']) && in_array($_SESSION['id_jabatan'], ['JB1', 'JB2', 'JB3', 'JB5', 'JB6']);

// Simpan filter ke session jika ada perubahan
if (isset($_GET['filter_uker'])) {
    $_SESSION['filter_uker'] = $_GET['filter_uker'];
}

// Ambil filter dari session
$filterUker = isset($_SESSION['filter_uker']) ? $conn->real_escape_string($_SESSION['filter_uker']) : '';

// Tentukan apakah admin atau kode_uker = 0050
$isAdminOrCabang = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ||
    (isset($_SESSION['kode_uker']) && $_SESSION['kode_uker'] === '0050');

// Tentukan WHERE clause berdasarkan role & filter
if ($isAdminOrCabang) {
    $whereClause = (!empty($filterUker)) ? "kode_uker = '$filterUker'" : "1";
} else {
    $kode_uker = $conn->real_escape_string($_SESSION['kode_uker']);
    $whereClause = "kode_uker = '$kode_uker'";
}

// Ambil data barang masuk dan keluar
$stocksIn = $conn->query("SELECT * FROM barang_masuk WHERE $whereClause ORDER BY nama_barang DESC");
$resultOut = $conn->query("SELECT * FROM barang_keluar ORDER BY tanggal DESC");
?>

<div class="dashboard-menu">
    <div class="content-heading">Log Inventory Management</div>
    <div><i>Track log incoming, and outgoing inventory</i></div>
    <div class="tab">
        <button class="tablinks active" onclick="openCity(event, 'barang_masuk')">STOCK IN</button>
        <button class="tablinks" onclick="openCity(event, 'barang_keluar')">STOCK OUT</button>
    </div>

    <div id="barang_masuk" class="tabcontent" style="display: block;">
        <div class="body-content">
            <div class="sub-menu" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin-bottom: 5px;">Log Record</p>
                    <?php if ($isAdminOrCabang): ?>
                        <form method="GET" style="display: inline-block;">
                            <!-- Jaga agar tetap di halaman log-inventory -->
                            <input type="hidden" name="page" value="log-inventory">
                            <?php if ($isBerwenang): ?>
                                <select name="filter_uker" onchange="this.form.submit()" class="list-select" style="padding: 5px;">
                                    <option value="">Filter Kode Uker</option>
                                    <?php
                                    $ukerQuery = $conn->query("SELECT DISTINCT kode_uker FROM barang_masuk ORDER BY kode_uker");
                                    while ($uker = $ukerQuery->fetch_assoc()):
                                        $selected = ($filterUker === $uker['kode_uker']) ? 'selected' : '';
                                        echo "<option value=\"{$uker['kode_uker']}\" $selected>{$uker['kode_uker']}</option>";
                                    endwhile;
                                    ?>
                                </select>
                            <?php else: ?>

                            <?php endif; ?>

                            <!-- <?php if (!empty($filterUker)): ?>
                                <a href="index.php?page=log-inventory&reset_filter=1" class="reset-filter">Reset</a>
                            <?php endif; ?> -->
                        </form>
                    <?php endif; ?>
                </div>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input" style="width: 200px;">
            </div>

            <div class="table-container">
                <table id="dataTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Kode Uker</th>
                            <th>Tanggal Input</th>
                            <th>Tanggal Nota</th>
                            <th>Tanggal Approval</th>
                            <th>Nama Barang</th>
                            <th>Harga Barang Satuan</th>
                            <th>Jumlah</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stocksIn && $stocksIn->num_rows > 0): ?>
                            <?php while ($row = $stocksIn->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                    <?php if ($row['tanggal_nota'] === null): ?>
                                        <td>
                                            Input Tanggal Nota
                                            <button style="background: none; border: none" class="btn-edit-nota"
                                                data-id="<?= $row['id'] ?>"
                                                data-current="<?= $row['tanggal_nota'] ?>">
                                                <i class="fa fa-edit" style="font-size:16px;color:red"></i>
                                            </button>
                                        </td>
                                    <?php else: ?>
                                        <td><?= htmlspecialchars($row['tanggal_nota']) ?></td>
                                    <?php endif; ?>
                                    <td><?= htmlspecialchars($row['tanggal_approve']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td><?= htmlspecialchars($row['price']) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                    <td></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align:center;">Belum ada data barang masuk</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="barang_keluar" class="tabcontent">
        <div class="body-content">
            <div class="sub-menu">
                <p>Log Record</p>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input">
            </div>

            <div class="table-container">
                <table id="dataTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Divisi</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultOut && $resultOut->num_rows > 0): ?>
                            <?php while ($row = $resultOut->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                    <td><?= htmlspecialchars($row['divisi']) ?></td>
                                    <td></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">Belum ada data barang keluar</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>