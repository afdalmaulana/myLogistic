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
        <?php if ($_GET['status'] === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: 'Kombinasi username dan password salah'
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
    document.addEventListener("DOMContentLoaded", function() {
        const signinBtn = document.getElementById("signinBtn");

        if (signinBtn) {
            signinBtn.addEventListener("click", function(e) {
                // e.preventDefault(); // Mencegah link langsung dijalankan
                Swal.fire({
                    position: "top-end",
                    icon: "success",
                    title: "Selamat Datang",
                    showConfirmButton: false,
                    timer: 1500,
                });
            });
        }
    });
</script>


<div class="background">
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
</div>
<form action="sign-inHandler.php" method="POST" onsubmit="return showLoadingSignin()">
    <div class="login-wrapper">
        <div class="form-input-login">
            <div class="login-heading">
                <div style="font-size: 22px; margin-top: 12px; font-weight:700">Welcome to LogiTrack</div>
                <p style="font-size: 12px;">Cluster SBO </p>
                <p>KC Makassar Ahmad Yani || Sudirman || Tamalanrea</p>
                <p>Masukkan username sesuai dengan kode uker</p>
            </div>
            <div class="input-login">
                <div style="display: flex; flex-direction:column">
                    <label style="display: flex; left:0">Username</label>
                    <input type="text" name="username" class="list-input" placeholder="Masukkan Username" style="border-radius: 10px;">
                </div>
                <div style="display: flex; flex-direction:column; position:relative">
                    <label>Password</label>
                    <input type="password" name="password" id="password" class="list-input" placeholder="Masukkan Password" style="border-radius: 10px;">
                    <span onclick="togglePassword()" style="position: absolute; right: 10px; top: 34px; cursor: pointer; color:black">
                        <i class="fa fa-eye-slash" id="toggleIcon"></i>
                    </span>
                </div>

                <div class="">
                    <button type="submit" id="signinBtn" class="button-signin">Sign in</button>
                </div>
            </div>
            <p>Forget Your Password ? Contact Admin</p>
            <p>Copyright by Muh. Afdal Maulana Said & Reinaldo Wattimena</p>
        </div>
</form>