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

    //fitur dropdown tanpa scroll

    function showLoading() {
        const btn = document.getElementById("submitBtn");
        btn.disabled = true;
        // btn.textContent = "Mengirim ...";
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        return true;
    }

    function loadingLink(link, event) {
        event.preventDefault(); // Cegah redirect langsung
        const originalText = link.innerHTML;

        link.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memuat...';
        link.style.pointerEvents = 'none'; // Biar nggak bisa diklik lagi

        // Redirect setelah 500ms agar spinner terlihat
        setTimeout(() => {
            window.location.href = link.href;
        }, 500);

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

        document.querySelectorAll(".btn-action").forEach(btn => {
            btn.addEventListener("click", (e) => {
                const kode = btn.dataset.kode;
                const status = btn.dataset.status;

                // Posisi menu di bawah tombol
                const rect = btn.getBoundingClientRect();
                globalMenu.style.top = (window.scrollY + rect.bottom) + "px";
                globalMenu.style.left = (window.scrollX + rect.left) + "px";
                globalMenu.style.display = "block";

                // Atur tombol yang muncul sesuai status
                document.getElementById("btn-forward").style.display = (status === "pending") ? "block" : "none";
                document.getElementById("btn-approve").style.display = (status === "forward") ? "block" : "none";
                document.getElementById("btn-reject").style.display = (status === "pending" || status === "forward") ? "block" : "none";

                // Inject data kode
                ["btn-forward", "btn-approve", "btn-reject"].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.setAttribute("data-kode", kode);
                });
            });
        });

        // Klik luar = close
        document.addEventListener("click", (e) => {
            if (!e.target.closest(".btn-action") && !e.target.closest("#global-actions")) {
                globalMenu.style.display = "none";
            }
        });
    });


    /**BUTTON ACTION LANJUTAN */
    document.addEventListener("DOMContentLoaded", () => {
        const globalMenu = document.getElementById("global-actions");

        // === Buka menu global saat titik tiga diklik ===
        document.querySelectorAll(".btn-action").forEach(btn => {
            btn.addEventListener("click", () => {
                const kode = document.getElementById("btn-forward").dataset.kode;
                const status = btn.dataset.status;

                const rect = btn.getBoundingClientRect();
                globalMenu.style.top = (window.scrollY + rect.bottom) + "px";
                globalMenu.style.left = (window.scrollX + rect.left) + "px";
                globalMenu.style.display = "block";

                ["btn-forward", "btn-approve", "btn-reject"].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.dataset.kode = kode;
                });

                document.getElementById("btn-forward").style.display = (status === "pending") ? "block" : "none";
                document.getElementById("btn-approve").style.display = (status === "forward") ? "block" : "none";
                document.getElementById("btn-reject").style.display = (status === "pending" || status === "forward") ? "block" : "none";
            });
        });

        // === Tombol Forward ===
        document.getElementById("btn-forward").addEventListener("click", () => {
            const kode = document.getElementById("btn-forward").dataset.kode;
            const row = document.querySelector(`.btn-action[data-kode="${kode}"]`).closest("tr");
            const totalJumlah = parseInt(row.children[5].innerText);

            Swal.fire({
                title: 'Forward Pengajuan',
                html: `<input id="swal-input1" class="swal2-input" placeholder="Nomor Surat">` +
                    `<input id="swal-input2" type="number" min="1" max="${totalJumlah}" class="swal2-input" placeholder="Jumlah yang di-forward (maks: ${totalJumlah})">`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Kirim',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const nomorSurat = document.getElementById('swal-input1').value;
                    const jumlahForward = parseInt(document.getElementById('swal-input2').value);

                    if (!nomorSurat) return Swal.showValidationMessage('Nomor surat wajib diisi!');
                    if (!jumlahForward || jumlahForward <= 0 || jumlahForward > totalJumlah) return Swal.showValidationMessage(`Jumlah harus antara 1 dan ${totalJumlah}`);
                    return {
                        nomorSurat,
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
                                nomor_surat: result.value.nomorSurat,
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

        // === Tombol Approve ===
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
                                nomor_surat: result.value
                            })
                        })
                        .then(msg => {
                            Swal.fire('Sukses', msg, 'success').then(() => {
                                // reload setelah user klik OK swal
                                location.reload();
                            });

                            const row = document.querySelector(`.btn-action[data-kode="${kode}"]`).closest("tr");
                            row.querySelector(".status-cell").innerText = "Approved"; // perbaiki typo
                            row.querySelector(".btn-action").dataset.status = "approved";
                        }).catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Terjadi kesalahan saat update', 'error');
                        })
                }
            });
        });

        // === Tombol Reject ===
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
                        .then(msg => {
                            Swal.fire('Sukses', msg, 'success').then(() => location.reload());
                        });
                }
            });
        });

        // === Klik luar untuk nutup menu global ===
        document.addEventListener("click", (e) => {
            if (!e.target.closest(".btn-action") && !e.target.closest("#global-actions")) {
                globalMenu.style.display = "none";
            }
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
            var i, tabcontent, tablinks;

            // Sembunyikan semua tab
            tabcontent = document.getElementsByClassName("tabscontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            // Hapus class "active" dari semua tombol
            tablinks = document.getElementsByClassName("tabslinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            // Tampilkan tab yang diminta
            document.getElementById(tabName).style.display = "block";

            // Tambahkan class active ke tombol
            if (evt) {
                evt.currentTarget.className += " active";
            } else {
                // Jika tidak dari klik, cari tombolnya dan aktifkan
                var autoBtn = document.querySelector('.tabslinks[onclick*="' + tabName + '"]');
                if (autoBtn) {
                    autoBtn.className += " active";
                }
            }
        }

        // Cek hash di URL dan buka tab sesuai
        const hashPengajuan = window.location.hash;
        if (hashPengajuan) {
            const tabName = hashPengajuan.substring(1); // hapus #
            openTab(null, tabName); // buka tab otomatis
        } else {
            openTab(null, 'incomplete'); // default ke tab "incomplete"
        }

        // Optional: expose openTab ke global (jika dipakai di HTML onclick)
        window.openTab = openTab;
    });


    /**TAB INVENTORY */
    document.addEventListener("DOMContentLoaded", function() {

        // Fungsi buka tab
        function openInvent(evt, tabName) {
            var i, tabcontentinvent, tablinks;

            // Sembunyikan semua tab
            tabcontentinvent = document.getElementsByClassName("tabcontent-invent");
            for (i = 0; i < tabcontentinvent.length; i++) {
                tabcontentinvent[i].style.display = "none";
            }

            // Hapus class "active" dari semua tombol
            tablinks = document.getElementsByClassName("tablink-invent");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            // Tampilkan tab yang diminta
            document.getElementById(tabName).style.display = "block";

            // Tambahkan class active ke tombol
            if (evt) {
                evt.currentTarget.className += " active";
            } else {
                // Jika tidak dari klik, cari tombolnya dan aktifkan
                var autoBtn = document.querySelector('.tablink-invent[onclick*="' + tabName + '"]');
                if (autoBtn) {
                    autoBtn.className += " active";
                }
            }
        }

        // Cek hash di URL dan buka tab sesuai
        const hash = window.location.hash;
        if (hash) {
            const tabName = hash.substring(1); // hapus #
            openInvent(null, tabName); // buka tab otomatis
        } else {
            openInvent(null, 'stocks'); // default ke tab "incomplete"
        }

        // Optional: expose openTab ke global (jika dipakai di HTML onclick)
        window.openInvent = openInvent;
    });

    /**TAB LOG */
    document.addEventListener("DOMContentLoaded", function() {

        // Fungsi buka tab
        function openCity(evt, tabName) {
            var i, tabcontent, tablinks;

            // Sembunyikan semua tab
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            // Hapus class "active" dari semua tombol
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            // Tampilkan tab yang diminta
            document.getElementById(tabName).style.display = "block";

            // Tambahkan class active ke tombol
            if (evt) {
                evt.currentTarget.className += " active";
            } else {
                // Jika tidak dari klik, cari tombolnya dan aktifkan
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
</script>