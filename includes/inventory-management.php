<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

$kodeUkerSession = $_SESSION['kode_uker'] ?? null;
$role = $_SESSION['role'] ?? '';
$user = $_SESSION['user'] ?? '';
$idJabatan = $_SESSION['id_jabatan'] ?? '';

$isAdminlog = ($role === 'admin');
$isBerwenang = in_array($idJabatan, ['JB1', 'JB2', 'JB3', 'JB5', 'JB6']);

// Daftar kode uker untuk masing-masing logistik
$sudirmanCodes = ['0334', '1548', '3050', '3411', '3581', '3582', '3810', '3811', '3815', '3816', '3819', '3821', '3822', '3825', '4986', '7016', '7077'];
$ahmadYaniCodes = ['0050', '1074', '0664', '2086', '2051', '2054', '1436'];
$tamalanreaCodes = ['0403', '7442', '4987', '3823', '3818', '3806', '3419', '3057', '2085', '1831', '1814', '1709', '1554'];

// Identifikasi user logistik berdasarkan user ID
$isLogistikTamalanrea = ($user === '00220631');
$isLogistikSudirman = ($user === '00344250');
$isLogistikAhmadYani = ($user === '00203119');

// Tangani filter_uker dari GET dan simpan di session
if (isset($_GET['filter_uker'])) {
    $_SESSION['filter_uker'] = $_GET['filter_uker'];
}
$filterUker = $_SESSION['filter_uker'] ?? '';

// Tentukan WHERE clause berdasarkan role dan user
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
    $kodeUkerEsc = $conn->real_escape_string($kodeUkerSession);
    $whereClause = "kode_uker = '$kodeUkerEsc'";
}

// Query data stok dan barang masuk berdasarkan where clause
$queryStock = "SELECT * FROM stok_barang WHERE $whereClause ORDER BY id ASC";
$stocks = $conn->query($queryStock);

$query = "SELECT * FROM barang_masuk WHERE $whereClause ORDER BY nama_barang DESC";
$stocksIn = $conn->query($query);

// Query log full (optional)
$query = "SELECT * FROM barang_masuk ORDER BY tanggal DESC";
$result = $conn->query($query);

$query = "SELECT * FROM barang_keluar ORDER BY tanggal DESC";
$resultOut = $conn->query($query);

// Untuk dropdown nama barang stok berdasarkan kode_uker session
if ($kodeUkerSession) {
    $stmt = $conn->prepare("SELECT nama_barang FROM stok_barang WHERE kode_uker = ? ORDER BY nama_barang ASC");
    $stmt->bind_param("s", $kodeUkerSession);
    $stmt->execute();
    $stokResult = $stmt->get_result();
} else {
    $stokResult = false;
}
?>

<?php if (isset($_GET['status'])): ?>
    <script src="../js/sweetalert.all.min.js"></script>
    <script>
        <?php if ($_GET['status'] === 'success'): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data Berhasil disimpan'
            });
        <?php elseif ($_GET['status'] === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan dalam form, mohon di ulangi'
            })
        <?php elseif ($_GET['status'] === 'outstock'): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Stock tidak mencukupi',
            });
        <?php endif; ?>
    </script>
<?php endif; ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Fungsi buka tab
        function openInvent(evt, tabName) {
            var i, tabcontentinvent, tablinks;

            tabcontentinvent = document.getElementsByClassName("tabcontent-invent");
            if (!tabcontentinvent.length) {
                console.warn("Tidak ada tab content ditemukan, mungkin ini bukan halaman inventory");
                return; // langsung keluar, gak usah buka tab
            }

            for (i = 0; i < tabcontentinvent.length; i++) {
                tabcontentinvent[i].style.display = "none";
            }

            tablinks = document.getElementsByClassName("tablink-invent");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            var tabElem = document.getElementById(tabName);
            if (!tabElem) {
                // fallback ke 'stocks' kalau id tidak ditemukan
                tabName = 'stocks';
                tabElem = document.getElementById(tabName);
            }
            if (!tabElem) {
                console.warn("Tab dengan id '" + tabName + "' tidak ditemukan, skip openInvent");
                return;
            }

            tabElem.style.display = "block";

            if (evt) {
                evt.currentTarget.className += " active";
            } else {
                var autoBtn = document.querySelector('.tablink-invent[onclick*="' + tabName + '"]');
                if (autoBtn) {
                    autoBtn.className += " active";
                }
            }
        }


        // Cek hash di URL dan buka tab sesuai
        const hash = window.location.hash;
        const validTabs = ['stocks', 'formBarang_masuk', 'formBarang_keluar'];

        let tabName = 'stocks'; // default

        if (hash) {
            const potentialTab = hash.substring(1);
            if (validTabs.includes(potentialTab)) {
                tabName = potentialTab;
            } else {
                // Kalau hash tidak valid, hapus hash dari URL
                history.replaceState(null, null, ' ');
            }
        }

        openInvent(null, tabName);

        // Optional: expose openTab ke global (jika dipakai di HTML onclick)
        window.openInvent = openInvent;
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.btn-delete-stock').forEach(function(button) {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: "Data akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('deleteStock.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'id=' + encodeURIComponent(id)
                            })
                            .then(response => response.text())
                            .then(data => {
                                if (data.trim() === 'success') {
                                    Swal.fire('Dihapus!', 'Data berhasil dihapus.', 'success').then(() => {
                                        location.reload(); // Atau: hapus baris langsung tanpa reload
                                    });
                                } else {
                                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus.', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error!', 'Tidak dapat terhubung ke server.', 'error');
                            });
                    }
                });
            });
        });
    });
