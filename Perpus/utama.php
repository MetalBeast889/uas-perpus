<?php
/*
  Plugin Name: SI Perpustakaan
  Description: Plugin CRUD kelompok UAS 6 Ksatria.
  Author: Pur, Per, Por, Par, Pir, dan Bor
  Version: 1.0.0
*/

function modulku() {
    global $conn;
    // Buat koneksi database sekali, pakai konstanta WP untuk host dan user database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><img width="25%" src="<?= plugin_dir_url(__FILE__) ?>/self-pic.jpg"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="collapsibleNavbar">
                <ul class="navbar-nav">
                    <li class="nav-item">
                      <a class="nav-link" href="admin.php?page=utama&panggil=kategori.php">Entry Data Kategori</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="admin.php?page=utama&panggil=buku.php">Entry Data Buku</a>
                    </li>
                    <!-- Tambah menu lain di sini jika perlu -->
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-3">
        <?php
        if (isset($_GET["panggil"])) {
            $file = sanitize_text_field($_GET["panggil"]);
            $path = plugin_dir_path(__FILE__) . $file;
            if (file_exists($path)) {
                include($path);
            } else {
                echo '<div class="alert alert-danger">File tidak ditemukan: ' . htmlspecialchars($file) . '</div>';
            }
        } else {
            echo '<h3>Selamat datang di Plugin SI Perpustakaan</h3>';
        }
        ?>
    </div>
    <?php
}

function tambah_menu_perpustakaan() {
    add_menu_page(
        'SI Perpustakaan 6 Ksatria', // Page title
        'SI Perpustakaan',           // Menu title
        'read',                     // Capability
        'utama',                    // Menu slug
        'modulku',                  // Callback function
        'dashicons-share-alt',      // Icon
        25                         // Position
    );
}

add_action('admin_menu', 'tambah_menu_perpustakaan');
?>
