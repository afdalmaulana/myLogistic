<?php

// Cek apakah user punya akses
if ($_SESSION['role'] !== 'admin') {
    // Gak perlu pakai header jika sudah ada output, tampilkan pesan langsung dan stop
    // echo "<h2>Maaf ini area terlarang, hanya orang ganteng yang bisa masuk</h2>";
    include 'includes/403.php';
    exit;
}

// Jika belum login atau bukan admin, kembalikan ke index
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: signin.php");
    exit;
}
?>
<?php
include 'db_connect.php';
$query = "SELECT kode_uker, nama_uker FROM unit_kerja ORDER BY nama_uker";
$result = $conn->query($query);

$queryJabatan = "SELECT * FROM jabatan ORDER BY id_jabatan";
$jabatanResult = $conn->query($queryJabatan)
?>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        }
    }
</script>


<?php if (isset($_GET['status'])): ?>
    <script src="../js/sweetalert.all.min.js"></script>
    <script>
        <?php if ($_GET['status'] === 'success'): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'User berhasil ditambahkan'
            });
        <?php elseif ($_GET['status'] === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan dalam form, mohon di ulangi'
            })
        <?php elseif ($_GET['status'] === 'duplicate'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Kode Uker sudah Terdaftar'
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

<form action="add_userHandler.php" method="POST" onsubmit="return showLoading()">
    <div class="content-wrapper">
        <div style="font-size: 32px; margin-top: 12px; font-weight:700">Tambah Akun</div>
        <p>Masukkan username sesuai dengan kode uker</p>
        <div class="body-content">
            <div class="form-input">
                <div class="submission-left">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="list-input" placeholder="Masukkan PN Pekerja" style="border-radius: 10px;">
                    </div>
                    <div class="form-group">
                        <label for="">Nama Unit Kerja</label>
                        <select name="kode_uker" class="list-input" required style="border-radius: 10px;">
                            <option value="" disabled selected hidden>Pilih Unit Kerja</option>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <option value="<?= $row['kode_uker']; ?>">
                                    <?= htmlspecialchars($row['nama_uker']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group" style="position: relative;">
                        <label for="">Password</label>
                        <input type="password" name="password" id="password" class="list-input" placeholder="Masukkan Password" style="border-radius: 10px;">
                        <span onclick="togglePassword()" style="position: absolute; right:10px; top:29px; font-size:26px; cursor:pointer">
                            <i class="fa fa-eye-slash" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>
                <div class="submission-right">
                    <div class="form-group">
                        <label>Nama Pekerja</label>
                        <input type="text" name="nama_pekerja" class="list-input" placeholder="Masukkan Nama Pekerja" style="border-radius: 10px;">
                    </div>
                    <div class="form-group">
                        <label for="">Role</label>
                        <select name="role" class="list-input" required style="border-radius: 10px;">
                            <option value="" disabled selected hidden>Pilih Role</option>
                            <option value="user">user</option>
                            <option value="admin">admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Jabatan</label>
                        <select name="id_jabatan" class="list-input" required style="border-radius: 10px;">
                            <option value="" disabled selected hidden>Pilih Jabatan</option>
                            <?php while ($row = $jabatanResult->fetch_assoc()): ?>
                                <option value="<?= $row['id_jabatan']; ?>">
                                    <?= htmlspecialchars($row['nama_jabatan']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="">
                <button type="submit" id="submitBtn" class="button-send" style="margin-top:10px">Kirim</button>
            </div>
        </div>
</form>