<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<div class="sidebar">
    <div class="side-left">
        <div class="menu-label" style="font-size: 12px; margin-top: 16px; color: #423f3fff;">NAVIGATION</div>
    </div>
    <div class="menusidebar">
        <a href="index.php" class="menu-item" onclick="return loadingLink(this, event)">Home</a>
        <div id="menu-surat">
            <div class="menu-label" style="font-size: 12px;">PENGAJUAN</div>
            <a href="index.php?page=submission-in" class="menu-item" onclick="return loadingLink(this, event)">Buat Pengajuan</a>
            <a href="index.php?page=submission-out" class="menu-item" onclick="return loadingLink(this, event)">Liat Pengajuan</a>
        </div>
        <div id="menu-logistik">
            <div class="menu-label" style="font-size: 12px;">INFORMASI BARANG</div>
            <a href="index.php?page=inventory-management" class="menu-item" onclick="return loadingLink(this, event)">Inventory Management</a>
            <a href="index.php?page=log-inventory" class="menu-item" onclick="return loadingLink(this, event)">Log Inventory</a>
            <a href="index.php?page=log-stock-out" class="menu-item" onclick="return loadingLink(this, event)">Barang Keluar</a>
            <a href="index.php?page=log-stock-in" class="menu-item" onclick="return loadingLink(this, event)">Barang Masuk</a>
            <!-- <a href="index.php?page=stocks" class="menu-item" onclick="return loadingLink(this, event)">Stok Barang</a> -->
        </div>

        <!-- Logout selalu muncul -->
        <div id="menu-logout" style="margin-top: 20px;">
            <a href="logout.php" class="menu-item" onclick="return confirm('Yakin ingin logout?')">Logout</a>
        </div>

        <!-- Tambah Akun hanya untuk admin -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div>
                <a href="index.php?page=add-user" class="menu-item">Tambah Akun</a>
            </div>
        <?php endif; ?>
    </div>
</div>