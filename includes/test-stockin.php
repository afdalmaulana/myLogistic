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


<div class="content-wrapper2">
    <div class="content-heading">
        Inventory Management
    </div>
    <div>Manage your inventory, track incoming, and outgoing</div>
<form action="stockIn_connect.php" method="POST" onsubmit="return showLoading()">
        <div class="body-content">
            <p>Record Barang Masuk</p>
            <input type="date" id="tanggal" name="tanggal" class="list-input" placeholder="Tanggal" style="border-radius: 10px;" required readonly>
            <div><i>* Tanggal Otomatis mengikut hari ini</i></div>
            <div class="form-input">
    <div class="submission-left">
        <div class="form-group">
            <label>Nomor Nota</label>
            <input type="text" name="nomor_nota" class="list-input">
        </div>
        <div class="form-group">
            <label>Tanggal Nota</label>
            <input type="date" name="tanggal_nota" class="list-input">
        </div>
        <div class="form-group">
            <label>Nama Barang</label>
            <input type="text" name="nama_barang" class="list-input">
        </div>
    </div>
    <div class="submission-right">
        <div class="form-group">
            <label>Harga Barang</label>
            <input type="text" name="harga_barang" class="list-input">
        </div>
        <div class="form-group">
            <label>Jumlah</label>
            <input type="number" name="jumlah" class="list-input">
        </div>
        <div class="form-group">
            <button type="submit" class="button-send">Kirim</button>
        </div>
    </div>
</div>

        </div>
    </form>
</div>
<!-- <?php include 'includes/log-stock-in.php'; ?> -->