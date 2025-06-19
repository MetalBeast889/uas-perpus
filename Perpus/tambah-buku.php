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

$id_buku_otomatis = generateIdBuku($conn);

// Untuk menampilkan pesan
$pesan = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id_buku     = $id_buku_otomatis;
    $judul       = $_POST['judul_buku'];
    $pengarang   = $_POST['pengarang'];
    $tahun       = $_POST['thn_terbit'];
    $jumlah      = $_POST['jml_buku'];
    $penerbit    = $_POST['penerbit'];
    $id_kategori = $_POST['id_kategori'];

    $stmt = $conn->prepare("INSERT INTO buku VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $id_buku, $judul, $pengarang, $tahun, $jumlah, $penerbit, $id_kategori);

    if ($stmt->execute()) {
        $pesan = "<div class='alert alert-success'>Buku berhasil ditambahkan!</div>";
        $id_buku_otomatis = generateIdBuku($conn); // Refresh ID agar siap tambah buku baru
    } else {
        $pesan = "<div class='alert alert-danger'>Gagal menambahkan buku: " . htmlspecialchars($stmt->error) . "</div>";
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container mt-4">
    <h3 class="mb-4">Tambah Buku</h3>
    <?= $pesan ?>
    <form method="post" class="border p-4 rounded bg-light">
        <div class="mb-3">
            <label>ID Buku:</label>
            <input type="text" name="id_buku" class="form-control" value="<?= $id_buku_otomatis ?>" readonly>
        </div>
        <div class="mb-3">
            <label>Judul Buku:</label>
            <input type="text" name="judul_buku" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Pengarang:</label>
            <input type="text" name="pengarang" class="form-control">
        </div>
        <div class="mb-3">
            <label>Tahun Terbit:</label>
            <input type="number" name="thn_terbit" class="form-control">
        </div>
        <div class="mb-3">
            <label>Jumlah Buku:</label>
            <input type="text" name="jml_buku" class="form-control">
        </div>
        <div class="mb-3">
            <label>Penerbit:</label>
            <input type="text" name="penerbit" class="form-control">
        </div>
        <div class="mb-3">
            <label>Kategori:</label>
            <select name="id_kategori" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                <?php while ($row = $kategori->fetch_assoc()) : ?>
                    <option value="<?= $row['id_kategori'] ?>"><?= htmlspecialchars($row['nm_kategori']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="admin.php?page=utama&panggil=buku.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
