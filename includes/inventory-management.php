<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

$kodeUkerSession = $_SESSION['kode_uker'] ?? null;
$isAdminOrCabang = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') || ($kodeUkerSession === '0050');

// Untuk kebutuhan tabel stok dan log stok (admin/kanwil bisa lihat semua)
$whereClause = $isAdminOrCabang ? "1" : "kode_uker = '{$conn->real_escape_string($kodeUkerSession)}'";

// Tabel stok
$queryStock = "SELECT * FROM stok_barang WHERE $whereClause ORDER BY id ASC";
$stocks = $conn->query($queryStock);

// Barang masuk
$query = "SELECT * FROM barang_masuk WHERE $whereClause ORDER BY nama_barang DESC";
$stocksIn = $conn->query($query);

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
            <div class="sub-menu">
                <p>Inventory List</p>
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