<div class="nav">
    <div class="isinavbar">
        <!-- Bagian Kiri -->
        <div class="nav-left">
            <h3 class="nav-title">Dashboard My Logistic</h3>
        </div>
        <div class="nav-right">
            <div class="dropdown">
                <button class="button-dropdown dropdown-toggle" onclick="toggleDropdown('dropdownContent')">
                    Pengajuan
                </button>
                <div class="dropdown-content" id="dropdownContent">
                    <a href="index.php?page=form-mail-in" onclick="return loadingLink(this, event)">Liat Pengajuan</a>
                    <a href="index.php?page=form-mail-out" onclick="return loadingLink(this, event)">Buat Pengajuan</a>
                </div>
            </div>
            <!-- Dropdown Barang -->
            <div class="dropdown">
                <button class="button-dropdown dropdown-toggle">
                    Barang
                </button>
                <div class="dropdown-content" id="dropdownContentLogistic">
                    <a href="index.php?page=stocks" onclick="return loadingLink(this, event)">Stock</a>
                    <a href="index.php?page=stock-in" onclick="return loadingLink(this, event)">Barang Masuk</a>
                    <a href="index.php?page=stock-out" onclick="return loadingLink(this, event)">Barang Keluar</a>
                </div>
            </div>
            <div id="menu-logout">
                <a href="logout.php" class="logoutBtn" onclick="return confirm('Yakin ingin logout?')">LOG OUT</a>
            </div>
        </div>

        <!-- <a href="index.php?page=formInMail" class="menu-item">Tulis Surat Masuk</a>
            <a href="index.php?page=formOutMail" class="menu-item">Tulis Surat Keluar</a> -->
        <!-- <button id="signinbutton">Sign in</button> -->
    </div>
</div>
</div>