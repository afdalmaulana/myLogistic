<?php
// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

$query = "SELECT * FROM unit_kerja ORDER BY kode_uker DESC";
$uker = $conn->query($query);;
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addUkerBtn = document.querySelector('.addUker');

        if (addUkerBtn) {
            addUkerBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Tambah Unit Kerja',
                    html: '<input id="swal-kode" class="swal2-input" placeholder="Kode Uker">' +
                        '<input id="swal-nama" class="swal2-input" placeholder="Nama Unit Kerja">',
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Simpan',
                    preConfirm: () => {
                        const kode = document.getElementById('swal-kode').value.trim();
                        const nama = document.getElementById('swal-nama').value.trim();

                        if (!kode || !nama) {
                            Swal.showValidationMessage('Semua field wajib diisi');
                            return false;
                        }

                        return {
                            kode,
                            nama
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const data = result.value;

                        fetch('addUker_Handler.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(data)
                            })
                            .then(response => response.json())
                            .then(res => {
                                if (res.success) {
                                    Swal.fire('Berhasil', 'Unit Kerja ditambahkan!', 'success')
                                        .then(() => location.reload());
                                } else {
                                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                                }
                            })
                            .catch(() => {
                                Swal.fire('Error', 'Tidak bisa terhubung ke server', 'error');
                            });
                    }
                });
            });
        }
    });
</script>


<div class="dashboard-menu">
    <div class="content-heading">User List Management</div>
    <div id="list-user" style="display: block;">
        <div class="body-content">
            <div class="sub-menu">
                <p>Daftar Unit Kerja</p>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ..." class="list-input">
                <button class="addUker">Tambah Unit Kerja</button>
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