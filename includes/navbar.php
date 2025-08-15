<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil nama user dari session
$namaUker = isset($_SESSION['nama_uker']) ? $_SESSION['nama_uker'] : 'Nama Uker';
$kodeUker = isset($_SESSION['kode_uker']) ? $_SESSION['kode_uker'] : 'Kode Uker';
?>
<div class="nav">
    <div class="isinavbar">
        <!-- Bagian Kiri -->
        <div class="nav-left">
            <h3 class="nav-title">Dashboard My Logistic</h3>
        </div>
        <div class="nav-right">
            <div class="dropdown">
                <button class="button-dropdown dropdown-toggle" onclick="toggleDropdown('dropdownContent')">
                    Pengajuan
                </button>
                <div class="dropdown-content" id="dropdownContent">
                    <a href="index.php?page=form-mail-in" onclick="return loadingLink(this, event)">Liat Pengajuan</a>
                    <a href="index.php?page=form-mail-out" onclick="return loadingLink(this, event)">Buat Pengajuan</a>
                </div>
            </div>
            <!-- Dropdown Barang -->
            <div class="dropdown">
                <button class="button-dropdown dropdown-toggle">
                    Barang
                </button>
                <div class="dropdown-content" id="dropdownContentLogistic">
                    <a href="index.php?page=stocks" onclick="return loadingLink(this, event)">Stock</a>
                    <a href="index.php?page=stock-in" onclick="return loadingLink(this, event)">Barang Masuk</a>
                    <a href="index.php?page=stock-out" onclick="return loadingLink(this, event)">Barang Keluar</a>
                </div>
            </div>
            <div class="profile-container">
                <div class="profile-icon">
                    <i class="fa fa-user-circle"></i>
                </div>
                <div class="profile-dropdown">
                    <div class="profile-name"><?php echo $kodeUker; ?></div>
                    <div class="profile-name"><?php echo $namaUker; ?></div>
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