</script>



<div class="dashboard-menu">
    <div class="content-heading">Inventory Management</div>
    <div><i>Manage your inventory, track incoming, and outgoing</i></div>
    <div class="tab-invent">
        <button class="tablink-invent active" onclick="openInvent(event, 'stocks')">STOCK</button>
        <button class="tablink-invent" onclick="openInvent(event, 'formBarang_masuk')">RECORD INCOMING</button>
        <button class="tablink-invent" onclick="openInvent(event, 'formBarang_keluar')">RECORD OUTGOING</button>
    </div>

    <div id="stocks" class="tabcontent-invent" style="display: block;">
        <div class="body-content">
            <div class="sub-menu" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p>Inventory List</p>
                    <?php if ($isBerwenang): ?>
                        <form method="GET" style="display: inline-block;">
                            <input type="hidden" name="page" value="inventory-management">
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
                        <?php endif; ?>
                        </form>
                </div>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input">
            </div>
            <div class="table-container">
                <table id="dataTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Kode Uker</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <?php if ($isAdminlog): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stocks->num_rows > 0): ?>
                            <?php while ($row = $stocks->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                    <?php if ($isAdminlog): ?>
                                        <td>
                                            <button class="btn-delete-stock" data-id="<?= $row['id'] ?>" style="background:none; border:none;" title="Hapus">
                                                <i class="fa fa-trash" style="color:red;"></i>
                                            </button>
                                        </td>
                                    <?php endif; ?>

                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align:center;">Belum ada data stok barang</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="formBarang_masuk" class="tabcontent-invent">
        <form action="stockIn_connect.php" method="POST" onsubmit="return showLoading()">
            <div class="body-content">
                <p>Incoming Stock</p>
                <div class="form-input">
                    <div class="submission-left">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="nama_barang" class="list-input" placeholder="Masukkan Nama Barang">
                        </div>
                    </div>
                    <div class="submission-right">
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="jumlah" class="list-input" placeholder="Masukkan Jumlah">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="button-send">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="formBarang_keluar" class="tabcontent-invent">
        <form action="stockOut_connect.php" method="POST" onsubmit="return showLoading()">
            <div class="body-content">
                <p>Outgoing Stock</p>
                <!-- <div><i>* Tanggal Otomatis mengikut hari ini</i></div> -->
                <div class="form-input">
                    <div class="submission-left">
                        <div class="form-group">
                            <label>Product Name</label>
                            <select name="nama_barang" class="list-input" required style="border-radius: 10px;">
                                <option value="" disabled selected hidden>Choose</option>
                                <?php
                                if ($stokResult && $stokResult->num_rows > 0) {
                                    while ($row = $stokResult->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['nama_barang']) . '">' . htmlspecialchars($row['nama_barang']) . '</option>';
                                    }
                                } else {
                                    echo '<option value="" disabled>Belum ada barang tersedia</option>';
                                }
                                ?>
                            </select>
                            <div class="form-group">
                                <label for="">Quantity</label>
                                <input type="number" name="jumlah" class="list-input" placeholder="Jumlah" required>
                            </div>
                        </div>
                    </div>
                    <div class="submission-right">
                        <div class="form-group">
                            <label>Departement</label>
                            <select name="divisi" class="list-input" required style="border-radius: 10px;">
                                <option value="" disabled selected hidden>Choose</option>
                                <option value="OPS">Operasional</option>
                                <option value="HC">Human Capital</option>
                                <option value="LOG">Logistik</option>
                                <option value="ADK">Administrasi Keuangan</option>
                                <option value="RMFT">RMFT</option>
                                <option value="RMFT">RMSME</option>
                                <option value="RMFT">CRR</option>
                                <option value="RMFT">BRIGUNA</option>
                                <option value="RMFT">KPR</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" id="submitBtn" class="button-send">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>