<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil nama user dari session
$username = isset($_SESSION['user']) ? $_SESSION['user'] : 'Username';
$kodeUker = isset($_SESSION['kode_uker']) ? $_SESSION['kode_uker'] : 'Kode Uker';
$namaPekerja = isset($_SESSION['nama_pekerja']) ? $_SESSION['nama_pekerja'] : 'BRI';
$jabatan = isset($_SESSION['nama_jabatan']) ? $_SESSION['nama_jabatan'] : '';
?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const profileContainer = document.getElementById("profileContainer");
        const logoutForm = document.getElementById("logoutForm");

        // Toggling dropdown saat diklik
        profileContainer.addEventListener("click", function(e) {
            e.stopPropagation(); // agar klik tidak tembus ke document
            this.classList.toggle("active");
        });

        // Klik di luar area profil menutup dropdown
        document.addEventListener("click", function(e) {
            if (!e.target.closest("#profileContainer")) {
                profileContainer.classList.remove("active");
            }
        });

        // Konfirmasi logout
        logoutForm.addEventListener("submit", function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Yakin ingin logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    logoutForm.submit(); // tetap pakai POST
                }
            });
        });
    });
</script>



<div class="nav">
    <div class="isinavbar">
        <!-- Bagian Kiri -->
        <div class="nav-left">
            <img src="../assets/img/logo.png" alt="bri" style="height:50px">
        </div>
        <div class="nav-right">
            <div class="profile-container" id="profileContainer">
                <div class="profile-icon">
                    <i class="fa fa-user-circle"></i>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-groups">
                        <div class="profile-name"><?= $kodeUker ?></div>
                        <div style="font-size: 32px; margin-top:-16px; color:black;"> - </div>
                        <div class="profile-name"><?= $jabatan ?></div>
                    </div>
                    <div class="profile-name"><?= $username ?></div>
                    <div class="profile-name"><?= $namaPekerja ?></div>
                    <form id="logoutForm" action="logout.php" method="post">
                        <button type="submit" class="logoutBtn">LOG OUT</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>