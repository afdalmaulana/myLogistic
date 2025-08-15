<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/footer.php'; ?>


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
                title: 'Login Gagal',
                text: 'Kombinasi username dan password salah'
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

<form action="sign-inHandler.php" method="POST" onsubmit="return showLoading()">
    <div class="login-wrapper">
        <div class="dashboard_login">
            <div class="form-input-login">
                <!-- <div style="display: flex; flex-direction:row; justify-content:space-between;"> -->
                <div style="font-size: 22px; margin-top: 12px; font-weight:700">Welcome to My-logistic</div>
                <!-- </div> -->
                <p class="" style="font-size: 10px;">Masukkan username sesuai dengan kode uker</p>
                <div class="input-mail">
                    <div style="display: flex; flex-direction:column">
                        <label style="display: flex; left:0">Username</label>
                        <input type="text" name="username" class="list-input" placeholder="Masukkan Username" style="border-radius: 10px;">
                    </div>
                    <div style="display: flex; flex-direction:column">
                        <label style="display: flex; left:0">Password</label>
                        <input type="password" name="password" class="list-input" placeholder="Masukkan Password" style="border-radius: 10px;">
                    </div>
                    <div class="">
                        <button type="submit" id="submitBtn" class="button-signin">Sign in</button>
                    </div>
                </div>
            </div>
        </div>
</form>