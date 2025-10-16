<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

$successMessage = '';
$errorMessage = '';


// Filter query sesuai role
// ------------------- PERBAIKAN FILTER QUERY -----------------------
$sudirmanCodes = ['0334', '1548', '3050', '3411', '3581', '3582', '3810', '3811', '3815', '3816', '3819', '3821', '3822', '3825', '4986', '7016', '7077'];
$ahmadYaniCodes = ['0050', '1074', '0664', '2086', '2051', '2054', '1436'];
$tamalanreaCodes = ['0403', '7442', '4987', '3823', '3818', '3806', '3419', '3057', '2085', '1831', '1814', '1709', '1554'];

$role = $_SESSION['role'] ?? '';
$user = $_SESSION['user'] ?? '';
$kodeUker = $_SESSION['kode_uker'] ?? '';
$idJabatan = $_SESSION['id_jabatan'] ?? '';

$isAdmin = $role === 'admin';
$isKanwil = $idJabatan === 'JB3';
$pnLogistikSudirman = $user === '00344250';
$pnLogistikAyani = $user === '00203119';
$pnLogistikTamalanrea = $user === '00220631';
$isSudirmanAccess = in_array($user, ['00068898', '00031021']);
$isAyaniAccess = in_array($user, ['00008839', '00030413']);
$isTamalanreaAccess = in_array($user, ['00028145', '00062209']);

$isLogistikSudirman = $pnLogistikSudirman || $isSudirmanAccess;
$isLogistikAhmadYani = $pnLogistikAyani || $isAyaniAccess;
$isLogistikTamalanrea = $pnLogistikTamalanrea || $isTamalanreaAccess;

if ($isAdmin || $isKanwil) {
    // Admin, 0050, dan Kanwil bisa lihat semua
    $query = "
        SELECT p.*, a.nama_anggaran 
        FROM pengajuan p
        LEFT JOIN anggaran a ON p.id_anggaran = a.id_anggaran
        ORDER BY p.tanggal_pengajuan DESC
    ";
} elseif ($isLogistikSudirman || $isSudirmanAccess) {
    // Logistik Sudirman hanya bisa lihat unit Sudirman
    $inClause = "'" . implode("','", $sudirmanCodes) . "'";
    $query = "
        SELECT p.*, a.nama_anggaran 
        FROM pengajuan p
        LEFT JOIN anggaran a ON p.id_anggaran = a.id_anggaran
        WHERE p.kode_uker IN ($inClause)
        ORDER BY p.tanggal_pengajuan DESC
    ";
} elseif ($isLogistikAhmadYani || $isAyaniAccess) {
    // Logistik Ahmad Yani hanya bisa lihat unit Ahmad Yani
    $inClause = "'" . implode("','", $ahmadYaniCodes) . "'";
    $query = "
        SELECT p.*, a.nama_anggaran 
        FROM pengajuan p
        LEFT JOIN anggaran a ON p.id_anggaran = a.id_anggaran
        WHERE p.kode_uker IN ($inClause)
        ORDER BY p.tanggal_pengajuan DESC
    ";
} elseif ($isLogistikTamalanrea || $isTamalanreaAccess) {
    // Logistik Tamalanrea hanya bisa melihat uker Tamalanrea
    $inClause = "'" . implode("','", $tamalanreaCodes) . "'";
    $query = "
        SELECT p.*, a.nama_anggaran 
        FROM pengajuan p
        LEFT JOIN anggaran a ON p.id_anggaran = a.id_anggaran
        WHERE p.kode_uker IN ($inClause)
        ORDER BY p.tanggal_pengajuan DESC
    ";
} else {
    // User biasa hanya bisa lihat pengajuan dari unit sendiri
    $kodeUkerEscaped = $conn->real_escape_string($kodeUker);
    $query = "
        SELECT p.*, a.nama_anggaran 
        FROM pengajuan p
        LEFT JOIN anggaran a ON p.id_anggaran = a.id_anggaran
        WHERE p.kode_uker = '$kodeUkerEscaped'
        ORDER BY p.tanggal_pengajuan DESC
    ";
}


$result = $conn->query($query);
$requestCount = 0;
$forwardCount = 0;
$incompleteCount = 0;
$completeCount = 0;

$result->data_seek(0);

while ($row = $result->fetch_assoc()) {
    $kode_uker = $row['kode_uker'];
    $status = strtolower($row['status']);
    $status_sisa = strtolower($row['status_sisa'] ?? '');
    $sisa_jumlah = (int)($row['sisa_jumlah'] ?? 0);

    // Cek apakah baris ini relevan untuk user logistik
    $isAllowed = false;
    if ($isLogistikSudirman && in_array($kode_uker, $sudirmanCodes)) {
        $isAllowed = true;
    } elseif ($isLogistikAhmadYani && in_array($kode_uker, $ahmadYaniCodes)) {
        $isAllowed = true;
    } elseif ($isLogistikTamalanrea && in_array($kode_uker, $tamalanreaCodes)) {
        $isAllowed = true;
    } elseif (!$isLogistikSudirman && !$isLogistikAhmadYani && !$isLogistikTamalanrea) {
        // Jika bukan user logistik, tampilkan semua
        $isAllowed = true;
    }

    if (!$isAllowed) {
        continue; // Lewati baris jika tidak sesuai
    }

    // Hitung request
    if ($status === 'pending') {
        $requestCount++;
    }

    // Hitung incomplete
    if ($status === 'forward') {
        $forwardCount++;
    }

    if ($status === 'approved' && $sisa_jumlah > 0) {
        $incompleteCount++;
    }

    // Hitung complete
    if (($status === 'approved' && $sisa_jumlah === 0) || $status === 'rejected') {
        $completeCount++;
    }
}
?>

