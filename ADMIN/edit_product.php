<?php
require 'db_connect.php';

// 1. Ambil ID & Validasi
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo "<script>alert('ID Produk tidak valid!'); window.location='view_product.php';</script>";
    exit;
}

// 2. Logic Simpan Data (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $_POST['name'] ?? '';
    $brand       = $_POST['brand'] ?? '';
    $categories  = $_POST['categories'] ?? '';
    $price       = intval($_POST['price'] ?? 0);
    $old_price   = intval($_POST['old_price'] ?? 0);
    $rating      = floatval($_POST['rating'] ?? 0);
    $description = $_POST['description'] ?? '';
    $size        = $_POST['size'] ?? '';
    $stock       = intval($_POST['stock'] ?? 0);

    // Update Text Data
    $stmt = $conn->prepare("UPDATE products SET name=?, brand=?, categories=?, price=?, old_price=?, rating=?, description=?, size=?, stock=? WHERE id_product=?");
    $stmt->bind_param("sssiidssii", $name, $brand, $categories, $price, $old_price, $rating, $description, $size, $stock, $id);
    
    if ($stmt->execute()) {
        $stmt->close();

        // 3. Logic Upload Gambar
        $targetDir = __DIR__ . '/../upload/';
        for ($i = 1; $i <= 5; $i++) {
            $inputName = 'image' . $i;
            if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
                $tmp  = $_FILES[$inputName]['tmp_name'];
                $orig = $_FILES[$inputName]['name'];
                $ext  = pathinfo($orig, PATHINFO_EXTENSION);
                $newName = uniqid("img{$i}_", true) . '.' . $ext;
                
                if (move_uploaded_file($tmp, $targetDir . $newName)) {
                    $conn->query("UPDATE products SET image$i = '$newName' WHERE id_product = $id");
                }
            }
        }
        echo "<script>alert('Produk berhasil diperbarui!'); window.location='view_product.php';</script>";
        exit;
    }
}

