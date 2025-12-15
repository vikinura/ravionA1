<?php
ob_start(); 
session_start();
require 'db_connect.php';

ini_set('display_errors', 0);
error_reporting(0);

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$session_id = session_id();

$action = $_REQUEST['action'] ?? null;
$product_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null; 
$size_raw = $_REQUEST['size'] ?? null;
$size = ($size_raw !== null && $size_raw !== '') ? trim(rawurldecode((string)$size_raw)) : null;
$qty_input = isset($_REQUEST['qty']) ? (int)$_REQUEST['qty'] : 1;
if ($qty_input < 1) $qty_input = 1;

function find_cart_item($conn, $user_id, $session_id, $product_id, $size) {
    if ($product_id === null) return null;
    if ($user_id !== null) {
        $sql = "SELECT * FROM carts WHERE user_id = ? AND product_id = ? AND size <=> ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iis', $user_id, $product_id, $size); 
    } else {
        $sql = "SELECT * FROM carts WHERE user_id IS NULL AND session_id = ? AND product_id = ? AND size <=> ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sis', $session_id, $product_id, $size);
    }

    if (!$stmt) return null;
    
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row;
}

if (isset($_REQUEST['ajax']) && $action === 'count') {
    $totalQty = 0;
    $sql = "SELECT SUM(qty) as t FROM carts WHERE " . ($user_id 
        ? "user_id = $user_id" 
        : "user_id IS NULL AND session_id = '$session_id'");
    
    $q = $conn->query($sql);
    
    if ($q && $d = $q->fetch_assoc()) {
        $totalQty = (int)$d['t'];
    }
    
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'total_qty' => $totalQty]);
    exit;
}

if (!$action) { 
    header('Location: cart.php'); 
    exit; 
}

$ref = $_SERVER['HTTP_REFERER'] ?? 'cart.php';

$existing = ($product_id) ? find_cart_item($conn, $user_id, $session_id, $product_id, $size) : null;

if ($action === 'add' || $action === 'inc') {
    $amount_to_add = ($action === 'add') ? $qty_input : 1;

    if ($existing) {
        $newQty = $existing['qty'] + $amount_to_add;
        $stmt = $conn->prepare("UPDATE carts SET qty = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('ii', $newQty, $existing['id']);
        $stmt->execute();
        $stmt->close();
    } else {

        if ($user_id === null) {
            $stmt = $conn->prepare("INSERT INTO carts (user_id, session_id, product_id, size, qty) VALUES (NULL, ?, ?, ?, ?)");
            $stmt->bind_param('sisi', $session_id, $product_id, $size, $amount_to_add);
        } else {
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

if (isset($_REQUEST['ajax'])) {
    $totalQty = 0;
    $sql = "SELECT SUM(qty) as t FROM carts WHERE " . ($user_id 
        ? "user_id = $user_id" 
        : "user_id IS NULL AND session_id = '$session_id'");
        
    $q = $conn->query($sql);
    if ($q && $d = $q->fetch_assoc()) $totalQty = (int)$d['t'];

    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'total_qty' => $totalQty]);
    exit;
}

$goto = $_REQUEST['goto'] ?? null;
$location = $goto ?? $ref;

ob_end_clean();
header("Location: $location");
exit;
?>