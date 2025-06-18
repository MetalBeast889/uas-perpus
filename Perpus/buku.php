<?php 
global $conn;

$sql = "SELECT buku.*, kategori.nm_kategori 
        FROM buku 
        LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori";

$result = $conn->query($sql);

if (!$result) {
    echo "<div class='alert alert-danger'>Query error: " . $conn->error . "</div>";
    return;
}
?>

<!-- Load Bootstrap CSS dari CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">
    <h2 class="mb-4">Daftar Buku</h2>
    <a href="admin.php?page=utama&panggil=tambah-buku.php" class="btn btn-primary mb-3">
        <i class="fa fa-plus"></i> Tambah Buku
    </a>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Pengarang</th>
                    <th>Tahun</th>
                    <th>Jumlah</th>
                    <th>Penerbit</th>
                    <th>Kategori</th>
                    <th style="width:120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?= htmlspecialchars($row['id_buku']) ?></td>
                    <td><?= htmlspecialchars($row['judul_buku']) ?></td>
                    <td><?= htmlspecialchars($row['pengarang']) ?></td>
                    <td><?= htmlspecialchars($row['thn_terbit']) ?></td>
                    <td><?= htmlspecialchars($row['jml_buku']) ?></td>
                    <td><?= htmlspecialchars($row['penerbit']) ?></td>
                    <td><?= htmlspecialchars($row['nm_kategori']) ?></td>
                    <td>
                        <a href="admin.php?page=utama&panggil=edit-buku.php&id=<?= urlencode($row['id_buku']) ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a href="admin.php?page=utama&panggil=hapus-buku.php&id=<?= urlencode($row['id_buku']) ?>" 
                           class="btn btn-sm btn-danger" 
                           title="Hapus" 
                           onclick="return confirm('Yakin ingin menghapus buku ini?');">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($result->num_rows == 0): ?>
                <tr>
                    <td colspan="8" class="text-center">Data buku kosong.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Load Font Awesome untuk icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
