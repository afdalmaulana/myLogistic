<?php
require 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $kode_pengajuan = $_POST['kode_pengajuan'] ?? '';
$id = intval($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$price = $_POST['price'] ?? null;
$jumlah = intval($_POST['jumlah'] ?? 0); // jumlah yang akan diforward atau disetujui
$PNpekerja = isset($_SESSION['user']) ? $_SESSION['user'] : "";
$namaPekerja = isset($_SESSION['nama_pekerja']) ? $_SESSION['nama_pekerja'] : 'BRI';

if (!$id) {
    http_response_code(400);
    echo "Kode pengajuan tidak valid.";
    exit;
}

$allowedStatuses = ['pending', 'forward', 'approved', 'rejected', 'delete', 'completed'];
if (!in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo "Status tidak valid.";
    exit;
}

// DELETE
if ($status === 'delete') {
    $stmt = $conn->prepare("DELETE FROM pengajuan WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "Pengajuan berhasil dihapus.";
    } else {
        http_response_code(500);
        echo "Gagal menghapus pengajuan.";
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Ambil data pengajuan dulu untuk kebutuhan lainnya
$stmtSelect = $conn->prepare("SELECT * FROM pengajuan WHERE id = ?");
$stmtSelect->bind_param("i", $id);
$stmtSelect->execute();
$result = $stmtSelect->get_result();
$data = $result->fetch_assoc();
$stmtSelect->close();

if (!$data) {
    http_response_code(404);
    echo "Data pengajuan tidak ditemukan.";
    exit;
}

$jumlah_asli = intval($data['jumlah']);
$kode_uker = $data['kode_uker'];
$nama_barang = $data['nama_barang'];
$satuan = $data['satuan'];
$tanggal_pengajuan = $data['tanggal_pengajuan'];
$harga_barang = intval($data['harga_barang'] ?? 0);
$sisa_jumlah = intval($data['sisa_jumlah'] ?? 0);
$status_sisa = $data['status_sisa'] ?? null;

// =============== ✅ FORWARD ===============
if ($status === 'forward') {
    if (!$price) {
        http_response_code(400);
        echo "Nomor surat wajib diisi untuk status forward.";
        exit;
    }

    if (!$jumlah || $jumlah <= 0 || $jumlah > $jumlah_asli) {
        http_response_code(400);
        echo "Jumlah forward tidak valid.";
        exit;
    }

    $sisa = max(0, $jumlah_asli - $jumlah);
    $status_sisa = $sisa > 0 ? 'not done' : 'done';
    $keterangan = "Disetujui sejumlah " . number_format($jumlah, 0, ',', '.') . " dari total " . number_format($jumlah_asli, 0, ',', '.') . " Oleh " . $PNpekerja;

    $stmtUpdate = $conn->prepare("UPDATE pengajuan SET status = ?, price = ?, jumlah = ?, sisa_jumlah = ?, status_sisa = ?, keterangan = ?, updated_at = NOW() WHERE id = ?");
    $stmtUpdate->bind_param("ssisssi", $status, $price, $jumlah, $sisa, $status_sisa, $keterangan, $id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    echo "Pengajuan berhasil diforward.";
    $conn->close();
    exit;
}

// =============== ✅ APPROVE ===============
if ($status === 'approved') {
    $jumlah_masuk = $jumlah_asli;

    if ($jumlah_masuk <= 0) {
        http_response_code(400);
        echo "Jumlah barang tidak valid untuk approved.";
        exit;
    }

    // Ambil tanggal_pengajuan dari tabel pengajuan
    $stmtTanggal = $conn->prepare("SELECT tanggal_pengajuan FROM pengajuan WHERE id = ?");
    $stmtTanggal->bind_param("i", $id);
    $stmtTanggal->execute();
    $stmtTanggal->bind_result($tanggal_pengajuan);
    $stmtTanggal->fetch();
    $stmtTanggal->close();

    if (!$tanggal_pengajuan) {
        http_response_code(400);
        echo "Tanggal pengajuan tidak ditemukan.";
        exit;
    }

    $tanggal_approve = date('Y-m-d'); // hari ini
    $tanggal_nota = null; // dikosongkan (user uker akan isi nanti)

    // Insert ke barang_masuk
    $stmtMasuk = $conn->prepare("
        INSERT INTO barang_masuk (
            tanggal, 
            tanggal_approve, 
            tanggal_nota, 
            price, 
            nama_barang, 
            jumlah, 
            kode_uker
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtMasuk->bind_param(
        "sssisis",
        $tanggal_pengajuan,
        $tanggal_approve,
        $tanggal_nota,
        $data['price'],
        $nama_barang,
        $jumlah_masuk,
        $kode_uker
    );
    $stmtMasuk->execute();
    $stmtMasuk->close();

    // Update stok_barang
    $cekStok = $conn->prepare("SELECT jumlah FROM stok_barang WHERE nama_barang = ? AND kode_uker = ?");
    $cekStok->bind_param("ss", $nama_barang, $kode_uker);
    $cekStok->execute();
    $cekStok->store_result();

    if ($cekStok->num_rows > 0) {
        $updateStok = $conn->prepare("UPDATE stok_barang SET jumlah = jumlah + ? WHERE nama_barang = ? AND kode_uker = ?");
        $updateStok->bind_param("isss", $jumlah_masuk, $satuan, $nama_barang, $kode_uker);
        $updateStok->execute();
        $updateStok->close();
    } else {
        $insertStok = $conn->prepare("INSERT INTO stok_barang (nama_barang, jumlah, satuan, kode_uker) VALUES (?, ?, ?, ?)");
        $insertStok->bind_param("siss", $nama_barang,  $jumlah_masuk, $satuan, $kode_uker);
        $insertStok->execute();
        $insertStok->close();
    }

    // Update status pengajuan (tidak menyimpan tanggal_approve ke tabel pengajuan)
    $stmtUpdate = $conn->prepare("UPDATE pengajuan SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmtUpdate->bind_param("si", $status, $id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    echo "Pengajuan berhasil di-approve dan ditambahkan ke stok.";
    $conn->close();
    exit;
}



// =============== ✅ SELESAIKAN ===============
if ($status === 'completed') {
    $jumlah_baru = intval($_POST['jumlah_selesai'] ?? 0); // dari POST jumlah_selesai
    $jumlah_sebelumnya = intval($data['jumlah']);          // jumlah sudah disetujui sebelumnya
    $sisa_sebelumnya = intval($data['sisa_jumlah']);       // sisa barang yang belum selesai
    $jumlah_asli = $jumlah_sebelumnya + $sisa_sebelumnya;  // total pengajuan awal

    // Validasi input jumlah selesai
    if ($jumlah_baru <= 0 || $jumlah_baru > $sisa_sebelumnya) {
        http_response_code(400);
        echo "Jumlah yang dimasukkan tidak valid atau melebihi sisa pengajuan.";
        exit;
    }

    // Hitung jumlah yang sudah disetujui total setelah ini
    $jumlah_disetujui_akhir = $jumlah_sebelumnya + $jumlah_baru;
    $sisa_baru = $jumlah_asli - $jumlah_disetujui_akhir;
    $status_sisa = $sisa_baru === 0 ? 'done' : 'not done';

    // Simpan ke barang_masuk (barang masuk bertambah sesuai jumlah yang diselesaikan sekarang)
    $tanggal_approve = date('Y-m-d'); // hari ini
    $tanggal_nota = null; // dikosongkan (user uker akan isi nanti)

    $jumlah_masuk = $jumlah_baru;
    // Insert ke barang_masuk
    $stmtMasuk = $conn->prepare("
        INSERT INTO barang_masuk (
            tanggal, 
            tanggal_approve, 
            tanggal_nota, 
            price, 
            nama_barang, 
            jumlah, 
            kode_uker
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtMasuk->bind_param(
        "sssisis",
        $tanggal_pengajuan,
        $tanggal_approve,
        $tanggal_nota,
        $data['price'],
        $nama_barang,
        $jumlah_masuk,
        $kode_uker
    );
    $stmtMasuk->execute();
    $stmtMasuk->close();

    // Update atau insert stok_barang (tambahkan stok sesuai jumlah baru ini)
    $cekStok = $conn->prepare("SELECT jumlah FROM stok_barang WHERE nama_barang = ? AND kode_uker = ?");
    $cekStok->bind_param("ss", $nama_barang, $kode_uker);
    $cekStok->execute();
    $cekStok->store_result();

    if ($cekStok->num_rows > 0) {
        $updateStok = $conn->prepare("UPDATE stok_barang SET jumlah = jumlah + ? WHERE nama_barang = ? AND kode_uker = ?");
        $updateStok->bind_param("iss", $jumlah_baru, $nama_barang, $kode_uker);
        $updateStok->execute();
        $updateStok->close();
    } else {
        $insertStok = $conn->prepare("INSERT INTO stok_barang (nama_barang, jumlah, kode_uker) VALUES (?, ?, ?)");
        $insertStok->bind_param("sis", $nama_barang, $jumlah_baru, $kode_uker);
        $insertStok->execute();
        $insertStok->close();
    }

    // Update data pengajuan (jumlah = total yang sudah disetujui sampai saat ini, sisa_jumlah = sisa)
    $keterangan = "Disetujui sejumlah " . number_format($jumlah_disetujui_akhir, 0, ',', '.') . " dari total " . number_format($jumlah_asli, 0, ',', '.');
    $status_final = 'approved'; // status tetap approved karena ini proses penyelesaian

    $stmtUpdate = $conn->prepare("UPDATE pengajuan SET status = ?, jumlah = ?, sisa_jumlah = ?, status_sisa = ?, keterangan = ?, updated_at = NOW() WHERE id = ?");
    $stmtUpdate->bind_param("siissi", $status_final, $jumlah_disetujui_akhir, $sisa_baru, $status_sisa, $keterangan, $id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    echo "Barang berhasil diselesaikan. " . ($status_sisa === 'done' ? "Semua barang sudah dipenuhi." : "Masih ada sisa barang yang belum disetujui.");
    $conn->close();
    exit;
}



// =============== ✅ REJECT / COMPLETED / LAINNYA ===============
$stmt = $conn->prepare("UPDATE pengajuan SET status = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo "Status berhasil diperbarui menjadi " . ucfirst($status) . ".";
} else {
    http_response_code(500);
    echo "Gagal memperbarui status.";
}

$stmt->close();
$conn->close();
