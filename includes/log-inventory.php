<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

$sudirmanCodes = ['0334', '1548', '3050', '3411', '3581', '3582', '3810', '3811', '3815', '3816', '3819', '3821', '3822', '3825', '4986', '7016', '7077'];
$ahmadYaniCodes = ['0050', '1074', '0664', '2086', '2051', '2054', '1436'];
$tamalanreaCodes = ['0403', '7442', '4987', '3823', '3818', '3806', '3419', '3057', '2085', '1831', '1814', '1709', '1554'];

$role = $_SESSION['role'] ?? '';
$user = $_SESSION['user'] ?? '';
$kodeUker = $_SESSION['kode_uker'] ?? '';
$idJabatan = $_SESSION['id_jabatan'] ?? '';

$isAdminlog = ($role === 'admin');
$isLogistikSudirman = $user === '00344250';
$isLogistikAhmadYani = $user === '00203119';
$isLogistikTamalanrea = $user === '00220631';
$isSudirmanAccess = in_array($user, ['00068898', '00031021']);
$isAyaniAccess = in_array($user, ['00008839', '00030413']);
$isTamalanreaAccess = in_array($user, ['00028145', '00062209']);

$isAdmin = $role === 'admin';
$isAdminLog = $isAdmin;
$isBerwenang = in_array($idJabatan, ['JB1', 'JB2', 'JB3', 'JB5', 'JB6']);

// Reset filter jika diminta
if (isset($_GET['reset_filter'])) {
    unset($_SESSION['filter_uker']);
    header("Location: index.php?page=log-inventory");
    exit;
}

// Simpan filter ke session jika ada perubahan
if (isset($_GET['filter_uker'])) {
    $_SESSION['filter_uker'] = $_GET['filter_uker'];
}

// Ambil filter dari session
$filterUker = $_SESSION['filter_uker'] ?? '';

// Tentukan WHERE clause berdasarkan role & filter
if ($isAdminlog) {
    // Admin bisa lihat semua, atau filter jika ada
    $whereClause = (!empty($filterUker)) ? "kode_uker = '" . $conn->real_escape_string($filterUker) . "'" : "1";
} else if ($isLogistikAhmadYani) {
    $allowedUkerList = "'" . implode("','", $ahmadYaniCodes) . "'";
    if (!empty($filterUker) && in_array($filterUker, $ahmadYaniCodes)) {
        $whereClause = "kode_uker = '" . $conn->real_escape_string($filterUker) . "'";
    } else {
        $whereClause = "kode_uker IN ($allowedUkerList)";
    }
} else if ($isLogistikSudirman) {
    $allowedUkerList = "'" . implode("','", $sudirmanCodes) . "'";
    if (!empty($filterUker) && in_array($filterUker, $sudirmanCodes)) {
        $whereClause = "kode_uker = '" . $conn->real_escape_string($filterUker) . "'";
    } else {
        $whereClause = "kode_uker IN ($allowedUkerList)";
    }
} else if ($isLogistikTamalanrea) {
    $allowedUkerList = "'" . implode("','", $tamalanreaCodes) . "'";
    if (!empty($filterUker) && in_array($filterUker, $tamalanreaCodes)) {
        $whereClause = "kode_uker = '" . $conn->real_escape_string($filterUker) . "'";
    } else {
        $whereClause = "kode_uker IN ($allowedUkerList)";
    }
} else {
    // User biasa hanya bisa lihat kode_uker dari sessionnya
    $kodeUkerEsc = $conn->real_escape_string($kodeUker);
    $whereClause = "kode_uker = '$kodeUkerEsc'";
}

