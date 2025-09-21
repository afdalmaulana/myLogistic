<?php
// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

if ($_SESSION['role'] !== 'admin') {
    // Gak perlu pakai header jika sudah ada output, tampilkan pesan langsung dan stop
    // echo "<h2>Maaf ini area terlarang, hanya orang ganteng yang bisa masuk</h2>";
    include 'includes/403.php';
    exit;
}
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
        jabatan.nama_jabatan, 
        unit_kerja.nama_uker
    FROM 
        users 
    LEFT JOIN 
        jabatan ON users.id_jabatan = jabatan.id_jabatan
    LEFT JOIN 
        unit_kerja ON users.kode_uker = unit_kerja.kode_uker
    WHERE $whereClause
";

$list = $conn->query($query);

// Optional debugging (hapus di production)
if (!$list) {
    die("Query error: " . $conn->error);
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // 🛠️ Event untuk tombol Edit
        document.querySelectorAll('.editUserBtn').forEach(btn => {
            btn.addEventListener('click', () => {
                const username = btn.dataset.username;
                const nama = btn.dataset.nama;
                const role = btn.dataset.role;
                const jabatan = btn.dataset.jabatan;
                const uker = btn.dataset.uker;

                Swal.fire({
                    title: 'Edit User',
                    html: `
          <input id="swal-username" class="swal2-input" value="${username}" placeholder="Username">
          <input id="swal-nama" class="swal2-input" value="${nama}" placeholder="Nama Pekerja">
          <input id="swal-role" class="swal2-input" value="${role}" placeholder="Role">
          <input id="swal-jabatan" class="swal2-input" value="${jabatan}" placeholder="ID Jabatan">
          <input id="swal-uker" class="swal2-input" value="${uker}" placeholder="Kode Uker">
        `,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    preConfirm: () => {
                        return {
                            username: document.getElementById('swal-username').value,
                            nama: document.getElementById('swal-nama').value,
                            role: document.getElementById('swal-role').value,
                            jabatan: document.getElementById('swal-jabatan').value,
                            uker: document.getElementById('swal-uker').value
                        };
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch('updateUser.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(result.value)
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Berhasil', 'User berhasil diupdate', 'success').then(() => location.reload());
                                } else {
                                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                                }
                            });
                    }
                });
            });
        });

        // 🔑 Event untuk Ganti Password
        document.querySelectorAll('.changePassBtn').forEach(btn => {
            btn.addEventListener('click', () => {
                const username = btn.dataset.username;

                Swal.fire({
                    title: `Ganti Password untuk ${username}`,
                    html: `<input id="swal-newpass" class="swal2-input" type="password" placeholder="Password Baru">`,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    preConfirm: () => {
                        const password = document.getElementById('swal-newpass').value;
                        if (!password) {
                            Swal.showValidationMessage('Password tidak boleh kosong');
                            return false;
                        }
                        return {
                            username,
                            password
                        };
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch('update_password.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(result.value)
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Berhasil', 'Password berhasil diubah', 'success');
                                } else {
                                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                                }
                            });
                    }
                });
            });
        });

    });
</script>


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
                            <th>Edit</th>
                            <th>Username</th>
                            <th>Nama Pekerja</th>
                            <th>Password</th>
                            <th>Role</th>
                            <th>Kode Uker</th>
                            <th>Nama Unit Kerja</th>
                            <th>Jabatan</th>
                            <!-- <th></th> -->
                            <!-- <th></th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($list->num_rows > 0): ?>
                            <?php while ($row = $list->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <button class="editUserBtn"
                                            data-username="<?= $row['username'] ?>"
                                            data-nama="<?= $row['nama_pekerja'] ?>"
                                            data-role="<?= $row['role'] ?>"
                                            data-jabatan="<?= $row['id_jabatan'] ?>"
                                            data-uker="<?= $row['kode_uker'] ?>"><i class="fa fa-edit" style="font-size:22px"></i></button>
                                    </td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_pekerja']) ?></td>
                                    <td>
                                        <button class="changePassBtn"
                                            data-username="<?= $row['username'] ?>">Ganti Password</button>
                                    </td>
                                    <td><?= htmlspecialchars($row['role']) ?></td>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
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