<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

$kodeUkerSession = $_SESSION['kode_uker'] ?? null;
$isItorNot = (isset($_SESSION['id_jabatan']) && $_SESSION['id_jabatan'] === 'JB6');

// Tabel stok
$queryStock = "SELECT * FROM stok_barang_it ORDER BY id ASC";
$stocksIt = $conn->query($queryStock);

//barang it masuk
$queryItStockMasuk = "SELECT * FROM barangit_masuk ORDER BY id ASC";
$stockItIn = $conn->query($queryItStockMasuk);

//barang it keluar
$queryItStockKeluar = "SELECT * FROM barangit_keluar ORDER BY id ASC";
$stockItOut = $conn->query($queryItStockKeluar);

//divisi
$queryDivisi = "SELECT * FROM divisi ORDER BY id_divisi ASC";
$divisi = $conn->query($queryDivisi);


//nama uker
$queryUker = "SELECT * FROM unit_kerja ORDER BY nama_uker";
$uker = $conn->query($queryUker);

// Simpan hasil divisi ke array agar bisa dipakai lebih dari sekali
$divisiList = [];
if ($divisi && $divisi->num_rows > 0) {
    while ($row = $divisi->fetch_assoc()) {
        $divisiList[] = $row;
    }
}

$stocksComputer = [];
if ($stocksIt && $stocksIt->num_rows > 0) {
    while ($row = $stocksIt->fetch_assoc()) {
        $stocksComputer[] = $row;
    }
}

// Simpan hasil unit kerja ke array agar bisa dipakai lebih dari sekali
$ukerList = [];
if ($uker && $uker->num_rows > 0) {
    while ($row = $uker->fetch_assoc()) {
        $ukerList[] = $row;
    }
}


?>
<?php if (isset($_GET['status'])): ?>
    <script src="/js/sweetalert.all.min.js"></script>
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
        <?php elseif ($_GET['status'] === 'incomplete'): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Penginputan salah',
            });
        <?php endif; ?>
    </script>
<?php endif; ?>

<script>
    function openIt(evt, tabName) {
        var i, tabcontentinvent, tablinks;

        // Sembunyikan semua tab
        tabcontentinvent = document.getElementsByClassName("tabcontent-it");
        if (!tabcontentinvent.length) {
            console.warn("Tidak ada elemen tabcontent-it. Mungkin bukan halaman IT.");
            return;
        }

        for (i = 0; i < tabcontentinvent.length; i++) {
            tabcontentinvent[i].style.display = "none";
        }

        // Hapus class "active" dari semua tombol
        tablinks = document.getElementsByClassName("tablink-it");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        // Tampilkan tab jika ada
        var tabElem = document.getElementById(tabName);
        if (!tabElem) {
            console.warn(`Tab dengan id '${tabName}' tidak ditemukan. Skip openIt.`);
            return;
        }

        tabElem.style.display = "block";

        // Tambahkan class active ke tombol
        if (evt) {
            evt.currentTarget.className += " active";
        } else {
            var autoBtn = document.querySelector('.tablink-it[onclick*="' + tabName + '"]');
            if (autoBtn) {
                autoBtn.className += " active";
            }
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        const hash = window.location.hash;
        if (hash) {
            const tabName = hash.substring(1); // hapus #
            openIt(null, tabName); // buka tab otomatis
        } else {
            openIt(null, 'stocks'); // default
        }

        window.openIt = openIt;
    });
</script>


