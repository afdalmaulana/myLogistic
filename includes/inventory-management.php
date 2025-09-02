<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

$kodeUkerSession = $_SESSION['kode_uker'] ?? null;
$isAdminOrCabang = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') || ($kodeUkerSession === '0050');

$isBerwenang = isset($_SESSION['id_jabatan']) && in_array($_SESSION['id_jabatan'], ['JB1', 'JB2', 'JB3', 'JB5', 'JB6']);
if (isset($_GET['filter_uker'])) {
    $_SESSION['filter_uker'] = $_GET['filter_uker'];
}

// Ambil filter dari session
if (isset($_GET['filter_uker'])) {
    $_SESSION['filter_uker'] = $_GET['filter_uker'];
}

$filterUker = isset($_SESSION['filter_uker']) ? $conn->real_escape_string($_SESSION['filter_uker']) : '';

if ($isAdminOrCabang) {
    $whereClause = (!empty($filterUker)) ? "kode_uker = '$filterUker'" : "1";
} else {
    $kode_uker = $conn->real_escape_string($_SESSION['kode_uker']);
    $whereClause = "kode_uker = '$kode_uker'";
}

$sudirmanCodes = ['0334', '1548', '3050', '3411', '3581', '3582', '3810', '3811', '3815', '3816', '3819', '3821', '3822', '3825', '4986', '7016', '7077'];
$ahmadYaniCodes = ['0050', '1074', '0664', '2086', '2051', '2054', '1436'];

$role = $_SESSION['role'] ?? '';
$user = $_SESSION['user'] ?? '';
$kodeUker = $_SESSION['kode_uker'] ?? '';
$idJabatan = $_SESSION['id_jabatan'] ?? '';

$isLogistikSudirman = $user === '00344250';
$isLogistikAhmadYani = $user === '00203119';

$isAdmin = $role === 'admin';
$isAdminOrCabang = $isAdmin || $kodeUker === '0050';
$isBerwenang = in_array($idJabatan, ['JB1', 'JB2', 'JB3', 'JB5', 'JB6']);


if (isset($_GET['filter_uker'])) {
    $_SESSION['filter_uker'] = $_GET['filter_uker'];
}

$filterUker = $_SESSION['filter_uker'] ?? '';

// Tentukan WHERE clause berdasarkan role & filter
if ($isAdminOrCabang) {
    $whereClause = (!empty($filterUker)) ? "kode_uker = '$filterUker'" : "1";
} else {
    $whereClause = "kode_uker = '$kodeUker'";
}
// HILANGKAN baris berikut supaya filter_uker tidak tertimpa:
// $whereClause = $isAdminOrCabang ? "1" : "kode_uker = '{$conn->real_escape_string($kodeUkerSession)}'";

// Query dengan filter yang sudah benar
$queryStock = "SELECT * FROM stok_barang WHERE $whereClause ORDER BY id ASC";
$stocks = $conn->query($queryStock);

$query = "SELECT * FROM barang_masuk WHERE $whereClause ORDER BY nama_barang DESC";
$stocksIn = $conn->query($query);;

// Barang masuk - full log
$query = "SELECT * FROM barang_masuk ORDER BY tanggal DESC";
$result = $conn->query($query);

// Barang keluar - full log
$query = "SELECT * FROM barang_keluar ORDER BY tanggal DESC";
$resultOut = $conn->query($query);

// ==========================
// FIX: Stok dropdown HANYA berdasarkan kode_uker login
// ==========================
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stocks->num_rows > 0): ?>
                            <?php while ($row = $stocks->fetch_assoc()): ?>
                                <?php
                                // Filter berdasarkan logistik
                                if ($isLogistikSudirman && !in_array($row['kode_uker'], $sudirmanCodes)) continue;
                                if ($isLogistikAhmadYani && !in_array($row['kode_uker'], $ahmadYaniCodes)) continue;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" style="text-align:center;">Belum ada data stok barang</td>
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
                <!-- <div><i>* Tanggal Otomatis mengikut hari ini</i></div> -->
                <div class="form-input">
                    <div class="submission-left">
                        <!-- <div class="form-group">
                            <label>Nomor Nota</label>
                            <input type="text" name="nomor_nota" class="list-input" placeholder="Masukkan Nomor Nota">
                        </div> -->
                        <!-- <div class="form-group">
                            <label>Tanggal Nota</label>
                            <input type="date" name="tanggal_nota" class="list-input" placeholder="Masukkan Tanggal Nota">
                        </div> -->
                        <div class="form-group">
                            <label>Nama Barang</label>
                            <input type="text" name="nama_barang" class="list-input" placeholder="Masukkan Nama Barang">
                        </div>
                    </div>
                    <div class="submission-right">
                        <!-- <div class="form-group">
                            <label>Harga Barang</label>
                            <input type="text" name="harga_barang" class="list-input" placeholder="Masukkan Harga">
                        </div> -->
                        <div class="form-group">
                            <label>Jumlah</label>
                            <input type="number" name="jumlah" class="list-input" placeholder="Masukkan Jumlah">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="button-send">Kirim</button>
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
                            <label>Pilih Nama Barang</label>
                            <select name="nama_barang" class="list-input" required style="border-radius: 10px;">
                                <option value="" disabled selected hidden>Pilih Nama Barang</option>
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
                                <label for="">Jumlah</label>
                                <input type="number" name="jumlah" class="list-input" placeholder="Jumlah" required>
                            </div>
                        </div>
                    </div>
                    <div class="submission-right">
                        <div class="form-group">
                            <label>Departemen</label>
                            <select name="divisi" class="list-input" required style="border-radius: 10px;">
                                <option value="" disabled selected hidden>Pilih Departemen</option>
                                <option value="OPS">Operasional</option>
                                <option value="HC">Human Capital</option>
                                <option value="LOG">Logistik</option>
                                <option value="ADK">Administrasi Keuangan</option>
                                <option value="RMFT">RMFT</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" id="submitBtn" class="button-send">Kirim</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>