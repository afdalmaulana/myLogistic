<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

$isAdminOrCabang = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ||
    (isset($_SESSION['kode_uker']) && $_SESSION['kode_uker'] === '0050');

if ($isAdminOrCabang) {
    // Admin atau Kanwil melihat semua data
    $whereClause = "1"; // tidak ada filter
} else {
    // Selain itu, hanya melihat data berdasarkan kode_uker
    $kode_uker = $conn->real_escape_string($_SESSION['kode_uker']);
    $whereClause = "kode_uker = '$kode_uker'";
}

$query = "SELECT * FROM barang_masuk WHERE $whereClause ORDER BY nama_barang DESC";
$stocksIn = $conn->query($query);

$query = "SELECT * FROM barang_masuk ORDER BY tanggal DESC";
$result = $conn->query($query);

$query = "SELECT * FROM barang_keluar ORDER BY tanggal DESC";
$resultOut = $conn->query($query);

$queryStock = "SELECT * FROM stok_barang WHERE $whereClause ORDER BY nama_barang ASC";
$stocks = $conn->query($queryStock);

?>

?>





<div class="content-wrappers">
    <div class="content-heading">Inventory Management</div>
    <div>Manage your inventory, track incoming, and outgoing</div>
    <div class="tab-invent">
        <button class="tablink-invent active" onclick="openInvent(event, 'stocks')">Stok Barang</button>
        <button class="tablink-invent" onclick="openInvent(event, 'formBarang_masuk')">Barang Masuk</button>
        <button class="tablink-invent" onclick="openInvent(event, 'formBarang_keluar')">Barang Keluar</button>
    </div>

    <div id="stocks" class="tabcontent-invent" style="display: block;">
        <div class="body-content">
            <div class="sub-menu">
                <p>Daftar Stok Barang</p>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input">
            </div>
            <div class="table-container">
                <table id="dataTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stocks->num_rows > 0): ?>
                            <?php while ($stocks = $result->fetch_assoc()): ?>
                                <tr>
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
        <div class="body-content">
            <p>Record Barang Masuk</p>
            <input type="date" id="tanggal_stockin" name="tanggal" class="list-input" placeholder="Tanggal" style="border-radius: 10px;" required readonly>
            <div><i>* Tanggal Otomatis mengikut hari ini</i></div>
            <div class="form-input">
                <div class="submission-left">
                    <div class="form-group">
                        <label>Nomor Nota</label>
                        <input type="text" name="nomor_nota" class="list-input">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Nota</label>
                        <input type="date" name="tanggal_nota" class="list-input">
                    </div>
                    <div class="form-group">
                        <label>Nama Barang</label>
                        <input type="text" name="nama_barang" class="list-input">
                    </div>
                </div>
                <div class="submission-right">
                    <div class="form-group">
                        <label>Harga Barang</label>
                        <input type="text" name="harga_barang" class="list-input">
                    </div>
                    <div class="form-group">
                        <label>Jumlah</label>
                        <input type="number" name="jumlah" class="list-input">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="button-send">Kirim</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="formBarang_keluar" class="tabcontent-invent">
        <div class="body-content">
            <p>Record Barang Keluar</p>
            <input type="date" id="tanggal" name="tanggal" class="list-input" placeholder="Tanggal" style="border-radius: 10px;" required readonly>
            <div><i>* Tanggal Otomatis mengikut hari ini</i></div>
            <div class="form-input">
                <div class="submission-left">
                    <div class="form-group">
                        <label>Pilih Nama Barang</label>
                        <select name="nama_barang" class="list-input" required style="border-radius: 10px;">
                            <option value="" disabled selected hidden>Pilih Nama Barang</option>
                            <?php
                            if ($stokResult->num_rows > 0) {
                                while ($row = $stokResult->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['nama_barang']) . '">' . htmlspecialchars($row['nama_barang']) . '</option>';
                                }
                            } else {
                                echo '<option value="" disabled>Belum ada barang tersedia</option>';
                            }
                            ?>
                        </select>
                        <input type="number" name="jumlah" class="list-input" placeholder="Jumlah" style="border-radius: 10px;" required>
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
                        <div>
                            <button type="submit" id="submitBtn" class="button-send">Kirim</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>