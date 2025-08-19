<?php
session_start();

// ⛔ Jika belum login, redirect ke signin.php
if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit;
}
?>


        
<!-- ✅ Layout hanya ditampilkan jika user sudah login -->
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>


<?php if (isset($_GET['status'])): ?>
    <script src="../js/sweetalert.all.min.js"></script>
    <script>
        window.onload = function () {
            <?php if ($_GET['status'] === 'success'): ?>
                Swal.fire({
                    position: "top-end",
                    icon: "success",
                    title: "Selamat Datang",
                    showConfirmButton: false,
                    timer: 1500
                });
            <?php elseif ($_GET['status'] === 'error'): ?>
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
        };
    </script>
<?php endif; ?>

<div class="main-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <div id="main-content">
        <?php
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
            $allowed_pages = [
                'stocks',
                'stock-in',
                'stock-out',
                'add-user',
                'submission-in',
                'submission-out',
                'inventory-management',
                'log-inventory',
            ];

            if (in_array($page, $allowed_pages)) {
                include "includes/$page.php";
            } else {
                echo "<h3>Halaman tidak ditemukan.</h3>";
            }
        } else {
            include 'includes/dashboard.php';
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>