<?php
ob_start();

// generate custom id buku 
function generateIdBuku($conn, $id_kategori) {
    $result = $conn->query("SELECT id_buku FROM buku WHERE id_buku LIKE '{$id_kategori}-B%' ORDER BY CAST(SUBSTRING_INDEX(id_buku, 'B', -1) AS UNSIGNED) DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $bagian = explode('-B', $row['id_buku']);
        $lastNumber = isset($bagian[1]) ? (int)$bagian[1] + 1 : 1;
    } else {
        $lastNumber = 1;
    }
    return "{$id_kategori}-B{$lastNumber}";
}

// generate id ajax
if (isset($_GET['generate_id']) && isset($_GET['id_kategori'])) {
    $id_kategori = $_GET['id_kategori'];
    echo generateIdBuku($conn, $id_kategori);
    exit;
}

//ambil kategori
$kategori = $conn->query("SELECT * FROM kategori");

// Ambil data jika edit
$isEdit = false;
if (isset($_GET['edit'])) {
    $idEdit = $conn->real_escape_string($_GET['edit']);
    $resultEdit = $conn->query("SELECT * FROM buku WHERE id_buku = '$idEdit'");
    if ($resultEdit && $resultEdit->num_rows > 0) {
        $editData = $resultEdit->fetch_assoc();
        $isEdit = true;
    }
}


// ID buku default
$id_buku_otomatis = '';
if ($isEdit) {
    $id_buku_otomatis = $editData['id_buku'];
}

// proses simpan
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $judul       = $_POST['judul_buku'];
    $pengarang   = $_POST['pengarang'];
    $tahun       = $_POST['thn_terbit'];
    $jumlah      = (int) $_POST['jml_buku'];
    $penerbit    = $_POST['penerbit'];
    $id_kategori = $_POST['id_kategori'];
    $id_buku     = $_POST['id_buku'];

    // Cek apakah buku sudah ada (edit)
    $cek = $conn->query("SELECT * FROM buku WHERE id_buku = '$id_buku'");
    if ($cek && $cek->num_rows > 0) {
        //Update Data Buku
        $stmt = $conn->prepare("UPDATE buku SET judul_buku=?, pengarang=?, thn_terbit=?, jml_buku=?, penerbit=? WHERE id_buku=?");
        $stmt->bind_param("ssssss", $judul, $pengarang, $tahun, $jumlah, $penerbit, $id_buku);

        if ($stmt->execute()) {
            // Update Copy Buku, Hitung jumlah copy buku sekarang
            $countCopy = $conn->query("SELECT COUNT(*) as total FROM copy_buku WHERE id_buku = '$id_buku'")->fetch_assoc()['total'];

            if ($jumlah > $countCopy) {
                // Tambah copy
                for ($i = $countCopy + 1; $i <= $jumlah; $i++) {
                    $no_copy = $id_buku . '-C' . $i;
                    $conn->query("INSERT INTO copy_buku (no_copy_buku, status_buku, id_buku) VALUES ('$no_copy', 'tersedia', '$id_buku')");
                }
            } elseif ($jumlah < $countCopy) {
                // Hapus copy dari belakang
                for ($i = $countCopy; $i > $jumlah; $i--) {
                    $no_copy = $id_buku . '-C' . $i;
                    $conn->query("DELETE FROM copy_buku WHERE no_copy_buku = '$no_copy'");
                }
            }

            echo "<div class='alert alert-success'>Data buku berhasil diperbarui.</div>";
            echo '<meta http-equiv="refresh" content="1;url=?page=perpus_utama&panggil=buku.php">';
        } else {
            echo "<div class='alert alert-danger'>Gagal memperbarui data: " . htmlspecialchars($stmt->error) . "</div>";
        }
    } else {
        //tambah buku baru
        $id_buku = generateIdBuku($conn, $id_kategori);
        $stmt = $conn->prepare("INSERT INTO buku VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $id_buku, $judul, $pengarang, $tahun, $jumlah, $penerbit, $id_kategori);

        if ($stmt->execute()) {
            // Tambah copy buku
            for ($i = 1; $i <= $jumlah; $i++) {
                $no_copy = $id_buku . '-C' . $i;
                $conn->query("INSERT INTO copy_buku (no_copy_buku, status_buku, id_buku) VALUES ('$no_copy', 'tersedia', '$id_buku')");
            }

            echo "<div class='alert alert-success'>Data buku berhasil disimpan.</div>";
            echo '<meta http-equiv="refresh" content="1;url=?page=perpus_utama&panggil=buku.php">';
        } else {
            echo "<div class='alert alert-danger'>Gagal menyimpan data: " . htmlspecialchars($stmt->error) . "</div>";
        }
    }
}

?>

<div class="container mt-4">
    <h3 class="mb-4 text-center"><?= $isEdit ? 'Edit' : 'Tambah' ?> Buku</h3>
    <form method="post" class="border p-4 rounded bg-light">
        <div class="mb-3">
            <label>ID Buku:</label>
            <input type="text" name="id_buku" class="form-control" 
                value="<?= htmlspecialchars($id_buku_otomatis) ?>" 
                readonly required>
        </div>
        <div class="mb-3">
            <label>Judul Buku:</label>
            <input type="text" name="judul_buku" class="form-control" required 
                value="<?= isset($editData['judul_buku']) ? htmlspecialchars($editData['judul_buku']) : '' ?>">
        </div>
        <div class="mb-3">
            <label>Pengarang:</label>
            <input type="text" name="pengarang" class="form-control" 
                value="<?= isset($editData['pengarang']) ? htmlspecialchars($editData['pengarang']) : '' ?>">
        </div>
        <div class="mb-3">
            <label>Tahun Terbit:</label>
            <input type="number" name="thn_terbit" class="form-control" 
                value="<?= isset($editData['thn_terbit']) ? htmlspecialchars($editData['thn_terbit']) : '' ?>">
        </div>
        <div class="mb-3">
            <label>Jumlah Buku:</label>
            <input type="number" name="jml_buku" class="form-control" min="1" required 
                value="<?= isset($editData['jml_buku']) ? htmlspecialchars($editData['jml_buku']) : '' ?>">
        </div>
        <div class="mb-3">
            <label>Penerbit:</label>
            <input type="text" name="penerbit" class="form-control" 
                value="<?= isset($editData['penerbit']) ? htmlspecialchars($editData['penerbit']) : '' ?>">
        </div>
        <div class="mb-3">
            <label>Kategori:</label>
            <select name="id_kategori" class="form-select" required <?= $isEdit ? 'disabled' : 'onchange="generateId()"' ?>>
                <option value="">-- Pilih Kategori --</option>
                <?php
                $kategoriResult = $conn->query("SELECT * FROM kategori");
                while ($row = $kategoriResult->fetch_assoc()) : ?>
                    <option value="<?= $row['id_kategori'] ?>" 
                        <?= (isset($editData['id_kategori']) && $editData['id_kategori'] == $row['id_kategori']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['nm_kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <?php if ($isEdit): ?>
                <input type="hidden" name="id_kategori" value="<?= htmlspecialchars($editData['id_kategori']) ?>">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Simpan' ?></button>
        <a href="?page=perpus_utama&panggil=buku.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
function generateId() {
    const idKategori = document.querySelector('[name="id_kategori"]').value;
    if (idKategori) {
        fetch('?generate_id=1&id_kategori=' + idKategori)
            .then(res => res.text())
            .then(data => {
                document.querySelector('[name="id_buku"]').value = data;
            });
    } else {
        document.querySelector('[name="id_buku"]').value = '';
    }
}
</script>

<?php ob_end_flush(); ?>
