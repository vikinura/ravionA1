<?php
// FILE: checkout_process.php
session_start();
require 'db_connect.php'; 

// 1. Cek User / Session
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

// 2. Ambil Keranjang dari Database
$cart_items = [];
if ($user_id) {
    $sql = "SELECT product_id, size, qty FROM carts WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
} else {
    $sql = "SELECT product_id, size, qty FROM carts WHERE user_id IS NULL AND session_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $session_id);
}

if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) {
        $cart_items[] = $row;
    }
    $stmt->close();
}

if (empty($cart_items)) {
    $_SESSION['error_message'] = "Keranjang belanja kosong.";
    header('Location: products.php'); 
    exit;
}

// 3. Sanitasi Input Form
$nama_lengkap = filter_input(INPUT_POST, 'nama_lengkap', FILTER_SANITIZE_STRING);
$email        = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$telepon      = filter_input(INPUT_POST, 'telepon', FILTER_SANITIZE_STRING);
$alamat       = filter_input(INPUT_POST, 'alamat', FILTER_SANITIZE_STRING);
$metode_bayar = filter_input(INPUT_POST, 'metode', FILTER_SANITIZE_STRING);

if (empty($nama_lengkap) || empty($email) || empty($metode_bayar)) {
    $_SESSION['error_message'] = "Data pembeli tidak lengkap.";
    header('Location: checkout.php');
    exit;
}

// 4. Hitung Total Harga (Ambil harga ASLI dari tabel products)
// Kita kumpulkan semua ID produk dari keranjang
$cart_product_ids = array_column($cart_items, 'product_id'); // PENTING: pakai key 'product_id' sesuai query di atas

if (empty($cart_product_ids)) {
    $_SESSION['error_message'] = "Gagal memproses item keranjang.";
    header('Location: cart.php'); 
    exit;
}

// Buat placeholder (?,?,?)
$placeholders = implode(',', array_fill(0, count($cart_product_ids), '?'));
$types = str_repeat('i', count($cart_product_ids));

// Query harga produk
$sql_prod = "SELECT id_product, price FROM products WHERE id_product IN ($placeholders)";
$stmt_prod = $conn->prepare($sql_prod);
$stmt_prod->bind_param($types, ...$cart_product_ids);
$stmt_prod->execute();
$res_prod = $stmt_prod->get_result();

$prices = [];
while($p = $res_prod->fetch_assoc()) {
    $prices[$p['id_product']] = $p['price'];
}
$stmt_prod->close();

// Hitung total final
$calculated_total = 0;
foreach ($cart_items as $item) {
    $pid = $item['product_id'];
    $qty = $item['qty'];
    if (isset($prices[$pid])) {
        $calculated_total += ($prices[$pid] * $qty);
    }
}

// 5. Transaksi Database Insert Order
$conn->begin_transaction();

try {
    $status = 'Menunggu Pembayaran';
    $now = date('Y-m-d H:i:s');
    
    // Siapkan variable user_id untuk DB (bisa NULL)
    $db_user_id = $user_id ? $user_id : NULL;

    // Insert ke ORDERS
    // Kolom tabel orders: user_id, nama_lengkap, email, telepon, alamat, total_harga, metode_pembayaran, status, order_date
    $sql_order = "INSERT INTO orders (user_id, nama_lengkap, email, telepon, alamat, total_harga, metode_pembayaran, status, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_ord = $conn->prepare($sql_order);
    // Tipe data: i=int, s=string, d=double/decimal
    $stmt_ord->bind_param("issssdsss", 
        $db_user_id, $nama_lengkap, $email, $telepon, $alamat, $calculated_total, $metode_bayar, $status, $now
    );
    
    if (!$stmt_ord->execute()) {
        throw new Exception("Error Insert Order: " . $stmt_ord->error);
    }
    $new_order_id = $conn->insert_id;
    $stmt_ord->close();

    // Opsional: Insert ke ORDER_ITEMS jika Anda punya tabel itu (tidak ada di screenshot, tapi ada di kode lama Anda).
    // Saya non-aktifkan dulu agar Anda fokus ke tabel ORDERS berhasil dulu. 
    // Jika punya tabel order_items, uncomment kode di bawah ini:
    /*
    $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase, size) VALUES (?, ?, ?, ?, ?)";
    $stmt_item = $conn->prepare($sql_item);
    foreach ($cart_items as $itm) {
        $pid = $itm['product_id'];
        $q   = $itm['qty'];
        $sz  = $itm['size'] ?? '';
        $prc = $prices[$pid] ?? 0;
        $stmt_item->bind_param("iiids", $new_order_id, $pid, $q, $prc, $sz);
        $stmt_item->execute();
    }
    $stmt_item->close();
    */

    // 6. Kosongkan Keranjang
    if ($user_id) {
        $stmt_del = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
        $stmt_del->bind_param('i', $user_id);
    } else {
        $stmt_del = $conn->prepare("DELETE FROM carts WHERE user_id IS NULL AND session_id = ?");
        $stmt_del->bind_param('s', $session_id);
    }
    $stmt_del->execute();
    $stmt_del->close();

    $conn->commit();

    // Sukses
    header("Location: payment.php?order_id=" . $new_order_id);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "Gagal memproses pesanan: " . $e->getMessage();
    header('Location: checkout.php');
    exit;
}
?>