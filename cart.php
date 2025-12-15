<?php
$page_title = 'Keranjang Belanja';
require 'header.php'; 
require 'db_connect.php'; 

// quick DB sanity check
if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection error: $conn not available. Periksa db_connect.php');
}

$cart_details = [];
$total_price = 0;

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

$cart_items = [];

if ($user_id !== null) {
    $sql = "SELECT id, product_id, `size`, qty FROM carts WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { die('DB prepare failed: ' . $conn->error); }
    $stmt->bind_param('i', $user_id);
} else {
    $sql = "SELECT id, product_id, `size`, qty FROM carts WHERE user_id IS NULL AND session_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { die('DB prepare failed: ' . $conn->error); }
    $stmt->bind_param('s', $session_id);
}
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $cart_items[] = ['cart_id' => $r['id'], 'id' => $r['product_id'], 'size' => $r['size'], 'qty' => (int)$r['qty']];
}
$stmt->close();

$cart_ids = array_column($cart_items, 'id');

if (!empty($cart_ids)) {
    $ids_str = implode(',', array_map('intval', $cart_ids));
    $sql = "SELECT id_product, name, brand, price, image1 FROM products WHERE id_product IN ($ids_str)";
    $result = $conn->query($sql);
    if ($result === false) { die('Products query failed: ' . $conn->error); }

    $product_map = [];
    while($row = $result->fetch_assoc()) {
        $product_map[$row['id_product']] = $row;
    }

    foreach ($cart_items as $item) {
        $product = $product_map[$item['id']] ?? null;
        if ($product) {
            $subtotal = $product['price'] * $item['qty'];
            $total_price += $subtotal;
            $cart_details[] = [
                'product' => $product,
                'item' => $item,
                'subtotal' => $subtotal,
            ];
        }
    }
}
?>
<main class="container cart-section">
    <h2>Keranjang Belanja</h2>
     <div id="cart-items">

       <?php if (empty($cart_details)): ?>
            <p>Keranjang kosong — <a href="products.php">Belanja sekarang</a></p>
       <?php else: ?>

        <?php foreach ($cart_details as $detail):
            $p = $detail['product'];
            $item = $detail['item'];
            $encoded_size = urlencode($item['size']);
        ?>
           <div class="cart-item">
                <img src="upload/<?php echo htmlspecialchars($p['image1']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" />

                <div style="flex:1">
                    <div style="font-weight:700"><?php echo htmlspecialchars($p['name']); ?></div>
                    <div style="font-size:13px;color:gray">Ukuran: <?php echo htmlspecialchars($item['size']); ?></div>
                    <div style="color:var(--muted)"><?php echo htmlspecialchars($p['brand']); ?></div>

                    <div style="margin-top:8px">
                        Rp <?php echo number_format($p['price'], 0, ',', '.'); ?> x <?php echo $item['qty']; ?> = <strong>Rp <?php echo number_format($detail['subtotal'], 0, ',', '.'); ?></strong>
                    </div>

                    <div class="action-row">
                    <a class="btn-qty" href="cart_action.php?action=dec&id=<?php echo $p['id_product']; ?>&size=<?php echo $encoded_size; ?>">&minus;</a>
    
                    <span class="qty-display"><?php echo $item['qty']; ?></span>
    
                    <a class="btn-qty" href="cart_action.php?action=inc&id=<?php echo $p['id_product']; ?>&size=<?php echo $encoded_size; ?>">&plus;</a>
    
                     <a class="btn-delete" href="cart_action.php?action=rem&id=<?php echo $p['id_product']; ?>&size=<?php echo $encoded_size; ?>">
                      Hapus
                    </a>
                    </div>
            </div>
        <?php endforeach; ?>

     </div>
    <div id="cart-summary" class="cart-summary">
        <div style="font-weight:700">Ringkasan Pesanan</div>
        <div style="margin-top:8px">Total: <strong>Rp <?php echo number_format($total_price, 0, ',', '.'); ?></strong></div>
        <div class="cart-actions">
          <a class="btn ghost" href="products.php">Lanjut Belanja</a>
          <a class="btn" href="checkout.php">Checkout</a>
        </div>
    </div>
    <?php endif; ?>

  </main>

<?php
require 'footer.php';
?>