<?php

$page_title = 'Struk Pembayaran';
require 'header.php'; 
require 'db_connect.php'; 

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);


if (!$order_id || $order_id <= 0) {
    die("Error: Nomor pesanan tidak valid.");
}

$order = null;
$items = [];

$sql_order = "SELECT * FROM orders WHERE order_id = ?";
$stmt_order = $conn->prepare($sql_order);

if ($stmt_order === false) {
    die("Error prepare statement: " . $conn->error);
}

$stmt_order->bind_param("i", $order_id); // 'i' untuk integer
$stmt_order->execute();
$result_order = $stmt_order->get_result();

if ($result_order->num_rows > 0) {
    $order = $result_order->fetch_assoc();
}
$stmt_order->close();

// Cek jika pesanan tidak ditemukan
if (!$order) {
    die("Error: Pesanan dengan ID #{$order_id} tidak ditemukan.");
}

// 3. Ambil Data Detail Item Pesanan (order_items)
$sql_items = "
    SELECT oi.*, p.name, p.brand, p.image1 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id_product
    WHERE oi.order_id = ?
";
$stmt_items = $conn->prepare($sql_items);

if ($stmt_items === false) {
    die("Error prepare statement item: " . $conn->error);
}

$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

while($row = $result_items->fetch_assoc()) {
    $items[] = $row;
}
$stmt_items->close();

?>
<div class="container" style="max-width:600px; margin-top:40px; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1);">
    <h2 style="text-align:center; color:var(--accent);">🎉 Pesanan Berhasil Diproses!</h2>
    <p style="text-align:center; color:var(--muted);">Terima kasih atas pesanan Anda. Berikut adalah detail struk pesanan.</p>

    <hr style="margin:20px 0;">

    <div class="order-summary-header">
        <p><strong>Nomor Pesanan:</strong> <span style="font-size:1.2em; color:var(--accent); font-weight:bold;">#<?php echo htmlspecialchars($order['order_id']); ?></span></p>
        <p><strong>Status:</strong> <span style="font-weight:bold;"><?php echo htmlspecialchars($order['status']); ?></span></p>
        <p><strong>Tanggal Order:</strong> <?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></p>
        <p><strong>Metode Bayar:</strong> <?php echo htmlspecialchars(strtoupper($order['metode_pembayaran'])); ?></p>
    </div>

    <hr style="margin:20px 0;">

    <h3>Detail Pembeli</h3>
    <p>Nama: <?php echo htmlspecialchars($order['nama_lengkap']); ?></p>
    <p>Email: <?php echo htmlspecialchars($order['email']); ?></p>
    <p>Alamat: <?php echo htmlspecialchars($order['alamat']); ?></p>

    <hr style="margin:20px 0;">

    <h3>Rincian Barang (<?php echo count($items); ?> Item)</h3>
    <?php 
    $total_items = 0;
    foreach ($items as $item): 
        $subtotal = $item['quantity'] * $item['price_at_purchase'];
        $total_items += $subtotal;
    ?>
    <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee;">
        <div>
            <strong><?php echo htmlspecialchars($item['name']); ?></strong> (x<?php echo $item['quantity']; ?>)
            <small style="display:block; color:var(--muted);">Ukuran: <?php echo htmlspecialchars($item['size']); ?></small>
        </div>
        <div style="text-align:right;">
            Rp <?php echo number_format($subtotal, 0, ',', '.'); ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div style="margin-top: 15px; display:flex; justify-content:space-between; font-size:1.1em; font-weight:bold; padding-top:10px; border-top: 2px solid #333;">
        <span>TOTAL BAYAR:</span>
        <span>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span>
    </div>

    <div style="text-align:center; margin-top:30px;">
        <a href="products.php" class="btn">Lanjut Belanja</a>
        </div>
</div>

<?php
require 'footer.php'; 
?>