<script>
    const isLogistikAyani = <?= ($pnLogistikAyani ? 'true' : 'false') ?>;
    const isLogistikSudirman = <?= ($pnLogistikSudirman ? 'true' : 'false') ?>;
    const isLogistikTamalanrea = <?= ($pnLogistikTamalanrea ? 'true' : 'false') ?>;
    document.addEventListener("DOMContentLoaded", function() {

        // Fungsi buka tab
        function openTab(evt, tabName) {
            var tabContent = document.getElementsByClassName("tabscontent");
            if (!tabContent.length) {
                console.warn("Tidak ada tab content ditemukan, skip openTab");
                return;
            }

            for (let i = 0; i < tabContent.length; i++) {
                tabContent[i].style.display = "none";
            }

            var tablinks = document.getElementsByClassName("tabslinks");
            for (let i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            var tabElem = document.getElementById(tabName);
            if (!tabElem) {
                tabName = 'incomplete';
                tabElem = document.getElementById(tabName);
            }

            if (!tabElem) {
                console.warn("Default tab '" + tabName + "' tidak ditemukan, skip openTab");
                return;
            }

            tabElem.style.display = "block";

            if (evt) {
                evt.currentTarget.className += " active";
            } else {
                var autoBtn = document.querySelector('.tabslinks[onclick*="' + tabName + '"]');
                if (autoBtn) {
                    autoBtn.className += " active";
                }
            }
        }

        // Cek hash di URL dan buka tab sesuai
        const hashPengajuan = window.location.hash;
        const allowedTabs = ['request', 'forward', 'incomplete', 'approved'];
        if (hashPengajuan) {
            let tabName = hashPengajuan.substring(1);
            if (!allowedTabs.includes(tabName)) {
                tabName = 'forward';
            }
            openTab(null, tabName);
        } else {
            openTab(null, 'forward');
        }

        // Optional: expose openTab ke global (jika dipakai di HTML onclick)
        window.openTab = openTab;
    });

    //button approval kanwil
    document.addEventListener('DOMContentLoaded', function() {
        function getSelectedIds() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            return Array.from(checkboxes).map(cb => cb.value);
        }

        function handleBulkAction(action) {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) {
                Swal.fire('Peringatan', 'Pilih minimal satu pengajuan terlebih dahulu.', 'warning');
                return;
            }

            const actionText = action === 'approve' ? 'Approve' : 'Reject';

            Swal.fire({
                title: 'Konfirmasi',
                text: `Yakin ingin ${actionText} ${selectedIds.length} pengajuan terpilih?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: `Ya, ${actionText}`,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim data dengan format URL-encoded, seperti tombol tunggal
                    fetch("update-submissionHandler.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                ids: selectedIds.join(','), // Kirim sebagai string dipisah koma
                                status: action === 'approve' ? 'approved' : 'rejected'
                            })
                        })
                        .then(res => res.text())
                        .then(msg => {
                            Swal.fire('Sukses', msg, 'success').then(() => {
                                location.reload();
                            });
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Terjadi kesalahan saat menghubungi server.', 'error');
                        });
                }
            });
        }

        document.getElementById('approve-selected')?.addEventListener('click', () => handleBulkAction('approve'));
        document.getElementById('reject-selected')?.addEventListener('click', () => handleBulkAction('reject'));
    });



    //BUTTON ACTION
    document.addEventListener("DOMContentLoaded", () => {
        const globalMenu = document.getElementById("global-actions");

        document.querySelectorAll(".btn-detail").forEach(button => {
            button.addEventListener("click", () => {
                const data = button.dataset;

                function getStatusClass(status) {
                    switch (status.toLowerCase()) {
                        case 'pending':
                            return 'status-pending';
                        case 'approved':
                            return 'status-approved';
                        case 'rejected':
                            return 'status-rejected';
                        case 'forward':
                            return 'status-forward';
                        default:
                            return '';
                    }
                }

                const statusClass = getStatusClass(data.status);


                Swal.fire({
                    title: `Detail Pengajuan`,
                    html: `
                    <table style="text-align:left; width:100%;">
                        <tr><td><strong>ID</strong></td><td>: ${data.id}</td></tr>
                        <tr><td><strong>Kode Pengajuan</strong></td><td>: ${data.kode}</td></tr>
                        <tr><td><strong>Kode Uker</strong></td><td>: ${data.kodeUker}</td></tr>
                        <tr><td><strong>Tanggal Pengajuan</strong></td><td>: ${data.tanggal}</td></tr>
                        <tr><td><strong>Nama Barang</strong></td><td>: ${data.namaBarang}</td></tr>
                         <tr><td class="status-cell"><strong>Status</strong></td><td class="${statusClass}">: ${data.status}</td></tr>
                        <tr><td><strong>Jumlah</strong></td><td>: ${data.jumlah} ${data.satuan}</td></tr>
                        <tr><td><strong>Anggaran</strong></td><td>: ${data.anggaran}</td></tr>
                        <tr><td><strong>Nominal</strong></td><td>: ${data.nominal}</td></tr>
                        <tr><td><strong>Keterangan</strong></td><td>: ${data.keterangan}</td></tr>
                    </table>
                `,
                    width: 600,
                    confirmButtonText: 'Tutup'
                });
            });
        });

        document.querySelectorAll(".btn-detailForward").forEach(button => {
            button.addEventListener("click", () => {
                const data = button.dataset;

                function getStatusClass(status) {
                    switch (status.toLowerCase()) {
                        case 'pending':
                            return 'status-pending';
                        case 'approved':
                            return 'status-approved';
                        case 'rejected':
                            return 'status-rejected';
                        case 'forward':
                            return 'status-forward';
                        default:
                            return '';
                    }
                }

                const statusClass = getStatusClass(data.status);


                Swal.fire({
                    title: `Detail Pengajuan`,
                    html: `
                    <table style="text-align:left; width:100%;">
                        <tr><td><strong>ID</strong></td><td style="font-size:12px;">: ${data.id}</td></tr>
                        <tr><td><strong>Kode Pengajuan</strong></td><td style="font-size:12px;">: ${data.kode}</td></tr>
                        <tr><td><strong>Kode Uker</strong></td><td style="font-size:12px;">: ${data.kodeUker}</td></tr>
                        <tr><td><strong>Tanggal Pengajuan</strong></td><td style="font-size:12px;">: ${data.tanggal}</td></tr>
                        <tr><td><strong>Nama Barang</strong></td><td style="font-size:12px;">: ${data.namaBarang}</td></tr>
                         <tr><td class="status-cell"><strong>Status</strong></td><td style="font-size:12px;" class="${statusClass}">: ${data.status}</td></tr>
                        <tr><td><strong>Jumlah</strong></td><td style="font-size:12px;">: ${data.jumlah} ${data.satuan}</td></tr>
                        <tr><td><strong>Sisa</strong></td><td style="font-size:12px;">: ${data.sisa_jumlah}</td></tr>
                        <tr><td><strong>Harga Barang</strong></td><td style="font-size:12px;">: ${data.price}</td></tr>
                        <tr><td><strong>Anggaran</strong></td><td style="font-size:12px;">: ${data.anggaran}</td></tr>
                        <tr><td><strong>Keterangan</strong></td><td style="font-size:12px;">: ${data.keterangan}</td></tr>
                    </table>
                `,
                    width: 600,
                    confirmButtonText: 'Tutup'
                });
            });
        });

        document.querySelectorAll(".btn-detailIncomplete").forEach(button => {
            button.addEventListener("click", () => {
                const data = button.dataset;

                function getStatusClass(status) {
                    switch (status.toLowerCase()) {
                        case 'pending':
                            return 'status-pending';
                        case 'approved':
                            return 'status-approved';
                        case 'rejected':
                            return 'status-rejected';
                        case 'forward':
                            return 'status-forward';
                        default:
                            return '';
                    }
                }

                const statusClass = getStatusClass(data.status);


                Swal.fire({
                    title: `Detail Pengajuan`,
                    html: `
                    <table style="text-align:left; width:100%;">
                        <tr><td><strong>ID</strong></td><td style="font-size:12px;">: ${data.id}</td></tr>
                        <tr><td><strong>Kode Pengajuan</strong></td><td style="font-size:12px;">: ${data.kode}</td></tr>
                        <tr><td><strong>Kode Uker</strong></td><td style="font-size:12px;">: ${data.kodeUker}</td></tr>
                        <tr><td><strong>Tanggal Pengajuan</strong></td><td style="font-size:12px;">: ${data.tanggal}</td></tr>
                        <tr><td><strong>Nama Barang</strong></td><td style="font-size:12px;">: ${data.namaBarang}</td></tr>
                         <tr><td class="status-cell"><strong>Status</strong></td><td style="font-size:12px;" class="${statusClass}">: ${data.status}</td></tr>
                        <tr><td><strong>Jumlah</strong></td><td style="font-size:12px;">: ${data.jumlah} ${data.satuan}</td></tr>
                        <tr><td><strong>Sisa</strong></td><td style="font-size:12px;">: ${data.sisa_jumlah}</td></tr>
                        <tr><td><strong>Harga Barang</strong></td><td style="font-size:12px;">: ${data.price}</td></tr>
                        <tr><td><strong>Anggaran</strong></td><td style="font-size:12px;">: ${data.anggaran}</td></tr>
                        <tr><td><strong>Keterangan</strong></td><td style="font-size:12px;">: ${data.keterangan}</td></tr>
                    </table>
                `,
                    width: 600,
                    confirmButtonText: 'Tutup'
                });
            });
        });
        // Fungsi untuk update posisi dan tampilkan menu sesuai status dan data tambahan
        function showGlobalMenu(btn) {
            const id = btn.dataset.id;
            const status = btn.dataset.status;

            // Ambil row terkait untuk cek data tambahan
            const row = btn.closest("tr");
            const sisaJumlah = parseInt(row.dataset.sisaJumlah || "0"); // pastikan ada atribut data-sisa-jumlah di tr
            const proses = row.dataset.proses || ""; // pastikan ada atribut data-proses di tr

            const rect = btn.getBoundingClientRect();
            globalMenu.style.top = (window.scrollY + rect.bottom - 50) + "px";
            globalMenu.style.left = (window.scrollX + rect.left - 60) + "px";
            globalMenu.style.display = "block";

            // Atur tombol tampil sesuai status dan kondisi tambahan
            document.getElementById("btn-forward").style.display = (status === "pending") ? "block" : "none";
            document.getElementById("btn-approve").style.display = (status === "forward") ? "block" : "none";
            document.getElementById("btn-reject").style.display = (status === "pending" || status === "forward") ? "block" : "none";

            // Tombol selesaikan muncul jika status approved, sisa_jumlah > 0 dan proses pending
            document.getElementById("btn-selesaikan").style.display = (status === "approved" && sisaJumlah > 0 && proses === "not done") ? "block" : "none";

            ["btn-forward", "btn-approve", "btn-reject", "btn-selesaikan"].forEach(idBtn => {
                const el = document.getElementById(idBtn);
                if (el) el.dataset.id = id;
            });
        }

        // Pasang event click ke semua tombol action
        document.querySelectorAll(".btn-action").forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.stopPropagation(); // agar tidak tertangkap oleh document click di bawah
                showGlobalMenu(btn);
            });
        });

        // Event klik luar untuk menutup menu global
        document.addEventListener("click", (e) => {
            if (!e.target.closest(".btn-action") && !e.target.closest("#global-actions")) {
                globalMenu.style.display = "none";
            }
        });

        // Tombol Forward
        document.getElementById("btn-forward").addEventListener("click", () => {
            const id = document.getElementById("btn-forward").dataset.id;
            const row = document.querySelector(`.btn-action[data-id="${id}"]`).closest("tr");
            const totalJumlah = parseInt(row.children[5].innerText);

            Swal.fire({
                title: 'Forward Pengajuan',
                html: `<input id="swal-input1" class="swal2-input" placeholder="Harga Barang">` +
                    `<input id="swal-input2" type="number" min="1" max="${totalJumlah}" class="swal2-input" placeholder="Jumlah yang di-forward (maks: ${totalJumlah})">`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Kirim',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const hargaBarang = parseInt(document.getElementById('swal-input1').value);
                    const jumlahForward = parseInt(document.getElementById('swal-input2').value);

                    if (!hargaBarang) return Swal.showValidationMessage('Harga Barang Wajib diisi!!!!');
                    if (!jumlahForward || jumlahForward <= 0 || jumlahForward > totalJumlah) return Swal.showValidationMessage(`Jumlah harus antara 1 dan ${totalJumlah}`);
                    return {
                        hargaBarang,
                        jumlahForward
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("update-submissionHandler.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                id: id,
                                status: "forward",
                                price: result.value.hargaBarang,
                                jumlah: result.value.jumlahForward
                            })
                        })
                        .then(res => res.text())
                        .then(msg => {
                            Swal.fire('Berhasil', msg, 'success').then(() => location.reload());
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Terjadi kesalahan saat update', 'error');
                        });
                }
            });
        });

        // Tombol Approve
        document.getElementById("btn-approve").addEventListener("click", () => {
            const id = document.getElementById("btn-forward").dataset.id;

            Swal.fire({
                title: 'Konfirmasi',
                text: "Setujui pengajuan ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Approve',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("update-submissionHandler.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                id: id,
                                status: "approved",
                            })
                        })
                        .then(res => res.text())
                        .then(msg => {
                            Swal.fire('Sukses', msg, 'success').then(() => {
                                location.reload();
                            });

                            const row = document.querySelector(`.btn-action[data-id="${id}"]`).closest("tr");
                            row.querySelector(".status-cell").innerText = "Approved";
                            row.querySelector(".btn-action").dataset.status = "approved";
                        }).catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Terjadi kesalahan saat update', 'error');
                        });
                }
            });
        });

        // Tombol Reject
        document.getElementById("btn-reject").addEventListener("click", () => {
            const id = document.getElementById("btn-reject").dataset.id;

            Swal.fire({
                title: 'Konfirmasi',
                text: "Tolak pengajuan ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Reject',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("update-submissionHandler.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                id: id,
                                status: "rejected",
                            })
                        })
                        .then(res => res.text())
                        .then(msg => {
                            Swal.fire('Sukses', msg, 'success').then(() => location.reload());
                        });
                }
            });
        });

        // Tombol Selesaikan (baru)

        document.getElementById("btn-selesaikan").addEventListener("click", () => {
            if (!isLogistikAyani && !isLogistikSudirman && !isLogistikTamalanrea) {
                Swal.fire("Akses Ditolak", "Hanya user logistik yang bisa menyelesaikan pengajuan ini.", "error");
                return;
            }
            const id = document.getElementById("btn-selesaikan").dataset.id;
            const row = document.querySelector(`.btn-action[data-id="${id}"]`).closest("tr");
            const sisaJumlah = parseInt(row.dataset.sisaJumlah || "0");

            Swal.fire({
                title: 'Selesaikan Pengajuan',
                text: "Masukkan jumlah yang ingin di beli",
                html: `<p>Masukkan jumlah yang ingin di beli</p>
                <input style="width:50%;" id="swal-input1" class="swal2-input" type="number" min="1" max="${sisaJumlah}" placeholder="Jumlah (maks: ${sisaJumlah})">`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Selesaikan',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const jumlahSelesai = parseInt(document.getElementById('swal-input1').value);
                    if (!jumlahSelesai || jumlahSelesai <= 0 || jumlahSelesai > sisaJumlah) return Swal.showValidationMessage(`Jumlah harus antara 1 dan ${sisaJumlah}`);
                    return jumlahSelesai;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("update-submissionHandler.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                id: id,
                                status: "completed",
                                jumlah_selesai: result.value
                            })
                        })
                        .then(res => res.text())
                        .then(msg => {
                            Swal.fire('Berhasil', msg, 'success').then(() => location.reload());
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Terjadi kesalahan saat update', 'error');
                        });
                }
            });
        });

        // Tombol Delete
        document.querySelectorAll('.button-trash').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                if (!id) return;

                Swal.fire({
                    title: `Hapus Pengajuan?`,
                    text: `Yakin ingin menghapus ID pengajuan ${id}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('update-submissionHandler.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: new URLSearchParams({
                                    id: id,
                                    status: 'delete'
                                })
                            })
                            .then(res => res.text())
                            .then(msg => {
                                Swal.fire('Berhasil', msg, 'success').then(() => location.reload());
                            })
                            .catch(err => {
                                console.error(err);
                                Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus pengajuan.', 'error');
                            });
                    }
                });
            });
        });

    });

    // Search 
    function searchTableById(tableId, inputValue) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const trs = table.querySelectorAll("tbody tr");
        const query = inputValue.toLowerCase();

        trs.forEach(tr => {
            const tds = tr.querySelectorAll("td");
            let match = false;

            tds.forEach(td => {
                if (td.innerText.toLowerCase().includes(query)) {
                    match = true;
                }
            });

            tr.style.display = match ? "" : "none";
        });
    }

    // Fungsi khusus untuk tiap tab
    function searchRequest() {
        const input = document.querySelector('#request .list-input-request').value;
        searchTableById('dataTable-request', input);
    }

    function searchIncomplete() {
        const input = document.querySelector('#incomplete .list-input-incomplete').value;
        searchTableById('dataTable-incomplete', input);
    }

    function searchApproved() {
        const input = document.querySelector('#approved .list-input-complete').value;
        searchTableById('dataTable-complete', input);
    }
