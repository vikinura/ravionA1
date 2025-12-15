<?php
require 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses tidak valid'
    ]);
    exit;
}

$id = isset($_POST['id_product']) ? intval($_POST['id_product']) : 0;

if ($id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID produk tidak valid'
    ]);
    exit;
}

$check = $conn->prepare("SELECT name FROM products WHERE id_product = ?");
$check->bind_param("i", $id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Produk tidak ditemukan'
    ]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM products WHERE id_product = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Produk berhasil dihapus'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Produk tidak bisa dihapus (terkait data lain)'
    ]);
}
?>