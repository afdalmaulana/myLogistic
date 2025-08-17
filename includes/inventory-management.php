<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';
?>
<?php if (isset($_GET['status'])): ?>
    <script src="../js/sweetalert.all.min.js"></script>
    <script>
        <?php if ($_GET['status'] === 'success'): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data Berhasil disimpan'
            });
        <?php elseif ($_GET['status'] === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan dalam form, mohon di ulangi'
            })
        <?php elseif ($_GET['status'] === 'incomplete'): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Data tidak lengkap',
                text: 'Harap lengkapi semua form.'
            });
        <?php endif; ?>
    </script>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');

        const formattedDate = `${yyyy}-${mm}-${dd}`;
        document.getElementById('tanggal').value = formattedDate;
    });
</script>


<div class="content-wrapper">
    <div class="content-heading">
        Inventory Management
    </div>
    <div>Manage your inventory, track incoming, and outgoing</div>
    <div class="button-invent-group">
        <button onclick="loadSection('index.php?page=stocks')">STOCK</button>
        <button onclick="loadSection('index.php?page=stock-in')">Barang Masuk</button>
        <button onclick="loadSection('stock-out.php')">Barang Keluar</button>
    </div>
    <div id="content-area">
        <?php include 'includes/stocks.php'; ?>
    </div>
</div>