<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';


$queryAnggaran = "SELECT * FROM anggaran ORDER BY id_anggaran ASC";
$anggaran = $conn->query($queryAnggaran);
?>


<?php if (isset($_GET['status'])): ?>
    <script src="../js/sweetalert.all.min.js"></script>
    <script>
        <?php if ($_GET['status'] === 'success'): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Pengajuan Berhasil!',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = 'index.php?page=submission-out';
            });
        <?php elseif ($_GET['status'] === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan Data',
                text: 'Silahkan Coba Secara Berkala atau Hubungi Admin.'
            });
        <?php elseif ($_GET['status'] === 'incomplete'): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Data tidak lengkap',
                text: 'Harap lengkapi semua form.'
            });
        <?php elseif ($_GET['status'] === 'duplicate'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Kode Pengajuan Sudah Ada',
                text: 'Harap masukkan kode pengajuan yang berbeda'
            });
        <?php endif; ?>
    </script>
<?php endif; ?>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set tanggal otomatis
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const formattedDate = `${yyyy}-${mm}-${dd}`;

        const tanggalInput = document.getElementById('tanggal_pengajuan');
        if (tanggalInput) {
            tanggalInput.value = formattedDate;
        }

        // Format angka untuk input jumlah anggaran
        const inputJumlah = document.getElementById('jumlahAnggaran');
        if (inputJumlah) {
            inputJumlah.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            });
        }
    });
</script>

<div class="content-wrapper">
    <div>
        <div class="sub-content">
            <h4 style="font-weight: 800; font-size:32px;">New Form Entry</h4>
            <p><i>Create a new submission and track incoming responses efficiently.</i></p>
        </div>
    </div>
    <form action="submission-inHandler.php" method="POST" enctype="multipart/form-data" onsubmit="return showLoading()">
        <div class="body-content">
            <div class="form-input">
                <div class="submission-left">
                    <div class="form-group">
                        <label for="">Submission Date</label>
                        <input type="date" id="tanggal_pengajuan" name="tanggal_pengajuan" class="list-input" placeholder="Tanggal" style="border-radius: 10px;" required readonly>
                    </div>
                    <div class="form-group">
                        <label for="">Submission Number</label>
                        <input type="text" name="kode_pengajuan" class="list-input" placeholder="Input here ... " style="border-radius: 10px;">
                    </div>
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="nama_barang" class="list-input" placeholder="Input here ..." style="border-radius: 10px;">
                    </div>
                    <div class="form-group">
                        <label for="">Budget Name</label>
                        <select name="id_anggaran" class="list-input" required style="border-radius: 10px;">
                            <option value="" disabled selected hidden>Choose Budget</option>
                            <?php
                            if ($anggaran && $anggaran->num_rows > 0) {
                                while ($row = $anggaran->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['id_anggaran']) . '">' . htmlspecialchars($row['nama_anggaran']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="submission-right">
                    <div class="form-group">
                        <label>Amount of Budget</label>
                        <input type="text" name="jumlah_anggaran" id="jumlahAnggaran" class="list-input" placeholder="Input here ..." style="border-radius: 10px;">
                    </div>
                    <div class="form-group">
                        <label>Input Quantity</label>
                        <input type="number" name="jumlah" class="list-input" placeholder="Input here ..." style="border-radius: 10px;">
                    </div>
                    <div class="form-group">
                        <label>Choose Box / Piece(s)</label>
                        <select name="satuan" class="list-input" required style="border-radius: 10px;">
                            <option value="" disabled selected hidden>Choose</option>
                            <option value="dos">dos</option>
                            <option value="pcs">pcs</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" id="submitBtn" class="button-send">Submit</button>
                    </div>
                </div>
            </div>


        </div>
    </form>
</div>