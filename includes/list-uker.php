<?php
// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

$query = "SELECT * FROM unit_kerja ORDER BY kode_uker DESC";
$uker = $conn->query($query);;
?>

<div class="dashboard-menu">
    <div class="content-heading">User List Management</div>

    <div id="list-user" style="display: block;">
        <div class="body-content">

            <div class="sub-menu">
                <p>Daftar Unit Kerja</p>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ..." class="list-input">
                <button>Tambah Unit Kerja</button>
            </div>

            <!-- Tabel Data User -->
            <div class="table-container">
                <table id="dataTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Kode Uker</th>
                            <th>Nama Unit Kerja</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($uker->num_rows > 0): ?>
                            <?php while ($row = $uker->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_uker']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" style="text-align:center;">Belum ada data user</td>
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