// Ambil data barang masuk dan keluar
$stocksIn = $conn->query("SELECT * FROM barang_masuk WHERE $whereClause ORDER BY tanggal DESC");
$resultOut = $conn->query("SELECT * FROM barang_keluar WHERE $whereClause ORDER BY tanggal DESC");
?>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Fungsi buka tab
        function openCity(evt, tabName) {
            var i, tabcontent, tablinks;

            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            var tabElem = document.getElementById(tabName);
            if (tabElem) {
                tabElem.style.display = "block";
            } else {
                console.warn('Tab dengan id "' + tabName + '" tidak ditemukan di DOM.');
            }

            if (evt) {
                evt.currentTarget.className += " active";
            } else {
                var autoBtn = document.querySelector('.tablinks[onclick*="' + tabName + '"]');
                if (autoBtn) {
                    autoBtn.className += " active";
                }
            }
        }

        // Cek hash di URL dan buka tab sesuai
        const hash = window.location.hash;
        if (hash) {
            const tabName = hash.substring(1); // hapus #
            openCity(null, tabName); // buka tab otomatis
        } else {
            openCity(null, 'barang_masuk'); // default ke tab "incomplete"
        }

        // Optional: expose openTab ke global (jika dipakai di HTML onclick)
        window.openCity = openCity;

        document.querySelectorAll('.btn-edit-nota').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const currentDate = this.dataset.current || '';

                Swal.fire({
                    title: 'Edit Tanggal Nota',
                    input: 'date',
                    inputLabel: 'Pilih tanggal baru',
                    inputValue: currentDate,
                    showCancelButton: true,
                    confirmButtonText: 'Simpan',
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Tanggal tidak boleh kosong!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kirim AJAX ke update_tanggal_nota.php
                        fetch('update_tanggal_nota.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `id=${encodeURIComponent(id)}&tanggal_nota=${encodeURIComponent(result.value)}`
                            })
                            .then(response => response.text())
                            .then(data => {
                                Swal.fire('Berhasil!', 'Tanggal nota diperbarui.', 'success')
                                    .then(() => {
                                        location.reload(); // atau update DOM tanpa reload
                                    });
                            })
                            .catch(error => {
                                console.error(error);
                                Swal.fire('Gagal!', 'Terjadi kesalahan saat mengupdate.', 'error');
                            });
                    }
                });
            });
        });

        //Delete 
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const table = this.dataset.table;

                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: "Data yang dihapus tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('deleteLog.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `id=${encodeURIComponent(id)}&table=${encodeURIComponent(table)}`
                            })
                            .then(response => response.text())
                            .then(data => {
                                Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success')
                                    .then(() => location.reload());
                            })
                            .catch(error => {
                                console.error(error);
                                Swal.fire('Gagal!', 'Gagal menghapus data.', 'error');
                            });
                    }
                });
            });
        });
    });
</script>

