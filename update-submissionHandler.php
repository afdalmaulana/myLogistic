<?php
require 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = intval($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
$price = $_POST['price'] ?? null;
$jumlah = intval($_POST['jumlah'] ?? 0);
$PNpekerja = $_SESSION['user'] ?? '';
$namaPekerja = $_SESSION['nama_pekerja'] ?? 'BRI';

$bulkIds = [];
if (isset($_POST['ids'])) {
    $bulkIdsString = $_POST['ids'];
    $bulkIds = explode(',', $bulkIdsString);
    $bulkIds = array_filter($bulkIds, fn($val) => ctype_digit($val) && intval($val) > 0);

    if (empty($bulkIds)) {
        http_response_code(400);
        echo "Format ID bulk tidak valid.";
        exit;
    }
} else {
    if (!$id) {
        http_response_code(400);
        echo "Kode pengajuan tidak valid.";
        exit;
    }
}

$allowedStatuses = ['pending', 'forward', 'approved', 'rejected', 'delete', 'completed', 'return'];
if (!in_array($status, $allowedStatuses)) {
    http_response_code(400);
    echo "Status tidak valid.";
    exit;
}

// Jika DELETE hanya bisa single, proses langsung dan exit
if ($status === 'delete') {
    $stmt = $conn->prepare("DELETE FROM pengajuan WHERE id = ?");
    $targetIds = !empty($bulkIds) ? $bulkIds : [$id];

    foreach ($targetIds as $delId) {
        $stmt->bind_param("i", $delId);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo "Gagal menghapus pengajuan dengan ID $delId.";
            $stmt->close();
            $conn->close();
            exit;
        }
    }
    $stmt->close();
    echo "Pengajuan berhasil dihapus.";
    $conn->close();
    exit;
}

// Fungsi helper update stok barang
function updateStock($conn, $nama_barang, $jumlah_masuk, $satuan, $kode_uker)
{
    $cekStok = $conn->prepare("SELECT jumlah FROM stok_barang WHERE nama_barang = ? AND kode_uker = ?");
    $cekStok->bind_param("ss", $nama_barang, $kode_uker);
    $cekStok->execute();
    $cekStok->store_result();

    if ($cekStok->num_rows > 0) {
        $cekStok->close();
        $updateStok = $conn->prepare("UPDATE stok_barang SET jumlah = jumlah + ? WHERE nama_barang = ? AND kode_uker = ?");
        $updateStok->bind_param("iss", $jumlah_masuk, $nama_barang, $kode_uker);
        $updateStok->execute();
        $updateStok->close();
    } else {
        $cekStok->close();
        $insertStok = $conn->prepare("INSERT INTO stok_barang (nama_barang, jumlah, satuan, kode_uker) VALUES (?, ?, ?, ?)");
        $insertStok->bind_param("siss", $nama_barang, $jumlah_masuk, $satuan, $kode_uker);
        $insertStok->execute();
        $insertStok->close();
    }
}

