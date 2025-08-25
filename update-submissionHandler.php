<?php
require 'db_connect.php';

$kode_pengajuan = $_POST['kode_pengajuan'] ?? '';
$status = $_POST['status'] ?? '';
$nomor_surat = $_POST['nomor_surat'] ?? null;
$jumlah = intval($_POST['jumlah'] ?? 0); // jumlah yang akan diforward

// Validasi awal
if (!$kode_pengajuan) {
    http_response_code(400);
    echo "Kode pengajuan tidak valid.";
    exit;
}

$allowedStatuses = ['pending', 'forward', 'approved', 'rejected', 'delete'];
if (!in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo "Status tidak valid.";
    exit;
}

// Proses hapus
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

// FORWARD CASE
if ($status === 'forward') {
    if (!$nomor_surat) {
        http_response_code(400);
        echo "Nomor surat wajib diisi untuk status forward.";
        exit;
    }

    if (!$jumlah || $jumlah <= 0) {
        http_response_code(400);
        echo "Jumlah forward tidak valid.";
        exit;
    }

    // Ambil data pengajuan
    $stmtSelect = $conn->prepare("SELECT * FROM pengajuan WHERE kode_pengajuan = ?");
    $stmtSelect->bind_param("s", $kode_pengajuan);
    $stmtSelect->execute();
    $result = $stmtSelect->get_result();
    $data = $result->fetch_assoc();
    $stmtSelect->close();

    if (!$data) {
        http_response_code(404);
        echo "Pengajuan tidak ditemukan.";
        exit;
    }

    $jumlah_asli = intval($data['jumlah']);
    $kode_uker = $data['kode_uker'];
    $nama_barang = $data['nama_barang']; // Dianggap sebagai nama_barang
    $tanggal_pengajuan = $data['tanggal_pengajuan'];
    $harga_barang = isset($data['harga_barang']) ? intval($data['harga_barang']) : 0;

    // Validasi jumlah
    if ($jumlah > $jumlah_asli) {
        http_response_code(400);
        echo "Jumlah forward melebihi pengajuan awal.";
        exit;
    }

    $sisa = max(0, $jumlah_asli - $jumlah);
    $status_sisa = $sisa > 0 ? 'pending' : null;
    $keterangan = "Disetujui sejumlah " . number_format($jumlah, 0, ',', '.') . " dari total " . number_format($jumlah_asli, 0, ',', '.');

    // Update pengajuan
    $stmtUpdate = $conn->prepare("UPDATE pengajuan SET status = ?, nomor_surat = ?, jumlah = ?, sisa_jumlah = ?, status_sisa = ?, keterangan = ?, updated_at = NOW() WHERE kode_pengajuan = ?");
    $stmtUpdate->bind_param("ssissss", $status, $nomor_surat, $jumlah, $sisa, $status_sisa, $keterangan, $kode_pengajuan);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // ===== ✅ Tambah ke barang_masuk =====
    $tanggal = date('Y-m-d');
    $stmtMasuk = $conn->prepare("INSERT INTO barang_masuk (tanggal, tanggal_nota, nomor_nota, nama_barang, harga_barang, jumlah, kode_uker) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtMasuk->bind_param("ssssdis", $tanggal, $tanggal, $nomor_surat, $nama_barang, $harga_barang, $jumlah, $kode_uker);
    $stmtMasuk->execute();
    $stmtMasuk->close();

    // ===== ✅ Update stok_barang =====
    $cekStok = $conn->prepare("SELECT jumlah FROM stok_barang WHERE nama_barang = ? AND kode_uker = ?");
    $cekStok->bind_param("ss", $nama_barang, $kode_uker);
    $cekStok->execute();
    $cekStok->store_result();

    if ($cekStok->num_rows > 0) {
        // Update stok jika barang sudah ada
        $updateStok = $conn->prepare("UPDATE stok_barang SET jumlah = jumlah + ? WHERE nama_barang = ? AND kode_uker = ?");
        $updateStok->bind_param("iss", $jumlah, $nama_barang, $kode_uker);
        $updateStok->execute();
        $updateStok->close();
    } else {
        // Insert stok baru jika barang belum ada
        $insertStok = $conn->prepare("INSERT INTO stok_barang (nama_barang, jumlah, kode_uker) VALUES (?, ?, ?)");
        $insertStok->bind_param("sis", $nama_barang, $jumlah, $kode_uker);
        $insertStok->execute();
        $insertStok->close();
    }

    echo "Pengajuan berhasil diforward dan ditambahkan ke stok barang.";
    $conn->close();
    exit;
}


// Selain forward: hanya update status
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