// 4. Ambil Data Produk Existing
$stmt = $conn->prepare("SELECT * FROM products WHERE id_product = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product Full - Ravion</title>
    <link rel="stylesheet" href="style_admin.css">
    <style>
        /* --- CSS KHUSUS HALAMAN INI (LAYOUT FULL) --- */
        
        /* Override container form agar lebar 100% */
        .form-container {
            max-width: 100% !important; /* Force Full Width */
            background: transparent !important; /* Hilangkan box putih pembungkus luar */
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        /* Grid Layout: Kiri (Konten Utama) - Kanan (Sidebar Detail) */
        .edit-layout {
            display: grid;
            grid-template-columns: 2fr 1fr; /* Kiri 2 bagian, Kanan 1 bagian */
            gap: 24px;
        }

        /* Card Style untuk Panel */
        .panel {
            background: #fff;
            padding: 24px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            height: fit-content;
        }

        /* Styling Gambar */
        .img-edit-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        .img-slot {
            aspect-ratio: 1;
            border: 2px dashed #ddd;
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            position: relative;
            transition: 0.2s;
        }
        .img-slot:hover { border-color: #000; }
        .img-slot img { width: 100%; height: 100%; object-fit: cover; }
        .hidden-input { display: none; }

        /* Responsif untuk Mobile (jadi 1 kolom) */
        @media (max-width: 900px) {
            .edit-layout { grid-template-columns: 1fr; }
            .img-edit-grid { grid-template-columns: repeat(3, 1fr); }
        }
    </style>
</head>
<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <img src="../img/LOGo2.png" alt="Logo" class="logo-img">
                <h1>Ravion Store</h1>
            </div>
            <div class="nav-title">Navigations</div>
            <nav class="menu">
                <div class="menu-parent">
                    <button class="parent-btn" data-target="home-sub">
                        <span class="icon">🏠</span><span class="label">Home</span><span class=\"chev\">›</span>
                    </button>
                    <div id="home-sub" class="submenu"><a href="../index.php">Main Page</a></div>
                </div>
                <div class="menu-parent">
                    <button class="parent-btn" data-target="product-sub">
                        <span class="icon">🛍️</span><span class=\"label\">Product</span><span class=\"chev\">›</span>
                    </button>
                    <div id="product-sub" class="submenu" style="display:flex;">
                        <a href="view_product.php">View</a>
                        <a href="add_product.html">Add Product</a>
                    </div>
                </div>
                <a href="ordering.php" class="single-link"> <span class="icon">📦</span><span class="label">Ordering</span>
</a>
            </nav>
        </aside>

        <main class="container">
            <div class="flex-between">
                <h2>Edit Product</h2>
                <div style="display:flex; gap:10px;">
                    <a href="view_product.php" class="btn-ghost" style="padding:10px 20px;">Cancel</a>
                    <button type="button" onclick="document.getElementById('mainForm').submit()" class="btn">Save Changes</button>
                </div>
            </div>

            <form id="mainForm" action="" method="POST" enctype="multipart/form-data" class="form-container">
                
                <div class="edit-layout">
                    
                    <div class="left-column">
                        
                        <div class="panel" style="margin-bottom: 24px;">
                            <h3 style="margin-bottom:15px; font-size:16px;">General Information</h3>
                            
                            <div class="form-group">
                                <label class="label">Product Name</label>
                                <input type="text" name="name" class="input" style="font-size:16px; font-weight:600;" value="<?= htmlspecialchars($product['name']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="label">Description</label>
                                <textarea name="description" class="input" rows="8" style="resize:vertical;"><?= htmlspecialchars($product['description']) ?></textarea>
                            </div>
                        </div>

                        <div class="panel">
                            <h3 style="margin-bottom:15px; font-size:16px;">Product Media</h3>
                            <div class="img-edit-grid">
                                <?php for($i=1; $i<=5; $i++): 
                                    $imgVal = $product['image'.$i];
                                    $imgSrc = !empty($imgVal) ? "../upload/".$imgVal : "";
                                ?>
                                <div>
                                    <input type="file" name="image<?= $i ?>" id="file-<?= $i ?>" class="hidden-input" accept="image/*" onchange="previewFile(this, 'preview-<?= $i ?>')">
                                    <div class="img-slot" onclick="document.getElementById('file-<?= $i ?>').click()" title="Change Image <?= $i ?>">
                                        <?php if($imgSrc): ?>
                                            <img src="<?= $imgSrc ?>" id="preview-<?= $i ?>">
                                        <?php else: ?>
                                            <img src="" id="preview-<?= $i ?>" style="display:none;">
                                            <div id="plus-<?= $i ?>" style="color:#ccc; font-size:24px;">+</div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="text-align:center; font-size:10px; margin-top:5px; color:#888;">IMG <?= $i ?></div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                    </div>

                    <div class="right-column">
                        
                        <div class="panel" style="margin-bottom: 24px;">
                            <h3 style="margin-bottom:15px; font-size:16px;">Organization</h3>
                            
                            <div class="form-group">
                                <label class="label">Brand</label>
                                <input type="text" name="brand" class="input" value="<?= htmlspecialchars($product['brand']) ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="label">Category</label>
                                <input type="text" name="categories" class="input" value="<?= htmlspecialchars($product['categories']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="label">Size / Ukuran</label>
                                <input type="text" name="size" class="input" value="<?= htmlspecialchars($product['size']) ?>">
                            </div>
                        </div>

                        <div class="panel">
                            <h3 style="margin-bottom:15px; font-size:16px;">Pricing & Inventory</h3>
                            
                            <div class="form-group">
                                <label class="label">Price (IDR)</label>
                                <input type="number" name="price" class="input" value="<?= $product['price'] ?>" style="font-weight:bold;" required>
                            </div>

                            <div class="form-group">
                                <label class="label">Old Price (Coret)</label>
                                <input type="number" name="old_price" class="input" value="<?= $product['old_price'] ?>">
                            </div>

                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                                <div>
                                    <label class="label">Stock</label>
                                    <input type="number" name="stock" class="input" value="<?= $product['stock'] ?>" required>
                                </div>
                                <div>
                                    <label class="label">Rating</label>
                                    <input type="number" step="0.1" max="5" name="rating" class="input" value="<?= $product['rating'] ?>">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-save" style="width:100%; margin-top:24px; padding: 15px;">UPDATE PRODUCT</button>

                    </div>
                </div>
            </form>
            </main>
    </div>

    <script>
        // Preview Function
        function previewFile(input, imgId) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById(imgId);
                    img.src = e.target.result;
                    img.style.display = "block";
                    const plus = input.nextElementSibling.querySelector('div[id^="plus-"]');
                    if(plus) plus.style.display='none';
                }
                reader.readAsDataURL(file);
            }
        }
        
        // Sidebar Toggle
        document.querySelectorAll('.parent-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const t = document.getElementById(btn.dataset.target);
                if(t) t.style.display = (t.style.display === 'flex') ? 'none' : 'flex';
            });
        });
    </script>
</body>
</html>