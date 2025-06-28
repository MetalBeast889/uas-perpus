<?php
// Fungsi generate ID kategori berdasarkan nama (dengan pengecekan ke DB)
function generateIdKategoriFromName($conn, $namaKategori) {
    $namaKategori = strtoupper(preg_replace('/[^A-Z]/', '', $namaKategori)); // Hanya huruf kapital

    $panjang = 3;
    $idCalon = substr($namaKategori, 0, $panjang);

    // Tambahkan karakter jika id sudah ada
    while (true) {
        $cek = $conn->query("SELECT COUNT(*) as total FROM kategori WHERE id_kategori = '$idCalon'");
        $row = $cek->fetch_assoc();

        if ($row['total'] == 0) {
            break;
        }

        $panjang++;
        $idCalon = substr($namaKategori, 0, $panjang);
        if ($panjang > strlen($namaKategori)) {
            // Jika sudah habis huruf, tambahkan angka
            $i = 1;
            do {
                $idCalon = substr($namaKategori, 0, 3) . $i;
                $cek = $conn->query("SELECT COUNT(*) as total FROM kategori WHERE id_kategori = '$idCalon'");
                $row = $cek->fetch_assoc();
                $i++;
            } while ($row['total'] > 0);
            break;
        }
    }

    return $idCalon;
}

// Ambil data edit jika ada
$editData = null;
if (isset($_GET['edit'])) {
    $idEdit = $conn->real_escape_string($_GET['edit']);
    $resultEdit = $conn->query("SELECT * FROM kategori WHERE id_kategori = '$idEdit'");
    if ($resultEdit && $resultEdit->num_rows > 0) {
        $editData = $resultEdit->fetch_assoc();
    }
}

// Handle simpan data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nmKategori = strtoupper($conn->real_escape_string($_POST['nmKategori']));
    $idKategori = $editData ? $conn->real_escape_string($_POST['idKategori']) : generateIdKategoriFromName($conn, $nmKategori);

    if (empty($idKategori)) {
        echo '<div class="alert alert-danger">ID Kategori harus diisi.</div>';
    } else {
        $cek = $conn->query("SELECT * FROM kategori WHERE id_kategori = '$idKategori'");
        if ($cek->num_rows > 0 && !$editData) {
            echo '<div class="alert alert-danger">ID kategori sudah ada.</div>';
        } else {
            if ($editData) {
                $sql = "UPDATE kategori SET nm_kategori = '$nmKategori' WHERE id_kategori = '$idKategori'";
            } else {
                $sql = "INSERT INTO kategori (id_kategori, nm_kategori) VALUES ('$idKategori', '$nmKategori')";
            }

            if ($conn->query($sql)) {
                echo '<div class="alert alert-success">Data berhasil disimpan.</div>';
                echo '<meta http-equiv="refresh" content="1;url=?page=perpus_utama&panggil=kategori.php">';
                exit;
            } else {
                echo '<div class="alert alert-danger">Gagal menyimpan data: ' . $conn->error . '</div>';
            }
        }
    }
}

// Jika bukan edit, isi ID kosong (digenerate saat simpan atau realtime)
$idKategoriOtomatis = $editData ? $editData['id_kategori'] : '';
?>

<h2 class="text-center"><?= isset($editData) ? "Edit" : "Tambah" ?> Kategori</h2>

<form method="POST" class="mb-4" oninput="generateIdKategoriRealtime()">
    <div class="mb-3">
        <label for="nmKategori" class="form-label">Nama Kategori (max 20 karakter)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-book"></i></span>
            <input type="text" maxlength="20" class="form-control" id="nmKategori" name="nmKategori" required
                style="text-transform: uppercase;"
                value="<?= isset($editData['nm_kategori']) ? htmlspecialchars($editData['nm_kategori']) : '' ?>">
        </div>
    </div>
    <div class="mb-3">
        <label for="idKategori" class="form-label">ID Kategori (otomatis dari nama)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-tag"></i></span>
            <input type="text" maxlength="6" class="form-control" id="idKategori" name="idKategori" readonly
                value="<?= htmlspecialchars($idKategoriOtomatis) ?>">
        </div>
    </div>
    <button type="submit" class="btn btn-primary">
        <?= isset($editData) ? 'Update' : 'Simpan' ?>
    </button>
    <a href="?page=perpus_utama&panggil=kategori.php" class="btn btn-secondary">Batal</a>
</form>

<script>
    function generateIdKategoriRealtime() {
    const nama = document.getElementById('nmKategori').value.toUpperCase().replace(/[^A-Z]/g, '');
    document.getElementById('idKategori').value = nama.substring(0, 3);
}
</script>
