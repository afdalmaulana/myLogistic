<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

$kodeUkerSession = $_SESSION['kode_uker'] ?? null;
$isItorNot = (isset($_SESSION['id_jabatan']) && $_SESSION['id_jabatan'] === 'JB6');

// Tabel stok
$queryStock = "SELECT * FROM stok_barang_it ORDER BY id DESC";
$stocksIt = $conn->query($queryStock);

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
            if (window.location.search.includes("status=success")) {
                setTimeout(() => {
                    // Hapus query string dan perbarui URL
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status');
                    window.history.replaceState({}, document.title, url.pathname + url.search);
                }, 100); // beri waktu alert jalan dulu
            }
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
                title: 'Data Tidak Lengkap',
            });
        <?php elseif ($_GET['status'] === 'notfound'): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Stock Tidak Ada',
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

        document.querySelectorAll('.editStocksIT').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const merk_komputer = btn.dataset.merk_komputer;
                const hostname = btn.dataset.hostname;
                const serial_number = btn.dataset.serial_number;
                const jumlah = btn.dataset.jumlah;

                Swal.fire({
                    title: 'Edit Stok',
                    html: `
                <input id="swal-merk_komputer" class="swal2-input" value="${merk_komputer}" placeholder="Nama Barang">
                <input id="swal-hostname" class="swal2-input" value="${hostname}" placeholder="Hostname">
                <input id="swal-serial_number" class="swal2-input" value="${serial_number}" placeholder="Serial Number">
                <input id="swal-jumlah" class="swal2-input" value="${jumlah}" placeholder="Jumlah" type="number">
            `,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    preConfirm: () => {
                        const updatedMerk = document.getElementById('swal-merk_komputer').value;
                        const updatedHost = document.getElementById('swal-hostname').value;
                        const updatedSerialNumber = document.getElementById('swal-serial_number').value;
                        const updatedJumlah = document.getElementById('swal-jumlah').value;

                        // Validasi sederhana
                        if (updatedMerk === '' || updatedHost === '') {
                            Swal.showValidationMessage('Semua field wajib diisi!');
                            return false;
                        }

                        return {
                            id: id,
                            merk_komputer: updatedMerk,
                            hostname: updatedHost,
                            serial_number: updatedSerialNumber,
                            jumlah: updatedJumlah
                        };
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch('updateStockITNew.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(result.value)
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Berhasil', 'Stok IT berhasil diperbarui!', 'success')
                                        .then(() => location.reload());
                                } else {
                                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
                                }
                            })
                            .catch(err => {
                                Swal.fire('Error', 'Gagal menghubungi server', 'error');
                                console.error(err);
                            });
                    }
                });
            });
        });
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
            <div class="sub-menu" style="display: flex; justify-content: space-between; align-items: center;">
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
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stocksComputer)): ?>
                            <?php foreach ($stocksComputer as $item): ?>
                                <?php if ($item['jumlah'] > 0): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['merk_komputer']) ?></td>
                                        <td><?= htmlspecialchars($item['hostname']) ?></td>
                                        <td><?= htmlspecialchars($item['serial_number']) ?></td>
                                        <td><?= htmlspecialchars($item['jumlah']) ?></td>
                                        <td>
                                            <button class="editStocksIT"
                                                data-id="<?= $item['id'] ?>"
                                                data-merk_komputer="<?= $item['merk_komputer'] ?>"
                                                data-hostname="<?= $item['hostname'] ?>"
                                                data-serial_number="<?= $item['serial_number'] ?>"
                                                data-jumlah="<?= $item['jumlah'] ?>">
                                                <i class="fa fa-edit" style="font-size:22px"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
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
                            <div style="display: flex; flex-direction:row; gap:6px">
                                <label>Hostname</label>
                                <label for=""><i style="font-size: 10px;color:red;">Kalau Hostname tidak ada, isi "Stok Gudang"</i></label>
                            </div>
                            <input type="text" name="hostname" class="list-input" placeholder="Input Here ...">
                        </div>
                        <div class="form-group">
                            <div style="display: flex; flex-direction:row; gap:6px">
                                <label>Serial Number</label>
                                <label for=""><i style="font-size: 10px;color:red;">Kalau tidak ada isi "-" (Tanda Kurang)</i></label>
                            </div>
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
                            <label for="">Jumlah</label>
                            <input type="number" name="jumlah" class="list-input" placeholder="Input Here ...">
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
                            <select name="merk_komputer" class="list-input" required style="border-radius: 10px;">
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
                            <div style="display: flex; flex-direction:row; gap:6px">
                                <label>Hostname Baru</label>
                                <label for=""><i style="font-size: 10px;color:red;">Kalau Hostname tidak ada, isi keterangan</i></label>
                            </div>
                            <input type="text" name="hostname_baru" class="list-input" placeholder="Input Here ...">
                        </div>
                        <div class="form-group">
                            <div style="display: flex; flex-direction:row; gap:6px">
                                <label>Serial Number</label>
                                <label for=""><i style="font-size: 10px;color:red;">Kalau tidak ada isi, PN Pekerja yang pinjam</i></label>
                            </div>
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
                            <label for="">Jumlah</label>
                            <input type="number" name="jumlah" class="list-input" placeholder="Input Here ...">
                        </div>
                        <div class="form-group">
                            <button type="submit" id="submitBtn" class="button-send">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div id="logIt_masuk" class="tabcontent-it">
        <div class="body-content">
            <div class="sub-menu" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin-bottom: 5px;">Log Record Masuk</p>
                    <a href="export_barangKeluar.php" class="list-select" style="padding:5px; text-decoration:none;">Download Excel</a>
                </div>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input" style="width: 200px;">
            </div>

            <div class="table-container">
                <table id="dataTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Merk Komputer</th>
                            <th>Hostname</th>
                            <th>Serial Number</th>
                            <th>Divisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stockItIn->num_rows > 0): ?>
                            <?php while ($row = $stockItIn->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                    <td><?= htmlspecialchars($row['merk_komputer']) ?></td>
                                    <td><?= htmlspecialchars($row['hostname']) ?></td>
                                    <td><?= htmlspecialchars($row['serial_number']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_divisi']) ?></td>

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


    <div id="logIt_keluar" class="tabcontent-it">
        <div class="body-content">
            <div class="sub-menu" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin-bottom: 5px;">Log Record Keluar</p>
                    <a href="export_barangKeluar.php" class="list-select" style="padding:5px; text-decoration:none;">Download Excel</a>
                </div>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input" style="width: 200px;">
            </div>

            <div class="table-container">
                <table id="dataTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Merk Komputer</th>
                            <th>Hostname</th>
                            <th>Serial Number / PN</th>
                            <th>Divisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stockItOut->num_rows > 0): ?>
                            <?php while ($row = $stockItOut->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                    <td><?= htmlspecialchars($row['merk_komputer']) ?></td>
                                    <td><?= htmlspecialchars($row['hostname_baru']) ?></td>
                                    <td><?= htmlspecialchars($row['serial_number']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_divisi']) ?></td>

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