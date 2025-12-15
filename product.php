<?php
require 'db_connect.php';

$page_title = 'Detail Produk';
require 'header.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$images = [];

if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id_product = ?");
    $stmt->bind_param("i", $product_id); 
    $stmt->execute();
    $result_detail = $stmt->get_result();

    if ($result_detail && $result_detail->num_rows == 1) {
        $product = $result_detail->fetch_assoc();
        
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($product['image'.$i])) {
                $images[] = 'upload/' . $product['image'.$i];
            }
        }
        $sizes = explode(',', $product['size']);
    }
    $stmt->close();
}

if (!$product) {
    header("Location: products.php");
    exit();
}

$formattedPrice = number_format($product['price'], 0, ',', '.');
$formattedOldPrice = $product['old_price'] ? number_format($product['old_price'], 0, ',', '.') : null;
$mainImageSource = $images[0] ?? 'upload/no-image.png'; 
?>

<style>
    /* Layout Utama */
    .product-detail-container {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 50px;
        padding-top: 40px;
        padding-bottom: 80px;
        align-items: start;
    }

    /* Bagian Gambar (Kiri) */
    .gallery-wrapper {
        display: flex;
        flex-direction: column-reverse; /* Thumbnail di bawah */
        gap: 15px;
    }

    .main-image-box {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 1px solid #eee;
    }

    .main-image-box img {
        width: 100%;
        max-width: 500px;
        height: auto;
        object-fit: contain;
        mix-blend-mode: multiply;
    }

    /* PERBAIKAN DISINI: Menambah padding agar outline tidak kepotong */
    .thumb-list {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 10px; /* Jarak aman di semua sisi */
        margin-top: 5px;
    }

    .thumb {
        width: 70px;
        height: 70px;
        object-fit: contain;
        border: 1px solid #ddd;
        border-radius: 8px;
        cursor: pointer;
        opacity: 0.6;
        transition: all 0.3s ease; /* Transisi diperhalus */
        background: #fff;
        padding: 5px;
        box-sizing: border-box; /* Agar border dihitung di dalam lebar */
    }

    .thumb:hover, .thumb.active {
        border: 2px solid #000; /* Border lebih tegas */
        opacity: 1;
        transform: translateY(-3px); /* Gerak ke atas sedikit, bukan zoom */
        box-shadow: 0 5px 10px rgba(0,0,0,0.1); /* Bayangan halus */
    }

    /* Bagian Info (Kanan) */
    .product-info h1 {
        font-size: 32px;
        font-weight: 800;
        margin: 5px 0 15px;
        line-height: 1.2;
    }

    .brand-text {
        text-transform: uppercase;
        font-size: 14px;
        font-weight: 600;
        color: #888;
        letter-spacing: 1px;
    }

    .price-lg {
        font-size: 26px;
        color: #d32f2f;
        font-weight: 700;
    }

    .old-price {
        text-decoration: line-through;
        color: #999;
        font-size: 16px;
        margin-left: 10px;
        font-weight: normal;
    }

    .desc-text {
        font-size: 15px;
        line-height: 1.6;
        color: #555;
        margin: 25px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        padding: 20px 0;
    }

    /* Pilihan Size */
    .option-label {
        font-weight: 700;
        font-size: 14px;
        display: block;
        margin-bottom: 10px;
        color: #333;
    }

    .sizes-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 25px;
    }

    .size-btn {
        min-width: 50px;
        height: 40px;
        border: 1px solid #ddd;
        background: #fff;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .size-btn:hover {
        border-color: #000;
    }

    .size-btn.selected {
        background: #000;
        color: #fff;
        border-color: #000;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    /* Quantity Input */
    .qty-wrapper {
        display: flex;
        align-items: center;
        border: 1px solid #ddd;
        border-radius: 8px;
        width: fit-content;
        margin-bottom: 25px;
    }

    .qty-btn {
        width: 40px;
        height: 40px;
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: #555;
    }
    
    .qty-btn:hover { background: #f5f5f5; }

    #product-qty {
        width: 50px;
        text-align: center;
        border: none;
        font-size: 16px;
        font-weight: 600;
        appearance: none; 
        -moz-appearance: textfield;
    }

    /* Tombol Aksi */
    .action-buttons {
        display: flex;
        gap: 15px;
    }

    .btn-main, .btn-sec {
        flex: 1;
        padding: 16px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        text-transform: uppercase;
        border: none;
        transition: 0.3s;
    }

    .btn-main {
        background: #000;
        color: #fff;
    }

    .btn-main:hover {
        background: #d32f2f;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(211, 47, 47, 0.3);
    }

    .btn-sec {
        background: #fff;
        color: #000;
        border: 2px solid #000;
    }

    .btn-sec:hover {
        background: #f0f0f0;
    }

    /* Toast Notification */
    #toast-notification {
        visibility: hidden;
        min-width: 250px;
        background-color: #333; 
        color: #fff;
        text-align: center;
        border-radius: 50px;
        padding: 16px;
        position: fixed;
        z-index: 9999;
        left: 50%;
        bottom: 30px;
        transform: translateX(-50%);
        font-size: 15px;
        box-shadow: 0px 4px 15px rgba(0,0,0,0.2);
        opacity: 0;
        transition: opacity 0.5s, bottom 0.5s;
    }

    #toast-notification.show {
        visibility: visible;
        opacity: 1;
        bottom: 50px; 
    }

    /* Responsive HP */
    @media (max-width: 768px) {
        .product-detail-container {
            grid-template-columns: 1fr;
            gap: 30px;
        }
        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<main class="container">
    
    <div class="product-detail-container"> 
        
        <div class="gallery-wrapper">
            <div class="thumb-list" id="thumb-list">
                <?php foreach ($images as $i => $img): ?>
                    <img src="<?php echo $img; ?>" 
                         class="thumb <?php echo ($i === 0) ? 'active' : ''; ?>" 
                         onclick="changeMainImage('<?php echo $img; ?>', this)">
                <?php endforeach; ?>
            </div>

            <div class="main-image-box">
                <img id="main-product-image" 
                     src="<?php echo htmlspecialchars($mainImageSource); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
        </div>

        <div class="product-info">
            

            <div class="brand-text" style="margin-bottom: 5px;">
                <?php echo htmlspecialchars($product['brand'] ?? ''); ?>
            </div>

            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div style="margin-bottom: 20px;">
                <span class="price-lg">Rp <?php echo $formattedPrice; ?></span>
                <?php if ($formattedOldPrice): ?>
                    <span class="old-price">Rp <?php echo $formattedOldPrice; ?></span>
                <?php endif; ?>
            </div>

            <?php if (!empty($product['categories'])): ?>
                <div style="font-size: 12px; font-weight: 700; color: #4d4949ff; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 1px;">
                    <?php echo htmlspecialchars($product['categories']); ?>
                </div>
            <?php endif; ?>

            <div style="font-size:14px; color:#f39c12; margin-bottom:10px;">
                ⭐ Rating: <strong><?php echo $product['rating']; ?></strong>/5.0
            </div>

            <p class="desc-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

            <div style="margin-bottom: 20px;">
                <span class="option-label">Pilih Ukuran (Size):</span>
                <div class="sizes-grid" id="size-options">
                    <?php foreach ($sizes as $size): ?>
                        <button type="button" class="size-btn" data-size="<?php echo trim($size); ?>">
                            <?php echo trim($size); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <span class="option-label">Jumlah:</span>
                <div class="qty-wrapper">
                    <button type="button" class="qty-btn qty-btn-minus">-</button>
                    <input id="product-qty" type="number" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly /> 
                    <button type="button" class="qty-btn qty-btn-plus">+</button>
                </div>
                <small style="color:#666;">Stok tersedia: <?php echo $product['stock']; ?> pasang</small>
            </div>

            <div class="action-buttons">
                <form method="POST" action="cart_action.php" id="add-cart-form" style="flex:1;">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?php echo $product['id_product']; ?>">
                    <input type="hidden" name="size" id="selected-size" value="">
                    <input type="hidden" name="qty" id="form-qty" value="1">
                    
                    <button class="btn-main" id="add-to-cart-btn" type="submit">
                        Masukkan Keranjang
                    </button> 
                </form>

                <form method="POST" action="cart_action.php" id="buy-now-form" style="flex:1;">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?php echo $product['id_product']; ?>">
                    <input type="hidden" name="goto" value="checkout.php">
                    <input type="hidden" name="size" id="selected-size-2" value="">
                    <input type="hidden" name="qty" id="form-qty-2" value="1">
                    
                    <button class="btn-sec" id="buy-now-btn" type="submit">
                        Beli Sekarang
                    </button>
                </form>
            </div>
        </div>
        
    </div>
    
    <div id="toast-notification">Produk berhasil ditambahkan!</div>

</main>


<?php require 'footer.php'; ?>

<script src="js/ui-helpers.js"></script> 
<script src="js/main-init.js"></script> 

<script>
  function changeMainImage(src, element) {
      document.getElementById('main-product-image').src = src;
      document.querySelectorAll('#thumb-list img').forEach(img => img.classList.remove('active'));
      element.classList.add('active');
  }

  document.addEventListener('DOMContentLoaded', () => {
    const qtyInput = document.getElementById('product-qty');
    const formAdd = document.getElementById('add-cart-form');
    const formBuy = document.getElementById('buy-now-form');
    const btnMinus = document.querySelector('.qty-btn-minus');
    const btnPlus = document.querySelector('.qty-btn-plus');
    const toast = document.getElementById('toast-notification'); 

    function showToast(message) {
        if(!toast) return;
        toast.innerText = message;
        toast.className = "show"; 
        setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
    }

    function syncHiddenQty(val) {
        if (document.getElementById('form-qty')) document.getElementById('form-qty').value = val;
        if (document.getElementById('form-qty-2')) document.getElementById('form-qty-2').value = val;
    }

    function updateQty(delta) {
        let current = parseInt(qtyInput.value) || 1;
        let next = current + delta;
        if (next < 1) next = 1;
        const maxStock = parseInt(qtyInput.getAttribute('max')) || 999;
        if (next > maxStock) next = maxStock;
        qtyInput.value = next;
        syncHiddenQty(next);
    }

    if (btnMinus) btnMinus.addEventListener('click', () => updateQty(-1));
    if (btnPlus) btnPlus.addEventListener('click', () => updateQty(1));
    if (qtyInput) {
        qtyInput.addEventListener('input', () => syncHiddenQty(parseInt(qtyInput.value) || 1));
    }

    document.querySelectorAll('.size-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
            e.target.classList.add('selected');
            const s = e.target.getAttribute('data-size');
            const inp1 = document.getElementById('selected-size');
            if(inp1) inp1.value = s;
            const inp2 = document.getElementById('selected-size-2');
            if(inp2) inp2.value = s;
        });
    });

    function checkSizeSelected() {
        const selectedSizeEl = document.querySelector('.sizes-grid .size-btn.selected');
        if (!selectedSizeEl) {
            showToast('⚠️ Harap pilih ukuran sepatu!');
            return false;
        }
        return true;
    }

    if (formAdd) {
        formAdd.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!checkSizeSelected()) return;

            syncHiddenQty(qtyInput.value);

            const formData = new FormData(formAdd);
            formData.append('ajax', '1'); 

            const btn = document.getElementById('add-to-cart-btn');
            const originalText = btn.innerText;
            btn.innerText = 'Menambahkan...';
            btn.disabled = true;

            try {
                const response = await fetch('cart_action.php', { 
                    method: 'POST',
                    body: formData
                });

                const textResult = await response.text();
                let result;
                try {
                    result = JSON.parse(textResult);
                } catch (err) {
                    console.error("Server Error (Not JSON):", textResult);
                    throw new Error("Respon server bermasalah.");
                }

                if (result.status === 'success') {
                    const cartCountEl = document.getElementById('cart-count'); 
                    if (cartCountEl) cartCountEl.innerText = result.total_qty;
                    
                    showToast('✅ Berhasil masuk keranjang!');
                } else {
                    showToast('❌ Gagal: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('❌ Terjadi kesalahan sistem.');
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });
    }

    if (formBuy) {
        formBuy.addEventListener('submit', (e) => {
            if (!checkSizeSelected()) {
                e.preventDefault(); 
            }
            syncHiddenQty(qtyInput.value);
        });
    }
  });
</script>

</body>
</html>