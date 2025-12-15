<?php
// FILE: cart_action.php
ob_start(); 
session_start();
require 'db_connect.php'; 

// Matikan error display agar tidak merusak respons JSON
ini_set('display_errors', 0);
error_reporting(0); 

// 1. Inisialisasi User
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

// 2. Tangkap Input
$action = $_REQUEST['action'] ?? null;
// Pastikan kita menangkap ID Produk, bukan ID Cart
$product_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null; 
$size_raw = $_REQUEST['size'] ?? null;
$size = ($size_raw !== null && $size_raw !== '') ? trim(rawurldecode((string)$size_raw)) : null;
$qty_input = isset($_REQUEST['qty']) ? (int)$_REQUEST['qty'] : 1;
if ($qty_input < 1) $qty_input = 1;

// Helper: Cari Item di Keranjang (Mencocokkan User/Sesi + Produk + Ukuran)
function find_cart_item($conn, $user_id, $session_id, $product_id, $size) {
    if ($user_id !== null) {
        $sql = "SELECT * FROM carts WHERE user_id = ? AND product_id = ? AND size <=> ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iis', $user_id, $product_id, $size);
    } else {
        $sql = "SELECT * FROM carts WHERE user_id IS NULL AND session_id = ? AND product_id = ? AND size <=> ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sis', $session_id, $product_id, $size);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row;
}

// Handler AJAX: Hitung Total Qty untuk Badge Keranjang
if (isset($_REQUEST['ajax']) && $action === 'count') {
    $totalQty = 0;
    if ($user_id) {
        $q = $conn->query("SELECT SUM(qty) as t FROM carts WHERE user_id = $user_id");
    } else {
        $q = $conn->query("SELECT SUM(qty) as t FROM carts WHERE user_id IS NULL AND session_id = '$session_id'");
    }
    if ($q && $d = $q->fetch_assoc()) {
        $totalQty = (int)$d['t'];
    }
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'total_qty' => $totalQty]);
    exit;
}

// Redirect jika akses langsung tanpa action
if (!$action) { header('Location: cart.php'); exit; }
$ref = $_SERVER['HTTP_REFERER'] ?? 'cart.php';

// Cari apakah item sudah ada?
$existing = null;
if ($product_id) {
    $existing = find_cart_item($conn, $user_id, $session_id, $product_id, $size);
}

// --- LOGIKA UTAMA ---

if ($action === 'add' || $action === 'inc') {
    $amount_to_add = ($action === 'add') ? $qty_input : 1;

    if ($existing) {
        // UPDATE (Item sudah ada, tambah qty)
        $newQty = $existing['qty'] + $amount_to_add;
        // Gunakan ID Cart ($existing['id']) untuk update
        $stmt = $conn->prepare("UPDATE carts SET qty = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('ii', $newQty, $existing['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        // INSERT (Item baru)
        // Pastikan urutan kolom sesuai struktur tabel carts Anda
        if ($user_id === null) {
            // Tamu
            $stmt = $conn->prepare("INSERT INTO carts (user_id, session_id, product_id, size, qty) VALUES (NULL, ?, ?, ?, ?)");
            $stmt->bind_param('sisi', $session_id, $product_id, $size, $amount_to_add);
        } else {
            // User Login
            $stmt = $conn->prepare("INSERT INTO carts (user_id, session_id, product_id, size, qty) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('isisi', $user_id, $session_id, $product_id, $size, $amount_to_add);
        }
        $stmt->execute();
        $stmt->close();
    }

} elseif ($action === 'dec') {
    if ($existing) {
        $newQty = $existing['qty'] - 1;
        if ($newQty <= 0) {
            $conn->query("DELETE FROM carts WHERE id = " . $existing['id']);
        } else {
            $conn->query("UPDATE carts SET qty = $newQty, updated_at = NOW() WHERE id = " . $existing['id']);
        }
    }

} elseif ($action === 'rem') {
    if ($existing) {
        $conn->query("DELETE FROM carts WHERE id = " . $existing['id']);
    }
}

// --- RESPON AJAX (Untuk Tombol Add to Cart) ---
if (isset($_REQUEST['ajax'])) {
    // Hitung ulang total qty setelah perubahan
    $totalQty = 0;
    if ($user_id) {
        $q = $conn->query("SELECT SUM(qty) as t FROM carts WHERE user_id = $user_id");
    } else {
        $q = $conn->query("SELECT SUM(qty) as t FROM carts WHERE user_id IS NULL AND session_id = '$session_id'");
    }
    if ($q && $d = $q->fetch_assoc()) $totalQty = (int)$d['t'];

    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'total_qty' => $totalQty]);
    exit;
}

// Redirect Normal (Untuk tombol +, -, Hapus di cart.php)
$goto = $_REQUEST['goto'] ?? null;
if ($goto) { header('Location: ' . $goto); exit; }
header("Location: $ref");
exit;
?>