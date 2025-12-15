<?php
// FILE: delete_product.php (MODE DEBUGGING)
require 'db_connect.php';

// Aktifkan laporan error PHP biar muncul di layar
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>--- MODE DEBUGGING DELETE ---</h3>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Cek apakah ID terkirim dari View Product
    $id = isset($_POST['id_product']) ? intval($_POST['id_product']) : 0;
    echo "ID yang diterima: <b>" . $id . "</b><br>";

    if ($id <= 0) {
        die("<h2 style='color:red'>GAGAL: ID Produk tidak terbaca (0).</h2> <p>Cek JavaScript di view_product.php apakah berhasil memasukkan ID ke input hidden.</p>");
    }

    // 2. Cek apakah produk ada di database
    $check = $conn->query("SELECT * FROM products WHERE id_product = $id");
    if ($check->num_rows == 0) {
        die("<h2 style='color:red'>GAGAL: Produk ID $id tidak ditemukan di database.</h2>");
    } else {
        $data = $check->fetch_assoc();
        echo "Produk ditemukan: <b>" . $data['name'] . "</b><br>";
    }

    // 3. COBA HAPUS (Eksekusi Query)
    echo "Mencoba menghapus...<br>";
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id_product = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "<h2 style='color:green'>SUKSES! Data berhasil dihapus.</h2>";
        echo "<a href='view_product.php'>Kembali ke List Produk</a>";
    } else {
        // TANGKAP PESAN ERROR DARI MYSQL
        $error_msg = $conn->error; // atau $stmt->error
        
        echo "<h2 style='color:red'>GAGAL MENGHAPUS!</h2>";
        echo "<b>Pesan Error MySQL:</b> <br><span style='background:#eee; padding:5px; border:1px solid #ccc; display:block; margin-top:5px; color:red;'>$error_msg</span>";
        
        // Analisa Error Umum
        if (strpos($error_msg, 'foreign key') !== false) {
            echo "<br><br><b>PENYEBAB:</b> Produk ini tidak bisa dihapus karena <b>sudah pernah dibeli/dipesan</b>. Database melindunginya agar riwayat pesanan (Ordering) tidak rusak.<br>";
            echo "<b>SOLUSI:</b> Hapus dulu data pesanan di tabel <i>order_items</i> yang memuat produk ini, baru produk bisa dihapus.";
        }
    }
    $stmt->close();

} else {
    echo "Akses ditolak. Harap submit dari tombol Delete.";
}
?>