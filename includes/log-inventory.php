<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';
?>



<div class="content-wrappers">
    <div class="content-heading">Log Inventory Management</div>
    <div>Track incoming, and outgoing inventory</div>
    <div class="button-invent-group">
        <button onclick="loadLog('log-stock-in', this)">Log Barang Masuk</button>
        <button onclick="loadLog('log-stock-out', this)">Log Barang Keluar</button>
    </div>

    <div id="content-areas">
        <?php include 'includes/log-stock-in.php'; ?>
    </div>

    <div id="loading-indicator" style="display: none;">
        <div class="spinner"></div>
    </div>
</div>