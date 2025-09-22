<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

$kodeUkerSession = $_SESSION['kode_uker'] ?? null;
$isItorNot = (isset($_SESSION['id_jabatan']) && $_SESSION['id_jabatan'] === 'JB6');

//barang it masuk
$queryItStockMasuk = "SELECT bk.*, d.divisi AS nama_divisi FROM barangit_masuk bk 
LEFT JOIN divisi d ON bk.id_divisi = d.id_divisi
ORDER BY bk.id DESC";
$stockItIn = $conn->query($queryItStockMasuk);

//barang it keluar
$queryItStockKeluar = "SELECT bk.*,
d.divisi AS nama_divisi
FROM barangit_keluar bk
LEFT JOIN divisi d ON bk.id_divisi = d.id_divisi
ORDER BY bk.id DESC";
$stockItOut = $conn->query($queryItStockKeluar);

//divisi
$queryDivisi = "SELECT * FROM divisi ORDER BY id_divisi ASC";
$divisi = $conn->query($queryDivisi);


//nama uker
$queryUker = "SELECT * FROM unit_kerja ORDER BY nama_uker";
$uker = $conn->query($queryUker);


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
    function logOpenIt(evt, tabName) {
        var i, tabcontentinvent, tablinks;

        // Sembunyikan semua tab
        tabcontentinvent = document.getElementsByClassName("tabLog-it");
        if (!tabcontentinvent.length) {
            console.warn("Tidak ada elemen tabLog-it. Mungkin bukan halaman IT.");
            return;
        }

        for (i = 0; i < tabcontentinvent.length; i++) {
            tabcontentinvent[i].style.display = "none";
        }

        // Hapus class "active" dari semua tombol
        tablinks = document.getElementsByClassName("tablinkLog-it");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        // Tampilkan tab jika ada
        var tabElem = document.getElementById(tabName);
        if (!tabElem) {
            console.warn(`Tab dengan id '${tabName}' tidak ditemukan. Skip logOpenIt.`);
            return;
        }

        tabElem.style.display = "block";

        // Tambahkan class active ke tombol
        if (evt) {
            evt.currentTarget.className += " active";
        } else {
            var autoBtn = document.querySelector('.tablinkLog-it[onclick*="' + tabName + '"]');
            if (autoBtn) {
                autoBtn.className += " active";
            }
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        const hash = window.location.hash;
        if (hash) {
            const tabName = hash.substring(1); // hapus #
            logOpenIt(null, tabName); // buka tab otomatis
        } else {
            logOpenIt(null, 'logIt_masuk'); // default
        }

        window.logOpenIt = logOpenIt;

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

        //Delete 
        document.querySelectorAll('.btn-delete-logIt').forEach(button => {
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
                        fetch('deleteLogIt.php', {
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
    <div class="content-heading">Inventory Management IT</div>
    <div><i>Manage your inventory, track incoming, and outgoing</i></div>
    <div class="tab-invent">
        <button class="tablinkLog-it active" onclick="logOpenIt(event, 'logIt_masuk')">LOG INGOING</button>
        <button class="tablinkLog-it" onclick="logOpenIt(event, 'logIt_keluar')">LOG OUTGOING</button>
    </div>

    <div id="logIt_masuk" class="tabLog-it">
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
                            <th></th>
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
                                    <td>
                                        <button class="btn-delete-logIt" data-id="<?= $row['id'] ?>" data-table="barangit_masuk" style="background:none; border:none;">
                                            <i class="fa fa-trash" style="color:red;"></i>
                                        </button>
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


    <div id="logIt_keluar" class="tabLog-it">
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