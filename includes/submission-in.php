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
        document.getElementById('tanggal_pengajuan').value = formattedDate;
    });
</script>
<div class="content-wrapper">
    <div>
        <div class="sub-content">
            <h4 style="font-weight: 800; font-size:32px;">Form Pengajuan</h4>
        </div>
    </div>
    <form action="submission-inHandler.php" method="POST" enctype="multipart/form-data" onsubmit="return showLoading()">
        <div class="body-content">
            <div class="form-input">
                <div class="submission-left">
                    <div class="form-group">
                        <label for="">Tanggal Hari ini</label>
                        <input type="date" id="tanggal_pengajuan" name="tanggal_pengajuan" class="list-input" placeholder="Tanggal" style="border-radius: 10px;" required readonly>
                    </div>
                    <div class="form-group">
                        <label for="">Nomor Pengajuan / Surat</label>
                        <input type="text" name="kode_pengajuan" class="list-input" placeholder="Input disini ... " style="border-radius: 10px;">
                    </div>
                </div>
                <div class="submission-right">
                    <div class="form-group">
                        <label>Perihal</label>
                        <input type="text" name="perihal" class="list-input" placeholder="Perihal" style="border-radius: 10px;">
                    </div>
                    <div>
                        <button type="submit" id="submitBtn" class="button-send">Kirim</button>
                    </div>
                </div>
            </div>


        </div>
    </form>
</div>