// Fungsi untuk proses approval per satu ID
function processApproval($conn, $id)
{
    $stmtSelect = $conn->prepare("SELECT * FROM pengajuan WHERE id = ?");
    $stmtSelect->bind_param("i", $id);
    $stmtSelect->execute();
    $result = $stmtSelect->get_result();
    $data = $result->fetch_assoc();
    $stmtSelect->close();

    if (!$data) return false;

    $jumlah_masuk = intval($data['jumlah']);
    if ($jumlah_masuk <= 0) return false;

    $tanggal_pengajuan = $data['tanggal_pengajuan'];
    $tanggal_approve = date('Y-m-d');
    $tanggal_nota = null;

    $nama_barang = $data['nama_barang'];
    $price = $data['price'];
    $kode_uker = $data['kode_uker'];
    $satuan = $data['satuan'];

    // Insert barang_masuk
    $stmtMasuk = $conn->prepare("
        INSERT INTO barang_masuk (
            tanggal, tanggal_approve, tanggal_nota, price, nama_barang, jumlah, kode_uker, satuan
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtMasuk->bind_param("sssisiss", $tanggal_pengajuan, $tanggal_approve, $tanggal_nota, $price, $nama_barang, $jumlah_masuk, $kode_uker, $satuan);
    $stmtMasuk->execute();
    $stmtMasuk->close();

    // Update stok
    updateStock($conn, $nama_barang, $jumlah_masuk, $satuan, $kode_uker);

    // Update status pengajuan
    $status = 'approved';
    $stmtUpdate = $conn->prepare("UPDATE pengajuan SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmtUpdate->bind_param("si", $status, $id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    return true;
}

// ===================== Proses Bulk Approve =====================
if ($status === 'approved' && !empty($bulkIds)) {
    foreach ($bulkIds as $bulkId) {
        $idBulk = intval($bulkId);
        processApproval($conn, $idBulk);
    }

    header('Content-Type: application/json');
    echo "Pengajuan berhasil di-approve dan ditambahkan ke stok.";
    $conn->close();
    exit;
}

// ===================== Proses Bulk Reject =====================
if ($status === 'rejected' && !empty($bulkIds)) {
    foreach ($bulkIds as $bulkId) {
        $idBulk = intval($bulkId);
        $stmtUpdate = $conn->prepare("UPDATE pengajuan SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmtUpdate->bind_param("si", $status, $idBulk);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }
    header('Content-Type: application/json');
    echo "Pengajuan di reject.";
    $conn->close();
    exit;
}

// ===================== Proses Single =====================
// Ambil data pengajuan
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

// DELETE sudah di handle atas

// FORWARD
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

// APPROVED single (tanpa bulk)
if ($status === 'approved' && empty($bulkIds)) {
    if (!processApproval($conn, $id)) {
        http_response_code(400);
        echo "Gagal memproses approval.";
        $conn->close();
        exit;
    }
    echo "Pengajuan berhasil di-approve dan ditambahkan ke stok.";
    $conn->close();
    exit;
}

// COMPLETED
if ($status === 'completed') {
    $jumlah_baru = intval($_POST['jumlah_selesai'] ?? 0);
    $jumlah_sebelumnya = intval($data['jumlah']);
    $sisa_sebelumnya = intval($data['sisa_jumlah']);
    $jumlah_asli_total = $jumlah_sebelumnya + $sisa_sebelumnya;

    if ($jumlah_baru <= 0 || $jumlah_baru > $sisa_sebelumnya) {
        http_response_code(400);
        echo "Jumlah yang dimasukkan tidak valid atau melebihi sisa pengajuan.";
        exit;
    }

    $jumlah_disetujui_akhir = $jumlah_sebelumnya + $jumlah_baru;
    $sisa_baru = $jumlah_asli_total - $jumlah_disetujui_akhir;
    $status_sisa = $sisa_baru === 0 ? 'done' : 'not done';

    $tanggal_approve = date('Y-m-d');
    $tanggal_nota = null;

    // Insert barang_masuk
    $stmtMasuk = $conn->prepare("
        INSERT INTO barang_masuk (
            tanggal, tanggal_approve, tanggal_nota, price, nama_barang, jumlah, kode_uker, satuan
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtMasuk->bind_param("sssisiss", $tanggal_pengajuan, $tanggal_approve, $tanggal_nota, $data['price'], $nama_barang, $jumlah_baru, $kode_uker, $satuan);
    $stmtMasuk->execute();
    $stmtMasuk->close();

    updateStock($conn, $nama_barang, $jumlah_baru, $satuan, $kode_uker);

    $keterangan = "Disetujui sejumlah " . number_format($jumlah_disetujui_akhir, 0, ',', '.') . " dari total " . number_format($jumlah_asli_total, 0, ',', '.');
    $status_final = 'approved';

    $stmtUpdate = $conn->prepare("UPDATE pengajuan SET status = ?, jumlah = ?, sisa_jumlah = ?, status_sisa = ?, keterangan = ?, updated_at = NOW() WHERE id = ?");
    $stmtUpdate->bind_param("siissi", $status_final, $jumlah_disetujui_akhir, $sisa_baru, $status_sisa, $keterangan, $id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    echo "Barang berhasil diselesaikan. " . ($status_sisa === 'done' ? "Semua barang sudah dipenuhi." : "Masih ada sisa barang yang belum disetujui.");
    $conn->close();
    exit;
}

// REJECTED single
if ($status === 'rejected' && empty($bulkIds)) {
    $status_sisa = 'done';
    $keterangan = "Pengajuan ditolak oleh $namaPekerja.";

    $stmtUpdate = $conn->prepare("UPDATE pengajuan SET status = ?, status_sisa = ?, keterangan = ?, updated_at = NOW() WHERE id = ?");
    $stmtUpdate->bind_param("sssi", $status, $status_sisa, $keterangan, $id);

    if ($stmtUpdate->execute()) {
        echo "Pengajuan berhasil ditolak.";
    } else {
        http_response_code(500);
        echo "Gagal menolak pengajuan.";
    }

    $stmtUpdate->close();
    $conn->close();
    exit;
}

// HANDLE STATUS RETURN
if ($status === 'return') {
    $newStatus = 'pending';  // ganti status return jadi forward di DB
    $keterangan = "Pengajuan dikembalikan oleh $namaPekerja.";
    $newStatus_sisa = null;
    $newSisa_jumlah = null;

    $stmtUpdate = $conn->prepare("UPDATE pengajuan SET status = ?, keterangan = ?, status_sisa = ?, sisa_jumlah = ?, updated_at = NOW() WHERE id = ?");
    $stmtUpdate->bind_param("ssiss", $newStatus, $keterangan, $newStatus_sisa, $newSisa_jumlah, $id);

    if ($stmtUpdate->execute()) {
        echo "Pengajuan berhasil dikembalikan ke PPO.";
    } else {
        http_response_code(500);
        echo "Gagal mengembalikan status pengajuan.";
    }

    $stmtUpdate->close();
    $conn->close();
    exit;
}


// UPDATE STATUS LAINNYA (pending, dll)
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
