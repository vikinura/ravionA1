<?php
require 'db_connect.php';

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

$where = "WHERE stock > 0";
if (!empty($search)) {
    $where .= " AND (name LIKE '%$search%' OR brand LIKE '%$search%' OR description LIKE '%$search%')";
}

$order = "ORDER BY id_product DESC";
if ($sort === 'price-asc') {
    $order = "ORDER BY price ASC";
} elseif ($sort === 'price-desc') {
    $order = "ORDER BY price DESC";
}

$sql = "SELECT * FROM products $where $order";
$res = $conn->query($sql);

$page_title = 'Daftar Produk';
require 'header.php';
?>

<main>
  <div class="container" style="padding-top: 40px; padding-bottom: 60px;">
    
    <div class="page-header">
        <h2 class="section-title">Koleksi Sepatu</h2>

        <form action="products.php" method="GET" class="filter-form">
            
            <input type="text" name="search" class="search-input" 
                   placeholder="Cari sepatu..." 
                   value="<?php echo htmlspecialchars($search); ?>">

            <select name="sort" class="sort-select" onchange="this.form.submit()">
                <option value="default" <?php if($sort == 'default') echo 'selected'; ?>>Terbaru</option>
                <option value="price-asc" <?php if($sort == 'price-asc') echo 'selected'; ?>>Harga Terendah</option>
                <option value="price-desc" <?php if($sort == 'price-desc') echo 'selected'; ?>>Harga Tertinggi</option>
            </select>
        </form>
    </div>

    <div class="product-grid">
        <?php if ($res && $res->num_rows > 0): ?>
            <?php while ($p = $res->fetch_assoc()): 
                // Logic PHP Harga
                $formattedPrice = number_format($p['price'], 0, ',', '.');
                $formattedOldPrice = $p['old_price'] ? number_format($p['old_price'], 0, ',', '.') : null;
            ?>
                
                <div class="product-card">
                    <a href="product.php?id=<?php echo $p['id_product']; ?>" class="card-img-wrap">
                        <img src="upload/<?php echo $p['image1']; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                    </a>

                    <div class="card-body">
                        <div class="card-brand"><?php echo htmlspecialchars($p['brand']); ?></div>
                        <h3 class="card-title">
                            <a href="product.php?id=<?php echo $p['id_product']; ?>" style="text-decoration:none; color:inherit;">
                                <?php echo htmlspecialchars($p['name']); ?>
                            </a>
                        </h3>
                        
                        <div class="price-box">
                            <span class="card-price">Rp <?php echo $formattedPrice; ?></span>
                            <?php if ($formattedOldPrice): ?>
                                <span class="card-old-price">Rp <?php echo $formattedOldPrice; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <a href="product.php?id=<?php echo $p['id_product']; ?>" class="btn-card-action">
                        Lihat Detail
                    </a>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align:center; padding: 40px;">
                <h3 style="color:#666;">Produk tidak ditemukan</h3>
                <p>Coba kata kunci lain atau reset pencarian.</p>
                <a href="products.php" style="color:var(--accent); text-decoration:underline;">Lihat Semua Produk</a>
            </div>
        <?php endif; ?>
    </div>
    
  </div>
</main>

<?php
require 'footer.php';
?>

<script src="js/ui-helpers.js"></script>
<script src="js/main-init.js"></script>
<script>
    document.getElementById('year').textContent = new Date().getFullYear();
</script>

</body>
</html>