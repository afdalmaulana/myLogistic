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
            $allowed_pages = [
                'add-user',
                'submission-in',
                'submission-out',
                'inventory-management',
                'log-inventory',
                'list-users',
                'inventory-It'

            ];

            if (in_array($page, $allowed_pages)) {
                include "includes/$page.php";
            } else {
                // tampilkan halaman error khusus
                include 'includes/403.php';
            }
        } else {
            include 'includes/dashboard.php';
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>