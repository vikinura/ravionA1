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
  <section class="products-section container">
    <h2>Produk Sepatu</h2>

    <div class="filters">
      <form action="products.php" method="GET" style="display:flex; gap:12px; flex:1">
        <input name="search" id="search" placeholder="Cari sepatu..." value="<?php echo htmlspecialchars($search); ?>" />
        <select name="sort" id="sort" onchange="this.form.submit()">
          <option value="default" <?php echo ($sort=='default')?'selected':''; ?>>Sort: Relevansi</option>
          <option value="price-asc" <?php echo ($sort=='price-asc')?'selected':''; ?>>Harga: Rendah ke Tinggi</option>
          <option value="price-desc" <?php echo ($sort=='price-desc')?'selected':''; ?>>Harga: Tinggi ke Rendah</option>
        </select>
        <button type="submit" style="padding:10px; border-radius:8px; border:1px solid #ddd;">Cari</button>
      </form>
    </div>

    <div id="products-grid" class="products-grid">
      <?php if ($res && $res->num_rows > 0): ?>
        <?php while ($p = $res->fetch_assoc()):
            $formattedPrice = number_format($p['price'], 0, ',', '.');
            $formattedOldPrice = $p['old_price'] ? number_format($p['old_price'], 0, ',', '.') : null;
        ?>
          <div class="card">
            <a href="product.php?id=<?php echo $p['id_product']; ?>">
              <img src="upload/<?php echo htmlspecialchars($p['image1']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" />
            </a>
            <div class="meta">
              <div style="display:flex;justify-content:space-between;align-items:center">
                <div>
                  <div style="font-size:14px;color:var(--muted)"><?php echo htmlspecialchars($p['brand']); ?></div>
                  <div style="font-weight:700"><?php echo htmlspecialchars($p['name']); ?></div>
                </div>
                <div style="text-align:right">
                  <div class="price">Rp <?php echo $formattedPrice; ?></div>
                  <?php if ($formattedOldPrice): ?>
                    <div style="text-decoration:line-through;color:var(--muted);font-size:13px">Rp <?php echo $formattedOldPrice; ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <div style="margin-top:10px;">
              <a class="btn-sm" style="display:block; text-align:center; width:100%; box-sizing:border-box;" href="product.php?id=<?php echo $p['id_product']; ?>">
              Lihat Detail & Pilih Ukuran
              </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>Tidak ada produk ditemukan.</p>
      <?php endif; ?>
    </div>

  </section>
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