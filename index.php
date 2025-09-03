<?php
session_start();

if (!isset($_SESSION['user'])) {
    // Belum login, tampilkan form login (include signin.php)
    include 'signin.php';
    exit; // pastikan berhenti di sini supaya gak jalan ke bawah
}

// Kalau sudah login, tampilkan dashboard (bisa langsung include dashboard.php)
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="main-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <div id="main-content">
        <?php
        // Kalau ada page lain, bisa di-handle di sini, tapi default tampil dashboard
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
                include 'includes/403.php';
            }
        } else {
            include 'includes/dashboard.php'; // Halaman dashboard default
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>