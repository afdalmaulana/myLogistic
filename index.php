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


<div class="main-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <div id="main-content">
        <?php
        if (isset($_GET['page'])) {
            $page = $_GET['page'];

            // ⛔ Daftar halaman yang diizinkan untuk semua user
            $allowed_pages = [
                'add-user',
                'submission-in',
                'submission-out',
                'inventory-management',
                'log-inventory',
                'list-users',
                'inventory-It'
            ];

            // ⛔ Batasi akses user tertentu
            $blocked_user = '90173431';
            $blocked_pages = ['inventory-management', 'submission-out', 'log-inventory', 'submission-in'];

            if (!in_array($page, $allowed_pages)) {
                include 'includes/403.php'; // halaman tidak dikenal
            } elseif ($username === $blocked_user && in_array($page, $blocked_pages)) {
                include 'includes/403.php'; // user tidak diizinkan akses halaman ini
            } else {
                include "includes/$page.php"; // halaman aman, tampilkan
            }
        } else {
            include 'includes/dashboard.php';
        }
        ?>
    </div>
</div>


<?php include 'includes/footer.php'; ?>