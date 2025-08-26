<?php
require 'db_connect.php';

$kode_pengajuan = $_POST['kode_pengajuan'] ?? '';
$status = $_POST['status'] ?? '';
$nomor_surat = $_POST['nomor_surat'] ?? null;
$jumlah = intval($_POST['jumlah'] ?? 0); // jumlah yang akan diforward atau disetujui

if (!$kode_pengajuan) {
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
    $stmt = $conn->prepare("DELETE FROM pengajuan WHERE kode_pengajuan = ?");
    $stmt->bind_param("s", $kode_pengajuan);
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
$stmtSelect = $conn->prepare("SELECT * FROM pengajuan WHERE kode_pengajuan = ?");
$stmtSelect->bind_param("s", $kode_pengajuan);
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
$tanggal_pengajuan = $data['tanggal_pengajuan'];
$harga_barang = intval($data['harga_barang'] ?? 0);
$sisa_jumlah = intval($data['sisa_jumlah'] ?? 0);
$status_sisa = $data['status_sisa'] ?? null;

// =============== ✅ FORWARD ===============
if ($status === 'forward') {
    if (!$nomor_surat) {
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
    $status_sisa = $sisa > 0 ? 'not done' : null;
    $keterangan = "Disetujui sejumlah " . number_format($jumlah, 0, ',', '.') . " dari total " . number_format($jumlah_asli, 0, ',', '.');

    $stmtUpdate = $conn->prepare("UPDATE pengajuan SET status = ?, nomor_surat = ?, jumlah = ?, sisa_jumlah = ?, status_sisa = ?, keterangan = ?, updated_at = NOW() WHERE kode_pengajuan = ?");
    $stmtUpdate->bind_param("ssissss", $status, $nomor_surat, $jumlah, $sisa, $status_sisa, $keterangan, $kode_pengajuan);
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

    // Tambah ke barang_masuk
    $tanggal = date('Y-m-d');
    $stmtMasuk = $conn->prepare("INSERT INTO barang_masuk (tanggal, tanggal_nota, nomor_nota, nama_barang, harga_barang, jumlah, kode_uker) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtMasuk->bind_param("ssssdis", $tanggal, $tanggal, $data['nomor_surat'], $nama_barang, $harga_barang, $jumlah_masuk, $kode_uker);
    $stmtMasuk->execute();
    $stmtMasuk->close();

    // Update atau insert ke stok_barang
    $cekStok = $conn->prepare("SELECT jumlah FROM stok_barang WHERE nama_barang = ? AND kode_uker = ?");
    $cekStok->bind_param("ss", $nama_barang, $kode_uker);
    $cekStok->execute();
    $cekStok->store_result();

    if ($cekStok->num_rows > 0) {
        $updateStok = $conn->prepare("UPDATE stok_barang SET jumlah = jumlah + ? WHERE nama_barang = ? AND kode_uker = ?");
        $updateStok->bind_param("iss", $jumlah_masuk, $nama_barang, $kode_uker);
        $updateStok->execute();
        $updateStok->close();
    } else {
        $insertStok = $conn->prepare("INSERT INTO stok_barang (nama_barang, jumlah, kode_uker) VALUES (?, ?, ?)");
        $insertStok->bind_param("sis", $nama_barang, $jumlah_masuk, $kode_uker);
        $insertStok->execute();
        $insertStok->close();
    }

    // Update status ke approved
    $stmtUpdate = $conn->prepare("UPDATE pengajuan SET status = ?, updated_at = NOW() WHERE kode_pengajuan = ?");
    $stmtUpdate->bind_param("ss", $status, $kode_pengajuan);
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
    $tanggal = date('Y-m-d');
    $stmtMasuk = $conn->prepare("INSERT INTO barang_masuk (tanggal, tanggal_nota, nomor_nota, nama_barang, harga_barang, jumlah, kode_uker) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtMasuk->bind_param("ssssdis", $tanggal, $tanggal, $data['nomor_surat'], $nama_barang, $harga_barang, $jumlah_baru, $kode_uker);
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

    $stmtUpdate = $conn->prepare("UPDATE pengajuan SET status = ?, jumlah = ?, sisa_jumlah = ?, status_sisa = ?, keterangan = ?, updated_at = NOW() WHERE kode_pengajuan = ?");
    $stmtUpdate->bind_param("siisss", $status_final, $jumlah_disetujui_akhir, $sisa_baru, $status_sisa, $keterangan, $kode_pengajuan);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    echo "Barang berhasil diselesaikan. " . ($status_sisa === 'done' ? "Semua barang sudah dipenuhi." : "Masih ada sisa barang yang belum disetujui.");
    $conn->close();
    exit;
}



// =============== ✅ REJECT / COMPLETED / LAINNYA ===============
$stmt = $conn->prepare("UPDATE pengajuan SET status = ?, updated_at = NOW() WHERE kode_pengajuan = ?");
$stmt->bind_param("ss", $status, $kode_pengajuan);

if ($stmt->execute()) {
    echo "Status berhasil diperbarui menjadi " . ucfirst($status) . ".";
} else {
    http_response_code(500);
    echo "Gagal memperbarui status.";
}

$stmt->close();
$conn->close();
