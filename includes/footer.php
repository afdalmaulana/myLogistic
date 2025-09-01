<script src="../js/sweetalert.all.min.js"></script>

<script>
    function deleteRow(button, id) {
        Swal.fire({
            title: 'Yakin ingin mengedit?',
            text: "Data akan di ubah.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Edit',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoadingDel(button);
                window.location.href = "index.php?page=inMail&delete_id=" + id;
            }
        });
    }

    function editRow(button, id) {
        Swal.fire({
            title: 'Yakin ingin mengedit?',
            text: "Data akan di ubah.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Edit',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoadingDel(button);
                window.location.href = "index.php?page=inMail&edit_id=" + id;
            }
        });
    }



    function toggleSidebar() {
        const body = document.body;

        const isVisible = body.classList.contains("sidebar-visible");

        if (isVisible) {
            body.classList.remove("sidebar-visible");
            body.classList.add("sidebar-hidden");
        } else {
            body.classList.remove("sidebar-hidden");
            body.classList.add("sidebar-visible");
        }
    }

    window.onload = function() {
        const body = document.body;
        body.classList.remove("sidebar-visible");
        body.classList.add("sidebar-hidden");
    };

    function searchTable() {
        const input = document.getElementById("searchInput").value.toLowerCase();
        const table = document.getElementById("dataTable");
        const trs = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

        for (let i = 0; i < trs.length; i++) {
            const tds = trs[i].getElementsByTagName("td");
            let match = false;
            for (let j = 0; j < tds.length; j++) {
                if (tds[j].innerText.toLowerCase().includes(input)) {
                    match = true;
                    break;
                }
            }
            trs[i].style.display = match ? "" : "none";
        }
    }

    function toggleDropdown(id) {
        // Tutup semua dropdown dulu
        const dropdowns = document.getElementsByClassName("dropdown-content");
        for (let i = 0; i < dropdowns.length; i++) {
            dropdowns[i].style.display = "none";
        }

        // Toggle dropdown yang diminta
        const dropdown = document.getElementById(id);
        if (dropdown) {
            dropdown.style.display = "block";
        }
    }

    function buttonactionDropdown(id) {
        const dropdowns = document.getElementsByClassName("dropdown-contents");
        for (let i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].id !== id) {
                dropdowns[i].style.display = "none";
            }
        }

        const dropdown = document.getElementById(id);
        if (dropdown) {
            dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
        }
    }
    //ini punya dropdown action
    window.onclick = function(event) {
        if (!event.target.closest('.dropdown')) {
            const dropdowns = document.getElementsByClassName("dropdown-contents");
            for (let i = 0; i < dropdowns.length; i++) {
                dropdowns[i].style.display = "none";
            }
        }
    };
    window.onclick = function(event) {
        // Cek apakah klik di luar dropdown
        if (!event.target.closest('.dropdown')) {
            const dropdowns = document.getElementsByClassName("dropdown-content");
            for (let i = 0; i < dropdowns.length; i++) {
                dropdowns[i].style.display = "none";
            }
        }
    };


    function showLoading() {
        const btn = document.getElementById("submitBtn");
        if (!btn) return true;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading...';

        return true; // allow form submit normally
    }

    function showLoadingSignin() {
        const btn = document.getElementById("signinBtn");
        if (!btn) return true;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading...';

        return true; // allow form submit normally
    }

    function loadingLink(link, event) {
        event.preventDefault(); // Cegah redirect langsung
        const originalText = link.innerHTML;

        link.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memuat...';
        link.style.pointerEvents = 'none'; // Biar nggak bisa diklik lagi

        // Redirect setelah 500ms agar spinner terlihat
        setTimeout(() => {
            window.location.href = link.href;
        }, 100);

        return false;
    }

    function showLoadingDel(button) {
        button.disabled = true;
        button.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        return true; // biar aksi (form submit / link) tetap lanjut
    }

    let currentlyEditingRow = null;

    /**BUTTON ACITION */
    document.addEventListener("DOMContentLoaded", () => {
        const globalMenu = document.getElementById("global-actions");

        // Fungsi untuk update posisi dan tampilkan menu sesuai status dan data tambahan
        function showGlobalMenu(btn) {
            const kode = btn.dataset.kode;
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

            // Inject data kode ke semua tombol aksi
            ["btn-forward", "btn-approve", "btn-reject", "btn-selesaikan"].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.dataset.kode = kode;
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
            const kode = document.getElementById("btn-forward").dataset.kode;
            const row = document.querySelector(`.btn-action[data-kode="${kode}"]`).closest("tr");
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
                                kode_pengajuan: kode,
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
            const kode = document.getElementById("btn-approve").dataset.kode;

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
                                kode_pengajuan: kode,
                                status: "approved",
                            })
                        })
                        .then(res => res.text())
                        .then(msg => {
                            Swal.fire('Sukses', msg, 'success').then(() => {
                                location.reload();
                            });

                            const row = document.querySelector(`.btn-action[data-kode="${kode}"]`).closest("tr");
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
            const kode = document.getElementById("btn-reject").dataset.kode;

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
                                kode_pengajuan: kode,
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
            if (!isLogistik) {
                Swal.fire("Akses Ditolak", "Hanya user logistik yang bisa menyelesaikan pengajuan ini.", "error");
                return;
            }
            const kode = document.getElementById("btn-selesaikan").dataset.kode;
            const row = document.querySelector(`.btn-action[data-kode="${kode}"]`).closest("tr");
            const sisaJumlah = parseInt(row.dataset.sisaJumlah || "0");

            Swal.fire({
                title: 'Selesaikan Pengajuan',
                html: `<input id="swal-input1" class="swal2-input" type="number" min="1" max="${sisaJumlah}" placeholder="Jumlah yang diselesaikan (maks: ${sisaJumlah})">`,
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
                                kode_pengajuan: kode,
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
    });


    /**BATAS ACTION TOMBOL */

    //Hapus Pengajuan
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.button-trash');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const kodePengajuan = this.getAttribute('data-kode');
                if (!kodePengajuan) return;

                if (!confirm(`Yakin ingin menghapus pengajuan ${kodePengajuan}?`)) return;

                fetch('update-submissionHandler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `kode_pengajuan=${encodeURIComponent(kodePengajuan)}&status=delete`
                    })
                    .then(response => response.text())
                    .then(data => {
                        alert(data);
                        location.reload(); // refresh agar data terbaru muncul
                    })
                    .catch(error => {
                        alert('Gagal menghapus pengajuan.');
                        console.error(error);
                    });
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const logoutBtn = document.getElementById("logoutBtn");

        if (logoutBtn) {
            logoutBtn.addEventListener("click", function(e) {
                e.preventDefault(); // Mencegah link langsung dijalankan

                Swal.fire({
                    title: 'Yakin ingin logout?',
                    text: "",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, logout!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Arahkan ke logout.php
                        window.location.href = logoutBtn.getAttribute("href");
                    }
                });
            });
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        const signinBtn = document.getElementById("signinBtn");

        if (signinBtn) {
            signinBtn.addEventListener("click", function(e) {
                // e.preventDefault(); // Mencegah link langsung dijalankan
                Swal.fire({
                    position: "top-end",
                    icon: "success",
                    title: "Selamat Datang",
                    showConfirmButton: false,
                    timer: 1500,
                });
            });
        }
    });

    /**Password */
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        }
    }

    function setTanggalHariIni() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const formattedDate = `${yyyy}-${mm}-${dd}`;

        // Asumsikan input tanggal ada id="tanggal"
        const tanggalInput = document.getElementById('tanggal');
        if (tanggalInput) {
            tanggalInput.value = formattedDate;
        }
    }

    /* TABS */
    function openCity(evt, cityName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(cityName).style.display = "block";
        evt.currentTarget.className += " active";
    }
    /* TABS */
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
        const allowedTabs = ['request', 'incomplete', 'approved'];
        if (hashPengajuan) {
            let tabName = hashPengajuan.substring(1);
            if (!allowedTabs.includes(tabName)) {
                tabName = 'incomplete';
            }
            openTab(null, tabName);
        } else {
            openTab(null, 'incomplete');
        }

        // Optional: expose openTab ke global (jika dipakai di HTML onclick)
        window.openTab = openTab;
    });


    /**TAB INVENTORY */
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

    /**TAB LOG */
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
    });

    //TAB INVENTORY IT
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

    // Cek hash di URL dan buka tab sesuai
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

    // SORT
    let sortAsc = true;

    function toggleSortStatus() {
        const table = document.getElementById("dataTable-incomplete");
        const tbody = table.tBodies[0];
        const rows = Array.from(tbody.rows);

        rows.sort((a, b) => {
            const statusA = a.cells[4].innerText.trim().toLowerCase();
            const statusB = b.cells[4].innerText.trim().toLowerCase();

            if (sortAsc) {
                // Forward sebelum Pending
                return (statusA === "forward" ? -1 : 1) - (statusB === "forward" ? -1 : 1);
            } else {
                // Pending sebelum Forward
                return (statusA === "pending" ? -1 : 1) - (statusB === "pending" ? -1 : 1);
            }
        });

        rows.forEach(row => tbody.appendChild(row)); // re-append sorted rows

        // Ubah arah panah
        document.getElementById("sortArrow").textContent = sortAsc ? "↑" : "↓";

        sortAsc = !sortAsc;
    }


    // button edit tanggal nota
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
</script>