</script>


<div class="content-wrapper">
    <div class="sub-content">
        <h4 style="font-weight: 800; font-size:28px;">Submission Overview</h4>
        <?php if ($isKanwil): ?>
            <div class="tabs">
                <button class="tabslinks" onclick="openTab(event, 'forward')">Request <span class="badge"><?= $incompleteCount ?></span></button>
                <button class="tabslinks" onclick="openTab(event, 'approved')">Complete <span class="badge"><?= $completeCount ?></span></button>
            </div>
        <?php else: ?>
            <div class="tabs">
                <button class="tabslinks active" onclick="openTab(event, 'request')">Request <span class="badge"><?= $requestCount ?></span></button>
                <button class="tabslinks" onclick="openTab(event, 'forward')">Forward <span class="badge"><?= $forwardCount ?></span></button>
                <button class="tabslinks" onclick="openTab(event, 'incomplete')">Incomplete <span class="badge"><?= $incompleteCount ?></span></button>
                <button class="tabslinks" onclick="openTab(event, 'approved')">Complete <span class="badge"><?= $completeCount ?></span></button>
            </div>
        <?php endif; ?>



        <div id="request" class="tabscontent">
            <div class="body-content">
                <div style="display: flex; gap:12px">
                    <input type="text" onkeyup="searchRequest()" placeholder="Search ..." class="list-input-request">
                    <a href="export_pengajuan.php" class="list-select" style="padding:6px;margin-bottom:2px; text-decoration:none;">Download Excel</a>
                </div>

                <div class="table-container">
                    <table id="dataTable-request" style="width:100%;text-align:center">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kode <br>Pengajuan</th>
                                <th>Kode <br>Uker</th>
                                <th>Tanggal <br>Pengajuan</th>
                                <th>Nama <br>Barang</th>
                                <!-- <th style="cursor:pointer;" onclick="toggleSortStatus()">Status <span id="sortArrow">↓</span></th> -->
                                <th>Jumlah</th>
                                <!-- <th>Satuan</th> -->
                                <!-- <th>Nama <br>Anggaran</th> -->
                                <!-- <th>Nominal <br>Anggaran</th> -->
                                <!-- <th>Keterangan</th> -->
                                <!-- <th>Aksi</th> -->
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result->data_seek(0);
                            $hasData = false;
                            while ($row = $result->fetch_assoc()):
                                $status = strtolower($row['status']);
                                if (!in_array($status, ['pending'])) continue;

                                // Filter khusus logistik berdasarkan kode uker
                                if ($isLogistikSudirman && !in_array($row['kode_uker'], $sudirmanCodes)) continue;
                                if ($isLogistikAhmadYani && !in_array($row['kode_uker'], $ahmadYaniCodes)) continue;
                                if ($isLogistikTamalanrea && !in_array($row['kode_uker'], $tamalanreaCodes)) continue;

                                $hasData = true;
                                $class = match ($status) {
                                    'pending' => 'status-pending',
                                    'approved' => 'status-approved',
                                    'rejected' => 'status-rejected',
                                    'forward' => 'status-forward',
                                    default => '',
                                };
                            ?>
                                <tr>

                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['kode_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <!-- <td class="status-cell <?= $class ?>"><?= htmlspecialchars($row['status']) ?></td> -->
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                    <!-- <td><?= htmlspecialchars($row['satuan']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($row['nama_anggaran']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($row['jumlah_anggaran']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($row['keterangan']) ?></td> -->
                                    <td></td>
                                    <td></td>
                                    <td style="display:flex; flex-direction:row; justify-content: center; align-items: center; gap: 8px; flex-wrap: nowrap;">
                                        <button class="btn-detail"
                                            data-id="<?= $row['id'] ?>"
                                            data-kode="<?= $row['kode_pengajuan'] ?>"
                                            data-kode-uker="<?= $row['kode_uker'] ?>"
                                            data-tanggal="<?= $row['tanggal_pengajuan'] ?>"
                                            data-nama-barang="<?= $row['nama_barang'] ?>"
                                            data-status="<?= $status ?>"
                                            data-jumlah="<?= $row['jumlah'] ?>"
                                            data-satuan="<?= $row['satuan'] ?>"
                                            data-anggaran="<?= $row['nama_anggaran'] ?>"
                                            data-nominal="<?= $row['jumlah_anggaran'] ?>"
                                            data-keterangan="<?= $row['keterangan'] ?>"
                                            style="padding:6px 10px; margin-right: 8px;">Detail</button>

                                        <?php if ($isAdmin || $isKanwil || $isLogistikSudirman || $isLogistikAhmadYani || $isLogistikTamalanrea): ?>
                                            <button class="btn-action" data-id="<?= $row['id'] ?>"
                                                data-kode="<?= $row['kode_pengajuan'] ?>"
                                                data-status="<?= $status ?>"
                                                style="font-size:24px; background: none; padding:10px; border:none; cursor: pointer;">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                        <?php elseif ($status === 'pending'): ?>
                                            <button class="button-trash"
                                                data-id="<?= $row['id'] ?>" data-kode="<?= $row['kode_pengajuan'] ?>"
                                                style="padding:6px 10px;">
                                                Hapus <i class="fa fa-trash-o"></i>
                                            </button>
                                        <?php else: ?>
                                            <button style="font-size:24px; background: none; padding:10px; border:none; cursor: not-allowed;" class="btn-disabled" disabled>
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>

                                </tr>
                            <?php endwhile; ?>

                            <?php if (!$hasData): ?>
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 20px; font-style: italic;">Belum ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        <div id="forward" class="tabscontent">
            <div class="body-content">
                <div style="display: flex; gap:12px">
                    <input type="text" onkeyup="searchIncomplete()" placeholder="Search ..." class="list-input-incomplete">
                    <a href="export_pengajuanForward.php" class="list-select" style="padding:6px;margin-bottom:2px; text-decoration:none;">Download Excel</a>
                </div>

                <?php if ($isKanwil): ?>
                    <div style="margin: 10px 0; display: flex; gap: 10px;">
                        <button id="approve-selected" class="list-select">Approve</button>
                        <button id="reject-selected" class="list-select">Reject</button>
                    </div>
                <?php endif; ?>


                <div class="table-container">
                    <table id="dataTable-forward" style="width:100%;text-align:center">
                        <thead>
                            <tr>
                                <!-- <th>ID</th> -->
                                <?php if ($isAdmin || $isKanwil): ?>
                                    <th></th>
                                <?php endif; ?>
                                <th>Kode<br> Pengajuan</th>
                                <th>Kode<br>Uker</th>
                                <th>Tanggal <br>Pengajuan</th>
                                <th>Nama <br>Barang</th>
                                <!-- <th style="cursor:pointer;" onclick="toggleSortStatus()">Status <span id="sortArrow">↓</span></th> -->
                                <th>Status</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Sisa</th>
                                <!-- <th>Harga <br>Barang</th> -->
                                <!-- <th>Anggaran</th> -->
                                <!-- <th>Keterangan</th> -->
                                <th style="cursor:pointer;" onclick="toggleSortForwardProses()">Proses <span id="sortArrowProsesForward">↓</span></th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result->data_seek(0);
                            $hasData = false;
                            while ($row = $result->fetch_assoc()):
                                $status = strtolower($row['status']);
                                $status_sisa = strtolower($row['status_sisa'] ?? '');
                                if (!in_array($status, ['forward'])) continue;
                                // if (!in_array($status, ['approved', 'forward'])) continue;
                                // if (!in_array($status_sisa, ['not done', 'done'])) continue;
                                // if ($status === 'approved' && $status_sisa === 'done') continue;

                                if ($isLogistikSudirman && !in_array($row['kode_uker'], $sudirmanCodes)) continue;
                                if ($isLogistikAhmadYani && !in_array($row['kode_uker'], $ahmadYaniCodes)) continue;
                                if ($isLogistikTamalanrea && !in_array($row['kode_uker'], $tamalanreaCodes)) continue;
                                $hasData = true;
                                $class = match ($status) {
                                    'pending' => 'status-pending',
                                    'approved' => 'status-approved',
                                    'rejected' => 'status-rejected',
                                    'forward' => 'status-forward',
                                    'not done' => 'status-notdone',
                                    default => '',
                                };
                            ?>
                                <tr data-sisa-jumlah="<?= htmlspecialchars($row['sisa_jumlah']) ?>"
                                    data-proses="<?= htmlspecialchars($row['status_sisa']) ?>">
                                    <!-- <td><?= htmlspecialchars($row['id']) ?></td> -->
                                    <?php if ($isAdmin): ?>
                                        <td>
                                            <?php if (strtolower($row['status']) === 'forward' && $isKanwil): ?>
                                                <input type="checkbox" class="row-checkbox" value="<?= $row['id'] ?>">
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>

                                    <td><?= htmlspecialchars($row['kode_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td class="status-cell <?= $class ?>"><?= htmlspecialchars($row['status']) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                    <td><?= htmlspecialchars($row['satuan']) ?></td>
                                    <td><?= htmlspecialchars($row['sisa_jumlah']) ?></td>
                                    <!-- <td><?= htmlspecialchars($row['price']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($row['nama_anggaran']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($row['keterangan']) ?></td> -->
                                    <td class="status-cell <?= $class ?>"><?= htmlspecialchars($row['status_sisa']) ?></td>

                                    <td style="display:flex; flex-direction:row; justify-content: center; align-items: center; gap: 8px; flex-wrap: nowrap;">
                                        <button class="btn-detailForward"
                                            data-id="<?= $row['id'] ?>"
                                            data-kode="<?= $row['kode_pengajuan'] ?>"
                                            data-kode-uker="<?= $row['kode_uker'] ?>"
                                            data-tanggal="<?= $row['tanggal_pengajuan'] ?>"
                                            data-nama-barang="<?= $row['nama_barang'] ?>"
                                            data-status="<?= $status ?>"
                                            data-jumlah="<?= $row['jumlah'] ?>"
                                            data-satuan="<?= $row['satuan'] ?>"
                                            data-sisa_jumlah="<?= $row['sisa_jumlah'] ?>"
                                            data-price="<?= $row['price'] ?>"
                                            data-anggaran="<?= $row['nama_anggaran'] ?>"
                                            data-keterangan="<?= $row['keterangan'] ?>"
                                            style="padding:6px 10px; margin-right: 8px;">Detail</button>
                                        <?php if ($status === 'forward' && $isKanwil): ?>
                                            <button class="btn-action" data-id="<?= $row['id'] ?>"
                                                data-kode="<?= $row['kode_pengajuan'] ?>"
                                                data-status="<?= $status ?>"
                                                style="font-size:24px; background: none; padding:10px; border:none">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                        <?php elseif ($status === 'approved' && ($isLogistikAhmadYani || $isLogistikSudirman || $isLogistikTamalanrea)): ?>
                                            <button class="btn-action" data-id="<?= $row['id'] ?>"
                                                data-kode="<?= $row['kode_pengajuan'] ?>"
                                                data-status="<?= $status ?>"
                                                style="font-size:24px; background: none; padding:10px; border:none">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                        <?php else: ?>
                                            <button style="font-size:24px; background: none; padding:10px; border:none" class="btn-disabled" disabled><i class="fa fa-ellipsis-v"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if (!$hasData): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 20px; font-style: italic;">Belum ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="incomplete" class="tabscontent">
            <div class="body-content">
                <div style="display: flex; gap:12px">
                    <input type="text" onkeyup="searchIncomplete()" placeholder="Search ..." class="list-input-incomplete">
                    <a href="export_pengajuanIncomplete.php" class="list-select" style="padding:6px;margin-bottom:2px; text-decoration:none;">Download Excel</a>
                </div>
                <div class="table-container">
                    <table id="dataTable-incomplete" style="width:100%;text-align:center">
                        <thead>
                            <tr>
                                <!-- <th>ID</th> -->
                                <th>Kode<br> Pengajuan</th>
                                <th>Kode<br>Uker</th>
                                <th>Tanggal <br>Pengajuan</th>
                                <th>Nama <br>Barang</th>
                                <!-- <th style="cursor:pointer;" onclick="toggleSortStatus()">Status <span id="sortArrow">↓</span></th> -->
                                <th>Status</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Sisa</th>
                                <!-- <th>Harga <br>Barang</th>
                                <th>Anggaran</th>
                                <th>Keterangan</th> -->
                                <th>Proses</th>
                                <!-- <th style="cursor:pointer;" onclick="toggleSortProses()">Proses <span id="sortArrowProses">↓</span></th> -->
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result->data_seek(0);
                            $hasData = false;
                            while ($row = $result->fetch_assoc()):
                                $status = strtolower($row['status']);
                                $status_sisa = strtolower($row['status_sisa'] ?? '');

                                if (!in_array($status, ['approved'])) continue;
                                if (!in_array($status_sisa, ['not done', 'done'])) continue;
                                if ($status === 'approved' && $status_sisa === 'done') continue;

                                if ($isLogistikSudirman && !in_array($row['kode_uker'], $sudirmanCodes)) continue;
                                if ($isLogistikAhmadYani && !in_array($row['kode_uker'], $ahmadYaniCodes)) continue;
                                if ($isLogistikTamalanrea && !in_array($row['kode_uker'], $tamalanreaCodes)) continue;
                                $hasData = true;
                                $class = match ($status) {
                                    'pending' => 'status-pending',
                                    'approved' => 'status-approved',
                                    'rejected' => 'status-rejected',
                                    'forward' => 'status-forward',
                                    'not done' => 'status-notdone',
                                    default => '',
                                };
                            ?>
                                <tr data-sisa-jumlah="<?= htmlspecialchars($row['sisa_jumlah']) ?>"
                                    data-proses="<?= htmlspecialchars($row['status_sisa']) ?>">
                                    <!-- <td><?= htmlspecialchars($row['id']) ?></td> -->
                                    <td><?= htmlspecialchars($row['kode_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td class="status-cell <?= $class ?>"><?= htmlspecialchars($row['status']) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                    <td><?= htmlspecialchars($row['satuan']) ?></td>
                                    <td><?= htmlspecialchars($row['sisa_jumlah']) ?></td>
                                    <!-- <td><?= htmlspecialchars($row['price']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($row['nama_anggaran']) ?></td> -->
                                    <!-- <td><?= htmlspecialchars($row['keterangan']) ?></td> -->
                                    <td class="status-cell <?= $class ?>"><?= htmlspecialchars($row['status_sisa']) ?></td>

                                    <td style="display:flex; flex-direction:row; justify-content: center; align-items: center; gap: 8px; flex-wrap: nowrap;">
                                        <button class="btn-detailIncomplete"
                                            data-id="<?= $row['id'] ?>"
                                            data-kode="<?= $row['kode_pengajuan'] ?>"
                                            data-kode-uker="<?= $row['kode_uker'] ?>"
                                            data-tanggal="<?= $row['tanggal_pengajuan'] ?>"
                                            data-nama-barang="<?= $row['nama_barang'] ?>"
                                            data-status="<?= $status ?>"
                                            data-jumlah="<?= $row['jumlah'] ?>"
                                            data-satuan="<?= $row['satuan'] ?>"
                                            data-sisa_jumlah="<?= $row['sisa_jumlah'] ?>"
                                            data-price="<?= $row['price'] ?>"
                                            data-anggaran="<?= $row['nama_anggaran'] ?>"
                                            data-keterangan="<?= $row['keterangan'] ?>"
                                            style="padding:6px 10px; margin-right: 8px;">Detail</button>
                                        <?php if ($status === 'forward' && $isKanwil): ?>
                                            <button class="btn-action" data-id="<?= $row['id'] ?>"
                                                data-kode="<?= $row['kode_pengajuan'] ?>"
                                                data-status="<?= $status ?>"
                                                style="font-size:24px; background: none; padding:10px; border:none">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                        <?php elseif ($status === 'approved' && ($isLogistikAhmadYani || $isLogistikSudirman || $isLogistikTamalanrea)): ?>
                                            <button class="btn-action" data-id="<?= $row['id'] ?>"
                                                data-kode="<?= $row['kode_pengajuan'] ?>"
                                                data-status="<?= $status ?>"
                                                style="font-size:24px; background: none; padding:10px; border:none">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                        <?php else: ?>
                                            <button style="font-size:24px; background: none; padding:10px; border:none" class="btn-disabled" disabled><i class="fa fa-ellipsis-v"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if (!$hasData): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 20px; font-style: italic;">Belum ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="approved" class="tabscontent">
            <div class="body-content">
                <div style="display: flex; gap:12px">
                    <input type="text" onkeyup="searchApproved()" placeholder="Search ..." class="list-input-complete">
                    <a href="export_pengajuanApproved.php" class="list-select" style="padding:6px;margin-bottom:2px; text-decoration:none;">Download Excel</a>
                </div>
                <div class="table-container">
                    <table id="dataTable-complete" style="width:100%;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kode Pengajuan</th>
                                <th>Kode Uker</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Nama Barang</th>
                                <th style="cursor:pointer;" onclick="toggleSortComplete()">Status <span id="sortArrowComplete">↓</span></th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Anggaran</th>
                                <th>Proses</th>
                                <th>Keterangan</th>
                                <?php if ($isAdmin): ?>
                                    <th>Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result->data_seek(0);
                            $hasData = false;
                            while ($row = $result->fetch_assoc()):
                                $status = strtolower($row['status']);
                                if (
                                    ($status === 'approved' && (int)$row['sisa_jumlah'] > 0) ||
                                    (!in_array($status, ['approved', 'rejected']))
                                ) continue;
                                $hasData = true;
                                $class = match ($status) {
                                    'pending' => 'status-pending',
                                    'approved' => 'status-approved',
                                    'rejected' => 'status-rejected',
                                    'forward' => 'status-forward',
                                    default => '',
                                };
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['kode_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['kode_uker']) ?></td>
                                    <td><?= htmlspecialchars($row['tanggal_pengajuan']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                    <td class="status-cell <?= $class ?>"><?= htmlspecialchars($row['status']) ?></td>
                                    <td><?= htmlspecialchars($row['jumlah']) ?></td>
                                    <td><?= htmlspecialchars($row['satuan']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_anggaran']) ?></td>
                                    <td><?= htmlspecialchars($row['status_sisa']) ?></td>
                                    <td style="background: none;">
                                        <?php if ($status === 'approved'): ?>
                                            <div style="font-size:12px;">Pengajuan disetujui</div>
                                        <?php elseif ($status === 'rejected'): ?>
                                            <div style="font-size:12px;">Pengajuan ditolak</div>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($isAdmin): ?>
                                        <td>
                                            <button class="button-trash" data-id="<?= $row['id'] ?>" data-kode="<?= $row['kode_pengajuan'] ?>">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                            <?php if (!$hasData): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 20px; font-style: italic;">Belum ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="global-actions" class="dropdown-action" style="display:none; position:absolute; z-index:9999;">
    <button id="btn-forward" class="button-approve">Forward</button>
    <button id="btn-approve" class="button-approve">Approve</button>
    <button id="btn-reject" class="button-reject">Reject</button>
    <button id="btn-selesaikan" class="button-complete">Selesaikan</button>
</div>