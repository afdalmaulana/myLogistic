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
</script>
<div class="nav">
    <div class="isinavbar">
        <!-- Bagian Kiri -->
        <div class="nav-left">
            <img src="../assets/img/logo.png" alt="bri" style="height:50px">
        </div>
        <div class="nav-right">
            <div class="profile-container">
                <div class="profile-icon">
                    <i class="fa fa-user-circle"></i>
                </div>
                <div class="profile-dropdown">
                    <div class="profile-groups">
                        <div class="profile-name"><?php echo $kodeUker; ?></div>
                        <div style="font-size: 32px; margin-top:-16px; color:black;"> - </div>
                        <div class="profile-name"><?php echo $jabatan; ?></div>
                    </div>
                    <div class="profile-name"><?php echo $username; ?></div>
                    <div class="profile-name"><?php echo $namaPekerja; ?></div>
                    <form action="logout.php" method="post">
                        <a href="logout.php" class="logoutBtn" id="logoutBtn">LOG OUT</a>
                    </form>
                </div>
            </div>
        </div>


        <!-- <a href="index.php?page=formInMail" class="menu-item">Tulis Surat Masuk</a>
            <a href="index.php?page=formOutMail" class="menu-item">Tulis Surat Keluar</a> -->
        <!-- <button id="signinbutton">Sign in</button> -->
    </div>
</div>
</div>