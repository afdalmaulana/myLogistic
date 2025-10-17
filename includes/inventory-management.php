<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';



$kodeUkerSession = $_SESSION['kode_uker'] ?? null;
$role = $_SESSION['role'] ?? '';
$user = $_SESSION['user'] ?? '';
$idJabatan = $_SESSION['id_jabatan'] ?? '';

if ($_SESSION['user'] === '90173431') {
    // Gak perlu pakai header jika sudah ada output, tampilkan pesan langsung dan stop
    // echo "<h2>Maaf ini area terlarang, hanya orang ganteng yang bisa masuk</h2>";
    include 'includes/403.php';
    exit;
}

$isAdminlog = ($role === 'admin');
$isBerwenang = in_array($idJabatan, ['JB1', 'JB2', 'JB3', 'JB5', 'JB6']);

// Daftar kode uker untuk masing-masing logistik
$sudirmanCodes = ['0334', '1548', '3050', '3411', '3581', '3582', '3810', '3811', '3815', '3816', '3819', '3821', '3822', '3825', '4986', '7016', '7077'];
$ahmadYaniCodes = ['0050', '1074', '0664', '2086', '2051', '2054', '1436'];
$tamalanreaCodes = ['0403', '7442', '4987', '3823', '3818', '3806', '3419', '3057', '2085', '1831', '1814', '1709', '1554'];


$isAhmadYani = in_array($kodeUkerSession, $ahmadYaniCodes);
$isTamalanrea = in_array($kodeUkerSession, $tamalanreaCodes);
$isSudirman = in_array($kodeUkerSession, $sudirmanCodes);

// Identifikasi user logistik berdasarkan user ID
$isLogistikTamalanrea = ($user === '00220631' || $user === '00028145');
$isLogistikSudirman = ($user === '00344250');
$isLogistikAhmadYani = ($user === '00203119');

// Tangani filter_uker dari GET dan simpan di session
// Ambil filter dari GET, lalu simpan ke SESSION
if (isset($_GET['filter_uker'])) {
    $_SESSION['filter_uker'] = $_GET['filter_uker'];
}
$filterUker = $_SESSION['filter_uker'] ?? '';

