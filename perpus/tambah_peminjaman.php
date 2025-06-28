<?php
// Ambil data anggota
$anggota_result = $conn->query("SELECT id_anggota, nm_anggota FROM anggota ORDER BY nm_anggota ASC");

// Ambil data buku + stok tersedia
$buku_result = $conn->query("SELECT buku.id_buku, judul_buku,
    (SELECT COUNT(*) FROM copy_buku WHERE id_buku = buku.id_buku AND status_buku = 'tersedia') AS stok
FROM buku ORDER BY judul_buku ASC");

$bookData = [];
while ($b = $buku_result->fetch_assoc()) {
    $bookData[$b['id_buku']] = [
        'judul' => $b['judul_buku'],
        'stok' => (int)$b['stok']
    ];
}
?>

<form method="POST" class="container">
    <div class="mb-3">
        <label class="form-label">Tanggal Pinjam</label>
        <input type="date" name="tgl_pinjam" class="form-control form-control-sm w-auto" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Tanggal Kembali</label>
        <input type="date" name="tgl_kembali" class="form-control form-control-sm w-auto" required>
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

    <div class="mb-3 bg-light p-3 rounded">
        <table class="table table-bordered" id="tabel_buku">
            <thead>
                <tr class="table-secondary text-center">
                    <th>No</th>
                    <th>ID Buku</th>
                    <th>Judul Buku</th>
                    <th>Jumlah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td><input type="text" name="id_buku[]" class="form-control form-control-sm id-buku" readonly></td>
                    <td>
                        <select class="form-select form-select-sm judul-buku" required>
                            <option value="">PILIH</option>
                            <?php foreach ($bookData as $id => $data): ?>
                                <option value="<?= $id ?>"><?= $data['judul'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" name="jumlah[]" class="form-control form-control-sm jumlah-buku" required></td>
                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus">-</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" id="btn-tambah" class="btn btn-success btn-sm">Tambah</button>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="admin.php?page=perpus_utama&panggil=peminjaman.php" class="btn btn-secondary">Batal</a>
</form>

<script>
const bookData = <?= json_encode($bookData) ?>;

const tableBody = document.querySelector("#tabel_buku tbody");
const btnTambah = document.getElementById("btn-tambah");

// Simpan semua ID buku yang sedang dipakai
function getSelectedBookIds() {
    return [...document.querySelectorAll(".judul-buku")].map(sel => sel.value).filter(val => val);
}

function updateDropdownOptions() {
    const selectedIds = getSelectedBookIds();

    document.querySelectorAll(".judul-buku").forEach(select => {
        const current = select.value;
        select.innerHTML = `<option value="">PILIH</option>` + Object.entries(bookData)
            .filter(([id]) => id === current || !selectedIds.includes(id))
            .map(([id, data]) => `<option value="${id}">${data.judul}</option>`)
            .join('');
        select.value = current;
    });
}

// Tambah baris baru
btnTambah.addEventListener("click", () => {
    const rowCount = tableBody.rows.length + 1;
    const row = tableBody.insertRow();
    row.innerHTML = `
        <td class="text-center">${rowCount}</td>
        <td><input type="text" name="id_buku[]" class="form-control form-control-sm id-buku" readonly></td>
        <td>
            <select class="form-select form-select-sm judul-buku" required>
                <option value="">PILIH</option>
                ${Object.entries(bookData)
                    .filter(([id]) => !getSelectedBookIds().includes(id))
                    .map(([id, data]) => `<option value="${id}">${data.judul}</option>`).join('')}
            </select>
        </td>
        <td><input type="number" name="jumlah[]" class="form-control form-control-sm jumlah-buku" required></td>
        <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-hapus">-</button></td>
    `;
    updateDropdownOptions();
});

// Sinkronkan judul -> ID dan jumlah max
tableBody.addEventListener("change", (e) => {
    if (e.target.classList.contains("judul-buku")) {
        const select = e.target;
        const id = select.value;
        const row = select.closest("tr");
        const idInput = row.querySelector(".id-buku");
        const jumlahInput = row.querySelector(".jumlah-buku");

        idInput.value = id;
        jumlahInput.max = bookData[id]?.stok || 1;
        jumlahInput.placeholder = "max: " + (bookData[id]?.stok || 1);

        updateDropdownOptions();
    }
});

// Hapus baris
tableBody.addEventListener("click", (e) => {
    if (e.target.classList.contains("btn-hapus")) {
        e.target.closest("tr").remove();
        updateNomor();
        updateDropdownOptions();
    }
});

// Update nomor urut
function updateNomor() {
    [...tableBody.rows].forEach((row, i) => {
        row.cells[0].textContent = i + 1;
    });
}
</script>
