<?php
// Ambil data peminjaman dan anggota
$sql = "SELECT peminjaman.*, anggota.nm_anggota 
        FROM peminjaman 
        LEFT JOIN anggota ON peminjaman.id_anggota = anggota.id_anggota 
        ORDER BY no_peminjaman ASC";
$result = $conn->query($sql);

if (!$result) {
    die("Query Error: " . $conn->error);
}

// Proses hapus jika ada parameter hapus
if (isset($_GET['hapus'])) {
    $idHapus = $conn->real_escape_string($_GET['hapus']);
    $conn->query("DELETE FROM peminjaman WHERE id_peminjaman = '$idHapus'");
    echo "<script>window.location.href='admin.php?page=perpus_utama&panggil=peminjaman.php';</script>";
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<h2 class="text-center mb-4">Data Peminjaman</h2>
<a href="admin.php?page=perpus_utama&panggil=tambah_peminjaman.php" class="btn btn-primary mb-3">Tambah Peminjaman</a>

<table class="table table-bordered table-striped">
    <thead class="table-dark text-center">
        <tr>
            <th>No</th>
            <th>Tanggal Pinjam</th>
            <th>Tanggal Kembali</th>
            <th>ID Anggota</th>
            <th>Nama Anggota</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while ($row = $result->fetch_assoc()) : ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= date('d-m-Y', strtotime($row['tgl_pinjam'])) ?></td>
                <td><?= date('d-m-Y', strtotime($row['tgl_kembali'])) ?></td>
                <td><?= $row['id_anggota'] ?></td>
                <td><?= htmlspecialchars($row['nm_anggota']) ?></td>
                <td class="text-center">
                    <a href="admin.php?page=perpus_utama&panggil=peminjaman.php&hapus=<?= $row['id_peminjaman'] ?>" 
                       class="btn btn-danger btn-sm" 
                       onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
