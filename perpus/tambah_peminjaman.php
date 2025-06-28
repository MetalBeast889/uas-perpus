<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tgl_pinjam = $_POST['tgl_pinjam'];
    $tgl_kembali = $_POST['tgl_kembali'];
    $id_anggota = $_POST['id_anggota'];

    // Simpan ke tabel peminjaman
    $query = "INSERT INTO peminjaman (tgl_pinjam, tgl_kembali, id_anggota) 
              VALUES ('$tgl_pinjam', '$tgl_kembali', '$id_anggota')";
    if ($conn->query($query)) {
        $id_peminjaman = $conn->insert_id;

        // Simpan detail buku
        foreach ($_POST['id_buku'] as $index => $id_buku) {
            $jumlah = $_POST['jumlah'][$index];
            $conn->query("INSERT INTO detail_peminjaman (id_peminjaman, id_buku, jumlah)
                          VALUES ('$id_peminjaman', '$id_buku', '$jumlah')");
        }

        echo "<script>alert('Peminjaman berhasil disimpan');</script>";
        echo "<script>window.location.href='admin.php?page=perpus_utama&panggil=peminjaman.php';</script>";
    } else {
        echo "Gagal menyimpan: " . $conn->error;
    }
}

$anggota_result = $conn->query("SELECT id_anggota, nm_anggota FROM anggota ORDER BY nm_anggota ASC");
$buku_result = $conn->query("SELECT id_buku, judul_buku FROM buku ORDER BY judul_buku ASC");

// Buat array buku untuk JS
$bookData = [];
while ($b = $buku_result->fetch_assoc()) {
    $bookData[$b['id_buku']] = $b['judul_buku'];
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<h2 class="text-center mb-4">Tambah Data Peminjaman</h2>

<form method="POST" class="container">
    <div class="mb-3">
        <label class="form-label">Tanggal Pinjam</label>
        <input type="date" name="tgl_pinjam" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Tanggal Kembali</label>
        <input type="date" name="tgl_kembali" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Nama Anggota</label>
        <select name="id_anggota" class="form-select" required>
            <option value="">-- Pilih Anggota --</option>
            <?php while ($a = $anggota_result->fetch_assoc()) : ?>
                <option value="<?= $a['id_anggota'] ?>"><?= htmlspecialchars($a['nm_anggota']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <!-- Bagian Input Buku -->
    <div class="mb-3 bg-light p-3 rounded">
        <table class="table table-bordered" id="tabel_buku">
            <thead>
                <tr class="table-secondary text-center">
                    <th>No</th>
                    <th>ID Buku</th>
                    <th>Judul Buku</th>
                    <th>Jumlah</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- baris pertama -->
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        <select name="id_buku[]" class="form-select id-buku" required>
                            <option value="">PILIH</option>
                            <?php foreach ($bookData as $id => $judul) : ?>
                                <option value="<?= $id ?>"><?= $id ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="text" class="form-control judul-buku" readonly></td>
                    <td><input type="number" name="jumlah[]" class="form-control" required></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm btn-hapus">-</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <button type="button" id="btn-tambah" class="btn btn-success">Tambah</button>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="admin.php?page=perpus_utama&panggil=peminjaman.php" class="btn btn-secondary">Batal</a>
</form>

<script>
const bookData = <?= json_encode($bookData) ?>;
const tableBody = document.querySelector("#tabel_buku tbody");
const btnTambah = document.getElementById("btn-tambah");

// Tambah baris baru
btnTambah.addEventListener("click", () => {
    const rowCount = tableBody.rows.length + 1;
    const row = tableBody.insertRow();
    row.innerHTML = `
        <td class="text-center">${rowCount}</td>
        <td>
            <select name="id_buku[]" class="form-select id-buku" required>
                <option value="">PILIH</option>
                ${Object.entries(bookData).map(([id, judul]) => `<option value="${id}">${id}</option>`).join("")}
            </select>
        </td>
        <td><input type="text" class="form-control judul-buku" readonly></td>
        <td><input type="number" name="jumlah[]" class="form-control" required></td>
        <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus">-</button></td>
    `;
});

// Hapus baris
tableBody.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn-hapus")) {
        const row = e.target.closest("tr");
        row.remove();
        updateNomor();
    }
});

// Update nomor urut
function updateNomor() {
    [...tableBody.rows].forEach((row, i) => {
        row.cells[0].textContent = i + 1;
    });
}

// Auto isi judul buku
tableBody.addEventListener("change", (e) => {
    if (e.target.classList.contains("id-buku")) {
        const id = e.target.value;
        const judulInput = e.target.closest("tr").querySelector(".judul-buku");
        judulInput.value = bookData[id] || '';
    }
});
</script>