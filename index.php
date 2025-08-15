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

<div id="main-content">
    <?php
    if (isset($_GET['page'])) {
        $page = $_GET['page'];

        // Daftar halaman yang boleh dimuat
        $allowed_pages = [
            'form-mail-in',
            'form-mail-out',
            'log-stock-in',
            'log-stock-out',
            'stocks',
            'stock-in',
            'stock-out',
            'add-user',
            'submission-in',
            'submission-out'
        ];

        if (in_array($page, $allowed_pages)) {
            include "includes/$page.php";
        } else {
            echo "<h3>Halaman tidak ditemukan.</h3>";
        }
    } else {
        // Jika tidak ada parameter page, tampilkan dashboard
        include 'includes/dashboard.php';
    }
    ?>
</div>

<?php include 'includes/footer.php'; ?>