// Tentukan kondisi WHERE berdasarkan role
if ($isAdminlog) {
    // Admin atau berwenang bisa lihat semua, atau sesuai filter
    $whereClause = (!empty($filterUker)) ? "kode_uker = '" . $conn->real_escape_string($filterUker) . "'" : "1";
} elseif ($isLogistikSudirman) {
    $allowedUkerList = "'" . implode("','", $sudirmanCodes) . "'";
    $whereClause = (!empty($filterUker) && in_array($filterUker, $sudirmanCodes))
        ? "kode_uker = '" . $conn->real_escape_string($filterUker) . "'"
        : "kode_uker IN ($allowedUkerList)";
} elseif ($isLogistikAhmadYani) {
    $allowedUkerList = "'" . implode("','", $ahmadYaniCodes) . "'";
    $whereClause = (!empty($filterUker) && in_array($filterUker, $ahmadYaniCodes))
        ? "kode_uker = '" . $conn->real_escape_string($filterUker) . "'"
        : "kode_uker IN ($allowedUkerList)";
} elseif ($isLogistikTamalanrea) {
    $allowedUkerList = "'" . implode("','", $tamalanreaCodes) . "'";
    $whereClause = (!empty($filterUker) && in_array($filterUker, $tamalanreaCodes))
        ? "kode_uker = '" . $conn->real_escape_string($filterUker) . "'"
        : "kode_uker IN ($allowedUkerList)";
} else {
    // User biasa hanya bisa lihat miliknya
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
$stokBarangList = [];
if ($stokResult && $stokResult->num_rows > 0) {
    while ($row = $stokResult->fetch_assoc()) {
        $stokBarangList[] = $row;
    }
}
?>
<?php if (isset($_GET['status'])): ?>
    <script src="../js/sweetalert.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($_GET['status'] === 'success'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data Berhasil disimpan'
                }).then(() => {
                    // ✅ Hapus status=success dari URL tanpa reload
                    if (window.history.replaceState) {
                        const cleanUrl = window.location.href.split('?')[0];
                        window.history.replaceState(null, null, cleanUrl + window.location.hash);
                    }
                });
            <?php elseif ($_GET['status'] === 'error'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan dalam form, mohon di ulangi'
                }).then(() => {
                    if (window.history.replaceState) {
                        const cleanUrl = window.location.href.split('?')[0];
                        window.history.replaceState(null, null, cleanUrl + window.location.hash);
                    }
                });
            <?php elseif ($_GET['status'] === 'outstock'): ?>
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock tidak mencukupi',
                }).then(() => {
                    if (window.history.replaceState) {
                        const cleanUrl = window.location.href.split('?')[0];
                        window.history.replaceState(null, null, cleanUrl + window.location.hash);
                    }
                });
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<script>
    const isAhmadYani = <?= $isAhmadYani ? 'true' : 'false' ?>;
    const isTamalanrea = <?= $isTamalanrea ? 'true' : 'false' ?>;
    const isSudirman = <?= $isSudirman ? 'true' : 'false' ?>;
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

        document.querySelectorAll('.btn-keluarkan').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const namaBarang = this.dataset.nama_barang;
                const satuan = this.dataset.satuan;
                const kodeUker = this.dataset.kode_uker;
                const jumlahTersedia = this.dataset.jumlah; // tetap string di sini, nanti parse di preConfirm

                let divisiOptions = '';

                if (isAhmadYani) {
                    divisiOptions = `
              <option value="DJS">Petugas Transaksi</option>
              <option value="Operasional">Operasional</option>
              <option value="TELLER">TELLER</option>
              <option value="TELLER-Pertamina">TELLER PERTAMINA</option>
              <option value="CS">CS</option>
              <option value="HC">Human Capital</option>
              <option value="LOG">Logistik</option>
              <option value="ADK">Petugas Operasional Kredit</option>
              <option value="RMFT">RMFT</option>
              <option value="RMSME">RMSME</option>
              <option value="CRR">CRR</option>
              <option value="BRIGUNA">BRIGUNA</option>
              <option value="KPR">KPR</option>
              <option value="Sekretaris">Sekretaris</option>
              <option value="KCP-Ratulangi">KCP Ratulangi</option>
              <option value="KCP-SlametRiyadi">KCP Slamet Riyadi</option>
              <option value="KCP-Latimojong">KCP Latimojong</option>
              <option value="KCP-YosSudarso">KCP Yos Sudarso</option>
              <option value="KCP-Sentral">KCP Sentral</option>
              <option value="KK-Taspen">KK Taspen</option>
              <option value="KPPN">KPPN</option>
            `;
                } else if (isTamalanrea || isSudirman) {
                    divisiOptions = `
              <option value="MANTRI">Mantri</option>
              <option value="CS">CS</option>
              <option value="TELLER">TELLER</option>
              <option value="RMSME">RMSME</option>
              <option value="ADK">Petugas Operasional Kredit</option>
              <option value="DJS">Petugas Transaksi</option>
              <option value="LOG">Logistik</option>
            `;
                } else {
                    divisiOptions = `
              <option value="" disabled selected hidden>Departemen tidak tersedia</option>
            `;
                }

                Swal.fire({
                    title: 'Record Outgoing Items',
                    html: `
              <table style="text-align:left; width:100%; margin-bottom: 15px;">
                <tr><td><strong>ID</strong></td><td style="font-size:14px;">: ${id}</td></tr>
                <tr><td><strong>Nama Barang</strong></td><td style="font-size:16px;">: ${namaBarang}</td></tr>
                <tr><td><strong>Kode Uker</strong></td><td style="font-size:16px;">: ${kodeUker}</td></tr>
                <tr><td><strong>Jumlah</strong></td><td style="font-size:16px;">: ${jumlahTersedia}</td></tr>
                <tr><td><strong>Satuan</strong></td><td style="font-size:16px;">: ${satuan}</td></tr>
                <tr><td><strong>Note</strong></td><td style="font-size:12px;">: <i>Barang keluar sesuai dengan satuan yang di stock</i></td></tr>
              </table>

              <input id="jumlahKeluar" type="number" min="1" max="${jumlahTersedia}" class="swal2-input" placeholder="Jumlah keluar" style="margin-top:10px; width:60%;">
              <select id="divisi" class="swal2-select" style="width:60%; padding: 6px 10px; border-radius: 8px; margin-top: 5px;">
                <option value="" disabled selected hidden>Choose Department</option>
                ${divisiOptions}
              </select>
            `,
                    showCancelButton: true,
                    confirmButtonText: 'Use Items',
                    preConfirm: () => {
                        const jumlahKeluar = parseInt(document.getElementById('jumlahKeluar').value);
                        const jumlahTersediaInt = parseInt(jumlahTersedia);
                        const divisi = document.getElementById('divisi').value;

                        if (isNaN(jumlahKeluar) || jumlahKeluar <= 0) {
                            Swal.showValidationMessage('Jumlah keluar harus lebih dari 0');
                            return false;
                        }
                        if (jumlahKeluar > jumlahTersediaInt) {
                            Swal.showValidationMessage(`Jumlah keluar tidak boleh lebih dari ${jumlahTersediaInt}`);
                            return false;
                        }
                        if (!divisi) {
                            Swal.showValidationMessage('Departemen harus dipilih');
                            return false;
                        }

                        return {
                            jumlah: jumlahKeluar,
                            divisi
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const data = {
                            nama_barang: namaBarang,
                            jumlah: result.value.jumlah,
                            satuan: satuan,
                            divisi: result.value.divisi
                        };

                        fetch('stockOut_connect.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: new URLSearchParams(data)
                            })
                            .then(response => response.json())
                            .then(res => {
                                if (res.status === 'success') {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: res.message,
                                        icon: 'success',
                                        timer: 2000,
                                        timerProgressBar: true,
                                        showConfirmButton: false,
                                        didClose: () => location.reload()
                                    });
                                } else {
                                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                                }
                            })
                            .catch(() => {
                                Swal.fire('Gagal', 'Tidak dapat menghubungi server', 'error');
                            });
                    }
                });
            });
        });


        document.querySelectorAll('.btn-add-stock').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const namaBarang = this.dataset.nama_barang;
                const satuan = this.dataset.satuan;
                const kodeUker = this.dataset.kode_uker;
                const jumlahTersedia = this.dataset.jumlah; // tetap string di sini, nanti parse di preConfirm
                Swal.fire({
                    title: 'Add Items',
                    html: `
              <table style="text-align:left; width:100%; margin-bottom: 15px;">
                <tr><td><strong>Nama Barang</strong></td><td style="font-size:16px;">: ${namaBarang}</td></tr>
                <tr><td><strong>Kode Uker</strong></td><td style="font-size:16px;">: ${kodeUker}</td></tr>
                <tr><td><strong>Jumlah</strong></td><td style="font-size:16px;">: ${jumlahTersedia}</td></tr>
                <tr><td><strong>Satuan</strong></td><td style="font-size:16px;">: ${satuan}</td></tr>
              </table>

              <input id="jumlahMasuk" type="number" min="1" class="swal2-input" placeholder="Input Jumlah" style="margin-top:10px; width:60%;">
              
            `,
                    showCancelButton: true,
                    confirmButtonText: 'Add Items',
                    preConfirm: () => {
                        const jumlahMasuk = parseInt(document.getElementById('jumlahMasuk').value);
                        const jumlahTersediaInt = parseInt(jumlahTersedia);

                        if (isNaN(jumlahMasuk) || jumlahMasuk <= 0) {
                            Swal.showValidationMessage('Jumlah tambahan stock harus lebih dari 0');
                            return false;
                        }

                        return {
                            jumlah: jumlahMasuk,
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const data = {
                            nama_barang: namaBarang,
                            jumlah: result.value.jumlah,
                            satuan: satuan,
                        };

                        fetch('stockIn_connect.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: new URLSearchParams(data)
                            })
                            .then(response => response.json())
                            .then(res => {
                                if (res.status === 'success') {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: res.message,
                                        icon: 'success',
                                        timer: 2000,
                                        timerProgressBar: true,
                                        showConfirmButton: false,
                                        didClose: () => location.reload()
                                    });
                                } else {
                                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                                }
                            })
                            .catch(() => {
                                Swal.fire('Gagal', 'Tidak dapat menghubungi server', 'error');
                            });
                    }
                });
            });
        });

        document.querySelectorAll('.btn-add-new-items').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const namaBarang = this.dataset.nama_barang;
                const satuan = this.dataset.satuan;
                const kodeUker = this.dataset.kode_uker;
                Swal.fire({
                    title: 'Add Items',
                    html: `
              <table style="text-align:left; width:100%; margin-bottom: 15px;">
                 <input id="swal-namaBarang" type="text" min="1" class="swal2-input" placeholder="Input Nama Barang" style="margin-top:10px; width:60%;">
                  <input id="quantityItems" type="number" min="1" class="swal2-input" placeholder="Input Jumlah" style="margin-top:10px; width:60%;">
                  <select id="swal-satuan" class="swal2-select" style="width:60%; padding: 6px 10px; border-radius: 8px; margin-top: 5px;">
                <option value="" disabled selected hidden>Choose Type</option>
                <option value="dos">dos</option>
                                <option value="pcs">pcs</option>
                                <option value="ikat">ikat</option>
                                <option value="rim">rim</option>
                                <option value="bungkus">bungkus</option>
              </select>
              </table>

            `,
                    showCancelButton: true,
                    confirmButtonText: 'Add New Items',
                    preConfirm: () => {
                        const namaBarang = document.getElementById('swal-namaBarang').value;
                        const quantityItems = parseInt(document.getElementById('quantityItems').value);
                        const satuanItem = document.getElementById('swal-satuan').value;

                        if (isNaN(quantityItems) || quantityItems <= 0) {
                            Swal.showValidationMessage('Jumlah tambahan stock harus lebih dari 0');
                            return false;
                        }

                        return {
                            nama_barang: namaBarang,
                            jumlah: quantityItems,
                            satuan: satuanItem

                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const data = {
                            nama_barang: result.value.nama_barang,
                            jumlah: result.value.jumlah,
                            satuan: result.value.satuan,
                        };

                        fetch('stockIn_connect.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: new URLSearchParams(data)
                            })
                            .then(response => response.json())
                            .then(res => {
                                if (res.status === 'success') {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: res.message,
                                        icon: 'success',
                                        timer: 2000,
                                        timerProgressBar: true,
                                        showConfirmButton: false,
                                        didClose: () => location.reload()
                                    });
                                } else {
                                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
                                }
                            })
                            .catch(() => {
                                Swal.fire('Gagal', 'Tidak dapat menghubungi server', 'error');
                            });
                    }
                });
            });
        });


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
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire('Dihapus!', 'Data berhasil dihapus.', 'success').then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat menghapus.', 'error');
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

        document.querySelectorAll('.editStocks').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const nama_barang = btn.dataset.nama_barang;
                const jumlah = btn.dataset.jumlah;

                Swal.fire({
                    title: 'Edit Stok',
                    html: `
                <input id="swal-nama_barang" class="swal2-input" value="${nama_barang}" placeholder="Nama Barang">
                <input id="swal-jumlah" class="swal2-input" value="${jumlah}" placeholder="Jumlah" type="number">
            `,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    preConfirm: () => {
                        const updatedNama = document.getElementById('swal-nama_barang').value;
                        const updatedJumlah = document.getElementById('swal-jumlah').value;

                        // Validasi sederhana
                        if (updatedNama === '' || updatedJumlah === '') {
                            Swal.showValidationMessage('Semua field wajib diisi!');
                            return false;
                        }

                        return {
                            id: id,
                            nama_barang: updatedNama,
                            jumlah: updatedJumlah
                        };
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch('updateStockNew.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(result.value)
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Berhasil', 'Stok berhasil diperbarui!', 'success')
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectBarang = document.getElementById('selectBarang');
        const inputBarangBaru = document.getElementById('inputBarangBaru');
        const btnToggle = document.getElementById('btnToggleInput');

        let isAddingNew = false;

        // ✅ Set default name saat pertama kali halaman dimuat
        selectBarang.name = 'nama_barang';
        inputBarangBaru.name = '';

        btnToggle.addEventListener('click', function() {
            isAddingNew = !isAddingNew;

            if (isAddingNew) {
                // Mode: Tambah Barang Baru
                selectBarang.style.display = 'none';
                inputBarangBaru.style.display = 'block';
                btnToggle.textContent = 'Kembali Pilih Barang yang Ada';

                // ⚠️ Aktifkan input text, nonaktifkan select
                selectBarang.name = '';
                inputBarangBaru.name = 'nama_barang';
                selectBarang.value = '';
            } else {
                // Mode: Pilih Barang dari Dropdown
                selectBarang.style.display = 'block';
                inputBarangBaru.style.display = 'none';
                btnToggle.textContent = 'Tambah Barang Baru';

                // ⚠️ Aktifkan select, nonaktifkan input text
                inputBarangBaru.name = '';
                selectBarang.name = 'nama_barang';
                inputBarangBaru.value = '';
            }
        });
    });
</script>






<div class="dashboard-menu">
    <div class="content-heading">Inventory Management</div>
    <div><i>Manage your inventory, track incoming, and outgoing</i></div>
    <div class="tab-invent">
        <button class="tablink-invent active" onclick="openInvent(event, 'stocks')">STOCK</button>
        <button class="tablink-invent" onclick="openInvent(event, 'formBarang_masuk')">RECORD INCOMING</button>
        <!-- <button class="tablink-invent" onclick="openInvent(event, 'formBarang_keluar')">RECORD OUTGOING</button> -->
    </div>

    <div id="stocks" class="tabcontent-invent" style="display: block;">
        <div class="body-content">
            <div class="sub-menu" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <?php if ($isAdminlog || $isLogistikSudirman || $isLogistikAhmadYani || $isLogistikTamalanrea): ?>
                        <form method="GET" style="display: inline-block;">
                            <input type="hidden" name="page" value="inventory-management">
                            <select name="filter_uker" onchange="this.form.submit()" class="list-select" style="padding: 5px;">
                                <option value="">Filter Kode Uker</option>
                                <?php
                                if ($isAdminlog) {
                                    // Admin: lihat semua kode uker
                                    $query = "SELECT DISTINCT kode_uker FROM stok_barang ORDER BY kode_uker";
                                } else {
                                    // Khusus logistik: tampilkan kode uker sesuai akses
                                    if ($isLogistikSudirman) {
                                        $allowedCodes = $sudirmanCodes;
                                    } elseif ($isLogistikAhmadYani) {
                                        $allowedCodes = $ahmadYaniCodes;
                                    } elseif ($isLogistikTamalanrea) {
                                        $allowedCodes = $tamalanreaCodes;
                                    } else {
                                        $allowedCodes = [];
                                    }

                                    if (!empty($allowedCodes)) {
                                        $codesList = "'" . implode("','", $allowedCodes) . "'";
                                        $query = "SELECT DISTINCT kode_uker FROM stok_barang WHERE kode_uker IN ($codesList) ORDER BY kode_uker";
                                    } else {
                                        $query = "SELECT DISTINCT kode_uker FROM stok_barang WHERE 1=0";
                                    }
                                }

                                $ukerQuery = $conn->query($query);
                                while ($uker = $ukerQuery->fetch_assoc()):
                                    $selected = ($filterUker === $uker['kode_uker']) ? 'selected' : '';
                                    echo "<option value=\"{$uker['kode_uker']}\" $selected>{$uker['kode_uker']}</option>";
                                endwhile;
                                ?>
                            </select>
                        </form>
                    <?php endif; ?>

                </div>
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari ... " class="list-input">
            </div>
            <div class="table-container">
                <div><button class="btn-add-new-items">Add New Stock Items</button></div>
                <table id="dataTable" style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kode Uker</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th></th>
                            <?php if ($isAdminlog): ?>
                                <th>Aksi</th>
                                <th>Edit</th>
                            <?php endif; ?>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stocks->num_rows > 0): ?>
                            <?php while ($row = $stocks->fetch_assoc()): ?>
                                <?php if ($row['jumlah'] > 0): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['id']) ?></td>
                                        <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                        <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                        <td><?= isset($row['satuan']) ? htmlspecialchars($row['satuan']) : '' ?></td>
                                        <td>
                                            <div style="display: flex; flex-direction: row; gap: 8px; align-items: center; justify-content: center;">
                                                <!-- Tombol Hapus -->
                                                <div class="tooltip-wrapper">
                                                    <button class="btn-delete-stock"
                                                        data-id="<?= $row['id'] ?>"
                                                        style="background: none; border: none; font-size: 16px;"
                                                        title="Hapus">
                                                        <i class="fa fa-trash" style="color: red;"></i>
                                                    </button>
                                                    <div class="tooltiptext">Delete item</div>
                                                </div>

                                                <!-- Tombol Keluarkan dengan Tooltip -->
                                                <div class="tooltip-wrapper">
                                                    <button class="btn-keluarkan"
                                                        data-id="<?= $row['id'] ?>"
                                                        data-nama_barang="<?= htmlspecialchars($row['nama_barang']) ?>"
                                                        data-satuan="<?= htmlspecialchars($row['satuan']) ?>"
                                                        data-kode_uker="<?= htmlspecialchars($row['kode_uker']) ?>"
                                                        data-jumlah="<?= $row['jumlah'] ?>">
                                                        <i class="fa fa-mail-forward"></i>
                                                    </button>
                                                    <div class="tooltiptext">Use item</div>
                                                </div>

                                                <!-- Tombol Tambah -->
                                                <div class="tooltip-wrapper">
                                                    <button class="btn-add-stock"
                                                        data-id="<?= $row['id'] ?>"
                                                        data-nama_barang="<?= htmlspecialchars($row['nama_barang']) ?>"
                                                        data-satuan="<?= htmlspecialchars($row['satuan']) ?>"
                                                        data-kode_uker="<?= htmlspecialchars($row['kode_uker']) ?>"
                                                        data-jumlah="<?= $row['jumlah'] ?>">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                    <div class="tooltiptext">Add ttem</div>
                                                </div>

                                            </div>
                                        </td>
                                        <td></td>
                                        <?php if ($isAdminlog): ?>
                                            <td>
                                                <button class="btn-delete-stock" data-id="<?= $row['id'] ?>" style="background:none; border:none;" title="Hapus">
                                                    <i class="fa fa-trash" style="color:red;"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <button class="editStocks"
                                                    data-id="<?= $row['id'] ?>"
                                                    data-nama_barang="<?= $row['nama_barang'] ?>"
                                                    data-jumlah="<?= $row['jumlah'] ?>">
                                                    <i class="fa fa-edit" style="font-size:22px"></i>
                                                </button>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endif; ?>

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
                            <!-- SELECT BARANG -->
                            <select id="selectBarang" class="list-input" style="border-radius: 10px;">
                                <option value="" disabled selected hidden>Pilih Barang yang Sudah Ada</option>
                                <?php
                                if (!empty($stokBarangList)) {
                                    foreach ($stokBarangList as $row) {
                                        echo '<option value="' . htmlspecialchars($row['nama_barang']) . '">' . htmlspecialchars($row['nama_barang']) . '</option>';
                                    }
                                }
                                ?>
                            </select>

                            <!-- INPUT BARANG BARU -->
                            <input type="text" id="inputBarangBaru" class="list-input" placeholder="Masukkan Nama Barang Baru" style="display:none; margin-top:10px;">

                            <!-- TOMBOL TOGGLE -->
                            <button type="button" id="btnToggleInput" class="button-send" style="margin-top:10px; background: #1d4d82;">
                                Tambah Barang Baru
                            </button>


                        </div>

                    </div>
                    <div class="submission-right">
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="jumlah" class="list-input" placeholder="Masukkan Jumlah">
                        </div>
                        <div class="form-group">
                            <label>Choose Type</label>
                            <select name="satuan" class="list-input" required style="border-radius: 10px;">
                                <option value="" disabled selected hidden>Choose</option>
                                <option value="dos">dos</option>
                                <option value="pcs">pcs</option>
                                <option value="pcs">ikat</option>
                                <option value="pcs">rim</option>
                                <option value="pcs">bungkus</option>
                            </select>
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
                                if (!empty($stokBarangList)) {
                                    foreach ($stokBarangList as $row) {
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
                                <option value="DJS">Petugas Transaksi</option>
                                <option value="Operasional">Operasional</option>
                                <option value="TELLER">TELLER</option>
                                <option value="TELLER-Pertamina">TELLER PERTAMINA</option>
                                <option value="CS">CS</option>
                                <option value="HC">Human Capital</option>
                                <option value="LOG">Logistik</option>
                                <option value="ADK">Petugas Operasional Kredit</option>
                                <option value="RMFT">RMFT</option>
                                <option value="RMSME">RMSME</option>
                                <option value="CRR">CRR</option>
                                <option value="BRIGUNA">BRIGUNA</option>
                                <option value="KPR">KPR</option>
                                <option value="Sekretaris">Sekretaris</option>
                                <option value="KCP-Ratulangi">KCP Ratulangi</option>
                                <option value="KCP-SlametRiyadi">KCP Slamet Riyadi</option>
                                <option value="KCP-Latimojong">KCP Latimojong</option>
                                <option value="KCP-YosSudarso">KCP Yos Sudarso</option>
                                <option value="KCP-Sentral">KCP Sentral</option>
                                <option value="KK-Taspen">KK Taspen</option>
                                <option value="KPPN">KPPN</option>
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