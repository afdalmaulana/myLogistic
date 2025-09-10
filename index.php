<?php
session_start();

if (!isset($_SESSION['user'])) {
    include 'signin.php';
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php if (isset($_SESSION['login_success'])): ?>
    <script src="js/sweetalert.all.min.js"></script>
    <script>
        Swal.fire({
            position: "top-end",
            icon: "success",
            html: `<div style="font-size: 18px; font-weight: bold;">Selamat Datang</div>
                   <div style="font-size: 16px;"><?= $_SESSION['nama_pekerja'] ?? $_SESSION['user'] ?></div>`,
            showConfirmButton: false,
            timer: 2500
        }).then(() => {
            const cleanUrl = window.location.origin + window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        });
    </script>
    <?php unset($_SESSION['login_success']); ?>
<?php endif; ?>

<script>
    // Sembunyikan spinner setelah halaman selesai dimuat
    window.addEventListener('load', function() {
        const loader = document.getElementById('loading-overlay');
        if (loader) loader.style.display = 'none';
    });

    // Tampilkan spinner saat klik menu/page lain
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function() {
            const href = this.getAttribute('href');
            if (href && href.includes('page=')) {
                const loader = document.getElementById('loading-overlay');
                if (loader) loader.style.display = 'flex';
            }
        });
    });
</script>

<!-- Tambahkan ini -->
<div id="loading-overlay">
    <div class="spinner"></div>
</div>
<!-- End loading spinner -->

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
                'inventory-It',
                'list-uker'
            ];

            if (in_array($page, $allowed_pages)) {
                include "includes/$page.php";
            } else {
                include 'includes/403.php';
            }
        } else {
            include 'includes/dashboard.php';
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php include 'db_close.php'; ?>