<div class="dashboard-menu">
    <div class="content-heading">Inventory Management IT</div>
    <div><i>Manage your inventory, track incoming, and outgoing</i></div>
    <div class="tab-invent">
        <button class="tablink-it active" onclick="openIt(event, 'stocks')">STOCK</button>
        <button class="tablink-it" onclick="openIt(event, 'it_masuk')">RECORD INCOMING</button>
        <button class="tablink-it" onclick="openIt(event, 'it_keluar')">RECORD OUTGOING</button>
    </div>

    <div id="stocks" class="tabcontent-it" style="display: block;">
        <div class="body-content">
            <div class="sub-menu">
                <p>Inventory List</p>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input">
            </div>
            <div class="table-container">
                <table id="dataTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Merk</th>
                            <th>Hostname</th>
                            <th>Serial Number</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stocksComputer)): ?>
                            <?php foreach ($stocksComputer as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['merk_komputer']) ?></td>
                                    <td><?= htmlspecialchars($item['hostname']) ?></td>
                                    <td><?= htmlspecialchars($item['serial_number']) ?></td>
                                    <td><?= htmlspecialchars($item['jumlah']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center;">Belum ada data stok barang</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="it_masuk" class="tabcontent-it">
        <form action="computerIn.php" method="POST" onsubmit="return showLoading()">
            <div class="body-content">
                <p>Incoming Stock</p>
                <!-- <div><i>* Tanggal Otomatis mengikut hari ini</i></div> -->
                <div class="form-input">
                    <div class="submission-left">
                        <div class="form-group">
                            <label>Merk</label>
                            <input type="text" name="merk_komputer" class="list-input" placeholder="Input Here ...">
                        </div>
                        <div class="form-group">
                            <label>Hostname</label>
                            <input type="text" name="hostname" class="list-input" placeholder="Input Here ...">
                        </div>
                        <div class="form-group">
                            <label>Serial Number</label>
                            <input type="text" name="serial_number" class="list-input" placeholder="Input Here ...">
                        </div>
                    </div>
                    <div class="submission-right">
                        <div class="form-group">
                            <label for="">Choose Divisi</label>
                            <select name="id_divisi" class="list-input" required style="border-radius: 10px;">
                                <option value="" disabled selected hidden>Divisi</option>
                                <?php foreach ($divisiList as $row): ?>
                                    <option value="<?= $row['id_divisi']; ?>">
                                        <?= htmlspecialchars($row['divisi']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Nama Unit Kerja</label>
                            <select name="kode_uker" class="list-input" required style="border-radius: 10px;">
                                <option value="" disabled selected hidden>Pilih Unit Kerja</option>
                                <?php foreach ($ukerList as $row): ?>
                                    <option value="<?= $row['kode_uker']; ?>">
                                        <?= htmlspecialchars($row['nama_uker']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="button-send">Kirim</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="it_keluar" class="tabcontent-it">
        <form action="computerOut.php" method="POST" onsubmit="return showLoading()">
            <div class="body-content">
                <p>Outgoing Stock</p>
                <div class="form-input">
                    <div class="submission-left">
                        <div class="form-group">
                            <label>Pilih Nama Barang</label>
                            <select name="nama_barang" class="list-input" required style="border-radius: 10px;">
                                <option value="" disabled selected hidden>Pilih Nama Barang</option>
                                <?php if (!empty($stocksComputer)): ?>
                                    <?php foreach ($stocksComputer as $item): ?>
                                        <option value="<?= htmlspecialchars($item['merk_komputer']) ?>">
                                            <?= htmlspecialchars($item['merk_komputer']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>Tidak ada stok komputer</option>
                                <?php endif; ?>
                            </select>

                        </div>
                        <div class="form-group">
                            <label>Hostname Baru</label>
                            <input type="text" name="hostname_baru" class="list-input" placeholder="Input Here ..." required>
                        </div>
                        <div class="form-group">
                            <label>Serial Number</label>
                            <input type="text" name="serial_number" class="list-input" placeholder="Input Here ...">
                        </div>
                    </div>
                    <div class="submission-right">
                        <div class="form-group">
                            <label for="">Choose Divisi</label>
                            <select name="id_divisi" class="list-input" required style="border-radius: 10px;">
                                <option value="" disabled selected hidden>Divisi</option>
                                <?php foreach ($divisiList as $row): ?>
                                    <option value="<?= $row['id_divisi']; ?>">
                                        <?= htmlspecialchars($row['divisi']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Nama Unit Kerja</label>
                            <select name="kode_uker" class="list-input" required style="border-radius: 10px;">
                                <option value="" disabled selected hidden>Pilih Unit Kerja</option>
                                <?php foreach ($ukerList as $row): ?>
                                    <option value="<?= $row['kode_uker']; ?>">
                                        <?= htmlspecialchars($row['nama_uker']); ?>
                                    </option>
                                <?php endforeach; ?>
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