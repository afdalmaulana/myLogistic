<?php

// Cek apakah user punya akses
if ($_SESSION['role'] !== 'admin') {
    // Gak perlu pakai header jika sudah ada output, tampilkan pesan langsung dan stop
    echo "<h2>Maaf ini area terlarang, hanya orang ganteng yang bisa masuk</h2>";
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
?>


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
    <div class="signup-wrapper">
        <div class="dashboard_login">
            <div class="form-input-login">
                <div style="font-size: 32px; margin-top: 12px; font-weight:700">Tambah Akun</div>
                <!-- </div> -->
                <p>Masukkan username sesuai dengan kode uker</p>
                <div class="input-mail">
                    <div style="display: flex; flex-direction:column">
                        <label style="display: flex; left:0">Username</label>
                        <input type="text" name="username" class="list-input" placeholder="Masukkan Kode Uker" style="border-radius: 10px;">
                    </div>
                    <div style="display: flex; flex-direction:column">
                        <label style="display: flex; left:0">Nama Unit Kerja</label>
                        <select name="kode_uker" class="list-input" required style="border-radius: 10px;">
                            <option value="" disabled selected hidden>Pilih Unit Kerja</option>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <option value="<?= $row['kode_uker']; ?>">
                                    <?= htmlspecialchars($row['nama_uker']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div style="display: flex; flex-direction:column">
                            <label style="display: flex; left:0">Password</label>
                            <input type="password" name="password" class="list-input" placeholder="Masukkan Password" style="border-radius: 10px;">
                        </div>
                        <div style="display: flex; flex-direction:column">
                            <select name="role" class="list-input" required style="border-radius: 10px;">
                                <option value="" disabled selected hidden>Pilih Role</option>
                                <option value="user">user</option>
                                <option value="admin">admin</option>

                            </select>
                        </div>

                        <div class="">
                            <button type="submit" id="submitBtn" class="button-send">Kirim</button>
                        </div>
                    </div>
                </div>
            </div>
</form>