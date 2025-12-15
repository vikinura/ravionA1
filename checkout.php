<?php
$page_title = 'Checkout Pembayaran';
require 'header.php'; 
require 'db_connect.php'; 

// [FIX]: Ambil User ID atau Session ID untuk query database
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

$cart_items = [];

// [FIX]: Query mengambil item keranjang dari TABEL DATABASE (carts)
if ($user_id !== null) {
    // Jika user login, ambil berdasarkan user_id
    $sql_cart = "SELECT product_id as id, size, qty FROM carts WHERE user_id = ?";
    $stmt_cart = $conn->prepare($sql_cart);
    $stmt_cart->bind_param('i', $user_id);
} else {
    // Jika tamu, ambil berdasarkan session_id
    $sql_cart = "SELECT product_id as id, size, qty FROM carts WHERE user_id IS NULL AND session_id = ?";
    $stmt_cart = $conn->prepare($sql_cart);
    $stmt_cart->bind_param('s', $session_id);
}

if ($stmt_cart) {
    $stmt_cart->execute();
    $res_cart = $stmt_cart->get_result();
    while ($row = $res_cart->fetch_assoc()) {
        $cart_items[] = $row;
    }
    $stmt_cart->close();
}

// Lanjutkan logika checkout seperti biasa
$cart_ids = array_column($cart_items, 'id');
$cart_details = [];
$total_price = 0;

// Tampilkan pesan error session jika ada (dari proses gagal)
if (isset($_SESSION['error_message'])) {
    echo '<div style="background-color:#f8d7da;color:#721c24;padding:15px;margin:15px auto;border-radius:5px;max-width:800px;">';
    echo '<strong>⚠️ Gagal Memproses Pesanan:</strong> ' . htmlspecialchars($_SESSION['error_message']);
    echo '</div>';
    unset($_SESSION['error_message']); 
}

if (!empty($cart_ids)) {
    // 1. Buat placeholder untuk SQL IN (?)
    $placeholders = implode(',', array_fill(0, count($cart_ids), '?')); 
    $types = str_repeat('i', count($cart_ids));
    
    // 2. Ambil detail produk berdasarkan ID yang ada di keranjang
    $sql = "SELECT id_product, name, brand, price, image1 FROM products WHERE id_product IN ($placeholders)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
         die('Prepare statement error: ' . htmlspecialchars($conn->error));
    }
    
    $stmt->bind_param($types, ...$cart_ids); 
    $stmt->execute();
    $result = $stmt->get_result();
    
    $product_map = [];
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $product_map[$row['id_product']] = $row;
        }
    }
    $stmt->close(); 
    
    // 3. Gabungkan data cart (qty/size) dengan data produk (nama/harga)
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

// [PENTING]: Jika keranjang kosong (dari database), baru lempar ke products.php
if (empty($cart_details)) {
    // Opsional: Anda bisa redirect ke cart.php atau products.php
    header('Location: products.php'); 
    exit;
}
?>

  <main class="checkout-container">
    <section class="checkout-form">
      <h2>Data Pembeli</h2>
      <form id="checkoutForm" action="checkout_process.php" method="POST">
        <label>Nama Lengkap</label>
        <input type="text" name="nama_lengkap" required 
            value="<?php echo htmlspecialchars($_POST['nama_lengkap'] ?? ''); ?>" />

        <label>Email</label>
        <input type="email" name="email" required 
            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />

        <label>Nomor Telepon</label>
        <input type="tel" name="telepon" required 
            value="<?php echo htmlspecialchars($_POST['telepon'] ?? ''); ?>" />

        <label>Alamat Lengkap</label>
        <textarea name="alamat" required><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>

        <label>Metode Pembayaran</label>
        <select name="metode" required>
          <option value="">Pilih metode pembayaran</option>
          <option value="qris">QRIS</option>
          <option value="va-bca">Virtual Account BCA</option>
          <option value="tf-bca">Transfer BCA</option>
        </select>
        
        <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">

        <button type="submit" class="btn-bayar">Lanjut ke Instruksi Pembayaran</button>
      </form>
    </section>

    <section class="checkout-summary">
      <h2>Ringkasan Belanja</h2>
      <div id="checkout-items">
        <?php foreach ($cart_details as $detail): 
            $p = $detail['product'];
            $item = $detail['item'];
        ?>
            <div class="checkout-item" style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
            <img src="upload/<?php echo htmlspecialchars($p['image1']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" style="width:70px;height:70px;object-fit:cover;border-radius:10px;border:1px solid #ddd">
            <div style="flex:1">
              <strong><?php echo htmlspecialchars($p['name']); ?></strong><br>
              <small>Ukuran: <?php echo htmlspecialchars($item['size']); ?></small><br>
              <small>Qty: <?php echo $item['qty']; ?></small>
            </div>
            <div style="text-align:right">
              <div>Rp <?php echo number_format($p['price'], 0, ',', '.'); ?></div>
              <div style="font-size:13px;color:gray">Subtotal: Rp <?php echo number_format($detail['subtotal'], 0, ',', '.'); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
      <div id="checkout-total">
        <hr style="margin:10px 0;">
        <p style="font-weight:700;font-size:16px">Total Belanja: Rp <?php echo number_format($total_price, 0, ',', '.'); ?></p>
      </div>
    </section>
  </main>
  
<div id="payment-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span> 
        <div id="payment-info">
            </div>
    </div>
</div>

<?php require 'footer.php'; ?>