<div class="dashboard-menu">
    <div class="content-heading">Log Inventory Management</div>
    <div><i>Track log incoming, and outgoing inventory</i></div>
    <div class="tab">
        <button class="tablinks active" onclick="openCity(event, 'barang_masuk')">STOCK IN</button>
        <button class="tablinks" onclick="openCity(event, 'barang_keluar')">STOCK OUT</button>
    </div>

    <!-- Barang Masuk -->
    <div id="barang_masuk" class="tabcontent" style="display: block;">
        <div class="body-content">
            <div class="sub-menu" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin-bottom: 5px;">Log Record</p>
                    <?php if ($isAdminLog && $isBerwenang): ?>
                        <form method="GET" style="display: inline-block;">
                            <input type="hidden" name="page" value="log-inventory">
                            <select name="filter_uker" onchange="this.form.submit()" class="list-select" style="padding: 5px;">
                                <option value="">Filter Kode Uker</option>
                                <?php
                                $allowedCodes = [];

                                if ($isLogistikSudirman || $isSudirmanAccess) {
                                    $allowedCodes = $sudirmanCodes;
                                } elseif ($isLogistikAhmadYani || $isAyaniAccess) {
                                    $allowedCodes = $ahmadYaniCodes;
                                } elseif ($isLogistikTamalanrea || $isTamalanreaAccess) {
                                    $allowedCodes = $tamalanreaCodes;
                                }

                                $query = "SELECT DISTINCT kode_uker FROM barang_masuk";
                                if (!empty($allowedCodes)) {
                                    // Filter hanya kode yang diperbolehkan
                                    $codesList = "'" . implode("','", $allowedCodes) . "'";
                                    $query .= " WHERE kode_uker IN ($codesList)";
                                }
                                $query .= " ORDER BY kode_uker";

                                $ukerQuery = $conn->query($query);
                                while ($uker = $ukerQuery->fetch_assoc()):
                                    $selected = ($filterUker === $uker['kode_uker']) ? 'selected' : '';
                                    echo "<option value=\"{$uker['kode_uker']}\" $selected>{$uker['kode_uker']}</option>";
                                endwhile;
                                ?>

                            </select>
                        </form>
                    <?php endif; ?>
                    <a href="export_barangMasuk.php" class="list-select" style="padding:5px; text-decoration:none;">Download Excel</a>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stocksIn && $stocksIn->num_rows > 0): ?>
                            <?php while ($row = $stocksIn->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                    <td>
                                        <?php if (empty($row['tanggal_nota'])): ?>
                                            Input Tanggal Nota
                                            <button style="background: none; border: none" class="btn-edit-nota"
                                                data-id="<?= $row['id'] ?>"
                                                data-current="<?= $row['tanggal_nota'] ?>">
                                                <i class="fa fa-edit" style="font-size:16px;color:red"></i>
                                            </button>
                                        <?php else: ?>
                                            <?= htmlspecialchars($row['tanggal_nota']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <?php if (!empty($row['tanggal_approve'])): ?>
                                        <td><?= htmlspecialchars($row['tanggal_approve']) ?></td>
                                    <?php else: ?>
                                        <td></td>
                                    <?php endif;
                                    ?>
                                    <!-- <td><?= htmlspecialchars($row['tanggal_approve']) ?></td> -->
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <!-- <td><?= htmlspecialchars($row['price']) ?></td> -->
                                    <?php if (!empty($row['price'])): ?>
                                        <td><?= htmlspecialchars($row['price']) ?></td>
                                    <?php else: ?>
                                        <td></td>
                                    <?php endif;
                                    ?>
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                    <td>
                                        <?php if ($isAdminlog): ?>
                                            <button class="btn-delete" data-id="<?= $row['id'] ?>" data-table="barang_masuk" style="background:none; border:none;">
                                                <i class="fa fa-trash" style="color:red;"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>

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

    <!-- Barang Keluar -->
    <div id="barang_keluar" class="tabcontent">
        <div class="body-content">
            <div class="sub-menu" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin-bottom: 5px;">Log Record</p>
                    <?php if ($isAdminLog && $isBerwenang): ?>
                        <form method="GET" style="display: inline-block;">
                            <input type="hidden" name="page" value="log-inventory">
                            <select name="filter_uker" onchange="this.form.submit()" class="list-select" style="padding: 5px;">
                                <option value="">Filter Kode Uker</option>
                                <?php
                                $allowedCodes = [];

                                if ($isLogistikSudirman) {
                                    $allowedCodes = $sudirmanCodes;
                                } elseif ($isLogistikAhmadYani) {
                                    $allowedCodes = $ahmadYaniCodes;
                                } elseif ($isLogistikTamalanrea) {
                                    $allowedCodes = $tamalanreaCodes;
                                }

                                $query = "SELECT DISTINCT kode_uker FROM barang_keluar";
                                if (!empty($allowedCodes)) {
                                    // Filter hanya kode yang diperbolehkan
                                    $codesList = "'" . implode("','", $allowedCodes) . "'";
                                    $query .= " WHERE kode_uker IN ($codesList)";
                                }
                                $query .= " ORDER BY kode_uker";

                                $ukerQuery = $conn->query($query);
                                while ($uker = $ukerQuery->fetch_assoc()):
                                    $selected = ($filterUker === $uker['kode_uker']) ? 'selected' : '';
                                    echo "<option value=\"{$uker['kode_uker']}\" $selected>{$uker['kode_uker']}</option>";
                                endwhile;
                                ?>
                            </select>
                        </form>
                    <?php endif; ?>
                    <a href="export_barangKeluar.php" class="list-select" style="padding:5px; text-decoration:none;">Download Excel</a>
                </div>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input" style="width: 200px;">
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
                                    <td>
                                        <?php if ($isAdminlog): ?>
                                            <button class="btn-delete" data-id="<?= $row['id'] ?>" data-table="barang_keluar" style="background:none; border:none;">
                                                <i class="fa fa-trash" style="color:red;"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>

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