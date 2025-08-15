<?php
require 'db_connect.php';

$stokQuery = "SELECT nama_barang FROM stok_barang ORDER BY nama_barang ASC";
$stokResult = $conn->query($stokQuery);
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
<form action="stockOut_connect.php" method="POST" onsubmit="return showLoading()">
    <div class="dashboard-mailin">
        <div class="form-input">
            <div style="display: flex; flex-direction:row; justify-content:space-between;">
                <div style="font-size: 32px; margin-top: 12px; font-weight:700">Formulir Barang Keluar</div>
                <a href="index.php?page=log-stock-out" class="button-log">
                    <i class="fa fa-trash-o" aria-hidden="true" onclick="return loadingLink(this, event)"></i> Lihat Log
                </a>
            </div>
            <p>Masukkan sesuai dengan ketentuan yang berlaku</p>
            <div class="input-mail">
                <!-- <input type="date" name="tanggal" class="list-input" placeholder="Tanggal" style="border-radius: 10px;" required> -->
                <input type="date" id="tanggal" name="tanggal" class="list-input" placeholder="Tanggal" style="border-radius: 10px;" required readonly>

                <select name="nama_barang" class="list-input" required style="border-radius: 10px;">
                    <option value="" disabled selected hidden>Pilih Nama Barang</option>
                    <?php
                    if ($stokResult->num_rows > 0) {
                        while ($row = $stokResult->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['nama_barang']) . '">' . htmlspecialchars($row['nama_barang']) . '</option>';
                        }
                    } else {
                        echo '<option value="" disabled>Belum ada barang tersedia</option>';
                    }
                    ?>
                </select>

                <input type="number" name="jumlah" class="list-input" placeholder="Jumlah" style="border-radius: 10px;" required>
                <select name="divisi" class="list-input" required style="border-radius: 10px;">
                    <option value="" disabled selected hidden>Pilih Departemen</option>
                    <option value="OPS">Operasional</option>
                    <option value="HC">Human Capital</option>
                    <option value="LOG">Logistik</option>
                    <option value="ADK">Administrasi Keuangan</option>
                    <option value="RMFT">RMFT</option>
                </select>
                <div>
                    <button type="submit" id="submitBtn" class="button-send">Kirim</button>
                </div>
            </div>
        </div>
    </div>
</form>