<?php
include 'db_connect.php';

header('Content-Type: application/json');

function respond($status, $msg)
{
    echo json_encode([
        'status' => $status,
        'message' => $msg
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond('error', 'Metode tidak diizinkan! Gunakan form.');
}

$targetDir = __DIR__ . "/../upload/";

if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0777, true)) {
        respond('error', 'Gagal membuat folder upload. Periksa permission.');
    }
}

$name = $conn->real_escape_string($_POST['name'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$size = $conn->real_escape_string($_POST['size'] ?? '');
$brand = $conn->real_escape_string($_POST['brand'] ?? '');
$old_price = isset($_POST['old_price']) ? intval($_POST['old_price']) : null;
$rating      = floatval($_POST['rating'] ?? 0);
$description = $conn->real_escape_string($_POST['description'] ?? '');
$categories = $conn->real_escape_string($_POST['categories'] ?? '');

$uploadedImages = [];

foreach ($_FILES['photos']['tmp_name'] as $index => $tmp) {
    if ($_FILES['photos']['error'][$index] === UPLOAD_ERR_OK) {

        $orig = basename($_FILES['photos']['name'][$index]);
        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $safeName = uniqid('img_', true) . '.' . $ext;
        $targetPath = $targetDir . $safeName;

        if (move_uploaded_file($tmp, $targetPath)) {
            $uploadedImages[] = $safeName;
        }
    }
}


while (count($uploadedImages) < 5) {
    $uploadedImages[] = null;
}


$stmt = $conn->prepare("
    INSERT INTO products 
    (name, brand, categories, price, old_price, rating, description,
     size, stock, image1, image2, image3, image4, image5)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");


$stmt->bind_param(
    "sssiidssisssss",
    $name,
    $brand,
    $categories,
    $price,
    $old_price,
    $rating,
    $description,
    $size,
    $stock,
    $uploadedImages[0],
    $uploadedImages[1],
    $uploadedImages[2],
    $uploadedImages[3],
    $uploadedImages[4]
);


if ($stmt->execute()) {
    respond('success', 'Produk berhasil ditambahkan!');
} else {
    respond('error', 'Error saat menyimpan produk: ' . $stmt->error);
}
