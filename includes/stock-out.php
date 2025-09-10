<?php
require_once __DIR__ . '/../db_connect.php';

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
    <div class="body-content">
        <p>Record Barang Keluar</p>
        <input type="date" id="tanggal" name="tanggal" class="list-input" placeholder="Tanggal" style="border-radius: 10px;" required readonly>
        <div><i>* Tanggal Otomatis mengikut hari ini</i></div>
        <div class="form-input">
            <div class="submission-left">
                <div class="form-group">
                    <label>Pilih Nama Barang</label>
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
                </div>
            </div>
            <div class="submission-right">
                <div class="form-group">
                    <label>Departemen</label>
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
    </div>
</form>