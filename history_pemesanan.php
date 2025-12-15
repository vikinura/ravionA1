<?php
$page_title = 'History Pemesanan';
require 'header.php'; 
require 'db_connect.php'; 

// 1. Ambil ID Pengguna dari Session
$user_id = $_SESSION['user_id'] ?? null; // <-- Nilai ini HARUS sama dengan yang di kolom orders.user_id

if (!$user_id) {
    echo '<div class="container" style="text-align:center; padding:50px;"><h2>Silakan login untuk melihat history pemesanan.</h2><a href="login.php" class="btn">Login</a></div>';
    require 'footer.php';
    exit;
}

// 2. Ambil semua pesanan user yang sedang login
// Pastikan nama kolom 'user_id' sudah benar di database
$sql_orders = "SELECT order_id, total_harga, order_date, status FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt_orders = $conn->prepare($sql_orders);

if ($stmt_orders === false) {
    // Pesan error jika query gagal (misalnya kolom user_id belum ada/salah nama)
    die('<div class="container" style="padding:50px;"><h2>Error SQL. Pastikan kolom user_id ada dan benar.</h2></div>');
}

// Bind parameter (i untuk integer/user_id)
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
$orders = $result_orders->fetch_all(MYSQLI_ASSOC);
$stmt_orders->close();

?>

<main class="container" style="max-width:800px; margin-top:30px;">
    <h2>History Pemesanan Anda</h2>
    <p>Semua transaksi yang pernah Anda buat di Ravion Store.</p>

    <div id="history-list" style="margin-top:20px;">
        <?php if (empty($orders)): ?>
            <div class="card" style="padding:20px; text-align:center;">
                <p>Anda belum memiliki riwayat pemesanan.</p>
                <a href="products.php" class="btn" style="margin-top:10px;">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): 
                $order_id = $order['order_id'];
                $status = htmlspecialchars($order['status']);
                
                // Tentukan warna status
                $status_color = '#6c757d';
                if ($status === 'Menunggu Pembayaran') $status_color = '#dc3545'; 
                if ($status === 'Diproses') $status_color = '#ffc107'; 
                if ($status === 'Selesai') $status_color = '#28a745'; 
            ?>
                <div class="card" style="margin-bottom:20px; border:1px solid #ddd; padding:15px; border-radius:10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:10px;">
                        <div>
                            <strong>Pesanan #<?php echo htmlspecialchars($order_id); ?></strong>
                            <div style="font-size:14px; color:gray;">Tanggal: <?php echo date('d M Y, H:i', strtotime($order['order_date'])); ?></div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:700; color:<?php echo $status_color; ?>;">
                                Status: <?php echo $status; ?>
                            </div>
                            <div style="font-size:18px; font-weight:700; margin-top:5px;">Total: Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></div>
                        </div>
                    </div>

                    <div style="margin-top:10px; padding:5px 0;">
                        <h4 style="font-size:14px; margin-bottom:5px;">Rincian Barang:</h4>
                        <?php
                        // Ambil detail item untuk pesanan ini (termasuk join ke tabel products)
                        $sql_items = "
                            SELECT oi.*, p.name, p.image1 
                            FROM order_items oi
                            JOIN products p ON oi.product_id = p.id_product
                            WHERE oi.order_id = ?
                        ";
                        $stmt_items = $conn->prepare($sql_items);
                        $stmt_items->bind_param("i", $order_id);
                        $stmt_items->execute();
                        $result_items = $stmt_items->get_result();
                        
                        while ($item = $result_items->fetch_assoc()):
                        ?>
                        <div style="display:flex; gap:10px; align-items:center; padding:5px 0; border-bottom:1px dotted #f0f0f0;">
                            <img src="upload/<?php echo htmlspecialchars($item['image1']); ?>" style="width:40px; height:40px; object-fit:cover; border-radius:5px;">
                            <div style="flex:1; font-size:14px;">
                                <?php echo htmlspecialchars($item['name']); ?>
                                <small style="color:gray; display:block;">(Ukuran: <?php echo htmlspecialchars($item['size']); ?> | Qty: <?php echo $item['quantity']; ?>)</small>
                            </div>
                            <div style="font-size:14px; font-weight:600;">Rp <?php echo number_format($item['price_at_purchase'] * $item['quantity'], 0, ',', '.'); ?></div>
                        </div>
                        <?php endwhile;
                        $stmt_items->close();
                        ?>
                    </div>

                    <div style="text-align:right; margin-top:10px;">
                        <?php if ($status === 'Menunggu Pembayaran'): ?>
                            <a href="payment.php?order_id=<?php echo $order_id; ?>" class="btn-sm" style="background:#ffc107; color:#333; padding:8px 12px; border-radius:6px; text-decoration:none; font-weight:bold;">Bayar Sekarang</a>
                        <?php else: ?>
                            <a href="receipt.php?order_id=<?php echo $order_id; ?>" class="btn-sm" style="background:#007bff; color:#fff; padding:8px 12px; border-radius:6px; text-decoration:none; font-weight:bold;">Lihat Struk</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php require 'footer.php'; ?>