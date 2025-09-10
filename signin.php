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
    <script src="js/sweetalert.all.min.js"></script>
    <script>
        <?php if ($_GET['status'] === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: 'Kombinasi username dan password salah'
            }).then(() => {
                if (window.history.replaceState) {
                    // Buat URL tanpa query string
                    const cleanUrl = window.location.origin + window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }
            });
        <?php elseif ($_GET['status'] === 'incomplete'): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Data tidak lengkap',
                text: 'Harap lengkapi semua form.'
            }).then(() => {
                if (window.history.replaceState) {
                    const cleanUrl = window.location.origin + window.location.pathname;
                    window.history.replaceState({}, document.title, cleanUrl);
                }
            });
        <?php endif; ?>
    </script>
<?php endif; ?>



<!-- <script>
    document.addEventListener("DOMContentLoaded", function() {
        const signinBtn = document.getElementById("signinBtn");

        if (signinBtn) {
            signinBtn.addEventListener("click", function(e) {
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
</script> -->


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
                <div style="font-size: 26px; margin-top: 12px; font-weight:700">Welcome to LogiTrack</div>
                <div style="display:flex; flex-direction:column; gap:2px">
                    <p style="font-size: 20px; margin: 0;">Cluster SBO </p>
                    <p style="font-size: 10px; margin: 0;">Makassar Ahmad Yani || Makassar Sudirman || Tamalanrea</p>
                </div>
            </div>
            <div class="input-login">
                <div style="display: flex; flex-direction:column">
                    <label style="display: flex; left:0">Username</label>
                    <input type="text" name="username" class="list-input" placeholder="Masukkan Username" style="border-radius: 10px;">
                </div>
                <div style="display: flex; flex-direction:column; position:relative">
                    <label style="display: flex; left:0;">Password</label>
                    <input type="password" name="password" id="password" class="list-input" placeholder="Masukkan Password" style="border-radius: 10px;">
                    <span onclick="togglePassword()" style="position: absolute; right: 10px; top: 34px; cursor: pointer; color:black">
                        <i class="fa fa-eye-slash" id="toggleIcon"></i>
                    </span>
                </div>

                <div class="">
                    <button type="submit" id="signinBtn" class="button-signin">Sign in</button>
                </div>
            </div>
            <div>
                <p>Forget Your Password ? Contact Admin</p>
                <p style="bottom : 0">Copyright by Muh. Afdal Maulana Said & Reinaldo Wattimena</p>
            </div>
        </div>
</form>