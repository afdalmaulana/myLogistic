<?php
// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

// Cek apakah user adalah admin atau cabang dengan hak khusus
$isAdminOrCabang = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ||
    (isset($_SESSION['kode_uker']) && $_SESSION['kode_uker'] === '0050');

// Siapkan whereClause awal
$whereClause = "1"; // default untuk admin

if (!$isAdminOrCabang) {
    $userKodeUker = $conn->real_escape_string($_SESSION['kode_uker']);
    $whereClause = "users.kode_uker = '$userKodeUker'";
}

// Ambil filter jika ada
$filter_uker = '';
if (isset($_GET['filter_uker']) && $_GET['filter_uker'] !== '') {
    $filter_uker = $conn->real_escape_string($_GET['filter_uker']);
    $whereClause .= " AND users.kode_uker = '$filter_uker'";
}

// Query utama
$query = "
    SELECT 
        users.*, 
        jabatan.nama_jabatan 
    FROM 
        users 
    LEFT JOIN 
        jabatan ON users.id_jabatan = jabatan.id_jabatan
    WHERE $whereClause
";

$list = $conn->query($query);

// Optional debugging (hapus di production)
if (!$list) {
    die("Query error: " . $conn->error);
}
?>

<div class="dashboard-menu">
    <div class="content-heading">User List Management</div>

    <div id="list-user" style="display: block;">
        <div class="body-content">

            <div class="sub-menu">
                <p>Daftar User</p>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ..." class="list-input">
            </div>

            <?php if ($isAdminOrCabang): ?>
                <!-- Filter Kode Uker -->
                <form method="GET" style="margin-bottom: 15px;">
                    <input type="hidden" name="page" value="user-list">
                    <?php if (!empty($filter_uker)): ?>
                        <a href="index.php?page=user-list" class="reset-filter" style="margin-left: 10px; color: red;">Reset</a>
                    <?php endif; ?>
                </form>
            <?php endif; ?>

            <!-- Tabel Data User -->
            <div class="table-container">
                <table id="dataTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Nama Pekerja</th>
                            <th>Role</th>
                            <th>Kode Uker</th>
                            <th>Jabatan</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($list->num_rows > 0): ?>
                            <?php while ($row = $list->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_pekerja']) ?></td>
                                    <td><?= htmlspecialchars($row['role']) ?></td>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                                    <td></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center;">Belum ada data user</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script>
    function searchTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll("#dataTable tbody tr");

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    }
</script>