<?php
global $conn;

// Ambil data kategori
$kategori = $conn->query("SELECT * FROM kategori");

// Generate ID Buku otomatis
function generateIdBuku($conn) {
    $result = $conn->query("SELECT id_buku FROM buku WHERE id_buku LIKE 'B%' ORDER BY CAST(SUBSTRING(id_buku, 2) AS UNSIGNED) DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $num = (int) substr($row['id_buku'], 1);
        $num++;
    } else {
        $num = 1;
    }
    return "B" . $num;
}

$pesan = "";
$editData = null;
$id_buku_otomatis = generateIdBuku($conn);

// Cek apakah mode edit
if (isset($_GET['edit'])) {
    $idEdit = $conn->real_escape_string($_GET['edit']);
    $resultEdit = $conn->query("SELECT * FROM buku WHERE id_buku = '$idEdit'");
    if ($resultEdit && $resultEdit->num_rows > 0) {
        $editData = $resultEdit->fetch_assoc();
        $id_buku_otomatis = $editData['id_buku']; // gunakan ID asli untuk update
    }
}

// Simpan data
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $judul       = $_POST['judul_buku'];
    $pengarang   = $_POST['pengarang'];
    $tahun       = $_POST['thn_terbit'];
    $jumlah      = $_POST['jml_buku'];
    $penerbit    = $_POST['penerbit'];
    $id_kategori = $_POST['id_kategori'];

    if (isset($_POST['id_buku_asli'])) {
        // Proses edit
        $id_buku = $_POST['id_buku_asli'];
        $stmt = $conn->prepare("UPDATE buku SET judul_buku=?, pengarang=?, thn_terbit=?, jml_buku=?, penerbit=?, id_kategori=? WHERE id_buku=?");
        $stmt->bind_param("sssssss", $judul, $pengarang, $tahun, $jumlah, $penerbit, $id_kategori, $id_buku);
    } else {
        // Proses tambah
        $id_buku = $id_buku_otomatis;
        $stmt = $conn->prepare("INSERT INTO buku VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $id_buku, $judul, $pengarang, $tahun, $jumlah, $penerbit, $id_kategori);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Data berhasil disimpan.'); window.location.href='admin.php?page=utama&panggil=buku.php';</script>";
        exit;
    } else {
        $pesan = "<div class='alert alert-danger'>Gagal menyimpan data: " . $stmt->error . "</div>";
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container mt-4">
    <h3 class="mb-4"><?= $editData ? "Edit Buku" : "Tambah Buku" ?></h3>
    <?= $pesan ?>
    <form method="post" class="border p-4 rounded bg-light">
        <div class="mb-3">
            <label>ID Buku:</label>
            <input type="text" name="id_buku" class="form-control" value="<?= $id_buku_otomatis ?>" readonly>
        </div>
        <div class="mb-3">
            <label>Judul Buku:</label>
            <input type="text" name="judul_buku" class="form-control" value="<?= $editData ? htmlspecialchars($editData['judul_buku']) : '' ?>" required>
        </div>
        <div class="mb-3">
            <label>Pengarang:</label>
            <input type="text" name="pengarang" class="form-control" value="<?= $editData ? htmlspecialchars($editData['pengarang']) : '' ?>">
        </div>
        <div class="mb-3">
            <label>Tahun Terbit:</label>
            <input type="number" name="thn_terbit" class="form-control" value="<?= $editData ? $editData['thn_terbit'] : '' ?>">
        </div>
        <div class="mb-3">
            <label>Jumlah Buku:</label>
            <input type="text" name="jml_buku" class="form-control" value="<?= $editData ? $editData['jml_buku'] : '' ?>">
        </div>
        <div class="mb-3">
            <label>Penerbit:</label>
            <input type="text" name="penerbit" class="form-control" value="<?= $editData ? htmlspecialchars($editData['penerbit']) : '' ?>">
        </div>
        <div class="mb-3">
            <label>Kategori:</label>
            <select name="id_kategori" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                <?php while ($row = $kategori->fetch_assoc()) : ?>
                    <option value="<?= $row['id_kategori'] ?>" <?= ($editData && $editData['id_kategori'] == $row['id_kategori']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['nm_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <?php if ($editData): ?>
            <input type="hidden" name="id_buku_asli" value="<?= $editData['id_buku'] ?>">
        <?php endif; ?>

        <button type="submit" class="btn btn-success"><?= $editData ? "Update" : "Simpan" ?></button>
        <a href="admin.php?page=utama&panggil=buku.php" class="btn btn-secondary">Kembali</a>
    </form>
</div>
