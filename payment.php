<?php
$page_title = 'Pembayaran Tertunda';
require 'header.php'; 
require 'db_connect.php'; 

// 1. Ambil Order ID dari URL
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id || $order_id <= 0) {
    die('<div class="container" style="text-align:center; padding:50px;"><h2>Nomor pesanan tidak valid.</h2><a href="products.php" class="btn">Kembali Belanja</a></div>');
}

// 2. Ambil data pesanan dari database menggunakan Prepared Statement
$sql_order = "SELECT total_harga, metode_pembayaran, status FROM orders WHERE order_id = ?";
$stmt_order = $conn->prepare($sql_order);

if ($stmt_order === false) {
     die("Error prepare statement: " . $conn->error);
}

$stmt_order->bind_param("i", $order_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order = $result_order->fetch_assoc(); // <-- $order diisi di sini
$stmt_order->close();

// KRUSIAL: Cek jika order tidak ditemukan
if (!$order) {
    die('<div class="container" style="text-align:center; padding:50px;"><h2>Pesanan dengan ID #' . htmlspecialchars($order_id) . ' tidak ditemukan.</h2><a href="products.php" class="btn">Kembali Belanja</a></div>');
}

// Data tampilan (AMAN, karena $order sudah dicek)
$total_bayar = number_format($order['total_harga'], 0, ',', '.');
$metode = strtoupper($order['metode_pembayaran']);

?>

<div class="container" style="max-width:600px; margin-top:40px; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1); text-align:center;">
    
    <h2>🔔 Pesanan Berhasil Dibuat!</h2>
    <p style="color:var(--accent); font-weight:bold;">Status: <?php echo htmlspecialchars($order['status']); ?></p>

    <hr style="margin:20px 0;">

    <h3>Selesaikan Pembayaran Anda (Order #<?php echo $order_id; ?>)</h3>
    <p>Total yang harus dibayar:</p>
    <h1 style="color:var(--accent); font-size:2.5em;">Rp <?php echo $total_bayar; ?></h1>
    <p>Menggunakan Metode: <strong><?php echo $metode; ?></strong></p>

    <div style="background:#f7f7f7; padding:20px; border-radius:10px; margin:20px 0;">
        <?php if ($metode === 'QRIS'): ?>
            <h4>Instruksi QRIS</h4>
            <p>Scan kode QR di bawah ini pada aplikasi pembayaran Anda:</p>
            <img src="img/QRISS.jpg" alt="QRIS" style="width:150px; margin:10px 0; border:1px solid #ddd;">
        <?php elseif ($metode === 'VA-BCA'): ?>
            <h4>Virtual Account BCA</h4>
            <p>Transfer ke Virtual Account ini:</p>
            <h2 class="receipt-code">1234567890</h2>
            <button class="copy-btn btn" onclick="copyToClipboard('1234567890')">Salin Nomor VA</button>
        <?php elseif ($metode === 'TF-BCA'): ?>
            <h4>Transfer Bank BCA</h4>
            <p>Transfer ke Rekening BCA a/n Ravion Store:</p>
            <h2 class="receipt-code">0987654321</h2>
            <button class="copy-btn btn" onclick="copyToClipboard('0987654321')">Salin Nomor Rekening</button>
        <?php endif; ?>
        <p style="font-size:0.85em; margin-top:15px; color:var(--muted);">Status Pembayaran Anda: <strong><?php echo htmlspecialchars($order['status']); ?></strong></p>
    </div>

    <div style="margin-top:20px;">
        <a href="history_pemesanan.php?order_id=<?php echo $order_id; ?>" class="btn">Lihat history pemesanan</a>
        <a href="products.php" class="btn ghost" style="margin-left:10px;">Kembali ke Home</a>
    </div>
</div>

<?php 
// Sertakan script JS yang dibutuhkan
echo '<script src="js/ui-helpers.js"></script>'; // Anda perlu memastikan file ini ada
echo '<script src="js/checkout.js"></script>'; // Script yang memuat fungsi copyToClipboard
require 'footer.php'; 
?>