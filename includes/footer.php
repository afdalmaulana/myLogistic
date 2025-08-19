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



    //update status
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.button-approve, .button-reject');

        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const kodePengajuan = this.getAttribute('data-kode');
                const status = this.getAttribute('data-status');

                if (!kodePengajuan || !status) return;

                if (status === 'forward') {
                    // Gunakan SweetAlert2 untuk input nomor surat
                    Swal.fire({
                        title: 'Masukkan Nomor Surat',
                        input: 'text',
                        inputLabel: 'Nomor Surat',
                        inputPlaceholder: 'Contoh: 123/XYZ/2025',
                        showCancelButton: true,
                        confirmButtonText: 'Kirim',
                        cancelButtonText: 'Batal',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Nomor surat wajib diisi!';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const nomorSurat = result.value;

                            fetch('update-submissionHandler.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: `kode_pengajuan=${encodeURIComponent(kodePengajuan)}&status=forward&nomor_surat=${encodeURIComponent(nomorSurat)}`
                                })
                                .then(response => {
                                    if (!response.ok) throw new Error('Terjadi kesalahan pada server.');
                                    return response.text();
                                })
                                .then(data => {
                                    Swal.fire('Sukses!', data, 'success').then(() => location.reload());
                                })
                                .catch(error => {
                                    Swal.fire('Gagal!', 'Gagal memperbarui status.', 'error');
                                    console.error('Error:', error);
                                });
                        }
                    });
                } else {
                    // Untuk status approved atau rejected biasa
                    const actionText = {
                        approved: 'menyetujui',
                        rejected: 'menolak'
                    } [status] || `mengubah status menjadi ${status}`;

                    Swal.fire({
                        title: `Yakin ingin ${actionText} pengajuan ${kodePengajuan}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('update-submissionHandler.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: `kode_pengajuan=${encodeURIComponent(kodePengajuan)}&status=${encodeURIComponent(status)}`
                                })
                                .then(response => {
                                    if (!response.ok) throw new Error('Terjadi kesalahan pada server.');
                                    return response.text();
                                })
                                .then(data => {
                                    Swal.fire('Sukses!', data, 'success').then(() => location.reload());
                                })
                                .catch(error => {
                                    Swal.fire('Gagal!', 'Gagal memperbarui status.', 'error');
                                    console.error('Error:', error);
                                });
                        }
                    });
                }
            });
        });
    });

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

    // Slide
    function loadSection(file) {
        fetch(file)
            .then(response => response.text())
            .then(html => {
                const contentArea = document.getElementById('content-area');
                contentArea.style.opacity = 0;
                setTimeout(() => {
                    contentArea.innerHTML = html;
                    contentArea.style.opacity = 1;
                }, 150);
            })
            .catch(error => {
                console.error('Gagal memuat konten:', error);
            });
    }
</script>