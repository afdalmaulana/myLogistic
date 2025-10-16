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
                return (statusA === "approved" ? -1 : 1) - (statusB === "approved" ? -1 : 1);
            }
        });

        rows.forEach(row => tbody.appendChild(row)); // re-append sorted rows

        // Ubah arah panah
        document.getElementById("sortArrow").textContent = sortAsc ? "↑" : "↓";

        sortAsc = !sortAsc;
    }
    let sortAscProses = true;

    function toggleSortProses() {
        const table = document.getElementById("dataTable-incomplete");
        const tbody = table.tBodies[0];
        const rows = Array.from(tbody.rows);

        rows.sort((a, b) => {
            const prosesA = a.cells[10].innerText.trim().toLowerCase(); // Kolom Proses
            const prosesB = b.cells[10].innerText.trim().toLowerCase();

            if (sortAscProses) {
                return (prosesA === "done" ? -1 : 1) - (prosesB === "done" ? -1 : 1);
            } else {
                return (prosesA === "not done" ? -1 : 1) - (prosesB === "not done" ? -1 : 1);
            }
        });

        rows.forEach(row => tbody.appendChild(row));

        document.getElementById("sortArrowProses").textContent = sortAscProses ? "↑" : "↓";

        sortAscProses = !sortAscProses;
    }

    let sortCompleteStatus = true;

    function toggleSortComplete() {
        const table = document.getElementById("dataTable-complete");
        const tbody = table.tBodies[0];
        const rows = Array.from(tbody.rows);

        rows.sort((a, b) => {
            const statusA = a.cells[5].innerText.trim().toLowerCase();
            const statusB = b.cells[5].innerText.trim().toLowerCase();

            if (sortAsc) {
                return (statusA === "approved" ? -1 : 1) - (statusB === "approved" ? -1 : 1);
            } else {
                return (statusA === "rejected" ? -1 : 1) - (statusB === "rejected" ? -1 : 1);
            }
        });

        rows.forEach(row => tbody.appendChild(row)); // re-append sorted rows

        // Ubah arah panah
        document.getElementById("sortArrowComplete").textContent = sortAsc ? "↑" : "↓";

        sortAsc = !sortAsc;
    }

    let sortForwardProses = true;

    function toggleSortForwardProses() {
        const table = document.getElementById("dataTable-forward");
        const tbody = table.tBodies[0];
        const rows = Array.from(tbody.rows);

        rows.sort((a, b) => {
            const prosesA = a.cells[8].innerText.trim().toLowerCase();
            const prosesB = b.cells[8].innerText.trim().toLowerCase();

            if (sortForwardProses) {
                return (prosesA === "not done" ? -1 : 1) - (prosesB === "not done" ? -1 : 1);
            } else {
                return (prosesA === "done" ? -1 : 1) - (prosesB === "done" ? -1 : 1);
            }
        });

        rows.forEach(row => tbody.appendChild(row));

        document.getElementById("sortArrowProsesForward").textContent = sortForwardProses ? "↑" : "↓";

        sortForwardProses = !sortForwardProses;
    }
</script>